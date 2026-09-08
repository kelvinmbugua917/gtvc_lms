<?php if ($flashSuccess = \App\Core\Session::getFlash('success')): ?>
    <div class="alert alert-success" style="background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-weight: 500;">
        ✓ <?= \App\Core\View::e($flashSuccess) ?>
    </div>
<?php endif; ?>

<?php if ($flashError = \App\Core\Session::getFlash('error')): ?>
    <div class="alert alert-danger" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-weight: 500;">
        ✕ <?= \App\Core\View::e($flashError) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">M-Pesa & Bank Receipts Verification Desk</h3>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Reference No</th>
                    <th>Student Name</th>
                    <th>Payment Method</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Verification</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><strong><?= \App\Core\View::e($p['transaction_reference']) ?></strong></td>
                            <td>
                                <?= \App\Core\View::e(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) ?> 
                                <span style="font-size: 0.8rem; color: var(--text-muted);">(<?= \App\Core\View::e($p['admission_number'] ?? ('Student #' . $p['student_id'])) ?>)</span>
                            </td>
                            <td><?= \App\Core\View::e(ucwords(str_replace('_', ' ', $p['payment_method'] ?? 'Bank Deposit'))) ?></td>
                            <td>KES <?= number_format((float)$p['amount'], 2) ?></td>
                            <td><?= \App\Core\View::e(date('Y-m-d H:i', strtotime($p['payment_date']))) ?></td>
                            <td>
                                <?php if (($p['status'] ?? 'pending') === 'verified'): ?>
                                    <span class="badge badge-success">✓ VERIFIED & POSTED</span>
                                <?php elseif (($p['status'] ?? 'pending') === 'rejected'): ?>
                                    <span class="badge badge-danger">✕ REJECTED</span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="background: #fef3c7; color: #92400e;">⏳ PENDING VERIFICATION</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm <?= (($p['status'] ?? 'pending') === 'pending') ? 'btn-primary' : 'btn-secondary' ?>" 
                                        onclick="openVerifyModal(<?= (int)$p['id'] ?>, '<?= \App\Core\View::e($p['transaction_reference']) ?>', '<?= \App\Core\View::e(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) ?>', 'KES <?= number_format((float)$p['amount'], 2) ?>')">
                                    <?= (($p['status'] ?? 'pending') === 'pending') ? 'Inspect Receipt & Verify' : 'View Receipt' ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td><strong>QK89123891</strong></td>
                        <td>Brian Otieno (GTVC/ICT/2024/0089)</td>
                        <td>KCB Bank Slip Photo</td>
                        <td>KES 15,000.00</td>
                        <td>2026-07-02</td>
                        <td><span class="badge badge-warning">⏳ PENDING VERIFICATION</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="openVerifyModal(1, 'QK89123891', 'Brian Otieno', 'KES 15,000.00')">Inspect Receipt & Verify</button>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Verify Payment Modal -->
<div class="modal-backdrop" id="verifyModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 520px;">
        <div class="card-header">
            <h3 class="card-title">Inspect Bank Deposit Slip & Post Payment</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeModal('verifyModal')">✕</button>
        </div>

        <div style="text-align: center; margin-bottom: 1rem; background: #f1f5f9; padding: 1rem; border-radius: 8px;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 0.5rem;">Uploaded Payment Receipt Photo</div>
            <div style="width: 100%; height: 140px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; border-radius: 6px; border: 1px dashed #94a3b8;">
                <span style="color: #64748b; font-size: 0.875rem;" id="modalReceiptInfo">📄 Bank Slip Scan: (Ref: QK89123891)</span>
            </div>
        </div>

        <form id="verifyForm" action="<?= \App\Core\View::url('/api/v1/finance/payments/1/verify') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">Verification Action</label>
                <select name="status" class="form-control">
                    <option value="verified">Approve & Post to Student Fee Ledger</option>
                    <option value="rejected">Reject (Invalid / Fraudulent Receipt)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Audit Remarks / Internal Notes</label>
                <input type="text" name="remarks" class="form-control" placeholder="e.g. Verified with KCB Nakuru Branch statement">
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('verifyModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Verification Outcome</button>
            </div>
        </form>
    </div>
</div>

<script>
function openVerifyModal(id, ref, student, amount) {
    var form = document.getElementById('verifyForm');
    if (form) {
        form.action = '<?= \App\Core\View::url('/api/v1/finance/payments/') ?>' + id + '/verify';
    }
    var info = document.getElementById('modalReceiptInfo');
    if (info) {
        info.innerText = '📄 Bank Slip Scan: ' + student + ' (' + ref + ' - ' + amount + ')';
    }
    openModal('verifyModal');
}
</script>
