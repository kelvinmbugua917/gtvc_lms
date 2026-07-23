<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\LessonProgress;
use App\Models\Lesson;
use App\Models\CourseOffering;
use App\Models\AuditLog;

class LessonProgressController
{
    /**
     * Get lesson progress for authenticated student
     */
    public function getLessonProgress(Request $request, int $lessonId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $lesson = Lesson::getLessonById($lessonId);
        if (!$lesson) {
            Response::error("Lesson not found", 404);
        }

        $studentProfileId = $currentUser['profile']['id'] ?? 0;
        if (!$studentProfileId) {
            Response::error("User does not have an active student profile record", 400);
        }

        $progress = LessonProgress::getProgress((int)$studentProfileId, $lessonId);
        Response::json(['data' => $progress]);
    }

    /**
     * Get overall course completion progress for authenticated student
     */
    public function getCourseProgress(Request $request, int $offeringId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $studentProfileId = $currentUser['profile']['id'] ?? 0;
        if (!$studentProfileId) {
            Response::error("User does not have an active student profile record", 400);
        }

        $summary = LessonProgress::getStudentCourseProgress((int)$studentProfileId, $offeringId);
        Response::json(['data' => $summary]);
    }

    /**
     * Save student lesson completion state
     * Strictly enforces that students can ONLY modify their OWN progress
     */
    public function saveLessonProgress(Request $request, int $lessonId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        $lesson = Lesson::getLessonById($lessonId);
        if (!$lesson) {
            Response::error("Lesson not found", 404);
        }

        $body = $request->getBody();

        // Check target student ID
        $targetStudentId = isset($body['student_id']) ? (int)$body['student_id'] : 0;
        $currentStudentProfileId = (int)($currentUser['profile']['id'] ?? 0);

        // Anti-IDOR / Anti-BOLA check
        if ($targetStudentId > 0 && $targetStudentId !== $currentStudentProfileId) {
            if (!in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true)) {
                AuditLog::log($currentUser['id'], 'auth.idor_blocked', 'student_lesson_progress', null, [
                    'target_student_id' => $targetStudentId,
                    'current_student_profile_id' => $currentStudentProfileId,
                    'lesson_id' => $lessonId,
                    'reason' => 'Student attempted to modify another student\'s lesson progress record'
                ]);
                Response::error("Forbidden: You cannot modify another student's progress record", 403);
            }
        } else {
            $targetStudentId = $currentStudentProfileId;
        }

        if ($targetStudentId <= 0) {
            Response::error("No valid student profile found for progress tracking", 400);
        }

        // Verify student enrollment in course
        $offeringId = (int)$lesson['course_offering_id'];
        if (!LessonProgress::isStudentEnrolledInCourseOffering($targetStudentId, $offeringId)) {
            Response::error("Forbidden: Student is not enrolled in this course offering", 403);
        }

        $isCompleted = isset($body['is_completed']) ? (bool)$body['is_completed'] : true;
        $timeSpentSeconds = isset($body['time_spent_seconds']) ? (int)$body['time_spent_seconds'] : 0;

        LessonProgress::saveProgress($targetStudentId, $lessonId, $isCompleted, $timeSpentSeconds);

        Response::json([
            'message' => 'Lesson progress saved successfully',
            'lesson_id' => $lessonId,
            'is_completed' => $isCompleted
        ]);
    }

    /**
     * Lecturer view: Get student progress overview table for a course offering
     */
    public function getCourseProgressOverview(Request $request, int $offeringId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        $offering = CourseOffering::getOfferingById($offeringId);
        if (!$offering) {
            Response::error("Course offering not found", 404);
        }

        // Authorization check: Primary lecturer, HOD, or Admin/Super Admin
        $staffProfileId = $currentUser['profile']['id'] ?? 0;
        $isPrimaryLecturer = ($offering['primary_lecturer_id'] && (int)$offering['primary_lecturer_id'] === (int)$staffProfileId);

        $isDepartmentHod = false;
        if (isset($currentUser['departments']) && is_array($currentUser['departments'])) {
            foreach ($currentUser['departments'] as $dept) {
                if ((int)$dept['id'] === (int)$offering['department_id'] && !empty($dept['is_head_of_department'])) {
                    $isDepartmentHod = true;
                    break;
                }
            }
        }

        $isAdmin = in_array('super_admin', $roleNames, true) || in_array('admin', $roleNames, true);

        if (!$isPrimaryLecturer && !$isDepartmentHod && !$isAdmin) {
            Response::error("Forbidden: You do not have permission to view progress overview for this course", 403);
        }

        $overview = LessonProgress::getCourseStudentProgressOverview($offeringId);
        Response::json(['data' => $overview]);
    }
}
