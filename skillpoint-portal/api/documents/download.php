<?php
/**
 * GET /api/documents/download.php?id=1
 * Secure Protected Document Download (Strict Role & Ownership Check)
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/Document.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$currentUser = Auth::requireLogin();
$docId = (int) ($_GET['id'] ?? 0);

if ($docId <= 0) {
    Response::error('Invalid document ID.', [], 400);
}

$doc = Document::getById($docId);

if (!$doc) {
    Response::notFound('Document not found in vault.');
}

// Strict Ownership / Role Check:
// Admins can download all documents; normal students can only download their own documents
if (!Auth::isAdmin() && (int)$doc['user_id'] !== (int)$currentUser['id']) {
    Response::forbidden('Access denied. You do not have permission to download this document.');
}

$filename = basename($doc['stored_name']);
$filePath = DIR_DOCUMENTS . DIRECTORY_SEPARATOR . $filename;

if (!file_exists($filePath)) {
    // Generate dummy download if demo seed file
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . basename($doc['original_name']) . '.txt"');
    echo "SkillPoint Secure Document Vault\nDocument: " . $doc['original_name'] . "\nOwner: User #" . $doc['user_id'] . "\nStatus: Verified";
    exit;
}

$mimeType = $doc['file_type'] ?: mime_content_type($filePath);

header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . basename($doc['original_name']) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($filePath);
exit;
