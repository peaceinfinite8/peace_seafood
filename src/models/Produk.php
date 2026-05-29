<?php

declare(strict_types=1);

namespace App\Models;

use App\utils\Database;

class Produk extends Model
{
    protected static string $table = 'produk';

    public static function findWithJenis(int $idGudang = 0, bool $allGudang = false): array
    {
        $satuanSelect = Database::hasColumn('produk', 'satuan')
            ? ", COALESCE(p.satuan, 'kg') as satuan"
            : ", 'kg' as satuan";

        if ($allGudang && $idGudang === 0) {
            return Database::fetchAll(
                "SELECT p.*, j.nama as nama_jenis, g.nama as nama_gudang{$satuanSelect}
                 FROM produk p
                 JOIN jenis_ikan j ON j.id = p.id_jenis_ikan
                 JOIN gudang g ON g.id = p.id_gudang
                 WHERE p.is_active = 1
                 ORDER BY g.nama, j.nama, p.nama"
            );
        }

        return Database::fetchAll(
            "SELECT p.*, j.nama as nama_jenis{$satuanSelect}
             FROM produk p
             JOIN jenis_ikan j ON j.id = p.id_jenis_ikan
             WHERE p.id_gudang = ? AND p.is_active = 1
             ORDER BY j.nama, p.nama",
            [$idGudang]
        );
    }

    public static function findById(int $id): array|false
    {
        return Database::fetchOne("SELECT * FROM produk WHERE id = ?", [$id]);
    }

    public static function insert(array $data): int
    {
        $payload = [
            'id_jenis_ikan' => (int)($data['id_jenis_ikan'] ?? $data['jenis_ikan_id'] ?? 0),
            'id_gudang'     => (int)($data['id_gudang'] ?? 0),
            'nama'          => $data['nama'],
            'deskripsi'     => $data['deskripsi'] ?? null,
            'harga_beli'    => (float)($data['harga_beli'] ?? 0),
            'harga_jual'    => (float)($data['harga_jual'] ?? 0),
            'stok_qty'      => (float)($data['stok_qty'] ?? 0),
            'nilai_stok'    => (float)($data['nilai_stok'] ?? 0),
            'stok_minimum'  => (float)($data['stok_minimum'] ?? 0),
            'is_active'     => (int)($data['is_active'] ?? 1),
        ];

        if (Database::hasColumn('produk', 'satuan')) {
            $payload['satuan'] = $data['satuan'] ?? 'kg';
        }

        if (Database::hasColumn('produk', 'size')) {
            $payload['size'] = $data['size'] ?? null;
            $payload['grade'] = $data['grade'] ?? null;
            $payload['asal'] = $data['asal'] ?? null;
        }

        return Database::insert('produk', $payload);
    }

    public static function updateRecord(int $id, array $data): bool
    {
        $payload = [];

        $allowedFields = ['id_jenis_ikan', 'nama', 'deskripsi', 'harga_beli', 'harga_jual', 'stok_qty', 'nilai_stok', 'stok_minimum', 'is_active'];
        if (Database::hasColumn('produk', 'satuan')) {
            $allowedFields[] = 'satuan';
        }
        if (Database::hasColumn('produk', 'size')) {
            $allowedFields[] = 'size';
            $allowedFields[] = 'grade';
            $allowedFields[] = 'asal';
        }

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (empty($payload)) {
            return false;
        }

        if (Database::hasColumn('produk', 'satuan') && array_key_exists('satuan', $data) && $data['satuan'] === null) {
            $payload['satuan'] = 'kg';
        }

        return Database::update('produk', $payload, 'id = ?', [$id]);
    }

    public static function getByGudang(int $idGudang, bool $activeOnly = true): array
    {
        $where = $activeOnly ? "AND p.is_active = 1" : "";
        return Database::fetchAll(
            "SELECT p.*, j.nama as jenis_ikan_nama
             FROM produk p
             JOIN jenis_ikan j ON j.id = p.id_jenis_ikan
             WHERE p.id_gudang = ? {$where}
             ORDER BY p.nama",
            [$idGudang]
        );
    }

    public static function getWithStokStatus(int $idGudang): array
    {
        return Database::fetchAll(
            "SELECT p.*, j.nama as jenis_ikan_nama,
                    CASE
                        WHEN p.stok_qty <= 0 THEN 'habis'
                        WHEN p.stok_qty < p.stok_minimum THEN 'critical'
                        WHEN p.stok_qty < (p.stok_minimum * 1.5) THEN 'warning'
                        ELSE 'aman'
                    END as stok_status
             FROM produk p
             JOIN jenis_ikan j ON j.id = p.id_jenis_ikan
             WHERE p.id_gudang = ? AND p.is_active = 1
             ORDER BY p.stok_qty ASC",
            [$idGudang]
        );
    }

    public static function getBelowMinimum(int $idGudang): array
    {
        return Database::fetchAll(
            "SELECT p.*, j.nama as jenis_ikan_nama
             FROM produk p
             JOIN jenis_ikan j ON j.id = p.id_jenis_ikan
             WHERE p.id_gudang = ? AND p.is_active = 1
             AND p.stok_qty < p.stok_minimum
             ORDER BY p.stok_qty ASC",
            [$idGudang]
        );
    }

    public static function updateStok(int $id, float $delta, string $operation = 'add'): bool
    {
        if ($operation === 'add') {
            return Database::execute(
                "UPDATE produk SET
                    stok_qty = stok_qty + ?,
                    nilai_stok = (stok_qty + ?) * harga_beli,
                    updated_at = NOW()
                 WHERE id = ?",
                [$delta, $delta, $id]
            );
        } else {
            return Database::execute(
                "UPDATE produk SET
                    stok_qty = GREATEST(0, stok_qty - ?),
                    nilai_stok = GREATEST(0, stok_qty - ?) * harga_beli,
                    updated_at = NOW()
                 WHERE id = ?",
                [$delta, $delta, $id]
            );
        }
    }

    public static function updateHarga(int $id, ?int $hargaBeli, ?int $hargaJual): bool
    {
        $sets   = [];
        $params = [];

        if ($hargaBeli !== null) {
            $sets[]   = 'harga_beli = ?';
            $params[] = $hargaBeli;
        }
        if ($hargaJual !== null) {
            $sets[]   = 'harga_jual = ?';
            $params[] = $hargaJual;
        }

        if (empty($sets)) return false;

        $params[] = $id;
        return Database::execute(
            "UPDATE produk SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = ?",
            $params
        );
    }

    public static function getTotalNilaiStok(int $idGudang): int
    {
        $row = Database::fetchOne(
            "SELECT COALESCE(SUM(nilai_stok), 0) as total FROM produk WHERE id_gudang = ? AND is_active = 1",
            [$idGudang]
        );
        return (int)($row['total'] ?? 0);
    }

    public static function search(int $idGudang, string $keyword): array
    {
        return Database::fetchAll(
            "SELECT p.*, j.nama as jenis_ikan_nama
             FROM produk p
             JOIN jenis_ikan j ON j.id = p.id_jenis_ikan
             WHERE p.id_gudang = ? AND p.is_active = 1
             AND (p.nama LIKE ? OR j.nama LIKE ?)
             ORDER BY p.nama LIMIT 30",
            [$idGudang, "%{$keyword}%", "%{$keyword}%"]
        );
    }
}
