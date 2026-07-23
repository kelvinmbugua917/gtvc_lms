<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class CourseOffering extends Model
{
    public static function getAllOfferings(?int $classId = null, ?int $lecturerId = null, ?int $studentUserId = null): array
    {
        $sql = "SELECT co.id, co.unit_id, co.class_id, co.academic_year_id, co.primary_lecturer_id, co.status, co.created_at,
                       u.code AS unit_code, u.title AS unit_title, u.credit_hours, u.is_cbet, u.department_id,
                       d.name AS department_name,
                       c.name AS class_name, c.code AS class_code, c.program_id,
                       p.name AS program_name, p.code AS program_code,
                       ay.name AS academic_year_name,
                       sp.staff_number, usr.first_name AS lecturer_first_name, usr.last_name AS lecturer_last_name, usr.email AS lecturer_email
                FROM course_offerings co
                JOIN units u ON u.id = co.unit_id
                JOIN departments d ON d.id = u.department_id
                JOIN classes c ON c.id = co.class_id
                JOIN programs p ON p.id = c.program_id
                JOIN academic_years ay ON ay.id = co.academic_year_id
                LEFT JOIN staff_profiles sp ON sp.id = co.primary_lecturer_id
                LEFT JOIN users usr ON usr.id = sp.user_id";

        $conditions = [];
        $params = [];

        if ($classId !== null) {
            $conditions[] = "co.class_id = :class_id";
            $params['class_id'] = $classId;
        }

        if ($lecturerId !== null) {
            $conditions[] = "co.primary_lecturer_id = :lecturer_id";
            $params['lecturer_id'] = $lecturerId;
        }

        if ($studentUserId !== null) {
            // Join active student enrollments for this student
            $sql .= " JOIN student_enrollments se ON se.class_id = co.class_id
                      JOIN student_profiles stp ON stp.id = se.student_id";
            $conditions[] = "stp.user_id = :student_user_id AND se.status = 'active'";
            $params['student_user_id'] = $studentUserId;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY u.code ASC";
        return self::fetchAll($sql, $params);
    }

    public static function getOfferingById(int $id): ?array
    {
        $sql = "SELECT co.id, co.unit_id, co.class_id, co.academic_year_id, co.primary_lecturer_id, co.status, co.created_at,
                       u.code AS unit_code, u.title AS unit_title, u.credit_hours, u.is_cbet, u.department_id,
                       d.name AS department_name,
                       c.name AS class_name, c.code AS class_code, c.program_id,
                       p.name AS program_name, p.code AS program_code,
                       ay.name AS academic_year_name,
                       sp.staff_number, usr.first_name AS lecturer_first_name, usr.last_name AS lecturer_last_name, usr.email AS lecturer_email
                FROM course_offerings co
                JOIN units u ON u.id = co.unit_id
                JOIN departments d ON d.id = u.department_id
                JOIN classes c ON c.id = co.class_id
                JOIN programs p ON p.id = c.program_id
                JOIN academic_years ay ON ay.id = co.academic_year_id
                LEFT JOIN staff_profiles sp ON sp.id = co.primary_lecturer_id
                LEFT JOIN users usr ON usr.id = sp.user_id
                WHERE co.id = :id";
        return self::fetchOne($sql, ['id' => $id]);
    }
}
