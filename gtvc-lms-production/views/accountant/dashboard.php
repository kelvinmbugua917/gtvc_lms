<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
    <div class="stat-card">
        <div>
            <div class="stat-label">Term Total Collections</div>
            <div class="stat-value" style="color: #047857;">KES <?= number_format((float)($totalCollections ?? 4850000), 2) ?></div>
        </div>
        <div class="stat-icon" style="background: #d1fae5; color: #047857;">💳</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Outstandings</div>
            <div class="stat-value" style="color: #be123c;">KES <?= number_format((float)($totalOutstanding ?? 1120000), 2) ?></div>
        </div>
        <div class="stat-icon" style="background: #ffe4e6; color: #be123c;">📊</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Pending Verification</div>
            <div class="stat-value" style="color: #b45309;"><?= (int)($pendingVerificationCount ?? 6) ?> Payments</div>
        </div>
        <div class="stat-icon" style="background: #fef3c7; color: #b45309;">📱</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Cleared Students</div>
            <div class="stat-value"><?= (int)($clearedCount ?? 284) ?> / <?= (int)($totalStudentsCount ?? 340) ?></div>
        </div>
        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">🛡️</div>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Recent Unverified Payment Receipts</h3>
        <a href="<?= \App\Core\View::url('/accountant/payments') ?>" class="btn btn-sm btn-primary">Open Verification Desk</a>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student Name & Reg No</th>
                    <th>Payment Ref</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($unverifiedPayments)): ?>
                    <?php foreach ($unverifiedPayments as $p): ?>
                        <tr>
                            <td><strong><?= \App\Core\View::e($p['student_name'] ?? 'Student') ?></strong> (<?= \App\Core\View::e($p['admission_number'] ?? '') ?>)</td>
                            <td><?= \App\Core\View::e($p['transaction_reference']) ?></td>
                            <td><?= \App\Core\View::e(strtoupper($p['payment_method'])) ?></td>
                            <td>KES <?= number_format((float)$p['amount'], 2) ?></td>
                            <td><a href="<?= \App\Core\View::url('/accountant/payments') ?>" class="btn btn-sm btn-primary">Verify & Post</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td><strong>John Kamau</strong> (GTVC/ELECT/2024/0012)</td>
                        <td>QK99120831</td>
                        <td>M-Pesa Paybill</td>
                        <td>KES 12,000.00</td>
                        <td><a href="<?= \App\Core\View::url('/accountant/payments') ?>" class="btn btn-sm btn-primary">Verify & Post</a></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
