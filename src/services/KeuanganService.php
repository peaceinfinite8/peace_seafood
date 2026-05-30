<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\Database;
use App\Utils\ActivityLogHelper;

class KeuanganService
{
    /**
     * Get list hutang/piutang.
     * Jika $allGudang = true, ambil semua gudang (untuk BOS).
     */
    public function getHutangPiutangList(int $idGudang, array $filters = [], bool $allGudang = false): array
    {
        if ($allGudang && $idGudang === 0) {
            $where  = "1=1";
            $params = [];
        } else {
            $where  = "hp.id_gudang = ?";
            $params = [$idGudang];
        }

        if (!empty($filters['jenis'])) {
            $where    .= " AND hp.jenis = ?";
            $params[]  = $filters['jenis'];
        }
        if (!empty($filters['status'])) {
            $where    .= " AND hp.status = ?";
            $params[]  = $filters['status'];
        }
        if (!empty($filters['dari'])) {
            $where    .= " AND DATE(hp.created_at) >= ?";
            $params[]  = $filters['dari'];
        }
        if (!empty($filters['sampai'])) {
            $where    .= " AND DATE(hp.created_at) <= ?";
            $params[]  = $filters['sampai'];
        }

        $gudangCol  = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
        $gudangJoin = ($allGudang && $idGudang === 0) ? "LEFT JOIN gudang g ON hp.id_gudang = g.id" : "";

        return Database::fetchAll(
            "SELECT hp.*,
                    s.nama as nama_supplier,
                    p.nama as nama_pembeli,
                    n.no_nota,
                    DATEDIFF(hp.jatuh_tempo, CURDATE()) as hari_jatuh_tempo{$gudangCol}
             FROM hutang_piutang hp
             LEFT JOIN supplier s ON hp.id_supplier = s.id
             LEFT JOIN pembeli p ON hp.id_pembeli = p.id
             LEFT JOIN nota n ON hp.id_nota = n.id
             {$gudangJoin}
             WHERE {$where}
             ORDER BY hp.jatuh_tempo ASC, hp.created_at DESC",
            $params
        );
    }

    public function createHutangPiutang(array $data, int $idGudang, int $idUser): int
    {
        $payload = [
            'id_gudang'   => $idGudang,
            'jenis'       => $data['jenis'],      // 'hutang' atau 'piutang'
            'id_supplier' => $data['id_supplier'] ?? null,
            'id_pembeli'  => $data['id_pembeli'] ?? null,
            'nominal'     => $data['nominal'],
            'jatuh_tempo' => $data['jatuh_tempo'] ?? null,
            'catatan'     => $data['catatan'] ?? null,
            'status'      => 'open',
            'created_by'  => $idUser,
        ];
        $idHP = Database::insert('hutang_piutang', $payload);

        ActivityLogHelper::log('INSERT', 'hutang_piutang', $idHP, null, $payload);

        return $idHP;
    }

    /**
     * Input pembayaran hutang/piutang.
     * Jika $allGudang = true, tidak filter by id_gudang (untuk BOS).
     */
    public function bayar(array $data, int $idGudang, int $idUser, bool $allGudang = false): bool
    {
        if ($allGudang && $idGudang === 0) {
            $hp = Database::fetchOne(
                "SELECT * FROM hutang_piutang WHERE id = ? AND status != 'lunas'",
                [(int)$data['id_hutang_piutang']]
            );
        } else {
            $hp = Database::fetchOne(
                "SELECT * FROM hutang_piutang WHERE id = ? AND id_gudang = ? AND status != 'lunas'",
                [(int)$data['id_hutang_piutang'], $idGudang]
            );
        }

        if (!$hp) return false;

        $nominalBayar = (float)$data['nominal_bayar'];
        if ($nominalBayar > (float)$hp['sisa_hutang']) return false;

        $sisaBaru = (float)$hp['sisa_hutang'] - $nominalBayar;
        $status   = $sisaBaru <= 0 ? 'lunas' : 'sebagian';

        try {
            Database::beginTransaction();

            // Update hutang piutang
            Database::update('hutang_piutang', [
                'nominal_bayar' => (float)$hp['nominal_bayar'] + $nominalBayar,
                'status'        => $status,
            ], 'id = ?', [(int)$hp['id']]);

            ActivityLogHelper::log('UPDATE', 'hutang_piutang', (int)$hp['id'], $hp, [
                'nominal_bayar' => (float)$hp['nominal_bayar'] + $nominalBayar,
                'status'        => $status,
            ]);

            $payloadHistory = [
                'id_hutang_piutang' => $hp['id'],
                'nominal_bayar'     => $nominalBayar,
                'keterangan'        => $data['catatan'] ?? null,
                'created_by'        => $idUser,
                'created_at'        => (isset($data['tanggal_bayar']) ? $data['tanggal_bayar'] : date('Y-m-d')) . ' ' . date('H:i:s'),
            ];

            // Simpan history pembayaran
            $idHPH = Database::insert('hutang_piutang_history', $payloadHistory);

            ActivityLogHelper::log('INSERT', 'hutang_piutang_history', $idHPH, null, $payloadHistory);

            Database::commit();
            return true;
        } catch (\Throwable $e) {
            if (Database::inTransaction()) {
                Database::rollBack();
            }
            return false;
        }
    }

