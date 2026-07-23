<?php

declare(strict_types=1);

namespace App\Config;

/**
 * Gilgil TVC LMS Application Configuration
 * Compatible with XAMPP, InfinityFree, Hostinger, and Standard Apache/PHP 8.x
 */
class AppConfig
{
    /**
     * Load environment variables from .env file if present
     */
    public static function loadEnv(string $envPath): void
    {
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }

                if (str_contains($line, '=')) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value, " \t\n\r\0\x0B\"'");
                    if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                        putenv("{$key}={$value}");
                        $_ENV[$key] = $value;
                        $_SERVER[$key] = $value;
                    }
                }
            }
        }

        // Set timezone
        $timezone = self::env('APP_TIMEZONE', 'Africa/Nairobi');
        date_default_timezone_set($timezone);

        // Configure error reporting
        $debug = filter_var(self::env('APP_DEBUG', true), FILTER_VALIDATE_BOOLEAN);
        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }

    /**
     * Fetch environment variable with fallback
     */
    public static function env(string $key, mixed $default = null): mixed
    {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($val === false || $val === null || $val === '') {
            return $default;
        }
        return $val;
    }
}
