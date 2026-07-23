<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class LessonProgress extends Model
{
    /**
     * Get progress for a specific student and lesson
     */
    public static function getProgress(int $studentId, int $lessonId): ?array
    {
        $sql = "SELECT id, student_id, lesson_id, is_completed, time_spent_seconds, completed_at
                FROM student_lesson_progress
                WHERE student_id = :student_id AND lesson_id = :lesson_id";

        return self::fetchOne($sql, [
            'student_id' => $studentId,
            'lesson_id' => $lessonId,
        ]);
    }

    /**
     * Record or update student lesson progress
     */
    public static function saveProgress(int $studentId, int $lessonId, bool $isCompleted, int $timeSpentSeconds = 0): bool
    {
        $existing = self::getProgress($studentId, $lessonId);

        if ($existing) {
            $sql = "UPDATE student_lesson_progress
                    SET is_completed = :is_completed,
                        time_spent_seconds = time_spent_seconds + :time_spent,
                        completed_at = :completed_at
                    WHERE id = :id";

            self::execute($sql, [
                'is_completed' => $isCompleted ? 1 : 0,
                'time_spent' => max(0, $timeSpentSeconds),
                'completed_at' => $isCompleted ? date('Y-m-d H:i:s') : ($existing['is_completed'] ? $existing['completed_at'] : null),
                'id' => $existing['id'],
            ]);
        } else {
            $sql = "INSERT INTO student_lesson_progress (student_id, lesson_id, is_completed, time_spent_seconds, completed_at)
                    VALUES (:student_id, :lesson_id, :is_completed, :time_spent, :completed_at)";

            self::execute($sql, [
                'student_id' => $studentId,
                'lesson_id' => $lessonId,
                'is_completed' => $isCompleted ? 1 : 0,
                'time_spent' => max(0, $timeSpentSeconds),
                'completed_at' => $isCompleted ? date('Y-m-d H:i:s') : null,
            ]);
        }

        return true;
    }

    /**
     * Get overall progress for a student in a course offering
     */
    public static function getStudentCourseProgress(int $studentId, int $courseOfferingId): array
    {
        // Total published lessons in course offering
        $totalSql = "SELECT COUNT(l.id) AS total_lessons
                     FROM lessons l
                     JOIN course_modules cm ON cm.id = l.module_id
                     WHERE cm.course_offering_id = :co_id AND l.is_published = 1";
        $totalRow = self::fetchOne($totalSql, ['co_id' => $courseOfferingId]);
        $totalLessons = (int)($totalRow['total_lessons'] ?? 0);

        // Completed lessons for this student
        $completedSql = "SELECT COUNT(slp.id) AS completed_lessons, COALESCE(SUM(slp.time_spent_seconds), 0) AS total_time_seconds
                         FROM student_lesson_progress slp
                         JOIN lessons l ON l.id = slp.lesson_id
                         JOIN course_modules cm ON cm.id = l.module_id
                         WHERE cm.course_offering_id = :co_id
                           AND slp.student_id = :student_id
                           AND slp.is_completed = 1";
        $completedRow = self::fetchOne($completedSql, [
            'co_id' => $courseOfferingId,
            'student_id' => $studentId,
        ]);

        $completedLessons = (int)($completedRow['completed_lessons'] ?? 0);
        $totalTimeSeconds = (int)($completedRow['total_time_seconds'] ?? 0);
        $percentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 1) : 0.0;

        return [
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'completion_percentage' => $percentage,
            'total_time_seconds' => $totalTimeSeconds,
        ];
    }

    /**
     * Verify if a student is actively enrolled in the course offering's class
     */
    public static function isStudentEnrolledInCourseOffering(int $studentId, int $courseOfferingId): bool
    {
        $sql = "SELECT se.id
                FROM student_enrollments se
                JOIN course_offerings co ON co.class_id = se.class_id
                WHERE se.student_id = :student_id
                  AND co.id = :course_offering_id
                  AND se.status = 'active'
                LIMIT 1";

        $row = self::fetchOne($sql, [
            'student_id' => $studentId,
            'course_offering_id' => $courseOfferingId,
        ]);

        return $row !== null;
    }

    /**
     * Lecturer view: Get progress summary for all students enrolled in a course offering
     */
    public static function getCourseStudentProgressOverview(int $courseOfferingId): array
    {
        $sql = "SELECT sp.id AS student_id, sp.index_number, u.first_name, u.last_name, u.email,
                       (SELECT COUNT(l.id) FROM lessons l JOIN course_modules cm ON cm.id = l.module_id WHERE cm.course_offering_id = :co_id AND l.is_published = 1) AS total_lessons,
                       (SELECT COUNT(slp.id) FROM student_lesson_progress slp JOIN lessons l ON l.id = slp.lesson_id JOIN course_modules cm ON cm.id = l.module_id WHERE cm.course_offering_id = :co_id AND slp.student_id = sp.id AND slp.is_completed = 1) AS completed_lessons,
                       (SELECT COALESCE(SUM(slp.time_spent_seconds), 0) FROM student_lesson_progress slp JOIN lessons l ON l.id = slp.lesson_id JOIN course_modules cm ON cm.id = l.module_id WHERE cm.course_offering_id = :co_id AND slp.student_id = sp.id) AS total_time_seconds
                FROM student_enrollments se
                JOIN student_profiles sp ON sp.id = se.student_id
                JOIN users u ON u.id = sp.user_id
                JOIN course_offerings co ON co.class_id = se.class_id
                WHERE co.id = :co_id AND se.status = 'active'
                ORDER BY u.last_name ASC, u.first_name ASC";

        $rows = self::fetchAll($sql, ['co_id' => $courseOfferingId]);

        foreach ($rows as &$row) {
            $total = (int)$row['total_lessons'];
            $completed = (int)$row['completed_lessons'];
            $row['percentage'] = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;
        }

        return $rows;
    }
}
