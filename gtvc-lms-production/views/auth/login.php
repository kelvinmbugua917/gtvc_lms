<div class="card" style="padding: 2rem; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);">
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <div style="width: 48px; height: 48px; background: #0d9488; color: #fff; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.5rem; margin-bottom: 0.75rem;">G</div>
        <h2 style="font-size: 1.25rem; font-weight: 800; color: #0f172a;">Gilgil TVC LMS Portal</h2>
        <p style="font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem;">Sign in with your institutional credentials</p>
    </div>

    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger" style="margin-bottom: 1rem;">
            <?= \App\Core\View::e($flashError) ?>
        </div>
    <?php endif; ?>

    <form action="/api/v1/auth/login" method="POST">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">

        <div class="form-group">
            <label class="form-label" for="email">Institutional Email / Username</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="e.g. student@gilgiltvc.ac.ke" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••••••" required>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; font-size: 0.8125rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: #475569;">
                <input type="checkbox" name="remember" style="accent-color: #0d9488;"> Remember session
            </label>
            <a href="#" style="color: #0d9488; text-decoration: none; font-weight: 600;">Forgot Password?</a>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.925rem;">
            Sign In to LMS
        </button>
    </form>

    <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; text-align: center; font-size: 0.75rem; color: #94a3b8;">
        &copy; <?= date('Y') ?> Gilgil Technical and Vocational College. All rights reserved.<br>
        TVET Authority Accredited Institution.
    </div>
</div>
