<?php
/**
 * GET /api/service-requests/detail.php
 * Fetch Single Service Request Details
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/ServiceRequest.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$requestIdOrNum = $_GET['id'] ?? $_GET['request_id'] ?? $_GET['number'] ?? null;
if (!$requestIdOrNum) {
    Response::error('Request ID is required.', [], 400);
}

$isAdmin = Auth::isAdmin();
$user = Auth::isLoggedIn() ? Auth::currentUser() : null;
$userId = $user ? (int)$user['id'] : null;

if (!$isAdmin && !$userId) {
    Response::unauthorized('Authentication required to view service request.');
}

$request = ServiceRequest::getByIdOrNumber($requestIdOrNum, $userId, $isAdmin);
if (!$request) {
    Response::notFound('Service request not found or access denied.');
}

Response::success($request, 'Service request details fetched successfully');
