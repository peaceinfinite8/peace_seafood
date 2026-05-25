<?php

declare(strict_types=1);

namespace App\Services;

use App\Middleware\AuthMiddleware;
use App\Utils\Database;
use App\Utils\Helper;

class PenitipanService
{
    /**
     * Terima titipan masuk
     */
    public function createTitipan(array $data, int $idUser, int $idGudang): int
    {
        $noTitipan = Helper::generateTitipanNumber($idGudang);
        $qty       = (float)($data['qty'] ?? $data['jumlah'] ?? 0);
        $harga     = (float)($data['harga_kesepakatan'] ?? $data['harga_titip'] ?? 0);
        $total     = $qty * $harga;

        return Database::insert('titipan', [
            'id_gudang'       => $idGudang,
            'id_pengirim'     => (int)($data['id_pengirim'] ?? $data['pembeli_id'] ?? $data['id_supplier'] ?? 0),
            'id_produk'       => (int)($data['id_produk'] ?? $data['produk_id'] ?? 0),
            'no_titipan'      => $noTitipan,
            'tanggal_masuk'   => $data['tanggal_masuk'] ?? date('Y-m-d'),
            'qty_total'       => $qty,
            'qty_dijual'      => 0,
            'qty_tersisa'     => $qty,
            'nominal_total'   => $total,
            'nominal_terjual' => 0,
            'komisi_persen'   => (float)($data['komisi_persen'] ?? 0),
            'komisi_tipe'     => $data['komisi_tipe'] ?? 'potong',
            'status'          => 'masuk',
            'catatan'         => $data['catatan'] ?? null,
            'created_by'      => $idUser,
        ]);
    }

    /**
     * Catat penjualan titipan
     */
    public function jualTitipan(int $idTitipan, array $data, int $idGudang): bool
    {
        $titipan = Database::fetchOne(
            "SELECT * FROM titipan WHERE id = ? AND id_gudang = ? AND status IN ('masuk', 'dijual_sebagian')",
            [$idTitipan, $idGudang]
        );

        if (!$titipan) return false;

        $qtyTerjual   = (float)($data['qty_terjual'] ?? $data['jumlah_terjual'] ?? 0);
        $hargaJual    = (float)($data['harga_jual'] ?? 0);
        $totalJual    = $qtyTerjual * $hargaJual;
        $komisi       = $totalJual * ((float)$titipan['komisi_persen'] / 100);

        // Insert titipan_penjualan
        Database::insert('titipan_penjualan', [
            'id_titipan'     => $idTitipan,
            'id_penjual'     => AuthMiddleware::getAuthUserId(),
            'id_pembeli'     => $data['id_pembeli'] ?? null,
            'qty'            => $qtyTerjual,
            'harga_jual'     => $hargaJual,
            'nominal'        => $totalJual,
            'komisi_nominal' => $komisi,
            'tanggal_jual'   => $data['tanggal'] ?? date('Y-m-d'),
            'status'         => 'terjual',
            'created_by'     => AuthMiddleware::getAuthUserId(),
        ]);

        // Hitung total terjual sampai sekarang
        $totalQtyTerjual = Database::fetchOne(
            "SELECT COALESCE(SUM(qty), 0) as total FROM titipan_penjualan WHERE id_titipan = ?",
            [$idTitipan]
        )['total'] ?? 0;

        // Update status titipan
        $newStatus = ((float)$totalQtyTerjual >= (float)$titipan['qty'])
            ? 'dijual_semua'
            : 'dijual_sebagian';

        Database::update('titipan', ['status' => $newStatus], 'id = ?', [$idTitipan]);

        // Handle komisi sesuai setting
        $settingKomisi = Helper::getSetting($idGudang, 'komisi_mode', 'potong_langsung');
        if ($settingKomisi === 'bayar_terpisah') {
            // Buat hutang komisi ke supplier
            Database::insert('hutang_piutang', [
                'id_gudang'     => $idGudang,
                'jenis'         => 'piutang',
                'id_supplier'   => $titipan['id_pengirim'],
                'nominal'       => $komisi,
                'nominal_bayar' => 0,
                'jatuh_tempo'   => date('Y-m-d'),
                'status'        => 'open',
                'catatan'       => "Komisi titipan #{$idTitipan}",
                'created_by'    => AuthMiddleware::getAuthUserId(),
            ]);
        }

        Database::update('titipan', [
            'qty_dijual'      => (float)$totalQtyTerjual,
            'qty_tersisa'     => max(0, (float)$titipan['qty_total'] - (float)$totalQtyTerjual),
            'nominal_terjual' => (float)Database::fetchOne("SELECT COALESCE(SUM(nominal),0) as total FROM titipan_penjualan WHERE id_titipan = ?", [$idTitipan])['total'] ?? 0,
        ], 'id = ?', [$idTitipan]);

        return true;
    }

