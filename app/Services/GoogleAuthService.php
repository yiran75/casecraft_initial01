<?php
namespace App\Services;

use App\Helpers\Session;

/**
 * Google Identity & OAuth 2.0 Security Service
 * 
 * Provides production-grade Google authentication:
 * 1. Google OAuth 2.0 Authorization Code Flow (Server-side redirect)
 * 2. Google Identity Services (GIS) / One-Tap JWT verification
 * 3. Token verification via official Google tokeninfo endpoints
 * 4. Automatic investigator provisioning and zero-knowledge session setup
 */
class GoogleAuthService
{
    private static ?array $config = null;

    /**
     * Load configuration
     */
    public static function getConfig(): array
    {
        if (self::$config === null) {
            $configPath = dirname(__DIR__, 2) . '/config/google.php';
            self::$config = file_exists($configPath) ? require $configPath : [];
        }
        return self::$config;
    }

    /**
     * Check if Google Cloud Client ID is configured
     */
    public static function isConfigured(): bool
    {
        $config = self::getConfig();
        return !empty($config['client_id']) && strlen(trim($config['client_id'])) > 10;
    }

    /**
     * Get Google Client ID
     */
    public static function getClientId(): string
    {
        $config = self::getConfig();
        return trim($config['client_id'] ?? '');
    }

    /**
     * Get Google Client Secret
     */
    public static function getClientSecret(): string
    {
        $config = self::getConfig();
        return trim($config['client_secret'] ?? '');
    }

    /**
     * Determine dynamic redirect URI
     */
    public static function getRedirectUri(): string
    {
        $config = self::getConfig();
        if (!empty($config['redirect_uri'])) {
            return $config['redirect_uri'];
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        return "{$scheme}://{$host}/auth/google/callback";
    }

    /**
     * Generate official Google OAuth 2.0 Authorization URL
     */
    public static function getAuthUrl(): string
    {
        $clientId = self::getClientId();
        $redirectUri = self::getRedirectUri();
        $state = bin2hex(random_bytes(16));
        Session::set('google_oauth_state', $state);

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'prompt' => 'select_account',
            'state' => $state
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for tokens and fetch user profile
     */
    public static function handleCodeExchange(string $code, ?string $state = null): array
    {
        // State validation for CSRF mitigation
        $savedState = Session::get('google_oauth_state');
        if ($savedState && $state && !hash_equals($savedState, $state)) {
            return ['success' => false, 'error' => 'Invalid OAuth state security token. Cross-site request rejected.'];
        }
        Session::remove('google_oauth_state');

        $clientId = self::getClientId();
        $clientSecret = self::getClientSecret();
        $redirectUri = self::getRedirectUri();

        if (empty($clientId) || empty($clientSecret)) {
            return ['success' => false, 'error' => 'Google Client ID or Secret is not configured.'];
        }

        // Exchange code with Google token endpoint
        $postData = [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 20
        ]);

        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => 'Failed to reach Google OAuth server: ' . $curlError];
        }

        $tokenData = json_decode($rawResponse, true);

        if ($httpCode !== 200 || empty($tokenData['access_token'])) {
            $errDesc = $tokenData['error_description'] ?? $tokenData['error'] ?? 'Token exchange rejected by Google.';
            return ['success' => false, 'error' => 'Google authorization failed: ' . $errDesc];
        }

        // Fetch user profile using access_token
        $userInfo = self::fetchUserInfo($tokenData['access_token']);
        if (!$userInfo['success']) {
            return $userInfo;
        }

        return $userInfo;
    }

    /**
     * Fetch user profile from Google UserInfo endpoint
     */
    public static function fetchUserInfo(string $accessToken): array
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Accept: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 15
        ]);

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($raw, true);

        if ($httpCode !== 200 || empty($data['email'])) {
            return ['success' => false, 'error' => 'Could not retrieve verified profile from Google.'];
        }

        return [
            'success' => true,
            'email' => strtolower($data['email']),
            'name' => $data['name'] ?? $data['email'],
            'google_id' => $data['sub'] ?? '',
            'picture' => $data['picture'] ?? null,
            'email_verified' => !empty($data['email_verified'])
        ];
    }

    /**
     * Verify Google Identity Services (GIS) ID Token (JWT) directly via Google tokeninfo
     */
    public static function verifyIdToken(string $idToken): array
    {
        $idToken = trim($idToken);
        if (empty($idToken)) {
            return ['success' => false, 'error' => 'Missing Google ID Token.'];
        }

        $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 15
        ]);

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => 'Token verification network error: ' . $curlError];
        }

        $data = json_decode($raw, true);

        if ($httpCode !== 200 || empty($data['email'])) {
            $err = $data['error_description'] ?? $data['error'] ?? 'Invalid ID token.';
            return ['success' => false, 'error' => 'Google verification failed: ' . $err];
        }

        // Validate Audience matches our Client ID if configured
        $configuredClientId = self::getClientId();
        if (!empty($configuredClientId) && !empty($data['aud'])) {
            if ($data['aud'] !== $configuredClientId) {
                return ['success' => false, 'error' => 'ID Token audience mismatch. Possible spoofed token.'];
            }
        }

        // Validate email verification status
        if (isset($data['email_verified']) && $data['email_verified'] !== 'true' && $data['email_verified'] !== true) {
            return ['success' => false, 'error' => 'Google email address is not verified.'];
        }

        return [
            'success' => true,
            'email' => strtolower($data['email']),
            'name' => $data['name'] ?? $data['email'],
            'google_id' => $data['sub'] ?? '',
            'picture' => $data['picture'] ?? null,
            'email_verified' => true
        ];
    }

    /**
     * Save/Update Google Cloud Credentials into config/google_client_secret.json
     */
    public static function saveCredentials(string $clientId, string $clientSecret, string $redirectUri = ''): bool
    {
        $clientId = trim($clientId);
        $clientSecret = trim($clientSecret);
        $redirectUri = trim($redirectUri) ?: self::getRedirectUri();

        $payload = [
            'web' => [
                'client_id' => $clientId,
                'project_id' => 'casecraft-cid-intel',
                'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
                'token_uri' => 'https://oauth2.googleapis.com/token',
                'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
                'client_secret' => $clientSecret,
                'redirect_uris' => [$redirectUri]
            ]
        ];

        $targetPath = dirname(__DIR__, 2) . '/config/google_client_secret.json';
        $written = @file_put_contents($targetPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Reset static cache
        self::$config = null;

        return $written !== false;
    }
}
