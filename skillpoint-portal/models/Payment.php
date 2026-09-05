<?php
/**
 * SkillPoint Payment Model
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';

class Payment {
    public static function getAll(string $search = '', string $status = 'all', int $limit = 50, int $offset = 0): array {
        $db = Database::getConnection();
        $sql = "
            SELECT 
                p.id, p.payment_number as paymentId, o.order_number as orderId,
                o.customer_name as customerName, o.course_title as courseTitle,
                p.amount, p.currency, p.gateway as paymentGateway, p.status,
                DATE_FORMAT(p.created_at, '%Y-%m-%d') as date
            FROM payments p
            JOIN orders o ON p.order_id = o.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (p.payment_number LIKE :s1 OR o.order_number LIKE :s2 OR o.customer_name LIKE :s3 OR o.course_title LIKE :s4)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
            $params[':s4'] = "%{$search}%";
        }

        if ($status !== 'all' && !empty($status)) {
            $sql .= " AND p.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY p.id DESC LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }
}
