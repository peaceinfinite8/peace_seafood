<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\Database;
use App\Utils\ActivityLogHelper;

class StokTransferService
{
    /**
     * Buat transfer stok baru (status pending/sent)
     */
    public function createTransfer(array $data, int $idUser, int $idGudangAsal): int
    {
        $idProduk      = (int)$data['id_produk'];
        $gudangTujuan  = (int)$data['gudang_tujuan_id'];
        $qty           = (float)$data['qty'];

        if ($idGudangAsal === $gudangTujuan) {
            throw new \Exception("Gudang asal dan tujuan tidak boleh sama");
        }

        // Cek kecukupan stok di gudang asal
        $produk = Database::fetchOne(
            "SELECT stok_qty FROM produk WHERE id = ? AND id_gudang = ?",
            [$idProduk, $idGudangAsal]
        );

        if (!$produk || (float)$produk['stok_qty'] < $qty) {
            throw new \Exception("Stok di gudang asal tidak cukup");
        }

        $idTransfer = Database::insert('stok_transfer', [
            'gudang_asal_id'   => $idGudangAsal,
            'gudang_tujuan_id' => $gudangTujuan,
            'id_produk'        => $idProduk,
            'qty'              => $qty,
            'status'           => 'pending',
        ]);

        ActivityLogHelper::log('INSERT', 'stok_transfer', $idTransfer, null, [
            'gudang_asal_id'   => $idGudangAsal,
            'gudang_tujuan_id' => $gudangTujuan,
            'id_produk'        => $idProduk,
            'qty'              => $qty,
            'status'           => 'pending'
        ]);

        return $idTransfer;
    }

    /**
     * Ambil list transfer stok
     */
    public function getTransferList(int $idGudang, bool $allGudang = false): array
    {
        if ($allGudang && $idGudang === 0) {
            return Database::fetchAll(
                "SELECT t.*, ga.nama as nama_gudang_asal, gt.nama as nama_gudang_tujuan,
                        p.nama as nama_produk, ji.nama as nama_jenis
                 FROM stok_transfer t
                 JOIN gudang ga ON t.gudang_asal_id = ga.id
                 JOIN gudang gt ON t.gudang_tujuan_id = gt.id
                 JOIN produk p ON t.id_produk = p.id
                 JOIN jenis_ikan ji ON p.id_jenis_ikan = ji.id
                 ORDER BY t.created_at DESC"
            );
        }

        return Database::fetchAll(
            "SELECT t.*, ga.nama as nama_gudang_asal, gt.nama as nama_gudang_tujuan,
                    p.nama as nama_produk, ji.nama as nama_jenis
             FROM stok_transfer t
             JOIN gudang ga ON t.gudang_asal_id = ga.id
             JOIN gudang gt ON t.gudang_tujuan_id = gt.id
             JOIN produk p ON t.id_produk = p.id
             JOIN jenis_ikan ji ON p.id_jenis_ikan = ji.id
             WHERE t.gudang_asal_id = ? OR t.gudang_tujuan_id = ?
             ORDER BY t.created_at DESC",
            [$idGudang, $idGudang]
        );
    }

