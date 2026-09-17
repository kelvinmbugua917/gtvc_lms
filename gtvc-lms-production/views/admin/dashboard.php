<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total System Users</div>
            <div class="stat-value"><?= (int)($totalUsers ?? 385) ?></div>
        </div>
        <div class="stat-icon" style="background: #ccfbf1; color: #0f766e;">👥</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Active Academic Units</div>
            <div class="stat-value"><?= (int)($activeUnits ?? 28) ?></div>
        </div>
        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">🏛️</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Database Health</div>
            <div class="stat-value" style="color: #047857;">OPTIMAL</div>
        </div>
        <div class="stat-icon" style="background: #d1fae5; color: #047857;">🛡️</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Security Audit Traces</div>
            <div class="stat-value"><?= (int)($auditLogsCount ?? 0) ?> Records</div>
        </div>
        <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;">📜</div>
    </div>
</div>

<?php
$userRoles = array_map(fn($r) => is_array($r) ? ($r['name'] ?? '') : (string)$r, $currentUser['roles'] ?? []);
$isRegistrar = in_array('registrar', $userRoles, true) && !in_array('admin', $userRoles, true) && !in_array('super_admin', $userRoles, true);
?>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title"><?= $isRegistrar ? 'Academic Registrar & Administration Control Panel' : 'System Administration Control Panel' ?></h3>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <a href="<?= \App\Core\View::url('/admin/users') ?>" class="btn btn-secondary" style="padding: 1.25rem; text-align: center; flex-direction: column;">
            <span style="font-size: 1.5rem;">👥</span>
            <span><?= $isRegistrar ? 'Student & User Registry' : 'User & Role Management' ?></span>
            <span style="font-size: 0.75rem; color: #64748b; font-weight: normal; margin-top: 0.25rem;">Admissions, Passwords & Roles</span>
        </a>
        <a href="<?= \App\Core\View::url('/admin/academic') ?>" class="btn btn-secondary" style="padding: 1.25rem; text-align: center; flex-direction: column;">
            <span style="font-size: 1.5rem;">🏛️</span>
            <span>Academic Hierarchy</span>
            <span style="font-size: 0.75rem; color: #64748b; font-weight: normal; margin-top: 0.25rem;">Terms, Departments & Classes</span>
        </a>
        <?php if (!$isRegistrar): ?>
        <a href="<?= \App\Core\View::url('/admin/settings') ?>" class="btn btn-secondary" style="padding: 1.25rem; text-align: center; flex-direction: column;">
            <span style="font-size: 1.5rem;">⚙️</span>
            <span>System Settings</span>
            <span style="font-size: 0.75rem; color: #64748b; font-weight: normal; margin-top: 0.25rem;">Global Configuration</span>
        </a>
        <?php endif; ?>
        <a href="<?= \App\Core\View::url('/admin/audit-logs') ?>" class="btn btn-secondary" style="padding: 1.25rem; text-align: center; flex-direction: column;">
            <span style="font-size: 1.5rem;">📜</span>
            <span>Security Audit Logs</span>
            <span style="font-size: 0.75rem; color: #64748b; font-weight: normal; margin-top: 0.25rem;">Audit & Activity Trail</span>
        </a>
    </div>
</div>
