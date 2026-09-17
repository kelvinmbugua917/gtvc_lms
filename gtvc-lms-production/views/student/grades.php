<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3 class="card-title">Evaluated Assignments & Practical Assessment Grades</h3>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Assignment Title</th>
                    <th>Course Unit</th>
                    <th>Status</th>
                    <th>Score / Max Points</th>
                    <th>Lecturer Feedback</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($assignments)): ?>
                    <?php foreach ($assignments as $a): ?>
                        <tr>
                            <td>
                                <strong><?= \App\Core\View::e($a['title']) ?></strong>
                            </td>
                            <td><?= \App\Core\View::e(($a['unit_code'] ?? '') . ': ' . ($a['unit_title'] ?? '')) ?></td>
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
                                <?php else: ?>
                                    <span class="text-muted">-- / <?= (int)$a['max_marks'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($a['feedback'])): ?>
                                    <span style="color: #374151; font-style: italic;">"<?= \App\Core\View::e($a['feedback']) ?>"</span>
                                <?php elseif ($a['marks_awarded'] !== null): ?>
                                    <span class="text-muted">No specific feedback provided</span>
                                <?php else: ?>
                                    <span class="text-muted">Pending evaluation</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #6b7280; padding: 1.5rem;">
                            No evaluated assignment scores recorded yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= isset($paginator) ? $paginator->render() : '' ?>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Academic Grade Ledger & TVET CBET Competency Outcome</h3>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Unit Code & Name</th>
                    <th>Coursework (40%)</th>
                    <th>Final Exam (60%)</th>
                    <th>Final Score</th>
                    <th>Letter Grade</th>
                    <th>TVET CBET Outcome</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>ICT 201: System Analysis & Design</strong></td>
                    <td>34 / 40</td>
                    <td>48 / 60</td>
                    <td><strong>82%</strong></td>
                    <td><span class="badge badge-success">A (DISTINCTION)</span></td>
                    <td><span class="badge badge-success">COMPETENT</span></td>
                </tr>
                <tr>
                    <td><strong>EE 104: Electrical Workshop Practice</strong></td>
                    <td>31 / 40</td>
                    <td>45 / 60</td>
                    <td><strong>76%</strong></td>
                    <td><span class="badge badge-success">B (CREDIT)</span></td>
                    <td><span class="badge badge-success">COMPETENT</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
