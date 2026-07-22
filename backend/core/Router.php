<?php

declare(strict_types=1);

namespace App\Core;

/**
 * RESTful API Router
 */
class Router
{
    private array $routes = [];

    /**
     * Register a GET route
     */
    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Register a POST route
     */
    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Register a PUT route
     */
    public function put(string $path, callable|array $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $path, callable|array $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        $this->routes[] = [
            'method'  => $method,
            'path'    => $this->normalizePath($path),
            'handler' => $handler,
        ];
    }

    private function normalizePath(string $path): string
    {
        $path = trim($path, '/');
        return $path === '' ? '/' : '/' . $path;
    }

    /**
     * Dispatch incoming request to matching handler
     */
    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $uri = $this->normalizePath($request->getUri());

        // Handle CORS OPTIONS preflight
        if ($method === 'OPTIONS') {
            http_response_code(204);
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

            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
            header('Access-Control-Max-Age: 86400');
            exit();
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Simple pattern matching for paths like /api/v1/health or /api/v1/users/{id}
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_\-]+)', $route['path']);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $handler = $route['handler'];

                if (is_callable($handler)) {
                    call_user_func($handler, $request, $params);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$controllerClass, $methodName] = $handler;
                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();
                        if (method_exists($controller, $methodName)) {
                            call_user_func([$controller, $methodName], $request, $params);
                            return;
                        }
                    }
                }
            }
        }

        Response::error("Route '{$method} {$uri}' not found", 404);
    }
}
