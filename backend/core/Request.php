<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP Request Handler
 */
class Request
{
    private string $method;
    private string $uri;
    private array $headers;
    private array $queryParams;
    private mixed $body;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $parsedUrl = parse_url($requestUri);
        $this->uri = rawurldecode($parsedUrl['path'] ?? '/');

        $this->queryParams = $_GET;
        $this->headers = getallheaders() ?: [];

        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $json = json_decode($input, true);
            $this->body = (json_last_error() === JSON_ERROR_NONE) ? $json : $_POST;
        } else {
            $this->body = $_POST;
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

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getBody(): mixed
    {
        return $this->body;
    }

    public function getHeader(string $name): ?string
    {
        $normalized = strtolower($name);
        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === $normalized) {
                return $value;
            }
        }
        return null;
    }
}
