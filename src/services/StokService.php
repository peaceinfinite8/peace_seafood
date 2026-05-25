<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\Database;
use App\Utils\Helper;
use App\Utils\ActivityLogHelper;
use App\Services\NotificationService;

class StokService
{
    /**
     * Ambil inventory stok dengan filter gudang.
     * Jika $allGudang = true dan $idGudang = 0, ambil semua gudang (untuk BOS).
     */
    public function getInventory(int $idGudang, bool $allGudang = false): array
    {
        $satuanSelect = Database::hasColumn('produk', 'satuan')
            ? ", COALESCE(p.satuan, 'kg') as satuan"
            : ", 'kg' as satuan";

        // Normalize column name: alias DB `nilai_stok` as `stok_value` for API/UI consistency.
        // Use LEFT JOINs so legacy rows with missing relations do not make BOS dashboards look empty.
        if ($allGudang && $idGudang === 0) {
            return Database::fetchAll(
                "SELECT p.*, COALESCE(p.nilai_stok, 0) as stok_value{$satuanSelect},
                        COALESCE(ji.nama, 'Tanpa Jenis') as nama_jenis,
                        COALESCE(g.nama, 'Tanpa Gudang') as nama_gudang,
                        CASE WHEN COALESCE(p.stok_qty, 0) < COALESCE(p.stok_minimum, 0) THEN 1 ELSE 0 END as is_low_stock
                 FROM produk p
                 LEFT JOIN jenis_ikan ji ON p.id_jenis_ikan = ji.id
                 LEFT JOIN gudang g ON p.id_gudang = g.id
                 ORDER BY COALESCE(g.nama, 'ZZZ'), COALESCE(ji.nama, 'ZZZ'), p.nama",
                []
            );
        }

        return Database::fetchAll(
            "SELECT p.*, COALESCE(p.nilai_stok, 0) as stok_value{$satuanSelect},
                    COALESCE(ji.nama, 'Tanpa Jenis') as nama_jenis,
                    CASE WHEN COALESCE(p.stok_qty, 0) < COALESCE(p.stok_minimum, 0) THEN 1 ELSE 0 END as is_low_stock
             FROM produk p
             LEFT JOIN jenis_ikan ji ON p.id_jenis_ikan = ji.id
             WHERE p.id_gudang = ?
             ORDER BY COALESCE(ji.nama, 'ZZZ'), p.nama",
            [$idGudang]
        );
    }

