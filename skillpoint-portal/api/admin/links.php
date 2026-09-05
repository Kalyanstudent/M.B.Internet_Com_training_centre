<?php
/**
 * /api/admin/links.php
 * Admin Important Links Management CRUD
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/ImportantLink.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

Auth::requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $links = ImportantLink::getAll('all');
    Response::success($links, 'Important links fetched successfully');
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if ($method === 'POST') {
    $action = $data['action'] ?? 'create';

    if ($action === 'toggle') {
        $id = $data['id'] ?? $data['link_code'] ?? 0;
        $newStatus = ImportantLink::toggleStatus($id);
        Response::success(['status' => $newStatus], "Link status updated to {$newStatus}.");
    }

    if ($action === 'delete') {
        $id = $data['id'] ?? $data['link_code'] ?? 0;
        ImportantLink::delete($id);
        Response::success(null, 'Important link deleted successfully.');
    }

    if (empty($data['title']) || empty($data['url'])) {
        Response::error('Title and URL are required.', [], 422);
    }

    if (!empty($data['id']) || !empty($data['link_code'])) {
        $id = $data['id'] ?? $data['link_code'];
        ImportantLink::update($id, $data);
        $updated = ImportantLink::getById($id);
        Response::success($updated, 'Important link updated successfully.');
    } else {
        $newId = ImportantLink::create($data);
        $created = ImportantLink::getById($newId);
        Response::success($created, 'Important link created successfully.', 201);
    }
}

if ($method === 'PUT') {
    $id = $data['id'] ?? $data['link_code'] ?? 0;
    if (!$id) Response::error('Link identifier is required for update.', [], 400);
    ImportantLink::update($id, $data);
    $updated = ImportantLink::getById($id);
    Response::success($updated, 'Important link updated successfully.');
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? $data['id'] ?? $data['link_code'] ?? 0;
    if (!$id) Response::error('Link identifier is required.', [], 400);
    ImportantLink::delete($id);
    Response::success(null, 'Important link deleted successfully.');
}
