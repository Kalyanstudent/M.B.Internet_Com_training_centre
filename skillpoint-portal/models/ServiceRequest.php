<?php
/**
 * SkillPoint Service Request Model (Tax & Digital Compliance)
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once __DIR__ . '/Document.php';

class ServiceRequest {
    public static function create(int $userId, array $data, array $documentIds = []): array {
        $db = Database::getConnection();

        try {
            $db->beginTransaction();

            $requestNumber = 'REQ-' . date('Y') . '-' . str_pad((string)mt_rand(100, 99999), 5, '0', STR_PAD_LEFT);

            $stmt = $db->prepare("
                INSERT INTO service_requests (
                    request_number, user_id, service_id, service_type, customer_name,
                    mobile, email, address, pan_number, financial_year, assessment_year,
                    status, internal_notes, created_at
                ) VALUES (
                    :req_num, :uid, :sid, :stype, :cname,
                    :mobile, :email, :addr, :pan, :fy, :ay,
                    'Pending', :notes, NOW()
                )
            ");

            $stmt->execute([
                ':req_num' => $requestNumber,
                ':uid'     => $userId,
                ':sid'     => $data['service_id'] ?? null,
                ':stype'   => $data['service_type'],
                ':cname'   => $data['customer_name'],
                ':mobile'  => $data['mobile'],
                ':email'   => $data['email'],
                ':addr'    => $data['address'] ?? '',
                ':pan'     => $data['pan_number'] ?? null,
                ':fy'      => $data['financial_year'] ?? '2025-26',
                ':ay'      => $data['assessment_year'] ?? '2026-27',
                ':notes'   => !empty($data['notes']) ? "Client Note: " . trim($data['notes']) : null
            ]);

            $requestId = (int) $db->lastInsertId();

            // Associate uploaded documents to this request
            if (!empty($documentIds)) {
                $docPlaceholders = implode(',', array_fill(0, count($documentIds), '?'));
                $linkStmt = $db->prepare("
                    UPDATE documents 
                    SET request_id = ? 
                    WHERE id IN ($docPlaceholders) AND user_id = ?
                ");
                $params = array_merge([$requestId], $documentIds, [$userId]);
                $linkStmt->execute($params);
            }

            $db->commit();

            return [
                'success'        => true,
                'request_id'     => $requestId,
                'request_number' => $requestNumber,
                'service_type'   => $data['service_type'],
                'customer_name'  => $data['customer_name'],
                'date'           => date('Y-m-d')
            ];

        } catch (Exception $e) {
            $db->rollBack();
            error_log('Create service request error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to save tax request: ' . $e->getMessage()];
        }
    }

    public static function getMyRequests(int $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                r.id, r.request_number as requestId, r.service_type as serviceType,
                r.customer_name as customerName, r.mobile, r.email, r.pan_number as panNumber,
                r.financial_year as financialYear, r.assessment_year as assessmentYear,
                r.status, DATE_FORMAT(r.created_at, '%Y-%m-%d') as date
            FROM service_requests r
            WHERE r.user_id = :uid
            ORDER BY r.id DESC
        ");
        $stmt->execute([':uid' => $userId]);
        $requests = $stmt->fetchAll();

        // Attach documents metadata to each request
        foreach ($requests as &$req) {
            $req['documents'] = Document::getByRequestId((int)$req['id']);
        }

        return $requests;
    }

    public static function getByIdOrNumber(string|int $idOrNumber, ?int $userId = null, bool $isAdmin = false): ?array {
        $db = Database::getConnection();
        $sql = "SELECT * FROM service_requests WHERE (id = :id_num OR request_number = :req_num)";
        if (!$isAdmin && $userId !== null) {
            $sql .= " AND user_id = :uid";
        }
        $sql .= " LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id_num', is_numeric($idOrNumber) ? (int)$idOrNumber : 0, PDO::PARAM_INT);
        $stmt->bindValue(':req_num', $idOrNumber);
        if (!$isAdmin && $userId !== null) {
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $row = $stmt->fetch();
        if (!$row) return null;

        // Hide internal notes from normal student users
        if (!$isAdmin) {
            unset($row['internal_notes']);
        }

        $row['documents'] = Document::getByRequestId((int)$row['id']);
        return $row;
    }

    public static function getAll(string $search = '', string $status = 'all', int $limit = 50, int $offset = 0): array {
        $db = Database::getConnection();
        $sql = "
            SELECT 
                r.id, r.request_number as requestId, r.customer_name as customerName,
                r.mobile, r.email, r.address, r.service_type as serviceType,
                r.pan_number as panNumber, r.financial_year as financialYear,
                r.assessment_year as assessmentYear, r.status, r.internal_notes as internalNotes,
                DATE_FORMAT(r.created_at, '%Y-%m-%d') as date
            FROM service_requests r
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (r.request_number LIKE :s1 OR r.customer_name LIKE :s2 OR r.mobile LIKE :s3 OR r.service_type LIKE :s4)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
            $params[':s4'] = "%{$search}%";
        }

        if ($status !== 'all' && !empty($status)) {
            $sql .= " AND r.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY r.id DESC LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['documents'] = Document::getByRequestId((int)$row['id']);
        }

        return $rows;
    }

    public static function countAll(string $search = '', string $status = 'all'): int {
        $db = Database::getConnection();
        $sql = "SELECT COUNT(*) FROM service_requests WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (request_number LIKE :s1 OR customer_name LIKE :s2 OR mobile LIKE :s3 OR service_type LIKE :s4)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
            $params[':s4'] = "%{$search}%";
        }

        if ($status !== 'all' && !empty($status)) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function updateStatusAndNotes(int|string $idOrNumber, string $status, ?string $internalNotes = null): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE service_requests 
            SET status = :status, internal_notes = :notes, updated_at = NOW() 
            WHERE id = :id_num OR request_number = :req_num
        ");

        return $stmt->execute([
            ':status'  => $status,
            ':notes'   => $internalNotes,
            ':id_num'  => is_numeric($idOrNumber) ? (int)$idOrNumber : 0,
            ':req_num' => $idOrNumber
        ]);
    }
}
