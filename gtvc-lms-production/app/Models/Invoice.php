<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Invoice extends Model
{
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('Y') . '-';
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        return $prefix . $random;
    }

    public static function getAll(array $filters = []): array
    {
        $db = self::getDb();
        $sql = "
            SELECT 
                i.*,
                sp.admission_number,
                u.first_name,
                u.last_name,
                u.email,
                fs.description AS fee_structure_description,
                p.code AS program_code
            FROM `invoices` i
            JOIN `student_profiles` sp ON i.student_id = sp.id
            JOIN `users` u ON sp.user_id = u.id
            LEFT JOIN `fee_structures` fs ON i.fee_structure_id = fs.id
            LEFT JOIN `programs` p ON sp.program_id = p.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['student_id'])) {
            $sql .= " AND i.student_id = :student_id";
            $params['student_id'] = (int)$filters['student_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND i.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (i.invoice_number LIKE :search OR sp.admission_number LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY i.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById(int $id): ?array
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT 
                i.*,
                sp.admission_number,
                u.first_name,
                u.last_name,
                u.email,
                fs.description AS fee_structure_description,
                p.code AS program_code,
                p.name AS program_name
            FROM `invoices` i
            JOIN `student_profiles` sp ON i.student_id = sp.id
            JOIN `users` u ON sp.user_id = u.id
            LEFT JOIN `fee_structures` fs ON i.fee_structure_id = fs.id
            LEFT JOIN `programs` p ON sp.program_id = p.id
            WHERE i.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        return $record ?: null;
    }

    public static function create(array $data): int
    {
        $db = self::getDb();
        $invoiceNumber = $data['invoice_number'] ?? self::generateInvoiceNumber();

        $stmt = $db->prepare("
            INSERT INTO `invoices`
                (`invoice_number`, `student_id`, `fee_structure_id`, `amount`, `due_date`, `status`, `created_at`)
            VALUES
                (:invoice_number, :student_id, :fee_structure_id, :amount, :due_date, :status, NOW())
        ");
        $stmt->execute([
            'invoice_number' => $invoiceNumber,
            'student_id' => $data['student_id'],
            'fee_structure_id' => $data['fee_structure_id'] ?? null,
            'amount' => $data['amount'],
            'due_date' => $data['due_date'],
            'status' => $data['status'] ?? 'unpaid'
        ]);

        $invoiceId = (int)$db->lastInsertId();

        // Recalculate student fee account
        StudentFeeAccount::recalculateBalance((int)$data['student_id']);

        return $invoiceId;
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $db = self::getDb();
        $invoice = self::getById($id);
        if (!$invoice) {
            return false;
        }

        $stmt = $db->prepare("
            UPDATE `invoices`
            SET `status` = :status
            WHERE `id` = :id
        ");
        $updated = $stmt->execute([
            'id' => $id,
            'status' => $status
        ]);

        if ($updated) {
            StudentFeeAccount::recalculateBalance((int)$invoice['student_id']);
        }

        return $updated;
    }
}
