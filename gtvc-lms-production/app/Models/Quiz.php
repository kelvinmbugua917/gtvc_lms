<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Quiz extends Model
{
    /**
     * Get quizzes for a course offering
     */
    public static function getQuizzesByOffering(int $offeringId, bool $publishedOnly = false): array
    {
        $sql = "SELECT q.id, q.course_offering_id, q.title, q.description, q.instructions,
                       q.time_limit_minutes, q.passing_percentage, q.max_attempts,
                       q.is_published, q.available_from, q.available_until, q.created_at, q.updated_at,
                       (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count,
                       (SELECT COALESCE(SUM(qq.marks), 0) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS total_marks
                FROM quizzes q
                WHERE q.course_offering_id = :offering_id";

        if ($publishedOnly) {
            $sql .= " AND q.is_published = 1";
        }

        $sql .= " ORDER BY q.available_from ASC, q.created_at DESC";

        return self::fetchAll($sql, ['offering_id' => $offeringId]);
    }

    /**
     * Get single quiz with course offering context
     */
    public static function getQuizById(int $id): ?array
    {
        $sql = "SELECT q.id, q.course_offering_id, q.title, q.description, q.instructions,
                       q.time_limit_minutes, q.passing_percentage, q.max_attempts,
                       q.is_published, q.available_from, q.available_until, q.created_at, q.updated_at,
                       co.unit_id, co.class_id, co.primary_lecturer_id, p.department_id,
                       u.title AS unit_title, u.code AS unit_code, c.name AS class_name,
                       (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count,
                       (SELECT COALESCE(SUM(qq.marks), 0) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS total_marks
                FROM quizzes q
                JOIN course_offerings co ON co.id = q.course_offering_id
                JOIN units u ON u.id = co.unit_id
                JOIN classes c ON c.id = co.class_id
                JOIN programs p ON p.id = c.program_id
                WHERE q.id = :id";

        return self::fetchOne($sql, ['id' => $id]);
    }

    /**
     * Create quiz
     */
    public static function createQuiz(array $data): int
    {
        $sql = "INSERT INTO quizzes 
                (course_offering_id, title, description, instructions, time_limit_minutes, passing_percentage, max_attempts, is_published, available_from, available_until)
                VALUES 
                (:course_offering_id, :title, :description, :instructions, :time_limit_minutes, :passing_percentage, :max_attempts, :is_published, :available_from, :available_until)";

        return self::execute($sql, [
            'course_offering_id' => $data['course_offering_id'],
            'title' => trim($data['title']),
            'description' => $data['description'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'time_limit_minutes' => isset($data['time_limit_minutes']) ? (int)$data['time_limit_minutes'] : 30,
            'passing_percentage' => isset($data['passing_percentage']) ? (float)$data['passing_percentage'] : 50.0,
            'max_attempts' => isset($data['max_attempts']) ? (int)$data['max_attempts'] : 1,
            'is_published' => isset($data['is_published']) ? (int)$data['is_published'] : 0,
            'available_from' => $data['available_from'] ?? null,
            'available_until' => $data['available_until'] ?? null
        ]);
    }

    /**
     * Update quiz
     */
    public static function updateQuiz(int $id, array $data): bool
    {
        $sql = "UPDATE quizzes
                SET title = :title,
                    description = :description,
                    instructions = :instructions,
                    time_limit_minutes = :time_limit_minutes,
                    passing_percentage = :passing_percentage,
                    max_attempts = :max_attempts,
                    is_published = :is_published,
                    available_from = :available_from,
                    available_until = :available_until,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        self::execute($sql, [
            'id' => $id,
            'title' => trim($data['title']),
            'description' => $data['description'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'time_limit_minutes' => (int)($data['time_limit_minutes'] ?? 30),
            'passing_percentage' => (float)($data['passing_percentage'] ?? 50.0),
            'max_attempts' => (int)($data['max_attempts'] ?? 1),
            'is_published' => (int)($data['is_published'] ?? 0),
            'available_from' => $data['available_from'] ?? null,
            'available_until' => $data['available_until'] ?? null
        ]);

        return true;
    }

    /**
     * Delete quiz
     */
    public static function deleteQuiz(int $id): bool
    {
        $sql = "DELETE FROM quizzes WHERE id = :id";
        self::execute($sql, ['id' => $id]);
        return true;
    }
}
