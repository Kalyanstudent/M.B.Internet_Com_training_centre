<?php
/**
 * GET /api/user/dashboard.php
 * Real Student Dashboard Aggregated Metrics (Strictly Restricted to Student Users)
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/User.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

// Strict Role Check: Must be authenticated as a normal student/user (Rejects Admin with 403)
$user = Auth::requireUser();

$stats = User::getUserOverviewStats((int)$user['id']);

Response::success([
    'user'  => $user,
    'stats' => $stats
], 'User dashboard data retrieved successfully');
