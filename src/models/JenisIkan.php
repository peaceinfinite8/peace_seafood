<?php

declare(strict_types=1);

namespace App\Models;

use App\Utils\Database;

class JenisIkan extends Model
{
    protected string $table = 'jenis_ikan';

    public function findActive(): array
    {
        return Database::fetchAll(
            "SELECT *, (SELECT COUNT(*) FROM produk p WHERE p.id_jenis_ikan = jenis_ikan.id AND p.is_active = 1) AS jumlah_produk
             FROM `jenis_ikan`
             WHERE `is_active` = 1
             ORDER BY `nama` ASC"
        );
    }
}
