<?php
/**
 * GET /api/courses/detail.php
 * Fetch Single Course Details by ID, Code or Slug (Enforcing Active Status for Guests)
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/Course.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$idOrSlug = $_GET['id'] ?? $_GET['slug'] ?? $_GET['code'] ?? null;

if (!$idOrSlug) {
    Response::error('Course identifier is required.', [], 400);
}

$course = Course::getByIdOrCode($idOrSlug);

if (!$course) {
    Response::notFound('Course not found.');
}

if ($course['status'] !== 'active' && !Auth::isAdmin()) {
    Response::notFound('Course is currently inactive or not accepting enrollments.');
}

Response::success($course, 'Course details fetched successfully');
