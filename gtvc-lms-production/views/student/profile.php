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

    <div class="grid grid-cols-1 lg:grid-cols-2" style="gap: 1.5rem;">
        <div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" value="<?= \App\Core\View::e($fullName) ?>" readonly>
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">Admission / Staff Number</label>
                <input type="text" class="form-control" value="<?= \App\Core\View::e($regNo) ?>" readonly>
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">Institutional Email</label>
                <input type="email" class="form-control" value="<?= \App\Core\View::e($user['email'] ?? 'user@gilgiltvc.ac.ke') ?>" readonly>
            </div>
        </div>

        <div>
            <h4 style="font-size: 0.925rem; font-weight: 700; margin-bottom: 0.75rem;">Change Password</h4>
            <form action="<?= \App\Core\View::url('/api/v1/auth/change-password') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Current Password</label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input type="password" id="current_password" name="current_password" class="form-control" required style="width: 100%; padding-right: 2.5rem;">
                        <button type="button" onclick="togglePasswordVisibility('current_password', this)" aria-label="Toggle password visibility" title="Show password" style="position: absolute; right: 0.75rem; background: none; border: none; cursor: pointer; color: #64748b; padding: 0.25rem; display: flex; align-items: center; justify-content: center; outline: none;">
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

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">New Password</label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input type="password" id="new_password" name="new_password" class="form-control" required style="width: 100%; padding-right: 2.5rem;">
                        <button type="button" onclick="togglePasswordVisibility('new_password', this)" aria-label="Toggle password visibility" title="Show password" style="position: absolute; right: 0.75rem; background: none; border: none; cursor: pointer; color: #64748b; padding: 0.25rem; display: flex; align-items: center; justify-content: center; outline: none;">
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

                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, btn) {
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
</script>
