<div class="card">
    <div class="card-header">
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
                <tr>
                    <td>
                        <strong>Use Case Diagram & SRS Document</strong><br>
                        <small class="text-muted">Max Points: 100 | Weight: 20%</small>
                    </td>
                    <td>ICT 201: System Analysis</td>
                    <td>2026-07-28 23:59</td>
                    <td><span class="badge badge-warning">PENDING SUBMISSION</span></td>
                    <td>-- / 100</td>
                    <td><button class="btn btn-sm btn-primary" onclick="openModal('submitModal')">Submit Solution</button></td>
                </tr>
                <tr>
                    <td>
                        <strong>Three-Phase Circuit Wiring Report</strong><br>
                        <small class="text-muted">Max Points: 100 | Weight: 30%</small>
                    </td>
                    <td>EE 104: Electrical Workshop</td>
                    <td>2026-07-15 17:00</td>
                    <td><span class="badge badge-success">SUBMITTED & GRADED</span></td>
                    <td><strong>85 / 100</strong> (DISTINCTION)</td>
                    <td><button class="btn btn-sm btn-secondary">View Feedback</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Assignment Submission -->
<div class="modal-backdrop" id="submitModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 500px;">
        <div class="card-header">
            <h3 class="card-title">Submit Solution Assignment</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeModal('submitModal')">✕</button>
        </div>

        <form action="<?= \App\Core\View::url('/api/v1/assignments/submissions') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">
            <input type="hidden" name="assignment_id" value="1">

            <div class="form-group">
                <label class="form-label">Submission Notes / Comments</label>
                <textarea name="comments" class="form-control" rows="3" placeholder="Add any technical comments for your lecturer..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Upload Solution File (PDF, DOCX, ZIP)</label>
                <input type="file" name="submission_file" class="form-control" required>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('submitModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit File</button>
            </div>
        </form>
    </div>
</div>