    /**
     * Get hutang aging report.
     * Jika $allGudang = true, ambil semua gudang (untuk BOS).
     */
    public function getHutangAging(int $idGudang, bool $allGudang = false): array
    {
        if ($allGudang && $idGudang === 0) {
            $where  = "hp.status != 'lunas' AND hp.status != 'cancelled'";
            $params = [];
        } else {
            $where  = "hp.id_gudang = ? AND hp.status != 'lunas' AND hp.status != 'cancelled'";
            $params = [$idGudang];
        }

        $gudangCol  = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
        $gudangJoin = ($allGudang && $idGudang === 0) ? "LEFT JOIN gudang g ON hp.id_gudang = g.id" : "";

        return Database::fetchAll(
            "SELECT hp.*,
                    s.nama as nama_supplier,
                    p.nama as nama_pembeli,
                    CASE
                        WHEN hp.jatuh_tempo IS NULL THEN 'no_due'
                        WHEN hp.jatuh_tempo < CURDATE() THEN 'overdue'
                        WHEN hp.jatuh_tempo <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'soon'
                        ELSE 'ok'
                    END as aging_status,
                    DATEDIFF(hp.jatuh_tempo, CURDATE()) as hari_jatuh_tempo{$gudangCol}
             FROM hutang_piutang hp
             LEFT JOIN supplier s ON hp.id_supplier = s.id
             LEFT JOIN pembeli p ON hp.id_pembeli = p.id
             {$gudangJoin}
             WHERE {$where}
             ORDER BY hp.jatuh_tempo ASC",
            $params
        );
    }

    /**
     * Get biaya operasional.
     * Jika $allGudang = true, ambil semua gudang (untuk BOS).
     */
    public function getBiayaList(int $idGudang, array $filters = [], bool $allGudang = false): array
    {
        if ($allGudang && $idGudang === 0) {
            $where  = "1=1";
            $params = [];
        } else {
            $where  = "id_gudang = ?";
            $params = [$idGudang];
        }

        if (!empty($filters['dari'])) {
            $where    .= " AND DATE(tanggal) >= ?";
            $params[]  = $filters['dari'];
        }
        if (!empty($filters['sampai'])) {
            $where    .= " AND DATE(tanggal) <= ?";
            $params[]  = $filters['sampai'];
        }

        return Database::fetchAll(
            "SELECT * FROM biaya_operasional WHERE {$where} ORDER BY tanggal DESC",
            $params
        );
    }

    /**
     * Input biaya operasional
     */
    public function createBiaya(array $data, int $idUser, int $idGudang): int
    {
        $payload = [
            'id_gudang'   => $idGudang,
            'kategori'    => $data['kategori'] ?? 'operasional',
            'deskripsi'   => $data['nama_biaya'] . (!empty($data['catatan']) ? ' - ' . $data['catatan'] : ''),
            'nominal'     => $data['nominal'],
            'tanggal'     => $data['tanggal'] ?? date('Y-m-d'),
            'created_by'  => $idUser,
        ];
        $idBiaya = Database::insert('biaya_operasional', $payload);

        ActivityLogHelper::log('INSERT', 'biaya_operasional', $idBiaya, null, $payload);

        return $idBiaya;
    }

