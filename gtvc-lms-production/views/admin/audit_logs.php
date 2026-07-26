<?php
$logs = \App\Models\AuditLog::getRecentLogs(100);
?>
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title" style="margin: 0;">Security Audit Logs & Trace Records</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Real-time system authentication and security action event trail.</p>
        </div>
        <button class="btn btn-sm btn-secondary" onclick="window.location.reload()">🔄 Refresh Logs</button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User / IP</th>
                    <th>Action Event</th>
                    <th>Event Details</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: #64748b;">
                            No security audit log events recorded yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                            $userName = (!empty($log['first_name']) || !empty($log['last_name'])) 
                                ? trim($log['first_name'] . ' ' . $log['last_name'])
                                : ($log['email'] ?? 'System / Anonymous');
                            $ip = $log['ip_address'] ?? '127.0.0.1';
                            $action = $log['action'] ?? 'EVENT';
                            
                            $isSuccess = str_contains(strtolower($action), 'success') || str_contains(strtolower($action), 'login');
                            $isFailed = str_contains(strtolower($action), 'fail') || str_contains(strtolower($action), 'error');
                            
                            $statusBadge = $isFailed 
                                ? '<span class="badge badge-danger">FAILED</span>'
                                : ($isSuccess ? '<span class="badge badge-success">SUCCESS</span>' : '<span class="badge badge-info">INFO</span>');
                            
                            $details = $log['details_json'] ?? '';
                        ?>
                        <tr>
                            <td><code><?= \App\Core\View::e($log['created_at']) ?></code></td>
                            <td>
                                <strong><?= \App\Core\View::e($userName) ?></strong>
                                <?php if (!empty($log['email'])): ?>
                                    <div style="font-size: 0.8rem; color: #64748b;"><?= \App\Core\View::e($log['email']) ?></div>
                                <?php endif; ?>
                                <span class="badge badge-secondary" style="font-size: 0.75rem;"><?= \App\Core\View::e($ip) ?></span>
                            </td>
                            <td><code><?= \App\Core\View::e($action) ?></code></td>
                            <td style="max-width: 300px; font-size: 0.85rem; overflow-wrap: break-word;">
                                <?= \App\Core\View::e($details) ?>
                            </td>
                            <td><?= $statusBadge ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
