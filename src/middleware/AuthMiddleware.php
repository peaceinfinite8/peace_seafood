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

        // Verify user still exists, is active, and fetch SaaS statuses
        $user = Database::fetchOne(
            "SELECT id, name, email, role, id_gudang, is_active, is_first_login, registration_status FROM users WHERE id = ?",
            [$payload['id'] ?? $payload['user_id'] ?? 0]
        );

        if (!$user || !$user['is_active']) {
            Response::unauthorized('Akun tidak aktif atau tidak ditemukan.');
        }

        // Bypass Protection for First Login Change Password
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestUri = preg_replace('#^/peace_seafood#', '', $requestUri);
        $requestUri = preg_replace('#^/public#', '', $requestUri);
        $requestUri = preg_replace('#^/api#', '', $requestUri);
        $requestUri = rtrim($requestUri, '/') ?: '/';

        $allowedUris = ['/auth/change-password', '/auth/logout', '/auth/profile'];
        if ((int) $user['is_first_login'] === 1 && !in_array($requestUri, $allowedUris, true)) {
            Response::error('Silakan ganti password default Anda terlebih dahulu.', 412);
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
        return (int) (self::user()['id'] ?? 0);
    }

    /**
     * Resolve gudang ID for the current request.
     *
     * - saas_owner / super_admin / bos: returns ?id_gudang query param if provided, otherwise 0 (= all warehouses).
     * - Admin/Checker: always returns their assigned id_gudang.
     *
     * Performs strict IDOR checking and Trial/Subscription verification on resolved warehouse.
     */
    public static function resolveGudang(): int
    {
        $user = self::user();
        $gudangId = 0;

        if (in_array($user['role'], ['saas_owner', 'super_admin', 'bos'], true)) {
            $gudangId = !empty($_GET['id_gudang']) ? (int) $_GET['id_gudang'] : 0;
        } else {
            $gudangId = (int) ($user['id_gudang'] ?? 0);
        }

        // Strict IDOR and Trial Expiration checks
        if ($gudangId > 0) {
            $gudang = Database::fetchOne(
                "SELECT id, id_bos, subscription_until, status_langganan FROM gudang WHERE id = ?",
                [$gudangId]
            );

            if (!$gudang) {
                Response::notFound('Gudang tidak ditemukan.');
            }

            // IDOR Check: Bos can only view/operate on their own warehouses
            if ($user['role'] === 'bos' && (int) $gudang['id_bos'] !== (int) $user['id']) {
                Response::forbidden('Anda tidak memiliki akses ke data gudang ini.');
            }

            // Subscription/Trial Check (exempt saas_owner and super_admin from billing locks)
            if (!in_array($user['role'], ['saas_owner', 'super_admin'], true)) {
                $expiry = $gudang['subscription_until'];
                $isExpired = $expiry && (strtotime($expiry) < time());
                $isSuspended = $gudang['status_langganan'] === 'suspend';

                if ($isExpired || $isSuspended) {
                    Response::error('Masa aktif uji coba (trial) atau langganan gudang ini telah berakhir.', 402);
                }
            }
        }

        return $gudangId;
    }

    /**
     * Returns true when the caller should query across ALL warehouses
     * (i.e. BOS with no specific gudang filter).
     */
    public static function isAllGudang(): bool
    {
        $user = self::user();
        return in_array($user['role'], ['bos', 'super_admin', 'saas_owner'], true) && empty($_GET['id_gudang']);
    }
}
