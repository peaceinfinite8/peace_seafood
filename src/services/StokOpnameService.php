<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\Database;
use App\Utils\ActivityLogHelper;

class StokOpnameService
{
    /**
     * Buat sesi stok opname baru (status DRAFT)
     */
    public function createOpname(array $data, int $idUser, int $idGudang): int
    {
        try {
            Database::beginTransaction();

            $idOpname = Database::insert('stok_opname', [
                'id_gudang'      => $idGudang,
                'tanggal_opname' => date('Y-m-d'),
                'status'         => 'draft',
                'created_by'     => $idUser,
            ]);

            foreach ($data['items'] as $item) {
                $idProduk = (int)$item['id_produk'];
                $qtyFisik = (float)$item['qty_fisik'];

                // Dapatkan stok sistem saat ini
                $produk = Database::fetchOne(
                    "SELECT stok_qty FROM produk WHERE id = ? AND id_gudang = ?",
                    [$idProduk, $idGudang]
                );
                $qtySistem = $produk ? (float)$produk['stok_qty'] : 0.0;

                Database::insert('stok_opname_detail', [
                    'id_stok_opname' => $idOpname,
                    'id_produk'      => $idProduk,
                    'qty_sistem'     => $qtySistem,
                    'qty_fisik'      => $qtyFisik,
                ]);
            }

            // Log Audit Trail
            ActivityLogHelper::log('INSERT', 'stok_opname', $idOpname, null, [
                'id_gudang'  => $idGudang,
                'status'     => 'draft',
                'item_count' => count($data['items'])
            ]);

            Database::commit();
            return $idOpname;
        } catch (\Throwable $e) {
            if (Database::inTransaction()) {
                Database::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Ambil daftar sesi stok opname
     */
    public function getOpnameList(int $idGudang, bool $allGudang = false): array
    {
        if ($allGudang && $idGudang === 0) {
            return Database::fetchAll(
                "SELECT so.*, g.nama as nama_gudang, u.name as nama_user
                 FROM stok_opname so
                 JOIN gudang g ON so.id_gudang = g.id
                 JOIN users u ON so.created_by = u.id
                 ORDER BY so.tanggal_opname DESC, so.created_at DESC"
            );
        }

        return Database::fetchAll(
            "SELECT so.*, u.name as nama_user
             FROM stok_opname so
             JOIN users u ON so.created_by = u.id
             WHERE so.id_gudang = ?
             ORDER BY so.tanggal_opname DESC, so.created_at DESC",
            [$idGudang]
        );
    }

    /**
     * Ambil detail sesi stok opname dan item-itemnya
     */
    public function getOpnameDetail(int $idOpname, int $idGudang): ?array
    {
        $so = Database::fetchOne(
            "SELECT so.*, g.nama as nama_gudang, u.name as nama_user
             FROM stok_opname so
             JOIN gudang g ON so.id_gudang = g.id
             JOIN users u ON so.created_by = u.id
             WHERE so.id = ? AND (so.id_gudang = ? OR ? = 0)",
            [$idOpname, $idGudang, $idGudang]
        );

        if (!$so) return null;

        $so['items'] = Database::fetchAll(
            "SELECT sod.*, p.nama as nama_produk, ji.nama as nama_jenis
             FROM stok_opname_detail sod
             JOIN produk p ON sod.id_produk = p.id
             JOIN jenis_ikan ji ON p.id_jenis_ikan = ji.id
             WHERE sod.id_stok_opname = ?",
            [$idOpname]
        );

        return $so;
    }

    /**
     * Finalisasi stok opname (lakukan adjustment stok nyata di gudang tertentu)
     */
    public function finalizeOpname(int $idOpname, int $idGudang): bool
    {
        $so = Database::fetchOne(
            "SELECT * FROM stok_opname WHERE id = ? AND id_gudang = ? AND status = 'draft'",
            [$idOpname, $idGudang]
        );

        if (!$so) return false;

        $items = Database::fetchAll(
            "SELECT * FROM stok_opname_detail WHERE id_stok_opname = ?",
            [$idOpname]
        );

        try {
            Database::beginTransaction();

            foreach ($items as $item) {
                $idProduk = (int)$item['id_produk'];
                $qtyFisik = (float)$item['qty_fisik'];

                // Dapatkan produk untuk audit trail & hitung nilai stok baru
                $produkBefore = Database::fetchOne(
                    "SELECT * FROM produk WHERE id = ? AND id_gudang = ?",
                    [$idProduk, $idGudang]
                );

                if ($produkBefore) {
                    $hargaBeli  = (float)$produkBefore['harga_beli'];
                    $nilaiBaru  = (int)($qtyFisik * $hargaBeli);

                    // UPDATE dengan filter id_gudang ketat agar aman dari interferensi multi-gudang
                    Database::query(
                        "UPDATE produk SET stok_qty = ?, nilai_stok = ? WHERE id = ? AND id_gudang = ?",
                        [$qtyFisik, $nilaiBaru, $idProduk, $idGudang]
                    );

                    // Log Audit Trail per produk
                    ActivityLogHelper::log('UPDATE', 'produk', $idProduk, $produkBefore, [
                        'id'         => $idProduk,
                        'id_gudang'  => $idGudang,
                        'stok_qty'   => $qtyFisik,
                        'nilai_stok' => $nilaiBaru,
                    ]);
                }
            }

            // Update status sesi opname
            Database::update('stok_opname', ['status' => 'final'], 'id = ?', [$idOpname]);

            // Log sesi opname final
            ActivityLogHelper::log('UPDATE', 'stok_opname', $idOpname, ['status' => 'draft'], ['status' => 'final']);

            Database::commit();
            return true;
        } catch (\Throwable $e) {
            if (Database::inTransaction()) {
                Database::rollBack();
            }
            return false;
        }
    }
}
