<?php
/**
 * CaseCraft - Front Controller Entry Point
 * Academic Demo - Fictional Data Only
 */

declare(strict_types=1);

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set root path
define('BASE_PATH', dirname(__DIR__));

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load global helper functions
require_once BASE_PATH . '/app/Helpers/helpers.php';

// Start secure session
\App\Helpers\Session::start();

// Catch unhandled exceptions and show friendly error page
try {
    // Load routes and dispatch
    $router = require BASE_PATH . '/routes/web.php';
    $router->dispatch();
} catch (\Throwable $e) {
    error_log("Unhandled Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);

    // If API/AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        \App\Helpers\View::json([
            'success' => false,
            'error' => 'An internal server error occurred. Please contact the administrator.'
        ], 500);
    }

    \App\Helpers\View::render('errors/500', [
        'title' => 'Server Error',
        'message' => 'An unexpected internal error occurred.',
        'exception' => $e
    ], 'layouts/auth');
}
