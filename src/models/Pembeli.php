<?php

declare(strict_types=1);

namespace App\Models;

class Pembeli extends Model
{
    protected static string $table = 'pembeli';

    public function findActive(string $orderBy = 'nama ASC'): array
    {
        return $this->findAll(['is_active' => 1], $orderBy);
    }

    public function search(string $keyword): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `pembeli` WHERE `nama` LIKE ? AND `is_active` = 1 ORDER BY `nama` ASC"
        );
        $stmt->execute(["%{$keyword}%"]);
        return $stmt->fetchAll();
    }
}
