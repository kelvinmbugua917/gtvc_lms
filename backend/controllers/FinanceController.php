<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\FeeStructure;
use App\Models\StudentFeeAccount;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\AuditLog;
use App\Models\Notification;

class FinanceController
{
    /**
     * Check if user is staff/finance/admin
     */
    private function isFinanceAuthorized(array $currentUser): bool
    {
        $roles = array_column($currentUser['roles'], 'name');
        return !empty(array_intersect(['admin', 'super_admin', 'finance_officer', 'bursar', 'hod'], $roles));
    }

    /**
     * GET /api/v1/finance/fee-structures
     */
    public function getFeeStructures(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $filters = [
            'program_id' => $request->getParam('program_id'),
            'academic_year_id' => $request->getParam('academic_year_id'),
            'intake_id' => $request->getParam('intake_id')
        ];

        $structures = FeeStructure::getAll($filters);
        Response::json(['data' => $structures]);
    }

    /**
     * POST /api/v1/finance/fee-structures
     */
    public function createFeeStructure(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        if (!$this->isFinanceAuthorized($currentUser)) {
            Response::json(['error' => 'Forbidden: Unauthorized to create fee structures'], 403);
            return;
        }

        $body = $request->getBody();
        if (empty($body['program_id']) || empty($body['academic_year_id']) || empty($body['intake_id']) || !isset($body['total_amount'])) {
            Response::json(['error' => 'Missing required fields: program_id, academic_year_id, intake_id, total_amount'], 400);
            return;
        }

        $id = FeeStructure::create([
            'program_id' => (int)$body['program_id'],
            'academic_year_id' => (int)$body['academic_year_id'],
            'intake_id' => (int)$body['intake_id'],
            'term_semester' => (int)($body['term_semester'] ?? 1),
            'total_amount' => (float)$body['total_amount'],
            'description' => $body['description'] ?? null,
        ]);

        AuditLog::log((int)$currentUser['id'], 'FINANCE_FEE_STRUCTURE_CREATED', null, null, [
            'fee_structure_id' => $id,
            'total_amount' => $body['total_amount'],
            'program_id' => $body['program_id']
        ]);

        Response::json(['message' => 'Fee structure created successfully', 'id' => $id], 201);
    }

    /**
     * PUT /api/v1/finance/fee-structures/{id}
     */
    public function updateFeeStructure(Request $request, array $params): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        if (!$this->isFinanceAuthorized($currentUser)) {
            Response::json(['error' => 'Forbidden: Unauthorized to modify fee structures'], 403);
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $body = $request->getBody();

        $updated = FeeStructure::update($id, [
            'program_id' => (int)$body['program_id'],
            'academic_year_id' => (int)$body['academic_year_id'],
            'intake_id' => (int)$body['intake_id'],
            'term_semester' => (int)($body['term_semester'] ?? 1),
            'total_amount' => (float)$body['total_amount'],
            'description' => $body['description'] ?? null,
        ]);

        if (!$updated) {
            Response::json(['error' => 'Fee structure not found or update failed'], 404);
            return;
        }

        AuditLog::log((int)$currentUser['id'], 'FINANCE_FEE_STRUCTURE_UPDATED', null, null, [
            'fee_structure_id' => $id,
            'total_amount' => $body['total_amount']
        ]);

        Response::json(['message' => 'Fee structure updated successfully', 'id' => $id]);
    }

    /**
     * DELETE /api/v1/finance/fee-structures/{id}
     */
    public function deleteFeeStructure(Request $request, array $params): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        if (!$this->isFinanceAuthorized($currentUser)) {
            Response::json(['error' => 'Forbidden: Unauthorized to delete fee structures'], 403);
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $deleted = FeeStructure::delete($id);

        if (!$deleted) {
            Response::json(['error' => 'Fee structure not found or deletion failed'], 404);
            return;
        }

        AuditLog::log((int)$currentUser['id'], 'FINANCE_FEE_STRUCTURE_DELETED', null, null, ['fee_structure_id' => $id]);

        Response::json(['message' => 'Fee structure deleted successfully', 'id' => $id]);
    }

