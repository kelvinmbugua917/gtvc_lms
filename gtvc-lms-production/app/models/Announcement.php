<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Announcement extends Model
{
    /**
     * Get targeted announcements for a user based on their role and department/cohort scope
     */
    public static function getAnnouncementsForUser(array $userContext, array $filters = []): array
    {
        $userId = (int)($userContext['id'] ?? 0);
        $roleNames = array_column($userContext['roles'] ?? [], 'name');
        $departmentIds = array_column($userContext['departments'] ?? [], 'id');
        
        $isStaffOrAdmin = !empty(array_intersect(['admin', 'super_admin', 'hod', 'lecturer', 'registrar'], $roleNames));
        $isStudent = in_array('student', $roleNames, true);

        // Base query with joins
        $sql = "SELECT a.id, a.title, a.content, a.priority, a.target_role,
                       a.target_department_id, a.target_program_id, a.target_class_id, a.target_course_offering_id,
                       a.author_user_id, a.attachment_path, a.attachment_name,
                       a.is_published, a.is_archived, a.publish_at, a.expire_at,
                       a.created_at, a.updated_at,
                       u.first_name AS author_first_name, u.last_name AS author_last_name, u.email AS author_email,
                       d.name AS department_name, d.code AS department_code,
                       p.name AS program_name, c.name AS class_name
                FROM announcements a
                LEFT JOIN users u ON u.id = a.author_user_id
                LEFT JOIN departments d ON d.id = a.target_department_id
                LEFT JOIN programs p ON p.id = a.target_program_id
                LEFT JOIN classes c ON c.id = a.target_class_id
                WHERE a.is_archived = 0";

        $params = [];

        // Non-management view only gets published & non-expired announcements
        if (empty($filters['management_view'])) {
            $sql .= " AND a.is_published = 1 
                      AND (a.publish_at IS NULL OR a.publish_at <= NOW()) 
                      AND (a.expire_at IS NULL OR a.expire_at >= NOW())";
        }

        // Targeted Audience filtering (unless super_admin / admin in management view)
        if (!in_array('super_admin', $roleNames, true) || empty($filters['management_view'])) {
            $targetingConditions = ["a.target_role = 'all'"];

            if ($isStudent) {
                $targetingConditions[] = "a.target_role = 'student'";
            }
            if ($isStaffOrAdmin) {
                $targetingConditions[] = "a.target_role IN ('staff', 'lecturer', 'hod', 'registrar', 'admin')";
            }

            // Role specific checks
            foreach ($roleNames as $r) {
                $targetingConditions[] = "a.target_role = " . self::getDb()->quote($r);
            }

            // Department targeting
            if (!empty($departmentIds)) {
                $deptPlaceholders = implode(',', array_map('intval', $departmentIds));
                $targetingConditions[] = "(a.target_department_id IS NULL OR a.target_department_id IN ({$deptPlaceholders}))";
            } else {
                $targetingConditions[] = "a.target_department_id IS NULL";
            }

            // Include announcements authored by self
            $targetingConditions[] = "a.author_user_id = :self_user_id";
            $params['self_user_id'] = $userId;

            $sql .= " AND (" . implode(" OR ", array_unique($targetingConditions)) . ")";
        }

        // Priority filter
        if (!empty($filters['priority'])) {
            $sql .= " AND a.priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        // Search term
        if (!empty($filters['search'])) {
            $sql .= " AND (a.title LIKE :search OR a.content LIKE :search)";
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        $sql .= " ORDER BY CASE a.priority WHEN 'urgent' THEN 1 WHEN 'important' THEN 2 ELSE 3 END, a.created_at DESC";

        return self::fetchAll($sql, $params);
    }

    /**
     * Get single announcement by ID
     */
    public static function getAnnouncementById(int $id): ?array
    {
        $sql = "SELECT a.id, a.title, a.content, a.priority, a.target_role,
                       a.target_department_id, a.target_program_id, a.target_class_id, a.target_course_offering_id,
                       a.author_user_id, a.attachment_path, a.attachment_name,
                       a.is_published, a.is_archived, a.publish_at, a.expire_at,
                       a.created_at, a.updated_at,
                       u.first_name AS author_first_name, u.last_name AS author_last_name, u.email AS author_email,
                       d.name AS department_name, d.code AS department_code,
                       p.name AS program_name, c.name AS class_name
                FROM announcements a
                LEFT JOIN users u ON u.id = a.author_user_id
                LEFT JOIN departments d ON d.id = a.target_department_id
                LEFT JOIN programs p ON p.id = a.target_program_id
                LEFT JOIN classes c ON c.id = a.target_class_id
                WHERE a.id = :id";

        return self::fetchOne($sql, ['id' => $id]);
    }

    /**
     * Create announcement
     */
    public static function createAnnouncement(array $data): int
    {
        $sql = "INSERT INTO announcements
                (title, content, priority, target_role, target_department_id, target_program_id, 
                 target_class_id, target_course_offering_id, author_user_id, attachment_path, 
                 attachment_name, is_published, is_archived, publish_at, expire_at)
                VALUES
                (:title, :content, :priority, :target_role, :target_department_id, :target_program_id,
                 :target_class_id, :target_course_offering_id, :author_user_id, :attachment_path,
                 :attachment_name, :is_published, 0, :publish_at, :expire_at)";

        return self::execute($sql, [
            'title' => trim($data['title']),
            'content' => trim($data['content']),
            'priority' => $data['priority'] ?? 'normal',
            'target_role' => $data['target_role'] ?? 'all',
            'target_department_id' => !empty($data['target_department_id']) ? (int)$data['target_department_id'] : null,
            'target_program_id' => !empty($data['target_program_id']) ? (int)$data['target_program_id'] : null,
            'target_class_id' => !empty($data['target_class_id']) ? (int)$data['target_class_id'] : null,
            'target_course_offering_id' => !empty($data['target_course_offering_id']) ? (int)$data['target_course_offering_id'] : null,
            'author_user_id' => (int)$data['author_user_id'],
            'attachment_path' => $data['attachment_path'] ?? null,
            'attachment_name' => $data['attachment_name'] ?? null,
            'is_published' => isset($data['is_published']) ? (int)$data['is_published'] : 1,
            'publish_at' => $data['publish_at'] ?? null,
            'expire_at' => $data['expire_at'] ?? null
        ]);
    }

    /**
     * Update announcement
     */
    public static function updateAnnouncement(int $id, array $data): bool
    {
        $sql = "UPDATE announcements SET
                title = :title,
                content = :content,
                priority = :priority,
                target_role = :target_role,
                target_department_id = :target_department_id,
                target_program_id = :target_program_id,
                target_class_id = :target_class_id,
                target_course_offering_id = :target_course_offering_id,
                attachment_path = COALESCE(:attachment_path, attachment_path),
                attachment_name = COALESCE(:attachment_name, attachment_name),
                publish_at = :publish_at,
                expire_at = :expire_at,
                updated_at = NOW()
                WHERE id = :id";

        self::execute($sql, [
            'id' => $id,
            'title' => trim($data['title']),
            'content' => trim($data['content']),
            'priority' => $data['priority'] ?? 'normal',
            'target_role' => $data['target_role'] ?? 'all',
            'target_department_id' => !empty($data['target_department_id']) ? (int)$data['target_department_id'] : null,
            'target_program_id' => !empty($data['target_program_id']) ? (int)$data['target_program_id'] : null,
            'target_class_id' => !empty($data['target_class_id']) ? (int)$data['target_class_id'] : null,
            'target_course_offering_id' => !empty($data['target_course_offering_id']) ? (int)$data['target_course_offering_id'] : null,
            'attachment_path' => $data['attachment_path'] ?? null,
            'attachment_name' => $data['attachment_name'] ?? null,
            'publish_at' => $data['publish_at'] ?? null,
            'expire_at' => $data['expire_at'] ?? null
        ]);

        return true;
    }

    /**
     * Set published status
     */
    public static function publishAnnouncement(int $id): bool
    {
        $sql = "UPDATE announcements SET is_published = 1, publish_at = COALESCE(publish_at, NOW()), updated_at = NOW() WHERE id = :id";
        self::execute($sql, ['id' => $id]);
        return true;
    }

    /**
     * Archive announcement
     */
    public static function archiveAnnouncement(int $id): bool
    {
        $sql = "UPDATE announcements SET is_archived = 1, updated_at = NOW() WHERE id = :id";
        self::execute($sql, ['id' => $id]);
        return true;
    }

    /**
     * Delete announcement
     */
    public static function deleteAnnouncement(int $id): bool
    {
        $sql = "DELETE FROM announcements WHERE id = :id";
        self::execute($sql, ['id' => $id]);
        return true;
    }
}
