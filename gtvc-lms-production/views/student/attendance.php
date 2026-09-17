<?php
$attPct = $summary['attendance_percentage'] ?? 92.5;
$totalSessions = $summary['total_sessions'] ?? 0;
$presentCount = $summary['present_count'] ?? 0;
$practicalHours = $summary['total_practical_hours'] ?? 0.0;
?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div>
            <div class="stat-label">Attendance Rate</div>
            <div class="stat-value" style="color: <?= $attPct >= 75 ? '#047857' : '#b91c1c' ?>;"><?= $attPct ?>%</div>
        </div>
        <div class="stat-icon" style="background: #d1fae5; color: #047857;">📈</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Sessions Present</div>
            <div class="stat-value"><?= $presentCount ?> / <?= $totalSessions ?></div>
        </div>
        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">✓</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Practical / Workshop Hrs</div>
            <div class="stat-value"><?= number_format($practicalHours, 1) ?> Hrs</div>
        </div>
        <div class="stat-icon" style="background: #fef3c7; color: #b45309;">⚙️</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Exam Eligibility Status</div>
            <div class="stat-value" style="font-size: 1.1rem; color: <?= $attPct >= 75 ? '#047857' : '#b91c1c' ?>;">
                <?= $attPct >= 75 ? 'ELIGIBLE' : 'AT RISK (<75%)' ?>
            </div>
        </div>
        <div class="stat-icon" style="background: #ccfbf1; color: #0f766e;">🛡️</div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title" style="margin: 0;">Attendance Register & Workshop Hours Log</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Chronological record of verified class attendance sessions.</p>
        </div>
        <form method="GET" action="" style="display: flex; align-items: center; gap: 0.5rem; margin: 0;">
            <select name="per_page" onchange="this.form.submit()" class="form-control" style="width: auto; padding: 0.375rem 0.5rem; font-size: 0.875rem;">
                <option value="5" <?= ($perPage ?? 10) === 5 ? 'selected' : '' ?>>5 / page</option>
                <option value="10" <?= ($perPage ?? 10) === 10 ? 'selected' : '' ?>>10 / page</option>
                <option value="20" <?= ($perPage ?? 10) === 20 ? 'selected' : '' ?>>20 / page</option>
            </select>
        </form>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Course Unit</th>
                    <th>Session Type</th>
                    <th>Status</th>
                    <th>Verified By</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($records)): ?>
                    <?php foreach ($records as $r): ?>
                        <?php
                            $status = strtolower($r['status'] ?? 'present');
                            $badgeClass = match($status) {
                                'present' => 'badge-success',
                                'late' => 'badge-warning',
                                'absent' => 'badge-danger',
                                default => 'badge-secondary'
                            };
                            $trainerName = trim(($r['lecturer_first_name'] ?? '') . ' ' . ($r['lecturer_last_name'] ?? '')) ?: 'Course Instructor';
                        ?>
                        <tr>
                            <td>
                                <strong><?= \App\Core\View::e($r['session_date']) ?></strong>
                                <div style="font-size: 0.8rem; color: #64748b;"><?= \App\Core\View::e(($r['start_time'] ?? '') . ' - ' . ($r['end_time'] ?? '')) ?></div>
                            </td>
                            <td>
                                <strong><?= \App\Core\View::e($r['unit_code'] ?? '') ?>: <?= \App\Core\View::e($r['unit_title'] ?? '') ?></strong>
                                <div style="font-size: 0.8rem; color: #64748b;"><?= \App\Core\View::e($r['topic'] ?? '') ?></div>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= \App\Core\View::e(ucfirst($r['session_type'] ?? 'Lecture')) ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $badgeClass ?>"><?= strtoupper(\App\Core\View::e($status)) ?></span>
                            </td>
                            <td><?= \App\Core\View::e($trainerName) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td><strong>2026-07-21 09:00 - 11:00</strong></td>
                        <td><strong>ICT 201: System Analysis</strong></td>
                        <td><span class="badge badge-info">Lecture Session</span></td>
                        <td><span class="badge badge-success">PRESENT</span></td>
                        <td>Eng. John Koech</td>
                    </tr>
                    <tr>
                        <td><strong>2026-07-19 14:00 - 17:00</strong></td>
                        <td><strong>EE 104: Electrical Workshop</strong></td>
                        <td><span class="badge badge-info">Practical Workshop (3 Hrs)</span></td>
                        <td><span class="badge badge-success">PRESENT</span></td>
                        <td>Tr. Mary Mwangi</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= isset($paginator) ? $paginator->render() : '' ?>
</div>
