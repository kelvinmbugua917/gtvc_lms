<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
    <div class="stat-card">
        <div>
            <div class="stat-label">Term Fee Billed</div>
            <div class="stat-value">KES 22,500</div>
        </div>
        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">📑</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Paid</div>
            <div class="stat-value" style="color: #047857;">KES 22,500</div>
        </div>
        <div class="stat-icon" style="background: #d1fae5; color: #047857;">💳</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Outstanding Balance</div>
            <div class="stat-value">KES 0.00</div>
        </div>
        <div class="stat-icon" style="background: #ccfbf1; color: #0f766e;">🛡️</div>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Fee Transactions & M-Pesa Receipts</h3>
        <button class="btn btn-sm btn-primary" onclick="openModal('payModal')">Submit Payment Reference</button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Receipt Ref</th>
                    <th>Payment Method</th>
                    <th>Amount (KES)</th>
                    <th>Transaction Date</th>
                    <th>Verification Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>QK89123891</strong></td>
                    <td>M-Pesa Paybill (522522)</td>
                    <td>KES 15,000.00</td>
                    <td>2026-07-02 11:20</td>
                    <td><span class="badge badge-success">VERIFIED & POSTED</span></td>
                </tr>
                <tr>
                    <td><strong>QK77182910</strong></td>
                    <td>KCB Bank Deposit</td>
                    <td>KES 7,500.00</td>
                    <td>2026-06-15 14:10</td>
                    <td><span class="badge badge-success">VERIFIED & POSTED</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Pay Modal -->
<div class="modal-backdrop" id="payModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 480px;">
        <div class="card-header">
            <h3 class="card-title">Log M-Pesa or Bank Payment</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeModal('payModal')">✕</button>
        </div>

        <form action="/api/v1/finance/payments" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-control" required>
                    <option value="mpesa">M-Pesa Paybill / Till Number</option>
                    <option value="bank_deposit">KCB / Equity Bank Receipt</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">M-Pesa / Bank Reference Code</label>
                <input type="text" name="reference_number" class="form-control" placeholder="e.g. QK89123891" required>
            </div>

            <div class="form-group">
                <label class="form-label">Amount Paid (KES)</label>
                <input type="number" name="amount" class="form-control" placeholder="22500" required>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('payModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit for Verification</button>
            </div>
        </form>
    </div>
</div>
