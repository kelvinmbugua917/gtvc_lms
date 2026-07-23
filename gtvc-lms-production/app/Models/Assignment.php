<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Assignment extends Model
{
    /**
     * Get all assignments for a course offering
     */
    public static function getAssignmentsByOffering(int $offeringId, bool $publishedOnly = false): array
    {
        $sql = "SELECT a.id, a.course_offering_id, a.title, a.description, a.instructions,
                       a.max_marks, a.is_published, a.release_date, a.due_date,
                       a.allow_late_submission, a.created_at, a.updated_at,
                       (SELECT COUNT(*) FROM assignment_submissions sub WHERE sub.assignment_id = a.id) AS submission_count
                FROM assignments a
                WHERE a.course_offering_id = :offering_id";

        if ($publishedOnly) {
            $sql .= " AND a.is_published = 1";
        }

        $sql .= " ORDER BY a.due_date ASC, a.created_at DESC";

        return self::fetchAll($sql, ['offering_id' => $offeringId]);
    }

    /**
     * Get single assignment with course offering context for authorization
     */
    public static function getAssignmentById(int $id): ?array
    {
        $sql = "SELECT a.id, a.course_offering_id, a.title, a.description, a.instructions,
                       a.max_marks, a.is_published, a.release_date, a.due_date,
                       a.allow_late_submission, a.created_at, a.updated_at,
                       co.unit_id, co.class_id, co.primary_lecturer_id,
                       p.department_id, u.title AS unit_title, u.code AS unit_code,
                       c.name AS class_name
                FROM assignments a
                JOIN course_offerings co ON co.id = a.course_offering_id
                JOIN units u ON u.id = co.unit_id
                JOIN classes c ON c.id = co.class_id
                JOIN programs p ON p.id = c.program_id
                WHERE a.id = :id";

        return self::fetchOne($sql, ['id' => $id]);
    }

    /**
     * Create assignment
     */
    public static function createAssignment(array $data): int
    {
        $sql = "INSERT INTO assignments 
                (course_offering_id, title, description, instructions, max_marks, is_published, release_date, due_date, allow_late_submission)
                VALUES 
                (:course_offering_id, :title, :description, :instructions, :max_marks, :is_published, :release_date, :due_date, :allow_late_submission)";

        return self::execute($sql, [
            'course_offering_id' => $data['course_offering_id'],
            'title' => trim($data['title']),
            'description' => $data['description'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'max_marks' => isset($data['max_marks']) ? (int)$data['max_marks'] : 100,
            'is_published' => isset($data['is_published']) ? (int)$data['is_published'] : 0,
            'release_date' => $data['release_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'allow_late_submission' => isset($data['allow_late_submission']) ? (int)$data['allow_late_submission'] : 1
        ]);
    }

    /**
     * Update assignment
     */
    public static function updateAssignment(int $id, array $data): bool
    {
        $sql = "UPDATE assignments 
                SET title = :title,
                    description = :description,
                    instructions = :instructions,
                    max_marks = :max_marks,
                    is_published = :is_published,
                    release_date = :release_date,
                    due_date = :due_date,
                    allow_late_submission = :allow_late_submission,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        self::execute($sql, [
            'id' => $id,
            'title' => trim($data['title']),
            'description' => $data['description'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'max_marks' => (int)($data['max_marks'] ?? 100),
            'is_published' => (int)($data['is_published'] ?? 0),
            'release_date' => $data['release_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'allow_late_submission' => (int)($data['allow_late_submission'] ?? 1)
        ]);

        return true;
    }

    /**
     * Delete assignment
     */
    public static function deleteAssignment(int $id): bool
    {
        $sql = "DELETE FROM assignments WHERE id = :id";
        self::execute($sql, ['id' => $id]);
        return true;
    }
}
