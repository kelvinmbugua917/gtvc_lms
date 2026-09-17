<?php
$isProduction = \App\Config\AppConfig::isProduction();
$showDemoAccounts = !$isProduction && filter_var(\App\Config\AppConfig::env('SHOW_DEMO_ACCOUNTS', true), FILTER_VALIDATE_BOOLEAN);
?>
<div class="card" style="padding: 2rem; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3); background: #ffffff;">
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <div style="width: 54px; height: 54px; background: #0d9488; color: #fff; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.75rem; margin-bottom: 0.75rem; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);">G</div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0;">Gilgil TVC LMS Portal</h2>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.35rem;">Sign in with your institutional credentials</p>
    </div>

    <!-- Error Alert Box -->
    <div id="loginAlert" class="alert alert-danger" style="margin-bottom: 1rem; <?= empty($flashError) ? 'display: none;' : '' ?>">
        <?= !empty($flashError) ? \App\Core\View::e($flashError) : '' ?>
    </div>

    <form id="loginForm" action="<?= \App\Core\View::url('/api/v1/auth/login') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">

        <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" for="email" style="font-weight: 600; font-size: 0.85rem; color: #334155; display: block; margin-bottom: 0.35rem;">Institutional Email / Username</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="e.g. student@gilgiltvc.ac.ke" required autofocus style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem;">
        </div>

        <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" for="password" style="font-weight: 600; font-size: 0.85rem; color: #334155; display: block; margin-bottom: 0.35rem;">Password</label>
            <div style="position: relative; display: flex; align-items: center;">
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••••••" required style="width: 100%; padding: 0.65rem 2.75rem 0.65rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem;">
                <button type="button" id="togglePasswordBtn" aria-label="Show password" title="Show password" style="position: absolute; right: 0.75rem; background: none; border: none; cursor: pointer; color: #64748b; padding: 0.25rem; display: flex; align-items: center; justify-content: center; outline: none;">
                    <!-- Eye Icon (Password hidden, click to show) -->
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <!-- Eye Off Icon (Password visible, click to hide) -->
                    <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                        <line x1="2" x2="22" y1="2" y2="22"/>
                    </svg>
                </button>
            </div>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; font-size: 0.8125rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: #475569;">
                <input type="checkbox" name="remember" style="accent-color: #0d9488;"> Remember session
            </label>
            <a href="#" onclick="alert('Please contact the ICT Helpdesk at support@gilgiltvc.ac.ke to reset your credentials.'); return false;" style="color: #0d9488; text-decoration: none; font-weight: 600;">Forgot Password?</a>
        </div>

        <button type="submit" id="submitBtn" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.95rem; font-weight: 700; border-radius: 8px; background: #0d9488; border: none; color: white; cursor: pointer;">
            Sign In to LMS
        </button>
    </form>

    <?php if ($showDemoAccounts): ?>
    <!-- Quick Demo Credential Selector (Non-production environments only) -->
    <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px dashed #cbd5e1;">
        <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; text-align: center;">
            Demo Accounts (Click to Autofill)
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
            <button type="button" class="btn-demo" data-email="admin@gilgiltvc.ac.ke" style="padding: 0.4rem; font-size: 0.75rem; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; text-align: left; color: #334155;">
                👑 <strong>Admin</strong>
            </button>
            <button type="button" class="btn-demo" data-email="kmbugua@student.gilgiltvc.ac.ke" style="padding: 0.4rem; font-size: 0.75rem; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; text-align: left; color: #334155;">
                🎓 <strong>Student</strong>
            </button>
            <button type="button" class="btn-demo" data-email="pkiprop@gilgiltvc.ac.ke" style="padding: 0.4rem; font-size: 0.75rem; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; text-align: left; color: #334155;">
                👨‍🏫 <strong>Lecturer</strong>
            </button>
            <button type="button" class="btn-demo" data-email="mwanjiru@gilgiltvc.ac.ke" style="padding: 0.4rem; font-size: 0.75rem; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; text-align: left; color: #334155;">
                💳 <strong>Accountant</strong>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <div style="margin-top: 1.25rem; text-align: center; font-size: 0.75rem; color: #94a3b8;">
        &copy; <?= date('Y') ?> Gilgil Technical and Vocational College.<br>
        TVET Authority Accredited Institution.
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const alertBox = document.getElementById('loginAlert');
    const submitBtn = document.getElementById('submitBtn');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('togglePasswordBtn');
    const eyeIcon = document.getElementById('eyeIcon');
    const eyeOffIcon = document.getElementById('eyeOffIcon');

    // Password visibility toggle with eye icon
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', () => {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            if (eyeIcon && eyeOffIcon) {
                eyeIcon.style.display = isPassword ? 'none' : 'inline-block';
                eyeOffIcon.style.display = isPassword ? 'inline-block' : 'none';
            }
            const labelText = isPassword ? 'Hide password' : 'Show password';
            togglePasswordBtn.setAttribute('title', labelText);
            togglePasswordBtn.setAttribute('aria-label', labelText);
            togglePasswordBtn.style.color = isPassword ? '#0d9488' : '#64748b';
        });
    }

    // Quick demo account filler (available only in non-production)
    document.querySelectorAll('.btn-demo').forEach(btn => {
        btn.addEventListener('click', () => {
            emailInput.value = btn.getAttribute('data-email');
            passwordInput.value = 'password';
            alertBox.style.display = 'none';
        });
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        alertBox.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.innerText = 'Authenticating...';

        try {
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                window.location.href = '<?= \App\Core\View::url('/dashboard') ?>';
            } else {
                alertBox.innerText = result.message || 'Invalid credentials or login failed.';
                alertBox.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerText = 'Sign In to LMS';
            }
        } catch (err) {
            // Fallback to standard form submit if fetch fails
            form.submit();
        }
    });
});
</script>
