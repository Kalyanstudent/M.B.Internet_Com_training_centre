<?php
/**
 * SkillPoint Order & Enrollment Model with Transaction Safety
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once __DIR__ . '/Course.php';

class Order {
    public static function createOrder(int $userId, int $courseId, array $customerDetails, ?string $couponCode = null): array {
        $db = Database::getConnection();

        // 1. Fetch exact course details from database (Never trust frontend price)
        $course = Course::getByIdOrCode($courseId);
        if (!$course || $course['status'] !== 'active') {
            return ['success' => false, 'message' => 'The selected course is currently unavailable for enrollment.'];
        }

        $basePrice = (float) $course['price'];
        $discount = 0.0;
        $cleanCoupon = strtoupper(trim((string)$couponCode));

        // 2. Validate discount coupons server-side
        if ($cleanCoupon === 'SKILL10') {
            $discount = round($basePrice * 0.10, 2);
        } elseif ($cleanCoupon === 'WELCOME500') {
            $discount = min(500.0, $basePrice);
        }

        $finalAmount = max(0.0, $basePrice - $discount);
        $orderNumber = 'ORD-2026-' . rand(10000, 99999);

        // 3. Insert order record
        $stmt = $db->prepare("
            INSERT INTO orders (
                order_number, user_id, course_id, customer_name, mobile, email, address,
                course_title, course_price, coupon_code, discount_amount, final_amount,
                currency, payment_gateway, payment_status, created_at
            ) VALUES (
                :order_number, :user_id, :course_id, :customer_name, :mobile, :email, :address,
                :course_title, :course_price, :coupon_code, :discount_amount, :final_amount,
                'INR', 'Razorpay', 'Pending', NOW()
            )
        ");

        $stmt->execute([
            ':order_number'    => $orderNumber,
            ':user_id'         => $userId,
            ':course_id'       => $course['id'],
            ':customer_name'   => trim($customerDetails['name']),
            ':mobile'          => trim($customerDetails['mobile']),
            ':email'           => strtolower(trim($customerDetails['email'])),
            ':address'         => trim($customerDetails['address'] ?? ''),
            ':course_title'    => $course['title'],
            ':course_price'    => $basePrice,
            ':coupon_code'     => $cleanCoupon ?: null,
            ':discount_amount' => $discount,
            ':final_amount'    => $finalAmount
        ]);

        $orderId = (int) $db->lastInsertId();

        return [
            'success'      => true,
            'order_id'     => $orderId,
            'order_number' => $orderNumber,
            'course'       => $course,
            'amount'       => $finalAmount,
            'base_price'   => $basePrice,
            'discount'     => $discount
        ];
    }

    public static function completePayment(
        int $orderId, 
        string $gatewayPaymentId, 
        string $gatewayOrderId, 
        string $gatewaySignature, 
        string $gatewayName = 'Razorpay'
    ): array {
        $db = Database::getConnection();

        try {
            $db->beginTransaction();

            // Fetch order
            $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                $db->rollBack();
                return ['success' => false, 'message' => 'Order not found.'];
            }

            if ($order['payment_status'] === PAYMENT_STATUS_PAID) {
                $db->commit();
                return [
                    'success'           => true,
                    'message'           => 'Order was already verified and paid.',
                    'order_number'      => $order['order_number'],
                    'enrollment_number' => $order['enrollment_number']
                ];
            }

            // Generate unique Enrollment ID
            $enrollmentNumber = 'ENR-2026-' . rand(10000, 99999);
            $paymentNumber = !empty($gatewayPaymentId) ? $gatewayPaymentId : 'PAY-2026-' . rand(10000, 99999);

            // 1. Update order
            $upStmt = $db->prepare("
                UPDATE orders SET 
                    enrollment_number = :enr,
                    payment_status = 'Paid',
                    razorpay_order_id = :rzp_order,
                    razorpay_payment_id = :rzp_pay,
                    razorpay_signature = :rzp_sig,
                    payment_gateway = :gateway,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $upStmt->execute([
                ':enr'       => $enrollmentNumber,
                ':rzp_order' => $gatewayOrderId,
                ':rzp_pay'   => $gatewayPaymentId,
                ':rzp_sig'   => $gatewaySignature,
                ':gateway'   => $gatewayName,
                ':id'        => $orderId
            ]);

            // 2. Create payment record
            $payStmt = $db->prepare("
                INSERT INTO payments (
                    payment_number, order_id, user_id, amount, currency, gateway,
                    gateway_order_id, gateway_payment_id, gateway_signature, status, created_at
                ) VALUES (
                    :pay_num, :order_id, :user_id, :amount, 'INR', :gateway,
                    :gw_order, :gw_pay, :gw_sig, 'Success', NOW()
                )
            ");
            $payStmt->execute([
                ':pay_num'  => $paymentNumber,
                ':order_id' => $orderId,
                ':user_id'  => $order['user_id'],
                ':amount'   => $order['final_amount'],
                ':gateway'  => $gatewayName,
                ':gw_order' => $gatewayOrderId,
                ':gw_pay'   => $gatewayPaymentId,
                ':gw_sig'   => $gatewaySignature
            ]);

            // 3. Create enrollment record
            $enrStmt = $db->prepare("
                INSERT INTO enrollments (
                    enrollment_number, order_id, user_id, course_id, status, enrolled_at
                ) VALUES (
                    :enr_num, :order_id, :user_id, :course_id, 'Active', NOW()
                )
            ");
            $enrStmt->execute([
                ':enr_num'   => $enrollmentNumber,
                ':order_id'  => $orderId,
                ':user_id'   => $order['user_id'],
                ':course_id' => $order['course_id']
            ]);

            $db->commit();

            return [
                'success'           => true,
                'order_id'          => $orderId,
                'order_number'      => $order['order_number'],
                'enrollment_number' => $enrollmentNumber,
                'payment_id'        => $paymentNumber,
                'course_title'      => $order['course_title'],
                'amount'            => $order['final_amount'],
                'date'              => date('Y-m-d')
            ];

        } catch (Exception $e) {
            $db->rollBack();
            error_log('Complete payment transaction error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Payment confirmation database error: ' . $e->getMessage()];
        }
    }

    public static function getMyOrders(int $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                o.id, o.order_number, o.enrollment_number, o.course_id, o.course_title, 
                o.final_amount as amount, o.payment_status, o.razorpay_payment_id as payment_id,
                o.created_at as date, c.course_code, c.duration
            FROM orders o
            LEFT JOIN courses c ON o.course_id = c.id
            WHERE o.user_id = :uid
            ORDER BY o.id DESC
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public static function getReceiptByNumber(string $orderNumber, ?int $userId = null, bool $isAdmin = false): ?array {
        $db = Database::getConnection();
        $sql = "
            SELECT 
                o.*, p.payment_number, p.gateway as payment_gateway_name, p.status as payment_record_status
            FROM orders o
            LEFT JOIN payments p ON o.id = p.order_id
            WHERE (o.order_number = :num OR o.enrollment_number = :num OR o.id = :id_num)
        ";

        if (!$isAdmin && $userId !== null) {
            $sql .= " AND o.user_id = :uid";
        }
        $sql .= " LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':num', $orderNumber);
        $stmt->bindValue(':id_num', is_numeric($orderNumber) ? (int)$orderNumber : 0, PDO::PARAM_INT);
        if (!$isAdmin && $userId !== null) {
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $order = $stmt->fetch();
        return $order ?: null;
    }

    public static function getAll(string $search = '', string $status = 'all', int $limit = 50, int $offset = 0): array {
        $db = Database::getConnection();
        $sql = "
            SELECT 
                o.id, o.order_number as orderId, o.enrollment_number as enrollmentId,
                o.customer_name as customerName, o.mobile, o.email, o.course_title as courseTitle,
                o.final_amount as amount, o.razorpay_payment_id as paymentId, o.payment_gateway as paymentGateway,
                o.payment_status as paymentStatus, DATE_FORMAT(o.created_at, '%Y-%m-%d') as date
            FROM orders o
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (o.order_number LIKE :s1 OR o.customer_name LIKE :s2 OR o.course_title LIKE :s3 OR o.mobile LIKE :s4)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
            $params[':s4'] = "%{$search}%";
        }

        if ($status !== 'all' && !empty($status)) {
            $sql .= " AND o.payment_status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY o.id DESC LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }
}
