<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Centralized JSON Response & Error Handler
 */
class Response
{
    /**
     * Set secure headers and CORS credentials parameters
     */
    private static function setSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header('Content-Type: application/json; charset=utf-8');

        // Modern Security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self';");

        // Strict CORS Headers checking allowed origins from APP_ALLOWED_ORIGINS
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowedOriginsEnv = \App\Config\AppConfig::env('APP_ALLOWED_ORIGINS', 'http://localhost:3000,http://127.0.0.1:3000');
        $allowedOrigins = array_map('trim', explode(',', $allowedOriginsEnv));

        if (!empty($origin) && in_array($origin, $allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: {$origin}");
            header('Access-Control-Allow-Credentials: true');
            header('Vary: Origin');
        } elseif (!empty($allowedOrigins[0])) {
            header("Access-Control-Allow-Origin: {$allowedOrigins[0]}");
            header('Access-Control-Allow-Credentials: true');
        }
    }

    /**
     * Send a successful JSON response
     */
    public static function json(mixed $data = null, string $message = 'Success', int $statusCode = 200, array $meta = []): void
    {
        http_response_code($statusCode);
        self::setSecurityHeaders();

        $payload = [
            'success'   => $statusCode >= 200 && $statusCode < 300,
            'message'   => $message,
            'timestamp' => date('Y-m-d\TH:i:sP'),
            'data'      => $data,
        ];

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit();
    }

    /**
     * Send a standardized API error response
     */
    public static function error(string $message = 'An error occurred', int $statusCode = 400, mixed $errors = null): void
    {
        http_response_code($statusCode);
        self::setSecurityHeaders();

        $payload = [
            'success'   => false,
            'message'   => $message,
            'timestamp' => date('Y-m-d\TH:i:sP'),
            'code'      => $statusCode,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit();
    }

    /**
     * Handle uncaught exceptions gracefully as JSON
     */
    public static function handleException(\Throwable $exception): void
    {
        $statusCode = (int)$exception->getCode();
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 500;
        }

        self::error(
            $exception->getMessage(),
            $statusCode,
            [
                'file' => basename($exception->getFile()),
                'line' => $exception->getLine(),
            ]
        );
    }
}