    /**
     * Summary keuangan untuk dashboard.
     * Jika $allGudang = true, ambil semua gudang (untuk BOS tanpa filter gudang).
     */
    public function getSummary(int $idGudang, bool $allGudang = false): array
    {
        if ($allGudang && $idGudang === 0) {
            $totalHutang = Database::fetchOne(
                "SELECT COALESCE(SUM(sisa_hutang), 0) as total FROM hutang_piutang
                 WHERE jenis = 'hutang' AND status != 'lunas'",
                []
            )['total'] ?? 0;

            $totalPiutang = Database::fetchOne(
                "SELECT COALESCE(SUM(sisa_hutang), 0) as total FROM hutang_piutang
                 WHERE jenis = 'piutang' AND status != 'lunas'",
                []
            )['total'] ?? 0;

            $overdueCount = Database::fetchOne(
                "SELECT COUNT(*) as cnt FROM hutang_piutang
                 WHERE status != 'lunas' AND jatuh_tempo < CURDATE()",
                []
            )['cnt'] ?? 0;

            // 💰 Keuangan Masuk: Total Penjualan Cash + Penerimaan Piutang
            $penjualanCash = Database::fetchOne(
                "SELECT COALESCE(SUM(total), 0) as total FROM nota 
                 WHERE pembayaran = 'cash' AND status = 'final'"
            )['total'] ?? 0;

            $terimaPiutang = Database::fetchOne(
                "SELECT COALESCE(SUM(hph.nominal_bayar), 0) as total 
                 FROM hutang_piutang_history hph 
                 JOIN hutang_piutang hp ON hph.id_hutang_piutang = hp.id 
                 WHERE hp.jenis = 'piutang'"
            )['total'] ?? 0;

            $keuanganMasuk = (float)$penjualanCash + (float)$terimaPiutang;

            // 💸 Keuangan Keluar: Pembelian Stok + Biaya Operasional + Pembayaran Hutang
            $pembelianStok = Database::fetchOne(
                "SELECT COALESCE(SUM(total), 0) as total FROM stok_masuk 
                 WHERE status = 'confirmed'"
            )['total'] ?? 0;

            $biayaOperasional = Database::fetchOne(
                "SELECT COALESCE(SUM(nominal), 0) as total FROM biaya_operasional"
            )['total'] ?? 0;

            $bayarHutang = Database::fetchOne(
                "SELECT COALESCE(SUM(hph.nominal_bayar), 0) as total 
                 FROM hutang_piutang_history hph 
                 JOIN hutang_piutang hp ON hph.id_hutang_piutang = hp.id 
                 WHERE hp.jenis = 'hutang'"
            )['total'] ?? 0;

            $keuanganKeluar = (float)$pembelianStok + (float)$biayaOperasional + (float)$bayarHutang;

        } else {
            $totalHutang = Database::fetchOne(
                "SELECT COALESCE(SUM(sisa_hutang), 0) as total FROM hutang_piutang
                 WHERE id_gudang = ? AND jenis = 'hutang' AND status != 'lunas'",
                [$idGudang]
            )['total'] ?? 0;

            $totalPiutang = Database::fetchOne(
                "SELECT COALESCE(SUM(sisa_hutang), 0) as total FROM hutang_piutang
                 WHERE id_gudang = ? AND jenis = 'piutang' AND status != 'lunas'",
                [$idGudang]
            )['total'] ?? 0;

            $overdueCount = Database::fetchOne(
                "SELECT COUNT(*) as cnt FROM hutang_piutang
                 WHERE id_gudang = ? AND status != 'lunas' AND jatuh_tempo < CURDATE()",
                [$idGudang]
            )['cnt'] ?? 0;

            // 💰 Keuangan Masuk: Total Penjualan Cash + Penerimaan Piutang
            $penjualanCash = Database::fetchOne(
                "SELECT COALESCE(SUM(total), 0) as total FROM nota 
                 WHERE id_gudang = ? AND pembayaran = 'cash' AND status = 'final'",
                [$idGudang]
            )['total'] ?? 0;

            $terimaPiutang = Database::fetchOne(
                "SELECT COALESCE(SUM(hph.nominal_bayar), 0) as total 
                 FROM hutang_piutang_history hph 
                 JOIN hutang_piutang hp ON hph.id_hutang_piutang = hp.id 
                 WHERE hp.id_gudang = ? AND hp.jenis = 'piutang'",
                [$idGudang]
            )['total'] ?? 0;

            $keuanganMasuk = (float)$penjualanCash + (float)$terimaPiutang;

            // 💸 Keuangan Keluar: Pembelian Stok + Biaya Operasional + Pembayaran Hutang
            $pembelianStok = Database::fetchOne(
                "SELECT COALESCE(SUM(total), 0) as total FROM stok_masuk 
                 WHERE id_gudang = ? AND status = 'confirmed'",
                [$idGudang]
            )['total'] ?? 0;

            $biayaOperasional = Database::fetchOne(
                "SELECT COALESCE(SUM(nominal), 0) as total FROM biaya_operasional 
                 WHERE id_gudang = ?",
                [$idGudang]
            )['total'] ?? 0;

            $bayarHutang = Database::fetchOne(
                "SELECT COALESCE(SUM(hph.nominal_bayar), 0) as total 
                 FROM hutang_piutang_history hph 
                 JOIN hutang_piutang hp ON hph.id_hutang_piutang = hp.id 
                 WHERE hp.id_gudang = ? AND hp.jenis = 'hutang'",
                [$idGudang]
            )['total'] ?? 0;

            $keuanganKeluar = (float)$pembelianStok + (float)$biayaOperasional + (float)$bayarHutang;
        }

        return [
            'total_hutang'    => (float)$totalHutang,
            'total_piutang'   => (float)$totalPiutang,
            'overdue_count'   => (int)$overdueCount,
            'keuangan_masuk'  => $keuanganMasuk,
            'keuangan_keluar' => $keuanganKeluar,
            'laba_rugi'       => $keuanganMasuk - $keuanganKeluar,
        ];
    }
}
