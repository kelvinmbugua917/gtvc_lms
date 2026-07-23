<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Payment extends Model
{
    public static function getAll(array $filters = []): array
    {
        $db = self::getDb();
        $sql = "
            SELECT 
                p.*,
                sp.admission_number,
                u.first_name,
                u.last_name,
                u.email,
                inv.invoice_number,
                v.first_name AS verifier_first_name,
                v.last_name AS verifier_last_name
            FROM `payments` p
            JOIN `student_profiles` sp ON p.student_id = sp.id
            JOIN `users` u ON sp.user_id = u.id
            LEFT JOIN `invoices` inv ON p.invoice_id = inv.id
            LEFT JOIN `users` v ON p.verified_by_user_id = v.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['student_id'])) {
            $sql .= " AND p.student_id = :student_id";
            $params['student_id'] = (int)$filters['student_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['payment_method'])) {
            $sql .= " AND p.payment_method = :payment_method";
            $params['payment_method'] = $filters['payment_method'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.transaction_reference LIKE :search OR sp.admission_number LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY p.payment_date DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById(int $id): ?array
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT 
                p.*,
                sp.admission_number,
                u.first_name,
                u.last_name,
                u.email,
                inv.invoice_number,
                v.first_name AS verifier_first_name,
                v.last_name AS verifier_last_name
            FROM `payments` p
            JOIN `student_profiles` sp ON p.student_id = sp.id
            JOIN `users` u ON sp.user_id = u.id
            LEFT JOIN `invoices` inv ON p.invoice_id = inv.id
            LEFT JOIN `users` v ON p.verified_by_user_id = v.id
            WHERE p.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        return $record ?: null;
    }

    public static function create(array $data): int
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            INSERT INTO `payments`
                (`transaction_reference`, `student_id`, `invoice_id`, `amount`, `payment_method`, `payment_date`, `verified_by_user_id`, `status`)
            VALUES
                (:transaction_reference, :student_id, :invoice_id, :amount, :payment_method, :payment_date, :verified_by_user_id, :status)
        ");
        
        $paymentDate = $data['payment_date'] ?? date('Y-m-d H:i:s');
        $status = $data['status'] ?? 'verified';

        $stmt->execute([
            'transaction_reference' => $data['transaction_reference'],
            'student_id' => $data['student_id'],
            'invoice_id' => $data['invoice_id'] ?? null,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] ?? 'mpesa',
            'payment_date' => $paymentDate,
            'verified_by_user_id' => $data['verified_by_user_id'] ?? null,
            'status' => $status
        ]);

        $paymentId = (int)$db->lastInsertId();

        // If verified, recalculate fee account balance & invoice status
        if ($status === 'verified') {
            StudentFeeAccount::recalculateBalance((int)$data['student_id']);
            if (!empty($data['invoice_id'])) {
                self::updateInvoicePaymentStatus((int)$data['invoice_id']);
            }
        }

        return $paymentId;
    }

    public static function verify(int $id, int $verifierUserId, string $status = 'verified'): bool
    {
        $db = self::getDb();
        $payment = self::getById($id);
        if (!$payment) {
            return false;
        }

        $stmt = $db->prepare("
            UPDATE `payments`
            SET `status` = :status, `verified_by_user_id` = :verifier_user_id
            WHERE `id` = :id
        ");
        $updated = $stmt->execute([
            'id' => $id,
            'status' => $status,
            'verifier_user_id' => $verifierUserId
        ]);

        if ($updated) {
            StudentFeeAccount::recalculateBalance((int)$payment['student_id']);
            if (!empty($payment['invoice_id'])) {
                self::updateInvoicePaymentStatus((int)$payment['invoice_id']);
            }
        }

        return $updated;
    }

    private static function updateInvoicePaymentStatus(int $invoiceId): void
    {
        $db = self::getDb();
        $invoice = Invoice::getById($invoiceId);
        if (!$invoice) {
            return;
        }

        // Sum up verified payments for this invoice
        $stmtPay = $db->prepare("
            SELECT COALESCE(SUM(amount), 0.00) 
            FROM `payments` 
            WHERE invoice_id = :invoice_id AND status = 'verified'
        ");
        $stmtPay->execute(['invoice_id' => $invoiceId]);
        $totalPaid = (float)$stmtPay->fetchColumn();

        $invoiceAmount = (float)$invoice['amount'];

        $newStatus = 'unpaid';
        if ($totalPaid >= $invoiceAmount) {
            $newStatus = 'paid';
        } elseif ($totalPaid > 0) {
            $newStatus = 'partially_paid';
        }

        Invoice::updateStatus($invoiceId, $newStatus);
    }
}
