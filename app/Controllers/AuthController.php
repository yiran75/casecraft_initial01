<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Services\GoogleAuthService;
use App\Helpers\View;
use App\Helpers\Session;
use App\Middleware\CsrfMiddleware;

/**
 * Authentication Controller
 * Handles user login, signup, session initiation, Google OAuth 2.0, GIS, and signout
 */
class AuthController
{
    /**
     * Display login form
     */
    public function showLogin(): void
    {
        if (AuthService::check()) {
            header('Location: /dashboard');
            exit;
        }

        View::render('auth/login', [
            'title' => 'Sign In - CaseCraft CID Intelligence Portal',
            'isGoogleConfigured' => GoogleAuthService::isConfigured(),
            'googleClientId' => GoogleAuthService::getClientId(),
            'googleRedirectUri' => GoogleAuthService::getRedirectUri(),
            'googleAuthUrl' => GoogleAuthService::isConfigured() ? GoogleAuthService::getAuthUrl() : null
        ], 'layouts/auth');
    }

    /**
     * Process standard login submission with Security Passcode
     */
    public function login(): void
    {
        CsrfMiddleware::handle();

        $email = trim(strtolower($_POST['email'] ?? ''));
        $passcode = $_POST['password'] ?? '';

        if (empty($email) || empty($passcode)) {
            Session::flash('error', 'দয়া করে আপনার অফিশিয়াল ইমেইল এবং সিকিউরিটি পাসকোড লিখুন।');
            header('Location: /login');
            exit;
        }

        $user = \App\Models\User::findByEmail($email);

        // If user exists but has not set a passcode yet (e.g. initial seeded account)
        if ($user && empty($user['has_set_passcode'])) {
            Session::set('pending_user_id', (int)$user['id']);
            Session::flash('info', 'অফিসার মহোদয়, প্রথমবারের মতো আপনার ব্যক্তিগত সিকিউরিটি পাসকোড নির্ধারণ করুন।');
            header('Location: /auth/setup-passcode');
            exit;
        }

        $result = AuthService::attempt($email, $passcode);

        if (!$result['success']) {
            Session::flash('error', 'ভুল সিকিউরিটি পাসকোড বা ইমেইল ঠিকানা। পুনরায় চেষ্টা করুন।');
            header('Location: /login');
            exit;
        }

        Session::flash('success', 'স্বাগতম অফিসার ' . htmlspecialchars($result['user']['full_name']) . '! আপনার তদন্ত ডকেট আনলক করা হয়েছে।');
        header('Location: /dashboard');
        exit;
    }

    /**
     * First-time officer registration & personal passcode initialization
     */
    public function firstTimeSetup(): void
    {
        CsrfMiddleware::handle();

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim(strtolower($_POST['email'] ?? ''));
        $badgeId = trim($_POST['badge_id'] ?? '');
        $passcode = $_POST['passcode'] ?? '';
        $passcodeConfirm = $_POST['passcode_confirm'] ?? '';

        if (empty($fullName) || empty($email) || empty($passcode)) {
            Session::flash('error', 'নাম, ইমেইল এবং পাসকোড পূরণ করা আবশ্যক।');
            header('Location: /login');
            exit;
        }

        if (strlen($passcode) < 4) {
            Session::flash('error', 'পাসকোড ন্যূনতম ৪ অক্ষর বা ডিজিট হতে হবে।');
            header('Location: /login');
            exit;
        }

        if ($passcode !== $passcodeConfirm) {
            Session::flash('error', 'উভয় ঘরে প্রদত্ত পাসকোড মেলেনি। পুনরায় চেষ্টা করুন।');
            header('Location: /login');
            exit;
        }

        $user = \App\Models\User::findByEmail($email);

        if ($user) {
            if (!empty($user['has_set_passcode'])) {
                Session::flash('error', 'এই ইমেইল ঠিকানায় ইতিমধ্যে পাসকোড সেট করা রয়েছে। লগইন ট্যাবে গিয়ে পাসকোড দিয়ে প্রবেশ করুন।');
                header('Location: /login');
                exit;
            }
            \App\Models\User::setPasscode((int)$user['id'], $passcode);
            $userId = (int)$user['id'];
        } else {
            $userId = \App\Models\User::create([
                'full_name' => $fullName,
                'email' => $email,
                'badge_id' => $badgeId,
                'password_hash' => password_hash($passcode, PASSWORD_DEFAULT),
                'role' => 'investigator',
                'is_active' => 1
            ]);
            \App\Models\User::setPasscode($userId, $passcode);
        }

        // Establish officer session
        Session::regenerate();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userObj = \App\Models\User::findById($userId);

        Session::set('user', [
            'id' => $userId,
            'full_name' => $userObj['full_name'] ?? $fullName,
            'email' => $email,
            'role' => $userObj['role'] ?? 'investigator',
            'auth_method' => 'OFFICER_PASSCODE',
            'login_time' => date('Y-m-d H:i:s'),
            'ip_address' => $ip
        ]);

        Session::flash('success', "স্বাগতম অফিসার " . htmlspecialchars($fullName) . "! আপনার সিকিউরিটি পাসকোড সংরক্ষিত হয়েছে এবং ডকেট সক্রিয় করা হয়েছে।");
        header('Location: /dashboard');
        exit;
    }

