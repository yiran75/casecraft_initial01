<?php
namespace App\Helpers;

/**
 * Cross-Site Request Forgery (CSRF) Protection
 */
class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Get or generate the current CSRF token
     */
    public static function getToken(): string
    {
        Session::start();
        $token = Session::get(self::TOKEN_KEY);
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::TOKEN_KEY, $token);
        }
        return $token;
    }

    /**
     * Validate an incoming token against the session token
     */
    public static function validate(?string $submittedToken): bool
    {
        Session::start();
        $sessionToken = Session::get(self::TOKEN_KEY);
        if (!$sessionToken || !$submittedToken) {
            return false;
        }
        return hash_equals($sessionToken, $submittedToken);
    }

    /**
     * Generate HTML hidden input field for forms
     */
    public static function field(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verify the current request's CSRF token (POST/PUT/DELETE)
     */
    public static function verifyRequest(): bool
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return true;
        }

        // Check POST parameter
        $token = $_POST['_csrf_token'] ?? null;

        // Check headers (e.g. for AJAX/Fetch)
        if (!$token) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }

        return self::validate($token);
    }
}
