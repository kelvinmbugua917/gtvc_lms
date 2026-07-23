<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Double-Submit Cookie / Header CSRF Protection
 */
class CsrfMiddleware
{
    public static function verify(Request $request): void
    {
        $method = $request->getMethod();
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        Session::start();
        $storedToken = Session::getCsrfToken();

        $providedToken = $request->getHeader('x-csrf-token');
        if (!$providedToken) {
            $body = $request->getBody();
            $providedToken = $body['csrf_token'] ?? null;
        }

        if (!$providedToken || !hash_equals($storedToken, $providedToken)) {
            if ($request->getHeader('accept') && str_contains($request->getHeader('accept'), 'application/json')) {
                Response::error('Invalid or missing CSRF security token.', 403);
            } else {
                Session::flash('error', 'Security check failed. Please refresh and try again.');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/dashboard'));
                exit();
            }
        }
    }
}
