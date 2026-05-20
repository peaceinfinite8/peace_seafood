<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\Database;
use App\Utils\Helper;

class StokService
{
    /**
     * Ambil inventory stok dengan filter gudang.
     * Jika $allGudang = true dan $idGudang = 0, ambil semua gudang (untuk BOS).
     */
    public function getInventory(int $idGudang, bool $allGudang = false): array
    {
        if ($allGudang && $idGudang === 0) {
            return Database::fetchAll(
                "SELECT p.*, p.gambar, p.nilai_stok AS stok_value, ji.nama as nama_jenis, g.nama as nama_gudang, 'kg' as satuan,
                        CASE WHEN p.stok_qty < p.stok_minimum THEN 1 ELSE 0 END as is_low_stock
                 FROM produk p
                 JOIN jenis_ikan ji ON p.id_jenis_ikan = ji.id
                 JOIN gudang g ON p.id_gudang = g.id
                 ORDER BY g.nama, ji.nama, p.nama",
                []
            );
        }

        return Database::fetchAll(
            "SELECT p.*, p.gambar, p.nilai_stok AS stok_value, ji.nama as nama_jenis, 'kg' as satuan,
                    CASE WHEN p.stok_qty < p.stok_minimum THEN 1 ELSE 0 END as is_low_stock
             FROM produk p
             JOIN jenis_ikan ji ON p.id_jenis_ikan = ji.id
             WHERE p.id_gudang = ?
             ORDER BY ji.nama, p.nama",
            [$idGudang]
        );
    }

    /**
     * Input stok masuk baru (status PENDING)
     */
    public function inputStokMasuk(array $data, int $idUser, int $idGudang): int
    {
        $total = (float) $data['qty'] * (float) $data['harga_beli'];

        return Database::insert('stok_masuk', [
            'id_gudang' => $idGudang,
            'id_supplier' => $data['id_supplier'],
            'id_produk' => $data['id_produk'],
            'qty' => $data['qty'],
            'harga_beli' => $data['harga_beli'],
            'total' => $total,
            'catatan' => $data['catatan'] ?? null,
            'status' => 'pending',
            'id_user' => $idUser,
        ]);
    }

    /**
     * Timbang stok masuk (ubah dari pending ke confirmed)
     */
    public function timbangStok(int $idStokMasuk, array $data, int $idUser): bool
    {
        $stokMasuk = Database::fetchOne(
            "SELECT * FROM stok_masuk WHERE id = ? AND status = 'pending'",
            [$idStokMasuk]
        );

        if (!$stokMasuk)
            return false;

        $qtyActual = (float) $data['qty_actual'];
        $susut = (float) $stokMasuk['qty'] - $qtyActual;

        // Simpan data timbangan
        Database::insert('timbangan', [
            'id_stok_masuk' => $idStokMasuk,
            'id_gudang' => $stokMasuk['id_gudang'],
            'qty_teoritis' => $stokMasuk['qty'],
            'qty_actual' => $qtyActual,
            'susut' => $susut,
            'alasan_susut' => $data['alasan_susut'] ?? null,
            'id_user' => $idUser,
        ]);

        // Update stok masuk ke CONFIRMED
        Database::update('stok_masuk', [
            'status' => 'confirmed',
            'qty_actual' => $qtyActual,
        ], 'id = ?', [$idStokMasuk]);

        // Update inventory produk
        $this->updateInventory($stokMasuk['id_produk'], $qtyActual, (float) $stokMasuk['harga_beli'], $stokMasuk['id_gudang']);

        // Check stok minimum alert
        $this->checkStokMinimumAlert($stokMasuk['id_produk'], $stokMasuk['id_gudang']);

        return true;
    }

