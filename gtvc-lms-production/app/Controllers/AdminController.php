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
            SELECT DISTINCT u.id, u.email, u.first_name, u.last_name, u.is_active, u.created_at, u.last_login_at
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
            $sql .= " AND u.is_active = :is_active";
            $params['is_active'] = (in_array(strtolower((string)$status), ['active', '1', 'true'], true) || $status === 1) ? 1 : 0;
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
        $isActive = (in_array(strtolower((string)$status), ['active', '1', 'true'], true) || $status === 1) ? 1 : 0;

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

        User::ensureRolesExist();
        $db = self::getDb();

        $phone = trim((string)($_POST['phone'] ?? $body['phone'] ?? ''));
        $nationalId = trim((string)($_POST['national_id'] ?? $body['national_id'] ?? ''));
        $regNumber = trim((string)($_POST['registration_number'] ?? $body['registration_number'] ?? $_POST['staff_number'] ?? $body['staff_number'] ?? ''));

        $hash = User::hashPassword($password);

        $stmt = $db->prepare("
            INSERT INTO `users` (`email`, `password_hash`, `first_name`, `last_name`, `phone`, `national_id`, `registration_number`, `is_active`, `created_at`)
            VALUES (:email, :hash, :first_name, :last_name, :phone, :national_id, :reg_num, :is_active, NOW())
        ");
        $stmt->execute([
            'email' => $email,
            'hash' => $hash,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => !empty($phone) ? $phone : null,
            'national_id' => !empty($nationalId) ? $nationalId : null,
            'reg_num' => !empty($regNumber) ? $regNumber : null,
            'is_active' => $isActive,
        ]);

        $userId = (int)$db->lastInsertId();

        // Assign default role if provided
        $roleInput = $_POST['roles'] ?? $_POST['role'] ?? $body['roles'] ?? $body['role'] ?? $body['role_id'] ?? 'student';
        $roleList = is_array($roleInput) ? $roleInput : [$roleInput];
        
        $roleNameToId = User::ensureRolesExist();

        foreach ($roleList as $r) {
            $rLower = strtolower(trim((string)$r));
            $roleId = is_numeric($r) ? (int)$r : ($roleNameToId[$rLower] ?? null);
            if ($roleId) {
                $stmtRole = $db->prepare("INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`) VALUES (:user_id, :role_id)");
                $stmtRole->execute(['user_id' => $userId, 'role_id' => $roleId]);
            }
        }

        User::syncAllUserProfiles();

        AuditLog::log((int)$currentUser['id'], 'ADMIN_USER_CREATED', null, null, [
            'created_user_id' => $userId,
            'email' => $email,
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

        // Super Admin Protection: Only super_admin can modify super_admin account
        $targetUserRoles = array_column(User::getUserRoles($userId), 'name');
        $currentAdminRoles = array_column(User::getUserRoles($currentUser['id']), 'name');
        $isCurrentSuperAdmin = in_array('super_admin', $currentAdminRoles, true);

        if (in_array('super_admin', $targetUserRoles, true) && !$isCurrentSuperAdmin) {
            if (!$isJson) {
                \App\Core\Session::setFlash('error', 'Security Policy Violation: Only a Super Admin can modify a Super Admin account.');
                Response::redirect('/admin/users');
            } else {
                Response::json(['error' => 'Security Policy Violation: Only a Super Admin can modify a Super Admin account.'], 403);
            }
            return;
        }

        $db = self::getDb();
        $firstName = trim((string)($_POST['first_name'] ?? $body['first_name'] ?? $user['first_name']));
        $lastName = trim((string)($_POST['last_name'] ?? $body['last_name'] ?? $user['last_name']));
        $phone = isset($_POST['phone']) ? trim((string)$_POST['phone']) : ($body['phone'] ?? $user['phone']);
        $nationalId = isset($_POST['national_id']) ? trim((string)$_POST['national_id']) : ($body['national_id'] ?? $user['national_id']);
        $regNumber = isset($_POST['registration_number']) ? trim((string)$_POST['registration_number']) : ($body['registration_number'] ?? $user['registration_number']);

        $status = $_POST['status'] ?? $body['status'] ?? 'active';
        $isActive = (in_array(strtolower((string)$status), ['active', '1', 'true'], true) || $status === 1) ? 1 : 0;
        $password = $_POST['password'] ?? $body['password'] ?? null;

        $sql = "UPDATE `users` SET `first_name` = :first_name, `last_name` = :last_name, `phone` = :phone, `national_id` = :national_id, `registration_number` = :reg_num, `is_active` = :is_active";
        $bind = [
            'id' => $userId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => !empty($phone) ? $phone : null,
            'national_id' => !empty($nationalId) ? $nationalId : null,
            'reg_num' => !empty($regNumber) ? $regNumber : null,
            'is_active' => $isActive
        ];

        if (!empty($password)) {
            $sql .= ", `password_hash` = :hash";
            $bind['hash'] = User::hashPassword($password);
        }

        $sql .= " WHERE `id` = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute($bind);

        // Update assigned user roles if roles submitted
        $rolesSubmitted = $_POST['roles'] ?? $_POST['role'] ?? $body['roles'] ?? $body['role'] ?? null;
        if ($rolesSubmitted !== null) {
            $roleList = is_array($rolesSubmitted) ? $rolesSubmitted : [$rolesSubmitted];
            $roleNameToId = User::ensureRolesExist();

            // Check if non-super_admin is trying to grant super_admin
            if (in_array('super_admin', array_map('strtolower', $roleList), true) && !$isCurrentSuperAdmin) {
                if (!$isJson) {
                    \App\Core\Session::setFlash('error', 'Security Policy Violation: Only a Super Admin can grant Super Admin privileges.');
                    Response::redirect('/admin/users');
                } else {
                    Response::json(['error' => 'Security Policy Violation: Only a Super Admin can grant Super Admin privileges.'], 403);
                }
                return;
            }

            $stmtDel = $db->prepare("DELETE FROM `user_roles` WHERE `user_id` = :user_id");
            $stmtDel->execute(['user_id' => $userId]);

            foreach ($roleList as $r) {
                $rLower = strtolower(trim((string)$r));
                $roleId = is_numeric($r) ? (int)$r : ($roleNameToId[$rLower] ?? null);
                if ($roleId) {
                    $stmtRole = $db->prepare("INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`) VALUES (:user_id, :role_id)");
                    $stmtRole->execute(['user_id' => $userId, 'role_id' => $roleId]);
                }
            }
        }

        User::syncAllUserProfiles();

        AuditLog::log((int)$currentUser['id'], 'ADMIN_USER_UPDATED', null, null, [
            'updated_user_id' => $userId,
            'is_active' => $isActive
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
