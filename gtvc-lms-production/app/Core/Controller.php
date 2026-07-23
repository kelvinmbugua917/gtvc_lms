<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base MVC Controller
 */
abstract class Controller
{
    /**
     * Send JSON Response helper
     */
    protected function json(mixed $data = null, string $message = 'Success', int $statusCode = 200, array $meta = []): void
    {
        Response::json($data, $message, $statusCode, $meta);
    }

    /**
     * Send JSON Error helper
     */
    protected function error(string $message = 'An error occurred', int $statusCode = 400, mixed $errors = null): void
    {
        Response::error($message, $statusCode, $errors);
    }
}
