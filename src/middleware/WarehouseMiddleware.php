<?php

declare(strict_types=1);

namespace App\middleware;

use App\utils\Response;

/**
 * Warehouse Access Control Middleware
 */
class WarehouseMiddleware
{
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

        // BOZ has access to all warehouses
        if ($user['role'] === 'bos') {
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

        if ($user['role'] === 'bos') {
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

        if ($user['role'] === 'bos' && $requestedId > 0) {
            return ['clause' => "AND {$col} = ?", 'params' => [$requestedId]];
        }

        if ($user['role'] === 'bos') {
            return ['clause' => '', 'params' => []];
        }

        return ['clause' => "AND {$col} = ?", 'params' => [(int)$user['id_gudang']]];
    }
}