    /**
     * Input stok masuk baru (status PENDING)
     */
    public function inputStokMasuk(array $data, int $idUser, int $idGudang): int
    {
        // Safe warehouse fallback
        if ($idGudang <= 0) {
            $firstGudang = Database::fetchOne("SELECT id FROM gudang WHERE is_active = 1 LIMIT 1");
            if ($firstGudang) {
                $idGudang = (int)$firstGudang['id'];
            } else {
                \App\Utils\Response::error('Gudang tidak ditemukan untuk mengaitkan data ini.', 422);
            }
        }

        // Process hybrid supplier input
        $idSupplier = $data['id_supplier'] ?? null;
        if (empty($idSupplier)) {
            \App\Utils\Response::error('Supplier tidak boleh kosong', 422);
        }

        if (!is_numeric($idSupplier) || (int)$idSupplier <= 0) {
            $namaSupplier = trim((string)$idSupplier);
            if (empty($namaSupplier)) {
                \App\Utils\Response::error('Supplier tidak boleh kosong', 422);
            }

            // Check if supplier with this name already exists in this warehouse (case-insensitive)
            $existing = Database::fetchOne(
                "SELECT id FROM supplier WHERE id_gudang = ? AND LOWER(nama) = ? AND is_active = 1",
                [$idGudang, strtolower($namaSupplier)]
            );

            if ($existing) {
                $idSupplier = (int)$existing['id'];
            } else {
                // Auto-create new supplier
                $idSupplier = Database::insert('supplier', [
                    'id_gudang' => $idGudang,
                    'nama'      => $namaSupplier,
                    'is_active' => 1
                ]);

                // Log in Audit Trail
                ActivityLogHelper::log('INSERT', 'supplier', $idSupplier, null, [
                    'id_gudang' => $idGudang,
                    'nama'      => $namaSupplier,
                    'is_active' => 1
                ]);
            }
        } else {
            $idSupplier = (int)$idSupplier;
        }

        $qty = (float)($data['berat_nota_supplier'] ?? $data['qty'] ?? 0);
        $hargaBeli = (float)$data['harga_beli'];
        $total = $qty * $hargaBeli;

        $id = Database::insert('stok_masuk', [
            'id_gudang'   => $idGudang,
            'id_supplier' => $idSupplier,
            'id_produk'   => $data['id_produk'],
            'qty'         => $qty,
            'harga_beli'  => $hargaBeli,
            'catatan'     => $data['catatan'] ?? null,
            'status'      => 'pending',
            'created_by'  => $idUser,
        ]);

        // Audit Trail Log
        ActivityLogHelper::log('INSERT', 'stok_masuk', $id, null, [
            'id_gudang'   => $idGudang,
            'id_supplier' => $data['id_supplier'],
            'id_produk'   => $data['id_produk'],
            'qty'         => $qty,
            'harga_beli'  => $hargaBeli,
            'status'      => 'pending',
        ]);

        // Buat notifikasi timbangan pending untuk Admin
        $produk = Database::fetchOne("SELECT nama FROM produk WHERE id = ?", [$data['id_produk']]);
        $checker = Database::fetchOne("SELECT name FROM users WHERE id = ?", [$idUser]);
        $namaProduk = $produk ? $produk['nama'] : 'Produk';
        $namaChecker = $checker ? $checker['name'] : 'Checker';

        $notifService = new NotificationService();
        $pesan = "Checker {$namaChecker} menginput stok masuk pending untuk {$namaProduk} ({$qty} kg). Silakan lakukan penimbangan dan konfirmasi.";
        $notifService->sendNotification(['admin'], $idGudang, 'timbangan_pending', 'Timbangan Pending Baru', $pesan, $id, 'stok_masuk');

        return $id;
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

        if (!$stokMasuk) return false;

        $qtyTeoritis = (float)$stokMasuk['qty']; // ini berat_nota_supplier
        $qtyActual  = (float)($data['berat_riil_gudang'] ?? $data['qty_actual'] ?? 0);
        $susut      = $qtyTeoritis - $qtyActual;

        // Simpan data timbangan
        $idTimbangan = Database::insert('timbangan', [
            'id_stok_masuk'  => $idStokMasuk,
            'id_produk'      => $stokMasuk['id_produk'],
            'qty_teoritis'   => $qtyTeoritis,
            'qty_actual'     => $qtyActual,
            'alasan_susut'   => $data['alasan_susut'] ?? null,
            'created_by'     => $idUser,
        ]);

        // Audit Trail Log untuk timbangan
        ActivityLogHelper::log('INSERT', 'timbangan', $idTimbangan, null, [
            'id_stok_masuk'  => $idStokMasuk,
            'qty_teoritis'   => $qtyTeoritis,
            'qty_actual'     => $qtyActual,
        ]);

        // Auto-Jurnal Kerugian Penyusutan ke Biaya Operasional jika terjadi penyusutan
        $persenSusut = $qtyTeoritis > 0 ? round(($susut / $qtyTeoritis) * 100, 2) : 0;
        if ($susut > 0) {
            $hargaBeli = (float)$stokMasuk['harga_beli'];
            $kerugian = $susut * $hargaBeli;

            $idBiaya = Database::insert('biaya_operasional', [
                'id_gudang'   => $stokMasuk['id_gudang'],
                'kategori'    => 'Penyusutan Stok',
                'deskripsi'   => "Kerugian penyusutan air/es stok masuk #{$idStokMasuk} ({$susut} kg, {$persenSusut}%) untuk produk ID {$stokMasuk['id_produk']}",
                'nominal'     => (int)$kerugian,
                'tanggal'     => date('Y-m-d'),
                'created_by'  => $idUser,
            ]);

            // Audit Trail Log untuk biaya
            ActivityLogHelper::log('INSERT', 'biaya_operasional', $idBiaya, null, [
                'id_gudang'  => $stokMasuk['id_gudang'],
                'kategori'   => 'Penyusutan Stok',
                'nominal'    => (int)$kerugian,
            ]);
        }

        // Update stok masuk ke CONFIRMED
        Database::update('stok_masuk', [
            'status'     => 'confirmed',
        ], 'id = ?', [$idStokMasuk]);

        ActivityLogHelper::log('UPDATE', 'stok_masuk', $idStokMasuk, $stokMasuk, [
            'id'     => $idStokMasuk,
            'status' => 'confirmed'
        ]);

        // Update inventory produk
        $this->updateInventory($stokMasuk['id_produk'], $qtyActual, (float)$stokMasuk['harga_beli'], $stokMasuk['id_gudang']);

        // Buat notifikasi timbangan selesai untuk Bos (dan Admin)
        $produk = Database::fetchOne("SELECT nama FROM produk WHERE id = ?", [$stokMasuk['id_produk']]);
        $admin = Database::fetchOne("SELECT name FROM users WHERE id = ?", [$idUser]);
        $supplier = Database::fetchOne("SELECT nama FROM supplier WHERE id = ?", [$stokMasuk['id_supplier']]);
        $namaProduk = $produk ? $produk['nama'] : 'Produk';
        $namaAdmin = $admin ? $admin['name'] : 'Admin';
        $namaSupplier = $supplier ? $supplier['nama'] : 'Supplier';

        if ($susut > 0) {
            $pesan = "Admin {$namaAdmin} selesai menimbang {$namaProduk} dari {$namaSupplier}. Terjadi penyusutan sebesar {$susut} kg ({$persenSusut}%). Kerugian dicatat otomatis.";
        } else {
            $pesan = "Admin {$namaAdmin} selesai menimbang {$namaProduk} dari {$namaSupplier} dengan berat penuh ({$qtyActual} kg).";
        }

        $notifService = new NotificationService();
        $notifService->sendNotification(['bos', 'admin'], $stokMasuk['id_gudang'], 'timbangan_selesai', 'Timbangan Selesai', $pesan, $idStokMasuk, 'stok_masuk');

        // Check stok minimum alert
        $this->checkStokMinimumAlert($stokMasuk['id_produk'], $stokMasuk['id_gudang']);

        return true;
    }

