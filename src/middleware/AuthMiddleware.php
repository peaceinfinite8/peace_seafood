<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Utils\JWT;
use App\Utils\Response;
use App\Utils\Database;
use App\Utils\Session;

/**
 * JWT Authentication Middleware with Session Support
 */
class AuthMiddleware
{
    /**
     * Verify JWT and Session, attach user to request context.
     * Returns decoded user payload or calls Response::unauthorized().
     */
    public static function handle(): array
    {
        // Initialize session
        Session::init();

        // Check if session is valid
        if (!Session::isValid()) {
            Response::unauthorized('Session expired. Silakan login ulang.');
        }

        $token = JWT::getFromRequest();

        if (!$token) {
            Response::unauthorized('Token tidak ditemukan. Silakan login terlebih dahulu.');
        }

        $payload = JWT::verify($token);

        if (!$payload) {
            // Token expired or invalid - destroy session
            Session::destroy();
            JWT::clearCookie();
            Response::unauthorized('Token tidak valid atau sudah expired. Silakan login ulang.');
        }

        // Verify user still exists and is active
        $user = Database::fetchOne(
            "SELECT id, name, email, role, id_gudang, is_active FROM users WHERE id = ?",
            [$payload['id'] ?? $payload['user_id'] ?? 0]
        );

        if (!$user || !$user['is_active']) {
            Session::destroy();
            JWT::clearCookie();
            Response::unauthorized('Akun tidak aktif atau tidak ditemukan.');
        }

        // Verify session user matches token user
        $sessionUserId = Session::get('user_id');
        if ($sessionUserId && $sessionUserId != $user['id']) {
            Session::destroy();
            JWT::clearCookie();
            Response::unauthorized('Session mismatch. Silakan login ulang.');
        }

        // Update session data if needed
        if (!$sessionUserId) {
            Session::set('user_id', $user['id']);
            Session::set('user_email', $user['email']);
            Session::set('user_role', $user['role']);
            Session::set('user_name', $user['name']);
            Session::set('id_gudang', $user['id_gudang']);
            Session::set('authenticated', true);
        }

        // Attach to global context
        $GLOBALS['auth_user'] = $user;

        return $user;
    }

    /**
     * Get currently authenticated user (must call handle() first)
     */
    public static function user(): array
    {
        return $GLOBALS['auth_user'] ?? [];
    }

    /**
     * Alias for user() - returns auth user array
     */
    public static function getAuthUser(): array
    {
        return self::user();
    }

    /**
     * Get authenticated user ID
     */
    public static function getAuthUserId(): int
    {
        return (int)(self::user()['id'] ?? 0);
    }

    /**
     * Resolve gudang ID for the current request.
     *
     * - BOS: returns ?id_gudang query param if provided, otherwise 0 (= all warehouses).
     * - Admin/Checker: always returns their assigned id_gudang.
     *
     * Callers that receive 0 for BOS must query WITHOUT a gudang filter.
     */
    public static function resolveGudang(): int
    {
        $user = self::user();
        if ($user['role'] === 'bos') {
            return !empty($_GET['id_gudang']) ? (int)$_GET['id_gudang'] : 0;
        }
        return (int)($user['id_gudang'] ?? 0);
    }

    /**
     * Returns true when the caller should query across ALL warehouses
     * (i.e. BOS with no specific gudang filter).
     */
    public static function isAllGudang(): bool
    {
        $user = self::user();
        return $user['role'] === 'bos' && empty($_GET['id_gudang']);
    }
}
