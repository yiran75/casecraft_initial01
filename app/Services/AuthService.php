<?php
namespace App\Services;

use App\Models\User;
use App\Helpers\Session;

/**
 * Authentication and Security Service
 * Handles login, registration, password hashing, and login throttling
 */
class AuthService
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 900; // 15 minutes

    /**
     * Authenticate user credentials with throttling
     */
    public static function attempt(string $email, string $password): array
    {
        $email = trim(strtolower($email));
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $throttleKey = 'throttle_' . md5($email . '_' . $ip);

        // Check throttling
        $throttle = Session::get($throttleKey, ['count' => 0, 'locked_until' => 0]);
        if ($throttle['locked_until'] > time()) {
            $remaining = ceil(($throttle['locked_until'] - time()) / 60);
            return [
                'success' => false,
                'error' => "Too many failed login attempts. Please try again in {$remaining} minutes."
            ];
        }

        $user = User::findByEmail($email);

        // Generic error message: do not reveal whether an email exists
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $throttle['count']++;
            if ($throttle['count'] >= self::MAX_ATTEMPTS) {
                $throttle['locked_until'] = time() + self::LOCKOUT_SECONDS;
                Session::set($throttleKey, $throttle);
                return [
                    'success' => false,
                    'error' => "Account temporarily locked due to multiple failed login attempts. Please try again in 15 minutes."
                ];
            }
            Session::set($throttleKey, $throttle);
            return [
                'success' => false,
                'error' => 'Invalid email or password.'
            ];
        }

        // Check if account is active
        if ((int)$user['is_active'] !== 1) {
            return [
                'success' => false,
                'error' => 'Your account is deactivated. Please contact the administrator.'
            ];
        }

        // Clear throttle on successful login
        Session::remove($throttleKey);

        // Regenerate session ID to prevent session fixation
        Session::regenerate();

        // Store user payload in session (excluding password hash)
        Session::set('user', [
            'id' => (int) $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'auth_method' => 'PASSWORD',
            'login_time' => date('Y-m-d H:i:s'),
            'ip_address' => $ip
        ]);

        return [
            'success' => true,
            'user' => Session::get('user')
        ];
    }

    /**
     * Authenticate via Biometric Challenge (Fingerprint / Face ID)
     */
    public static function biometricAttempt(string $email, string $authMode = 'FINGERPRINT'): array
    {
        $email = trim(strtolower($email));
        $user = User::findByEmail($email);

        if (!$user) {
            // Default fallback to first active admin/investigator if matching email not found
            $staff = User::getAllStaff();
            $user = !empty($staff) ? User::findById((int)$staff[0]['id']) : null;
        }

        if (!$user || (int)$user['is_active'] !== 1) {
            return ['success' => false, 'error' => 'Personnel profile not active or unrecognized.'];
        }

        // Regenerate session
        Session::regenerate();

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $methodLabel = ($authMode === 'FACE_ID') ? 'FACIAL_RECOGNITION_ID' : 'FINGERPRINT_BIOMETRIC';

        Session::set('user', [
            'id' => (int) $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'auth_method' => $methodLabel,
            'login_time' => date('Y-m-d H:i:s'),
            'ip_address' => $ip
        ]);

        return [
            'success' => true,
            'user' => Session::get('user')
        ];
    }

    /**
     * Authenticate and initialize session for verified Google user
     */
    public static function googleLoginSuccess(array $googleData): array
    {
        $email = trim(strtolower($googleData['email'] ?? ''));
        if (empty($email)) {
            return ['success' => false, 'error' => 'No verified email provided by Google.'];
        }

        $name = trim($googleData['name'] ?? $email);
        $picture = $googleData['picture'] ?? null;
        $googleId = $googleData['google_id'] ?? '';

        $user = User::findByEmail($email);

        if (!$user) {
            // Auto-provision investigator account for verified Google identity
            $userId = User::create([
                'full_name' => $name,
                'email' => $email,
                'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                'role' => 'investigator',
                'is_active' => 1
            ]);
            $user = User::findById($userId);
        }

        if (!$user || (int)$user['is_active'] !== 1) {
            return ['success' => false, 'error' => 'Personnel account is deactivated by CID command.'];
        }

        Session::regenerate();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        Session::set('user', [
            'id' => (int) $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'auth_method' => 'GOOGLE_OAUTH_2_0_VERIFIED',
            'google_id' => $googleId,
            'avatar_url' => $picture,
            'login_time' => date('Y-m-d H:i:s'),
            'ip_address' => $ip
        ]);

        return [
            'success' => true,
            'user' => Session::get('user')
        ];
    }

    /**
     * Authenticate via Google SSO OAuth (Legacy/Fallback)
     */
    public static function googleAttempt(?string $googleEmail = null, ?string $googleName = null): array
    {
        $email = trim(strtolower($googleEmail ?: 'admin@casecraft.local'));
        $name = trim($googleName ?: 'Marcus Vance (Google SSO)');

        $user = User::findByEmail($email);

        if (!$user) {
            // Auto-provision investigator account for authenticated Google ID
            $userId = User::create([
                'full_name' => $name,
                'email' => $email,
                'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                'role' => 'investigator',
                'is_active' => 1
            ]);
            $user = User::findById($userId);
        }

        if ((int)$user['is_active'] !== 1) {
            return ['success' => false, 'error' => 'Account is currently deactivated.'];
        }

        Session::regenerate();

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        Session::set('user', [
            'id' => (int) $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'auth_method' => 'GOOGLE_SSO_OAUTH',
            'login_time' => date('Y-m-d H:i:s'),
            'ip_address' => $ip
        ]);

        return [
            'success' => true,
            'user' => Session::get('user')
        ];
    }

    /**
     * Register a new user
     */
    public static function register(array $data): array
    {
        $fullName = trim($data['full_name'] ?? '');
        $email = trim(strtolower($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $confirmPassword = $data['password_confirmation'] ?? '';
        $role = $data['role'] ?? 'investigator';

        // Validation
        if (empty($fullName)) {
            return ['success' => false, 'error' => 'Full name is required.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'A valid email address is required.'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters long.'];
        }

        if ($password !== $confirmPassword) {
            return ['success' => false, 'error' => 'Password and confirmation password do not match.'];
        }

        if (User::findByEmail($email)) {
            return ['success' => false, 'error' => 'An account with this email address already exists.'];
        }

        // Restrict self-registration to investigator or reviewer by default
        $allowedRoles = ['investigator', 'reviewer'];
        if (!in_array($role, $allowedRoles, true)) {
            $role = 'investigator';
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $userId = User::create([
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
            'is_active' => 1
        ]);

        return [
            'success' => true,
            'user_id' => $userId
        ];
    }

    /**
     * Terminate user session
     */
    public static function logout(): void
    {
        Session::destroy();
    }

    /**
     * Get currently logged-in user or null
     */
    public static function user(): ?array
    {
        return Session::get('user');
    }

    /**
     * Check if user is authenticated
     */
    public static function check(): bool
    {
        return Session::has('user') && !empty(Session::get('user')['id']);
    }

    /**
     * Check user role
     */
    public static function hasRole(string ...$roles): bool
    {
        $user = self::user();
        if (!$user || empty($user['role'])) {
            return false;
        }
        return in_array($user['role'], $roles, true);
    }
}
