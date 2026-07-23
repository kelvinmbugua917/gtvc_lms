<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\StudentCourseGrade;
use App\Models\CourseOffering;
use App\Models\LessonProgress;
use App\Models\AuditLog;

class GradebookController
{
    /**
     * Get grades for a course offering
     */
    public function getCourseGrades(Request $request, int $offeringId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        $offering = CourseOffering::getOfferingById($offeringId);
        if (!$offering) {
            Response::error("Course offering not found", 404);
        }

        $isStudent = in_array('student', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true);

        if ($isStudent) {
            $studentProfileId = $currentUser['profile']['id'] ?? 0;
            if (!LessonProgress::isStudentEnrolledInCourseOffering((int)$studentProfileId, $offeringId)) {
                AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'course_offerings', $offeringId);
                Response::error("Forbidden: You are not enrolled in this course offering", 403);
            }

            $myGrade = StudentCourseGrade::getStudentGradeForOffering($offeringId, (int)$currentUser['id']);
            if (!$myGrade || $myGrade['is_published'] != 1) {
                Response::json(['data' => null, 'message' => "Grades for this course offering are not yet published"]);
                return;
            }

            Response::json(['data' => $myGrade]);
        } else {
            // Lecturers / HODs / Admins see full gradebook
            $this->verifyLecturerOrAdminCourseAccess($currentUser, $offeringId, 'grade.view');
            $grades = StudentCourseGrade::getGradesByOffering($offeringId, false);
            Response::json(['data' => $grades]);
        }
    }

    /**
     * Update/Save student grade for course offering
     */
    public function saveGrade(Request $request, int $offeringId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $offering = CourseOffering::getOfferingById($offeringId);
        if (!$offering) {
            Response::error("Course offering not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, $offeringId, 'grade.submit');

        $body = $request->getBody();
        if (empty($body['student_id'])) {
            Response::error("Validation Error: 'student_id' is required", 422);
        }

        $courseworkScore = (float)($body['coursework_score'] ?? 0.0);
        $examScore = (float)($body['exam_score'] ?? 0.0);

        if ($courseworkScore < 0 || $courseworkScore > 100 || $examScore < 0 || $examScore > 100) {
            Response::error("Validation Error: Scores must be between 0 and 100", 422);
        }

        StudentCourseGrade::saveStudentGrade([
            'student_id' => (int)$body['student_id'],
            'course_offering_id' => $offeringId,
            'coursework_score' => $courseworkScore,
            'exam_score' => $examScore,
            'is_published' => $body['is_published'] ?? 0
        ]);

        AuditLog::log($currentUser['id'], 'grade.update', 'student_course_grades', 0, [
            'student_id' => $body['student_id'],
            'offering_id' => $offeringId,
            'coursework_score' => $courseworkScore,
            'exam_score' => $examScore
        ]);

        Response::json(['message' => "Student grade recorded successfully"]);
    }

    /**
     * Publish all grades for a course offering
     */
    public function publishGrades(Request $request, int $offeringId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $offering = CourseOffering::getOfferingById($offeringId);
        if (!$offering) {
            Response::error("Course offering not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, $offeringId, 'grade.publish');

        StudentCourseGrade::publishGradesForOffering($offeringId);

        AuditLog::log($currentUser['id'], 'grade.publish', 'course_offerings', $offeringId);

        Response::json(['message' => "Grades published successfully for all enrolled students"]);
    }

    /**
     * Student views all their published grades across all enrolled courses
     */
    public function getStudentGrades(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        if (!in_array('student', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true)) {
            Response::error("Forbidden: Endpoint is reserved for students", 403);
        }

        $grades = StudentCourseGrade::getAllStudentGrades((int)$currentUser['id']);
        Response::json(['data' => $grades]);
    }

    /**
     * Helper for lecturer / admin course scoping
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

        if (in_array('lecturer', $roleNames, true)) {
            if ((int)$offering['primary_lecturer_id'] !== (int)$currentUser['id']) {
                AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'course_offerings', $offeringId, ['permission' => $permission]);
                Response::error("Forbidden: You are not assigned as lecturer for this course offering", 403);
            }
            return;
        }

        if (in_array('hod', $roleNames, true)) {
            $userDeptId = $currentUser['department_id'] ?? 0;
            if ((int)$offering['department_id'] !== (int)$userDeptId) {
                AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'course_offerings', $offeringId, ['permission' => $permission]);
                Response::error("Forbidden: Course offering belongs to another department", 403);
            }
            return;
        }

        Response::error("Forbidden: Insufficient privileges", 403);
    }
}