    /**
     * Update status transfer (pending -> sent -> received)
     */
    public function updateStatus(int $idTransfer, string $newStatus, int $idUser, int $idGudangUser): bool
    {
        $transfer = Database::fetchOne(
            "SELECT * FROM stok_transfer WHERE id = ?",
            [$idTransfer]
        );

        if (!$transfer) return false;

        $oldStatus = $transfer['status'];

        // Pengaman transisi status
        if ($oldStatus === 'received') {
            return false; // Transfer yang sudah diterima tidak bisa diubah lagi
        }

        if ($newStatus === 'sent' && $oldStatus !== 'pending') {
            return false;
        }

        if ($newStatus === 'received' && $oldStatus !== 'sent' && $oldStatus !== 'pending') {
            return false;
        }

        try {
            Database::beginTransaction();

            if ($newStatus === 'received') {
                $qty = (float)$transfer['qty'];
                $idProduk = (int)$transfer['id_produk'];
                $gudangAsalId = (int)$transfer['gudang_asal_id'];
                $gudangTujuanId = (int)$transfer['gudang_tujuan_id'];

                // 1. Potong stok di gudang asal
                $sourceProduct = Database::fetchOne(
                    "SELECT * FROM produk WHERE id = ? AND id_gudang = ?",
                    [$idProduk, $gudangAsalId]
                );

                if (!$sourceProduct || (float)$sourceProduct['stok_qty'] < $qty) {
                    throw new \Exception("Stok di gudang asal tidak mencukupi untuk transfer.");
                }

                $sourceStokBaru = (float)$sourceProduct['stok_qty'] - $qty;
                $sourceHargaBeli = (float)$sourceProduct['harga_beli'];
                $sourceNilaiBaru = $sourceStokBaru * $sourceHargaBeli;

                Database::update('produk', [
                    'stok_qty'   => $sourceStokBaru,
                    'nilai_stok' => $sourceNilaiBaru,
                ], 'id = ? AND id_gudang = ?', [$idProduk, $gudangAsalId]);

                ActivityLogHelper::log('UPDATE', 'produk', $idProduk, $sourceProduct, [
                    'id'         => $idProduk,
                    'id_gudang'  => $gudangAsalId,
                    'stok_qty'   => $sourceStokBaru,
                    'nilai_stok' => $sourceNilaiBaru
                ]);

                // 2. Tambah stok di gudang tujuan (Duplikasi produk dinamis jika belum ada)
                // Cari produk dengan nama dan jenis yang sama di gudang tujuan
                $destProduct = Database::fetchOne(
                    "SELECT * FROM produk WHERE nama = ? AND id_jenis_ikan = ? AND id_gudang = ?",
                    [$sourceProduct['nama'], $sourceProduct['id_jenis_ikan'], $gudangTujuanId]
                );

                if (!$destProduct) {
                    // Salin secara dinamis semua kolom produk asal untuk menjaga SKU/Kode Produk dan atribut lainnya
                    $payload = [];
                    foreach ($sourceProduct as $key => $val) {
                        if (in_array($key, ['id', 'created_at', 'updated_at'])) {
                            continue;
                        }
                        if ($key === 'id_gudang') {
                            $payload[$key] = $gudangTujuanId;
                        } elseif ($key === 'stok_qty' || $key === 'nilai_stok') {
                            $payload[$key] = 0;
                        } else {
                            $payload[$key] = $val;
                        }
                    }

                    $destId = Database::insert('produk', $payload);
                    $destProduct = Database::fetchOne("SELECT * FROM produk WHERE id = ?", [$destId]);

                    ActivityLogHelper::log('INSERT', 'produk', $destId, null, $payload);
                }

                $destId = (int)$destProduct['id'];
                $destStokBaru = (float)$destProduct['stok_qty'] + $qty;
                $destHargaBeli = (float)$sourceProduct['harga_beli']; // Samakan harga beli rata-rata asal
                $destNilaiBaru = $destStokBaru * $destHargaBeli;

                Database::update('produk', [
                    'stok_qty'   => $destStokBaru,
                    'nilai_stok' => $destNilaiBaru,
                    'harga_beli' => $destHargaBeli,
                ], 'id = ? AND id_gudang = ?', [$destId, $gudangTujuanId]);

                ActivityLogHelper::log('UPDATE', 'produk', $destId, $destProduct, [
                    'id'         => $destId,
                    'id_gudang'  => $gudangTujuanId,
                    'stok_qty'   => $destStokBaru,
                    'nilai_stok' => $destNilaiBaru
                ]);
            }

            // Update status transfer
            Database::update('stok_transfer', [
                'status' => $newStatus,
            ], 'id = ?', [$idTransfer]);

            ActivityLogHelper::log('UPDATE', 'stok_transfer', $idTransfer, ['status' => $oldStatus], ['status' => $newStatus]);

            Database::commit();
            return true;
        } catch (\Throwable $e) {
            if (Database::inTransaction()) {
                Database::rollBack();
            }
            error_log("Failed to process stock transfer status: " . $e->getMessage());
            return false;
        }
    }
}
