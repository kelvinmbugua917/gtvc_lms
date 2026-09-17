<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title" style="margin: 0;">Approved Program Fee Structures</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Manage academic tuition breakdown and mandatory semester term fees.</p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <form method="GET" action="" style="display: flex; align-items: center; gap: 0.5rem; margin: 0; flex-wrap: wrap;">
                <input type="text" name="search" value="<?= \App\Core\View::e($search ?? '') ?>" placeholder="Search program, code..." class="form-control" style="width: 200px; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                <select name="per_page" onchange="this.form.submit()" class="form-control" style="width: auto; padding: 0.375rem 0.5rem; font-size: 0.875rem;">
                    <option value="10" <?= ($perPage ?? 15) === 10 ? 'selected' : '' ?>>10 / page</option>
                    <option value="15" <?= ($perPage ?? 15) === 15 ? 'selected' : '' ?>>15 / page</option>
                    <option value="25" <?= ($perPage ?? 15) === 25 ? 'selected' : '' ?>>25 / page</option>
                    <option value="50" <?= ($perPage ?? 15) === 50 ? 'selected' : '' ?>>50 / page</option>
                </select>
                <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
                <?php if (!empty($search)): ?>
                    <a href="?" class="btn btn-sm btn-secondary" title="Clear filters">✕</a>
                <?php endif; ?>
            </form>
            <button class="btn btn-sm btn-primary" onclick="openModal('feeStructureModal')">+ Create Fee Structure</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Program Title</th>
                    <th>Academic Year</th>
                    <th>Term / Intake</th>
                    <th>Total Fee (KES)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($feeStructures)): ?>
                    <?php foreach ($feeStructures as $fs): ?>
                        <tr>
                            <td>
                                <strong><?= \App\Core\View::e($fs['program_name'] ?? 'N/A') ?></strong>
                                <div style="font-size: 0.8rem; color: #64748b;"><?= \App\Core\View::e($fs['program_code'] ?? '') ?></div>
                            </td>
                            <td><?= \App\Core\View::e($fs['academic_year_name'] ?? '2025/2026') ?></td>
                            <td>
                                <?= \App\Core\View::e($fs['term_semester'] ?? 'Term 1') ?>
                                <?php if (!empty($fs['intake_name'])): ?>
                                    <span style="font-size: 0.8rem; color: #64748b;">(<?= \App\Core\View::e($fs['intake_name']) ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td><strong>KES <?= number_format((float)$fs['total_amount'], 2) ?></strong></td>
                            <td><button class="btn btn-sm btn-secondary" onclick="openModal('feeStructureModal')">Edit Items</button></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: #64748b;">
                            No fee structures found. Click "+ Create Fee Structure" to define one.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= isset($paginator) ? $paginator->render() : '' ?>
</div>

<!-- Fee Structure Modal -->
<div class="modal-backdrop" id="feeStructureModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 500px;">
        <div class="card-header">
            <h3 class="card-title">Create / Edit Program Fee Structure</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeModal('feeStructureModal')">✕</button>
        </div>

        <form action="<?= \App\Core\View::url('/api/v1/finance/fee-structures') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">Academic Program</label>
                <select name="program_id" class="form-control" required>
                    <option value="1">Diploma in ICT (DICT)</option>
                    <option value="2">Diploma in Electrical Engineering</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Academic Year & Term</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <input type="text" name="academic_year" class="form-control" value="2025/2026" required>
                    <select name="term" class="form-control">
                        <option value="Term 1">Term 1</option>
                        <option value="Term 2" selected>Term 2</option>
                        <option value="Term 3">Term 3</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Tuition & Operations Fee (KES)</label>
                <input type="number" name="tuition_fee" class="form-control" placeholder="22500" required>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('feeStructureModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Fee Structure</button>
            </div>
        </form>
    </div>
</div>
