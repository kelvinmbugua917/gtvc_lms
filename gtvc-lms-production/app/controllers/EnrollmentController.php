<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\ClassCohort;
use App\Models\Department;
use App\Models\AuditLog;

class EnrollmentController extends Controller
{
    /**
     * GET /api/v1/enrollments
     */
    public function getEnrollments(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        $studentId = $request->get('student_id') ? (int)$request->get('student_id') : null;
        $departmentId = $request->get('department_id') ? (int)$request->get('department_id') : null;
        $status = $request->get('status') ? (string)$request->get('status') : null;

        // If user is a student, restrict viewing to their own enrollment records
        if (in_array('student', $roleNames, true) && !in_array('admin', $roleNames, true) && !in_array('super_admin', $roleNames, true)) {
            $studentProfile = Student::getStudentByUserId((int)$currentUser['id']);
            if (!$studentProfile) {
                Response::json([], 'No student profile found');
                return;
            }
            $studentId = (int)$studentProfile['student_profile_id'];
        }

        $enrollments = Enrollment::getAllEnrollments($studentId, $departmentId, $status);
        Response::json($enrollments, 'Enrollment records retrieved successfully');
    }

    /**
     * POST /api/v1/enrollments
     */
    public function createEnrollment(Request $request): void
    {
        // Must possess enrollment/academic administrative management permission
        $currentUser = AuthMiddleware::requirePermission($request, 'academic.manage');

        $data = $request->getJsonBody();

        if (empty($data['student_id']) || empty($data['class_id']) || empty($data['program_id']) || empty($data['intake_id'])) {
            Response::error("Missing required parameters: student_id, class_id, program_id, intake_id", 422);
        }

        $studentId = (int)$data['student_id'];
        $classId = (int)$data['class_id'];
        $programId = (int)$data['program_id'];
        $intakeId = (int)$data['intake_id'];

        // 1. Validate student exists
        $student = Student::getStudentById($studentId);
        if (!$student) {
            Response::error("Target student record not found", 404);
        }

        // 2. Validate class exists and matches program/intake context
        $classCohort = ClassCohort::getClassById($classId);
        if (!$classCohort) {
            Response::error("Target class/cohort record not found", 404);
        }

        if ((int)$classCohort['program_id'] !== $programId) {
            Response::error("Class '{$classCohort['name']}' does not belong to the selected program context", 422);
        }

        if ((int)$classCohort['intake_id'] !== $intakeId) {
            Response::error("Class '{$classCohort['name']}' does not match the selected intake context", 422);
        }

        // 3. Department isolation for HOD
        AuthMiddleware::requireDepartmentAccess($request, $classCohort['department_id']);

        // 4. Duplicate active enrollment check
        if (Enrollment::checkActiveEnrollmentExists($studentId)) {
            Response::error("Student already possesses an active academic enrollment record. Defer or discontinue existing enrollment prior to re-enrolling.", 409);
        }

        $enrollmentId = Enrollment::createEnrollment([
            'student_id' => $studentId,
            'class_id' => $classId,
            'program_id' => $programId,
            'intake_id' => $intakeId,
            'enrollment_date' => $data['enrollment_date'] ?? date('Y-m-d'),
            'status' => 'active',
        ]);

        AuditLog::log(
            $currentUser['id'],
            'enrollment.created',
            'student_enrollments',
            $enrollmentId,
            [
                'student_id' => $studentId,
                'class_id' => $classId,
                'program_id' => $programId,
            ]
        );

        $newEnrollment = Enrollment::getEnrollmentById($enrollmentId);
        Response::json($newEnrollment, 'Student enrollment recorded successfully', 201);
    }

    /**
     * PUT /api/v1/enrollments/{id}
     */
    public function updateEnrollment(Request $request, array $params = []): void
    {
        $currentUser = AuthMiddleware::requirePermission($request, 'academic.manage');
        $enrollmentId = (int)($params['id'] ?? $request->get('id'));

        $data = $request->getJsonBody();
        if (empty($data['status'])) {
            Response::error("Missing required parameter 'status'", 422);
        }

        $existing = Enrollment::getEnrollmentById($enrollmentId);
        if (!$existing) {
            Response::error("Enrollment record not found", 404);
        }

        // Department isolation for HOD
        AuthMiddleware::requireDepartmentAccess($request, $existing['department_id']);

        Enrollment::updateEnrollmentStatus($enrollmentId, (string)$data['status']);

        AuditLog::log(
            $currentUser['id'],
            'enrollment.status_updated',
            'student_enrollments',
            $enrollmentId,
            ['old_status' => $existing['status'], 'new_status' => $data['status']]
        );

        $updated = Enrollment::getEnrollmentById($enrollmentId);
        Response::json($updated, 'Enrollment status updated successfully');
    }
}
