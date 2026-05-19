<?php

declare(strict_types=1);

namespace App\Models;

use App\Utils\Database;

class Pembeli extends Model
{
    protected string $table = 'pembeli';

    public function findActive(): array
    {
        return Database::fetchAll("SELECT *, telpon AS telepon FROM `pembeli` WHERE `is_active` = 1 ORDER BY `nama` ASC");
    }

    public function search(string $keyword): array
    {
        return Database::fetchAll(
            "SELECT *, telpon AS telepon FROM `pembeli` WHERE `nama` LIKE ? AND `is_active` = 1 ORDER BY `nama` ASC",
            ["%{$keyword}%"]
        );
    }
}
