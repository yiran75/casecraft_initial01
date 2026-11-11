<?php
/**
 * Global Helper Functions
 * Loaded at bootstrap into global namespace
 */

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return \App\Helpers\Sanitizer::e($value);
    }
}
