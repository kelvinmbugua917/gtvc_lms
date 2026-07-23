<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ClassCohort extends Model
{
    public static function getAllClasses(?int $programId = null, ?int $intakeId = null): array
    {
        $sql = "SELECT c.id, c.program_id, c.intake_id, c.code, c.name, c.year_of_study, c.status, c.created_at,
                       p.name AS program_name, p.code AS program_code, p.department_id,
                       d.name AS department_name, d.code AS department_code,
                       i.name AS intake_name, i.code AS intake_code, ay.name AS academic_year_name
                FROM classes c
                JOIN programs p ON p.id = c.program_id
                JOIN departments d ON d.id = p.department_id
                JOIN intakes i ON i.id = c.intake_id
                JOIN academic_years ay ON ay.id = i.academic_year_id";
        
        $conditions = [];
        $params = [];

        if ($programId !== null) {
            $conditions[] = "c.program_id = :program_id";
            $params['program_id'] = $programId;
        }

        if ($intakeId !== null) {
            $conditions[] = "c.intake_id = :intake_id";
            $params['intake_id'] = $intakeId;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY c.created_at DESC";
        return self::fetchAll($sql, $params);
    }

    public static function getClassById(int $id): ?array
    {
        $sql = "SELECT c.id, c.program_id, c.intake_id, c.code, c.name, c.year_of_study, c.status, c.created_at,
                       p.name AS program_name, p.code AS program_code, p.department_id,
                       d.name AS department_name, d.code AS department_code,
                       i.name AS intake_name, i.code AS intake_code, ay.name AS academic_year_name
                FROM classes c
                JOIN programs p ON p.id = c.program_id
                JOIN departments d ON d.id = p.department_id
                JOIN intakes i ON i.id = c.intake_id
                JOIN academic_years ay ON ay.id = i.academic_year_id
                WHERE c.id = :id";
        return self::fetchOne($sql, ['id' => $id]);
    }
}
