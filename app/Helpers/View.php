<?php
namespace App\Helpers;

/**
 * View Rendering Helper with Layout Support
 */
class View
{
    /**
     * Render a view within a layout
     */
    public static function render(string $viewPath, array $data = [], ?string $layoutPath = 'layouts/main'): void
    {
        $viewsDir = dirname(__DIR__, 2) . '/resources/views/';
        $viewFile = $viewsDir . $viewPath . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "Error: View '{$viewPath}' not found at {$viewFile}";
            return;
        }

        // Ensure sanitizer helper is loaded
        require_once __DIR__ . '/Sanitizer.php';

        // Global variables available in all views
        $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
        $currentUser = Session::get('user');
        $csrfToken = Csrf::getToken();
        $flashes = Session::getAllFlashes();

        // Extract passed data into local scope
        extract($data);

        // Capture view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // If no layout requested, output raw view content
        if ($layoutPath === null) {
            echo $content;
            return;
        }

        $layoutFile = $viewsDir . $layoutPath . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Return JSON response
     */
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data);
        exit;
    }
}