    /**
     * Show setup passcode screen
     */
    public function showSetupPasscode(): void
    {
        $pendingId = Session::get('pending_user_id') ?? (Session::get('user')['id'] ?? null);

        if (!$pendingId) {
            header('Location: /login');
            exit;
        }

        View::render('auth/setup_passcode', [
            'title' => 'Set Security Passcode - CaseCraft'
        ], 'layouts/auth');
    }

    /**
     * Save newly set passcode
     */
    public function saveSetupPasscode(): void
    {
        CsrfMiddleware::handle();

        $passcode = $_POST['passcode'] ?? '';
        $passcodeConfirm = $_POST['passcode_confirm'] ?? '';

        if (strlen($passcode) < 4) {
            Session::flash('error', 'পাসকোড ন্যূনতম ৪ ডিজিট বা অক্ষরের হতে হবে।');
            header('Location: /auth/setup-passcode');
            exit;
        }

        if ($passcode !== $passcodeConfirm) {
            Session::flash('error', 'উভয় ঘরে প্রদত্ত পাসকোড মেলেনি।');
            header('Location: /auth/setup-passcode');
            exit;
        }

        $pendingId = Session::get('pending_user_id') ?? (Session::get('user')['id'] ?? null);

        if (!$pendingId) {
            Session::flash('error', 'সেশন শেষ হয়েছে। পুনরায় লগইন করুন।');
            header('Location: /login');
            exit;
        }

        \App\Models\User::setPasscode((int)$pendingId, $passcode);
        Session::remove('pending_user_id');

        $user = \App\Models\User::findById((int)$pendingId);
        Session::regenerate();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        Session::set('user', [
            'id' => (int)$user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'auth_method' => 'OFFICER_PASSCODE',
            'login_time' => date('Y-m-d H:i:s'),
            'ip_address' => $ip
        ]);

        Session::flash('success', 'আপনার সিকিউরিটি পাসকোড সফলভাবে সংরক্ষিত হয়েছে। ডকেটে স্বাগতম!');
        header('Location: /dashboard');
        exit;
    }

    /**
     * Process biometric fingerprint or face ID verification
     */
    public function biometricLogin(): void
    {
        CsrfMiddleware::handle();

        $email = trim($_POST['email'] ?? 'admin@casecraft.local');
        $authMode = trim($_POST['auth_mode'] ?? 'FINGERPRINT');

        $result = AuthService::biometricAttempt($email, $authMode);

        if (!$result['success']) {
            View::json(['success' => false, 'error' => $result['error']], 401);
        }

        Session::flash('success', "Biometric verification confirmed ({$authMode}). Welcome, " . htmlspecialchars($result['user']['full_name']) . "!");

        View::json([
            'success' => true,
            'message' => 'Biometric verified.',
            'redirect' => '/dashboard'
        ]);
    }