    /**
     * GET /api/v1/finance/student-accounts
     */
    public function getStudentAccounts(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roles = array_column($currentUser['roles'], 'name');

        if (in_array('student', $roles, true) && !$this->isFinanceAuthorized($currentUser)) {
            $studentProfileId = (int)($currentUser['profile']['id'] ?? 0);
            $account = StudentFeeAccount::getOrCreateAccount($studentProfileId);
            Response::json(['data' => [$account]]);
            return;
        }

        $filters = [
            'clearance_status' => $request->getParam('clearance_status'),
            'search' => $request->getParam('search')
        ];

        $accounts = StudentFeeAccount::getAll($filters);
        Response::json(['data' => $accounts]);
    }

    /**
     * GET /api/v1/finance/student-accounts/{student_id}
     */
    public function getStudentAccountById(Request $request, array $params): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $studentId = (int)($params['student_id'] ?? 0);
        $roles = array_column($currentUser['roles'], 'name');

        // BOLA / IDOR Protection
        if (in_array('student', $roles, true) && !$this->isFinanceAuthorized($currentUser)) {
            $ownProfileId = (int)($currentUser['profile']['id'] ?? 0);
            if ($studentId !== $ownProfileId) {
                Response::json(['error' => 'Forbidden: Cannot view financial account of another student'], 403);
                return;
            }
        }

        $account = StudentFeeAccount::getOrCreateAccount($studentId);
        $invoices = Invoice::getAll(['student_id' => $studentId]);
        $payments = Payment::getAll(['student_id' => $studentId]);

