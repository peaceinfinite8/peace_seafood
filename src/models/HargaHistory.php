<?php

declare(strict_types=1);

namespace App\Models;

class HargaHistory extends Model
{
    protected static string $table = 'harga_history';

    public function findByProduk(int $produkId): array
    {
        return $this->findAll(['produk_id' => $produkId], 'created_at DESC');
    }
}