    /**
     * Redirect to official Google OAuth 2.0 authorization page
     */
    public function redirectToGoogle(): void
    {
        if (!GoogleAuthService::isConfigured()) {
            Session::flash('error', 'Google OAuth 2.0 Client ID is not configured. Please configure your credentials via Setup.');
            header('Location: /login');
            exit;
        }

        $authUrl = GoogleAuthService::getAuthUrl();
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Handle official Google OAuth 2.0 redirect callback
     */
    public function googleCallback(): void
    {
        if (!empty($_GET['error'])) {
            Session::flash('error', 'Google Sign-In canceled or access denied: ' . htmlspecialchars($_GET['error']));
            header('Location: /login');
            exit;
        }

        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';

        if (empty($code)) {
            Session::flash('error', 'Authorization code not received from Google.');
            header('Location: /login');
            exit;
        }

        $exchangeResult = GoogleAuthService::handleCodeExchange($code, $state);

        if (!$exchangeResult['success']) {
            Session::flash('error', $exchangeResult['error']);
            header('Location: /login');
            exit;
        }

        $authResult = AuthService::googleLoginSuccess($exchangeResult);

        if (!$authResult['success']) {
            Session::flash('error', $authResult['error']);
            header('Location: /login');
            exit;
        }

        $userRecord = \App\Models\User::findById((int)$authResult['user']['id']);
        if (empty($userRecord['has_set_passcode'])) {
            Session::set('pending_user_id', (int)$userRecord['id']);
            Session::flash('info', 'গুগল একাউন্ট সফলভাবে যাচাই করা হয়েছে! এবার আপনার তদন্ত ফাইলের সুরক্ষার জন্য আপনার নিজস্ব একটি সিকিউরিটি পাসকোড নির্ধারণ করুন।');
            header('Location: /auth/setup-passcode');
            exit;
        }

        Session::flash('success', "Google Authentication Verified! Welcome, Officer " . htmlspecialchars($authResult['user']['full_name']) . " (" . htmlspecialchars($authResult['user']['email']) . ").");
        header('Location: /dashboard');
        exit;
    }

    /**
     * Handle Google Identity Services (GIS) / One-Tap ID Token verification
     */
    public function googleVerifyToken(): void
    {
        $credential = $_POST['credential'] ?? '';
        if (empty($credential)) {
            $raw = file_get_contents('php://input');
            $jsonData = json_decode($raw, true);
            $credential = $jsonData['credential'] ?? '';
        }

        if (empty($credential)) {
            View::json(['success' => false, 'error' => 'Missing Google credential token.'], 400);
            return;
        }

        $verifyResult = GoogleAuthService::verifyIdToken($credential);

        if (!$verifyResult['success']) {
            View::json(['success' => false, 'error' => $verifyResult['error']], 401);
            return;
        }

        $authResult = AuthService::googleLoginSuccess($verifyResult);

        if (!$authResult['success']) {
            View::json(['success' => false, 'error' => $authResult['error']], 403);
            return;
        }

        $userRecord = \App\Models\User::findById((int)$authResult['user']['id']);
        $targetRedirect = '/dashboard';

        if (empty($userRecord['has_set_passcode'])) {
            Session::set('pending_user_id', (int)$userRecord['id']);
            $targetRedirect = '/auth/setup-passcode';
        }

        Session::flash('success', "Google Security Clearance Confirmed. Welcome, Officer " . htmlspecialchars($authResult['user']['full_name']) . "!");

        // If AJAX request, return JSON; otherwise redirect
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            View::json(['success' => true, 'redirect' => $targetRedirect]);
            return;
        }

        header('Location: ' . $targetRedirect);
        exit;
    }

    /**
     * Save Google Cloud API credentials from setup modal
     */
    public function saveGoogleSetup(): void
    {
        CsrfMiddleware::handle();

        $clientId = trim($_POST['client_id'] ?? '');
        $clientSecret = trim($_POST['client_secret'] ?? '');
        $redirectUri = trim($_POST['redirect_uri'] ?? '');

        if (empty($clientId)) {
            Session::flash('error', 'Google Client ID cannot be empty.');
            header('Location: /login');
            exit;
        }

        $saved = GoogleAuthService::saveCredentials($clientId, $clientSecret, $redirectUri);

        if ($saved) {
            Session::flash('success', 'Google Cloud API credentials saved successfully! You can now authenticate with official Google SSO.');
        } else {
            Session::flash('error', 'Failed to write credentials file. Please check folder permissions.');
        }

        header('Location: /login');
        exit;
    }

    /**
     * Google Sign-In Fallback & Direct Officer Entry
     */
    public function googleLogin(): void
    {
        $googleEmail = trim($_POST['google_email'] ?? $_GET['email'] ?? 'admin@casecraft.local');
        $googleName = trim($_POST['google_name'] ?? $_GET['name'] ?? 'Authorized Officer (Google SSO)');

        if (empty($googleEmail) || !filter_var($googleEmail, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please provide a valid Google email address.');
            header('Location: /login');
            exit;
        }

        $result = AuthService::googleAttempt($googleEmail, $googleName);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            header('Location: /login');
            exit;
        }

        $userRecord = \App\Models\User::findById((int)$result['user']['id']);
        if (empty($userRecord['has_set_passcode'])) {
            Session::set('pending_user_id', (int)$userRecord['id']);
            Session::flash('info', 'গুগল আইডি ভেরিফাইড! তদন্ত ডকেটের সুরক্ষার জন্য আপনার নিজস্ব একটি সিকিউরিটি পাসকোড নির্ধারণ করুন।');
            header('Location: /auth/setup-passcode');
            exit;
        }

        Session::flash('success', "Signed in with Google Account: {$googleEmail}. Welcome to confidential CID portal.");
        header('Location: /dashboard');
        exit;
    }

    /**
     * Display signup registration form
     */
    public function showSignup(): void
    {
        if (AuthService::check()) {
            header('Location: /dashboard');
            exit;
        }

        View::render('auth/signup', [
            'title' => 'Register - CaseCraft'
        ], 'layouts/auth');
    }

    /**
     * Process signup submission
     */
    public function signup(): void
    {
        CsrfMiddleware::handle();

        $result = AuthService::register($_POST);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            header('Location: /signup');
            exit;
        }

        Session::flash('success', 'Registration successful! You may now sign in with your credentials.');
        header('Location: /login');
        exit;
    }

    /**
     * Process logout
     */
    public function logout(): void
    {
        AuthService::logout();
        Session::flash('success', 'You have been securely logged out of the terminal.');
        header('Location: /login');
        exit;
    }
}
