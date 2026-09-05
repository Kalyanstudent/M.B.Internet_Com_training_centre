<?php
/**
 * /api/admin/service-requests.php
 * Admin Tax Requests Workflow Management & Internal Notes
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/ServiceRequest.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

Auth::requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $search = trim($_GET['search'] ?? $_GET['q'] ?? '');
    $status = trim($_GET['status'] ?? 'all');
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
    $offset = max(0, (int)($_GET['offset'] ?? 0));

    $requests = ServiceRequest::getAll($search, $status, $limit, $offset);
    Response::success($requests, 'Tax service requests retrieved successfully');
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if ($method === 'POST' || $method === 'PUT') {
    $requestId = $data['request_id'] ?? $data['requestId'] ?? $data['id'] ?? null;
    $newStatus = trim($data['status'] ?? '');
    $internalNotes = $data['internal_notes'] ?? $data['internalNotes'] ?? null;

    if (!$requestId || empty($newStatus)) {
        Response::error('Request ID and new status are required.', [], 422);
    }

    $validStatuses = [TAX_STATUS_PENDING, TAX_STATUS_PROCESSING, TAX_STATUS_NEED_INFO, TAX_STATUS_COMPLETED, TAX_STATUS_REJECTED];
    if (!in_array($newStatus, $validStatuses, true)) {
        Response::error('Invalid status value provided.', [], 422);
    }

    $updated = ServiceRequest::updateStatusAndNotes($requestId, $newStatus, $internalNotes);
    if ($updated) {
        $req = ServiceRequest::getByIdOrNumber($requestId, null, true);
        Response::success($req, "Service request {$requestId} updated to {$newStatus}.");
    } else {
        Response::error('Failed to update service request.', [], 500);
    }
}
