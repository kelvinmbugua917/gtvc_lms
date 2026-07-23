<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Department extends Model
{
    public static function getAllDepartments(): array
    {
        $sql = "SELECT d.id, d.code, d.name, d.description, d.head_of_department_id, d.created_at,
                       u.first_name AS hod_first_name, u.last_name AS hod_last_name, u.email AS hod_email
                FROM departments d
                LEFT JOIN users u ON u.id = d.head_of_department_id
                ORDER BY d.name ASC";
        return self::fetchAll($sql);
    }

    public static function getDepartmentById(int $id): ?array
    {
        $sql = "SELECT d.id, d.code, d.name, d.description, d.head_of_department_id, d.created_at,
                       u.first_name AS hod_first_name, u.last_name AS hod_last_name, u.email AS hod_email
                FROM departments d
                LEFT JOIN users u ON u.id = d.head_of_department_id
                WHERE d.id = :id";
        return self::fetchOne($sql, ['id' => $id]);
    }

    public static function getAllPrograms(?int $departmentId = null): array
    {
        $sql = "SELECT p.id, p.department_id, p.code, p.name, p.award_type, p.duration_months, p.created_at,
                       d.name AS department_name, d.code AS department_code
                FROM programs p
                JOIN departments d ON d.id = p.department_id";
        
        $params = [];
        if ($departmentId !== null) {
            $sql .= " WHERE p.department_id = :department_id";
            $params['department_id'] = $departmentId;
        }

        $sql .= " ORDER BY p.name ASC";
        return self::fetchAll($sql, $params);
    }

    public static function getProgramById(int $id): ?array
    {
        $sql = "SELECT p.id, p.department_id, p.code, p.name, p.award_type, p.duration_months, p.created_at,
                       d.name AS department_name, d.code AS department_code
                FROM programs p
                JOIN departments d ON d.id = p.department_id
                WHERE p.id = :id";
        return self::fetchOne($sql, ['id' => $id]);
    }

    public static function createDepartment(array $data): int
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            INSERT INTO departments (code, name, head_of_department_id, created_at)
            VALUES (:code, :name, :hod_id, NOW())
        ");
        $stmt->execute([
            'code' => $data['code'],
            'name' => $data['name'],
            'hod_id' => !empty($data['hod_id']) ? (int)$data['hod_id'] : null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function getProgramLevels(int $programId): array
    {
        $sql = "SELECT id, program_id, level_number, name, created_at
                FROM program_levels
                WHERE program_id = :program_id
                ORDER BY level_number ASC";
        return self::fetchAll($sql, ['program_id' => $programId]);
    }
}
