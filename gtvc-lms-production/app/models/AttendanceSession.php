<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AttendanceSession extends Model
{
    /**
     * Get attendance sessions with optional filtering
     */
    public static function getSessions(array $filters = []): array
    {
        $sql = "SELECT s.id, s.course_offering_id, s.class_id, s.lecturer_id, s.session_date,
                       s.start_time, s.end_time, s.session_type, s.topic, s.notes,
                       s.facility_equipment, s.practical_hours, s.theory_hours, s.status,
                       s.created_at, s.updated_at,
                       u.title AS unit_title, u.code AS unit_code,
                       c.name AS class_name, c.code AS class_code,
                       p.name AS program_name, p.department_id,
                       d.name AS department_name, d.code AS department_code,
                       lec.first_name AS lecturer_first_name, lec.last_name AS lecturer_last_name,
                       (SELECT COUNT(*) FROM attendance_records r WHERE r.attendance_session_id = s.id) AS total_records,
                       (SELECT COUNT(*) FROM attendance_records r WHERE r.attendance_session_id = s.id AND r.status = 'present') AS present_count
                FROM attendance_sessions s
                JOIN course_offerings co ON co.id = s.course_offering_id
                JOIN units u ON u.id = co.unit_id
                JOIN classes c ON c.id = s.class_id
                JOIN programs p ON p.id = c.program_id
                JOIN departments d ON d.id = p.department_id
                LEFT JOIN users lec ON lec.id = s.lecturer_id";

        $conditions = [];
        $params = [];

        if (!empty($filters['course_offering_id'])) {
            $conditions[] = "s.course_offering_id = :course_offering_id";
            $params['course_offering_id'] = (int)$filters['course_offering_id'];
        }

        if (!empty($filters['lecturer_id'])) {
            $conditions[] = "s.lecturer_id = :lecturer_id";
            $params['lecturer_id'] = (int)$filters['lecturer_id'];
        }

        if (!empty($filters['class_id'])) {
            $conditions[] = "s.class_id = :class_id";
            $params['class_id'] = (int)$filters['class_id'];
        }

        if (!empty($filters['department_id'])) {
            $conditions[] = "p.department_id = :department_id";
            $params['department_id'] = (int)$filters['department_id'];
        }

        if (!empty($filters['session_type'])) {
            $conditions[] = "s.session_type = :session_type";
            $params['session_type'] = $filters['session_type'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "s.session_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "s.session_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY s.session_date DESC, s.start_time DESC";

        return self::fetchAll($sql, $params);
    }

    /**
     * Get session details by ID
     */
    public static function getSessionById(int $id): ?array
    {
        $sql = "SELECT s.id, s.course_offering_id, s.class_id, s.lecturer_id, s.session_date,
                       s.start_time, s.end_time, s.session_type, s.topic, s.notes,
                       s.facility_equipment, s.practical_hours, s.theory_hours, s.status,
                       s.created_at, s.updated_at,
                       co.unit_id, co.primary_lecturer_id,
                       u.title AS unit_title, u.code AS unit_code,
                       c.name AS class_name, c.code AS class_code,
                       p.name AS program_name, p.department_id,
                       d.name AS department_name, d.code AS department_code,
                       lec.first_name AS lecturer_first_name, lec.last_name AS lecturer_last_name
                FROM attendance_sessions s
                JOIN course_offerings co ON co.id = s.course_offering_id
                JOIN units u ON u.id = co.unit_id
                JOIN classes c ON c.id = s.class_id
                JOIN programs p ON p.id = c.program_id
                JOIN departments d ON d.id = p.department_id
                LEFT JOIN users lec ON lec.id = s.lecturer_id
                WHERE s.id = :id";

        return self::fetchOne($sql, ['id' => $id]);
    }

    /**
     * Create attendance session
     */
    public static function createSession(array $data): int
    {
        $sql = "INSERT INTO attendance_sessions
                (course_offering_id, class_id, lecturer_id, session_date, start_time, end_time, session_type, topic, notes, facility_equipment, practical_hours, theory_hours, status)
                VALUES
                (:course_offering_id, :class_id, :lecturer_id, :session_date, :start_time, :end_time, :session_type, :topic, :notes, :facility_equipment, :practical_hours, :theory_hours, :status)";

        $sessionType = $data['session_type'] ?? 'theory';
        $practicalHours = ($sessionType === 'practical' || $sessionType === 'workshop' || $sessionType === 'laboratory') 
            ? (float)($data['practical_hours'] ?? 2.0) 
            : 0.0;
        $theoryHours = ($sessionType === 'theory' || $sessionType === 'examination') 
            ? (float)($data['theory_hours'] ?? 2.0) 
            : 0.0;

        return self::execute($sql, [
            'course_offering_id' => (int)$data['course_offering_id'],
            'class_id' => (int)$data['class_id'],
            'lecturer_id' => (int)$data['lecturer_id'],
            'session_date' => $data['session_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'session_type' => $sessionType,
            'topic' => trim($data['topic']),
            'notes' => isset($data['notes']) ? trim($data['notes']) : null,
            'facility_equipment' => isset($data['facility_equipment']) ? trim($data['facility_equipment']) : null,
            'practical_hours' => $practicalHours,
            'theory_hours' => $theoryHours,
            'status' => $data['status'] ?? 'completed'
        ]);
    }

    /**
     * Update attendance session
     */
    public static function updateSession(int $id, array $data): bool
    {
        $sessionType = $data['session_type'] ?? 'theory';
        $practicalHours = ($sessionType === 'practical' || $sessionType === 'workshop' || $sessionType === 'laboratory') 
            ? (float)($data['practical_hours'] ?? 2.0) 
            : 0.0;
        $theoryHours = ($sessionType === 'theory' || $sessionType === 'examination') 
            ? (float)($data['theory_hours'] ?? 2.0) 
            : 0.0;

        $sql = "UPDATE attendance_sessions
                SET session_date = :session_date,
                    start_time = :start_time,
                    end_time = :end_time,
                    session_type = :session_type,
                    topic = :topic,
                    notes = :notes,
                    facility_equipment = :facility_equipment,
                    practical_hours = :practical_hours,
                    theory_hours = :theory_hours,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        return self::execute($sql, [
            'id' => $id,
            'session_date' => $data['session_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'session_type' => $sessionType,
            'topic' => trim($data['topic']),
            'notes' => isset($data['notes']) ? trim($data['notes']) : null,
            'facility_equipment' => isset($data['facility_equipment']) ? trim($data['facility_equipment']) : null,
            'practical_hours' => $practicalHours,
            'theory_hours' => $theoryHours,
            'status' => $data['status'] ?? 'completed'
        ]) > 0;
    }

    /**
     * Delete attendance session
     */
    public static function deleteSession(int $id): bool
    {
        // Delete child records first
        self::execute("DELETE FROM attendance_records WHERE attendance_session_id = :id", ['id' => $id]);
        return self::execute("DELETE FROM attendance_sessions WHERE id = :id", ['id' => $id]) > 0;
    }

    /**
     * Get student list for marking attendance for a class or course offering
     */
    public static function getClassRosterForAttendance(int $classId): array
    {
        $sql = "SELECT se.id AS enrollment_id, se.student_id AS student_profile_id, sp.user_id,
                       sp.index_number, u.first_name, u.last_name, u.email, u.registration_number
                FROM student_enrollments se
                JOIN student_profiles sp ON sp.id = se.student_id
                JOIN users u ON u.id = sp.user_id
                WHERE se.class_id = :class_id AND se.status IN ('Active', 'Enrolled')
                ORDER BY u.last_name ASC, u.first_name ASC";

        return self::fetchAll($sql, ['class_id' => $classId]);
    }
}
