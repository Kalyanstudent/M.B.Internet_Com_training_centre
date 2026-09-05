<?php
/**
 * SkillPoint Document Vault Model
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';

class Document {
    public static function getById(int $docId): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT d.*, r.request_number, r.customer_name, r.user_id as request_user_id
            FROM documents d
            LEFT JOIN service_requests r ON d.request_id = r.id
            WHERE d.id = :id 
            LIMIT 1
        ");
        $stmt->execute([':id' => $docId]);
        $doc = $stmt->fetch();
        return $doc ?: null;
    }

    public static function getByRequestId(int $requestId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT id, original_name as name, stored_name, file_type as type, 
                   file_size_formatted as size, file_size as size_bytes, created_at as uploadDate
            FROM documents 
            WHERE request_id = :rid 
            ORDER BY id ASC
        ");
        $stmt->execute([':rid' => $requestId]);
        return $stmt->fetchAll();
    }

    public static function getAllVault(int $limit = 50, int $offset = 0): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                d.id, d.original_name as docName, d.stored_name, d.file_type, 
                d.file_size_formatted as size, DATE_FORMAT(d.created_at, '%Y-%m-%d') as uploadDate,
                COALESCE(r.request_number, 'UNASSIGNED') as requestId,
                COALESCE(r.customer_name, u.name) as customerName,
                COALESCE(r.status, 'Uploaded') as status
            FROM documents d
            LEFT JOIN service_requests r ON d.request_id = r.id
            LEFT JOIN users u ON d.user_id = u.id
            ORDER BY d.id DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
