<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title" style="margin: 0;">Class Attendance Sessions & Registers</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Create daily attendance registers, track trainee participation, and verify session hours.</p>
        </div>
        <button class="btn btn-sm btn-primary" onclick="openModal('sessionModal')">+ Start New Register Session</button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Course Unit & Class</th>
                    <th>Session Type</th>
                    <th>Present Count</th>
                    <th>Topic / Notes</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($sessions)): ?>
                    <?php foreach ($sessions as $s): ?>
                        <tr>
                            <td>
                                <strong><?= \App\Core\View::e($s['session_date']) ?></strong>
                                <div style="font-size: 0.8rem; color: #64748b;"><?= \App\Core\View::e(($s['start_time'] ?? '') . ' - ' . ($s['end_time'] ?? '')) ?></div>
                            </td>
                            <td>
                                <strong><?= \App\Core\View::e($s['unit_code'] ?? '') ?>: <?= \App\Core\View::e($s['unit_title'] ?? '') ?></strong>
                                <div style="font-size: 0.8rem; color: #64748b;">Class: <?= \App\Core\View::e($s['class_name'] ?? 'General') ?></div>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= \App\Core\View::e(ucfirst($s['session_type'] ?? 'Lecture')) ?></span>
                            </td>
                            <td>
                                <strong><?= (int)($s['present_count'] ?? 0) ?></strong> / <?= (int)($s['total_records'] ?? 0) ?> Trainees
                            </td>
                            <td>
                                <?= \App\Core\View::e($s['topic'] ?? 'General Session') ?>
                            </td>
                            <td>
                                <span class="badge badge-success"><?= strtoupper(\App\Core\View::e($s['status'] ?? 'COMPLETED')) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #64748b; padding: 2rem;">
                            No attendance sessions recorded yet. Click "Start New Register Session" above to begin.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= isset($paginator) ? $paginator->render() : '' ?>
</div>

<div class="modal-backdrop" id="sessionModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 500px;">
        <div class="card-header">
            <h3 class="card-title">New Attendance Session</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeModal('sessionModal')">✕</button>
        </div>

        <form action="<?= \App\Core\View::url('/api/v1/attendance/sessions') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" style="display: block; margin-bottom: 0.25rem; font-weight: 500;">Course Offering Unit</label>
                <select name="course_offering_id" class="form-control" style="width: 100%;" required>
                    <?php if (!empty($courseOfferings)): ?>
                        <?php foreach ($courseOfferings as $co): ?>
                            <option value="<?= (int)$co['id'] ?>"><?= \App\Core\View::e($co['code'] . ' - ' . $co['title']) ?> (<?= \App\Core\View::e($co['class_name'] ?? 'General') ?>)</option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="1">ICT 201: System Analysis & Design</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" style="display: block; margin-bottom: 0.25rem; font-weight: 500;">Session Type</label>
                <select name="session_type" class="form-control" style="width: 100%;">
                    <option value="lecture">Lecture Session (Theory)</option>
                    <option value="practical">Practical / Workshop Session</option>
                    <option value="tutorial">Tutorial Session</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" style="display: block; margin-bottom: 0.25rem; font-weight: 500;">Topic Covered</label>
                <input type="text" name="topic_covered" class="form-control" placeholder="e.g. Unit 3: ER Diagrams and Normalization" style="width: 100%;">
            </div>
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1.25rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('sessionModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Start Session</button>
            </div>
        </form>
    </div>
</div>
