<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quizzes & Computer-Based Testing (CBT)</h3>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Quiz Title</th>
                    <th>Course Unit</th>
                    <th>Duration</th>
                    <th>Questions</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($quizzes)): ?>
                    <?php foreach ($quizzes as $quiz): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($quiz['title']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($quiz['description'] ?? 'Auto-graded CBT Assessment') ?></small>
                            </td>
                            <td><?= htmlspecialchars($quiz['unit_code'] ?? 'ICT 201') ?>: <?= htmlspecialchars($quiz['unit_title'] ?? 'System Analysis') ?></td>
                            <td><?= (int)$quiz['time_limit_minutes'] ?> Mins</td>
                            <td><?= (int)($quiz['question_count'] ?? 0) ?> Questions</td>
                            <td>
                                <?php if (!empty($quiz['my_attempts'])): ?>
                                    <span class="badge badge-success">COMPLETED</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">AVAILABLE</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($quiz['my_attempts'])): ?>
                                    <strong><?= (float)$quiz['my_attempts'][0]['score_achieved'] ?> / <?= (float)$quiz['my_attempts'][0]['total_possible_marks'] ?> (<?= round((float)$quiz['my_attempts'][0]['percentage_score']) ?>%)</strong>
                                <?php else: ?>
                                    <span class="text-muted">Not Attempted</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-secondary" onclick="openReviewModal('<?= htmlspecialchars($quiz['title']) ?>', '<?= !empty($quiz['my_attempts']) ? (float)$quiz['my_attempts'][0]['score_achieved'] . ' / ' . (float)$quiz['my_attempts'][0]['total_possible_marks'] : '18 / 20' ?>', '<?= !empty($quiz['my_attempts']) ? round((float)$quiz['my_attempts'][0]['percentage_score']) . '%' : '90%' ?>')">Review Results</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td>
                            <strong>CAT 1: System Analysis Concepts</strong><br>
                            <small class="text-muted">Auto-graded CBT Assessment</small>
                        </td>
                        <td>ICT 201: System Analysis</td>
                        <td>45 Mins</td>
                        <td>20 MCQs</td>
                        <td><span class="badge badge-success">COMPLETED</span></td>
                        <td><strong>18 / 20 (90%)</strong></td>
                        <td><button class="btn btn-sm btn-secondary" onclick="openReviewModal('CAT 1: System Analysis Concepts', '18 / 20', '90%')">Review Results</button></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Review Quiz Results Modal -->
<div class="modal-backdrop" id="reviewModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); align-items: center; justify-content: center; z-index: 1000;">
    <div class="modal-dialog" style="background: #ffffff; border-radius: 12px; max-width: 650px; width: 90%; max-height: 85vh; overflow-y: auto; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
            <h4 style="margin: 0; font-size: 1.1rem; color: #0f172a;" id="reviewModalTitle">Quiz Attempt Results</h4>
            <button type="button" onclick="closeReviewModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 0.85rem; color: #166534; font-weight: 600;">STATUS: COMPLETED & PASSED</span>
                    <h3 style="margin: 4px 0 0 0; color: #15803d; font-size: 1.5rem;" id="reviewModalScore">18 / 20 Marks</h3>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 1.25rem; font-weight: bold; color: #15803d;" id="reviewModalPct">90%</span>
                    <small style="display: block; color: #166534;">Passing Mark: 50%</small>
                </div>
            </div>
        </div>

        <h5 style="font-size: 0.95rem; font-weight: bold; color: #334155; margin-bottom: 12px;">Questions & Response Breakdown</h5>
        
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9rem; color: #1e293b; margin-bottom: 6px;">
                    <span>Q1. What is the primary purpose of a Data Flow Diagram (DFD)?</span>
                    <span style="color: #16a34a; font-size: 0.8rem;">1 / 1 Mark</span>
                </div>
                <div style="font-size: 0.85rem; color: #475569; background: #ffffff; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <span style="color: #16a34a; font-weight: bold;">✓ Selected Answer:</span> To visually represent data movement through system processes and data stores.
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9rem; color: #1e293b; margin-bottom: 6px;">
                    <span>Q2. Which requirement gathering technique is best for observing real-world workflows?</span>
                    <span style="color: #16a34a; font-size: 0.8rem;">1 / 1 Mark</span>
                </div>
                <div style="font-size: 0.85rem; color: #475569; background: #ffffff; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <span style="color: #16a34a; font-weight: bold;">✓ Selected Answer:</span> Direct User Observation & Workflow Shadowing.
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9rem; color: #1e293b; margin-bottom: 6px;">
                    <span>Q3. What does the acronym SDLC stand for in software systems design?</span>
                    <span style="color: #16a34a; font-size: 0.8rem;">1 / 1 Mark</span>
                </div>
                <div style="font-size: 0.85rem; color: #475569; background: #ffffff; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <span style="color: #16a34a; font-weight: bold;">✓ Selected Answer:</span> Software Development Life Cycle.
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9rem; color: #1e293b; margin-bottom: 6px;">
                    <span>Q4. In database design, what defines a Foreign Key constraint?</span>
                    <span style="color: #dc2626; font-size: 0.8rem;">0 / 1 Mark</span>
                </div>
                <div style="font-size: 0.85rem; color: #991b1b; background: #fef2f2; padding: 8px; border-radius: 6px; border: 1px solid #fecaca; margin-bottom: 4px;">
                    <span style="font-weight: bold;">✗ Selected Answer:</span> An auto-incrementing integer identifier unique to the table.
                </div>
                <div style="font-size: 0.85rem; color: #166534; background: #f0fdf4; padding: 8px; border-radius: 6px; border: 1px solid #bbf7d0;">
                    <span style="font-weight: bold;">✓ Correct Answer:</span> A column referencing a Primary Key in another table to establish relational integrity.
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 12px;">
            <button type="button" class="btn btn-secondary" onclick="closeReviewModal()">Close Review</button>
        </div>
    </div>
</div>

<script>
function openReviewModal(title, score, pct) {
    if (title) document.getElementById('reviewModalTitle').innerText = title + ' - Review Results';
    if (score) document.getElementById('reviewModalScore').innerText = score + ' Marks';
    if (pct) document.getElementById('reviewModalPct').innerText = pct;
    document.getElementById('reviewModal').style.display = 'flex';
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
}
</script>

