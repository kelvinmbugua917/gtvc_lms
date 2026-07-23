<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Student extends Model
{
    public static function getAllStudents(?int $departmentId = null, ?string $search = null): array
    {
        $sql = "SELECT sp.id AS student_profile_id, sp.user_id, sp.index_number, sp.gender, sp.date_of_birth,
                       sp.address, sp.guardian_name, sp.guardian_phone, sp.cbet_reg_no, sp.created_at AS profile_created_at,
                       u.registration_number, u.national_id, u.first_name, u.last_name, u.email, u.phone, u.is_active,
                       se.id AS active_enrollment_id, se.status AS enrollment_status, se.enrollment_date,
                       c.id AS class_id, c.name AS class_name, c.code AS class_code,
                       p.id AS program_id, p.name AS program_name, p.code AS program_code, p.department_id,
                       d.name AS department_name, d.code AS department_code,
                       i.name AS intake_name, ay.name AS academic_year_name
                FROM student_profiles sp
                JOIN users u ON u.id = sp.user_id
                LEFT JOIN student_enrollments se ON se.student_id = sp.id AND se.status = 'active'
                LEFT JOIN classes c ON c.id = se.class_id
                LEFT JOIN programs p ON p.id = se.program_id
                LEFT JOIN departments d ON d.id = p.department_id
                LEFT JOIN intakes i ON i.id = se.intake_id
                LEFT JOIN academic_years ay ON ay.id = i.academic_year_id";

        $conditions = [];
        $params = [];

        if ($departmentId !== null) {
            $conditions[] = "p.department_id = :department_id";
            $params['department_id'] = $departmentId;
        }

        if ($search !== null && trim($search) !== '') {
            $conditions[] = "(u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search OR u.registration_number LIKE :search OR sp.index_number LIKE :search)";
            $params['search'] = '%' . trim($search) . '%';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY u.last_name ASC, u.first_name ASC";
        return self::fetchAll($sql, $params);
    }

    public static function getStudentById(int $id): ?array
    {
        $sql = "SELECT sp.id AS student_profile_id, sp.user_id, sp.index_number, sp.gender, sp.date_of_birth,
                       sp.address, sp.guardian_name, sp.guardian_phone, sp.cbet_reg_no, sp.created_at AS profile_created_at,
                       u.registration_number, u.national_id, u.first_name, u.last_name, u.email, u.phone, u.is_active,
                       se.id AS active_enrollment_id, se.status AS enrollment_status, se.enrollment_date,
                       c.id AS class_id, c.name AS class_name, c.code AS class_code,
                       p.id AS program_id, p.name AS program_name, p.code AS program_code, p.department_id,
                       d.name AS department_name, d.code AS department_code,
                       i.id AS intake_id, i.name AS intake_name, ay.id AS academic_year_id, ay.name AS academic_year_name
                FROM student_profiles sp
                JOIN users u ON u.id = sp.user_id
                LEFT JOIN student_enrollments se ON se.student_id = sp.id AND se.status = 'active'
                LEFT JOIN classes c ON c.id = se.class_id
                LEFT JOIN programs p ON p.id = se.program_id
                LEFT JOIN departments d ON d.id = p.department_id
                LEFT JOIN intakes i ON i.id = se.intake_id
                LEFT JOIN academic_years ay ON ay.id = i.academic_year_id
                WHERE sp.id = :id";
        return self::fetchOne($sql, ['id' => $id]);
    }

    public static function getStudentByUserId(int $userId): ?array
    {
        $sql = "SELECT sp.id AS student_profile_id, sp.user_id, sp.index_number, sp.gender, sp.date_of_birth,
                       sp.address, sp.guardian_name, sp.guardian_phone, sp.cbet_reg_no, sp.created_at AS profile_created_at,
                       u.registration_number, u.national_id, u.first_name, u.last_name, u.email, u.phone, u.is_active,
                       se.id AS active_enrollment_id, se.status AS enrollment_status, se.enrollment_date,
                       c.id AS class_id, c.name AS class_name, c.code AS class_code,
                       p.id AS program_id, p.name AS program_name, p.code AS program_code, p.department_id,
                       d.name AS department_name, d.code AS department_code,
                       i.id AS intake_id, i.name AS intake_name, ay.id AS academic_year_id, ay.name AS academic_year_name
                FROM student_profiles sp
                JOIN users u ON u.id = sp.user_id
                LEFT JOIN student_enrollments se ON se.student_id = sp.id AND se.status = 'active'
                LEFT JOIN classes c ON c.id = se.class_id
                LEFT JOIN programs p ON p.id = se.program_id
                LEFT JOIN departments d ON d.id = p.department_id
                LEFT JOIN intakes i ON i.id = se.intake_id
                LEFT JOIN academic_years ay ON ay.id = i.academic_year_id
                WHERE sp.user_id = :user_id";
        return self::fetchOne($sql, ['user_id' => $userId]);
    }

    public static function existsIndexNumber(string $indexNumber, ?int $excludeProfileId = null): bool
    {
        $sql = "SELECT id FROM student_profiles WHERE index_number = :index_number";
        $params = ['index_number' => $indexNumber];

        if ($excludeProfileId !== null) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeProfileId;
        }

        $row = self::fetchOne($sql, $params);
        return $row !== null;
    }

    public static function createStudent(array $userData, array $profileData): int
    {
        $pdo = self::getDatabase()->getConnection();
        $pdo->beginTransaction();

        try {
            // 1. Create base user account
            $stmtUser = $pdo->prepare(
                "INSERT INTO users (registration_number, national_id, first_name, last_name, email, phone, password_hash, is_active)
                 VALUES (:reg_no, :nat_id, :first_name, :last_name, :email, :phone, :password_hash, 1)"
            );
            $stmtUser->execute([
                'reg_no' => $userData['registration_number'] ?? null,
                'nat_id' => $userData['national_id'] ?? null,
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'] ?? null,
                'password_hash' => password_hash($userData['password'] ?? 'password', PASSWORD_DEFAULT),
            ]);
            $userId = (int)$pdo->lastInsertId();

            // 2. Assign 'student' role (role_id = 4)
            $stmtRole = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, 4)");
            $stmtRole->execute(['user_id' => $userId]);

            // 3. Create student profile
            $stmtProfile = $pdo->prepare(
                "INSERT INTO student_profiles (user_id, index_number, gender, date_of_birth, address, guardian_name, guardian_phone, cbet_reg_no)
                 VALUES (:user_id, :index_number, :gender, :dob, :address, :g_name, :g_phone, :cbet_reg)"
            );
            $stmtProfile->execute([
                'user_id' => $userId,
                'index_number' => $profileData['index_number'] ?? null,
                'gender' => $profileData['gender'] ?? 'other',
                'dob' => $profileData['date_of_birth'] ?? null,
                'address' => $profileData['address'] ?? null,
                'g_name' => $profileData['guardian_name'] ?? null,
                'g_phone' => $profileData['guardian_phone'] ?? null,
                'cbet_reg' => $profileData['cbet_reg_no'] ?? null,
            ]);
            $profileId = (int)$pdo->lastInsertId();

            $pdo->commit();
            return $profileId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
