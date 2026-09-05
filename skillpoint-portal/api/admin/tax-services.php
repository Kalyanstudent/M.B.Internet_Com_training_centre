<?php
/**
 * /api/admin/tax-services.php
 * Admin Tax & Digital Services Catalog CRUD
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/TaxService.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

Auth::requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $services = TaxService::getAll('all');
    Response::success($services, 'Tax services fetched successfully');
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if ($method === 'POST') {
    $action = $data['action'] ?? 'create';

    if ($action === 'toggle') {
        $id = (int) ($data['id'] ?? 0);
        $newStatus = TaxService::toggleStatus($id);
        Response::success(['status' => $newStatus], "Tax service status updated to {$newStatus}.");
    }

    if ($action === 'delete') {
        $id = (int) ($data['id'] ?? 0);
        TaxService::delete($id);
        Response::success(null, 'Tax service deleted successfully.');
    }

    if (empty($data['title']) || empty($data['short_desc'])) {
        Response::error('Title and short description are required.', [], 422);
    }

    if (!empty($data['id']) && is_numeric($data['id'])) {
        $id = (int)$data['id'];
        TaxService::update($id, $data);
        $updated = TaxService::getByIdOrSlug($id);
        Response::success($updated, 'Tax service updated successfully.');
    } else {
        $newId = TaxService::create($data);
        $created = TaxService::getByIdOrSlug($newId);
        Response::success($created, 'Tax service created successfully.', 201);
    }
}

if ($method === 'PUT') {
    $id = (int) ($data['id'] ?? 0);
    if ($id <= 0) Response::error('Service ID is required for update.', [], 400);
    TaxService::update($id, $data);
    $updated = TaxService::getByIdOrSlug($id);
    Response::success($updated, 'Tax service updated successfully.');
}

if ($method === 'DELETE') {
    $id = (int) ($_GET['id'] ?? $data['id'] ?? 0);
    if ($id <= 0) Response::error('Service ID is required.', [], 400);
    TaxService::delete($id);
    Response::success(null, 'Tax service deleted successfully.');
}
