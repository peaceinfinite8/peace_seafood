<?php

declare(strict_types=1);

namespace App\middleware;

use App\utils\Response;

/**
 * Role-based Access Control Middleware
 */
class RoleMiddleware
{
    /**
     * Require one of the given roles.
     * @param string|array $roles
     */
    public static function require(string|array $roles): void
    {
        $user = AuthMiddleware::user();

        if (empty($user)) {
            Response::unauthorized();
        }

        $roles = (array) $roles;

        if (!in_array($user['role'], $roles, true)) {
            Response::forbidden(
                'Role ' . strtoupper($user['role']) . ' tidak memiliki akses ke resource ini.'
            );
        }
    }

    /**
     * Check if current user has a specific permission.
     */
    public static function can(string $permission): bool
    {
        $user = AuthMiddleware::user();
        if (empty($user)) return false;

        $permissions = require __DIR__ . '/../../config/roles.php';
        $rolePerms   = $permissions[$user['role']] ?? [];

        foreach ($rolePerms as $perm) {
            // Wildcard support: 'laporan.*' matches 'laporan.view'
            if ($perm === $permission) return true;
            if (str_ends_with($perm, '.*')) {
                $prefix = rtrim($perm, '.*');
                if (str_starts_with($permission, $prefix . '.')) return true;
            }
        }

        return false;
    }

    /**
     * Require a specific permission or abort with 403.
     */
    public static function requirePermission(string $permission): void
    {
        if (!self::can($permission)) {
            Response::forbidden("Anda tidak memiliki permission: {$permission}");
        }
    }

    public static function isBos(): bool
    {
        return (AuthMiddleware::user()['role'] ?? '') === 'bos';
    }

    public static function isAdmin(): bool
    {
        return (AuthMiddleware::user()['role'] ?? '') === 'admin';
    }

    public static function isChecker(): bool
    {
        return (AuthMiddleware::user()['role'] ?? '') === 'checker';
    }
}
