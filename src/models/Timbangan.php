<?php

declare(strict_types=1);

namespace App\models;

use App\utils\Database;

class Timbangan extends Model
{
    protected static string $table = 'timbangan';

    public static function getByStokMasuk(int $idStokMasuk): array|false
    {
        return Database::fetchOne(
            "SELECT t.*, u.name as created_by_name
             FROM timbangan t
             JOIN users u ON u.id = t.created_by
             WHERE t.id_stok_masuk = ?",
            [$idStokMasuk]
        );
    }

    public static function getSusutToday(int $idGudang): array
    {
        return Database::fetchOne(
            "SELECT COALESCE(SUM(t.selisih), 0) as total_susut,
                    COUNT(*) as jumlah_timbang
             FROM timbangan t
             JOIN stok_masuk sm ON sm.id = t.id_stok_masuk
             WHERE sm.id_gudang = ? AND DATE(t.created_at) = CURDATE()",
            [$idGudang]
        ) ?: ['total_susut' => 0, 'jumlah_timbang' => 0];
    }

    public static function getSusutTrend(int $idGudang, int $days = 7): array
    {
        return Database::fetchAll(
            "SELECT DATE(t.created_at) as tanggal,
                    COALESCE(SUM(t.selisih), 0) as total_susut,
                    COALESCE(AVG(t.persen_susut), 0) as avg_persen
             FROM timbangan t
             JOIN stok_masuk sm ON sm.id = t.id_stok_masuk
             WHERE sm.id_gudang = ?
             AND t.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(t.created_at)
             ORDER BY tanggal ASC",
            [$idGudang, $days]
        );
    }
}
