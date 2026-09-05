<?php
/**
 * /api/admin/settings.php
 * Admin Institute Settings Management
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/Setting.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

Auth::requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $settings = Setting::getAll();
    Response::success($settings, 'Settings fetched successfully');
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if ($method === 'POST' || $method === 'PUT') {
    Setting::saveAll($data);
    $updated = Setting::getAll();
    Response::success($updated, 'Institute settings updated successfully.');
}
