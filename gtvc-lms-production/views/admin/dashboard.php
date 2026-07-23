<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total System Users</div>
            <div class="stat-value">385</div>
        </div>
        <div class="stat-icon" style="background: #ccfbf1; color: #0f766e;">👥</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Active Academic Units</div>
            <div class="stat-value">28</div>
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
            <div class="stat-label">Security Traces</div>
            <div class="stat-value">0 Alerts</div>
        </div>
        <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;">📜</div>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">System Administration Control Panel</h3>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <a href="<?= \App\Core\View::url('/admin/users') ?>" class="btn btn-secondary" style="padding: 1.25rem; text-align: center; flex-direction: column;">
            <span style="font-size: 1.5rem;">👥</span>
            <span>User & Role Management</span>
        </a>
        <a href="<?= \App\Core\View::url('/admin/academic') ?>" class="btn btn-secondary" style="padding: 1.25rem; text-align: center; flex-direction: column;">
            <span style="font-size: 1.5rem;">🏛️</span>
            <span>Academic Hierarchy</span>
        </a>
        <a href="<?= \App\Core\View::url('/admin/settings') ?>" class="btn btn-secondary" style="padding: 1.25rem; text-align: center; flex-direction: column;">
            <span style="font-size: 1.5rem;">⚙️</span>
            <span>System Settings</span>
        </a>
        <a href="<?= \App\Core\View::url('/admin/audit-logs') ?>" class="btn btn-secondary" style="padding: 1.25rem; text-align: center; flex-direction: column;">
            <span style="font-size: 1.5rem;">📜</span>
            <span>Security Audit Logs</span>
        </a>
    </div>
</div>
