<?php
/**
 * /api/admin/courses.php
 * Admin Course CRUD & Status Management
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/Course.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

Auth::requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $category = $_GET['category'] ?? 'all';
    $search = trim($_GET['search'] ?? '');
    $courses = Course::getAll($category, $search, 'popular', 'all');
    Response::success($courses, 'Courses fetched successfully');
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if ($method === 'POST') {
    $action = $data['action'] ?? 'create';

    if ($action === 'toggle') {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) Response::error('Invalid course ID', [], 400);
        $newStatus = Course::toggleStatus($id);
        Response::success(['status' => $newStatus], "Course status updated to {$newStatus}.");
    }

    if ($action === 'delete') {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) Response::error('Invalid course ID', [], 400);
        Course::delete($id);
        Response::success(null, 'Course deleted successfully.');
    }

    // Create Course
    if (empty($data['title']) || empty($data['price']) || empty($data['duration'])) {
        Response::error('Title, price, and duration are required fields.', [], 422);
    }

    if (!empty($data['id']) && is_numeric($data['id'])) {
        // Update
        $id = (int) $data['id'];
        Course::update($id, $data);
        $updated = Course::getByIdOrCode($id);
        Response::success($updated, 'Course updated successfully.');
    } else {
        // Create
        $newId = Course::create($data);
        $created = Course::getByIdOrCode($newId);
        Response::success($created, 'Course created successfully.', 201);
    }
}

if ($method === 'PUT') {
    $id = (int) ($data['id'] ?? 0);
    if ($id <= 0) Response::error('Course ID is required for update.', [], 400);
    Course::update($id, $data);
    $updated = Course::getByIdOrCode($id);
    Response::success($updated, 'Course updated successfully.');
}

if ($method === 'DELETE') {
    $id = (int) ($_GET['id'] ?? $data['id'] ?? 0);
    if ($id <= 0) Response::error('Invalid course ID', [], 400);
    Course::delete($id);
    Response::success(null, 'Course deleted successfully.');
}
