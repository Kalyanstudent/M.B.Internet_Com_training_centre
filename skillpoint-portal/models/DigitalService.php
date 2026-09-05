<?php
/**
 * MB Internet And Digital Studio
 * Digital Services Model (Form Filling, Scanning, Printing, Photo Studio)
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';

class DigitalService {
    public static function getAll(string $status = 'active'): array {
        $db = Database::getConnection();
        $sql = "SELECT * FROM digital_services";
        $params = [];

        if ($status !== 'all') {
            $sql .= " WHERE status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY display_order ASC, id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM digital_services WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($data['title']))) . '-' . rand(100, 999);

        $stmt = $db->prepare("
            INSERT INTO digital_services (
                slug, title, category, description, icon, accent_class, price_text, display_order, status, created_at
            ) VALUES (
                :slug, :title, :category, :description, :icon, :accent_class, :price_text, :display_order, :status, NOW()
            )
        ");

        $stmt->execute([
            ':slug'          => $slug,
            ':title'         => trim($data['title']),
            ':category'      => trim($data['category'] ?? 'Application Services'),
            ':description'   => trim($data['description']),
            ':icon'          => trim($data['icon'] ?? 'bi-printer-fill'),
            ':accent_class'  => trim($data['accent_class'] ?? 'accent-digital'),
            ':price_text'    => trim($data['price_text'] ?? 'Nominal Service Charge'),
            ':display_order' => (int)($data['display_order'] ?? 1),
            ':status'        => ($data['status'] === 'inactive') ? 'inactive' : 'active'
        ]);

        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE digital_services 
            SET title = :title, category = :category, description = :description,
                icon = :icon, accent_class = :accent_class, price_text = :price_text,
                display_order = :display_order, status = :status, updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            ':title'         => trim($data['title']),
            ':category'      => trim($data['category'] ?? 'Application Services'),
            ':description'   => trim($data['description']),
            ':icon'          => trim($data['icon'] ?? 'bi-printer-fill'),
            ':accent_class'  => trim($data['accent_class'] ?? 'accent-digital'),
            ':price_text'    => trim($data['price_text'] ?? 'Nominal Service Charge'),
            ':display_order' => (int)($data['display_order'] ?? 1),
            ':status'        => ($data['status'] === 'inactive') ? 'inactive' : 'active',
            ':id'            => $id
        ]);
    }

    public static function toggleStatus(int $id): string {
        $db = Database::getConnection();
        $curr = self::getById($id);
        if (!$curr) return 'inactive';

        $next = ($curr['status'] === 'active') ? 'inactive' : 'active';
        $stmt = $db->prepare("UPDATE digital_services SET status = :status, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':status' => $next, ':id' => $id]);
        return $next;
    }

    public static function delete(int $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM digital_services WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
