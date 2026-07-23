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
        $code = trim((string)($_POST['code'] ?? $body['code'] ?? ''));
        $name = trim((string)($_POST['name'] ?? $body['name'] ?? ''));
        $hodId = (int)($_POST['hod_id'] ?? $body['hod_id'] ?? 0);

        if (!empty($code) && !empty($name)) {
            Department::createDepartment([
                'code' => $code,
                'name' => $name,
                'hod_id' => $hodId,
            ]);
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
