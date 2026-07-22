<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * CSRF Protection Middleware
 * Implements Double-Submit Cookie & Header Validation for state-changing HTTP methods
 */
class CsrfMiddleware
{
    /**
     * Generate or retrieve active CSRF token for session
     */
    public static function getToken(): string
    {
        Session::start();
        $token = Session::get('_csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }
        return $token;
    }

    /**
     * Verify CSRF token on POST, PUT, DELETE, PATCH requests
     */
    public static function verify(Request $request): void
    {
        $method = $request->getMethod();
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        Session::start();
        $sessionToken = Session::get('_csrf_token');

        $headerToken = $request->getHeader('X-CSRF-Token');
        $body = $request->getBody();
        $bodyToken = is_array($body) ? ($body['_csrf_token'] ?? null) : null;

        $submittedToken = $headerToken ?: $bodyToken;

        if (!$sessionToken || !$submittedToken || !hash_equals($sessionToken, $submittedToken)) {
            Response::error('CSRF validation failed: Invalid or missing CSRF token', 403);
        }
    }
}
