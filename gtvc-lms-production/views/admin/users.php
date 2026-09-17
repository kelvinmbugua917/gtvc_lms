<?php
use App\Core\Paginator;

\App\Models\User::syncAllUserProfiles();
$db = \App\Core\Model::getDb();
$selectedRole = $_GET['role'] ?? 'all';
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = max(5, min(100, (int)($_GET['per_page'] ?? 15)));

$whereClauses = ["1=1"];
$params = [];

if ($selectedRole !== 'all' && !empty($selectedRole)) {
    $whereClauses[] = "r.name = :role";
    $params['role'] = $selectedRole;
}

if (!empty($search)) {
    $whereClauses[] = "(u.email LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search OR u.registration_number LIKE :search OR u.phone LIKE :search OR u.national_id LIKE :search)";
    $params['search'] = '%' . $search . '%';
}

$whereSql = implode(' AND ', $whereClauses);

$countSql = "
    SELECT COUNT(DISTINCT u.id)
    FROM `users` u
    LEFT JOIN `user_roles` ur ON u.id = ur.user_id
    LEFT JOIN `roles` r ON ur.role_id = r.id
    WHERE {$whereSql}
";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$totalUsersCount = (int)$countStmt->fetchColumn();

$paginator = new Paginator($totalUsersCount, $perPage, $page);

$sql = "
    SELECT DISTINCT u.id, u.email, u.first_name, u.last_name, u.phone, u.national_id, u.registration_number, u.is_active, u.created_at
    FROM `users` u
    LEFT JOIN `user_roles` ur ON u.id = ur.user_id
    LEFT JOIN `roles` r ON ur.role_id = r.id
    WHERE {$whereSql}
    ORDER BY u.id ASC
    LIMIT :limit OFFSET :offset
";
$stmt = $db->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue(':' . $k, $v);
}
$stmt->bindValue(':limit', $paginator->getLimit(), \PDO::PARAM_INT);
$stmt->bindValue(':offset', $paginator->getOffset(), \PDO::PARAM_INT);
$stmt->execute();
$usersList = $stmt->fetchAll(\PDO::FETCH_ASSOC);

