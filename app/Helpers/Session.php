<?php
namespace App\Helpers;

/**
 * Secure Session Manager with Flash Messaging
 */
class Session
{
    private static bool $started = false;

    /**
     * Start the session with secure cookie parameters
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $config = require dirname(__DIR__, 2) . '/config/app.php';
        $sessConfig = $config['session'] ?? [];

        // Set secure cookie parameters
        session_set_cookie_params([
            'lifetime' => $sessConfig['lifetime'] ?? 7200,
            'path' => '/',
            'domain' => '',
            'secure' => $sessConfig['secure'] ?? false,
            'httponly' => $sessConfig['httponly'] ?? true,
            'samesite' => $sessConfig['samesite'] ?? 'Lax'
        ]);

        if (!empty($sessConfig['name'])) {
            session_name($sessConfig['name']);
        }

        // Ensure session save path is inside storage/sessions
        $sessPath = dirname(__DIR__, 2) . '/storage/sessions';
        if (!is_dir($sessPath)) {
            @mkdir($sessPath, 0777, true);
        }
        @session_save_path($sessPath);

        session_start();
        self::$started = true;
    }

    /**
     * Regenerate session ID (mitigate session fixation on login)
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        self::$started = false;
    }

    /**
     * Flash messages: survive for one redirect only
     */
    public static function flash(string $type, string $message): void
    {
        self::start();
        $_SESSION['_flash'][$type] = $message;
    }

    public static function getFlash(string $type): ?string
    {
        self::start();
        if (isset($_SESSION['_flash'][$type])) {
            $msg = $_SESSION['_flash'][$type];
            unset($_SESSION['_flash'][$type]);
            return $msg;
        }
        return null;
    }

    public static function getAllFlashes(): array
    {
        self::start();
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }
}
