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

<!-- Section 1: Submitted Assignments to Grade -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 class="card-title">Submitted Student Assignments & Evaluation</h3>
            <p class="text-muted" style="margin: 0; font-size: 0.875rem;">Review student submissions, award marks, and provide constructive feedback</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student Name & Reg No</th>
                    <th>Assignment & Course Unit</th>
                    <th>Submission Date</th>
                    <th>Attachment / Notes</th>
                    <th>Status & Score</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($submissions)): ?>
                    <?php foreach ($submissions as $sub): ?>
                        <tr>
                            <td>
                                <strong><?= \App\Core\View::e($sub['student_name']) ?></strong><br>
                                <small class="text-muted"><?= \App\Core\View::e($sub['registration_number'] ?? $sub['student_email']) ?></small>
                            </td>
                            <td>
                                <strong><?= \App\Core\View::e($sub['assignment_title']) ?></strong><br>
                                <small class="text-muted"><?= \App\Core\View::e(($sub['unit_code'] ?? '') . ': ' . ($sub['unit_title'] ?? '')) ?></small>
                            </td>
                            <td>
                                <?= !empty($sub['submitted_at']) ? date('Y-m-d H:i', strtotime($sub['submitted_at'])) : '--' ?>
                                <?php if (!empty($sub['is_late'])): ?>
                                    <br><span style="color: #dc2626; font-size: 0.75rem; font-weight: 600;">(LATE SUBMISSION)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($sub['original_filename'])): ?>
                                    <a href="<?= \App\Core\View::url('/api/v1/assignments/submissions/' . $sub['submission_id'] . '/download') ?>" style="color: #2563eb; text-decoration: underline; font-weight: 500;">
                                        📎 <?= \App\Core\View::e($sub['original_filename']) ?>
                                    </a>
                                <?php elseif (!empty($sub['file_path'])): ?>
                                    <span class="text-muted">📎 PDF Solution Attached</span>
                                <?php else: ?>
                                    <span class="text-muted">Text response submitted</span>
                                <?php endif; ?>
                                <?php if (!empty($sub['submission_text'])): ?>
                                    <div style="font-size: 0.8rem; color: #4b5563; margin-top: 0.2rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        "<?= \App\Core\View::e($sub['submission_text']) ?>"
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($sub['marks_awarded'] === null): ?>
                                    <span class="badge" style="background-color: #2563eb; color: #ffffff; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-weight: 600;">⏳ WAITING FOR REVIEW</span>
                                <?php else: ?>
                                    <span class="badge badge-success">✓ GRADED</span><br>
                                    <strong style="color: #059669; font-size: 1.05rem;"><?= (float)$sub['marks_awarded'] ?> / <?= (int)$sub['max_marks'] ?></strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="openGradeModal(<?= (int)$sub['submission_id'] ?>, '<?= \App\Core\View::e(addslashes($sub['student_name'])) ?>', '<?= \App\Core\View::e(addslashes($sub['assignment_title'])) ?>', <?= (int)$sub['max_marks'] ?>, <?= $sub['marks_awarded'] !== null ? (float)$sub['marks_awarded'] : 'null' ?>, '<?= \App\Core\View::e(addslashes($sub['feedback'] ?? '')) ?>')">
                                    <?= $sub['marks_awarded'] !== null ? 'Edit Grade' : 'Grade Submission' ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6b7280; padding: 2rem;">
                            No submitted assignments pending review.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Section 2: Overall Academic Gradebook & CBET Matrix -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title">Gradebook & TVET CBET Assessment Matrix</h3>
        <button class="btn btn-sm btn-primary" onclick="alert('Grades published successfully for semester!')">Publish Semester Grades</button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student Name & Reg No</th>
                    <th>CAT 1 (20%)</th>
                    <th>Assignment (20%)</th>
                    <th>Exam (60%)</th>
                    <th>Total Score</th>
                    <th>CBET Outcome</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Brian Otieno</strong><br>
                        <small class="text-muted">GTVC/ICT/2024/0089</small>
                    </td>
                    <td>18 / 20</td>
                    <td>16 / 20</td>
                    <td>48 / 60</td>
                    <td><strong>82%</strong></td>
                    <td><span class="badge badge-success">COMPETENT</span></td>
                    <td><button class="btn btn-sm btn-secondary">Edit Marks</button></td>
                </tr>
                <tr>
                    <td>
                        <strong>Grace Wanjiru</strong><br>
                        <small class="text-muted">GTVC/ICT/2024/0102</small>
                    </td>
                    <td>16 / 20</td>
                    <td>15 / 20</td>
                    <td>42 / 60</td>
                    <td><strong>73%</strong></td>
                    <td><span class="badge badge-success">COMPETENT</span></td>
                    <td><button class="btn btn-sm btn-secondary">Edit Marks</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Grading Submission -->
<div class="modal-backdrop" id="gradeModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title" id="gradeModalTitle">Grade Student Submission</h3>
            <button type="button" class="btn btn-sm btn-secondary" onclick="closeModal('gradeModal')">✕</button>
        </div>

        <form action="<?= \App\Core\View::url('/api/v1/assignments/grade') ?>" method="POST" style="padding: 1.25rem;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">
            <input type="hidden" name="submission_id" id="modal_submission_id" value="">
            <input type="hidden" name="redirect" value="/lecturer/gradebook">

            <div style="background-color: #f3f4f6; padding: 0.75rem 1rem; border-radius: 0.375rem; margin-bottom: 1rem; font-size: 0.875rem;">
                <div><strong>Student:</strong> <span id="modal_student_name">--</span></div>
                <div><strong>Assignment:</strong> <span id="modal_assignment_name">--</span></div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">Marks Awarded (Max: <span id="modal_max_marks">100</span>)</label>
                <input type="number" step="0.5" min="0" max="100" name="marks_awarded" id="modal_marks_awarded" class="form-control" placeholder="Enter score (e.g. 85)" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">Lecturer Feedback / Recommendations</label>
                <textarea name="feedback" id="modal_feedback" class="form-control" rows="3" placeholder="Provide constructive comments for the student..." style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;"></textarea>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('gradeModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background-color: #059669; color: #ffffff; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; cursor: pointer;">Save Grade & Feedback</button>
            </div>
        </form>
    </div>
</div>

<script>
function openGradeModal(submissionId, studentName, assignmentTitle, maxMarks, currentMarks, feedback) {
    document.getElementById('modal_submission_id').value = submissionId;
    document.getElementById('modal_student_name').innerText = studentName;
    document.getElementById('modal_assignment_name').innerText = assignmentTitle;
    document.getElementById('modal_max_marks').innerText = maxMarks;
    document.getElementById('modal_marks_awarded').setAttribute('max', maxMarks);
    document.getElementById('modal_marks_awarded').value = currentMarks !== null ? currentMarks : '';
    document.getElementById('modal_feedback').value = feedback || '';

    const modal = document.getElementById('gradeModal');
    modal.style.display = 'flex';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'none';
}
</script>
