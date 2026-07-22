<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AcademicYear extends Model
{
    public static function getAllYears(): array
    {
        $sql = "SELECT id, name, start_date, end_date, is_current, created_at
                FROM academic_years
                ORDER BY start_date DESC";
        return self::fetchAll($sql);
    }

    public static function getYearById(int $id): ?array
    {
        $sql = "SELECT id, name, start_date, end_date, is_current, created_at
                FROM academic_years
                WHERE id = :id";
        return self::fetchOne($sql, ['id' => $id]);
    }

    public static function getCurrentYear(): ?array
    {
        $sql = "SELECT id, name, start_date, end_date, is_current, created_at
                FROM academic_years
                WHERE is_current = 1
                LIMIT 1";
        return self::fetchOne($sql);
    }

    public static function getAllIntakes(?int $academicYearId = null): array
    {
        $sql = "SELECT i.id, i.academic_year_id, i.name, i.code, i.start_date, i.end_date, i.status, i.created_at,
                       ay.name AS academic_year_name
                FROM intakes i
                JOIN academic_years ay ON ay.id = i.academic_year_id";
        
        $params = [];
        if ($academicYearId !== null) {
            $sql .= " WHERE i.academic_year_id = :academic_year_id";
            $params['academic_year_id'] = $academicYearId;
        }

        $sql .= " ORDER BY i.start_date DESC";
        return self::fetchAll($sql, $params);
    }

    public static function getIntakeById(int $id): ?array
    {
        $sql = "SELECT i.id, i.academic_year_id, i.name, i.code, i.start_date, i.end_date, i.status, i.created_at,
                       ay.name AS academic_year_name
                FROM intakes i
                JOIN academic_years ay ON ay.id = i.academic_year_id
                WHERE i.id = :id";
        return self::fetchOne($sql, ['id' => $id]);
    }
}
