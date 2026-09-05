<?php
/**
 * MB Internet And Digital Studio | Secure File Upload Manager
 */

require_once dirname(__DIR__) . '/config/db.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/auth.php';

class FileUploader {
    public const MAX_SIZE_BYTES = 5242880; // 5 MB
    public const ALLOWED_EXTS = ['pdf', 'jpg', 'jpeg', 'png', 'xlsx', 'xls'];
    public const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'application/octet-stream'
    ];

    public static function uploadDocument(array $fileArray, int $userId, ?int $requestId = null): array {
        global $conn;

        $targetDir = dirname(__DIR__) . '/storage/documents';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0750, true);
        }

        // 1. Check upload error
        if ($fileArray['error'] !== UPLOAD_ERR_OK) {
            $msg = match ($fileArray['error']) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Uploaded file exceeds the maximum allowed limit of 5 MB.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded. Please try again.',
                UPLOAD_ERR_NO_FILE => 'No file was selected.',
                default => 'File upload error (Code: ' . $fileArray['error'] . ')'
            };
            return ['success' => false, 'message' => $msg];
        }

        // 2. Check file size
        $fileSize = (int)$fileArray['size'];
        if ($fileSize > self::MAX_SIZE_BYTES || $fileSize <= 0) {
            return ['success' => false, 'message' => 'File size exceeds maximum allowed limit of 5 MB.'];
        }

        // 3. Check extension
        $origName = basename($fileArray['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTS, true)) {
            return ['success' => false, 'message' => "Invalid file extension (.{$ext}). Allowed formats: PDF, JPG, PNG, Excel."];
        }

        // 4. Server-side MIME verification
        $tmpPath = $fileArray['tmp_name'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($tmpPath);

        if (!in_array($detectedMime, self::ALLOWED_MIMES, true)) {
            // Block dangerous executables masquerading as documents
            return ['success' => false, 'message' => 'Security error: File content signature does not match an allowed document format.'];
        }

        // 5. Generate safe unique cryptographically random filename
        $storedName = 'doc_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $destination = $targetDir . DIRECTORY_SEPARATOR . $storedName;

        if (!move_uploaded_file($tmpPath, $destination)) {
            return ['success' => false, 'message' => 'Failed to save uploaded file to secure storage.'];
        }

        // 6. Format size
        $formattedSize = ($fileSize >= 1048576) 
            ? round($fileSize / 1048576, 2) . ' MB' 
            : round($fileSize / 1024, 1) . ' KB';

        // 7. Store record in database with PDO
        $stmt = $conn->prepare("
            INSERT INTO documents 
            (request_id, user_id, original_name, stored_name, file_type, file_size_bytes, file_size_formatted, file_path, upload_ip, created_at)
            VALUES 
            (:request_id, :user_id, :original_name, :stored_name, :file_type, :file_size, :file_size_formatted, :file_path, :upload_ip, NOW())
        ");

        $stmt->execute([
            ':request_id'          => $requestId,
            ':user_id'             => $userId,
            ':original_name'       => $origName,
            ':stored_name'         => $storedName,
            ':file_type'           => $detectedMime,
            ':file_size'           => $fileSize,
            ':file_size_formatted' => $formattedSize,
            ':file_path'           => 'storage/documents/' . $storedName,
            ':upload_ip'           => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);

        $docId = (int)$conn->lastInsertId();

        security_log('DOCUMENT_UPLOADED', [
            'doc_id'   => $docId,
            'orig_name'=> $origName,
            'size'     => $formattedSize
        ], $userId);

        return [
            'success'  => true,
            'document' => [
                'id'          => $docId,
                'name'        => $origName,
                'stored_name' => $storedName,
                'size'        => $formattedSize,
                'type'        => $detectedMime
            ]
        ];
    }
}