    /**
     * Update inventory produk setelah stok masuk
     */
    private function updateInventory(int $idProduk, float $qtyTambah, float $hargaBeli, int $idGudang): void
    {
        $produk = Database::fetchOne(
            "SELECT * FROM produk WHERE id = ? AND id_gudang = ?",
            [$idProduk, $idGudang]
        );

        if (!$produk) return;

        $stokBaru    = (float)$produk['stok_qty'] + $qtyTambah;
        $nilaiStok   = $stokBaru * $hargaBeli;

        Database::update('produk', [
            'stok_qty'   => $stokBaru,
            'nilai_stok' => $nilaiStok,
            'harga_beli' => $hargaBeli, // update harga beli terbaru
        ], 'id = ?', [$idProduk]);
    }

    /**
     * Kurangi stok setelah penjualan
     */
    public function kurangiStok(int $idProduk, float $qty, int $idGudang): bool
    {
        $produk = Database::fetchOne(
            "SELECT * FROM produk WHERE id = ? AND id_gudang = ?",
            [$idProduk, $idGudang]
        );

        if (!$produk) return false;
        if ((float)$produk['stok_qty'] < $qty) return false; // insufficient stock

        $stokBaru  = (float)$produk['stok_qty'] - $qty;
        $nilaiStok = $stokBaru * (float)$produk['harga_beli'];

        Database::update('produk', [
            'stok_qty'    => $stokBaru,
            'nilai_stok'  => $nilaiStok,
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

        if (!$produk) return;

        if ((float)$produk['stok_qty'] < (float)$produk['stok_minimum']) {
            // Cek apakah notifikasi serupa sudah ada (hindari duplikasi)
            $existing = Database::fetchOne(
                "SELECT id FROM notifikasi WHERE tipe = 'stok_minimum' 
                 AND is_read = 0 AND pesan LIKE ?",
                ['%' . $produk['nama'] . '%']
            );

            if (!$existing) {
                $notifService = new NotificationService();
                $pesan = "Stok {$produk['nama']} menipis! Sisa: {$produk['stok_qty']} kg (min: {$produk['stok_minimum']} kg)";

                // Kirim ke Bos dan Admin gudang terkait
                $notifService->sendNotification(
                    ['bos', 'admin'],
                    $idGudang,
                    'stok_minimum',
                    'Stok Menipis',
                    $pesan,
                    $idProduk,
                    'produk'
                );
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
                "SELECT sm.*, p.nama as nama_produk, s.nama as nama_supplier, u.name as nama_user,
                        COALESCE(ji.nama, 'Tanpa Jenis') as nama_jenis,
                        COALESCE(g.nama, 'Tanpa Gudang') as nama_gudang
                 FROM stok_masuk sm
                 LEFT JOIN produk p ON sm.id_produk = p.id
                 LEFT JOIN jenis_ikan ji ON p.id_jenis_ikan = ji.id
                 LEFT JOIN supplier s ON sm.id_supplier = s.id
                 LEFT JOIN users u ON sm.created_by = u.id
                 LEFT JOIN gudang g ON sm.id_gudang = g.id
                 WHERE sm.status = 'pending'
                 ORDER BY sm.created_at DESC",
                []
            );
        }

        return Database::fetchAll(
            "SELECT sm.*, p.nama as nama_produk, s.nama as nama_supplier, u.name as nama_user,
                    COALESCE(ji.nama, 'Tanpa Jenis') as nama_jenis
             FROM stok_masuk sm
             LEFT JOIN produk p ON sm.id_produk = p.id
             LEFT JOIN jenis_ikan ji ON p.id_jenis_ikan = ji.id
             LEFT JOIN supplier s ON sm.id_supplier = s.id
             LEFT JOIN users u ON sm.created_by = u.id
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
            $where  = "1=1";
            $params = [];
        } else {
            $where  = "sm.id_gudang = ?";
            $params = [$idGudang];
        }

        if (!empty($filters['dari'])) {
            $where    .= " AND DATE(sm.created_at) >= ?";
            $params[]  = $filters['dari'];
        }
        if (!empty($filters['sampai'])) {
            $where    .= " AND DATE(sm.created_at) <= ?";
            $params[]  = $filters['sampai'];
        }
        if (!empty($filters['id_produk'])) {
            $where    .= " AND sm.id_produk = ?";
            $params[]  = $filters['id_produk'];
        }

        $gudangJoin = ($allGudang && $idGudang === 0)
            ? "JOIN gudang g ON sm.id_gudang = g.id"
            : "";
        $gudangCol  = ($allGudang && $idGudang === 0)
            ? ", g.nama as nama_gudang"
            : "";

        return Database::fetchAll(
            "SELECT sm.*, p.nama as nama_produk, s.nama as nama_supplier,
                    ji.nama as nama_jenis, u.name as nama_user{$gudangCol}
             FROM stok_masuk sm
             JOIN produk p ON sm.id_produk = p.id
             JOIN jenis_ikan ji ON p.id_jenis_ikan = ji.id
             JOIN supplier s ON sm.id_supplier = s.id
             JOIN users u ON sm.created_by = u.id
             {$gudangJoin}
             WHERE {$where}
             ORDER BY sm.created_at DESC",
            $params
        );
    }
}
