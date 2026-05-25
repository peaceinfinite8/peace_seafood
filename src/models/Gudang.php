<?php

declare(strict_types=1);

namespace App\Models;

use App\Utils\Database;

class Gudang extends Model
{
    protected static string $table = 'gudang';

    public static function getByBos(int $idBos): array
    {
        return Database::fetchAll(
            "SELECT * FROM gudang WHERE id_bos = ? AND is_active = 1 ORDER BY nama",
            [$idBos]
        );
    }

    public static function getActive(): array
    {
        return Database::fetchAll(
            "SELECT * FROM gudang WHERE is_active = 1 ORDER BY nama"
        );
    }

    public static function findWithBos(int $id): array|false
    {
        return Database::fetchOne(
            "SELECT g.*, u.name as bos_name, u.email as bos_email
             FROM gudang g
             JOIN users u ON u.id = g.id_bos
             WHERE g.id = ?",
            [$id]
        );
    }
}
