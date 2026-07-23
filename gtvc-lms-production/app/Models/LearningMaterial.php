<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class LearningMaterial extends Model
{
    /**
     * Get learning materials associated with a lesson
     */
    public static function getMaterialsByLesson(int $lessonId): array
    {
        $sql = "SELECT lm.id, lm.lesson_id, lm.title, lm.file_path, lm.file_type, lm.file_size_bytes, lm.external_url, lm.created_at
                FROM learning_materials lm
                WHERE lm.lesson_id = :lesson_id
                ORDER BY lm.created_at ASC";

        return self::fetchAll($sql, ['lesson_id' => $lessonId]);
    }

    /**
     * Get single material details with lesson, module, course context
     */
    public static function getMaterialById(int $id): ?array
    {
        $sql = "SELECT lm.id, lm.lesson_id, lm.title, lm.file_path, lm.file_type, lm.file_size_bytes, lm.external_url, lm.created_at,
                       l.module_id, cm.course_offering_id, co.unit_id, co.class_id, co.primary_lecturer_id,
                       p.department_id
                FROM learning_materials lm
                JOIN lessons l ON l.id = lm.lesson_id
                JOIN course_modules cm ON cm.id = l.module_id
                JOIN course_offerings co ON co.id = cm.course_offering_id
                JOIN classes c ON c.id = co.class_id
                JOIN programs p ON p.id = c.program_id
                WHERE lm.id = :id";

        return self::fetchOne($sql, ['id' => $id]);
    }

    /**
     * Create learning material
     */
    public static function createMaterial(array $data): int
    {
        $sql = "INSERT INTO learning_materials (lesson_id, title, file_path, file_type, file_size_bytes, external_url)
                VALUES (:lesson_id, :title, :file_path, :file_type, :file_size_bytes, :external_url)";

        return self::execute($sql, [
            'lesson_id' => $data['lesson_id'],
            'title' => trim($data['title']),
            'file_path' => $data['file_path'] ?? '',
            'file_type' => $data['file_type'] ?? 'application/octet-stream',
            'file_size_bytes' => isset($data['file_size_bytes']) ? (int)$data['file_size_bytes'] : null,
            'external_url' => $data['external_url'] ?? null,
        ]);
    }

    /**
     * Delete material
     */
    public static function deleteMaterial(int $id): bool
    {
        $sql = "DELETE FROM learning_materials WHERE id = :id";
        self::execute($sql, ['id' => $id]);
        return true;
    }
}
