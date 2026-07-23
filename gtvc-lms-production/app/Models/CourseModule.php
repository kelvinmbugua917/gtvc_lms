<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class CourseModule extends Model
{
    /**
     * Retrieve all modules for a course offering ordered by sequence_order ASC
     */
    public static function getModulesByOffering(int $courseOfferingId, bool $publishedOnly = false): array
    {
        $sql = "SELECT cm.id, cm.course_offering_id, cm.title, cm.description, cm.sequence_order, cm.created_at,
                       (SELECT COUNT(*) FROM lessons l WHERE l.module_id = cm.id" . ($publishedOnly ? " AND l.is_published = 1" : "") . ") AS lesson_count
                FROM course_modules cm
                WHERE cm.course_offering_id = :course_offering_id
                ORDER BY cm.sequence_order ASC, cm.id ASC";

        return self::fetchAll($sql, ['course_offering_id' => $courseOfferingId]);
    }

    /**
     * Retrieve single module details
     */
    public static function getModuleById(int $id): ?array
    {
        $sql = "SELECT cm.id, cm.course_offering_id, cm.title, cm.description, cm.sequence_order, cm.created_at,
                       co.unit_id, co.class_id, co.primary_lecturer_id,
                       p.department_id
                FROM course_modules cm
                JOIN course_offerings co ON co.id = cm.course_offering_id
                JOIN classes c ON c.id = co.class_id
                JOIN programs p ON p.id = c.program_id
                WHERE cm.id = :id";

        return self::fetchOne($sql, ['id' => $id]);
    }

    /**
     * Create a new course module
     */
    public static function createModule(array $data): int
    {
        // Calculate next sequence order
        $seqSql = "SELECT COALESCE(MAX(sequence_order), 0) + 1 AS next_seq FROM course_modules WHERE course_offering_id = :co_id";
        $row = self::fetchOne($seqSql, ['co_id' => $data['course_offering_id']]);
        $nextSeq = (int)($row['next_seq'] ?? 1);

        $sql = "INSERT INTO course_modules (course_offering_id, title, description, sequence_order)
                VALUES (:course_offering_id, :title, :description, :sequence_order)";

        return self::execute($sql, [
            'course_offering_id' => $data['course_offering_id'],
            'title' => trim($data['title']),
            'description' => $data['description'] ?? null,
            'sequence_order' => $data['sequence_order'] ?? $nextSeq,
        ]);
    }

    /**
     * Update module details
     */
    public static function updateModule(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (array_key_exists('title', $data)) {
            $fields[] = "title = :title";
            $params['title'] = trim($data['title']);
        }
        if (array_key_exists('description', $data)) {
            $fields[] = "description = :description";
            $params['description'] = $data['description'];
        }
        if (array_key_exists('sequence_order', $data)) {
            $fields[] = "sequence_order = :sequence_order";
            $params['sequence_order'] = (int)$data['sequence_order'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE course_modules SET " . implode(", ", $fields) . " WHERE id = :id";
        self::execute($sql, $params);
        return true;
    }

    /**
     * Delete module (cascades lessons, materials via MySQL foreign keys)
     */
    public static function deleteModule(int $id): bool
    {
        $sql = "DELETE FROM course_modules WHERE id = :id";
        self::execute($sql, ['id' => $id]);
        return true;
    }

    /**
     * Reorder module sequence
     */
    public static function reorderModules(int $courseOfferingId, array $orderedModuleIds): bool
    {
        $sql = "UPDATE course_modules SET sequence_order = :seq WHERE id = :id AND course_offering_id = :co_id";
        foreach ($orderedModuleIds as $index => $moduleId) {
            self::execute($sql, [
                'seq' => $index + 1,
                'id' => (int)$moduleId,
                'co_id' => $courseOfferingId,
            ]);
        }
        return true;
    }
}
