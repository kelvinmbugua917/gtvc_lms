<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Lesson extends Model
{
    /**
     * Get lessons inside a module ordered by sequence_order ASC
     */
    public static function getLessonsByModule(int $moduleId, bool $publishedOnly = false): array
    {
        $sql = "SELECT l.id, l.module_id, l.title, l.content_type, l.text_content, l.duration_minutes,
                       l.sequence_order, l.is_published, l.created_at,
                       (SELECT COUNT(*) FROM learning_materials lm WHERE lm.lesson_id = l.id) AS material_count
                FROM lessons l
                WHERE l.module_id = :module_id";

        if ($publishedOnly) {
            $sql .= " AND l.is_published = 1";
        }

        $sql .= " ORDER BY l.sequence_order ASC, l.id ASC";

        return self::fetchAll($sql, ['module_id' => $moduleId]);
    }

    /**
     * Get single lesson by ID with module, course, department, and lecturer context
     */
    public static function getLessonById(int $id): ?array
    {
        $sql = "SELECT l.id, l.module_id, l.title, l.content_type, l.text_content, l.duration_minutes,
                       l.sequence_order, l.is_published, l.created_at,
                       cm.course_offering_id, cm.title AS module_title,
                       co.unit_id, co.class_id, co.primary_lecturer_id,
                       p.department_id
                FROM lessons l
                JOIN course_modules cm ON cm.id = l.module_id
                JOIN course_offerings co ON co.id = cm.course_offering_id
                JOIN classes c ON c.id = co.class_id
                JOIN programs p ON p.id = c.program_id
                WHERE l.id = :id";

        return self::fetchOne($sql, ['id' => $id]);
    }

    /**
     * Create a new lesson
     */
    public static function createLesson(array $data): int
    {
        // Calculate next sequence order inside the target module
        $seqSql = "SELECT COALESCE(MAX(sequence_order), 0) + 1 AS next_seq FROM lessons WHERE module_id = :module_id";
        $row = self::fetchOne($seqSql, ['module_id' => $data['module_id']]);
        $nextSeq = (int)($row['next_seq'] ?? 1);

        $allowedTypes = ['text', 'video', 'document', 'quiz', 'assignment'];
        $contentType = in_array($data['content_type'] ?? 'text', $allowedTypes, true) ? $data['content_type'] : 'text';

        $sql = "INSERT INTO lessons (module_id, title, content_type, text_content, duration_minutes, sequence_order, is_published)
                VALUES (:module_id, :title, :content_type, :text_content, :duration_minutes, :sequence_order, :is_published)";

        return self::execute($sql, [
            'module_id' => $data['module_id'],
            'title' => trim($data['title']),
            'content_type' => $contentType,
            'text_content' => $data['text_content'] ?? null,
            'duration_minutes' => (int)($data['duration_minutes'] ?? 30),
            'sequence_order' => $data['sequence_order'] ?? $nextSeq,
            'is_published' => isset($data['is_published']) ? (int)(bool)$data['is_published'] : 1,
        ]);
    }

    /**
     * Update lesson
     */
    public static function updateLesson(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (array_key_exists('title', $data)) {
            $fields[] = "title = :title";
            $params['title'] = trim($data['title']);
        }
        if (array_key_exists('content_type', $data)) {
            $fields[] = "content_type = :content_type";
            $params['content_type'] = $data['content_type'];
        }
        if (array_key_exists('text_content', $data)) {
            $fields[] = "text_content = :text_content";
            $params['text_content'] = $data['text_content'];
        }
        if (array_key_exists('duration_minutes', $data)) {
            $fields[] = "duration_minutes = :duration_minutes";
            $params['duration_minutes'] = (int)$data['duration_minutes'];
        }
        if (array_key_exists('sequence_order', $data)) {
            $fields[] = "sequence_order = :sequence_order";
            $params['sequence_order'] = (int)$data['sequence_order'];
        }
        if (array_key_exists('is_published', $data)) {
            $fields[] = "is_published = :is_published";
            $params['is_published'] = (int)(bool)$data['is_published'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE lessons SET " . implode(", ", $fields) . " WHERE id = :id";
        self::execute($sql, $params);
        return true;
    }

    /**
     * Delete lesson
     */
    public static function deleteLesson(int $id): bool
    {
        $sql = "DELETE FROM lessons WHERE id = :id";
        self::execute($sql, ['id' => $id]);
        return true;
    }

    /**
     * Reorder lessons in a module
     */
    public static function reorderLessons(int $moduleId, array $orderedLessonIds): bool
    {
        $sql = "UPDATE lessons SET sequence_order = :seq WHERE id = :id AND module_id = :module_id";
        foreach ($orderedLessonIds as $index => $lessonId) {
            self::execute($sql, [
                'seq' => $index + 1,
                'id' => (int)$lessonId,
                'module_id' => $moduleId,
            ]);
        }
        return true;
    }
}
