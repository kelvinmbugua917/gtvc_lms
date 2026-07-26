<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\ClassCohort;
use App\Models\Unit;
use App\Models\CourseOffering;

class AcademicController extends Controller
{
    /**
     * GET /api/v1/academic-years
     */
    public function getAcademicYears(Request $request): void
    {
        AuthMiddleware::authenticate($request);
        $years = AcademicYear::getAllYears();
        Response::json($years, 'Academic years retrieved successfully');
    }

    /**
     * GET /api/v1/intakes
     */
    public function getIntakes(Request $request): void
    {
        AuthMiddleware::authenticate($request);
        $academicYearId = $request->get('academic_year_id') ? (int)$request->get('academic_year_id') : null;
        $intakes = AcademicYear::getAllIntakes($academicYearId);
        Response::json($intakes, 'Intakes retrieved successfully');
    }

    /**
     * GET /api/v1/departments
     */
    public function getDepartments(Request $request): void
    {
        AuthMiddleware::authenticate($request);
        $departments = Department::getAllDepartments();
        Response::json($departments, 'Departments retrieved successfully');
    }

    /**
     * POST /api/v1/departments
     */
    public function createDepartment(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $body = $request->getBody();
        $id = (int)($_POST['id'] ?? $body['id'] ?? 0);
        $code = trim((string)($_POST['code'] ?? $body['code'] ?? ''));
        $name = trim((string)($_POST['name'] ?? $body['name'] ?? ''));
        $hodId = (int)($_POST['hod_id'] ?? $body['hod_id'] ?? 0);

        if (!empty($code) && !empty($name)) {
            $existingDept = Department::fetchOne("SELECT id FROM departments WHERE LOWER(code) = LOWER(:code)", ['code' => $code]);
            if ($id > 0) {
                $db = Department::getDb();
                $stmt = $db->prepare("UPDATE departments SET code = :code, name = :name, head_of_department_id = :hod_id WHERE id = :id");
                $stmt->execute([
                    'code' => $code,
                    'name' => $name,
                    'hod_id' => $hodId > 0 ? $hodId : null,
                    'id' => $id,
                ]);
            } else if ($existingDept) {
                $db = Department::getDb();
                $stmt = $db->prepare("UPDATE departments SET name = :name, head_of_department_id = :hod_id WHERE id = :id");
                $stmt->execute([
                    'name' => $name,
                    'hod_id' => $hodId > 0 ? $hodId : null,
                    'id' => $existingDept['id'],
                ]);
            } else {
                Department::createDepartment([
                    'code' => $code,
                    'name' => $name,
                    'hod_id' => $hodId,
                ]);
            }
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        $isJson = str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');

        if (!$isJson && $_SERVER['REQUEST_METHOD'] === 'POST') {
            \App\Core\Session::setFlash('success', 'Academic Department saved successfully!');
            Response::redirect('/admin/academic');
        } else {
            Response::json(['message' => 'Department created successfully'], 201);
        }
    }

    /**
     * POST /api/v1/programs
     */
    public function createProgram(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $body = $request->getBody();
        $id = (int)($_POST['id'] ?? $body['id'] ?? 0);
        $departmentId = (int)($_POST['department_id'] ?? $body['department_id'] ?? 0);
        $code = trim((string)($_POST['code'] ?? $body['code'] ?? ''));
        $name = trim((string)($_POST['name'] ?? $body['name'] ?? ''));
        $awardType = $_POST['award_type'] ?? $body['award_type'] ?? 'diploma';
        $durationMonths = (int)($_POST['duration_months'] ?? $body['duration_months'] ?? 24);

        if (!empty($code) && !empty($name) && $departmentId > 0) {
            $db = Department::getDb();
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE programs SET department_id = :dept_id, code = :code, name = :name, award_type = :award, duration_months = :dur WHERE id = :id");
                $stmt->execute([
                    'dept_id' => $departmentId,
                    'code' => $code,
                    'name' => $name,
                    'award' => $awardType,
                    'dur' => $durationMonths,
                    'id' => $id
                ]);
            } else {
                $stmt = $db->prepare("
                    INSERT INTO programs (department_id, code, name, award_type, duration_months, created_at)
                    VALUES (:dept_id, :code, :name, :award, :dur, NOW())
                    ON DUPLICATE KEY UPDATE name = VALUES(name), award_type = VALUES(award_type), duration_months = VALUES(duration_months)
                ");
                $stmt->execute([
                    'dept_id' => $departmentId,
                    'code' => $code,
                    'name' => $name,
                    'award' => $awardType,
                    'dur' => $durationMonths
                ]);
            }
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        $isJson = str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');

        if (!$isJson && $_SERVER['REQUEST_METHOD'] === 'POST') {
            \App\Core\Session::setFlash('success', 'Academic Program / Course saved successfully!');
            Response::redirect('/admin/academic');
        } else {
            Response::json(['message' => 'Program saved successfully'], 201);
        }
    }

    /**
     * POST /api/v1/classes
     */
    public function createClass(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $body = $request->getBody();
        $id = (int)($_POST['id'] ?? $body['id'] ?? 0);
        $programId = (int)($_POST['program_id'] ?? $body['program_id'] ?? 0);
        $intakeId = (int)($_POST['intake_id'] ?? $body['intake_id'] ?? 0);
        $code = trim((string)($_POST['code'] ?? $body['code'] ?? ''));
        $name = trim((string)($_POST['name'] ?? $body['name'] ?? ''));
        $yearOfStudy = (int)($_POST['year_of_study'] ?? $body['year_of_study'] ?? 1);
        $status = $_POST['status'] ?? $body['status'] ?? 'active';

        if (!empty($code) && !empty($name) && $programId > 0 && $intakeId > 0) {
            $db = ClassCohort::getDb();
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE classes SET program_id = :p_id, intake_id = :i_id, code = :code, name = :name, year_of_study = :yos, status = :status WHERE id = :id");
                $stmt->execute([
                    'p_id' => $programId,
                    'i_id' => $intakeId,
                    'code' => $code,
                    'name' => $name,
                    'yos' => $yearOfStudy,
                    'status' => $status,
                    'id' => $id
                ]);
            } else {
                $stmt = $db->prepare("
                    INSERT INTO classes (program_id, intake_id, code, name, year_of_study, status, created_at)
                    VALUES (:p_id, :i_id, :code, :name, :yos, :status, NOW())
                    ON DUPLICATE KEY UPDATE name = VALUES(name), year_of_study = VALUES(year_of_study), status = VALUES(status)
                ");
                $stmt->execute([
                    'p_id' => $programId,
                    'i_id' => $intakeId,
                    'code' => $code,
                    'name' => $name,
                    'yos' => $yearOfStudy,
                    'status' => $status
                ]);
            }
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        $isJson = str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');

        if (!$isJson && $_SERVER['REQUEST_METHOD'] === 'POST') {
            \App\Core\Session::setFlash('success', 'Class Group / Cohort saved successfully!');
            Response::redirect('/admin/academic');
        } else {
            Response::json(['message' => 'Class saved successfully'], 201);
        }
    }

    /**
     * GET /api/v1/programs
     */
    public function getPrograms(Request $request): void
    {
        AuthMiddleware::authenticate($request);
        $departmentId = $request->get('department_id') ? (int)$request->get('department_id') : null;
        $programs = Department::getAllPrograms($departmentId);
        Response::json($programs, 'Programs retrieved successfully');
    }

    /**
     * GET /api/v1/classes
     */
    public function getClasses(Request $request): void
    {
        AuthMiddleware::authenticate($request);
        $programId = $request->get('program_id') ? (int)$request->get('program_id') : null;
        $intakeId = $request->get('intake_id') ? (int)$request->get('intake_id') : null;
        $classes = ClassCohort::getAllClasses($programId, $intakeId);
        Response::json($classes, 'Classes retrieved successfully');
    }

    /**
     * GET /api/v1/units
     */
    public function getUnits(Request $request): void
    {
        AuthMiddleware::authenticate($request);
        $departmentId = $request->get('department_id') ? (int)$request->get('department_id') : null;
        $programId = $request->get('program_id') ? (int)$request->get('program_id') : null;
        $units = Unit::getAllUnits($departmentId, $programId);
        Response::json($units, 'Units retrieved successfully');
    }

    /**
     * GET /api/v1/course-offerings
     */
    public function getCourseOfferings(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $classId = $request->get('class_id') ? (int)$request->get('class_id') : null;
        
        $roleNames = array_column($currentUser['roles'], 'name');
        
        // Lecturer scoping: if user is a lecturer and not admin/super_admin, restrict to assigned offerings unless specified
        $lecturerId = null;
        if (in_array('lecturer', $roleNames, true) && !in_array('admin', $roleNames, true) && !in_array('super_admin', $roleNames, true)) {
            $lecturerId = isset($currentUser['profile']['id']) ? (int)$currentUser['profile']['id'] : null;
        }

        // Student scoping: if user is student, fetch offerings for student's class
        $studentUserId = null;
        if (in_array('student', $roleNames, true) && !in_array('admin', $roleNames, true) && !in_array('super_admin', $roleNames, true)) {
            $studentUserId = (int)$currentUser['id'];
        }

        $offerings = CourseOffering::getAllOfferings($classId, $lecturerId, $studentUserId);
        Response::json($offerings, 'Course offerings retrieved successfully');
    }
}
