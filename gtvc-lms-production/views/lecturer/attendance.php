<div class="card">
    <div class="card-header">
        <h3 class="card-title">Create Attendance Register Session</h3>
        <button class="btn btn-sm btn-primary" onclick="openModal('sessionModal')">Start New Register Session</button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Course Unit</th>
                    <th>Session Type</th>
                    <th>Present Count</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>2026-07-21</td>
                    <td>ICT 201: System Analysis</td>
                    <td>Lecture Session (2 Hours)</td>
                    <td>39 / 42 Students</td>
                    <td><button class="btn btn-sm btn-secondary">View / Edit Register</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-backdrop" id="sessionModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 480px;">
        <div class="card-header">
            <h3 class="card-title">New Attendance Session</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeModal('sessionModal')">✕</button>
        </div>

        <form action="/api/v1/attendance/sessions" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">
            <div class="form-group">
                <label class="form-label">Course Offering Unit</label>
                <select name="course_offering_id" class="form-control">
                    <option value="1">ICT 201: System Analysis & Design</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Session Type</label>
                <select name="session_type" class="form-control">
                    <option value="lecture">Lecture Session</option>
                    <option value="practical">Practical / Workshop Session</option>
                </select>
            </div>
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('sessionModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Start Session</button>
            </div>
        </form>
    </div>
</div>
