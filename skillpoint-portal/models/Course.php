<?php
/**
 * SkillPoint Course Model
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';

class Course {
    public static function formatCourse(array $row): array {
        return [
            'id'             => (int) $row['id'],
            'course_code'    => $row['course_code'],
            'slug'           => $row['slug'],
            'title'          => $row['title'],
            'category'       => $row['category'],
            'shortDesc'      => $row['short_desc'],
            'overview'       => $row['overview'] ?? '',
            'duration'       => $row['duration'],
            'hours'          => $row['hours'] ?? '45 Hours',
            'price'          => (int) $row['price'],
            'originalPrice'  => !empty($row['original_price']) ? (int) $row['original_price'] : null,
            'rating'         => (float) $row['rating'],
            'reviewsCount'   => (int) $row['reviews_count'],
            'badge'          => $row['badge'] ?? '',
            'icon'           => $row['icon'] ?: 'bi-journal-bookmark-fill',
            'level'          => $row['level'] ?: 'Beginner to Advanced',
            'eligibility'    => $row['eligibility'] ?: 'Open to all',
            'learnings'      => !empty($row['learnings_json']) ? json_decode($row['learnings_json'], true) : [],
            'syllabus'       => !empty($row['syllabus_json']) ? json_decode($row['syllabus_json'], true) : [],
            'benefits'       => !empty($row['benefits_json']) ? json_decode($row['benefits_json'], true) : [],
            'batches'        => !empty($row['batches_json']) ? json_decode($row['batches_json'], true) : [],
            'status'         => $row['status'],
            'display_order'  => (int) ($row['display_order'] ?? 0),
            'created_at'     => $row['created_at'],
            'updated_at'     => $row['updated_at']
        ];
    }

    public static function getAll(string $category = 'all', string $search = '', string $sortBy = 'popular', ?string $status = 'active'): array {
        $db = Database::getConnection();
        $sql = "SELECT * FROM courses WHERE 1=1";
        $params = [];

        if ($status !== null && $status !== 'all') {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }

        if ($category !== 'all' && !empty($category)) {
            $sql .= " AND LOWER(category) = :category";
            $params[':category'] = strtolower($category);
        }

        if (!empty($search)) {
            $sql .= " AND (title LIKE :s1 OR short_desc LIKE :s2 OR category LIKE :s3)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
        }

        if ($sortBy === 'price-low') {
            $sql .= " ORDER BY price ASC";
        } elseif ($sortBy === 'price-high') {
            $sql .= " ORDER BY price DESC";
        } elseif ($sortBy === 'duration') {
            $sql .= " ORDER BY duration ASC";
        } else {
            $sql .= " ORDER BY display_order ASC, id ASC";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map([self::class, 'formatCourse'], $rows);
    }

    public static function getByIdOrCode(string|int $idOrCode): ?array {
        $db = Database::getConnection();
        if (is_numeric($idOrCode)) {
            $stmt = $db->prepare("SELECT * FROM courses WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => (int) $idOrCode]);
        } else {
            $stmt = $db->prepare("SELECT * FROM courses WHERE course_code = :code OR slug = :slug LIMIT 1");
            $stmt->execute([':code' => $idOrCode, ':slug' => $idOrCode]);
        }

        $row = $stmt->fetch();
        return $row ? self::formatCourse($row) : null;
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $code = !empty($data['course_code']) ? $data['course_code'] : 'course-' . time();
        $slug = !empty($data['slug']) ? $data['slug'] : preg_replace('/[^a-z0-9]+/i', '-', strtolower($data['title']));

        $stmt = $db->prepare("
            INSERT INTO courses (
                course_code, slug, title, category, short_desc, overview, duration, hours, 
                price, original_price, rating, reviews_count, badge, icon, level, eligibility, 
                learnings_json, syllabus_json, benefits_json, batches_json, status, display_order, created_at
            ) VALUES (
                :course_code, :slug, :title, :category, :short_desc, :overview, :duration, :hours, 
                :price, :original_price, :rating, :reviews_count, :badge, :icon, :level, :eligibility, 
                :learnings_json, :syllabus_json, :benefits_json, :batches_json, :status, :display_order, NOW()
            )
        ");

        $stmt->execute([
            ':course_code'    => $code,
            ':slug'           => $slug,
            ':title'          => $data['title'],
            ':category'       => $data['category'],
            ':short_desc'     => $data['short_desc'],
            ':overview'       => $data['overview'] ?? '',
            ':duration'       => $data['duration'],
            ':hours'          => $data['hours'] ?? '45 Hours',
            ':price'          => $data['price'],
            ':original_price' => !empty($data['original_price']) ? $data['original_price'] : null,
            ':rating'         => $data['rating'] ?? 5.0,
            ':reviews_count'  => $data['reviews_count'] ?? 100,
            ':badge'          => $data['badge'] ?? '',
            ':icon'           => $data['icon'] ?? 'bi-mortarboard-fill',
            ':level'          => $data['level'] ?? 'Beginner to Advanced',
            ':eligibility'    => $data['eligibility'] ?? 'Open to all',
            ':learnings_json' => isset($data['learnings']) ? json_encode($data['learnings']) : '[]',
            ':syllabus_json'  => isset($data['syllabus']) ? json_encode($data['syllabus']) : '[]',
            ':benefits_json'  => isset($data['benefits']) ? json_encode($data['benefits']) : '[]',
            ':batches_json'   => isset($data['batches']) ? json_encode($data['batches']) : '[]',
            ':status'         => $data['status'] ?? 'active',
            ':display_order'  => (int) ($data['display_order'] ?? 0)
        ]);

        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE courses SET 
                title = :title,
                category = :category,
                short_desc = :short_desc,
                overview = :overview,
                duration = :duration,
                hours = :hours,
                price = :price,
                original_price = :original_price,
                eligibility = :eligibility,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            ':title'          => $data['title'],
            ':category'       => $data['category'],
            ':short_desc'     => $data['short_desc'],
            ':overview'       => $data['overview'] ?? '',
            ':duration'       => $data['duration'],
            ':hours'          => $data['hours'] ?? '45 Hours',
            ':price'          => $data['price'],
            ':original_price' => !empty($data['original_price']) ? $data['original_price'] : null,
            ':eligibility'    => $data['eligibility'] ?? 'Open to all',
            ':status'         => $data['status'] ?? 'active',
            ':id'             => $id
        ]);
    }

    public static function toggleStatus(int $id): string {
        $db = Database::getConnection();
        $current = $db->query("SELECT status FROM courses WHERE id = " . (int)$id)->fetchColumn();
        $newStatus = ($current === 'active') ? 'inactive' : 'active';
        $stmt = $db->prepare("UPDATE courses SET status = :status, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':status' => $newStatus, ':id' => $id]);
        return $newStatus;
    }

    public static function delete(int $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM courses WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
