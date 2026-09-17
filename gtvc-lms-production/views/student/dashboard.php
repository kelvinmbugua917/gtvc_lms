<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
    <a href="<?= \App\Core\View::url('/student/courses') ?>" class="stat-card" title="View Enrolled Units & Course Modules" id="stat-enrolled-units">
        <div>
            <div class="stat-label">Enrolled Units</div>
            <div class="stat-value"><?= (int)($enrolledUnits ?? 5) ?></div>
            <span class="stat-hint" style="font-size: 0.75rem; color: #0d9488; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.35rem;">View Units &rarr;</span>
        </div>
        <div class="stat-icon" style="background: #ccfbf1; color: #0f766e;">📚</div>
    </a>

    <a href="<?= \App\Core\View::url('/student/assignments') ?>" class="stat-card" title="View Pending Assignments & Assessments" id="stat-pending-assignments">
        <div>
            <div class="stat-label">Pending Assignments</div>
            <div class="stat-value"><?= (int)($pendingAssignments ?? 2) ?></div>
            <span class="stat-hint" style="font-size: 0.75rem; color: #d97706; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.35rem;">View Tasks &rarr;</span>
        </div>
        <div class="stat-icon" style="background: #fef3c7; color: #b45309;">📝</div>
    </a>

    <a href="<?= \App\Core\View::url('/student/attendance') ?>" class="stat-card" title="View Attendance Log & Workshop Sessions" id="stat-attendance-rate">
        <div>
            <div class="stat-label">Attendance Rate</div>
            <div class="stat-value"><?= \App\Core\View::e($attendanceRate ?? '92%') ?></div>
            <span class="stat-hint" style="font-size: 0.75rem; color: #059669; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.35rem;">View Log &rarr;</span>
        </div>
        <div class="stat-icon" style="background: #d1fae5; color: #047857;">⏱️</div>
    </a>

    <a href="<?= \App\Core\View::url('/student/fees') ?>" class="stat-card" title="View Fee Clearance & Exam Eligibility" id="stat-exam-clearance">
        <div>
            <div class="stat-label">Exam Clearance</div>
            <div class="stat-value" style="font-size: 1.125rem; color: #047857; text-transform: uppercase;"><?= \App\Core\View::e($clearanceStatus ?? 'CLEARED') ?></div>
            <span class="stat-hint" style="font-size: 0.75rem; color: #0284c7; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.35rem;">View Clearance &rarr;</span>
        </div>
        <div class="stat-icon" style="background: #e0f2fe; color: #0369a1;">🛡️</div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3" style="margin-top: 1.5rem;">
    <!-- Active Course Offerings -->
    <div class="card lg:grid-cols-2" style="grid-column: span 2;">
        <div class="card-header">
            <h3 class="card-title">Current Semester Course Offerings</h3>
            <a href="<?= \App\Core\View::url('/student/courses') ?>" class="btn btn-sm btn-secondary">View All</a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Unit Code & Name</th>
                        <th>Trainer</th>
                        <th>Progress</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $c): ?>
                            <tr>
                                <td>
                                    <strong><?= \App\Core\View::e(($c['unit_code'] ?? '') . ': ' . ($c['unit_title'] ?? 'Unit')) ?></strong><br>
                                    <small class="text-muted"><?= \App\Core\View::e($c['program_name'] ?? 'Diploma Program') ?></small>
                                </td>
                                <td><?= \App\Core\View::e($c['lecturer_name'] ?? 'Eng. John Koech') ?></td>
                                <td>
                                    <div style="width: 100px; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                        <div style="width: <?= (int)($c['progress_pct'] ?? 75) ?>%; height: 100%; background: #0d9488;"></div>
                                    </div>
                                    <small><?= (int)($c['progress_pct'] ?? 75) ?>%</small>
                                </td>
                                <td><span class="badge badge-success">ACTIVE</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td>
                                <strong>ICT 201: System Analysis & Design</strong><br>
                                <small class="text-muted">Diploma in ICT - Term 2</small>
                            </td>
                            <td>Eng. John Koech</td>
                            <td>
                                <div style="width: 100px; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                    <div style="width: 75%; height: 100%; background: #0d9488;"></div>
                                </div>
                                <small>75%</small>
                            </td>
                            <td><span class="badge badge-success">ACTIVE</span></td>
                        </tr>
                        <tr>
                            <td>
                                <strong>EE 104: Electrical Workshop Practice</strong><br>
                                <small class="text-muted">Craft Certificate in Electrical Eng</small>
                            </td>
                            <td>Tr. Mary Mwangi</td>
                            <td>
                                <div style="width: 100px; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                    <div style="width: 88%; height: 100%; background: #0d9488;"></div>
                                </div>
                                <small>88%</small>
                            </td>
                            <td><span class="badge badge-success">ACTIVE</span></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Sidebar Notices -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Bulletins</h3>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php if (!empty($bulletins)): ?>
                <?php foreach ($bulletins as $b): ?>
                    <div style="padding-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0;">
                        <span class="badge badge-warning" style="margin-bottom: 0.375rem;"><?= \App\Core\View::e(strtoupper($b['type'] ?? 'NOTICE')) ?></span>
                        <h4 style="font-size: 0.875rem; font-weight: 700;"><?= \App\Core\View::e($b['title']) ?></h4>
                        <p style="font-size: 0.775rem; color: #64748b; margin-top: 0.25rem;"><?= \App\Core\View::e($b['content']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0;">
                    <span class="badge badge-warning" style="margin-bottom: 0.375rem;">ACADEMIC NOTICE</span>
                    <h4 style="font-size: 0.875rem; font-weight: 700;">Term 2 Mid-Semester CAT Timetable</h4>
                    <p style="font-size: 0.775rem; color: #64748b; margin-top: 0.25rem;">CAT exams will commence on Monday, July 27th. Ensure exam cards are printed.</p>
                </div>

                <div style="padding-bottom: 0.75rem;">
                    <span class="badge badge-info" style="margin-bottom: 0.375rem;">FINANCE DEPT</span>
                    <h4 style="font-size: 0.875rem; font-weight: 700;">Fee Payment Clearance Deadline</h4>
                    <p style="font-size: 0.775rem; color: #64748b; margin-top: 0.25rem;">All students must have a zero balance or approved fee payment plan by Friday.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
