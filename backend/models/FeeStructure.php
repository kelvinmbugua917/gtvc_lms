<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class FeeStructure extends Model
{
    public static function getAll(array $filters = []): array
    {
        $db = self::getDb();
        $sql = "
            SELECT 
                fs.*,
                p.name AS program_name,
                p.code AS program_code,
                ay.name AS academic_year_name,
                i.name AS intake_name,
                i.code AS intake_code
            FROM `fee_structures` fs
            JOIN `programs` p ON fs.program_id = p.id
            JOIN `academic_years` ay ON fs.academic_year_id = ay.id
            JOIN `intakes` i ON fs.intake_id = i.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['program_id'])) {
            $sql .= " AND fs.program_id = :program_id";
            $params['program_id'] = (int)$filters['program_id'];
        }

        if (!empty($filters['academic_year_id'])) {
            $sql .= " AND fs.academic_year_id = :academic_year_id";
            $params['academic_year_id'] = (int)$filters['academic_year_id'];
        }

        if (!empty($filters['intake_id'])) {
            $sql .= " AND fs.intake_id = :intake_id";
            $params['intake_id'] = (int)$filters['intake_id'];
        }

        $sql .= " ORDER BY fs.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById(int $id): ?array
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT 
                fs.*,
                p.name AS program_name,
                p.code AS program_code,
                ay.name AS academic_year_name,
                i.name AS intake_name
            FROM `fee_structures` fs
            JOIN `programs` p ON fs.program_id = p.id
            JOIN `academic_years` ay ON fs.academic_year_id = ay.id
            JOIN `intakes` i ON fs.intake_id = i.id
            WHERE fs.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        return $record ?: null;
    }

    public static function create(array $data): int
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            INSERT INTO `fee_structures` 
                (`program_id`, `academic_year_id`, `intake_id`, `term_semester`, `total_amount`, `description`, `created_at`)
            VALUES 
                (:program_id, :academic_year_id, :intake_id, :term_semester, :total_amount, :description, NOW())
        ");
        $stmt->execute([
            'program_id' => $data['program_id'],
            'academic_year_id' => $data['academic_year_id'],
            'intake_id' => $data['intake_id'],
            'term_semester' => $data['term_semester'] ?? 1,
            'total_amount' => $data['total_amount'],
            'description' => $data['description'] ?? null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            UPDATE `fee_structures`
            SET 
                `program_id` = :program_id,
                `academic_year_id` = :academic_year_id,
                `intake_id` = :intake_id,
                `term_semester` = :term_semester,
                `total_amount` = :total_amount,
                `description` = :description
            WHERE `id` = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'program_id' => $data['program_id'],
            'academic_year_id' => $data['academic_year_id'],
            'intake_id' => $data['intake_id'],
            'term_semester' => $data['term_semester'] ?? 1,
            'total_amount' => $data['total_amount'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public static function delete(int $id): bool
    {
        $db = self::getDb();
        $stmt = $db->prepare("DELETE FROM `fee_structures` WHERE `id` = :id");
        return $stmt->execute(['id' => $id]);
    }
}
