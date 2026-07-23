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
    public function createUser(Request $request, array $params = []): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        if (!$this->isAdminAuthorized($currentUser)) {
            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
            $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
            $isJson = str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');
            if (!$isJson) {
                \App\Core\Session::setFlash('error', 'Forbidden: Admin access required');
                Response::redirect('/admin/users');
            } else {
                Response::json(['error' => 'Forbidden: Admin access required'], 403);
            }
            return;
        }

        $body = $request->getBody();
        $email = strtolower(trim((string)($_POST['email'] ?? $body['email'] ?? '')));
        $firstName = trim((string)($_POST['first_name'] ?? $body['first_name'] ?? ''));
        $lastName = trim((string)($_POST['last_name'] ?? $body['last_name'] ?? ''));
        $password = (string)($_POST['password'] ?? $body['password'] ?? '');
        $status = $_POST['status'] ?? $body['status'] ?? 'active';

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        $isJson = str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');

        if (empty($email) || empty($firstName) || empty($lastName) || empty($password)) {
            if (!$isJson) {
                \App\Core\Session::setFlash('error', 'Missing required user fields');
                Response::redirect('/admin/users');
            } else {
                Response::json(['error' => 'Missing required user fields: email, first_name, last_name, password'], 400);
            }
            return;
        }

        if (User::findByEmail($email)) {
            if (!$isJson) {
                \App\Core\Session::setFlash('error', 'A user account with this email address already exists');
                Response::redirect('/admin/users');
            } else {
                Response::json(['error' => 'A user account with this email address already exists'], 409);
            }
            return;
        }

        $db = self::getDb();
        $hash = User::hashPassword($password);

        $stmt = $db->prepare("
            INSERT INTO `users` (`email`, `password_hash`, `first_name`, `last_name`, `status`, `created_at`)
            VALUES (:email, :hash, :first_name, :last_name, :status, NOW())
        ");
        $stmt->execute([
            'email' => $email,
            'hash' => $hash,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'status' => $status,
        ]);

        $userId = (int)$db->lastInsertId();

        // Assign default role if provided
        $roleInput = $_POST['role'] ?? $body['role'] ?? $body['role_id'] ?? 'student';
        $roleMap = ['admin' => 1, 'super_admin' => 1, 'student' => 2, 'lecturer' => 3, 'trainer' => 3, 'hod' => 4, 'accountant' => 5, 'bursar' => 5];
        $roleId = is_numeric($roleInput) ? (int)$roleInput : ($roleMap[$roleInput] ?? 2);

        $stmtRole = $db->prepare("INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES (:user_id, :role_id)");
        $stmtRole->execute(['user_id' => $userId, 'role_id' => $roleId]);

        AuditLog::log((int)$currentUser['id'], 'ADMIN_USER_CREATED', null, null, [
            'created_user_id' => $userId,
            'email' => $email,
            'role_id' => $roleId
        ]);

        if (!$isJson) {
            \App\Core\Session::setFlash('success', 'User account created successfully!');
            Response::redirect('/admin/users');
        } else {
            Response::json(['message' => 'User account created successfully', 'id' => $userId], 201);
        }
    }

    /**
     * PUT or POST /api/v1/admin/users/{id}
     */
    public function updateUser(Request $request, array $params = []): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        $isJson = str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');

        if (!$this->isAdminAuthorized($currentUser)) {
            if (!$isJson) {
                \App\Core\Session::setFlash('error', 'Forbidden: Admin access required');
                Response::redirect('/admin/users');
            } else {
                Response::json(['error' => 'Forbidden: Admin access required'], 403);
            }
            return;
        }

        $body = $request->getBody();
        $userId = (int)($params['id'] ?? $body['id'] ?? $_POST['id'] ?? 1);
        $user = User::findById($userId);
        if (!$user) {
            if (!$isJson) {
                \App\Core\Session::setFlash('error', 'User not found');
                Response::redirect('/admin/users');
            } else {
                Response::json(['error' => 'User not found'], 404);
            }
            return;
        }

        $db = self::getDb();
        $firstName = trim((string)($_POST['first_name'] ?? $body['first_name'] ?? $user['first_name']));
        $lastName = trim((string)($_POST['last_name'] ?? $body['last_name'] ?? $user['last_name']));
        $status = $_POST['status'] ?? $body['status'] ?? $user['status'];
        $password = $_POST['password'] ?? $body['password'] ?? null;

        $sql = "UPDATE `users` SET `first_name` = :first_name, `last_name` = :last_name, `status` = :status";
        $bind = [
            'id' => $userId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'status' => $status
        ];

        if (!empty($password)) {
            $sql .= ", `password_hash` = :hash";
            $bind['hash'] = User::hashPassword($password);
        }

        $sql .= " WHERE `id` = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute($bind);

        AuditLog::log((int)$currentUser['id'], 'ADMIN_USER_UPDATED', null, null, [
            'updated_user_id' => $userId,
            'status' => $status
        ]);

        if (!$isJson) {
            \App\Core\Session::setFlash('success', 'User account updated successfully!');
            Response::redirect('/admin/users');
        } else {
            Response::json(['message' => 'User account updated successfully', 'id' => $userId]);
        }
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
     * PUT or POST /api/v1/admin/settings
     */
    public function updateSettings(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        $isJson = str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');

        if (!$this->isAdminAuthorized($currentUser)) {
            if (!$isJson) {
                \App\Core\Session::setFlash('error', 'Forbidden: Admin access required');
                Response::redirect('/admin/settings');
            } else {
                Response::json(['error' => 'Forbidden: Admin access required'], 403);
            }
            return;
        }

        $body = $request->getBody();
        $settingsData = $body['settings'] ?? $_POST ?? [];
        unset($settingsData['csrf_token']);

        foreach ($settingsData as $key => $val) {
            if (is_string($key)) {
                SystemSetting::setKey($key, is_array($val) ? json_encode($val) : (string)$val);
            }
        }

        AuditLog::log((int)$currentUser['id'], 'ADMIN_SETTINGS_UPDATED', null, null, ['updated_keys' => array_keys($settingsData)]);

        if (!$isJson) {
            \App\Core\Session::setFlash('success', 'System settings saved successfully!');
            Response::redirect('/admin/settings');
        } else {
            Response::json(['message' => 'System settings updated successfully']);
        }
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
