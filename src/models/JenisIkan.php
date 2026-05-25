<?php

declare(strict_types=1);

namespace App\Models;

class JenisIkan extends Model
{
    protected static string $table = 'jenis_ikan';

    public function findActive(string $orderBy = 'nama ASC'): array
    {
        return $this->findAll(['is_active' => 1], $orderBy);
    }
}
