<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\Database;
use App\Utils\Helper;

class PenjualanService
{
    private StokService $stokService;

    public function __construct()
    {
        $this->stokService = new StokService();
    }

    /**
     * Buat nota draft baru
     */
    public function createNota(array $data, int $idUser, int $idGudang): int
    {
        $noNota = Helper::generateNotaNumber($idGudang);
        $subtotal = 0;

        foreach ($data['items'] as $item) {
            $subtotal += (float) $item['qty'] * (float) $item['harga_jual'];
        }

        $diskon = (float) ($data['diskon'] ?? 0);
        $pajak = (float) ($data['pajak'] ?? 0);
        $total = $subtotal - $diskon + $pajak;

        $idNota = Database::insert('nota', [
            'no_nota' => $noNota,
            'id_gudang' => $idGudang,
            'id_pembeli' => $data['id_pembeli'],
            'created_by' => $idUser,
            'tanggal_nota' => date('Y-m-d'),
            'subtotal' => $subtotal,
            'diskon_nominal' => $diskon,
            'pajak' => $pajak,
            'total' => $total,
            'pembayaran' => $data['jenis_pembayaran'] ?? 'cash',
            'status' => 'draft',
            'catatan' => $data['catatan'] ?? null,
        ]);

        // Insert items
        foreach ($data['items'] as $item) {
            $itemSubtotal = (float) $item['qty'] * (float) $item['harga_jual'];
            Database::insert('nota_detail', [
                'id_nota' => $idNota,
                'id_produk' => $item['id_produk'],
                'qty' => $item['qty'],
                'harga_jual' => $item['harga_jual'],
                'subtotal' => $itemSubtotal,
            ]);
        }

        return $idNota;
    }

    /**
     * Finalize nota (dari draft ke final) + update stok + hutang/piutang
     */
    public function finalizeNota(int $idNota, int $idGudang): bool
    {
        $nota = Database::fetchOne(
            "SELECT * FROM nota WHERE id = ? AND id_gudang = ? AND status = 'draft'",
            [$idNota, $idGudang]
        );

        if (!$nota)
            return false;

        $items = Database::fetchAll(
            "SELECT * FROM nota_detail WHERE id_nota = ?",
            [$idNota]
        );

        // Kurangi stok untuk setiap item
        foreach ($items as $item) {
            $ok = $this->stokService->kurangiStok(
                (int) $item['id_produk'],
                (float) $item['qty'],
                $idGudang
            );
            if (!$ok)
                return false; // stok tidak cukup
        }

        // Update nota ke FINAL
        Database::update('nota', ['status' => 'final'], 'id = ?', [$idNota]);

        // Jika hutang, buat record hutang/piutang
        if ($nota['pembayaran'] === 'hutang') {
            $jatuhTempo = Helper::calcJatuhTempo($idGudang);
            Database::insert('hutang_piutang', [
                'id_gudang' => $idGudang,
                'jenis' => 'piutang',
                'id_pembeli' => $nota['id_pembeli'],
                'id_nota' => $idNota,
                'nominal' => $nota['total'],
                'nominal_bayar' => 0,
                'jatuh_tempo' => $jatuhTempo,
                'status' => 'open',
                'created_by' => $nota['created_by'],
            ]);
        }

        return true;
    }

    /**
     * Cancel nota (revert changes)
     */
    public function cancelNota(int $idNota, int $idGudang): bool
    {
        $nota = Database::fetchOne(
            "SELECT * FROM nota WHERE id = ? AND id_gudang = ? AND status IN ('draft', 'final')",
            [$idNota, $idGudang]
        );

        if (!$nota)
            return false;

        // Jika sudah final, kembalikan stok
        if ($nota['status'] === 'final') {
            $items = Database::fetchAll(
                "SELECT * FROM nota_detail WHERE id_nota = ?",
                [$idNota]
            );

            foreach ($items as $item) {
                // Tambah kembali stok
                Database::query(
                    "UPDATE produk SET stok_qty = stok_qty + ? WHERE id = ?",
                    [(float) $item['qty'], (int) $item['id_produk']]
                );
            }

            // Hapus hutang piutang terkait jika ada
            Database::query(
                "UPDATE hutang_piutang SET status = 'cancelled' WHERE id_nota = ?",
                [$idNota]
            );
        }

        Database::update('nota', ['status' => 'cancelled'], 'id = ?', [$idNota]);

        return true;
    }

