<?php
namespace App\Middleware;

use App\Services\AuthService;
use App\Helpers\Session;

/**
 * Authentication Middleware
 * Enforces server-side authentication for protected endpoints
 */
class AuthMiddleware
{
    public static function handle(): bool
    {
        if (!AuthService::check()) {
            Session::flash('error', 'Please log in to access this page.');
            header('Location: /login');
            exit;
        }
        return true;
    }
}
