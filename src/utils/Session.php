<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Session Manager
 * Handles session initialization, validation, and security
 * 
 * NOTE: This class is currently NOT USED in the application.
 * The application uses JWT-based authentication via cookies instead.
 * This class is kept for potential future use.
 * 
 * @see JWT::setHttpOnlyCookie() for current authentication method
 */
class Session
{
    private static bool $initialized = false;
    private static array $config = [];

    /**
     * Initialize session with security settings
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        // Load configuration
        $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
        self::$config = $appConfig['session'];

        // Prevent session fixation
        if (session_status() === PHP_SESSION_NONE) {
            // Set session name
            session_name(self::$config['name']);

            // Configure session cookie parameters
            session_set_cookie_params([
                'lifetime' => self::$config['cookie_lifetime'],
                'path'     => self::$config['cookie_path'],
                'domain'   => self::$config['cookie_domain'],
                'secure'   => self::$config['cookie_secure'],
                'httponly' => self::$config['cookie_httponly'],
                'samesite' => self::$config['cookie_samesite'],
            ]);

            // Start session
            session_start();

            // Regenerate session ID on first access
            if (!isset($_SESSION['initialized'])) {
                session_regenerate_id(true);
                $_SESSION['initialized'] = true;
                $_SESSION['created_at'] = time();
            }

            // Set last activity time
            if (!isset($_SESSION['last_activity'])) {
                $_SESSION['last_activity'] = time();
            }
        }

        self::$initialized = true;
    }

    /**
     * Check if session is valid (not expired)
     */
    public static function isValid(): bool
    {
        self::init();

        // Check if session has last activity
        if (!isset($_SESSION['last_activity'])) {
            return false;
        }

        $timeoutSeconds = self::$config['timeout_minutes'] * 60;
        $elapsed = time() - $_SESSION['last_activity'];

        // Session expired
        if ($elapsed > $timeoutSeconds) {
            self::destroy();
            return false;
        }

        // Update last activity time
        $_SESSION['last_activity'] = time();

        return true;
    }

    /**
     * Set session data
     */
    public static function set(string $key, mixed $value): void
    {
        self::init();
        $_SESSION[$key] = $value;
    }

    /**
     * Get session data
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::init();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     */
    public static function has(string $key): bool
    {
        self::init();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     */
    public static function remove(string $key): void
    {
        self::init();
        unset($_SESSION[$key]);
    }

    /**
     * Get all session data
     */
    public static function all(): array
    {
        self::init();
        return $_SESSION;
    }

    /**
     * Destroy session completely
     */
    public static function destroy(): void
    {
        self::init();

        // Clear session data
        $_SESSION = [];

        // Delete session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires'  => time() - 3600,
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'],
                ]
            );
        }

        // Destroy session
        session_destroy();
        self::$initialized = false;
    }

    /**
     * Regenerate session ID (prevent session fixation)
     */
    public static function regenerate(): void
    {
        self::init();
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    /**
     * Get session ID
     */
    public static function getId(): string
    {
        self::init();
        return session_id();
    }

    /**
     * Get remaining session time in seconds
     */
    public static function getRemainingTime(): int
    {
        self::init();

        if (!isset($_SESSION['last_activity'])) {
            return 0;
        }

        $timeoutSeconds = self::$config['timeout_minutes'] * 60;
        $elapsed = time() - $_SESSION['last_activity'];
        $remaining = $timeoutSeconds - $elapsed;

        return max(0, $remaining);
    }

    /**
     * Get session info for debugging
     */
    public static function getInfo(): array
    {
        self::init();

        return [
            'session_id'       => session_id(),
            'session_name'     => session_name(),
            'created_at'       => $_SESSION['created_at'] ?? null,
            'last_activity'    => $_SESSION['last_activity'] ?? null,
            'remaining_time'   => self::getRemainingTime(),
            'timeout_minutes'  => self::$config['timeout_minutes'],
            'is_valid'         => self::isValid(),
        ];
    }

    /**
     * Flash message - set data that will be available only for next request
     */
    public static function flash(string $key, mixed $value): void
    {
        self::init();
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Get flash message and remove it
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        self::init();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Check if flash message exists
     */
    public static function hasFlash(string $key): bool
    {
        self::init();
        return isset($_SESSION['_flash'][$key]);
    }
}

