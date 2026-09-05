<?php
/**
 * /api/admin/products.php
 * Admin Mobile & Gift House Catalog CRUD (Auth Guard: requireAdmin)
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/Product.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

Auth::requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $category = $_GET['category'] ?? 'all';
    $products = Product::getAll($category, 'all');
    Response::success($products, 'Products fetched successfully');
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if ($method === 'POST') {
    $action = $data['action'] ?? 'create';

    if ($action === 'toggle') {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) Response::error('Invalid product ID', [], 400);
        $newStatus = Product::toggleStatus($id);
        Response::success(['status' => $newStatus], "Product status updated to {$newStatus}.");
    }

    if ($action === 'delete') {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) Response::error('Invalid product ID', [], 400);
        Product::delete($id);
        Response::success(null, 'Product deleted successfully.');
    }

    if (empty($data['name']) || empty($data['category'])) {
        Response::error('Product name and category are required.', [], 422);
    }

    if (!empty($data['id']) && is_numeric($data['id'])) {
        $id = (int)$data['id'];
        Product::update($id, $data);
        $updated = Product::getById($id);
        Response::success($updated, 'Product updated successfully.');
    } else {
        $newId = Product::create($data);
        $created = Product::getById($newId);
        Response::success($created, 'Product created successfully.', 201);
    }
}

if ($method === 'DELETE') {
    $id = (int) ($_GET['id'] ?? $data['id'] ?? 0);
    if ($id <= 0) Response::error('Invalid product ID', [], 400);
    Product::delete($id);
    Response::success(null, 'Product deleted successfully.');
}
