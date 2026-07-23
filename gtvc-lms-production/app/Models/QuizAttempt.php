<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class QuizAttempt extends Model
{
    /**
     * Get attempt by ID
     */
    public static function getAttemptById(int $id): ?array
    {
        $sql = "SELECT qa.id, qa.quiz_id, qa.student_id, qa.attempt_number, qa.started_at,
                       qa.submitted_at, qa.score_achieved, qa.total_possible_marks,
                       qa.percentage_score, qa.is_passed, qa.status,
                       q.title AS quiz_title, q.time_limit_minutes, q.passing_percentage,
                       q.course_offering_id, co.unit_id, co.class_id, co.primary_lecturer_id,
                       p.department_id, u.full_name AS student_name, u.email AS student_email
                FROM quiz_attempts qa
                JOIN quizzes q ON q.id = qa.quiz_id
                JOIN course_offerings co ON co.id = q.course_offering_id
                JOIN classes c ON c.id = co.class_id
                JOIN programs p ON p.id = c.program_id
                JOIN users u ON u.id = qa.student_id
                WHERE qa.id = :id";

        return self::fetchOne($sql, ['id' => $id]);
    }

    /**
     * Get list of attempts by student for a specific quiz
     */
    public static function getStudentAttemptsForQuiz(int $quizId, int $studentId): array
    {
        $sql = "SELECT id, quiz_id, student_id, attempt_number, started_at, submitted_at,
                       score_achieved, total_possible_marks, percentage_score, is_passed, status
                FROM quiz_attempts
                WHERE quiz_id = :quiz_id AND student_id = :student_id
                ORDER BY attempt_number DESC";

        return self::fetchAll($sql, [
            'quiz_id' => $quizId,
            'student_id' => $studentId
        ]);
    }

    /**
     * Start a new attempt
     */
    public static function startAttempt(int $quizId, int $studentId): array
    {
        // Check existing attempts count
        $attempts = self::getStudentAttemptsForQuiz($quizId, $studentId);
        $attemptNumber = count($attempts) + 1;

        $sql = "INSERT INTO quiz_attempts (quiz_id, student_id, attempt_number, started_at, status)
                VALUES (:quiz_id, :student_id, :attempt_number, CURRENT_TIMESTAMP, 'in_progress')";

        $attemptId = self::execute($sql, [
            'quiz_id' => $quizId,
            'student_id' => $studentId,
            'attempt_number' => $attemptNumber
        ]);

        return self::getAttemptById($attemptId);
    }

    /**
     * Complete and evaluate quiz attempt (evaluates score server-side)
     */
    public static function finalizeAttempt(int $attemptId, float $scoreAchieved, float $totalPossibleMarks, float $passingPercentage): bool
    {
        $percentageScore = $totalPossibleMarks > 0 ? round(($scoreAchieved / $totalPossibleMarks) * 100, 2) : 0.0;
        $isPassed = $percentageScore >= $passingPercentage ? 1 : 0;

        $sql = "UPDATE quiz_attempts
                SET score_achieved = :score_achieved,
                    total_possible_marks = :total_possible_marks,
                    percentage_score = :percentage_score,
                    is_passed = :is_passed,
                    status = 'submitted',
                    submitted_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        self::execute($sql, [
            'id' => $attemptId,
            'score_achieved' => $scoreAchieved,
            'total_possible_marks' => $totalPossibleMarks,
            'percentage_score' => $percentageScore,
            'is_passed' => $isPassed
        ]);

        return true;
    }
}
