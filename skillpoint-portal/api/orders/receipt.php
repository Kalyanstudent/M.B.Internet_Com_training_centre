<?php
/**
 * GET /api/orders/receipt.php
 * Retrieve Official Payment Receipt Data
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';
require_once dirname(__DIR__, 2) . '/models/Setting.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$orderNumber = $_GET['order_id'] ?? $_GET['id'] ?? $_GET['number'] ?? null;
if (!$orderNumber) {
    Response::error('Order identifier is required.', [], 400);
}

$isAdmin = Auth::isAdmin();
$user = Auth::isLoggedIn() ? Auth::currentUser() : null;
$userId = $user ? (int)$user['id'] : null;

$receipt = Order::getReceiptByNumber($orderNumber, $userId, $isAdmin);
if (!$receipt) {
    Response::notFound('Receipt not found or you do not have permission to view it.');
}

$settings = Setting::getAll();

Response::success([
    'receipt'   => $receipt,
    'institute' => $settings
], 'Receipt retrieved successfully');
