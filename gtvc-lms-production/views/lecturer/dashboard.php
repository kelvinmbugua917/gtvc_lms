<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
    <div class="stat-card">
        <div>
            <div class="stat-label">Assigned Units</div>
            <div class="stat-value"><?= (int)($assignedUnits ?? 3) ?></div>
        </div>
        <div class="stat-icon" style="background: #ccfbf1; color: #0f766e;">🎓</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Active Students</div>
            <div class="stat-value"><?= (int)($activeStudents ?? 124) ?></div>
        </div>
        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">👥</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Submissions to Grade</div>
            <div class="stat-value" style="color: #b45309;"><?= (int)($submissionsToGrade ?? 18) ?></div>
        </div>
        <div class="stat-icon" style="background: #fef3c7; color: #b45309;">📝</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Upcoming Sessions</div>
            <div class="stat-value"><?= (int)($upcomingSessions ?? 2) ?> Today</div>
        </div>
        <div class="stat-icon" style="background: #d1fae5; color: #047857;">⏱️</div>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">My Assigned Course Offerings</h3>
        <a href="<?= \App\Core\View::url('/lecturer/courses') ?>" class="btn btn-sm btn-primary">Manage Classes</a>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Unit Code & Name</th>
                    <th>Department & Cohort</th>
                    <th>Enrolled</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($courses)): ?>
                    <?php foreach ($courses as $c): ?>
                        <tr>
                            <td><strong><?= \App\Core\View::e(($c['unit_code'] ?? '') . ': ' . ($c['unit_title'] ?? '')) ?></strong></td>
                            <td><?= \App\Core\View::e(($c['department_name'] ?? 'ICT Dept') . ' | ' . ($c['term_name'] ?? 'Term 2')) ?></td>
                            <td><?= (int)($c['enrolled_count'] ?? 42) ?> Students</td>
                            <td>
                                <a href="<?= \App\Core\View::url('/lecturer/gradebook') ?>" class="btn btn-sm btn-secondary">Gradebook</a>
                                <a href="<?= \App\Core\View::url('/lecturer/attendance') ?>" class="btn btn-sm btn-primary">Mark Attendance</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td><strong>ICT 201: System Analysis & Design</strong></td>
                        <td>ICT Dept | Diploma Term 2</td>
                        <td>42 Students</td>
                        <td>
                            <a href="<?= \App\Core\View::url('/lecturer/gradebook') ?>" class="btn btn-sm btn-secondary">Gradebook</a>
                            <a href="<?= \App\Core\View::url('/lecturer/attendance') ?>" class="btn btn-sm btn-primary">Mark Attendance</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
