<?php

declare(strict_types=1);

namespace App\Models;

class BiayaOperasional extends Model
{
    protected string $table = 'biaya_operasional';

    public function findByGudangAndPeriod(int $gudangId, string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `biaya_operasional`
             WHERE gudang_id = ? AND tanggal BETWEEN ? AND ?
             ORDER BY tanggal DESC"
        );
        $stmt->execute([$gudangId, $from, $to]);
        return $stmt->fetchAll();
    }

    public function getTotalByPeriod(int $gudangId, string $from, string $to): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(jumlah), 0) FROM `biaya_operasional`
             WHERE gudang_id = ? AND tanggal BETWEEN ? AND ?"
        );
        $stmt->execute([$gudangId, $from, $to]);
        return (float) $stmt->fetchColumn();
    }
}
