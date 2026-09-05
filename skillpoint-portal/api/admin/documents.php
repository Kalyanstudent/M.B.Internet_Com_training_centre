<?php
/**
 * GET /api/admin/documents.php
 * Admin Document Vault Listing
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/Document.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

Auth::requireAdmin();

$limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
$offset = max(0, (int)($_GET['offset'] ?? 0));

$docs = Document::getAllVault($limit, $offset);
Response::success($docs, 'Vault documents retrieved successfully');
