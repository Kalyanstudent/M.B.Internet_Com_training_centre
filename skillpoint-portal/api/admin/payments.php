<?php
/**
 * GET /api/admin/payments.php
 * Admin Payments Log & Audit Trail
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/Payment.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

Auth::requireAdmin();

$search = trim($_GET['search'] ?? $_GET['q'] ?? '');
$status = trim($_GET['status'] ?? 'all');
$limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
$offset = max(0, (int)($_GET['offset'] ?? 0));

$payments = Payment::getAll($search, $status, $limit, $offset);
Response::success($payments, 'Payment records retrieved successfully');
