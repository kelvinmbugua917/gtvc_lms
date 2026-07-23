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
                <tr>
                    <td><strong>QK89123891</strong></td>
                    <td>Brian Otieno (GTVC/ICT/2024/0089)</td>
                    <td>KCB Bank Slip Photo</td>
                    <td>KES 15,000.00</td>
                    <td>2026-07-02</td>
                    <td><span class="badge badge-warning">PENDING VERIFICATION</span></td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="openModal('verifyModal')">Inspect Receipt & Verify</button>
                    </td>
                </tr>
                <tr>
                    <td><strong>QK77182910</strong></td>
                    <td>Jane Kamau (GTVC/ELEC/2024/0012)</td>
                    <td>Equity Bank Deposit</td>
                    <td>KES 22,500.00</td>
                    <td>2026-06-15</td>
                    <td><span class="badge badge-success">VERIFIED & POSTED</span></td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="openModal('verifyModal')">View Receipt</button>
                    </td>
                </tr>
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
            <div style="width: 100%; height: 180px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; border-radius: 6px; border: 1px dashed #94a3b8;">
                <span style="color: #64748b; font-size: 0.875rem;">📄 Bank Slip Scan: KCB Deposit (Ref: QK89123891 - KES 15,000)</span>
            </div>
        </div>

        <form action="<?= \App\Core\View::url('/api/v1/finance/payments/1/verify') ?>" method="POST">
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
