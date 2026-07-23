<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\FileUpload;
use App\Middleware\AuthMiddleware;
use App\Models\LearningMaterial;
use App\Models\Lesson;
use App\Models\CourseOffering;
use App\Models\LessonProgress;
use App\Models\AuditLog;

class LearningMaterialController
{
    /**
     * Get learning materials for a lesson
     */
    public function getMaterials(Request $request, int $lessonId): void
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

        $materials = LearningMaterial::getMaterialsByLesson($lessonId);
        Response::json(['data' => $materials]);
    }

    /**
     * Upload learning material or add external URL
     */
    public function createMaterial(Request $request, int $lessonId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $lesson = Lesson::getLessonById($lessonId);
        if (!$lesson) {
            Response::error("Lesson not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$lesson['course_offering_id'], 'material.upload');

        $body = $request->getBody();
        $files = $request->getFiles();

        $title = trim($body['title'] ?? '');
        if (empty($title)) {
            Response::error("Validation Error: 'title' is required for learning material", 422);
        }

        $filePath = '';
        $fileType = 'application/octet-stream';
        $fileSizeBytes = null;
        $externalUrl = $body['external_url'] ?? null;

        // Handle File Upload if provided
        if (isset($files['file']) && is_array($files['file'])) {
            $validation = FileUpload::validate($files['file']);
            if (!$validation['valid']) {
                Response::error("Upload Error: " . $validation['error'], 422);
            }

            $filePath = FileUpload::store($files['file'], $validation);
            $fileType = $validation['mime_type'];
            $fileSizeBytes = $validation['size'];
        } elseif (!empty($externalUrl)) {
            $fileType = 'external_resource';
        } else {
            Response::error("Validation Error: Either a valid file upload or an external URL must be supplied", 422);
        }

        $materialId = LearningMaterial::createMaterial([
            'lesson_id' => $lessonId,
            'title' => $title,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_size_bytes' => $fileSizeBytes,
            'external_url' => $externalUrl,
        ]);

        AuditLog::log($currentUser['id'], 'material.uploaded', 'learning_materials', $materialId, [
            'lesson_id' => $lessonId,
            'title' => $title,
            'file_type' => $fileType
        ]);

        Response::json(['message' => 'Learning material added successfully', 'id' => $materialId], 201);
    }

    /**
     * Delete learning material
     */
    public function deleteMaterial(Request $request, int $materialId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $material = LearningMaterial::getMaterialById($materialId);
        if (!$material) {
            Response::error("Learning material not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$material['course_offering_id'], 'material.delete');

        LearningMaterial::deleteMaterial($materialId);

        AuditLog::log($currentUser['id'], 'material.deleted', 'learning_materials', $materialId, [
            'title' => $material['title'],
            'lesson_id' => $material['lesson_id']
        ]);

        Response::json(['message' => 'Learning material deleted successfully']);
    }

    /**
     * Secure file download endpoint
     */
    public function downloadMaterial(Request $request, int $materialId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        $material = LearningMaterial::getMaterialById($materialId);
        if (!$material) {
            Response::error("Learning material not found", 404);
        }

        $offeringId = (int)$material['course_offering_id'];

        // Enforce student enrollment boundaries
        if (in_array('student', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true)) {
            $studentProfileId = $currentUser['profile']['id'] ?? 0;
            if (!LessonProgress::isStudentEnrolledInCourseOffering((int)$studentProfileId, $offeringId)) {
                AuditLog::log($currentUser['id'], 'auth.unauthorized_download', 'learning_materials', $materialId, [
                    'reason' => 'Student attempted unauthorized download of material in non-enrolled course'
                ]);
                Response::error("Forbidden: You are not enrolled in this course offering", 403);
            }
        }

        if (empty($material['file_path'])) {
            Response::error("Material has no physical file stored on server (external URL)", 400);
        }

        AuditLog::log($currentUser['id'], 'material.downloaded', 'learning_materials', $materialId, [
            'title' => $material['title']
        ]);

        FileUpload::download($material['file_path'], $material['title'], $material['file_type']);
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
            Response::error("Forbidden: You do not have authorization to manage learning materials for this course offering", 403);
        }
    }
}
