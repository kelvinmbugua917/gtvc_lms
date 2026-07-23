<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Enrollment extends Model
{
    public static function getAllEnrollments(?int $studentId = null, ?int $departmentId = null, ?string $status = null): array
    {
        $sql = "SELECT se.id, se.student_id, se.class_id, se.program_id, se.intake_id, se.enrollment_date, se.status, se.created_at,
                       sp.index_number, u.first_name, u.last_name, u.email, u.registration_number,
                       c.name AS class_name, c.code AS class_code,
                       p.name AS program_name, p.code AS program_code, p.department_id,
                       d.name AS department_name, d.code AS department_code,
                       i.name AS intake_name, ay.name AS academic_year_name
                FROM student_enrollments se
                JOIN student_profiles sp ON sp.id = se.student_id
                JOIN users u ON u.id = sp.user_id
                JOIN classes c ON c.id = se.class_id
                JOIN programs p ON p.id = se.program_id
                JOIN departments d ON d.id = p.department_id
                JOIN intakes i ON i.id = se.intake_id
                JOIN academic_years ay ON ay.id = i.academic_year_id";

        $conditions = [];
        $params = [];

        if ($studentId !== null) {
            $conditions[] = "se.student_id = :student_id";
            $params['student_id'] = $studentId;
        }

        if ($departmentId !== null) {
            $conditions[] = "p.department_id = :department_id";
            $params['department_id'] = $departmentId;
        }

        if ($status !== null) {
            $conditions[] = "se.status = :status";
            $params['status'] = $status;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY se.created_at DESC";
        return self::fetchAll($sql, $params);
    }

    public static function getEnrollmentById(int $id): ?array
    {
        $sql = "SELECT se.id, se.student_id, se.class_id, se.program_id, se.intake_id, se.enrollment_date, se.status, se.created_at,
                       sp.index_number, sp.user_id, u.first_name, u.last_name, u.email, u.registration_number,
                       c.name AS class_name, c.code AS class_code,
                       p.name AS program_name, p.code AS program_code, p.department_id,
                       d.name AS department_name, d.code AS department_code,
                       i.name AS intake_name, ay.name AS academic_year_name
                FROM student_enrollments se
                JOIN student_profiles sp ON sp.id = se.student_id
                JOIN users u ON u.id = sp.user_id
                JOIN classes c ON c.id = se.class_id
                JOIN programs p ON p.id = se.program_id
                JOIN departments d ON d.id = p.department_id
                JOIN intakes i ON i.id = se.intake_id
                JOIN academic_years ay ON ay.id = i.academic_year_id
                WHERE se.id = :id";
        return self::fetchOne($sql, ['id' => $id]);
    }

    public static function checkActiveEnrollmentExists(int $studentId): bool
    {
        $sql = "SELECT id FROM student_enrollments WHERE student_id = :student_id AND status = 'active' LIMIT 1";
        $row = self::fetchOne($sql, ['student_id' => $studentId]);
        return $row !== null;
    }

    public static function createEnrollment(array $data): int
    {
        $sql = "INSERT INTO student_enrollments (student_id, class_id, program_id, intake_id, enrollment_date, status)
                VALUES (:student_id, :class_id, :program_id, :intake_id, :enrollment_date, :status)";
        
        return self::execute($sql, [
            'student_id' => $data['student_id'],
            'class_id' => $data['class_id'],
            'program_id' => $data['program_id'],
            'intake_id' => $data['intake_id'],
            'enrollment_date' => $data['enrollment_date'] ?? date('Y-m-d'),
            'status' => $data['status'] ?? 'active',
        ]);
    }

    public static function updateEnrollmentStatus(int $id, string $status): bool
    {
        $allowedStatuses = ['active', 'suspended', 'deferred', 'graduated', 'discontinued'];
        if (!in_array($status, $allowedStatuses, true)) {
            throw new \InvalidArgumentException("Invalid enrollment status '{$status}'");
        }

        $sql = "UPDATE student_enrollments SET status = :status WHERE id = :id";
        self::execute($sql, ['status' => $status, 'id' => $id]);
        return true;
    }
}
