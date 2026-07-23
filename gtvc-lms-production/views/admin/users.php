<div class="card">
    <div class="card-header">
        <h3 class="card-title">System User Accounts & Role Management</h3>
        <button class="btn btn-sm btn-primary" onclick="openModal('createUserModal')">+ Create User Account</button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Assigned Roles</th>
                    <th>Account Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Super Administrator</strong></td>
                    <td>admin@gilgiltvc.ac.ke</td>
                    <td><span class="badge badge-info">SUPER_ADMIN</span></td>
                    <td><span class="badge badge-success">ACTIVE</span></td>
                    <td><button class="btn btn-sm btn-secondary" onclick="openModal('editUserModal')">Edit Account & Roles</button></td>
                </tr>
                <tr>
                    <td><strong>Eng. John Koech</strong></td>
                    <td>jkoech@gilgiltvc.ac.ke</td>
                    <td><span class="badge badge-info">LECTURER</span></td>
                    <td><span class="badge badge-success">ACTIVE</span></td>
                    <td><button class="btn btn-sm btn-secondary" onclick="openModal('editUserModal')">Edit Account & Roles</button></td>
                </tr>
                <tr>
                    <td><strong>Brian Otieno</strong></td>
                    <td>botieno@gilgiltvc.ac.ke</td>
                    <td><span class="badge badge-info">STUDENT</span></td>
                    <td><span class="badge badge-success">ACTIVE</span></td>
                    <td><button class="btn btn-sm btn-secondary" onclick="openModal('editUserModal')">Edit Account & Roles</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal-backdrop" id="createUserModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 500px;">
        <div class="card-header">
            <h3 class="card-title">Create New System User</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeModal('createUserModal')">✕</button>
        </div>

        <form action="<?= \App\Core\View::url('/api/v1/admin/users') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" placeholder="e.g. Mary" required>
            </div>

            <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" placeholder="e.g. Wanjiku" required>
            </div>

            <div class="form-group">
                <label class="form-label">Institutional Email</label>
                <input type="email" name="email" class="form-control" placeholder="mwanjiku@gilgiltvc.ac.ke" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Initial account password" required>
            </div>

            <div class="form-group">
                <label class="form-label">Assign System Role</label>
                <select name="role" class="form-control" required>
                    <option value="student">Student</option>
                    <option value="lecturer">Lecturer / Trainer</option>
                    <option value="hod">Head of Department (HOD)</option>
                    <option value="accountant">Bursar / Accountant</option>
                    <option value="admin">System Administrator</option>
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create User Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User & Roles Modal -->
<div class="modal-backdrop" id="editUserModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 500px;">
        <div class="card-header">
            <h3 class="card-title">Modify User & Role Permissions</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeModal('editUserModal')">✕</button>
        </div>

        <form action="<?= \App\Core\View::url('/api/v1/admin/users/1') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">Account Status</label>
                <select name="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Assigned Roles</label>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                        <input type="checkbox" name="roles[]" value="admin" checked> System Administrator
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                        <input type="checkbox" name="roles[]" value="lecturer"> Lecturer / Trainer
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                        <input type="checkbox" name="roles[]" value="hod"> Head of Department
                    </label>
                </div>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
