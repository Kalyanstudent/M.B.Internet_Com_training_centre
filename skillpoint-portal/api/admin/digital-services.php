<?php
/**
 * /api/admin/digital-services.php
 * Admin Digital Services CRUD (Auth Guard: requireAdmin)
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/DigitalService.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

Auth::requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $services = DigitalService::getAll('all');
    Response::success($services, 'Digital services fetched successfully');
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if ($method === 'POST') {
    $action = $data['action'] ?? 'create';

    if ($action === 'toggle') {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) Response::error('Invalid service ID', [], 400);
        $newStatus = DigitalService::toggleStatus($id);
        Response::success(['status' => $newStatus], "Service status updated to {$newStatus}.");
    }

    if ($action === 'delete') {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) Response::error('Invalid service ID', [], 400);
        DigitalService::delete($id);
        Response::success(null, 'Service deleted successfully.');
    }

    if (empty($data['title']) || empty($data['description'])) {
        Response::error('Title and description are required.', [], 422);
    }

    if (!empty($data['id']) && is_numeric($data['id'])) {
        $id = (int)$data['id'];
        DigitalService::update($id, $data);
        $updated = DigitalService::getById($id);
        Response::success($updated, 'Digital service updated successfully.');
    } else {
        $newId = DigitalService::create($data);
        $created = DigitalService::getById($newId);
        Response::success($created, 'Digital service created successfully.', 201);
    }
}

if ($method === 'DELETE') {
    $id = (int) ($_GET['id'] ?? $data['id'] ?? 0);
    if ($id <= 0) Response::error('Invalid service ID', [], 400);
    DigitalService::delete($id);
    Response::success(null, 'Digital service deleted successfully.');
}
