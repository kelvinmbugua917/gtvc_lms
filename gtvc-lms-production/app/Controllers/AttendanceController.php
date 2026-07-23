<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\CourseOffering;
use App\Models\LessonProgress;
use App\Models\AuditLog;

class AttendanceController
{
    /**
     * Get attendance sessions based on role and filters
     */
    public function getSessions(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        $filters = [
            'course_offering_id' => $request->getParam('course_offering_id'),
            'class_id' => $request->getParam('class_id'),
            'session_type' => $request->getParam('session_type'),
            'date_from' => $request->getParam('date_from'),
            'date_to' => $request->getParam('date_to')
        ];

        if (in_array('student', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true)) {
            // Students can only see sessions for classes they are enrolled in
            $studentProfileId = $currentUser['profile']['id'] ?? 0;
            $history = AttendanceRecord::getStudentAttendanceHistory((int)$studentProfileId, $filters['course_offering_id'] ? (int)$filters['course_offering_id'] : null);
            Response::json(['data' => $history]);
            return;
        }

        if (in_array('lecturer', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true) && !in_array('hod', $roleNames, true)) {
            $filters['lecturer_id'] = (int)$currentUser['id'];
        } else if (in_array('hod', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true)) {
            $filters['department_id'] = (int)($currentUser['department_id'] ?? 0);
        }

        $sessions = AttendanceSession::getSessions($filters);
        Response::json(['data' => $sessions]);
    }

    /**
     * Get single session details with student roster
     */
    public function getSessionById(Request $request, int $id): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $session = AttendanceSession::getSessionById($id);

        if (!$session) {
            Response::error("Attendance session not found", 404);
        }

        $this->verifyCourseAccess($currentUser, (int)$session['course_offering_id']);

        $records = AttendanceRecord::getRecordsBySession($id);

        // If records are empty, fetch default class roster for marking
        if (empty($records)) {
            $roster = AttendanceSession::getClassRosterForAttendance((int)$session['class_id']);
            $records = array_map(function ($s) use ($id) {
                return [
                    'id' => null,
                    'attendance_session_id' => $id,
                    'student_profile_id' => $s['student_profile_id'],
                    'enrollment_id' => $s['enrollment_id'],
                    'status' => 'present',
                    'arrival_time' => null,
                    'excuse_reason' => null,
                    'lecturer_notes' => null,
                    'practical_competency_obs' => null,
                    'index_number' => $s['index_number'],
                    'user_id' => $s['user_id'],
                    'first_name' => $s['first_name'],
                    'last_name' => $s['last_name'],
                    'registration_number' => $s['registration_number'],
                    'email' => $s['email']
                ];
            }, $roster);
        }

