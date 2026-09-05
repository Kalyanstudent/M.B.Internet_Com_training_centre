<?php
/**
 * GET /api/products/list.php
 * Public Mobile & Gift House Product Catalog
 */

require_once dirname(__DIR__, 2) . '/models/Product.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$category = $_GET['category'] ?? 'all';
$products = Product::getAll($category, 'active');
Response::success($products, 'Products fetched successfully');
