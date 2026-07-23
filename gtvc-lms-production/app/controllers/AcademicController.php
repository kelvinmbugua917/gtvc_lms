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
