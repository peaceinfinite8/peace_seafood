<?php

declare(strict_types=1);

namespace App\Models;

class TitipanPenjualan extends Model
{
    protected static string $table = 'titipan_penjualan';

    public function findByTitipan(int $titipanId): array
    {
        return $this->findAll(['titipan_id' => $titipanId], 'tanggal DESC');
    }
}
