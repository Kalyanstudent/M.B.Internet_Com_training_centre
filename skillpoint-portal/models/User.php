<?php
/**
 * SkillPoint User Model
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';

class User {
    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, user_code, name, email, mobile, address, role, status, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function getAll(string $search = '', string $status = 'all', int $limit = 50, int $offset = 0): array {
        $db = Database::getConnection();
        $sql = "SELECT id, user_code, name, email, mobile, address, role, status, created_at FROM users WHERE role = 'user'";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (name LIKE :s1 OR email LIKE :s2 OR mobile LIKE :s3 OR user_code LIKE :s4)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
            $params[':s4'] = "%{$search}%";
        }

        if ($status !== 'all' && in_array($status, [STATUS_ACTIVE, STATUS_INACTIVE, STATUS_SUSPENDED])) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countAll(string $search = '', string $status = 'all'): int {
        $db = Database::getConnection();
        $sql = "SELECT COUNT(*) FROM users WHERE role = 'user'";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (name LIKE :s1 OR email LIKE :s2 OR mobile LIKE :s3 OR user_code LIKE :s4)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
            $params[':s4'] = "%{$search}%";
        }

        if ($status !== 'all' && in_array($status, [STATUS_ACTIVE, STATUS_INACTIVE, STATUS_SUSPENDED])) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function updateProfile(int $userId, string $name, string $mobile, string $address): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE users 
            SET name = :name, mobile = :mobile, address = :address, updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':name'    => $name,
            ':mobile'  => $mobile,
            ':address' => $address,
            ':id'      => $userId
        ]);
    }

    public static function setStatus(int $userId, string $status): bool {
        if (!in_array($status, [STATUS_ACTIVE, STATUS_INACTIVE, STATUS_SUSPENDED])) {
            return false;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET status = :status, updated_at = NOW() WHERE id = :id");
        return $stmt->execute([
            ':status' => $status,
            ':id'     => $userId
        ]);
    }

    public static function getUserOverviewStats(int $userId): array {
        $db = Database::getConnection();

        // 1. Purchased courses count
        $stmt1 = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :uid AND payment_status = 'Paid'");
        $stmt1->execute([':uid' => $userId]);
        $purchasedCourses = (int) $stmt1->fetchColumn();

        // 2. Active enrollments count
        $stmt2 = $db->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = :uid AND status = 'Active'");
        $stmt2->execute([':uid' => $userId]);
        $activeBatches = (int) $stmt2->fetchColumn();

        // 3. Total tax requests
        $stmt3 = $db->prepare("SELECT COUNT(*) FROM service_requests WHERE user_id = :uid");
        $stmt3->execute([':uid' => $userId]);
        $totalTaxRequests = (int) $stmt3->fetchColumn();

        // 4. Pending tax requests
        $stmt4 = $db->prepare("SELECT COUNT(*) FROM service_requests WHERE user_id = :uid AND status IN ('Pending', 'Processing')");
        $stmt4->execute([':uid' => $userId]);
        $pendingTaxRequests = (int) $stmt4->fetchColumn();

        return [
            'purchased_courses'    => $purchasedCourses,
            'active_courses'       => $activeBatches ?: $purchasedCourses,
            'total_tax_requests'   => $totalTaxRequests,
            'pending_tax_requests' => $pendingTaxRequests
        ];
    }
}
