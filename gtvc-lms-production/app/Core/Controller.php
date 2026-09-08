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
    protected function json(mixed $data = null, string|int $arg2 = 'Success', string|int $arg3 = 200, array $meta = []): void
    {
        Response::json($data, $arg2, $arg3, $meta);
    }

    /**
     * Send JSON Error helper
     */
    protected function error(string $message = 'An error occurred', int $statusCode = 400, mixed $errors = null): void
    {
        Response::error($message, $statusCode, $errors);
    }
}
