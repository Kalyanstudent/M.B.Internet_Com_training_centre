<?php
/**
 * GET /api/courses/list.php
 * Public Course Catalog with Filter, Search & Sorting
 */

require_once dirname(__DIR__, 2) . '/models/Course.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$category = $_GET['category'] ?? 'all';
$search = trim($_GET['search'] ?? $_GET['q'] ?? '');
$sortBy = $_GET['sort'] ?? 'popular';

$courses = Course::getAll($category, $search, $sortBy, 'active');
Response::success($courses, 'Courses fetched successfully');
