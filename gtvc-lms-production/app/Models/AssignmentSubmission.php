<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AssignmentSubmission extends Model
{
    /**
     * Get submission by ID with security context
     */
    public static function getSubmissionById(int $id): ?array
    {
        $sql = "SELECT sub.id, sub.assignment_id, sub.student_id, sub.file_path, sub.original_filename,
                       sub.file_size_bytes, sub.submission_text, sub.submitted_at, sub.is_late,
                       sub.marks_awarded, sub.feedback, sub.graded_by_user_id, sub.graded_at,
                       a.title AS assignment_title, a.max_marks, a.due_date, a.course_offering_id,
                       co.unit_id, co.class_id, co.primary_lecturer_id, p.department_id,
                       u.full_name AS student_name, u.email AS student_email,
                       sp.index_number
                FROM assignment_submissions sub
                JOIN assignments a ON a.id = sub.assignment_id
                JOIN course_offerings co ON co.id = a.course_offering_id
                JOIN classes c ON c.id = co.class_id
                JOIN programs p ON p.id = c.program_id
                JOIN users u ON u.id = sub.student_id
                LEFT JOIN student_profiles sp ON sp.user_id = u.id
                WHERE sub.id = :id";

        return self::fetchOne($sql, ['id' => $id]);
    }

    /**
     * Get student's submission for a specific assignment
     */
    public static function getStudentSubmission(int $assignmentId, int $studentId): ?array
    {
        $sql = "SELECT sub.id, sub.assignment_id, sub.student_id, sub.file_path, sub.original_filename,
                       sub.file_size_bytes, sub.submission_text, sub.submitted_at, sub.is_late,
                       sub.marks_awarded, sub.feedback, sub.graded_by_user_id, sub.graded_at
                FROM assignment_submissions sub
                WHERE sub.assignment_id = :assignment_id AND sub.student_id = :student_id";

        return self::fetchOne($sql, [
            'assignment_id' => $assignmentId,
            'student_id' => $studentId
        ]);
    }

    /**
     * Get all submissions for an assignment
     */
    public static function getSubmissionsByAssignment(int $assignmentId): array
    {
        $sql = "SELECT sub.id, sub.assignment_id, sub.student_id, sub.file_path, sub.original_filename,
                       sub.file_size_bytes, sub.submission_text, sub.submitted_at, sub.is_late,
                       sub.marks_awarded, sub.feedback, sub.graded_by_user_id, sub.graded_at,
                       u.full_name AS student_name, u.email AS student_email,
                       sp.index_number
                FROM assignment_submissions sub
                JOIN users u ON u.id = sub.student_id
                LEFT JOIN student_profiles sp ON sp.user_id = u.id
                WHERE sub.assignment_id = :assignment_id
                ORDER BY sub.submitted_at DESC";

        return self::fetchAll($sql, ['assignment_id' => $assignmentId]);
    }

    /**
     * Create or replace student assignment submission
     */
    public static function saveSubmission(array $data): int
    {
        // Check if existing
        $existing = self::getStudentSubmission((int)$data['assignment_id'], (int)$data['student_id']);

        if ($existing) {
            $sql = "UPDATE assignment_submissions 
                    SET file_path = :file_path,
                        original_filename = :original_filename,
                        file_size_bytes = :file_size_bytes,
                        submission_text = :submission_text,
                        submitted_at = CURRENT_TIMESTAMP,
                        is_late = :is_late
                    WHERE id = :id";

            self::execute($sql, [
                'id' => $existing['id'],
                'file_path' => $data['file_path'] ?? $existing['file_path'],
                'original_filename' => $data['original_filename'] ?? $existing['original_filename'],
                'file_size_bytes' => isset($data['file_size_bytes']) ? (int)$data['file_size_bytes'] : $existing['file_size_bytes'],
                'submission_text' => $data['submission_text'] ?? $existing['submission_text'],
                'is_late' => isset($data['is_late']) ? (int)$data['is_late'] : 0
            ]);

            return (int)$existing['id'];
        } else {
            $sql = "INSERT INTO assignment_submissions 
                    (assignment_id, student_id, file_path, original_filename, file_size_bytes, submission_text, is_late)
                    VALUES 
                    (:assignment_id, :student_id, :file_path, :original_filename, :file_size_bytes, :submission_text, :is_late)";

            return self::execute($sql, [
                'assignment_id' => $data['assignment_id'],
                'student_id' => $data['student_id'],
                'file_path' => $data['file_path'] ?? null,
                'original_filename' => $data['original_filename'] ?? null,
                'file_size_bytes' => isset($data['file_size_bytes']) ? (int)$data['file_size_bytes'] : null,
                'submission_text' => $data['submission_text'] ?? null,
                'is_late' => isset($data['is_late']) ? (int)$data['is_late'] : 0
            ]);
        }
    }

    /**
     * Grade assignment submission
     */
    public static function gradeSubmission(int $submissionId, float $marksAwarded, ?string $feedback, int $gradedByUserId): bool
    {
        $sql = "UPDATE assignment_submissions
                SET marks_awarded = :marks_awarded,
                    feedback = :feedback,
                    graded_by_user_id = :graded_by_user_id,
                    graded_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        self::execute($sql, [
            'id' => $submissionId,
            'marks_awarded' => $marksAwarded,
            'feedback' => $feedback,
            'graded_by_user_id' => $gradedByUserId
        ]);

        return true;
    }

    /**
     * Delete submission
     */
    public static function deleteSubmission(int $submissionId): bool
    {
        $sql = "DELETE FROM assignment_submissions WHERE id = :id";
        self::execute($sql, ['id' => $submissionId]);
        return true;
    }
}
