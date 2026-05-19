<?php

declare(strict_types=1);

namespace App\Models;

class Retur extends Model
{
    protected string $table = 'retur';

    public function findByGudang(int $gudangId, int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, p.nama AS pembeli_nama
             FROM `retur` r
             LEFT JOIN `pembeli` p ON r.pembeli_id = p.id
             WHERE r.gudang_id = ?
             ORDER BY r.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$gudangId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public function generateNomor(): string
    {
        return 'RTR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}
