<div class="card">
    <div class="card-header">
        <h3 class="card-title">Academic Hierarchy (Departments, Programs & Intakes)</h3>
        <button class="btn btn-sm btn-primary" onclick="openModal('addDepartmentModal')">+ Add Department / Program</button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Department Code</th>
                    <th>Department Name</th>
                    <th>Head of Department (HOD)</th>
                    <th>Programs Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>ICT</strong></td>
                    <td>Information Communication Technology</td>
                    <td>Dr. Felix Kiprono</td>
                    <td>3 Programs</td>
                    <td><button class="btn btn-sm btn-secondary" onclick="openModal('addDepartmentModal')">Edit Dept</button></td>
                </tr>
                <tr>
                    <td><strong>ELEC</strong></td>
                    <td>Electrical & Electronics Engineering</td>
                    <td>Eng. Samuel Njuguna</td>
                    <td>4 Programs</td>
                    <td><button class="btn btn-sm btn-secondary" onclick="openModal('addDepartmentModal')">Edit Dept</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Department Modal -->
<div class="modal-backdrop" id="addDepartmentModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 500px;">
        <div class="card-header">
            <h3 class="card-title">Add / Edit Academic Department</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeModal('addDepartmentModal')">✕</button>
        </div>

        <form action="<?= \App\Core\View::url('/api/v1/departments') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">Department Code</label>
                <input type="text" name="code" class="form-control" placeholder="e.g. MECH" required>
            </div>

            <div class="form-group">
                <label class="form-label">Department Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Mechanical Engineering" required>
            </div>

            <div class="form-group">
                <label class="form-label">Head of Department (HOD)</label>
                <select name="hod_id" class="form-control">
                    <option value="">Select HOD...</option>
                    <option value="2">Eng. John Koech</option>
                    <option value="3">Dr. Felix Kiprono</option>
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addDepartmentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Department</button>
            </div>
        </form>
    </div>
</div>
