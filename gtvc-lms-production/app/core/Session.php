<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Hardened Session Manager with Secure Cookies and Flash Support
 */
class Session
{
    private static bool $started = false;

    /**
     * Start secure HTTP session
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443';
        
        session_set_cookie_params([
            'lifetime' => 28800, // 8 hours
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_start();
        self::$started = true;

        // Session expiration / inactivity check (2 hours)
        $maxInactive = 7200;
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $maxInactive)) {
            self::destroy();
            session_start();
            $_SESSION['flash_error'] = 'Your session expired due to inactivity. Please log in again.';
        }
        $_SESSION['last_activity'] = time();

        // Initialize CSRF Token if not present
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, string $message): void
    {
        self::start();
        $_SESSION['flash_' . $key] = $message;
    }

    public static function getFlash(string $key): ?string
    {
        self::start();
        $flashKey = 'flash_' . $key;
        if (isset($_SESSION[$flashKey])) {
            $msg = $_SESSION[$flashKey];
            unset($_SESSION[$flashKey]);
            return $msg;
        }
        return null;
    }

    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function getCsrfToken(): string
    {
        self::start();
        return $_SESSION['csrf_token'] ?? '';
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            session_destroy();
            self::$started = false;
        }
    }
}
