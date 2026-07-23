<div class="card">
    <div class="card-header">
        <h3 class="card-title">Global System Settings & Thresholds</h3>
    </div>

    <form action="<?= \App\Core\View::url('/api/v1/admin/settings') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">

        <div class="grid grid-cols-1 lg:grid-cols-2">
            <div class="form-group">
                <label class="form-label">Institution Name</label>
                <input type="text" name="institution_name" class="form-control" value="Gilgil Technical and Vocational College">
            </div>

            <div class="form-group">
                <label class="form-label">Min Attendance Percentage Threshold (%)</label>
                <input type="number" name="min_attendance_threshold" class="form-control" value="75">
            </div>

            <div class="form-group">
                <label class="form-label">Exam Fee Clearance Requirement</label>
                <select name="exam_clearance_mode" class="form-control">
                    <option value="zero_balance">Strict Zero Balance</option>
                    <option value="fee_plan">Approved Fee Plan Allowed</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Save System Settings</button>
    </form>
</div>
