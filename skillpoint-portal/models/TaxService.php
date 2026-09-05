<?php
/**
 * SkillPoint Tax & Digital Services Catalog Model
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';

class TaxService {
    public static function getAll(?string $status = 'active'): array {
        $db = Database::getConnection();
        $sql = "SELECT * FROM tax_services WHERE 1=1";
        if ($status !== null && $status !== 'all') {
            $sql .= " AND status = :status";
        }
        $sql .= " ORDER BY display_order ASC, id ASC";

        $stmt = $db->prepare($sql);
        if ($status !== null && $status !== 'all') {
            $stmt->execute([':status' => $status]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    public static function getByIdOrSlug(string|int $idOrSlug): ?array {
        $db = Database::getConnection();
        if (is_numeric($idOrSlug)) {
            $stmt = $db->prepare("SELECT * FROM tax_services WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => (int) $idOrSlug]);
        } else {
            $stmt = $db->prepare("SELECT * FROM tax_services WHERE slug = :slug LIMIT 1");
            $stmt->execute([':slug' => $idOrSlug]);
        }
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $slug = !empty($data['slug']) ? $data['slug'] : preg_replace('/[^a-z0-9]+/i', '-', strtolower($data['title']));

        $stmt = $db->prepare("
            INSERT INTO tax_services (slug, title, category, short_desc, full_desc, icon, accent_class, price, display_order, status, created_at)
            VALUES (:slug, :title, :category, :short_desc, :full_desc, :icon, :accent_class, :price, :display_order, :status, NOW())
        ");

        $stmt->execute([
            ':slug'          => $slug,
            ':title'         => $data['title'],
            ':category'      => $data['category'] ?? 'Taxation',
            ':short_desc'    => $data['short_desc'],
            ':full_desc'     => $data['full_desc'] ?? '',
            ':icon'          => $data['icon'] ?? 'bi-file-earmark-medical-fill',
            ':accent_class'  => $data['accent_class'] ?? 'accent-word',
            ':price'         => !empty($data['price']) ? (float)$data['price'] : null,
            ':display_order' => (int) ($data['display_order'] ?? 0),
            ':status'        => $data['status'] ?? 'active'
        ]);

        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE tax_services SET
                title = :title,
                category = :category,
                short_desc = :short_desc,
                full_desc = :full_desc,
                icon = :icon,
                accent_class = :accent_class,
                price = :price,
                display_order = :display_order,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            ':title'         => $data['title'],
            ':category'      => $data['category'] ?? 'Taxation',
            ':short_desc'    => $data['short_desc'],
            ':full_desc'     => $data['full_desc'] ?? '',
            ':icon'          => $data['icon'] ?? 'bi-file-earmark-medical-fill',
            ':accent_class'  => $data['accent_class'] ?? 'accent-word',
            ':price'         => !empty($data['price']) ? (float)$data['price'] : null,
            ':display_order' => (int) ($data['display_order'] ?? 0),
            ':status'        => $data['status'] ?? 'active',
            ':id'            => $id
        ]);
    }

    public static function toggleStatus(int $id): string {
        $db = Database::getConnection();
        $current = $db->query("SELECT status FROM tax_services WHERE id = " . (int)$id)->fetchColumn();
        $newStatus = ($current === 'active') ? 'inactive' : 'active';
        $stmt = $db->prepare("UPDATE tax_services SET status = :status, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':status' => $newStatus, ':id' => $id]);
        return $newStatus;
    }

    public static function delete(int $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM tax_services WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
