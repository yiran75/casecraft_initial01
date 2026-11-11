<?php
namespace App\Helpers;

/**
 * Input sanitization and XSS prevention helper.
 */
class Sanitizer
{
    /**
     * Escape output for HTML (XSS prevention)
     */
    public static function e(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Sanitize plain string input
     */
    public static function cleanString(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return trim(strip_tags($value));
    }

    /**
     * Sanitize email address
     */
    public static function cleanEmail(?string $email): string
    {
        if ($email === null) {
            return '';
        }
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL) ?: '';
    }
}
