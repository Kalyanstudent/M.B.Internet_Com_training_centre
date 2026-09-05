<?php
/**
 * POST /api/orders/create.php
 * Create Course Checkout Order (Strict Server-Side Price Verification)
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/includes/Validator.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

// Strict Role Check: Must be a student user
$user = Auth::requireUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', [], 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$courseId = $data['course_id'] ?? $data['courseId'] ?? null;
if (!$courseId) {
    Response::error('Course ID is required.', ['course_id' => 'Required'], 422);
}

$customerDetails = [
    'name'    => $data['name'] ?? $data['customerName'] ?? $user['name'],
    'mobile'  => $data['mobile'] ?? $user['mobile'],
    'email'   => $data['email'] ?? $user['email'],
    'address' => $data['address'] ?? $user['address'] ?? ''
];

$couponCode = $data['coupon_code'] ?? $data['coupon'] ?? null;

$result = Order::createOrder((int)$user['id'], (int)$courseId, $customerDetails, $couponCode);

if ($result['success']) {
    Response::success($result, 'Order created successfully. Ready for payment.');
} else {
    Response::error($result['message'], [], 400);
}
