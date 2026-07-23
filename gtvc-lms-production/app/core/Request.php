<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP Request Abstraction & Input Sanitization
 */
class Request
{
    private string $method;
    private string $uri;
    private array $headers = [];

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = dirname($scriptName);

        if ($baseDir !== '/' && str_starts_with($uri, $baseDir)) {
            $uri = substr($uri, strlen($baseDir));
        }

        $pos = strpos($uri, '?');
        if ($pos !== false) {
            $uri = substr($uri, 0, $pos);
        }

        $this->uri = '/' . trim($uri, '/');
        
        foreach (getallheaders() as $name => $value) {
            $this->headers[strtolower($name)] = $value;
        }
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function getBody(): array
    {
        $body = [];

        if ($this->method === 'GET') {
            foreach ($_GET as $key => $value) {
                $body[$key] = is_string($value) ? trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8')) : $value;
            }
        } elseif ($this->method === 'POST') {
            if ($this->getHeader('content-type') && str_contains($this->getHeader('content-type'), 'application/json')) {
                $json = file_get_contents('php://input');
                $decoded = json_decode($json, true);
                $body = is_array($decoded) ? $decoded : [];
            } else {
                foreach ($_POST as $key => $value) {
                    $body[$key] = is_string($value) ? trim($value) : $value;
                }
            }
        } elseif (in_array($this->method, ['PUT', 'PATCH', 'DELETE'], true)) {
            $json = file_get_contents('php://input');
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $body = $decoded;
            } else {
                parse_str($json, $parsed);
                $body = is_array($parsed) ? $parsed : [];
            }
        }

        return $body;
    }

    public function getFile(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }
}
