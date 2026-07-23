<?php
$user = $currentUser ?? \App\Core\Session::get('user') ?? [];
$fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
if (empty($fullName)) {
    $fullName = $user['username'] ?? 'GTVC User';
}
$regNo = $user['profile']['admission_number'] ?? $user['profile']['staff_number'] ?? $user['username'] ?? 'GTVC/ICT/2024/0089';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">User Account & Security Credentials</h3>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2">
        <div>
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" value="<?= \App\Core\View::e($fullName) ?>" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Admission / Staff Number</label>
                <input type="text" class="form-control" value="<?= \App\Core\View::e($regNo) ?>" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Institutional Email</label>
                <input type="email" class="form-control" value="<?= \App\Core\View::e($user['email'] ?? 'user@gilgiltvc.ac.ke') ?>" readonly>
            </div>
        </div>

        <div>
            <h4 style="font-size: 0.925rem; font-weight: 700; margin-bottom: 0.75rem;">Change Password</h4>
            <form action="<?= \App\Core\View::url('/api/v1/auth/change-password') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>
</div>
