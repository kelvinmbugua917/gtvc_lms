<?php
use App\Core\Paginator;
use App\Core\Model;

$db = Model::getDb();
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = max(5, min(100, (int)($_GET['per_page'] ?? 20)));
$search = trim($_GET['search'] ?? '');
$selectedAction = trim($_GET['action'] ?? '');

$whereClauses = ["1=1"];
$params = [];
if (!empty($selectedAction)) {
    $whereClauses[] = "al.action = :action";
    $params['action'] = $selectedAction;
}
if (!empty($search)) {
    $whereClauses[] = "(al.action LIKE :search OR u.email LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search OR al.ip_address LIKE :search)";
    $params['search'] = '%' . $search . '%';
}
$whereSql = implode(' AND ', $whereClauses);

$countSql = "
    SELECT COUNT(*)
    FROM `audit_logs` al
    LEFT JOIN `users` u ON al.user_id = u.id
    WHERE {$whereSql}
";
$cStmt = $db->prepare($countSql);
$cStmt->execute($params);
$totalLogs = (int)$cStmt->fetchColumn();

$paginator = new Paginator($totalLogs, $perPage, $page);

$sql = "
    SELECT al.*, u.email, u.first_name, u.last_name
    FROM `audit_logs` al
    LEFT JOIN `users` u ON al.user_id = u.id
    WHERE {$whereSql}
    ORDER BY al.created_at DESC
    LIMIT :limit OFFSET :offset
";
$stmt = $db->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue(':' . $k, $v);
}
$stmt->bindValue(':limit', $paginator->getLimit(), \PDO::PARAM_INT);
$stmt->bindValue(':offset', $paginator->getOffset(), \PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
?>
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title" style="margin: 0;">Security Audit Logs & Trace Records</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Real-time system authentication and security action event trail.</p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <form method="GET" action="" style="display: flex; align-items: center; gap: 0.5rem; margin: 0; flex-wrap: wrap;">
                <input type="text" name="search" value="<?= \App\Core\View::e($search) ?>" placeholder="Search action, email, IP..." class="form-control" style="width: 200px; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                <select name="per_page" onchange="this.form.submit()" class="form-control" style="width: auto; padding: 0.375rem 0.5rem; font-size: 0.875rem;">
                    <option value="15" <?= $perPage === 15 ? 'selected' : '' ?>>15 / page</option>
                    <option value="20" <?= $perPage === 20 ? 'selected' : '' ?>>20 / page</option>
                    <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50 / page</option>
                    <option value="100" <?= $perPage === 100 ? 'selected' : '' ?>>100 / page</option>
                </select>
                <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
                <?php if (!empty($search) || !empty($selectedAction)): ?>
                    <a href="?" class="btn btn-sm btn-secondary" title="Clear filters">✕</a>
                <?php endif; ?>
            </form>
            <button class="btn btn-sm btn-secondary" onclick="window.location.reload()">🔄 Refresh Logs</button>
        </div>
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
    <?= $paginator->render() ?>
</div>
