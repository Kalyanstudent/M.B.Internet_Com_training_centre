<?php
/**
 * POST /api/documents/upload.php
 * Secure Document Upload with MIME Inspection
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/includes/FileUploader.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$user = Auth::requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', [], 405);
}

if (empty($_FILES['file']) && empty($_FILES['document'])) {
    Response::error('No file was received for upload.', [], 400);
}

$fileData = $_FILES['file'] ?? $_FILES['document'];
$requestId = !empty($_POST['request_id']) ? (int)$_POST['request_id'] : null;

$uploadResult = FileUploader::uploadDocument($fileData, (int)$user['id'], $requestId);

if ($uploadResult['success']) {
    Response::success($uploadResult['document'], 'Document uploaded successfully.', 201);
} else {
    Response::error($uploadResult['message'], [], 400);
}
