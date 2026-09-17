<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title" style="margin: 0;">Department Trainers & Teaching Loads</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Inspect academic staff teaching assignments and contact details.</p>
        </div>
        <form method="GET" action="" style="display: flex; align-items: center; gap: 0.5rem; margin: 0; flex-wrap: wrap;">
            <input type="text" name="search" value="<?= \App\Core\View::e($search ?? '') ?>" placeholder="Search trainer name, email..." class="form-control" style="width: 210px; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
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
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Trainer Name</th>
                    <th>Email Address</th>
                    <th>Phone</th>
                    <th>Assigned Course Units</th>
                    <th>Account Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($lecturers)): ?>
                    <?php foreach ($lecturers as $lec): ?>
                        <?php
                            $name = trim(($lec['first_name'] ?? '') . ' ' . ($lec['last_name'] ?? ''));
                            if (empty($name)) {
                                $name = $lec['email'] ?? 'Trainer #' . $lec['id'];
                            }
                            $isActive = (int)($lec['is_active'] ?? 1) === 1;
                            $units = (int)($lec['units_count'] ?? 0);
                        ?>
                        <tr>
                            <td><strong><?= \App\Core\View::e($name) ?></strong></td>
                            <td><?= \App\Core\View::e($lec['email'] ?? 'N/A') ?></td>
                            <td><?= \App\Core\View::e($lec['phone'] ?: 'N/A') ?></td>
                            <td>
                                <span class="badge badge-info"><?= $units ?> Assigned <?= $units === 1 ? 'Unit' : 'Units' ?></span>
                            </td>
                            <td>
                                <?php if ($isActive): ?>
                                    <span class="badge badge-success">ACTIVE</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">INACTIVE</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: #64748b;">
                            No trainers or lecturers found matching the query.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= isset($paginator) ? $paginator->render() : '' ?>
</div>
