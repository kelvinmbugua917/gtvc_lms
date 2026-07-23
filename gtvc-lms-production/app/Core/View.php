<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        extract($data);
        
        // Handle flash messages globally so views can access them
        $flashSuccess = Session::getFlash('success');
        $flashError = Session::getFlash('error');

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

    public static function e(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}
