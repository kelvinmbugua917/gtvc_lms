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
            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••••••" required style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem;">
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

    <!-- Quick Demo Credential Selector -->
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

    // Quick demo account filler
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
