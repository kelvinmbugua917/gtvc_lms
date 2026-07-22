<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Unit extends Model
{
    public static function getAllUnits(?int $departmentId = null, ?int $programId = null): array
    {
        if ($programId !== null) {
            $sql = "SELECT u.id, u.department_id, u.code, u.title, u.description, u.credit_hours, u.is_cbet, u.created_at,
                           d.name AS department_name, d.code AS department_code,
                           pu.is_core, pu.program_level_id, pl.name AS program_level_name
                    FROM units u
                    JOIN departments d ON d.id = u.department_id
                    JOIN program_units pu ON pu.unit_id = u.id
                    LEFT JOIN program_levels pl ON pl.id = pu.program_level_id
                    WHERE pu.program_id = :program_id
                    ORDER BY u.code ASC";
            return self::fetchAll($sql, ['program_id' => $programId]);
        }

        $sql = "SELECT u.id, u.department_id, u.code, u.title, u.description, u.credit_hours, u.is_cbet, u.created_at,
                       d.name AS department_name, d.code AS department_code
                FROM units u
                JOIN departments d ON d.id = u.department_id";
        
        $params = [];
        if ($departmentId !== null) {
            $sql .= " WHERE u.department_id = :department_id";
            $params['department_id'] = $departmentId;
        }

        $sql .= " ORDER BY u.code ASC";
        return self::fetchAll($sql, $params);
    }

    public static function getUnitById(int $id): ?array
    {
        $sql = "SELECT u.id, u.department_id, u.code, u.title, u.description, u.credit_hours, u.is_cbet, u.created_at,
                       d.name AS department_name, d.code AS department_code
                FROM units u
                JOIN departments d ON d.id = u.department_id
                WHERE u.id = :id";
        return self::fetchOne($sql, ['id' => $id]);
    }
}
