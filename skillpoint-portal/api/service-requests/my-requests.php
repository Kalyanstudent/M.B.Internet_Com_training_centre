<?php
/**
 * GET /api/service-requests/my-requests.php
 * Fetch Authenticated User's Tax Requests
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/ServiceRequest.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$user = Auth::requireAuth();
$requests = ServiceRequest::getMyRequests((int)$user['id']);

Response::success($requests, 'Tax service requests retrieved successfully');
