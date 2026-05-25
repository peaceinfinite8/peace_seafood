<?php

declare(strict_types=1);

namespace App\Models;

class Titipan extends Model
{
    protected static string $table = 'titipan';

    public function findAktif(int $gudangId): array
    {
        $stmt = $this->db->prepare(
            "SELECT t.*, p.nama AS pembeli_nama, pr.nama AS produk_nama
             FROM `titipan` t
             LEFT JOIN `pembeli` p ON t.pembeli_id = p.id
             LEFT JOIN `produk` pr ON t.produk_id = pr.id
             WHERE t.gudang_id = ? AND t.status = 'aktif'
             ORDER BY t.tanggal_masuk DESC"
        );
        $stmt->execute([$gudangId]);
        return $stmt->fetchAll();
    }
}
