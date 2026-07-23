<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\LoginAttempt;
use App\Middleware\AuthMiddleware;

/**
 * Authentication & Security Controller
 */
class AuthController extends Controller
{
    /**
     * Authenticate user credentials and establish session
     * POST /api/v1/auth/login
     */
    public function login(Request $request): void
    {
        $body = $request->getBody();
        $email = trim((string)($body['email'] ?? ''));
        $password = (string)($body['password'] ?? '');

        if (empty($email) || empty($password)) {
            Response::error('Email address and password are required', 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Invalid email format', 400);
        }

        // Check for brute-force rate limit
        if (LoginAttempt::isRateLimited($email)) {
            $remaining = LoginAttempt::getLockoutRemaining($email);
            AuditLog::log(
                null,
                'auth.login.rate_limited',
                null,
                null,
                ['email' => $email, 'remaining_seconds' => $remaining]
            );
            Response::error(
                "Too many failed login attempts. Account temporarily locked for security. Try again in {$remaining} seconds.",
                429
            );
        }

        try {
            $user = User::findByEmail($email);
        } catch (\Throwable $e) {
            Response::error(
                "Database Connection / Query Failure: Unable to query database 'gilgil_lms'. Please verify XAMPP MySQL service is active and database/schema.sql & database/seeds.sql are imported. Error: " . $e->getMessage(),
                500
            );
        }

        // Secure password verification (protects against user enumeration)
        if (!$user || !User::verifyPassword($password, $user['password_hash'])) {
            LoginAttempt::recordFailedAttempt($email);
            AuditLog::log(
                $user ? (int)$user['id'] : null,
                'auth.login.failed',
                null,
                null,
                ['email' => $email]
            );
            Response::error('Invalid credentials', 401);
        }

        if (!(int)$user['is_active']) {
            AuditLog::log(
                (int)$user['id'],
                'auth.login.inactive_account',
                null,
                null,
                ['email' => $email]
            );
            Response::error('Account is deactivated. Please contact ICT support.', 403);
        }

        // Login success: reset brute-force counters & regenerate session ID
        LoginAttempt::resetAttempts($email);
        Session::start();
        Session::regenerate();
        Session::set('_user_id', (int)$user['id']);

        User::updateLastLogin($user['id']);

        $roles = User::getUserRoles($user['id']);
        $permissions = User::getUserPermissions($user['id']);
        $departments = User::getUserDepartments($user['id']);
        $roleNames = array_column($roles, 'name');
        $profile = User::getProfileDetails($user['id'], $roleNames);

        $sanitizedUser = User::sanitizeUser($user, $roles, $permissions, $departments, $profile);
        Session::set('user', $sanitizedUser);

        AuditLog::log(
            (int)$user['id'],
            'auth.login.success',
            null,
            null,
            ['email' => $email, 'roles' => $roleNames]
        );

        Response::json($sanitizedUser, 'Authentication successful');
    }

    /**
     * Terminate active user session
     * POST /api/v1/auth/logout or GET /logout
     */
    public function logout(Request $request): void
    {
        Session::start();
        $userId = Session::get('_user_id');

        if ($userId) {
            AuditLog::log((int)$userId, 'auth.logout', null, null, []);
        }

        Session::destroy();

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' || str_contains($accept, 'application/json') || str_contains($uri, '/api/')) {
            Response::json(null, 'Successfully logged out');
        } else {
            Response::redirect('/login');
        }
    }

    /**
     * Retrieve current authenticated user profile
     * GET /api/v1/auth/me
     */
    public function me(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);

        Response::json($currentUser, 'Authenticated profile retrieved');
    }

    /**
     * Get or initialize CSRF token for current session
     * GET /api/v1/auth/csrf
     */
    public function csrfToken(Request $request): void
    {
        $token = \App\Middleware\CsrfMiddleware::getToken();
        Response::json(['csrf_token' => $token], 'CSRF token issued');
    }

    /**
     * Change authenticated user password
     * POST /api/v1/auth/change-password
     */
    public function changePassword(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $body = $request->getBody();
        $currentPassword = (string)($_POST['current_password'] ?? $body['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? $body['new_password'] ?? '');

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        $isJson = str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');

        if (empty($currentPassword) || empty($newPassword)) {
            if (!$isJson) {
                Session::setFlash('error', 'Current password and new password are required');
                Response::redirect('/student/profile');
            } else {
                Response::error('Current password and new password are required', 400);
            }
            return;
        }

        if (strlen($newPassword) < 6) {
            if (!$isJson) {
                Session::setFlash('error', 'New password must be at least 6 characters long');
                Response::redirect('/student/profile');
            } else {
                Response::error('New password must be at least 6 characters long', 400);
            }
            return;
        }

        $fullUser = User::findById($currentUser['id']);
        if (!$fullUser || !User::verifyPassword($currentPassword, $fullUser['password_hash'])) {
            if (!$isJson) {
                Session::setFlash('error', 'Current password is incorrect');
                Response::redirect('/student/profile');
            } else {
                Response::error('Current password is incorrect', 400);
            }
            return;
        }

        $newHash = User::hashPassword($newPassword);
        User::updatePassword($currentUser['id'], $newHash);

        AuditLog::log((int)$currentUser['id'], 'auth.change_password', null, null, []);

        if (!$isJson) {
            Session::setFlash('success', 'Password updated successfully!');
            Response::redirect('/student/profile');
        } else {
            Response::json(null, 'Password updated successfully');
        }
    }
}
