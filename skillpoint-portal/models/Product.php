<?php
/**
 * MB Internet And Digital Studio | Mobile & Gift House
 * Product Catalog Model (Mobile Accessories, Cables, Chargers, Personalized Gifts)
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';

class Product {
    public static function getAll(string $category = 'all', string $status = 'active'): array {
        $db = Database::getConnection();
        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];

        if ($category !== 'all' && !empty($category)) {
            $sql .= " AND LOWER(category) = :category";
            $params[':category'] = strtolower($category);
        }

        if ($status !== 'all') {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY display_order ASC, id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $code = 'PROD-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name'] ?? 'ITEM'), 0, 4)) . '-' . rand(10, 99);

        $stmt = $db->prepare("
            INSERT INTO products (
                code, name, category, description, price, badge, icon, accent_class, display_order, status, created_at
            ) VALUES (
                :code, :name, :category, :description, :price, :badge, :icon, :accent_class, :display_order, :status, NOW()
            )
        ");

        $stmt->execute([
            ':code'          => $code,
            ':name'          => trim($data['name']),
            ':category'      => trim($data['category'] ?? 'Mobile Accessories'),
            ':description'   => trim($data['description'] ?? ''),
            ':price'         => !empty($data['price']) ? (float)$data['price'] : null,
            ':badge'         => !empty($data['badge']) ? trim($data['badge']) : null,
            ':icon'          => trim($data['icon'] ?? 'bi-phone-fill'),
            ':accent_class'  => trim($data['accent_class'] ?? 'accent-mobile'),
            ':display_order' => (int)($data['display_order'] ?? 1),
            ':status'        => ($data['status'] === 'inactive') ? 'inactive' : 'active'
        ]);

        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE products 
            SET name = :name, category = :category, description = :description,
                price = :price, badge = :badge, icon = :icon, accent_class = :accent_class,
                display_order = :display_order, status = :status, updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            ':name'          => trim($data['name']),
            ':category'      => trim($data['category'] ?? 'Mobile Accessories'),
            ':description'   => trim($data['description'] ?? ''),
            ':price'         => !empty($data['price']) ? (float)$data['price'] : null,
            ':badge'         => !empty($data['badge']) ? trim($data['badge']) : null,
            ':icon'          => trim($data['icon'] ?? 'bi-phone-fill'),
            ':accent_class'  => trim($data['accent_class'] ?? 'accent-mobile'),
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
        $stmt = $db->prepare("UPDATE products SET status = :status, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':status' => $next, ':id' => $id]);
        return $next;
    }

    public static function delete(int $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
