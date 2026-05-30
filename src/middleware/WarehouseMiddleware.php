<?php

declare(strict_types=1);

namespace App\Middleware;

use App\utils\Response;

/**
 * Warehouse Access Control Middleware
 */
class WarehouseMiddleware
{
    /**
     * Instance method called from controller constructors.
     * Ensures the user is authenticated. BOS passes through freely.
     * Admin/Checker must have a valid id_gudang assigned.
     */
    public function handle(): void
    {
        $user = AuthMiddleware::user();

        if (empty($user)) {
            Response::unauthorized();
        }

        // BOS and Super Admin can access all warehouses — no restriction
        if (in_array($user['role'], ['bos', 'super_admin'], true)) {
            return;
        }

        // Admin/Checker must have an assigned warehouse
        if (empty($user['id_gudang'])) {
            Response::forbidden('Akun Anda belum ditetapkan ke gudang manapun.');
        }
    }

    /**
     * Check if current user can access the given warehouse.
     * BOZ can access all warehouses.
     * Admin/Checker can only access their assigned warehouse.
     */
    public static function check(int $warehouseId): void
    {
        $user = AuthMiddleware::user();

        if (empty($user)) {
            Response::unauthorized();
        }

        // BOS and Super Admin have access to all warehouses
        if (in_array($user['role'], ['bos', 'super_admin'], true)) {
            return;
        }

        // Admin/Checker must be assigned to this warehouse
        if ((int)$user['id_gudang'] !== $warehouseId) {
            Response::forbidden(
                'Anda tidak memiliki akses ke gudang ini.'
            );
        }
    }

    /**
     * Get the warehouse ID for the current user.
     * For BOS: returns ?id_gudang query param if provided, otherwise 0 (= all warehouses).
     * For Admin/Checker: returns their assigned warehouse ID.
     */
    public static function getGudangId(): int
    {
        $user = AuthMiddleware::user();

        if (in_array($user['role'], ['bos', 'super_admin'], true)) {
            return !empty($_GET['id_gudang']) ? (int)$_GET['id_gudang'] : 0;
        }

        return (int)$user['id_gudang'];
    }

    /**
     * Build a WHERE clause fragment for warehouse filtering.
     * Returns ['clause' => 'AND id_gudang = ?', 'params' => [1]]
     */
    public static function buildWhereClause(string $alias = '', int $requestedId = 0): array
    {
        $user    = AuthMiddleware::user();
        $col     = $alias ? "{$alias}.id_gudang" : 'id_gudang';

        if (in_array($user['role'], ['bos', 'super_admin'], true) && $requestedId > 0) {
            return ['clause' => "AND {$col} = ?", 'params' => [$requestedId]];
        }

        if (in_array($user['role'], ['bos', 'super_admin'], true)) {
            return ['clause' => '', 'params' => []];
        }

        return ['clause' => "AND {$col} = ?", 'params' => [(int)$user['id_gudang']]];
    }
}
