<?php

declare(strict_types=1);

namespace App\models;

use App\utils\Database;

class StokMasuk extends Model
{
    protected static string $table = 'stok_masuk';

    public static function getList(int $idGudang, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = "WHERE sm.id_gudang = ?";
        $params = [$idGudang];

        if (!empty($filters['status'])) {
            $where   .= " AND sm.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['id_produk'])) {
            $where   .= " AND sm.id_produk = ?";
            $params[] = $filters['id_produk'];
        }
        if (!empty($filters['date_from'])) {
            $where   .= " AND DATE(sm.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where   .= " AND DATE(sm.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $offset = ($page - 1) * $perPage;

        $rows = Database::fetchAll(
            "SELECT sm.*, p.nama as produk_nama, s.nama as supplier_nama,
                    u.name as created_by_name,
                    t.qty_actual, t.selisih, t.persen_susut
             FROM stok_masuk sm
             JOIN produk p ON p.id = sm.id_produk
             JOIN supplier s ON s.id = sm.id_supplier
             JOIN users u ON u.id = sm.created_by
             LEFT JOIN timbangan t ON t.id_stok_masuk = sm.id
             {$where}
             ORDER BY sm.created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        $total = (int)(Database::fetchOne(
            "SELECT COUNT(*) as cnt FROM stok_masuk sm {$where}",
            $params
        )['cnt'] ?? 0);

        return ['data' => $rows, 'total' => $total];
    }

    public static function getPending(int $idGudang): array
    {
        return Database::fetchAll(
            "SELECT sm.*, p.nama as produk_nama, s.nama as supplier_nama
             FROM stok_masuk sm
             JOIN produk p ON p.id = sm.id_produk
             JOIN supplier s ON s.id = sm.id_supplier
             WHERE sm.id_gudang = ? AND sm.status = 'pending'
             ORDER BY sm.created_at ASC",
            [$idGudang]
        );
    }

    public static function getTodayTotal(int $idGudang): array
    {
        return Database::fetchOne(
            "SELECT COUNT(*) as jumlah,
                    COALESCE(SUM(qty), 0) as total_qty
             FROM stok_masuk
             WHERE id_gudang = ? AND DATE(created_at) = CURDATE() AND status = 'confirmed'",
            [$idGudang]
        ) ?: ['jumlah' => 0, 'total_qty' => 0];
    }
}
