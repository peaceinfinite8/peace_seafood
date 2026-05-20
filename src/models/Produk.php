<?php

declare(strict_types=1);

namespace App\models;

use App\utils\Database;

class Produk extends Model
{
    protected static string $table = 'produk';

    public function findWithJenis(): array
    {
        return Database::fetchAll(
            "SELECT p.*, p.gambar, j.nama AS nama_jenis
             FROM produk p
             JOIN jenis_ikan j ON j.id = p.id_jenis_ikan
             WHERE p.is_active = 1
             ORDER BY p.nama"
        );
    }

    public static function getByGudang(int $idGudang, bool $activeOnly = true): array
    {
        $where = $activeOnly ? "AND p.is_active = 1" : "";
        return Database::fetchAll(
            "SELECT p.*, p.gambar, j.nama as jenis_ikan_nama
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
            "SELECT p.*, p.gambar, j.nama as jenis_ikan_nama,
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
            "SELECT p.*, p.gambar, j.nama as jenis_ikan_nama
             FROM produk p
             JOIN jenis_ikan j ON j.id = p.id_jenis_ikan
             WHERE p.id_gudang = ? AND p.is_active = 1
             AND p.stok_qty < p.stok_minimum
             ORDER BY p.stok_qty ASC",
            [$idGudang]
        );
    }

    /**
     * Update stok produk (add atau subtract)
     * 
     * Formula untuk ADD:
     * - Qty Baru = Qty Lama + Delta
     * - Nilai Baru = Nilai Lama + (Delta × Harga Beli)
     * 
     * Formula untuk SUBTRACT:
     * - Qty Baru = max(0, Qty Lama - Delta)
     * - Nilai Keluar = Delta × Harga Beli Rata-rata
     * - Nilai Baru = max(0, Nilai Lama - Nilai Keluar)
     * 
     * Contoh ADD:
     * Stok: 100 kg @ Rp 50.000 = Rp 5.000.000
     * Tambah: 50 kg @ Rp 60.000
     * Stok baru: 150 kg = Rp 5.000.000 + Rp 3.000.000 = Rp 8.000.000
     * 
     * Contoh SUBTRACT:
     * Stok: 150 kg @ Rp 53.333 = Rp 8.000.000
     * Kurang: 30 kg
     * Nilai keluar: 30 × Rp 53.333 = Rp 1.600.000
     * Stok baru: 120 kg = Rp 8.000.000 - Rp 1.600.000 = Rp 6.400.000
     */
    public static function updateStok(int $id, float $delta, string $operation = 'add'): bool
    {
        if ($operation === 'add') {
            // Ambil data produk untuk hitung weighted average
            $produk = Database::fetchOne("SELECT * FROM produk WHERE id = ?", [$id]);
            if (!$produk) return false;
            
            $stokLama = (float) $produk['stok_qty'];
            $nilaiLama = (float) $produk['nilai_stok'];
            $hargaBeli = (float) $produk['harga_beli'];
            
            $stokBaru = $stokLama + $delta;
            $nilaiTambahan = $delta * $hargaBeli;
            $nilaiBaru = $nilaiLama + $nilaiTambahan;
            
            return Database::execute(
                "UPDATE produk SET
                    stok_qty = ?,
                    nilai_stok = ?,
                    updated_at = NOW()
                 WHERE id = ?",
                [$stokBaru, $nilaiBaru, $id]
            );
        } else {
            // Subtract: kurangi dengan harga rata-rata
            $produk = Database::fetchOne("SELECT * FROM produk WHERE id = ?", [$id]);
            if (!$produk) return false;
            
            $stokLama = (float) $produk['stok_qty'];
            $nilaiLama = (float) $produk['nilai_stok'];
            $hargaRataRata = (float) $produk['harga_beli'];
            
            $stokBaru = max(0, $stokLama - $delta);
            $nilaiKeluar = $delta * $hargaRataRata;
            $nilaiBaru = max(0, $nilaiLama - $nilaiKeluar);
            
            return Database::execute(
                "UPDATE produk SET
                    stok_qty = ?,
                    nilai_stok = ?,
                    updated_at = NOW()
                 WHERE id = ?",
                [$stokBaru, $nilaiBaru, $id]
            );
        }
    }

    public static function updateHarga(int $id, ?int $hargaBeli, ?int $hargaJual): bool
    {
        $sets = [];
        $params = [];

        if ($hargaBeli !== null) {
            $sets[] = 'harga_beli = ?';
            $params[] = $hargaBeli;
        }
        if ($hargaJual !== null) {
            $sets[] = 'harga_jual = ?';
            $params[] = $hargaJual;
        }

        if (empty($sets))
            return false;

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
        return (int) ($row['total'] ?? 0);
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
