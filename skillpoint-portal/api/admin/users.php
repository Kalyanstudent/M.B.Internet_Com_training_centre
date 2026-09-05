<?php
/**
 * /api/admin/users.php
 * Admin Student Accounts Directory & Status Management
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/User.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';
require_once dirname(__DIR__, 2) . '/models/ServiceRequest.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

Auth::requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $userId = $_GET['id'] ?? null;

    if ($userId) {
        $user = User::getById((int)$userId);
        if (!$user) Response::notFound('User not found.');

        $orders = Order::getMyOrders((int)$userId);
        $taxRequests = ServiceRequest::getMyRequests((int)$userId);

        Response::success([
            'user'         => $user,
            'orders'       => $orders,
            'tax_requests' => $taxRequests
        ], 'User details retrieved successfully');
    }

    $search = trim($_GET['search'] ?? $_GET['q'] ?? '');
    $status = trim($_GET['status'] ?? 'all');
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
    $offset = max(0, (int)($_GET['offset'] ?? 0));

    $users = User::getAll($search, $status, $limit, $offset);

    // Compute enrolled counts for list table
    $db = Database::getConnection();
    foreach ($users as &$u) {
        $u['orders_count'] = (int) $db->query("SELECT COUNT(*) FROM orders WHERE user_id = " . (int)$u['id'])->fetchColumn();
        $u['tax_count'] = (int) $db->query("SELECT COUNT(*) FROM service_requests WHERE user_id = " . (int)$u['id'])->fetchColumn();
    }

    Response::success($users, 'Users list retrieved successfully');
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if ($method === 'POST') {
    $action = $data['action'] ?? '';
    $userId = (int) ($data['id'] ?? $data['user_id'] ?? 0);

    if ($userId <= 0) {
        Response::error('User ID is required.', [], 400);
    }

    if ($action === 'toggle') {
        $currentUser = User::getById($userId);
        if (!$currentUser) Response::notFound('User not found.');

        $newStatus = ($currentUser['status'] === STATUS_ACTIVE) ? STATUS_INACTIVE : STATUS_ACTIVE;
        User::setStatus($userId, $newStatus);

        Response::success(['status' => $newStatus], "User status updated to {$newStatus}.");
    }

    if ($action === 'status') {
        $status = $data['status'] ?? STATUS_ACTIVE;
        User::setStatus($userId, $status);
        Response::success(['status' => $status], "User status set to {$status}.");
    }
}
