<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;
use App\Models\AuditLog;

/**
 * Authentication and Role-Based Access Control (RBAC) Middleware
 */
class AuthMiddleware
{
    /**
     * Authenticate session and retrieve current user object
     */
    public static function authenticate(Request $request): array
    {
        Session::start();
        $userId = Session::get('_user_id');

        if (!$userId) {
            AuditLog::log(
                null,
                'auth.unauthorized_access',
                null,
                null,
                ['uri' => $request->getUri(), 'reason' => 'Unauthenticated session']
            );
            Response::error('Unauthenticated: Please log in to access this resource', 401);
        }

        $user = User::findById($userId);
        if (!$user || !$user['is_active']) {
            Session::destroy();
            AuditLog::log(
                $userId ? (int)$userId : null,
                'auth.unauthorized_access',
                null,
                null,
                ['uri' => $request->getUri(), 'reason' => 'Account inactive or non-existent']
            );
            Response::error('Account is deactivated or session is invalid', 401);
        }

        $roles = User::getUserRoles($user['id']);
        $permissions = User::getUserPermissions($user['id']);
        $departments = User::getUserDepartments($user['id']);
        $roleNames = array_column($roles, 'name');
        $profile = User::getProfileDetails($user['id'], $roleNames);

        return User::sanitizeUser($user, $roles, $permissions, $departments, $profile);
    }

    /**
     * Enforce granular permission check
     */
    public static function requirePermission(Request $request, string $permission): array
    {
        $currentUser = self::authenticate($request);

        if (!in_array($permission, $currentUser['permissions'], true)) {
            AuditLog::log(
                $currentUser['id'],
                'auth.unauthorized_access',
                null,
                null,
                ['permission_required' => $permission, 'uri' => $request->getUri()]
            );
            Response::error("Forbidden: Missing required permission '{$permission}'", 403);
        }

        return $currentUser;
    }

    /**
     * Enforce role check
     */
    public static function requireRole(Request $request, string|array $allowedRoles): array
    {
        $currentUser = self::authenticate($request);
        $allowedRoles = (array)$allowedRoles;
        $userRoleNames = array_column($currentUser['roles'], 'name');

        $hasRole = !empty(array_intersect($allowedRoles, $userRoleNames));

        if (!$hasRole) {
            AuditLog::log(
                $currentUser['id'],
                'auth.unauthorized_access',
                null,
                null,
                ['roles_required' => $allowedRoles, 'uri' => $request->getUri()]
            );
            Response::error("Forbidden: Access restricted to authorized roles", 403);
        }

        return $currentUser;
    }

    /**
     * Prevent IDOR / BOLA vulnerabilities
     * Allows access if user owns the resource OR possesses administrative permission
     */
    public static function requireSelfOrPermission(
        Request $request,
        int|string $targetUserId,
        string $overridePermission = 'user.manage'
    ): array {
        $currentUser = self::authenticate($request);

        $isSelf = (string)$currentUser['id'] === (string)$targetUserId;
        $hasPermission = in_array($overridePermission, $currentUser['permissions'], true);

        if (!$isSelf && !$hasPermission) {
            AuditLog::log(
                $currentUser['id'],
                'auth.idor_blocked',
                null,
                null,
                [
                    'target_user_id' => $targetUserId,
                    'current_user_id' => $currentUser['id'],
                    'uri' => $request->getUri(),
                ]
            );
            Response::error("Forbidden: Unauthorized cross-user data access attempt", 403);
        }

        return $currentUser;
    }

    /**
     * Enforce department-level isolation for departmental officers
     */
    public static function requireDepartmentAccess(Request $request, int|string $departmentId): array
    {
        $currentUser = self::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        // Super admins have global cross-department privileges
        if (in_array('super_admin', $roleNames, true)) {
            return $currentUser;
        }

        $userDeptIds = array_column($currentUser['departments'], 'id');
        if (!in_array((int)$departmentId, array_map('intval', $userDeptIds), true)) {
            AuditLog::log(
                $currentUser['id'],
                'auth.unauthorized_access',
                null,
                null,
                ['target_department_id' => $departmentId, 'uri' => $request->getUri()]
            );
            Response::error("Forbidden: Access restricted to assigned department", 403);
        }

        return $currentUser;
    }
}
