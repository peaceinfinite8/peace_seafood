<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\Database;

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

    /**
     * Buat hutang/piutang manual
     */
    public function createHutangPiutang(array $data, int $idGudang): int
    {
        return Database::insert('hutang_piutang', [
            'id_gudang'   => $idGudang,
            'jenis'       => $data['jenis'],      // 'hutang' atau 'piutang'
            'id_supplier' => $data['id_supplier'] ?? null,
            'id_pembeli'  => $data['id_pembeli'] ?? null,
            'nominal'     => $data['nominal'],
            'sisa_hutang' => $data['nominal'],
            'jatuh_tempo' => $data['jatuh_tempo'] ?? null,
            'catatan'     => $data['catatan'] ?? null,
            'status'      => 'open',
        ]);
    }

    /**
     * Input pembayaran hutang/piutang.
     * Jika $allGudang = true, tidak filter by id_gudang (untuk BOS).
     * 
     * Formula:
     * - Sisa Hutang Baru = Sisa Hutang Lama - Nominal Bayar
     * - Total Bayar = Total Bayar Lama + Nominal Bayar
     * - Status = 'lunas' jika Sisa Hutang <= 0
     *          = 'sebagian' jika Total Bayar > 0 dan Sisa Hutang > 0
     *          = 'open' jika Total Bayar = 0
     * 
     * Contoh:
     * Hutang: Rp 10.000.000
     * Sudah bayar: Rp 3.000.000
     * Sisa: Rp 7.000.000
     * Bayar sekarang: Rp 4.000.000
     * 
     * Total bayar baru: Rp 3.000.000 + Rp 4.000.000 = Rp 7.000.000
     * Sisa hutang baru: Rp 7.000.000 - Rp 4.000.000 = Rp 3.000.000
     * Status: 'sebagian' (masih ada sisa Rp 3.000.000)
     */
    public function bayar(array $data, int $idGudang, bool $allGudang = false): bool
    {
        if ($allGudang && $idGudang === 0) {
            $hp = Database::fetchOne(
                "SELECT * FROM hutang_piutang WHERE id = ? AND status != 'lunas' AND status != 'cancelled'",
                [(int)$data['id_hutang_piutang']]
            );
        } else {
            $hp = Database::fetchOne(
                "SELECT * FROM hutang_piutang WHERE id = ? AND id_gudang = ? AND status != 'lunas' AND status != 'cancelled'",
                [(int)$data['id_hutang_piutang'], $idGudang]
            );
        }

        if (!$hp) return false;

        $nominalBayar = (float)$data['nominal_bayar'];
        $sisaHutangLama = (float)($hp['sisa_hutang'] ?? $hp['nominal']);
        
        // Validasi: nominal bayar tidak boleh lebih dari sisa hutang
        if ($nominalBayar > $sisaHutangLama) return false;

        $nominalBayarLama = (float)($hp['nominal_bayar'] ?? 0);
        $sisaBaru = $sisaHutangLama - $nominalBayar;
        $totalBayarBaru = $nominalBayarLama + $nominalBayar;
        
        // Tentukan status
        if ($sisaBaru <= 0) {
            $status = 'lunas';
        } elseif ($totalBayarBaru > 0) {
            $status = 'sebagian';
        } else {
            $status = 'open';
        }

        // Update hutang piutang
        Database::update('hutang_piutang', [
            'sisa_hutang' => max(0, $sisaBaru),
            'nominal_bayar' => $totalBayarBaru,
            'status' => $status,
        ], 'id = ?', [(int)$hp['id']]);

        // Simpan history pembayaran
        Database::insert('hutang_piutang_history', [
            'id_hutang_piutang' => $hp['id'],
            'tipe' => 'bayar',
            'nominal' => $nominalBayar,
            'tanggal_bayar' => $data['tanggal_bayar'] ?? date('Y-m-d'),
            'catatan' => $data['catatan'] ?? null,
            'created_by' => $data['created_by'] ?? 0,
        ]);

        return true;
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
        return Database::insert('biaya_operasional', [
            'id_gudang'   => $idGudang,
            'nama_biaya'  => $data['nama_biaya'],
            'nominal'     => $data['nominal'],
            'tanggal'     => $data['tanggal'] ?? date('Y-m-d'),
            'kategori'    => $data['kategori'] ?? 'operasional',
            'catatan'     => $data['catatan'] ?? null,
            'id_user'     => $idUser,
        ]);
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
        }

        return [
            'total_hutang'  => (float)$totalHutang,
            'total_piutang' => (float)$totalPiutang,
            'overdue_count' => (int)$overdueCount,
        ];
    }
}
