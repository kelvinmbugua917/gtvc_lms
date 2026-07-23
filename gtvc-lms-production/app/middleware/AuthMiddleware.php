<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Authentication and Role-Based Access Control (RBAC) Middleware
 */
class AuthMiddleware
{
    /**
     * Authenticate request session
     */
    public static function authenticate(Request $request): array
    {
        Session::start();
        $user = Session::get('user');

        if (!$user) {
            if ($request->getHeader('accept') && str_contains($request->getHeader('accept'), 'application/json')) {
                Response::error('Authentication required. Please log in.', 401);
            } else {
                Session::flash('error', 'Please log in to access this page.');
                header('Location: /login');
                exit();
            }
        }

        return $user;
    }

    /**
     * Require specified roles
     */
    public static function requireRoles(array $allowedRoles, Request $request): array
    {
        $user = self::authenticate($request);
        $userRoles = $user['roles'] ?? [];

        if (is_string($userRoles)) {
            $userRoles = [$userRoles];
        }

        $hasRole = false;
        foreach ($userRoles as $role) {
            $roleName = is_array($role) ? ($role['name'] ?? '') : (string)$role;
            if (in_array($roleName, $allowedRoles, true) || $roleName === 'super_admin' || $roleName === 'admin') {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            if ($request->getHeader('accept') && str_contains($request->getHeader('accept'), 'application/json')) {
                Response::error('Access denied. Insufficient permissions.', 403);
            } else {
                Session::flash('error', 'Access denied. You do not have permission to view that page.');
                header('Location: /dashboard');
                exit();
            }
        }

        return $user;
    }
}
