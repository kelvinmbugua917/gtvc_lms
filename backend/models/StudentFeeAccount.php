<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class StudentFeeAccount extends Model
{
    public static function getByStudentId(int $studentId): ?array
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT 
                sfa.*,
                sp.admission_number,
                u.first_name,
                u.last_name,
                u.email,
                p.name AS program_name,
                p.code AS program_code,
                d.name AS department_name
            FROM `student_fee_accounts` sfa
            JOIN `student_profiles` sp ON sfa.student_id = sp.id
            JOIN `users` u ON sp.user_id = u.id
            LEFT JOIN `programs` p ON sp.program_id = p.id
            LEFT JOIN `departments` d ON p.department_id = d.id
            WHERE sfa.student_id = :student_id
        ");
        $stmt->execute(['student_id' => $studentId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        return $record ?: null;
    }

    public static function getOrCreateAccount(int $studentId): array
    {
        $account = self::getByStudentId($studentId);
        if ($account) {
            return $account;
        }

        $db = self::getDb();
        $stmt = $db->prepare("
            INSERT INTO `student_fee_accounts`
                (`student_id`, `total_billed`, `total_paid`, `current_balance`, `clearance_status`, `updated_at`)
            VALUES
                (:student_id, 0.00, 0.00, 0.00, 'pending', NOW())
            ON DUPLICATE KEY UPDATE `updated_at` = NOW()
        ");
        $stmt->execute(['student_id' => $studentId]);

        return self::getByStudentId($studentId) ?? [
            'student_id' => $studentId,
            'total_billed' => 0.00,
            'total_paid' => 0.00,
            'current_balance' => 0.00,
            'clearance_status' => 'pending'
        ];
    }

    public static function getAll(array $filters = []): array
    {
        $db = self::getDb();
        $sql = "
            SELECT 
                sfa.*,
                sp.admission_number,
                u.first_name,
                u.last_name,
                u.email,
                p.name AS program_name,
                p.code AS program_code,
                d.name AS department_name
            FROM `student_fee_accounts` sfa
            JOIN `student_profiles` sp ON sfa.student_id = sp.id
            JOIN `users` u ON sp.user_id = u.id
            LEFT JOIN `programs` p ON sp.program_id = p.id
            LEFT JOIN `departments` d ON p.department_id = d.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['clearance_status'])) {
            $sql .= " AND sfa.clearance_status = :clearance_status";
            $params['clearance_status'] = $filters['clearance_status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (sp.admission_number LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY sfa.current_balance DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function recalculateBalance(int $studentId): array
    {
        $db = self::getDb();

        // Calculate total billed from non-cancelled invoices
        $stmtInv = $db->prepare("
            SELECT COALESCE(SUM(amount), 0.00) AS total_billed 
            FROM `invoices` 
            WHERE student_id = :student_id AND status != 'cancelled'
        ");
        $stmtInv->execute(['student_id' => $studentId]);
        $totalBilled = (float)$stmtInv->fetchColumn();

        // Calculate total paid from verified payments
        $stmtPay = $db->prepare("
            SELECT COALESCE(SUM(amount), 0.00) AS total_paid 
            FROM `payments` 
            WHERE student_id = :student_id AND status = 'verified'
        ");
        $stmtPay->execute(['student_id' => $studentId]);
        $totalPaid = (float)$stmtPay->fetchColumn();

        $currentBalance = $totalBilled - $totalPaid;

        // Default clearance logic: balance <= 0 => cleared, else pending
        $clearanceStatus = ($currentBalance <= 0) ? 'cleared' : 'pending';

        $stmtUpd = $db->prepare("
            INSERT INTO `student_fee_accounts`
                (`student_id`, `total_billed`, `total_paid`, `current_balance`, `clearance_status`, `updated_at`)
            VALUES
                (:student_id, :total_billed, :total_paid, :current_balance, :clearance_status, NOW())
            ON DUPLICATE KEY UPDATE
                `total_billed` = VALUES(`total_billed`),
                `total_paid` = VALUES(`total_paid`),
                `current_balance` = VALUES(`current_balance`),
                `clearance_status` = VALUES(`clearance_status`),
                `updated_at` = NOW()
        ");
        $stmtUpd->execute([
            'student_id' => $studentId,
            'total_billed' => $totalBilled,
            'total_paid' => $totalPaid,
            'current_balance' => $currentBalance,
            'clearance_status' => $clearanceStatus
        ]);

        return [
            'student_id' => $studentId,
            'total_billed' => $totalBilled,
            'total_paid' => $totalPaid,
            'current_balance' => $currentBalance,
            'clearance_status' => $clearanceStatus
        ];
    }

    public static function updateClearanceStatus(int $studentId, string $status): bool
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            UPDATE `student_fee_accounts`
            SET `clearance_status` = :status, `updated_at` = NOW()
            WHERE `student_id` = :student_id
        ");
        return $stmt->execute([
            'student_id' => $studentId,
            'status' => $status
        ]);
    }
}
