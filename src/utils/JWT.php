<?php

declare(strict_types=1);

namespace App\Utils;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Exception;

/**
 * JWT Token Helper
 */
class JWT
{
    private static string $secret;
    private static string $algorithm;
    private static int    $expiration;

    private static function init(): void
    {
        $config = require dirname(__DIR__, 2) . '/config/app.php';
        self::$secret     = $config['jwt']['secret'];
        self::$algorithm  = $config['jwt']['algorithm'];
        self::$expiration = $config['jwt']['expiration'];
    }

    public static function generate(array $payload): string
    {
        self::init();

        $payload['iat'] = time();
        $payload['exp'] = time() + self::$expiration;

        return FirebaseJWT::encode($payload, self::$secret, self::$algorithm);
    }

    public static function verify(string $token): array|false
    {
        self::init();

        try {
            $decoded = FirebaseJWT::decode($token, new Key(self::$secret, self::$algorithm));
            return (array) $decoded;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Set HttpOnly cookie for JWT token
     * 
     * Security Features:
     * - HttpOnly: Prevents XSS attacks (JavaScript cannot access)
     * - Secure: Only sent over HTTPS in production
     * - SameSite=Strict: Prevents CSRF attacks
     * - Path=/: Available site-wide
     */
    public static function setHttpOnlyCookie(string $token): void
    {
        self::init();

        setcookie('auth_token', $token, [
            'expires'  => time() + self::$expiration,
            'path'     => '/',
            'httponly' => true,
            'secure'   => isset($_SERVER['HTTPS']),
            'samesite' => 'Strict',
        ]);
    }

    /**
     * Clear HttpOnly cookie on logout
     */
    public static function clearCookie(): void
    {
        setcookie('auth_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'secure'   => isset($_SERVER['HTTPS']),
            'samesite' => 'Strict',
        ]);
    }

    public static function getFromRequest(): string|false
    {
        // From HttpOnly cookie
        if (!empty($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }

        // From Authorization header — beberapa cara karena Apache/XAMPP
        // variasi $_SERVER key tergantung konfigurasi Apache
        $header = '';
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $header = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } elseif (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $header  = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }

        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        // Fallback for export downloads in new window
        if (!empty($_GET['token'])) {
            return $_GET['token'];
        }

        return false;
    }
}
