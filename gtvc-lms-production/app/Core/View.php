<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    public static function baseUrl(): string
    {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? ''));
        $scriptDir = str_replace('\\', '/', dirname($scriptName));
        return ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/');
    }

    public static function url(string $path = ''): string
    {
        $baseUrl = self::baseUrl();
        if (empty($path) || $path === '/') {
            return $baseUrl !== '' ? $baseUrl : '/';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }
        return $baseUrl . '/' . ltrim($path, '/');
    }

    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        extract($data);
        
        // Handle flash messages, CSRF token, and current user globally so views can access them
        $flashSuccess = Session::getFlash('success');
        $flashError = Session::getFlash('error');
        $csrfToken = Session::getCsrfToken();
        $baseUrl = self::baseUrl();
        $currentUser = $data['currentUser'] ?? Session::get('user');

        ob_start();
        $viewFile = __DIR__ . '/../../views/' . str_replace('.', '/', $view) . '.php';
        
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "<h2>View not found: " . self::e($view) . "</h2>";
        }
        $content = ob_get_clean();

        $layoutFile = __DIR__ . '/../../views/' . str_replace('.', '/', $layout) . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    public static function e(?string $str): string
    {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}