    /**
     * Update inventory produk setelah stok masuk
     * Menggunakan weighted average untuk menghitung harga beli rata-rata
     * 
     * Formula:
     * - Nilai Stok Baru = Nilai Stok Lama + (Qty Tambah × Harga Beli Baru)
     * - Harga Rata-rata = Nilai Stok Baru / Qty Stok Baru
     * 
     * Contoh:
     * Stok lama: 100 kg @ Rp 50.000 = Rp 5.000.000
     * Stok masuk: 50 kg @ Rp 60.000 = Rp 3.000.000
     * Stok baru: 150 kg = Rp 8.000.000
     * Harga rata-rata: Rp 8.000.000 / 150 = Rp 53.333/kg
     */
    private function updateInventory(int $idProduk, float $qtyTambah, float $hargaBeli, int $idGudang): void
    {
        $produk = Database::fetchOne(
            "SELECT * FROM produk WHERE id = ? AND id_gudang = ?",
            [$idProduk, $idGudang]
        );

        if (!$produk)
            return;

        $stokLama = (float) $produk['stok_qty'];
        $nilaiStokLama = (float) $produk['nilai_stok'];
        
        // Tambah stok dan nilai (weighted average)
        $stokBaru = $stokLama + $qtyTambah;
        $nilaiTambahan = $qtyTambah * $hargaBeli;
        $nilaiStokBaru = $nilaiStokLama + $nilaiTambahan;
        
        // Hitung harga rata-rata (weighted average)
        $hargaRataRata = $stokBaru > 0 ? $nilaiStokBaru / $stokBaru : $hargaBeli;

        Database::update('produk', [
            'stok_qty' => $stokBaru,
            'nilai_stok' => $nilaiStokBaru,
            'harga_beli' => $hargaRataRata, // update ke harga rata-rata
        ], 'id = ?', [$idProduk]);
    }

    /**
     * Kurangi stok setelah penjualan
     * Menggunakan harga rata-rata yang sudah ada untuk menghitung nilai stok
     * 
     * Formula:
     * - Qty Keluar × Harga Rata-rata = Nilai yang Keluar
     * - Nilai Stok Baru = Nilai Stok Lama - Nilai yang Keluar
     * 
     * Contoh:
     * Stok: 150 kg @ Rp 53.333/kg = Rp 8.000.000
     * Keluar: 30 kg
     * Nilai keluar: 30 × Rp 53.333 = Rp 1.600.000
     * Stok baru: 120 kg = Rp 8.000.000 - Rp 1.600.000 = Rp 6.400.000
     * Harga rata-rata tetap: Rp 53.333/kg
     */
    public function kurangiStok(int $idProduk, float $qty, int $idGudang): bool
    {
        $produk = Database::fetchOne(
            "SELECT * FROM produk WHERE id = ? AND id_gudang = ?",
            [$idProduk, $idGudang]
        );

        if (!$produk)
            return false;
        if ((float) $produk['stok_qty'] < $qty)
            return false; // insufficient stock

        $stokLama = (float) $produk['stok_qty'];
        $nilaiStokLama = (float) $produk['nilai_stok'];
        $hargaRataRata = (float) $produk['harga_beli'];
        
        // Kurangi stok dan nilai (menggunakan harga rata-rata)
        $stokBaru = $stokLama - $qty;
        $nilaiKeluar = $qty * $hargaRataRata;
        $nilaiStokBaru = max(0, $nilaiStokLama - $nilaiKeluar);

        Database::update('produk', [
            'stok_qty' => $stokBaru,
            'nilai_stok' => $nilaiStokBaru,
            // harga_beli tetap (tidak berubah saat pengurangan)
        ], 'id = ?', [$idProduk]);

        $this->checkStokMinimumAlert($idProduk, $idGudang);

        return true;
    }

