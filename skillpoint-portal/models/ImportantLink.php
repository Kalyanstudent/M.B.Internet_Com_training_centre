<?php
/**
 * SkillPoint Important Links Model
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';

class ImportantLink {
    public static function getAll(?string $status = 'active'): array {
        $db = Database::getConnection();
        $sql = "SELECT * FROM important_links WHERE 1=1";
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

        $rows = $stmt->fetchAll();
        return array_map(function($r) {
            return [
                'id'          => $r['link_code'] ?: (string)$r['id'],
                'db_id'       => (int) $r['id'],
                'title'       => $r['title'],
                'category'    => $r['category'],
                'description' => $r['description'],
                'url'         => $r['url'],
                'buttonText'  => $r['button_text'] ?: 'Visit Website',
                'icon'        => $r['icon'] ?: 'bi-link-45deg',
                'accentClass' => $r['accent_class'] ?: 'accent-word',
                'order'       => (int) $r['display_order'],
                'status'      => $r['status']
            ];
        }, $rows);
    }

    public static function getById(int|string $idOrCode): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM important_links WHERE (id = :id_num OR link_code = :code) LIMIT 1");
        $stmt->bindValue(':id_num', is_numeric($idOrCode) ? (int)$idOrCode : 0, PDO::PARAM_INT);
        $stmt->bindValue(':code', (string)$idOrCode);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $code = !empty($data['link_code']) ? $data['link_code'] : 'link-' . time();

        $stmt = $db->prepare("
            INSERT INTO important_links (
                link_code, title, category, description, url, button_text, 
                icon, accent_class, display_order, status, created_at
            ) VALUES (
                :link_code, :title, :category, :description, :url, :button_text, 
                :icon, :accent_class, :display_order, :status, NOW()
            )
        ");

        $stmt->execute([
            ':link_code'     => $code,
            ':title'         => trim($data['title']),
            ':category'      => trim($data['category'] ?? 'Taxation'),
            ':description'   => trim($data['description'] ?? ''),
            ':url'           => trim($data['url']),
            ':button_text'   => trim($data['button_text'] ?? 'Visit Website'),
            ':icon'          => $data['icon'] ?? 'bi-link-45deg',
            ':accent_class'  => $data['accent_class'] ?? 'accent-word',
            ':display_order' => (int) ($data['display_order'] ?? 1),
            ':status'        => $data['status'] ?? 'active'
        ]);

        return (int) $db->lastInsertId();
    }

    public static function update(int|string $idOrCode, array $data): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE important_links SET
                title = :title,
                category = :category,
                description = :description,
                url = :url,
                button_text = :button_text,
                display_order = :display_order,
                status = :status,
                updated_at = NOW()
            WHERE (id = :id_num OR link_code = :code)
        ");

        return $stmt->execute([
            ':title'         => trim($data['title']),
            ':category'      => trim($data['category'] ?? 'Taxation'),
            ':description'   => trim($data['description'] ?? ''),
            ':url'           => trim($data['url']),
            ':button_text'   => trim($data['button_text'] ?? 'Visit Website'),
            ':display_order' => (int) ($data['display_order'] ?? 1),
            ':status'        => $data['status'] ?? 'active',
            ':id_num'        => is_numeric($idOrCode) ? (int)$idOrCode : 0,
            ':code'          => (string)$idOrCode
        ]);
    }

    public static function toggleStatus(int|string $idOrCode): string {
        $db = Database::getConnection();
        $current = self::getById($idOrCode);
        if (!$current) return 'inactive';

        $newStatus = ($current['status'] === 'active') ? 'inactive' : 'active';
        $stmt = $db->prepare("UPDATE important_links SET status = :status, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':status' => $newStatus, ':id' => $current['id']]);
        return $newStatus;
    }

    public static function delete(int|string $idOrCode): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM important_links WHERE (id = :id_num OR link_code = :code)");
        return $stmt->execute([
            ':id_num' => is_numeric($idOrCode) ? (int)$idOrCode : 0,
            ':code'   => (string)$idOrCode
        ]);
    }
}
