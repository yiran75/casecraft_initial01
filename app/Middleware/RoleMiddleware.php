<?php
namespace App\Middleware;

use App\Services\AuthService;
use App\Helpers\View;

/**
 * Role-Based Access Control Middleware
 * Enforces server-side permissions for specific roles (e.g. admin, investigator)
 */
class RoleMiddleware
{
    public static function handle(string ...$allowedRoles): bool
    {
        AuthMiddleware::handle();

        if (!AuthService::hasRole(...$allowedRoles)) {
            http_response_code(403);
            View::render('errors/403', [
                'title' => 'Access Denied',
                'message' => 'You do not have the required role or authorization to access this resource.'
            ], 'layouts/main');
            exit;
        }

        return true;
    }
}
