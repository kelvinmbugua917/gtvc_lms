<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\Lesson;
use App\Models\CourseModule;
use App\Models\CourseOffering;
use App\Models\LessonProgress;
use App\Models\AuditLog;

class LessonController
{
    /**
     * Get lessons for a module
     */
    public function getLessons(Request $request, int $moduleId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        $module = CourseModule::getModuleById($moduleId);
        if (!$module) {
            Response::error("Course module not found", 404);
        }

        $offeringId = (int)$module['course_offering_id'];

        if (in_array('student', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true)) {
            $studentProfileId = $currentUser['profile']['id'] ?? 0;
            if (!LessonProgress::isStudentEnrolledInCourseOffering((int)$studentProfileId, $offeringId)) {
                Response::error("Forbidden: You are not enrolled in this course offering", 403);
            }
            $lessons = Lesson::getLessonsByModule($moduleId, true);
        } else {
            $lessons = Lesson::getLessonsByModule($moduleId, false);
        }

        Response::json(['data' => $lessons]);
    }

    /**
     * Get lesson details
     */
    public function getLessonDetails(Request $request, int $lessonId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        $lesson = Lesson::getLessonById($lessonId);
        if (!$lesson) {
            Response::error("Lesson not found", 404);
        }

        $offeringId = (int)$lesson['course_offering_id'];

        if (in_array('student', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true)) {
            $studentProfileId = $currentUser['profile']['id'] ?? 0;
            if (!LessonProgress::isStudentEnrolledInCourseOffering((int)$studentProfileId, $offeringId)) {
                Response::error("Forbidden: You are not enrolled in this course offering", 403);
            }
            if (!$lesson['is_published']) {
                Response::error("Forbidden: Lesson is not published yet", 403);
            }
        }

        Response::json(['data' => $lesson]);
    }

    /**
     * Create lesson in a module
     */
    public function createLesson(Request $request, int $moduleId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $module = CourseModule::getModuleById($moduleId);
        if (!$module) {
            Response::error("Course module not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$module['course_offering_id'], 'lesson.create');

        $body = $request->getBody();
        if (empty($body['title'])) {
            Response::error("Validation Error: 'title' is required for lesson", 422);
        }

        $lessonId = Lesson::createLesson([
            'module_id' => $moduleId,
            'title' => $body['title'],
            'content_type' => $body['content_type'] ?? 'text',
            'text_content' => $body['text_content'] ?? null,
            'duration_minutes' => $body['duration_minutes'] ?? 30,
            'sequence_order' => $body['sequence_order'] ?? null,
            'is_published' => $body['is_published'] ?? 1,
        ]);

        AuditLog::log($currentUser['id'], 'lesson.created', 'lessons', $lessonId, [
            'module_id' => $moduleId,
            'title' => $body['title'],
            'content_type' => $body['content_type'] ?? 'text'
        ]);

        Response::json(['message' => 'Lesson created successfully', 'id' => $lessonId], 201);
    }

    /**
     * Update lesson
     */
    public function updateLesson(Request $request, int $lessonId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $lesson = Lesson::getLessonById($lessonId);
        if (!$lesson) {
            Response::error("Lesson not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$lesson['course_offering_id'], 'lesson.update');

        $body = $request->getBody();
        Lesson::updateLesson($lessonId, $body);

        AuditLog::log($currentUser['id'], 'lesson.updated', 'lessons', $lessonId, $body);

        Response::json(['message' => 'Lesson updated successfully']);
    }

    /**
     * Delete lesson
     */
    public function deleteLesson(Request $request, int $lessonId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $lesson = Lesson::getLessonById($lessonId);
        if (!$lesson) {
            Response::error("Lesson not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$lesson['course_offering_id'], 'lesson.delete');

        Lesson::deleteLesson($lessonId);

        AuditLog::log($currentUser['id'], 'lesson.deleted', 'lessons', $lessonId, [
            'title' => $lesson['title'],
            'module_id' => $lesson['module_id']
        ]);

        Response::json(['message' => 'Lesson deleted successfully']);
    }

    /**
     * Reorder lessons
     */
    public function reorderLessons(Request $request, int $moduleId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $module = CourseModule::getModuleById($moduleId);
        if (!$module) {
            Response::error("Course module not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$module['course_offering_id'], 'lesson.update');

        $body = $request->getBody();
        if (empty($body['ordered_lesson_ids']) || !is_array($body['ordered_lesson_ids'])) {
            Response::error("Validation Error: 'ordered_lesson_ids' array is required", 422);
        }

        Lesson::reorderLessons($moduleId, $body['ordered_lesson_ids']);

        Response::json(['message' => 'Lessons reordered successfully']);
    }

    /**
     * Helper to verify if user has rights to modify course offering content
     */
    private function verifyLecturerOrAdminCourseAccess(array $currentUser, int $offeringId, string $permission): void
    {
        $roleNames = array_column($currentUser['roles'], 'name');
        if (in_array('super_admin', $roleNames, true) || in_array('admin', $roleNames, true)) {
            return;
        }

        $offering = CourseOffering::getOfferingById($offeringId);
        if (!$offering) {
            Response::error("Course offering not found", 404);
        }

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

        $hasPerm = in_array($permission, $currentUser['permissions'], true);

        if (!$isPrimaryLecturer && !$isDepartmentHod && !$hasPerm) {
            Response::error("Forbidden: You do not have authorization to manage lessons for this course offering", 403);
        }
    }
}
