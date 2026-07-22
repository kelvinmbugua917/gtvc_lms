<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\Student;
use App\Models\AuditLog;

class StudentController extends Controller
{
    /**
     * GET /api/v1/students
     */
    public function getStudents(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        $departmentId = $request->get('department_id') ? (int)$request->get('department_id') : null;
        $search = $request->get('search') ? (string)$request->get('search') : null;

        // HOD Departmental Isolation: If HOD, enforce department scope
        if (in_array('admin', $roleNames, true) && !in_array('super_admin', $roleNames, true)) {
            $userDeptIds = array_column($currentUser['departments'], 'id');
            if (!empty($userDeptIds) && $departmentId === null) {
                $departmentId = (int)$userDeptIds[0];
            }
        }

        $students = Student::getAllStudents($departmentId, $search);
        Response::json($students, 'Student directory retrieved successfully');
    }

    /**
     * GET /api/v1/students/{id}
     */
    public function getStudentById(Request $request, array $params = []): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $studentId = (int)($params['id'] ?? $request->get('id'));

        $student = Student::getStudentById($studentId);
        if (!$student) {
            Response::error("Student record not found", 404);
        }

        // IDOR / BOLA Check: Student can only view their own profile unless staff
        AuthMiddleware::requireSelfOrPermission($request, $student['user_id'], 'student.manage');

        Response::json($student, 'Student profile retrieved successfully');
    }

    /**
     * POST /api/v1/students
     */
    public function createStudent(Request $request): void
    {
        $currentUser = AuthMiddleware::requirePermission($request, 'user.manage');

        $data = $request->getJsonBody();

        // Validate required fields
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
            Response::error("Missing required fields: first_name, last_name, email", 422);
        }

        // Validate index number uniqueness if provided
        if (!empty($data['index_number'])) {
            if (Student::existsIndexNumber($data['index_number'])) {
                Response::error("Index number '{$data['index_number']}' is already assigned to another student", 409);
            }
        }

        $profileId = Student::createStudent(
            [
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name']),
                'email' => strtolower(trim($data['email'])),
                'phone' => $data['phone'] ?? null,
                'registration_number' => $data['registration_number'] ?? null,
                'national_id' => $data['national_id'] ?? null,
                'password' => $data['password'] ?? 'password',
            ],
            [
                'index_number' => $data['index_number'] ?? null,
                'gender' => $data['gender'] ?? 'male',
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'address' => $data['address'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_phone' => $data['guardian_phone'] ?? null,
                'cbet_reg_no' => $data['cbet_reg_no'] ?? null,
            ]
        );

        AuditLog::log(
            $currentUser['id'],
            'student.created',
            'student_profiles',
            $profileId,
            ['email' => $data['email'], 'index_number' => $data['index_number'] ?? null]
        );

        $newStudent = Student::getStudentById($profileId);
        Response::json($newStudent, 'Student profile and account created successfully', 201);
    }
}
