<?php
namespace App\Middleware;

use App\Helpers\Csrf;
use App\Helpers\View;

/**
 * CSRF Validation Middleware
 */
class CsrfMiddleware
{
    public static function handle(): bool
    {
        if (!Csrf::verifyRequest()) {
            http_response_code(419);
            // If AJAX / JSON request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                View::json(['success' => false, 'error' => 'Security token (CSRF) expired or invalid. Please refresh the page.'], 419);
            }

            View::render('errors/403', [
                'title' => 'Page Expired (CSRF Invalid)',
                'message' => 'Your security session token has expired or is invalid. Please return and refresh the page.'
            ], 'layouts/auth');
            exit;
        }

        return true;
    }
}
