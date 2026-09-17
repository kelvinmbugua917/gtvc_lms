<?php
$flashSuccess = \App\Core\Session::getFlash('success');
$flashError = \App\Core\Session::getFlash('error');
?>

<?php if ($flashSuccess): ?>
    <div style="background-color: #d1fae5; color: #065f46; border: 1px solid #10b981; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <svg style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span><?= \App\Core\View::e($flashSuccess) ?></span>
        </div>
    </div>
<?php endif; ?>

<?php if ($flashError): ?>
    <div style="background-color: #fee2e2; color: #991b1b; border: 1px solid #ef4444; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <svg style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span><?= \App\Core\View::e($flashError) ?></span>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title">Assignments & Practical Submissions</h3>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Assignment Title</th>
                    <th>Course Unit</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Score / Grade</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($assignments)): ?>
                    <?php foreach ($assignments as $a): ?>
                        <tr>
                            <td>
                                <strong><?= \App\Core\View::e($a['title']) ?></strong><br>
                                <small class="text-muted">Max Points: <?= (int)$a['max_marks'] ?></small>
                                <?php if (!empty($a['description'])): ?>
                                    <div style="font-size: 0.825rem; color: #6b7280; margin-top: 0.25rem;"><?= \App\Core\View::e($a['description']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= \App\Core\View::e(($a['unit_code'] ?? '') . ': ' . ($a['unit_title'] ?? 'General Unit')) ?></td>
                            <td>
                                <?= !empty($a['due_date']) ? date('Y-m-d H:i', strtotime($a['due_date'])) : 'No Deadline' ?>
                            </td>
                            <td>
                                <?php if (empty($a['submission_id'])): ?>
                                    <span class="badge badge-warning">⏳ PENDING SUBMISSION</span>
                                <?php elseif ($a['marks_awarded'] === null): ?>
                                    <span class="badge" style="background-color: #2563eb; color: #ffffff; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-weight: 600;">⏳ WAITING FOR REVIEW</span>
                                <?php else: ?>
                                    <span class="badge badge-success">✓ GRADED</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($a['marks_awarded'] !== null): ?>
                                    <strong style="color: #059669; font-size: 1.05rem;"><?= (float)$a['marks_awarded'] ?> / <?= (int)$a['max_marks'] ?></strong>
                                    <?php if (!empty($a['feedback'])): ?>
                                        <br><small style="color: #4b5563; font-style: italic;">"<?= \App\Core\View::e($a['feedback']) ?>"</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-- / <?= (int)$a['max_marks'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="openSubmitModal(<?= (int)$a['assignment_id'] ?>, '<?= \App\Core\View::e(addslashes($a['title'])) ?>')">
                                    <?= !empty($a['submission_id']) ? 'Re-submit Solution' : 'Submit Solution' ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td>
                            <strong>Use Case Diagram & SRS Document</strong><br>
                            <small class="text-muted">Max Points: 100</small>
                        </td>
                        <td>ICT 201: System Analysis</td>
                        <td>2026-07-28 23:59</td>
                        <td><span class="badge badge-warning">⏳ PENDING SUBMISSION</span></td>
                        <td>-- / 100</td>
                        <td><button class="btn btn-sm btn-primary" onclick="openSubmitModal(1, 'Use Case Diagram & SRS Document')">Submit Solution</button></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= isset($paginator) ? $paginator->render() : '' ?>
</div>

<!-- Modal for Assignment Submission -->
<div class="modal-backdrop" id="submitModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title" id="submitModalTitle">Submit Solution Assignment</h3>
            <button type="button" class="btn btn-sm btn-secondary" onclick="closeModal('submitModal')">✕</button>
        </div>

        <form id="assignmentSubmitForm" action="<?= \App\Core\View::url('/student/assignments/submit') ?>" method="POST" enctype="multipart/form-data" style="padding: 1.25rem;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">
            <input type="hidden" name="assignment_id" id="modal_assignment_id" value="1">
            <input type="hidden" name="redirect" value="/student/assignments">

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">Submission Notes / Comments</label>
                <textarea name="comments" id="modal_comments" class="form-control" rows="3" placeholder="Add technical comments or explanation for your lecturer..." style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;"></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">Upload Solution File (PDF, DOCX, ZIP, PNG, JPG)</label>
                <input type="file" name="submission_file" id="modal_file" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                <small style="color: #6b7280; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Maximum size: 25MB. PDF, Word documents, code archives, or diagrams.</small>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('submitModal')">Cancel</button>
                <button type="submit" id="submitBtn" class="btn btn-primary" style="background-color: #0d9488; color: #ffffff; border: none; padding: 0.5rem 1.25rem; border-radius: 0.375rem; cursor: pointer; font-weight: 600;">Submit Solution</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSubmitModal(assignmentId, title) {
    document.getElementById('modal_assignment_id').value = assignmentId || 1;
    document.getElementById('submitModalTitle').innerText = 'Submit Solution: ' + (title || 'Assignment');
    const modal = document.getElementById('submitModal');
    modal.style.display = 'flex';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'none';
}

document.getElementById('assignmentSubmitForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerText = 'Submitting...';
    }
});
</script>
