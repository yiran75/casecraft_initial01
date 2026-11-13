<?php
/**
 * CaseCraft - Application Configuration
 * Academic Demo - Fictional Data
 */

return [
    'app_name' => 'CaseCraft',
    'app_tagline' => 'Investigation Case Management System',
    'env' => 'development', // 'development' or 'production'
    'debug' => true,
    'base_url' => getenv('APP_URL') ?: '',
    
    // Academic Demo Notice
    'academic_notice' => 'Academic Demo – Fictional Data Only. Not an official law enforcement system.',

    // Security & Sessions
    'session' => [
        'name' => 'CASECRAFT_SESSID',
        'lifetime' => 7200, // 2 hours in seconds
        'secure' => false,  // Set true if HTTPS is enabled
        'httponly' => true,
        'samesite' => 'Lax'
    ],

    // File Upload Security Allowlist
    'uploads' => [
        'storage_path' => dirname(__DIR__) . '/storage/uploads',
        'max_size_bytes' => 5 * 1024 * 1024, // 5 MB max
        'allowed_mime_types' => [
            'application/pdf',
            'image/jpeg',
            'image/png'
        ],
        'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png']
    ],

    // Pagination
    'pagination' => [
        'per_page' => 10
    ],

    // Rate Limiting (Login Throttling)
    'throttling' => [
        'max_attempts' => 5,
        'lockout_time_seconds' => 900 // 15 minutes lockout
    ]
];
