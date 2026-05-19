<?php

declare(strict_types=1);

namespace App\models;

use App\utils\Database;

class Supplier extends Model
{
    protected static string $table = 'supplier';

    public static function findActive(): array
    {
        return Database::fetchAll(
            "SELECT *, telpon AS telepon FROM supplier WHERE is_active = 1 ORDER BY nama"
        );
    }

    public static function getByGudang(int $idGudang, bool $activeOnly = true): array
    {
        $where = $activeOnly ? "AND is_active = 1" : "";
        return Database::fetchAll(
            "SELECT * FROM supplier WHERE id_gudang = ? {$where} ORDER BY nama",
            [$idGudang]
        );
    }

    public static function search(int $idGudang, string $keyword): array
    {
        return Database::fetchAll(
            "SELECT * FROM supplier
             WHERE id_gudang = ? AND is_active = 1
             AND (nama LIKE ? OR telpon LIKE ?)
             ORDER BY nama LIMIT 20",
            [$idGudang, "%{$keyword}%", "%{$keyword}%"]
        );
    }

    public static function getTotalHutang(int $idSupplier): int
    {
        $row = Database::fetchOne(
            "SELECT COALESCE(SUM(sisa_hutang), 0) as total
             FROM hutang_piutang
             WHERE id_supplier = ? AND jenis = 'hutang' AND status != 'lunas'",
            [$idSupplier]
        );
        return (int) ($row['total'] ?? 0);
    }
}
