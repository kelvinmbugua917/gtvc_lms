<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title" style="margin: 0;">Department Student Roster & At-Risk Monitoring</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Review enrolled trainees, class assignments, and monitor student academic performance.</p>
        </div>
        <form method="GET" action="" style="display: flex; align-items: center; gap: 0.5rem; margin: 0; flex-wrap: wrap;">
            <input type="text" name="search" value="<?= \App\Core\View::e($search ?? '') ?>" placeholder="Search student, adm no, email..." class="form-control" style="width: 220px; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
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
                    <th>Student Name & Reg No</th>
                    <th>Program & Class</th>
                    <th>Contact Info</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $s): ?>
                        <?php
                            $name = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
                            $reg = $s['registration_number'] ?: ($s['index_number'] ?: 'Unassigned');
                            $prog = $s['program_name'] ?? ($s['program_code'] ?? 'N/A');
                            $class = $s['class_name'] ?? ($s['class_code'] ?? 'General');
                            $isActive = (int)($s['is_active'] ?? 1) === 1;
                        ?>
                        <tr>
                            <td>
                                <strong><?= \App\Core\View::e($name) ?></strong>
                                <div style="font-size: 0.8rem; color: #475569; font-family: monospace;"><?= \App\Core\View::e($reg) ?></div>
                            </td>
                            <td>
                                <?= \App\Core\View::e($prog) ?>
                                <div style="font-size: 0.8rem; color: #64748b;">Class: <?= \App\Core\View::e($class) ?></div>
                            </td>
                            <td>
                                <div><?= \App\Core\View::e($s['email'] ?? 'N/A') ?></div>
                                <div style="font-size: 0.8rem; color: #64748b;"><?= \App\Core\View::e($s['phone'] ?? '') ?></div>
                            </td>
                            <td>
                                <?php if ($isActive): ?>
                                    <span class="badge badge-success">ACTIVE</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">INACTIVE</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= \App\Core\View::url('/admin/users') ?>?search=<?= urlencode($s['email'] ?? '') ?>" class="btn btn-sm btn-secondary">Profile</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: #64748b;">
                            No students found in the department roster matching the search query.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= isset($paginator) ? $paginator->render() : '' ?>
</div>
