<?php
/**
 * POST /api/payments/verify.php
 * Server-Side Razorpay Signature Verification & Enrollment Finalization
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/includes/RazorpayHandler.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$user = Auth::requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', [], 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$orderId = $data['order_id'] ?? $data['orderId'] ?? null;
$razorpayOrderId = $data['razorpay_order_id'] ?? $data['razorpayOrderId'] ?? '';
$razorpayPaymentId = $data['razorpay_payment_id'] ?? $data['razorpayPaymentId'] ?? '';
$razorpaySignature = $data['razorpay_signature'] ?? $data['razorpaySignature'] ?? '';
$gatewayName = $data['gateway_name'] ?? 'Razorpay / UPI';

if (!$orderId) {
    Response::error('Order ID is required.', [], 422);
}

if (empty($razorpayPaymentId)) {
    Response::error('Razorpay Payment ID is required.', [], 422);
}

// 1. Fetch internal order
$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM orders WHERE (id = :id OR order_number = :num) AND user_id = :uid LIMIT 1");
$stmt->bindValue(':id', is_numeric($orderId) ? (int)$orderId : 0, PDO::PARAM_INT);
$stmt->bindValue(':num', (string)$orderId);
$stmt->bindValue(':uid', (int)$user['id'], PDO::PARAM_INT);
$stmt->execute();
$order = $stmt->fetch();

if (!$order) {
    Response::notFound('Order not found.');
}

// 2. Perform server-side HMAC-SHA256 signature verification
$isVerified = RazorpayHandler::verifySignature(
    $razorpayOrderId ?: ($order['razorpay_order_id'] ?? ''),
    $razorpayPaymentId,
    $razorpaySignature
);

if (!$isVerified) {
    Response::error('Payment signature verification failed. Forged or invalid transaction.', [], 400);
}

// 3. Mark payment as completed, create enrollment inside a DB transaction
$result = Order::completePayment(
    (int)$order['id'],
    $razorpayPaymentId,
    $razorpayOrderId ?: ($order['razorpay_order_id'] ?? ''),
    $razorpaySignature,
    $gatewayName
);

if ($result['success']) {
    Response::success($result, 'Payment verified and enrollment confirmed!');
} else {
    Response::error($result['message'], [], 500);
}
