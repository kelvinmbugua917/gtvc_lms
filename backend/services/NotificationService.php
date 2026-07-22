<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Model;
use App\Models\Notification;

class NotificationService extends Model
{
    /**
     * Get user IDs of all enrolled students in a course offering
     */
    public static function getEnrolledStudentUserIds(int $courseOfferingId): array
    {
        $sql = "SELECT DISTINCT s.user_id
                FROM enrollments e
                JOIN course_offerings co ON co.class_id = e.class_id
                JOIN student_profiles sp ON sp.id = e.student_id
                JOIN users s ON s.id = sp.user_id
                WHERE co.id = :offering_id AND e.status = 'active'";

        $rows = self::fetchAll($sql, ['offering_id' => $courseOfferingId]);
        return array_map('intval', array_column($rows, 'user_id'));
    }

    /**
     * Get user IDs for announcement target audience
     */
    public static function getTargetAudienceUserIds(array $announcement): array
    {
        $role = $announcement['target_role'] ?? 'all';
        $departmentId = !empty($announcement['target_department_id']) ? (int)$announcement['target_department_id'] : null;

        $sql = "SELECT DISTINCT u.id
                FROM users u
                LEFT JOIN user_roles ur ON ur.user_id = u.id
                LEFT JOIN roles r ON r.id = ur.role_id
                LEFT JOIN user_departments ud ON ud.user_id = u.id
                WHERE u.is_active = 1";

        $params = [];

        if ($role !== 'all') {
            $sql .= " AND r.name = :role_name";
            $params['role_name'] = $role;
        }

        if ($departmentId !== null) {
            $sql .= " AND ud.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }

        $rows = self::fetchAll($sql, $params);
        return array_map('intval', array_column($rows, 'id'));
    }

    /**
     * Notify enrolled students when new lesson/material is published
     */
    public static function notifyLessonPublished(int $courseOfferingId, string $lessonTitle, int $lessonId): int
    {
        try {
            $studentUserIds = self::getEnrolledStudentUserIds($courseOfferingId);
            if (empty($studentUserIds)) {
                return 0;
            }

            return Notification::createBatchNotifications($studentUserIds, [
                'type' => 'academic_material',
                'title' => 'New Learning Content Published',
                'message' => "New lesson '{$lessonTitle}' has been made available in your course portal.",
                'priority' => 'normal',
                'related_entity_type' => 'lesson',
                'related_entity_id' => $lessonId,
            ]);
        } catch (\Throwable $e) {
            error_log("NotificationService Error (LessonPublished): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Notify enrolled students when assignment is created/released
     */
    public static function notifyAssignmentCreated(int $courseOfferingId, string $assignmentTitle, int $assignmentId, ?string $dueDate): int
    {
        try {
            $studentUserIds = self::getEnrolledStudentUserIds($courseOfferingId);
            if (empty($studentUserIds)) {
                return 0;
            }

            $dueFormatted = $dueDate ? date('M j, Y H:i', strtotime($dueDate)) : 'TBA';

            return Notification::createBatchNotifications($studentUserIds, [
                'type' => 'academic_assignment',
                'title' => 'New Assignment Released',
                'message' => "Assignment '{$assignmentTitle}' is now open. Deadline: {$dueFormatted}.",
                'priority' => 'important',
                'related_entity_type' => 'assignment',
                'related_entity_id' => $assignmentId,
            ]);
        } catch (\Throwable $e) {
            error_log("NotificationService Error (AssignmentCreated): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Notify student when assignment submission is graded
     */
    public static function notifyAssignmentGraded(int $studentUserId, string $assignmentTitle, float $marks, float $maxMarks): int
    {
        try {
            return Notification::createNotification([
                'user_id' => $studentUserId,
                'type' => 'academic_grade',
                'title' => 'Assignment Submission Graded',
                'message' => "Your submission for '{$assignmentTitle}' has been graded: {$marks}/{$maxMarks} marks.",
                'priority' => 'important',
                'related_entity_type' => 'assignment',
                'related_entity_id' => 0
            ]);
        } catch (\Throwable $e) {
            error_log("NotificationService Error (AssignmentGraded): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Notify enrolled students when quiz becomes available
     */
    public static function notifyQuizAvailable(int $courseOfferingId, string $quizTitle, int $quizId): int
    {
        try {
            $studentUserIds = self::getEnrolledStudentUserIds($courseOfferingId);
            if (empty($studentUserIds)) {
                return 0;
            }

            return Notification::createBatchNotifications($studentUserIds, [
                'type' => 'academic_quiz',
                'title' => 'New Online Assessment Quiz Available',
                'message' => "Quiz '{$quizTitle}' is ready for attempts. Please log in to take the assessment.",
                'priority' => 'important',
                'related_entity_type' => 'quiz',
                'related_entity_id' => $quizId,
            ]);
        } catch (\Throwable $e) {
            error_log("NotificationService Error (QuizAvailable): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Notify enrolled students when course grade is published
     */
    public static function notifyGradesPublished(int $courseOfferingId, string $unitCode): int
    {
        try {
            $studentUserIds = self::getEnrolledStudentUserIds($courseOfferingId);
            if (empty($studentUserIds)) {
                return 0;
            }

            return Notification::createBatchNotifications($studentUserIds, [
                'type' => 'academic_grade',
                'title' => 'Official Course Grades Published',
                'message' => "Final academic grades for unit '{$unitCode}' have been published by the lecturer.",
                'priority' => 'important',
                'related_entity_type' => 'grade',
                'related_entity_id' => $courseOfferingId,
            ]);
        } catch (\Throwable $e) {
            error_log("NotificationService Error (GradesPublished): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Notify student if attendance falls into Warning or Critical threshold
     */
    public static function notifyAttendanceWarning(int $studentUserId, string $unitCode, float $percentage, string $status): int
    {
        try {
            $priority = ($status === 'CRITICAL') ? 'urgent' : 'important';
            $title = ($status === 'CRITICAL') 
                ? 'CRITICAL ATTENDANCE ALERT: Exam Disqualification Risk' 
                : 'Attendance Threshold Warning';
            
            $message = "Your calculated attendance for unit '{$unitCode}' is currently {$percentage}%. "
                     . ($status === 'CRITICAL' 
                        ? 'This is below the 60% requirement. You are at risk of exam debarment.' 
                        : 'This is below the 75% target threshold. Please report to your workshop lecturer.');

            return Notification::createNotification([
                'user_id' => $studentUserId,
                'type' => 'attendance_warning',
                'title' => $title,
                'message' => $message,
                'priority' => $priority,
                'related_entity_type' => 'attendance',
                'related_entity_id' => 0
            ]);
        } catch (\Throwable $e) {
            error_log("NotificationService Error (AttendanceWarning): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Notify target audience when an announcement is published
     */
    public static function notifyAnnouncementPublished(int $announcementId, array $announcement): int
    {
        try {
            $targetUserIds = self::getTargetAudienceUserIds($announcement);
            if (empty($targetUserIds)) {
                return 0;
            }

            return Notification::createBatchNotifications($targetUserIds, [
                'type' => 'announcement',
                'title' => 'New Announcement: ' . $announcement['title'],
                'message' => substr(strip_tags($announcement['content']), 0, 180) . '...',
                'priority' => $announcement['priority'] ?? 'normal',
                'related_entity_type' => 'announcement',
                'related_entity_id' => $announcementId,
            ]);
        } catch (\Throwable $e) {
            error_log("NotificationService Error (AnnouncementPublished): " . $e->getMessage());
            return 0;
        }
    }
}
