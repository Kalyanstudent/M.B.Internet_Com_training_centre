<?php
/**
 * POST /api/payments/create-razorpay-order.php
 * Create Server-Side Razorpay Order for Student User
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/includes/RazorpayHandler.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$user = Auth::requireUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', [], 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$orderId = $data['order_id'] ?? $data['orderId'] ?? null;
if (!$orderId) {
    Response::error('Internal order ID is required.', ['order_id' => 'Required'], 422);
}

// Fetch order from database ensuring it belongs to current authenticated user
$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM orders WHERE id = :id AND user_id = :uid LIMIT 1");
$stmt->execute([
    ':id'  => (int) $orderId,
    ':uid' => (int) $user['id']
]);
$order = $stmt->fetch();

if (!$order) {
    Response::notFound('Order not found or does not belong to your account.');
}

if ($order['payment_status'] === PAYMENT_STATUS_PAID) {
    Response::error('This order is already paid and completed.', [], 400);
}

$finalAmountRupees = (int) ceil((float)$order['final_amount']);
$rzpResult = RazorpayHandler::createOrder($finalAmountRupees, $order['order_number'], [
    'user_id'      => (string) $user['id'],
    'order_number' => $order['order_number'],
    'course_title' => $order['course_title']
]);

if ($rzpResult['success']) {
    $upStmt = $db->prepare("UPDATE orders SET razorpay_order_id = :rzp_id WHERE id = :id");
    $upStmt->execute([
        ':rzp_id' => $rzpResult['order_id'],
        ':id'     => $order['id']
    ]);

    Response::success([
        'razorpay_order_id' => $rzpResult['order_id'],
        'amount'            => $rzpResult['amount'],
        'currency'          => $rzpResult['currency'],
        'key_id'            => $rzpResult['key_id'],
        'order_number'      => $order['order_number'],
        'customer_name'     => $order['customer_name'],
        'customer_email'    => $order['email'],
        'customer_mobile'   => $order['mobile'],
        'course_title'      => $order['course_title']
    ], 'Razorpay order created successfully.');
} else {
    Response::error('Failed to create Razorpay order on server.', [], 500);
}