        $session['records'] = $records;
        Response::json(['data' => $session]);
    }

    /**
     * Create attendance session
     */
    public function createSession(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $body = $request->getBody();

        $offeringId = (int)($_POST['course_offering_id'] ?? $body['course_offering_id'] ?? 1);
        $sessionDate = $_POST['session_date'] ?? $body['session_date'] ?? date('Y-m-d');
        $startTime = $_POST['start_time'] ?? $body['start_time'] ?? '08:00:00';
        $endTime = $_POST['end_time'] ?? $body['end_time'] ?? '10:00:00';
        $topic = $_POST['topic'] ?? $body['topic'] ?? 'Lecture & Practical Session';
        $sessionType = $_POST['session_type'] ?? $body['session_type'] ?? 'lecture';

        $sessionData = [
            'course_offering_id' => $offeringId,
            'class_id' => (int)($_POST['class_id'] ?? $body['class_id'] ?? 1),
            'lecturer_id' => (int)($_POST['lecturer_id'] ?? $body['lecturer_id'] ?? $currentUser['id']),
            'session_date' => $sessionDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'session_type' => $sessionType,
            'topic' => $topic,
            'notes' => $_POST['notes'] ?? $body['notes'] ?? null,
            'facility_equipment' => $_POST['facility_equipment'] ?? $body['facility_equipment'] ?? null,
            'practical_hours' => 2.0,
            'theory_hours' => 2.0,
            'status' => 'completed'
        ];

        $sessionId = AttendanceSession::createSession($sessionData);

        // Auto populate class roster as 'present' by default if no records
        $roster = AttendanceSession::getClassRosterForAttendance((int)$sessionData['class_id']);
        if (!empty($roster)) {
            $defaultRecords = array_map(function ($s) {
                return [
                    'student_profile_id' => $s['student_profile_id'],
                    'enrollment_id' => $s['enrollment_id'],
                    'status' => 'present',
                    'arrival_time' => null,
                    'excuse_reason' => null,
                    'lecturer_notes' => null,
                    'practical_competency_obs' => null
                ];
            }, $roster);
            AttendanceRecord::saveSessionRecords($sessionId, $defaultRecords);
        }

        AuditLog::log($currentUser['id'], 'attendance.session_created', 'attendance_sessions', $sessionId, [
            'topic' => $sessionData['topic'],
            'session_type' => $sessionData['session_type'],
            'date' => $sessionData['session_date']
        ]);

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        $isJson = str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');

        if (!$isJson && $_SERVER['REQUEST_METHOD'] === 'POST') {
            \App\Core\Session::setFlash('success', 'New Attendance Register Session started successfully!');
            Response::redirect('/lecturer/attendance');
        } else {
            $createdSession = AttendanceSession::getSessionById($sessionId);
            Response::json(['data' => $createdSession, 'message' => "Attendance session created successfully"], 201);
        }
    }

    /**
     * Update attendance session details
     */
    public function updateSession(Request $request, int $id): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $session = AttendanceSession::getSessionById($id);

        if (!$session) {
            Response::error("Attendance session not found", 404);
        }

        $this->verifyCourseAccess($currentUser, (int)$session['course_offering_id'], 'attendance.update');

        $body = $request->getBody();
        $updatedData = array_merge($session, $body);

        AttendanceSession::updateSession($id, $updatedData);

        if (isset($body['records']) && is_array($body['records'])) {
            AttendanceRecord::saveSessionRecords($id, $body['records']);
        }

        AuditLog::log($currentUser['id'], 'attendance.session_updated', 'attendance_sessions', $id);

        $updatedSession = AttendanceSession::getSessionById($id);
        $updatedSession['records'] = AttendanceRecord::getRecordsBySession($id);

        Response::json(['data' => $updatedSession, 'message' => "Attendance session updated successfully"]);
    }

    /**
     * Delete session
     */
    public function deleteSession(Request $request, int $id): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $session = AttendanceSession::getSessionById($id);

        if (!$session) {
            Response::error("Attendance session not found", 404);
        }

        $this->verifyCourseAccess($currentUser, (int)$session['course_offering_id'], 'attendance.delete');

        AttendanceSession::deleteSession($id);

        AuditLog::log($currentUser['id'], 'attendance.session_deleted', 'attendance_sessions', $id);

        Response::json(['message' => "Attendance session deleted successfully"]);
    }

    /**
     * Save student records for a session
     */
    public function saveSessionRecords(Request $request, int $sessionId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $session = AttendanceSession::getSessionById($sessionId);

        if (!$session) {
            Response::error("Attendance session not found", 404);
        }

        $this->verifyCourseAccess($currentUser, (int)$session['course_offering_id'], 'attendance.mark');

        $body = $request->getBody();
        $records = $body['records'] ?? [];

        if (!is_array($records) || empty($records)) {
            Response::error("Invalid or empty student records array", 400);
        }

        $savedCount = AttendanceRecord::saveSessionRecords($sessionId, $records);

        AuditLog::log($currentUser['id'], 'attendance.marked', 'attendance_sessions', $sessionId, ['count' => $savedCount]);

        $updatedRecords = AttendanceRecord::getRecordsBySession($sessionId);

        Response::json([
            'data' => $updatedRecords,
            'message' => "Saved attendance records for {$savedCount} students"
        ]);
    }

    /**
     * Get logged in student's own attendance summary and history
     */
    public function getMyAttendance(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        if (!in_array('student', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true)) {
            Response::error("Forbidden: Endpoint is reserved for students", 403);
        }

        $studentProfileId = (int)($currentUser['profile']['id'] ?? 0);
        if ($studentProfileId === 0) {
            Response::error("Student profile not found for user", 404);
        }

        $summary = AttendanceRecord::getStudentAttendanceSummary($studentProfileId);
        $history = AttendanceRecord::getStudentAttendanceHistory($studentProfileId);

        Response::json([
            'data' => [
                'summary' => $summary,
                'history' => $history
            ]
        ]);
    }

    /**
     * Helper to verify access to course offering
     */
    private function verifyCourseAccess(array $currentUser, int $offeringId, string $action = 'attendance.view'): void
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

        Response::error("Forbidden: Insufficient privileges for attendance management", 403);
    }
}
