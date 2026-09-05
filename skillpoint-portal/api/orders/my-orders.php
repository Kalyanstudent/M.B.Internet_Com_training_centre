<?php
/**
 * GET /api/orders/my-orders.php
 * Fetch Enrolled Courses for Authenticated Student User
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$user = Auth::requireUser();
$orders = Order::getMyOrders((int)$user['id']);

Response::success($orders, 'Enrolled courses retrieved successfully');
