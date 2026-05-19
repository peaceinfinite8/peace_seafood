<?php

declare(strict_types=1);

namespace App\Services;

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
        $qty = (float) ($data['jumlah'] ?? 0);
        $harga = (float) ($data['harga_titip'] ?? 0);
        $total = $qty * $harga;

        return Database::insert('titipan', [
            'no_titipan' => $noTitipan,
            'id_gudang' => $idGudang,
            'id_pengirim' => (int) ($data['pembeli_id'] ?? 0),
            'tanggal_masuk' => $data['tanggal_masuk'] ?? date('Y-m-d'),
            'qty_total' => $qty,
            'qty_dijual' => 0,
            'qty_tersisa' => $qty,
            'nominal_total' => $total,
            'nominal_terjual' => 0,
            'komisi_persen' => (float) ($data['komisi_persen'] ?? 0),
            'komisi_tipe' => 'potong',
            'status' => 'masuk',
            'catatan' => $data['catatan'] ?? null,
            'created_by' => $idUser,
        ]);
    }

    public function terima(int $idGudang, int $idUser, array $data): int
    {
        return $this->createTitipan($data, $idUser, $idGudang);
    }

    /**
     * Catat penjualan titipan
     * 
     * Komisi dihitung dari harga kesepakatan, bukan harga jual aktual
     * 
     * Formula:
     * - Harga Kesepakatan = Nominal Total Titipan / Qty Total Titipan
     * - Total Kesepakatan = Qty Terjual × Harga Kesepakatan
     * - Komisi = Total Kesepakatan × Komisi Persen / 100
     * 
     * Contoh:
     * Titipan: 100 kg @ Rp 100.000/kg = Rp 10.000.000 (kesepakatan)
     * Komisi: 5%
     * Terjual: 10 kg @ Rp 120.000/kg (harga jual aktual lebih tinggi)
     * 
     * Total kesepakatan: 10 × Rp 100.000 = Rp 1.000.000
     * Komisi: Rp 1.000.000 × 5% = Rp 50.000 (bukan dari Rp 1.200.000)
     * 
     * Pembayaran ke pengirim:
     * - Jika potong langsung: Rp 1.200.000 - Rp 50.000 = Rp 1.150.000
     * - Jika bayar terpisah: Rp 1.200.000 (full), komisi jadi piutang
     */
    public function jualTitipan(int $idTitipan, int $idUser, array $data, int $idGudang): bool
    {
        $sql = "SELECT * FROM titipan WHERE id = ?";
        $params = [$idTitipan];
        if ($idGudang > 0) {
            $sql .= " AND id_gudang = ?";
            $params[] = $idGudang;
        }
        $sql .= " AND status IN ('masuk', 'dijual_sebagian')";

        $titipan = Database::fetchOne($sql, $params);

        if (!$titipan)
            return false;

        $qtyTerjual = (float) ($data['jumlah_terjual'] ?? 0);
        $hargaJual = (float) ($data['harga_jual'] ?? 0);
        if ($qtyTerjual <= 0 || $hargaJual <= 0)
            return false;

        $qtyTersisa = (float) $titipan['qty_tersisa'];
        if ($qtyTerjual > $qtyTersisa)
            return false;

        // Hitung harga kesepakatan dari data titipan
        $hargaKesepakatan = (float) $titipan['qty_total'] > 0 
            ? (float) $titipan['nominal_total'] / (float) $titipan['qty_total']
            : 0;
        
        // Total jual untuk pencatatan penjualan
        $totalJual = $qtyTerjual * $hargaJual;
        
        // Komisi dihitung dari harga kesepakatan, bukan harga jual
        $totalKesepakatan = $qtyTerjual * $hargaKesepakatan;
        $komisi = $totalKesepakatan * ((float) $titipan['komisi_persen'] / 100);

        // Insert titipan_penjualan
        Database::insert('titipan_penjualan', [
            'id_titipan' => $idTitipan,
            'id_penjual' => $idUser,
            'id_pembeli' => null,
            'qty' => $qtyTerjual,
            'harga_jual' => $hargaJual,
            'nominal' => $totalJual,
            'komisi_nominal' => $komisi,
            'tanggal_jual' => $data['tanggal'] ?? date('Y-m-d'),
            'status' => 'terjual',
            'created_by' => $idUser,
        ]);

        // Hitung total terjual sampai sekarang
        $totalQtyTerjual = Database::fetchOne(
            "SELECT COALESCE(SUM(qty), 0) as total FROM titipan_penjualan WHERE id_titipan = ?",
            [$idTitipan]
        )['total'] ?? 0;

        // Update status titipan
        $newStatus = ((float) $totalQtyTerjual >= (float) $titipan['qty_total'])
            ? 'dijual_semua'
            : 'dijual_sebagian';

        Database::update('titipan', [
            'qty_dijual' => (float) $totalQtyTerjual,
            'qty_tersisa' => max(0, (float) $titipan['qty_total'] - (float) $totalQtyTerjual),
            'nominal_terjual' => (float) $titipan['nominal_terjual'] + $totalJual,
            'status' => $newStatus,
        ], 'id = ?', [$idTitipan]);

        // Handle komisi sesuai setting
        $settingKomisi = Helper::getSetting($idGudang, 'komisi_penitipan_tipe', 'potong');
        if ($settingKomisi === 'bayar_terpisah') {
            // Buat piutang komisi dari supplier (supplier harus bayar komisi)
            Database::insert('hutang_piutang', [
                'id_gudang' => $idGudang,
                'jenis' => 'piutang',
                'id_supplier' => $titipan['id_pengirim'],
                'nominal' => $komisi,
                'sisa_hutang' => $komisi,
                'nominal_bayar' => 0,
                'jatuh_tempo' => date('Y-m-d', strtotime('+7 days')),
                'status' => 'open',
                'catatan' => "Komisi titipan #{$titipan['no_titipan']} - {$qtyTerjual} kg",
                'created_by' => $idUser,
            ]);
        }
        // Jika 'potong', komisi langsung dipotong dari pembayaran (tidak perlu record hutang)

        return true;
    }

    public function jual(array $data, int $idUser, int $idGudang): bool
    {
        return $this->jualTitipan((int) $data['titipan_id'], $idUser, $data, $idGudang);
    }

    /**
     * Selesaikan titipan
     */
    public function selesaikanTitipan(int $idTitipan, int $idGudang): bool
    {
        $condition = 'id = ?';
        $params = [$idTitipan];
        if ($idGudang > 0) {
            $condition .= ' AND id_gudang = ?';
            $params[] = $idGudang;
        }
        $condition .= ' AND qty_tersisa <= 0';

        return Database::update(
            'titipan',
            ['status' => 'selesai'],
            $condition,
            $params
        );
    }

    public function settlement(int $idTitipan, int $idGudang): bool
    {
        return $this->selesaikanTitipan($idTitipan, $idGudang);
    }

    /**
     * Get list titipan.
     * Jika $allGudang = true, ambil semua gudang (untuk BOS).
     */
    public function getTitipanList(int $idGudang, array $filters = [], bool $allGudang = false): array
    {
        if ($allGudang && $idGudang === 0) {
            $where = "1=1";
            $params = [];
        } else {
            $where = "t.id_gudang = ?";
            $params = [$idGudang];
        }

        if (!empty($filters['status'])) {
            $where .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['dari'])) {
            $where .= " AND DATE(t.created_at) >= ?";
            $params[] = $filters['dari'];
        }
        if (!empty($filters['sampai'])) {
            $where .= " AND DATE(t.created_at) <= ?";
            $params[] = $filters['sampai'];
        }

        $gudangCol = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
        $gudangJoin = ($allGudang && $idGudang === 0) ? "LEFT JOIN gudang g ON t.id_gudang = g.id" : "";

        return Database::fetchAll(
            "SELECT t.*,
                s.nama as nama_supplier,
                CONCAT('Titipan ', t.no_titipan) as nama_produk,
                t.qty_total as jumlah,
                t.qty_dijual as jumlah_terjual,
                CASE WHEN t.qty_total > 0 THEN ROUND(t.nominal_total / t.qty_total, 0) ELSE 0 END as harga_titip,
                COALESCE(SUM(tp.qty), 0) as total_terjual,
                COALESCE(SUM(tp.komisi_nominal), 0) as total_komisi{$gudangCol}
             FROM titipan t
             JOIN supplier s ON t.id_pengirim = s.id
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
    public function getSettlement(int $idTitipan, int $idGudang, bool $allGudang = false): ?array
    {
        $sql = "SELECT t.*,
                    s.nama as nama_supplier,
                    CONCAT('Titipan ', t.no_titipan) as nama_produk,
                    t.qty_total as jumlah,
                    t.qty_dijual as jumlah_terjual,
                    CASE WHEN t.qty_total > 0 THEN ROUND(t.nominal_total / t.qty_total, 0) ELSE 0 END as harga_titip
             FROM titipan t
             JOIN supplier s ON t.id_pengirim = s.id
             WHERE t.id = ?";
        $params = [$idTitipan];
        if (!$allGudang && $idGudang > 0) {
            $sql .= " AND t.id_gudang = ?";
            $params[] = $idGudang;
        }

        $titipan = Database::fetchOne($sql, $params);

        if (!$titipan)
            return null;

        $titipan['penjualan'] = Database::fetchAll(
            "SELECT tp.*, u.name as nama_user
             FROM titipan_penjualan tp
             LEFT JOIN users u ON tp.created_by = u.id
             WHERE tp.id_titipan = ? ORDER BY tp.created_at",
            [$idTitipan]
        );

        $titipan['total_terjual'] = array_sum(array_column($titipan['penjualan'], 'qty'));
        $titipan['total_pendapatan'] = array_sum(array_column($titipan['penjualan'], 'nominal'));
        $titipan['total_komisi'] = array_sum(array_column($titipan['penjualan'], 'komisi_nominal'));
        $titipan['sisa_qty'] = (float) $titipan['qty_total'] - $titipan['total_terjual'];

        return $titipan;
    }
}
