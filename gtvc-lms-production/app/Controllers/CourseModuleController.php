<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\CourseModule;
use App\Models\CourseOffering;
use App\Models\LessonProgress;
use App\Models\AuditLog;

class CourseModuleController
{
    /**
     * Get modules for a course offering
     */
    public function getModules(Request $request, int $offeringId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        $offering = CourseOffering::getOfferingById($offeringId);
        if (!$offering) {
            Response::error("Course offering not found", 404);
        }

        // Student access check: must be actively enrolled
        if (in_array('student', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true)) {
            $studentProfileId = $currentUser['profile']['id'] ?? 0;
            if (!LessonProgress::isStudentEnrolledInCourseOffering((int)$studentProfileId, $offeringId)) {
                AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'course_offerings', $offeringId, ['reason' => 'Student not enrolled in course offering']);
                Response::error("Forbidden: You are not enrolled in this course offering", 403);
            }
            // Students only see published lessons inside modules
            $modules = CourseModule::getModulesByOffering($offeringId, true);
        } else {
            // Lecturers / Admins see all modules
            $modules = CourseModule::getModulesByOffering($offeringId, false);
        }

        Response::json(['data' => $modules]);
    }

    /**
     * Create module
     */
    public function createModule(Request $request, array $params = []): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $body = $request->getBody();
        $offeringId = (int)($params['offering_id'] ?? $params['id'] ?? $body['course_offering_id'] ?? $_POST['course_offering_id'] ?? 1);

        $title = trim((string)($_POST['title'] ?? $body['title'] ?? 'Module 1: Introduction'));
        $description = $_POST['description'] ?? $body['description'] ?? null;

        $moduleId = CourseModule::createModule([
            'course_offering_id' => $offeringId,
            'title' => $title,
            'description' => $description,
            'sequence_order' => (int)($_POST['sequence_order'] ?? $body['sequence_order'] ?? 1),
        ]);

        AuditLog::log($currentUser['id'], 'module.created', 'course_modules', $moduleId, [
            'course_offering_id' => $offeringId,
            'title' => $title
        ]);

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        $isJson = str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');

        if (!$isJson && $_SERVER['REQUEST_METHOD'] === 'POST') {
            \App\Core\Session::setFlash('success', 'New Course Learning Module created successfully!');
            Response::redirect('/lecturer/modules');
        } else {
            Response::json(['message' => 'Course module created successfully', 'id' => $moduleId], 201);
        }
    }

    /**
     * Update module
     */
    public function updateModule(Request $request, int $moduleId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $module = CourseModule::getModuleById($moduleId);
        if (!$module) {
            Response::error("Course module not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$module['course_offering_id'], 'module.update');

        $body = $request->getBody();
        CourseModule::updateModule($moduleId, $body);

        AuditLog::log($currentUser['id'], 'module.updated', 'course_modules', $moduleId, $body);

        Response::json(['message' => 'Course module updated successfully']);
    }

    /**
     * Delete module
     */
    public function deleteModule(Request $request, int $moduleId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $module = CourseModule::getModuleById($moduleId);
        if (!$module) {
            Response::error("Course module not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$module['course_offering_id'], 'module.delete');

        CourseModule::deleteModule($moduleId);

        AuditLog::log($currentUser['id'], 'module.deleted', 'course_modules', $moduleId, [
            'title' => $module['title'],
            'course_offering_id' => $module['course_offering_id']
        ]);

        Response::json(['message' => 'Course module deleted successfully']);
    }

    /**
     * Reorder modules
     */
    public function reorderModules(Request $request, int $offeringId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $this->verifyLecturerOrAdminCourseAccess($currentUser, $offeringId, 'module.update');

        $body = $request->getBody();
        if (empty($body['ordered_module_ids']) || !is_array($body['ordered_module_ids'])) {
            Response::error("Validation Error: 'ordered_module_ids' array is required", 422);
        }

        CourseModule::reorderModules($offeringId, $body['ordered_module_ids']);

        Response::json(['message' => 'Course modules reordered successfully']);
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

        // Check if user is primary lecturer assigned to offering
        $staffProfileId = $currentUser['profile']['id'] ?? 0;
        $isPrimaryLecturer = ($offering['primary_lecturer_id'] && (int)$offering['primary_lecturer_id'] === (int)$staffProfileId);

        // Check if user is HOD of department
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
            AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'course_offerings', $offeringId, [
                'required_permission' => $permission,
                'reason' => 'User is neither assigned primary lecturer nor department HOD'
            ]);
            Response::error("Forbidden: You do not have authorization to manage content for this course offering", 403);
        }
    }
}
