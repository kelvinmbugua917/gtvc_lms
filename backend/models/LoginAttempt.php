<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use Throwable;

/**
 * Login Security & Brute-Force Rate Limiting Protection
 * Enforces IP and Email account throttling independently of client session cookies.
 */
class LoginAttempt
{
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 900; // 15 minutes temporary lockout

    private static function getStorageFile(): string
    {
        return sys_get_temp_dir() . '/gtvc_login_rate_limits.json';
    }

    private static function loadAttempts(): array
    {
        $file = self::getStorageFile();
        if (file_exists($file)) {
            $content = @file_get_contents($file);
            if ($content !== false) {
                $data = @json_decode($content, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        }
        return [];
    }

    private static function saveAttempts(array $data): void
    {
        $file = self::getStorageFile();
        @file_put_contents($file, json_encode($data), LOCK_EX);
    }

    private static function getKey(string $email, ?string $ipAddress = null): string
    {
        $ip = $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        return md5(strtolower(trim($email)) . '_' . $ip);
    }

    /**
     * Check if client IP or target email is currently rate-limited
     */
    public static function isRateLimited(string $email, ?string $ipAddress = null): bool
    {
        $key = self::getKey($email, $ipAddress);
        $attempts = self::loadAttempts();

        if (isset($attempts[$key]['locked_until'])) {
            if ($attempts[$key]['locked_until'] > time()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get remaining lockout duration in seconds
     */
    public static function getLockoutRemaining(string $email, ?string $ipAddress = null): int
    {
        $key = self::getKey($email, $ipAddress);
        $attempts = self::loadAttempts();

        if (isset($attempts[$key]['locked_until'])) {
            $remaining = $attempts[$key]['locked_until'] - time();
            return max(0, $remaining);
        }

        return 0;
    }

    /**
     * Record a failed login attempt
     */
    public static function recordFailedAttempt(string $email, ?string $ipAddress = null): void
    {
        $key = self::getKey($email, $ipAddress);
        $attempts = self::loadAttempts();

        $item = $attempts[$key] ?? [
            'count' => 0,
            'first_failed_at' => time(),
            'locked_until' => 0,
        ];

        // Reset counter if previous lockout expired
        if ($item['locked_until'] > 0 && $item['locked_until'] <= time()) {
            $item['count'] = 0;
            $item['locked_until'] = 0;
        }

        $item['count']++;

        if ($item['count'] >= self::MAX_FAILED_ATTEMPTS) {
            $item['locked_until'] = time() + self::LOCKOUT_SECONDS;
        }

        $attempts[$key] = $item;
        self::saveAttempts($attempts);
    }

    /**
     * Reset failed login attempt counter upon successful login
     */
    public static function resetAttempts(string $email, ?string $ipAddress = null): void
    {
        $key = self::getKey($email, $ipAddress);
        $attempts = self::loadAttempts();

        if (isset($attempts[$key])) {
            unset($attempts[$key]);
            self::saveAttempts($attempts);
        }
    }
}

