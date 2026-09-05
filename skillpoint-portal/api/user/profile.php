<?php
/**
 * GET /api/user/profile.php
 * Retrieve Authenticated User Profile
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$user = Auth::requireLogin();
Response::success($user, 'User profile retrieved successfully');