    /**
     * Selesaikan titipan
     */
    public function selesaikanTitipan(int $idTitipan, int $idGudang): bool
    {
        return Database::update(
            'titipan',
            ['status' => 'selesai'],
            'id = ? AND id_gudang = ?',
            [$idTitipan, $idGudang]
        );
    }

    /**
     * Get list titipan.
     * Jika $allGudang = true, ambil semua gudang (untuk BOS).
     */
    public function getTitipanList(int $idGudang, array $filters = [], bool $allGudang = false): array
    {
        if ($allGudang && $idGudang === 0) {
            $where  = "1=1";
            $params = [];
        } else {
            $where  = "t.id_gudang = ?";
            $params = [$idGudang];
        }

        if (!empty($filters['status'])) {
            $where    .= " AND t.status = ?";
            $params[]  = $filters['status'];
        }
        if (!empty($filters['dari'])) {
            $where    .= " AND DATE(t.created_at) >= ?";
            $params[]  = $filters['dari'];
        }
        if (!empty($filters['sampai'])) {
            $where    .= " AND DATE(t.created_at) <= ?";
            $params[]  = $filters['sampai'];
        }

        $gudangCol  = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
        $gudangJoin = ($allGudang && $idGudang === 0) ? "LEFT JOIN gudang g ON t.id_gudang = g.id" : "";

        return Database::fetchAll(
            "SELECT t.*, s.nama as nama_supplier, p.nama as nama_produk,
                    COALESCE(SUM(tp.qty), 0) as total_terjual,
                    COALESCE(SUM(tp.komisi_nominal), 0) as total_komisi{$gudangCol}
             FROM titipan t
             JOIN supplier s ON t.id_pengirim = s.id
             LEFT JOIN produk p ON t.id_produk = p.id
             LEFT JOIN titipan_penjualan tp ON tp.id_titipan = t.id
             {$gudangJoin}
             WHERE {$where}
             GROUP BY t.id
             ORDER BY t.created_at DESC",
            $params
        );
    }

    /**
     * Get settlement detail titipan
     */
    public function getSettlement(int $idTitipan, int $idGudang): ?array
    {
        $titipan = Database::fetchOne(
            "SELECT t.*, s.nama as nama_supplier, p.nama as nama_produk
             FROM titipan t
             JOIN supplier s ON t.id_pengirim = s.id
             LEFT JOIN produk p ON 1=0
             WHERE t.id = ? AND t.id_gudang = ?",
            [$idTitipan, $idGudang]
        );

        if (!$titipan) return null;

        $titipan['penjualan'] = Database::fetchAll(
            "SELECT * FROM titipan_penjualan WHERE id_titipan = ? ORDER BY created_at",
            [$idTitipan]
        );

        $titipan['total_terjual']     = array_sum(array_column($titipan['penjualan'], 'qty'));
        $titipan['total_pendapatan']  = array_sum(array_column($titipan['penjualan'], 'nominal'));
        $titipan['total_komisi']      = array_sum(array_column($titipan['penjualan'], 'komisi_nominal'));
        $titipan['sisa_qty']          = (float)$titipan['qty_total'] - (float)$titipan['total_terjual'];

        return $titipan;
    }
}
