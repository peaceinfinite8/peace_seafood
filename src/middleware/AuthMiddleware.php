<?php

declare(strict_types=1);

namespace App\Middleware;

use App\utils\JWT;
use App\utils\Response;
use App\utils\Database;

/**
 * JWT Authentication Middleware
 */
class AuthMiddleware
{
    /**
     * Verify JWT and attach user to request context.
     * Returns decoded user payload or calls Response::unauthorized().
     */
    public static function handle(): array
    {
        $token = JWT::getFromRequest();

        if (!$token) {
            Response::unauthorized('Token tidak ditemukan. Silakan login terlebih dahulu.');
        }

        $payload = JWT::verify($token);

        if (!$payload) {
            Response::unauthorized('Token tidak valid atau sudah expired. Silakan login ulang.');
        }

        // Verify user still exists and is active
        $user = Database::fetchOne(
            "SELECT id, name, email, role, id_gudang, is_active FROM users WHERE id = ?",
            [$payload['id'] ?? $payload['user_id'] ?? 0]
        );

        if (!$user || !$user['is_active']) {
            Response::unauthorized('Akun tidak aktif atau tidak ditemukan.');
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
        if (in_array($user['role'], ['bos', 'super_admin'], true)) {
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
        return in_array($user['role'], ['bos', 'super_admin'], true) && empty($_GET['id_gudang']);
    }
}
