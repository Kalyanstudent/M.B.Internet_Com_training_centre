<?php
/**
 * GET /api/admin/orders.php
 * Admin Course Orders Directory with Filtering & Search
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

Auth::requireAdmin();

$search = trim($_GET['search'] ?? $_GET['q'] ?? '');
$status = trim($_GET['status'] ?? 'all');
$limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
$offset = max(0, (int)($_GET['offset'] ?? 0));

$orders = Order::getAll($search, $status, $limit, $offset);
Response::success($orders, 'Orders retrieved successfully');
