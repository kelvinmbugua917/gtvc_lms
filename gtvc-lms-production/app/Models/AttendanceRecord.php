<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AttendanceRecord extends Model
{
    /**
     * Get all records for an attendance session
     */
    public static function getRecordsBySession(int $sessionId): array
    {
        $sql = "SELECT r.id, r.attendance_session_id, r.student_id AS student_profile_id, r.enrollment_id,
                       r.status, r.arrival_time, r.excuse_reason, r.lecturer_notes,
                       r.practical_competency_obs, r.created_at, r.updated_at,
                       sp.index_number, sp.user_id, u.first_name, u.last_name, u.registration_number, u.email
                FROM attendance_records r
                JOIN student_profiles sp ON sp.id = r.student_id
                JOIN users u ON u.id = sp.user_id
                WHERE r.attendance_session_id = :session_id
                ORDER BY u.last_name ASC, u.first_name ASC";

        return self::fetchAll($sql, ['session_id' => $sessionId]);
    }

    /**
     * Save/upsert student attendance records for a session
     */
    public static function saveSessionRecords(int $sessionId, array $studentRecords): int
    {
        $savedCount = 0;

        foreach ($studentRecords as $rec) {
            $studentProfileId = (int)$rec['student_profile_id'];
            $enrollmentId = (int)$rec['enrollment_id'];
            $status = $rec['status'] ?? 'present';
            $arrivalTime = $rec['arrival_time'] ?? null;
            $excuseReason = $rec['excuse_reason'] ?? null;
            $lecturerNotes = $rec['lecturer_notes'] ?? null;
            $practicalObs = $rec['practical_competency_obs'] ?? null;

            // Check if record exists
            $checkSql = "SELECT id FROM attendance_records 
                         WHERE attendance_session_id = :session_id AND student_id = :student_id";
            $existing = self::fetchOne($checkSql, [
                'session_id' => $sessionId,
                'student_id' => $studentProfileId
            ]);

            if ($existing) {
                $updateSql = "UPDATE attendance_records
                              SET status = :status,
                                  arrival_time = :arrival_time,
                                  excuse_reason = :excuse_reason,
                                  lecturer_notes = :lecturer_notes,
                                  practical_competency_obs = :practical_competency_obs,
                                  updated_at = CURRENT_TIMESTAMP
                              WHERE id = :id";
                self::execute($updateSql, [
                    'id' => $existing['id'],
                    'status' => $status,
                    'arrival_time' => $arrivalTime,
                    'excuse_reason' => $excuseReason,
                    'lecturer_notes' => $lecturerNotes,
                    'practical_competency_obs' => $practicalObs
                ]);
            } else {
                $insertSql = "INSERT INTO attendance_records
                              (attendance_session_id, student_id, enrollment_id, status, arrival_time, excuse_reason, lecturer_notes, practical_competency_obs)
                              VALUES
                              (:session_id, :student_id, :enrollment_id, :status, :arrival_time, :excuse_reason, :lecturer_notes, :practical_competency_obs)";
                self::execute($insertSql, [
                    'session_id' => $sessionId,
                    'student_id' => $studentProfileId,
                    'enrollment_id' => $enrollmentId,
                    'status' => $status,
                    'arrival_time' => $arrivalTime,
                    'excuse_reason' => $excuseReason,
                    'lecturer_notes' => $lecturerNotes,
                    'practical_competency_obs' => $practicalObs
                ]);
            }
            $savedCount++;
        }

        return $savedCount;
    }

    /**
     * Get attendance summary for a student (overall, theory, practical, course breakdown)
     */
    public static function getStudentAttendanceSummary(int $studentProfileId, ?int $offeringId = null): array
    {
        $params = ['student_id' => $studentProfileId];
        $offeringWhere = "";

        if ($offeringId !== null) {
            $offeringWhere = " AND s.course_offering_id = :offering_id";
            $params['offering_id'] = $offeringId;
        }

        // Overall query
        $sql = "SELECT 
                    COUNT(r.id) AS total_sessions,
                    SUM(CASE WHEN r.status = 'present' THEN 1 ELSE 0 END) AS present_count,
                    SUM(CASE WHEN r.status = 'absent' THEN 1 ELSE 0 END) AS absent_count,
                    SUM(CASE WHEN r.status = 'late' THEN 1 ELSE 0 END) AS late_count,
                    SUM(CASE WHEN r.status = 'excused' THEN 1 ELSE 0 END) AS excused_count,
                    SUM(CASE WHEN s.session_type IN ('practical', 'workshop', 'laboratory') AND r.status IN ('present', 'late') THEN s.practical_hours ELSE 0 END) AS total_practical_hours,
                    SUM(CASE WHEN s.session_type IN ('theory', 'examination') AND r.status IN ('present', 'late') THEN s.theory_hours ELSE 0 END) AS total_theory_hours
                FROM attendance_records r
                JOIN attendance_sessions s ON s.id = r.attendance_session_id
                WHERE r.student_id = :student_id $offeringWhere";

        $summary = self::fetchOne($sql, $params);

        $total = (int)($summary['total_sessions'] ?? 0);
        $present = (int)($summary['present_count'] ?? 0);
        $late = (int)($summary['late_count'] ?? 0);
        $excused = (int)($summary['excused_count'] ?? 0);
        $absent = (int)($summary['absent_count'] ?? 0);

        // Effective attendance % formula: (Present + Excused + 0.5*Late) / Total * 100
        $effectiveScore = $present + $excused + ($late * 0.5);
        $percentage = $total > 0 ? round(($effectiveScore / $total) * 100, 1) : 100.0;

        $warningLevel = 'normal';
        if ($percentage < 60.0) {
            $warningLevel = 'critical';
        } else if ($percentage < 75.0) {
            $warningLevel = 'warning';
        }

        // Course Breakdown
        $breakdownSql = "SELECT 
                            co.id AS course_offering_id,
                            u.code AS unit_code,
                            u.title AS unit_title,
                            COUNT(r.id) AS total_sessions,
                            SUM(CASE WHEN r.status = 'present' THEN 1 ELSE 0 END) AS present_count,
                            SUM(CASE WHEN r.status = 'absent' THEN 1 ELSE 0 END) AS absent_count,
                            SUM(CASE WHEN r.status = 'late' THEN 1 ELSE 0 END) AS late_count,
                            SUM(CASE WHEN r.status = 'excused' THEN 1 ELSE 0 END) AS excused_count,
                            SUM(CASE WHEN s.session_type IN ('practical', 'workshop', 'laboratory') AND r.status IN ('present', 'late') THEN s.practical_hours ELSE 0 END) AS practical_hours_completed
                         FROM attendance_records r
                         JOIN attendance_sessions s ON s.id = r.attendance_session_id
                         JOIN course_offerings co ON co.id = s.course_offering_id
                         JOIN units u ON u.id = co.unit_id
                         WHERE r.student_id = :student_id
                         GROUP BY co.id, u.code, u.title";

        $courseBreakdown = self::fetchAll($breakdownSql, ['student_id' => $studentProfileId]);

        foreach ($courseBreakdown as &$course) {
            $cTotal = (int)$course['total_sessions'];
            $cPres = (int)$course['present_count'];
            $cLate = (int)$course['late_count'];
            $cExc = (int)$course['excused_count'];
            $cScore = $cPres + $cExc + ($cLate * 0.5);
            $course['percentage'] = $cTotal > 0 ? round(($cScore / $cTotal) * 100, 1) : 100.0;
            $course['warning_level'] = $course['percentage'] < 60.0 ? 'critical' : ($course['percentage'] < 75.0 ? 'warning' : 'normal');
        }

        return [
            'total_sessions' => $total,
            'present_count' => $present,
            'absent_count' => $absent,
            'late_count' => $late,
            'excused_count' => $excused,
            'attendance_percentage' => $percentage,
            'warning_level' => $warningLevel,
            'total_practical_hours' => (float)($summary['total_practical_hours'] ?? 0.0),
            'total_theory_hours' => (float)($summary['total_theory_hours'] ?? 0.0),
            'course_breakdown' => $courseBreakdown
        ];
    }

    /**
     * Get attendance history log for student
     */
    public static function getStudentAttendanceHistory(int $studentProfileId, ?int $offeringId = null): array
    {
        $sql = "SELECT r.id, r.status, r.arrival_time, r.excuse_reason, r.lecturer_notes, r.practical_competency_obs, r.created_at,
                       s.session_date, s.start_time, s.end_time, s.session_type, s.topic, s.facility_equipment, s.practical_hours, s.theory_hours,
                       u.code AS unit_code, u.title AS unit_title,
                       c.name AS class_name,
                       lec.first_name AS lecturer_first_name, lec.last_name AS lecturer_last_name
                FROM attendance_records r
                JOIN attendance_sessions s ON s.id = r.attendance_session_id
                JOIN course_offerings co ON co.id = s.course_offering_id
                JOIN units u ON u.id = co.unit_id
                JOIN classes c ON c.id = s.class_id
                LEFT JOIN users lec ON lec.id = s.lecturer_id
                WHERE r.student_id = :student_id";

        $params = ['student_id' => $studentProfileId];

        if ($offeringId !== null) {
            $sql .= " AND s.course_offering_id = :offering_id";
            $params['offering_id'] = $offeringId;
        }

        $sql .= " ORDER BY s.session_date DESC, s.start_time DESC";

        return self::fetchAll($sql, $params);
    }

    /**
     * Get course attendance matrix for lecturer or HOD
     */
    public static function getCourseAttendanceMatrix(int $offeringId): array
    {
        $sessions = AttendanceSession::getSessions(['course_offering_id' => $offeringId]);
        
        $offering = CourseOffering::getOfferingById($offeringId);
        if (!$offering) {
            return ['sessions' => [], 'students' => []];
        }

        $roster = AttendanceSession::getClassRosterForAttendance((int)$offering['class_id']);

        $studentsWithRecords = [];
        foreach ($roster as $student) {
            $studentId = (int)$student['student_profile_id'];
            $summary = self::getStudentAttendanceSummary($studentId, $offeringId);
            $history = self::getStudentAttendanceHistory($studentId, $offeringId);

            $sessionMap = [];
            foreach ($history as $h) {
                $sessionMap[$h['id']] = $h['status'];
            }

            $studentsWithRecords[] = [
                'student_profile_id' => $studentId,
                'user_id' => $student['user_id'],
                'first_name' => $student['first_name'],
                'last_name' => $student['last_name'],
                'index_number' => $student['index_number'],
                'registration_number' => $student['registration_number'],
                'summary' => $summary,
                'session_statuses' => $sessionMap
            ];
        }

        return [
            'sessions' => $sessions,
            'students' => $studentsWithRecords
        ];
    }
}