        Response::json([
            'data' => [
                'account' => $account,
                'invoices' => $invoices,
                'payments' => $payments
            ]
        ]);
    }

    /**
     * POST /api/v1/finance/invoices
     */
    public function createInvoice(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        if (!$this->isFinanceAuthorized($currentUser)) {
            Response::json(['error' => 'Forbidden: Unauthorized to issue invoices'], 403);
            return;
        }

        $body = $request->getBody();
        if (empty($body['student_id']) || !isset($body['amount']) || empty($body['due_date'])) {
            Response::json(['error' => 'Missing required fields: student_id, amount, due_date'], 400);
            return;
        }

        $invoiceId = Invoice::create([
            'student_id' => (int)$body['student_id'],
            'fee_structure_id' => !empty($body['fee_structure_id']) ? (int)$body['fee_structure_id'] : null,
            'amount' => (float)$body['amount'],
            'due_date' => $body['due_date'],
            'status' => $body['status'] ?? 'unpaid'
        ]);

        AuditLog::log((int)$currentUser['id'], 'FINANCE_INVOICE_CREATED', null, null, [
            'invoice_id' => $invoiceId,
            'student_id' => $body['student_id'],
            'amount' => $body['amount']
        ]);

        Response::json(['message' => 'Invoice created successfully', 'id' => $invoiceId], 201);
    }

    /**
     * GET /api/v1/finance/invoices
     */
    public function getInvoices(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roles = array_column($currentUser['roles'], 'name');

        $filters = [
            'student_id' => $request->getParam('student_id'),
            'status' => $request->getParam('status'),
            'search' => $request->getParam('search')
        ];

        // Enforce student BOLA scoping
        if (in_array('student', $roles, true) && !$this->isFinanceAuthorized($currentUser)) {
            $filters['student_id'] = (int)($currentUser['profile']['id'] ?? 0);
        }

        $invoices = Invoice::getAll($filters);
        Response::json(['data' => $invoices]);
    }

    /**
     * POST /api/v1/finance/payments
     */
    public function recordPayment(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $body = $request->getBody();

        if (empty($body['transaction_reference']) || empty($body['student_id']) || !isset($body['amount'])) {
            Response::json(['error' => 'Missing required fields: transaction_reference, student_id, amount'], 400);
            return;
        }

        $studentId = (int)$body['student_id'];
        $roles = array_column($currentUser['roles'], 'name');

        // BOLA check: if student is submitting payment reference, verify it's for self
        if (in_array('student', $roles, true) && !$this->isFinanceAuthorized($currentUser)) {
            $ownProfileId = (int)($currentUser['profile']['id'] ?? 0);
            if ($studentId !== $ownProfileId) {
                Response::json(['error' => 'Forbidden: Cannot submit payment for another student'], 403);
                return;
            }
        }

        $isFinance = $this->isFinanceAuthorized($currentUser);
        $status = $isFinance ? ($body['status'] ?? 'verified') : 'pending';
        $verifierId = $isFinance ? (int)$currentUser['id'] : null;

        $paymentId = Payment::create([
            'transaction_reference' => trim($body['transaction_reference']),
            'student_id' => $studentId,
            'invoice_id' => !empty($body['invoice_id']) ? (int)$body['invoice_id'] : null,
            'amount' => (float)$body['amount'],
            'payment_method' => $body['payment_method'] ?? 'mpesa',
            'payment_date' => $body['payment_date'] ?? date('Y-m-d H:i:s'),
            'verified_by_user_id' => $verifierId,
            'status' => $status
        ]);

        AuditLog::log((int)$currentUser['id'], 'FINANCE_PAYMENT_RECORDED', null, null, [
            'payment_id' => $paymentId,
            'transaction_reference' => $body['transaction_reference'],
            'amount' => $body['amount'],
            'status' => $status
        ]);

        Response::json(['message' => 'Payment recorded successfully', 'id' => $paymentId, 'status' => $status], 201);
    }

    /**
     * GET /api/v1/finance/payments
     */
    public function getPayments(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roles = array_column($currentUser['roles'], 'name');

        $filters = [
            'student_id' => $request->getParam('student_id'),
            'status' => $request->getParam('status'),
            'payment_method' => $request->getParam('payment_method'),
            'search' => $request->getParam('search')
        ];

        // Enforce student BOLA scoping
        if (in_array('student', $roles, true) && !$this->isFinanceAuthorized($currentUser)) {
            $filters['student_id'] = (int)($currentUser['profile']['id'] ?? 0);
        }

        $payments = Payment::getAll($filters);
        Response::json(['data' => $payments]);
    }

    /**
     * POST /api/v1/finance/payments/{id}/verify
     */
    public function verifyPayment(Request $request, array $params): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        if (!$this->isFinanceAuthorized($currentUser)) {
            Response::json(['error' => 'Forbidden: Unauthorized to verify payments'], 403);
            return;
        }

        $paymentId = (int)($params['id'] ?? 0);
        $body = $request->getBody();
        $status = $body['status'] ?? 'verified'; // 'verified' or 'rejected'

        $updated = Payment::verify($paymentId, (int)$currentUser['id'], $status);

        if (!$updated) {
            Response::json(['error' => 'Payment record not found or verification failed'], 404);
            return;
        }

        AuditLog::log((int)$currentUser['id'], 'FINANCE_PAYMENT_VERIFIED', null, null, [
            'payment_id' => $paymentId,
            'status' => $status
        ]);

        Response::json(['message' => "Payment updated to {$status}", 'id' => $paymentId]);
    }

    /**
     * POST /api/v1/finance/clearance/update
     */
    public function updateClearance(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        if (!$this->isFinanceAuthorized($currentUser)) {
            Response::json(['error' => 'Forbidden: Unauthorized to modify fee clearance status'], 403);
            return;
        }

        $body = $request->getBody();
        if (empty($body['student_id']) || empty($body['clearance_status'])) {
            Response::json(['error' => 'Missing student_id or clearance_status'], 400);
            return;
        }

        $studentId = (int)$body['student_id'];
        $status = $body['clearance_status']; // 'cleared', 'pending', 'blocked_exam'

        $updated = StudentFeeAccount::updateClearanceStatus($studentId, $status);

        AuditLog::log((int)$currentUser['id'], 'FINANCE_CLEARANCE_STATUS_UPDATED', null, null, [
            'student_id' => $studentId,
            'clearance_status' => $status
        ]);

        Response::json(['message' => 'Clearance status updated successfully', 'student_id' => $studentId, 'status' => $status]);
    }
}
