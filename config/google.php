<?php
/**
 * CaseCraft - Google Identity & OAuth 2.0 Configuration
 * High-Security Production Authentication Gateway
 */

// Check for Google Cloud Console JSON credentials file if present
$jsonCredentialsPath = __DIR__ . '/credentials.json';
if (!file_exists($jsonCredentialsPath)) {
    $jsonCredentialsPath = __DIR__ . '/google_client_secret.json';
}

$fileClientId = '';
$fileClientSecret = '';
$fileRedirectUri = '';

if (file_exists($jsonCredentialsPath)) {
    $jsonRaw = @file_get_contents($jsonCredentialsPath);
    $data = @json_decode($jsonRaw, true);
    if (!empty($data['web'])) {
        $fileClientId = $data['web']['client_id'] ?? '';
        $fileClientSecret = $data['web']['client_secret'] ?? '';
        if (!empty($data['web']['redirect_uris'][0])) {
            $fileRedirectUri = $data['web']['redirect_uris'][0];
        }
    } elseif (!empty($data['installed'])) {
        $fileClientId = $data['installed']['client_id'] ?? '';
        $fileClientSecret = $data['installed']['client_secret'] ?? '';
    }
}

return [
    /**
     * Google OAuth 2.0 Client ID
     * Obtain from https://console.cloud.google.com/apis/credentials
     */
    'client_id' => getenv('GOOGLE_CLIENT_ID') ?: ($fileClientId ?: ''),

    /**
     * Google OAuth 2.0 Client Secret
     */
    'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: ($fileClientSecret ?: ''),

    /**
     * Authorized Redirect URI
     * Must match exactly in Google Cloud Console
     */
    'redirect_uri' => getenv('GOOGLE_REDIRECT_URI') ?: ($fileRedirectUri ?: 'http://localhost:8000/auth/google/callback'),

    /**
     * OAuth Scopes
     */
    'scopes' => [
        'openid',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile'
    ],

    /**
     * Fallback / Demo sandbox officer accounts when credentials are being set up
     */
    'demo_officers' => [
        [
            'name' => 'Superintendent M. Vance (CID HQ)',
            'email' => 'admin@casecraft.local',
            'role' => 'admin'
        ],
        [
            'name' => 'Inspector Elena Rostova (Special Crime)',
            'email' => 'investigator@casecraft.local',
            'role' => 'investigator'
        ],
        [
            'name' => 'Forensic Analyst David Chen (Cyber Forensics)',
            'email' => 'dchen@casecraft.local',
            'role' => 'investigator'
        ]
    ]
];