    /**
     * Cek stok minimum dan buat notifikasi jika perlu
     */
    private function checkStokMinimumAlert(int $idProduk, int $idGudang): void
    {
        $produk = Database::fetchOne(
            "SELECT p.*, ji.nama as nama_jenis FROM produk p 
             JOIN jenis_ikan ji ON p.id_jenis_ikan = ji.id
             WHERE p.id = ?",
            [$idProduk]
        );

        if (!$produk)
            return;

        if ((float) $produk['stok_qty'] < (float) $produk['stok_minimum']) {
            // Cek apakah notifikasi serupa sudah ada (hindari duplikasi)
            $existing = Database::fetchOne(
                "SELECT id FROM notifikasi WHERE id_gudang = ? AND tipe = 'stok_minimum' 
                 AND is_read = 0 AND pesan LIKE ?",
                [$idGudang, '%' . $produk['nama'] . '%']
            );

            if (!$existing) {
                Database::insert('notifikasi', [
                    'id_gudang' => $idGudang,
                    'tipe' => 'stok_minimum',
                    'pesan' => "Stok {$produk['nama']} menipis! Sisa: {$produk['stok_qty']} kg (min: {$produk['stok_minimum']} kg)",
                    'is_read' => 0,
                ]);
            }
        }
    }

    /**
     * Get pending timbangan list.
     * Jika $allGudang = true dan $idGudang = 0, ambil semua gudang (untuk BOS).
     */
    public function getPendingTimbangan(int $idGudang, bool $allGudang = false): array
    {
        if ($allGudang && $idGudang === 0) {
            return Database::fetchAll(
                "SELECT sm.*, p.nama as nama_produk, s.nama as nama_supplier,
                        ji.nama as nama_jenis, g.nama as nama_gudang
                 FROM stok_masuk sm
                 JOIN produk p ON sm.id_produk = p.id
                 JOIN jenis_ikan ji ON p.id_jenis_ikan = ji.id
                 JOIN supplier s ON sm.id_supplier = s.id
                 JOIN gudang g ON sm.id_gudang = g.id
                 WHERE sm.status = 'pending'
                 ORDER BY sm.created_at DESC",
                []
            );
        }

        return Database::fetchAll(
            "SELECT sm.*, p.nama as nama_produk, s.nama as nama_supplier,
                    ji.nama as nama_jenis
             FROM stok_masuk sm
             JOIN produk p ON sm.id_produk = p.id
             JOIN jenis_ikan ji ON p.id_jenis_ikan = ji.id
             JOIN supplier s ON sm.id_supplier = s.id
             WHERE sm.id_gudang = ? AND sm.status = 'pending'
             ORDER BY sm.created_at DESC",
            [$idGudang]
        );
    }

    /**
     * Get stok history.
     * Jika $allGudang = true dan $idGudang = 0, ambil semua gudang (untuk BOS).
     */
    public function getHistory(int $idGudang, array $filters = [], bool $allGudang = false): array
    {
        if ($allGudang && $idGudang === 0) {
            $where = "1=1";
            $params = [];
        } else {
            $where = "sm.id_gudang = ?";
            $params = [$idGudang];
        }

        if (!empty($filters['dari'])) {
            $where .= " AND DATE(sm.created_at) >= ?";
            $params[] = $filters['dari'];
        }
        if (!empty($filters['sampai'])) {
            $where .= " AND DATE(sm.created_at) <= ?";
            $params[] = $filters['sampai'];
        }
        if (!empty($filters['id_produk'])) {
            $where .= " AND sm.id_produk = ?";
            $params[] = $filters['id_produk'];
        }

        $gudangJoin = ($allGudang && $idGudang === 0)
            ? "JOIN gudang g ON sm.id_gudang = g.id"
            : "";
        $gudangCol = ($allGudang && $idGudang === 0)
            ? ", g.nama as nama_gudang"
            : "";

        return Database::fetchAll(
            "SELECT sm.*, p.nama as nama_produk, s.nama as nama_supplier,
                    ji.nama as nama_jenis, u.name as nama_user{$gudangCol}
             FROM stok_masuk sm
             JOIN produk p ON sm.id_produk = p.id
             JOIN jenis_ikan ji ON p.id_jenis_ikan = ji.id
             JOIN supplier s ON sm.id_supplier = s.id
             JOIN users u ON sm.id_user = u.id
             {$gudangJoin}
             WHERE {$where}
             ORDER BY sm.created_at DESC",
            $params
        );
    }
}
