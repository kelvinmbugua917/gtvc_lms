<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\User;
use App\Models\SystemSetting;
use App\Models\AuditLog;
use App\Core\Model;
use PDO;

class AdminController extends Model
{
    private function isAdminAuthorized(array $currentUser): bool
    {
        $roles = array_column($currentUser['roles'], 'name');
        return !empty(array_intersect(['admin', 'super_admin', 'hod', 'registrar'], $roles));
    }

    /**
     * GET /api/v1/admin/users
     */
    public function getUsers(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        if (!$this->isAdminAuthorized($currentUser)) {
            Response::json(['error' => 'Forbidden: Admin access required'], 403);
            return;
        }

        $db = self::getDb();
        $search = $request->getParam('search');
        $role = $request->getParam('role');
        $status = $request->getParam('status');

        $sql = "
            SELECT DISTINCT u.id, u.email, u.first_name, u.last_name, u.status, u.created_at, u.last_login_at
            FROM `users` u
            LEFT JOIN `user_roles` ur ON u.id = ur.user_id
            LEFT JOIN `roles` r ON ur.role_id = r.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (u.email LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($role)) {
            $sql .= " AND r.name = :role";
            $params['role'] = $role;
        }

        if (!empty($status)) {
            $sql .= " AND u.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY u.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Populate roles & departments for each user
        foreach ($users as &$u) {
            $u['roles'] = User::getUserRoles((int)$u['id']);
            $u['departments'] = User::getUserDepartments((int)$u['id']);
        }

        Response::json(['data' => $users]);
    }

    /**
     * POST /api/v1/admin/users
     */
    public function createUser(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        if (!$this->isAdminAuthorized($currentUser)) {
            Response::json(['error' => 'Forbidden: Admin access required'], 403);
            return;
        }

        $body = $request->getBody();
        if (empty($body['email']) || empty($body['first_name']) || empty($body['last_name']) || empty($body['password'])) {
            Response::json(['error' => 'Missing required user fields: email, first_name, last_name, password'], 400);
            return;
        }

        $email = strtolower(trim($body['email']));
        if (User::findByEmail($email)) {
            Response::json(['error' => 'A user account with this email address already exists'], 409);
            return;
        }

        $db = self::getDb();
        $hash = User::hashPassword($body['password']);
        $status = $body['status'] ?? 'active';

        $stmt = $db->prepare("
            INSERT INTO `users` (`email`, `password_hash`, `first_name`, `last_name`, `status`, `created_at`)
            VALUES (:email, :hash, :first_name, :last_name, :status, NOW())
        ");
        $stmt->execute([
            'email' => $email,
            'hash' => $hash,
            'first_name' => trim($body['first_name']),
            'last_name' => trim($body['last_name']),
            'status' => $status,
        ]);

        $userId = (int)$db->lastInsertId();

        // Assign default role if provided
        $roleId = !empty($body['role_id']) ? (int)$body['role_id'] : 2; // Default 2 = student
        $stmtRole = $db->prepare("INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES (:user_id, :role_id)");
        $stmtRole->execute(['user_id' => $userId, 'role_id' => $roleId]);

        AuditLog::log((int)$currentUser['id'], 'ADMIN_USER_CREATED', null, null, [
            'created_user_id' => $userId,
            'email' => $email,
            'role_id' => $roleId
        ]);

        Response::json(['message' => 'User account created successfully', 'id' => $userId], 201);
    }

    /**
     * PUT /api/v1/admin/users/{id}
     */
    public function updateUser(Request $request, array $params): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        if (!$this->isAdminAuthorized($currentUser)) {
            Response::json(['error' => 'Forbidden: Admin access required'], 403);
            return;
        }

        $userId = (int)($params['id'] ?? 0);
        $user = User::findById($userId);
        if (!$user) {
            Response::json(['error' => 'User not found'], 404);
            return;
        }

        $body = $request->getBody();
        $db = self::getDb();

        $sql = "UPDATE `users` SET `first_name` = :first_name, `last_name` = :last_name, `status` = :status";
        $bind = [
            'id' => $userId,
            'first_name' => trim($body['first_name'] ?? $user['first_name']),
            'last_name' => trim($body['last_name'] ?? $user['last_name']),
            'status' => $body['status'] ?? $user['status']
        ];

        if (!empty($body['password'])) {
            $sql .= ", `password_hash` = :hash";
            $bind['hash'] = User::hashPassword($body['password']);
        }

        $sql .= " WHERE `id` = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute($bind);

        AuditLog::log((int)$currentUser['id'], 'ADMIN_USER_UPDATED', null, null, [
            'updated_user_id' => $userId,
            'status' => $body['status'] ?? $user['status']
        ]);

        Response::json(['message' => 'User account updated successfully', 'id' => $userId]);
    }

    /**
     * GET /api/v1/admin/roles
     */
    public function getRoles(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        if (!$this->isAdminAuthorized($currentUser)) {
            Response::json(['error' => 'Forbidden: Admin access required'], 403);
            return;
        }

        $db = self::getDb();
        $stmt = $db->query("SELECT * FROM `roles` ORDER BY id ASC");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtP = $db->query("SELECT * FROM `permissions` ORDER BY module, name ASC");
        $permissions = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        Response::json([
            'data' => [
                'roles' => $roles,
                'permissions' => $permissions
            ]
        ]);
    }

    /**
     * POST /api/v1/admin/roles/assign
     */
    public function assignRoles(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        if (!$this->isAdminAuthorized($currentUser)) {
            Response::json(['error' => 'Forbidden: Admin access required'], 403);
            return;
        }

        $body = $request->getBody();
        if (empty($body['user_id']) || !isset($body['role_ids']) || !is_array($body['role_ids'])) {
            Response::json(['error' => 'Missing user_id or role_ids array'], 400);
            return;
        }

        $userId = (int)$body['user_id'];
        $db = self::getDb();

        // Clear existing user roles
        $stmtDel = $db->prepare("DELETE FROM `user_roles` WHERE `user_id` = :user_id");
        $stmtDel->execute(['user_id' => $userId]);

        // Insert new roles
        $stmtIns = $db->prepare("INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES (:user_id, :role_id)");
        foreach ($body['role_ids'] as $roleId) {
            $stmtIns->execute(['user_id' => $userId, 'role_id' => (int)$roleId]);
        }

        AuditLog::log((int)$currentUser['id'], 'ADMIN_USER_ROLES_ASSIGNED', null, null, [
            'target_user_id' => $userId,
            'assigned_role_ids' => $body['role_ids']
        ]);

        Response::json(['message' => 'Roles assigned successfully', 'user_id' => $userId]);
    }

    /**
     * GET /api/v1/admin/settings
     */
    public function getSettings(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $settings = SystemSetting::getAll();
        Response::json(['data' => $settings]);
    }

    /**
     * PUT /api/v1/admin/settings
     */
    public function updateSettings(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        if (!$this->isAdminAuthorized($currentUser)) {
            Response::json(['error' => 'Forbidden: Admin access required'], 403);
            return;
        }

        $body = $request->getBody();
        if (empty($body['settings']) || !is_array($body['settings'])) {
            Response::json(['error' => 'Missing settings object/array'], 400);
            return;
        }

        foreach ($body['settings'] as $key => $val) {
            SystemSetting::setKey($key, (string)$val);
        }

        AuditLog::log((int)$currentUser['id'], 'ADMIN_SETTINGS_UPDATED', null, null, ['updated_keys' => array_keys($body['settings'])]);

        Response::json(['message' => 'System settings updated successfully']);
    }

    /**
     * GET /api/v1/admin/audit-logs
     */
    public function getAuditLogs(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        if (!$this->isAdminAuthorized($currentUser)) {
            Response::json(['error' => 'Forbidden: Admin access required'], 403);
            return;
        }

        $db = self::getDb();
        $search = $request->getParam('search');
        $action = $request->getParam('action');

        $sql = "
            SELECT al.*, u.email, u.first_name, u.last_name
            FROM `audit_logs` al
            LEFT JOIN `users` u ON al.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($action)) {
            $sql .= " AND al.action = :action";
            $params['action'] = $action;
        }

        if (!empty($search)) {
            $sql .= " AND (al.action LIKE :search OR u.email LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT 100";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::json(['data' => $logs]);
    }
}
