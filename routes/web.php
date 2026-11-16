<?php
/**
 * CaseCraft - Web Routes Definition
 */

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => rtrim($path, '/') ?: '/',
            'handler' => $handler
        ];
    }

    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Normalize base path if running inside a subfolder (e.g. /casecraft/public)
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && str_starts_with($uri, $scriptName)) {
            $uri = substr($uri, strlen($scriptName));
        }

        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $route['path'] === $uri) {
                $handler = $route['handler'];

                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    $controller->$action();
                    return;
                }

                if (is_callable($handler)) {
                    $handler();
                    return;
                }
            }
        }

        // Route not found -> 404
        http_response_code(404);
        \App\Helpers\View::render('errors/404', [
            'title' => 'Page Not Found',
            'message' => 'The page you requested could not be found.'
        ], \App\Services\AuthService::check() ? 'layouts/main' : 'layouts/auth');
    }
}

// Router instance
$router = new Router();

// Public Routes
$router->get('/', [\App\Controllers\HomeController::class, 'index']);
$router->get('/login', [\App\Controllers\AuthController::class, 'showLogin']);
$router->post('/login', [\App\Controllers\AuthController::class, 'login']);
$router->get('/auth/setup-passcode', [\App\Controllers\AuthController::class, 'showSetupPasscode']);
$router->post('/auth/setup-passcode', [\App\Controllers\AuthController::class, 'saveSetupPasscode']);
$router->post('/auth/first-time-setup', [\App\Controllers\AuthController::class, 'firstTimeSetup']);
$router->post('/auth/biometric', [\App\Controllers\AuthController::class, 'biometricLogin']);

// Real Google OAuth 2.0 & Identity Services Routes
$router->get('/auth/google/redirect', [\App\Controllers\AuthController::class, 'redirectToGoogle']);
$router->get('/auth/google/callback', [\App\Controllers\AuthController::class, 'googleCallback']);
$router->post('/auth/google/token', [\App\Controllers\AuthController::class, 'googleVerifyToken']);
$router->post('/auth/google/setup', [\App\Controllers\AuthController::class, 'saveGoogleSetup']);
$router->get('/auth/google', [\App\Controllers\AuthController::class, 'googleLogin']);
$router->post('/auth/google', [\App\Controllers\AuthController::class, 'googleLogin']);

$router->get('/signup', [\App\Controllers\AuthController::class, 'showSignup']);
$router->post('/signup', [\App\Controllers\AuthController::class, 'signup']);
$router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);

// Dashboard & Case Management
$router->get('/dashboard', [\App\Controllers\DashboardController::class, 'index']);
$router->get('/cases', [\App\Controllers\CaseController::class, 'index']);
$router->get('/cases/create', [\App\Controllers\CaseController::class, 'create']);
$router->post('/cases/create', [\App\Controllers\CaseController::class, 'store']);
$router->get('/cases/view', [\App\Controllers\CaseController::class, 'show']);
$router->get('/cases/edit', [\App\Controllers\CaseController::class, 'edit']);
$router->post('/cases/edit', [\App\Controllers\CaseController::class, 'update']);
$router->post('/cases/status', [\App\Controllers\CaseController::class, 'updateStatusAjax']);

// Suspects & Criminal Profiles
$router->post('/suspects/create', [\App\Controllers\SuspectController::class, 'store']);
$router->post('/suspects/update', [\App\Controllers\SuspectController::class, 'update']);
$router->post('/suspects/delete', [\App\Controllers\SuspectController::class, 'delete']);

// Victims Profiles
$router->post('/victims/create', [\App\Controllers\VictimController::class, 'store']);
$router->post('/victims/update', [\App\Controllers\VictimController::class, 'update']);
$router->post('/victims/delete', [\App\Controllers\VictimController::class, 'delete']);

// Witness Statements
$router->post('/witness/create', [\App\Controllers\WitnessController::class, 'store']);
$router->post('/witness/update', [\App\Controllers\WitnessController::class, 'update']);
$router->post('/witness/delete', [\App\Controllers\WitnessController::class, 'delete']);

// Purge Demo Cases for clean operational use
$router->post('/cases/purge-demo', [\App\Controllers\CaseController::class, 'purgeDemoCases']);

// Evidence Routes
$router->post('/evidence/create', [\App\Controllers\EvidenceController::class, 'store']);
$router->get('/evidence/download', [\App\Controllers\EvidenceController::class, 'download']);

// Witness Statements & Notes
$router->post('/witness/create', [\App\Controllers\WitnessController::class, 'store']);
$router->post('/notes/create', [\App\Controllers\NoteController::class, 'store']);

// Reports & Export
$router->get('/reports', [\App\Controllers\ReportController::class, 'index']);
$router->get('/cases/report', [\App\Controllers\ReportController::class, 'printReport']);
$router->get('/cases/export-csv', [\App\Controllers\ReportController::class, 'exportCsv']);

// Profile
$router->get('/profile', [\App\Controllers\ProfileController::class, 'index']);
$router->post('/profile/update', [\App\Controllers\ProfileController::class, 'updateProfile']);
$router->post('/profile/password', [\App\Controllers\ProfileController::class, 'changePassword']);

// Admin-Only Routes
$router->get('/admin/users', [\App\Controllers\AdminController::class, 'users']);
$router->post('/admin/users/role', [\App\Controllers\AdminController::class, 'updateUserRole']);
$router->get('/admin/logs', [\App\Controllers\AdminController::class, 'logs']);

return $router;