    /**
     * Get list nota.
     * Jika $allGudang = true, ambil semua gudang (untuk BOS).
     */
    public function getNotaList(int $idGudang, array $filters = [], bool $allGudang = false): array
    {
        if ($allGudang && $idGudang === 0) {
            $where = "1=1";
            $params = [];
        } else {
            $where = "n.id_gudang = ?";
            $params = [$idGudang];
        }

        if (!empty($filters['status'])) {
            $where .= " AND n.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['dari'])) {
            $where .= " AND DATE(n.tanggal_nota) >= ?";
            $params[] = $filters['dari'];
        }
        if (!empty($filters['sampai'])) {
            $where .= " AND DATE(n.tanggal_nota) <= ?";
            $params[] = $filters['sampai'];
        }
        if (!empty($filters['id_pembeli'])) {
            $where .= " AND n.id_pembeli = ?";
            $params[] = $filters['id_pembeli'];
        }

        $gudangCol = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
        $gudangJoin = ($allGudang && $idGudang === 0) ? "LEFT JOIN gudang g ON n.id_gudang = g.id" : "";

        return Database::fetchAll(
            "SELECT n.*, p.nama as nama_pembeli, u.name as nama_user{$gudangCol}
             FROM nota n
             LEFT JOIN pembeli p ON n.id_pembeli = p.id
             LEFT JOIN users u ON n.created_by = u.id
             {$gudangJoin}
             WHERE {$where}
             ORDER BY n.tanggal_nota DESC, n.id DESC",
            $params
        );
    }

    /**
     * Get detail nota dengan items.
     * Jika $allGudang = true, tidak filter by id_gudang (untuk BOS).
     */
    public function getNotaDetail(int $idNota, int $idGudang, bool $allGudang = false): ?array
    {
        // Select fields with aliases that match frontend expectations.
        // Use `created_by` to join users and `telpon` column from pembeli.
        if ($allGudang && $idGudang === 0) {
            $nota = Database::fetchOne(
                "SELECT n.*, p.nama as nama_pembeli, p.telpon as no_telepon,
                        p.alamat as alamat_pembeli, u.name as nama_user,
                        g.nama as nama_gudang
                 FROM nota n
                 LEFT JOIN pembeli p ON n.id_pembeli = p.id
                 LEFT JOIN users u ON n.created_by = u.id
                 LEFT JOIN gudang g ON n.id_gudang = g.id
                 WHERE n.id = ?",
                [$idNota]
            );
        } else {
            $nota = Database::fetchOne(
                "SELECT n.*, p.nama as nama_pembeli, p.telpon as no_telepon,
                        p.alamat as alamat_pembeli, u.name as nama_user,
                        g.nama as nama_gudang
                 FROM nota n
                 LEFT JOIN pembeli p ON n.id_pembeli = p.id
                 LEFT JOIN users u ON n.created_by = u.id
                 LEFT JOIN gudang g ON n.id_gudang = g.id
                 WHERE n.id = ? AND n.id_gudang = ?",
                [$idNota, $idGudang]
            );
        }

        if (!$nota)
            return null;

        $nota['items'] = Database::fetchAll(
            "SELECT nd.*, pr.nama as nama_produk, ji.nama as nama_jenis, 'kg' as satuan
             FROM nota_detail nd
             JOIN produk pr ON nd.id_produk = pr.id
             JOIN jenis_ikan ji ON pr.id_jenis_ikan = ji.id
             WHERE nd.id_nota = ?",
            [$idNota]
        );

        return $nota;
    }
}
