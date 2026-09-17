<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title" style="margin: 0;">Term Invoices & Auto-Billing Ledger</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Manage student fee invoices generated per semester and academic intake.</p>
        </div>
        <form method="GET" action="" style="display: flex; align-items: center; gap: 0.5rem; margin: 0; flex-wrap: wrap;">
            <input type="text" name="search" value="<?= \App\Core\View::e($search ?? '') ?>" placeholder="Search invoice, student, adm..." class="form-control" style="width: 210px; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
            <select name="status" onchange="this.form.submit()" class="form-control" style="width: auto; padding: 0.375rem 0.5rem; font-size: 0.875rem;">
                <option value="all" <?= empty($status) || $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="unpaid" <?= ($status ?? '') === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                <option value="partial" <?= ($status ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
                <option value="paid" <?= ($status ?? '') === 'paid' ? 'selected' : '' ?>>Paid in Full</option>
                <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
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
                    <th>Invoice No</th>
                    <th>Student Name & Reg</th>
                    <th>Fee Structure / Description</th>
                    <th>Due Date</th>
                    <th>Total Billed</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($invoices)): ?>
                    <?php foreach ($invoices as $inv): ?>
                        <?php
                            $studentName = trim(($inv['first_name'] ?? '') . ' ' . ($inv['last_name'] ?? ''));
                            if (empty($studentName)) {
                                $studentName = $inv['email'] ?? 'Student #' . $inv['student_id'];
                            }
                            $admNo = $inv['admission_number'] ?? 'N/A';
                            $invStatus = strtolower($inv['status'] ?? 'unpaid');
                        ?>
                        <tr>
                            <td><strong><?= \App\Core\View::e($inv['invoice_number']) ?></strong></td>
                            <td>
                                <strong><?= \App\Core\View::e($studentName) ?></strong>
                                <div style="font-size: 0.8rem; color: #475569; font-family: monospace;"><?= \App\Core\View::e($admNo) ?></div>
                            </td>
                            <td><?= \App\Core\View::e($inv['fee_structure_description'] ?? 'Tuition & Amenities Term Fee') ?></td>
                            <td><?= !empty($inv['due_date']) ? \App\Core\View::e(date('Y-m-d', strtotime($inv['due_date']))) : '<span style="color:#94a3b8;">N/A</span>' ?></td>
                            <td><strong>KES <?= number_format((float)$inv['amount'], 2) ?></strong></td>
                            <td>
                                <?php if ($invStatus === 'paid'): ?>
                                    <span class="badge badge-success">✓ PAID IN FULL</span>
                                <?php elseif ($invStatus === 'partial'): ?>
                                    <span class="badge badge-info">PARTIALLY PAID</span>
                                <?php elseif ($invStatus === 'cancelled'): ?>
                                    <span class="badge badge-secondary">CANCELLED</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">UNPAID</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: #64748b;">
                            No fee invoices found matching the specified filters.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= isset($paginator) ? $paginator->render() : '' ?>
</div>
