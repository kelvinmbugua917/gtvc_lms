<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title" style="margin: 0;">Student Fee Balances & Billing Ledger</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Track invoices, verified receipts, and current outstanding balances per student.</p>
        </div>
        <form method="GET" action="" style="display: flex; align-items: center; gap: 0.5rem; margin: 0; flex-wrap: wrap;">
            <input type="text" name="search" value="<?= \App\Core\View::e($search ?? '') ?>" placeholder="Search student, adm no..." class="form-control" style="width: 210px; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
            <select name="status" onchange="this.form.submit()" class="form-control" style="width: auto; padding: 0.375rem 0.5rem; font-size: 0.875rem;">
                <option value="all" <?= empty($status) || $status === 'all' ? 'selected' : '' ?>>All Clearances</option>
                <option value="cleared" <?= ($status ?? '') === 'cleared' ? 'selected' : '' ?>>Cleared</option>
                <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="blocked" <?= ($status ?? '') === 'blocked' ? 'selected' : '' ?>>Blocked</option>
            </select>
            <select name="per_page" onchange="this.form.submit()" class="form-control" style="width: auto; padding: 0.375rem 0.5rem; font-size: 0.875rem;">
                <option value="10" <?= ($perPage ?? 15) === 10 ? 'selected' : '' ?>>10 / page</option>
                <option value="15" <?= ($perPage ?? 15) === 15 ? 'selected' : '' ?>>15 / page</option>
                <option value="25" <?= ($perPage ?? 15) === 25 ? 'selected' : '' ?>>25 / page</option>
                <option value="50" <?= ($perPage ?? 15) === 50 ? 'selected' : '' ?>>50 / page</option>
            </select>
            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
            <?php if (!empty($search) || (!empty($status) && $status !== 'all')): ?>
                <a href="?" class="btn btn-sm btn-secondary" title="Clear filters">✕</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student Reg No & Name</th>
                    <th>Program</th>
                    <th>Billed (KES)</th>
                    <th>Paid (KES)</th>
                    <th>Balance (KES)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($accounts)): ?>
                    <?php foreach ($accounts as $acc): ?>
                        <?php
                            $bal = (float)$acc['current_balance'];
                            $billed = (float)$acc['total_billed'];
                            $paid = (float)$acc['total_paid'];
                            $studentName = trim(($acc['first_name'] ?? '') . ' ' . ($acc['last_name'] ?? ''));
                            if (empty($studentName)) {
                                $studentName = $acc['email'] ?? 'Student #' . $acc['student_id'];
                            }
                            $admNo = $acc['admission_number'] ?? 'N/A';
                            $progName = $acc['program_name'] ?? ($acc['program_code'] ?? 'General Studies');
                            $clearance = strtolower($acc['clearance_status'] ?? 'pending');
                        ?>
                        <tr>
                            <td>
                                <strong><?= \App\Core\View::e($studentName) ?></strong>
                                <div style="font-size: 0.8rem; color: #475569; font-family: monospace;"><?= \App\Core\View::e($admNo) ?></div>
                            </td>
                            <td><?= \App\Core\View::e($progName) ?></td>
                            <td>KES <?= number_format($billed, 2) ?></td>
                            <td>KES <?= number_format($paid, 2) ?></td>
                            <td>
                                <strong style="color: <?= $bal <= 0 ? '#16a34a' : '#dc2626' ?>;">
                                    KES <?= number_format($bal, 2) ?>
                                </strong>
                            </td>
                            <td>
                                <?php if ($clearance === 'cleared' || $bal <= 0): ?>
                                    <span class="badge badge-success">✓ CLEARED</span>
                                <?php elseif ($clearance === 'blocked'): ?>
                                    <span class="badge badge-danger">✕ BLOCKED</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">⏳ PENDING</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: #64748b;">
                            No student fee account records found matching the criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= isset($paginator) ? $paginator->render() : '' ?>
</div>
