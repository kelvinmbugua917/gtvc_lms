<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Server-Rendered View Engine for PHP Front-End
 */
class View
{
    /**
     * Render a view file inside a layout template
     */
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        // Add global auth session state to view data
        $data['currentUser'] = Session::get('user');
        $data['flashMessage'] = Session::getFlash('success');
        $data['flashError'] = Session::getFlash('error');
        $data['csrfToken'] = Session::getCsrfToken();

        extract($data);

        // Buffer the view content
        ob_start();
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "<div style='padding: 2rem; color: #dc2626; font-family: sans-serif;'>
                    <h2>View Not Found</h2>
                    <p>Template file <code>views/{$view}.php</code> could not be found.</p>
                  </div>";
        }
        $content = ob_get_clean();

        // Render inside layout
        $layoutPath = __DIR__ . '/../../views/' . $layout . '.php';
        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Escape output safely against XSS
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
