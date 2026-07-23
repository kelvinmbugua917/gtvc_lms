<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\AttendanceRecord;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\AuditLog;

class AttendanceReportController
{
    /**
     * Get full course attendance matrix for lecturer or HOD
     */
    public function getCourseMatrix(Request $request, int $offeringId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $this->verifyCourseAccess($currentUser, $offeringId, 'attendance.report');

        $matrix = AttendanceRecord::getCourseAttendanceMatrix($offeringId);
        Response::json(['data' => $matrix]);
    }

    /**
     * Get department-wide attendance report
     */
    public function getDepartmentReport(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        $deptId = $request->getParam('department_id');

        if (in_array('hod', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true)) {
            $deptId = (int)($currentUser['department_id'] ?? 0);
        } else if (empty($deptId)) {
            $deptId = (int)($currentUser['department_id'] ?? 1);
        } else {
            $deptId = (int)$deptId;
        }

        $enrollments = Enrollment::getAllEnrollments(null, $deptId, 'Active');

        $studentSummaries = [];
        $warningCount = 0;
        $criticalCount = 0;
        $totalPracticalHours = 0.0;

        foreach ($enrollments as $enr) {
            $studentProfileId = (int)$enr['student_id'];
            $summary = AttendanceRecord::getStudentAttendanceSummary($studentProfileId);

            if ($summary['warning_level'] === 'warning') {
                $warningCount++;
            } else if ($summary['warning_level'] === 'critical') {
                $criticalCount++;
            }

            $totalPracticalHours += $summary['total_practical_hours'];

            $studentSummaries[] = [
                'enrollment_id' => $enr['id'],
                'student_profile_id' => $studentProfileId,
                'index_number' => $enr['index_number'],
                'first_name' => $enr['first_name'],
                'last_name' => $enr['last_name'],
                'email' => $enr['email'],
                'registration_number' => $enr['registration_number'],
                'class_name' => $enr['class_name'],
                'program_name' => $enr['program_name'],
                'attendance_summary' => $summary
            ];
        }

        Response::json([
            'data' => [
                'department_id' => $deptId,
                'total_students' => count($studentSummaries),
                'warning_count' => $warningCount,
                'critical_count' => $criticalCount,
                'total_practical_hours' => $totalPracticalHours,
                'students' => $studentSummaries
            ]
        ]);
    }

    /**
     * Get specific student's attendance report
     */
    public function getStudentReport(Request $request, int $studentId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        if (in_array('student', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true)) {
            $myProfileId = (int)($currentUser['profile']['id'] ?? 0);
            if ($myProfileId !== $studentId) {
                AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'student_profiles', $studentId);
                Response::error("Forbidden: You can only view your own attendance report", 403);
            }
        }

        $summary = AttendanceRecord::getStudentAttendanceSummary($studentId);
        $history = AttendanceRecord::getStudentAttendanceHistory($studentId);

        Response::json([
            'data' => [
                'student_profile_id' => $studentId,
                'summary' => $summary,
                'history' => $history
            ]
        ]);
    }

    /**
     * Helper to verify access to course offering
     */
    private function verifyCourseAccess(array $currentUser, int $offeringId, string $action = 'attendance.report'): void
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
                AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'course_offerings', $offeringId, ['action' => $action]);
                Response::error("Forbidden: You are not assigned as lecturer for this course offering", 403);
            }
            return;
        }

        if (in_array('hod', $roleNames, true)) {
            $userDeptId = $currentUser['department_id'] ?? 0;
            if ((int)$offering['department_id'] !== (int)$userDeptId) {
                AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'course_offerings', $offeringId, ['action' => $action]);
                Response::error("Forbidden: Course offering belongs to another department", 403);
            }
            return;
        }

        Response::error("Forbidden: Insufficient privileges", 403);
    }
}