foreach ($usersList as &$u) {
    $u['roles'] = \App\Models\User::getUserRoles((int)$u['id']);
}
unset($u);
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title" style="margin: 0;">System User Accounts & Role Management</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Manage users, assign security roles, update phone/IDs, and filter accounts.</p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <form method="GET" action="" style="display: flex; align-items: center; gap: 0.5rem; margin: 0; flex-wrap: wrap;">
                <input type="text" name="search" value="<?= \App\Core\View::e($search) ?>" placeholder="Search name, email, reg no..." class="form-control" style="width: 200px; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                <select name="role" onchange="this.form.submit()" class="form-control" style="width: auto; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                    <option value="all" <?= $selectedRole === 'all' ? 'selected' : '' ?>>All Roles</option>
                    <option value="student" <?= $selectedRole === 'student' ? 'selected' : '' ?>>Students</option>
                    <option value="lecturer" <?= $selectedRole === 'lecturer' ? 'selected' : '' ?>>Lecturers</option>
                    <option value="trainer" <?= $selectedRole === 'trainer' ? 'selected' : '' ?>>Trainers</option>
                    <option value="hod" <?= $selectedRole === 'hod' ? 'selected' : '' ?>>Heads of Dept</option>
                    <option value="accountant" <?= $selectedRole === 'accountant' ? 'selected' : '' ?>>Accountants</option>
                    <option value="bursar" <?= $selectedRole === 'bursar' ? 'selected' : '' ?>>Bursar</option>
                    <option value="registrar" <?= $selectedRole === 'registrar' ? 'selected' : '' ?>>Registrar</option>
                    <option value="it_admin" <?= $selectedRole === 'it_admin' ? 'selected' : '' ?>>IT Admin</option>
                    <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Admins</option>
                    <option value="super_admin" <?= $selectedRole === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
                <select name="per_page" onchange="this.form.submit()" class="form-control" style="width: auto; padding: 0.375rem 0.5rem; font-size: 0.875rem;">
                    <option value="10" <?= $perPage === 10 ? 'selected' : '' ?>>10 / page</option>
                    <option value="15" <?= $perPage === 15 ? 'selected' : '' ?>>15 / page</option>
                    <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25 / page</option>
                    <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50 / page</option>
                </select>
                <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
                <?php if (!empty($search) || $selectedRole !== 'all'): ?>
                    <a href="?" class="btn btn-sm btn-secondary" title="Clear filters">✕</a>
                <?php endif; ?>
            </form>
            <button class="btn btn-sm btn-primary" onclick="openModal('createUserModal')">+ Create User Account</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User ID / Reg No</th>
                    <th>Full Name</th>
                    <th>Email & Contact</th>
                    <th>National ID</th>
                    <th>Assigned Roles</th>
                    <th>Account Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usersList)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: #64748b;">
                            No user accounts found matching the selected role filter.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usersList as $u): ?>
                        <?php 
                            $roleNames = array_column($u['roles'], 'name');
                            $roleBadges = implode(' ', array_map(function($r) {
                                $rUpper = strtoupper(str_replace('_', ' ', $r));
                                $color = match($r) {
                                    'super_admin', 'admin' => 'badge-danger',
                                    'it_admin', 'registrar' => 'badge-primary',
                                    'lecturer', 'trainer', 'hod' => 'badge-info',
                                    'accountant', 'bursar' => 'badge-warning',
                                    default => 'badge-success',
                                };
                                return "<span class=\"badge {$color}\">{$rUpper}</span>";
                            }, $roleNames));
                            if (empty($roleBadges)) {
                                $roleBadges = '<span class="badge badge-secondary">USER</span>';
                            }
                            $statusBadge = $u['is_active'] ? '<span class="badge badge-success">ACTIVE</span>' : '<span class="badge badge-danger">SUSPENDED</span>';
                            $rolesJson = htmlspecialchars(json_encode($roleNames), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr>
                            <td>
                                <strong>#<?= (int)$u['id'] ?></strong>
                                <?php if (!empty($u['registration_number'])): ?>
                                    <div style="font-size: 0.8rem; color: #475569; font-family: monospace;"><?= \App\Core\View::e($u['registration_number']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= \App\Core\View::e($u['first_name'] . ' ' . $u['last_name']) ?></strong></td>
                            <td>
                                <div><?= \App\Core\View::e($u['email']) ?></div>
                                <?php if (!empty($u['phone'])): ?>
                                    <div style="font-size: 0.8rem; color: #64748b;"><?= \App\Core\View::e($u['phone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= !empty($u['national_id']) ? \App\Core\View::e($u['national_id']) : '<span style="color:#94a3b8;">N/A</span>' ?></td>
                            <td><?= $roleBadges ?></td>
                            <td><?= $statusBadge ?></td>
                            <td>
                                <button class="btn btn-sm btn-secondary" onclick='openEditUserModal(<?= (int)$u["id"] ?>, <?= json_encode($u["first_name"]) ?>, <?= json_encode($u["last_name"]) ?>, <?= json_encode($u["email"]) ?>, <?= json_encode($u["phone"] ?? "") ?>, <?= json_encode($u["national_id"] ?? "") ?>, <?= json_encode($u["registration_number"] ?? "") ?>, <?= $u["is_active"] ? 1 : 0 ?>, <?= $rolesJson ?>)'>
                                    Edit Account & Roles
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= $paginator->render() ?>
</div>

<!-- Create User Modal -->
<div class="modal-backdrop" id="createUserModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 550px; background: white; margin: auto; max-height: 90vh; overflow-y: auto;">
        <div class="card-header">
            <h3 class="card-title">Create New System User</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeModal('createUserModal')">✕</button>
        </div>

        <form action="<?= \App\Core\View::url('/api/v1/admin/users') ?>" method="POST" style="padding: 1rem;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div class="form-group">
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" class="form-control" placeholder="e.g. Mary" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="last_name" class="form-control" placeholder="e.g. Wanjiku" required>
                </div>
            </div>

            <div class="form-group" style="margin-top: 0.75rem;">
                <label class="form-label">Institutional Email *</label>
                <input type="email" name="email" class="form-control" placeholder="mwanjiku@gilgiltvc.ac.ke" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.75rem;">
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="+254712345678">
                </div>
                <div class="form-group">
                    <label class="form-label">National ID / Birth Cert</label>
                    <input type="text" name="national_id" class="form-control" placeholder="33445566">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.75rem;">
                <div class="form-group">
                    <label class="form-label">Reg / Staff ID</label>
                    <input type="text" name="registration_number" class="form-control" placeholder="GTVC/DIT/2025/001 or STF001">
                </div>
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input type="password" id="newUserPassword" name="password" class="form-control" placeholder="Initial password" required style="width: 100%; padding-right: 2.5rem;">
                        <button type="button" onclick="toggleAdminPassVisibility('newUserPassword', this)" aria-label="Toggle password visibility" title="Show password" style="position: absolute; right: 0.75rem; background: none; border: none; cursor: pointer; color: #64748b; padding: 0.25rem; display: flex; align-items: center; justify-content: center; outline: none;">
                            <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                <line x1="2" x2="22" y1="2" y2="22"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-top: 0.75rem;">
                <label class="form-label">Assign Primary System Role</label>
                <select name="role" class="form-control" required>
                    <option value="student">Student / Trainee</option>
                    <option value="lecturer">Lecturer</option>
                    <option value="trainer">Trainer</option>
                    <option value="hod">Head of Department (HOD)</option>
                    <option value="accountant">Accountant</option>
                    <option value="bursar">Bursar</option>
                    <option value="registrar">Academic Registrar</option>
                    <option value="it_admin">IT Admin</option>
                    <option value="admin">System Administrator</option>
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1.25rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create User Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User & Roles Modal -->
<div class="modal-backdrop" id="editUserModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 550px; background: white; margin: auto; max-height: 90vh; overflow-y: auto;">
        <div class="card-header">
            <h3 class="card-title">Modify User Account & Roles (<span id="editUserNameDisplay">User</span>)</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeModal('editUserModal')">✕</button>
        </div>

        <form id="editUserForm" action="<?= \App\Core\View::url('/api/v1/admin/users/1') ?>" method="POST" style="padding: 1rem;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">
            <input type="hidden" id="editUserIdInput" name="id" value="1">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" id="editFirstName" name="first_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" id="editLastName" name="last_name" class="form-control" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.75rem;">
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" id="editPhone" name="phone" class="form-control" placeholder="+254712345678">
                </div>
                <div class="form-group">
                    <label class="form-label">National ID / Birth Cert</label>
                    <input type="text" id="editNationalId" name="national_id" class="form-control" placeholder="33445566">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.75rem;">
                <div class="form-group">
                    <label class="form-label">Reg / Staff ID</label>
                    <input type="text" id="editRegistrationNumber" name="registration_number" class="form-control" placeholder="GTVC/DIT/2025/001">
                </div>
                <div class="form-group">
                    <label class="form-label">Account Status</label>
                    <select id="editUserStatus" name="status" class="form-control">
                        <option value="active">Active</option>
                        <option value="suspended">Suspended / Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top: 0.75rem;">
                <label class="form-label">Assigned Security Roles</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; background: #f8fafc;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                        <input type="checkbox" name="roles[]" value="student" class="edit-role-cb"> Student / Trainee
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                        <input type="checkbox" name="roles[]" value="lecturer" class="edit-role-cb"> Lecturer
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                        <input type="checkbox" name="roles[]" value="trainer" class="edit-role-cb"> Technical Trainer
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                        <input type="checkbox" name="roles[]" value="hod" class="edit-role-cb"> Head of Department (HOD)
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                        <input type="checkbox" name="roles[]" value="accountant" class="edit-role-cb"> Accountant
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                        <input type="checkbox" name="roles[]" value="bursar" class="edit-role-cb"> Bursar
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                        <input type="checkbox" name="roles[]" value="registrar" class="edit-role-cb"> Academic Registrar
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                        <input type="checkbox" name="roles[]" value="it_admin" class="edit-role-cb"> IT Admin
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                        <input type="checkbox" name="roles[]" value="admin" class="edit-role-cb"> System Admin
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                        <input type="checkbox" name="roles[]" value="super_admin" class="edit-role-cb"> Super Admin
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-top: 0.75rem;">
                <label class="form-label">Reset Password (Optional)</label>
                <div style="position: relative; display: flex; align-items: center;">
                    <input type="password" id="editUserPassword" name="password" class="form-control" placeholder="Leave blank to keep current password" style="width: 100%; padding-right: 2.5rem;">
                    <button type="button" onclick="toggleAdminPassVisibility('editUserPassword', this)" aria-label="Toggle password visibility" title="Show password" style="position: absolute; right: 0.75rem; background: none; border: none; cursor: pointer; color: #64748b; padding: 0.25rem; display: flex; align-items: center; justify-content: center; outline: none;">
                        <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                            <line x1="2" x2="22" y1="2" y2="22"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1.25rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAdminPassVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const isPassword = input.getAttribute('type') === 'password';
    input.setAttribute('type', isPassword ? 'text' : 'password');
    const eyeIcon = btn.querySelector('.eye-icon');
    const eyeOffIcon = btn.querySelector('.eye-off-icon');
    if (eyeIcon && eyeOffIcon) {
        eyeIcon.style.display = isPassword ? 'none' : 'inline-block';
        eyeOffIcon.style.display = isPassword ? 'inline-block' : 'none';
    }
    const labelText = isPassword ? 'Hide password' : 'Show password';
    btn.setAttribute('title', labelText);
    btn.setAttribute('aria-label', labelText);
    btn.style.color = isPassword ? '#0d9488' : '#64748b';
}
function openEditUserModal(userId, firstName, lastName, email, phone, nationalId, regNumber, isActive, roles) {
    document.getElementById('editUserNameDisplay').innerText = firstName + ' ' + lastName;
    document.getElementById('editUserIdInput').value = userId;
    document.getElementById('editFirstName').value = firstName;
    document.getElementById('editLastName').value = lastName;
    document.getElementById('editPhone').value = phone;
    document.getElementById('editNationalId').value = nationalId;
    document.getElementById('editRegistrationNumber').value = regNumber;
    
    document.getElementById('editUserForm').action = "<?= \App\Core\View::url('/api/v1/admin/users/') ?>" + userId;
    document.getElementById('editUserStatus').value = isActive ? "active" : "suspended";
    
    var cbs = document.querySelectorAll('.edit-role-cb');
    cbs.forEach(function(cb) {
        cb.checked = roles.includes(cb.value);
    });
    
    openModal('editUserModal');
}
</script>
