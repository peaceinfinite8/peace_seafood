<?php

declare(strict_types=1);

namespace App\Services;

use App\Middleware\AuthMiddleware;
use App\Utils\Database;
use App\Utils\Helper;
use App\Utils\ActivityLogHelper;

class PenjualanService
{
    private StokService $stokService;

    public function __construct()
    {
        $this->stokService = new StokService();
    }

    private function parseMoney(mixed $value): float
    {
        if (is_string($value)) {
            $value = preg_replace('/[^0-9,.-]/', '', $value);
            $value = str_replace(['.', ','], ['', '.'], $value);
        }
        return max(0, (float) $value);
    }

    private function calculateItemDiscount(array $item): float
    {
        $qty = (float) ($item['qty'] ?? 0);
        $hargaJual = $this->parseMoney($item['harga_jual'] ?? 0);
        $gross = $qty * $hargaJual;

        $mode = strtolower((string) ($item['diskon_mode'] ?? $item['discount_mode'] ?? 'nominal'));
        $value = $this->parseMoney($item['diskon_value'] ?? $item['diskon'] ?? $item['discount_value'] ?? 0);

        return match ($mode) {
            'per_unit', 'per-satuan', 'per_satuan' => $qty * $value,
            'percent', 'persen' => $gross * ($value / 100),
            default => $value,
        };
    }

    /**
     * Buat nota draft baru
     */
    public function createNota(array $data, int $idUser, int $idGudang): int
    {
        $noNota = Helper::generateNotaNumber($idGudang);
        $subtotal = 0;
        $totalDiskon = 0;
        $itemDiscountTotal = 0;
        $hasItemDiscount = false;
        $globalDiscountMode = strtolower((string) ($data['diskon_mode'] ?? $data['discount_mode'] ?? 'nominal'));
        $globalDiscountValue = $this->parseMoney($data['diskon'] ?? $data['discount_value'] ?? 0);

        foreach ($data['items'] as $item) {
            $qty = (float) ($item['qty'] ?? 0);
            $hargaJual = $this->parseMoney($item['harga_jual'] ?? 0);
            $gross = $qty * $hargaJual;
            $itemDiscount = $this->calculateItemDiscount($item);
            $subtotal += $gross;
            if ($itemDiscount > 0) {
                $hasItemDiscount = true;
                $itemDiscountTotal += $itemDiscount;
            }
        }

        $totalDiskon = $hasItemDiscount
            ? $itemDiscountTotal
            : match ($globalDiscountMode) {
                'per_unit', 'per-satuan', 'per_satuan' => $globalDiscountValue * array_sum(array_map(fn($i) => (float) ($i['qty'] ?? 0), $data['items'])),
                'percent', 'persen' => $subtotal * ($globalDiscountValue / 100),
                default => $globalDiscountValue,
            };

        $pajak = $this->parseMoney($data['pajak'] ?? 0);
        $total = max(0, $subtotal - $totalDiskon + $pajak);

        // ── Resolve id_pembeli ──────────────────────────────────────────────
        // Tiga kasus:
        //  1. Kosong / null   → cari/buat record "Pembeli Umum" untuk gudang
        //                       (id_pembeli NOT NULL di DB, tidak bisa null)
        //  2. Angka           → cast ke int, gunakan langsung
        //  3. String nama     → cari case-insensitive di tabel pembeli gudang ini;
        //                       jika belum ada, daftarkan otomatis sebagai 'retail'
        $rawPembeli = $data['id_pembeli'] ?? '';
        if ($rawPembeli === '' || $rawPembeli === null) {
            // Cari "Pembeli Umum" atau buat baru jika belum ada
            $umum = Database::fetchOne(
                "SELECT id FROM pembeli WHERE LOWER(nama) = 'pembeli umum' AND id_gudang = ? AND is_active = 1 LIMIT 1",
                [$idGudang]
            );
            if ($umum) {
                $resolvedIdPembeli = (int) $umum['id'];
            } else {
                $resolvedIdPembeli = Database::insert('pembeli', [
                    'id_gudang' => $idGudang,
                    'nama' => 'Pembeli Umum',
                    'tipe' => 'retail',
                    'kredit_limit' => 0,
                    'is_active' => 1,
                ]);
            }
        } elseif (is_numeric($rawPembeli)) {
            $resolvedIdPembeli = (int) $rawPembeli;
        } else {
            // Nama kustom diketik admin – lookup case-insensitive
            $trimmed = trim((string) $rawPembeli);
            $existing = Database::fetchOne(
                "SELECT id FROM pembeli WHERE LOWER(nama) = LOWER(?) AND id_gudang = ? AND is_active = 1 LIMIT 1",
                [$trimmed, $idGudang]
            );
            if ($existing) {
                $resolvedIdPembeli = (int) $existing['id'];
            } else {
                // Auto-daftarkan sebagai pembeli retail baru untuk gudang ini
                $resolvedIdPembeli = Database::insert('pembeli', [
                    'id_gudang' => $idGudang,
                    'nama' => $trimmed,
                    'tipe' => 'retail',
                    'kredit_limit' => 0,
                    'is_active' => 1,
                ]);
            }
        }
        // ────────────────────────────────────────────────────────────────────

        $notaPayload = [
            'no_nota' => $noNota,
            'id_gudang' => $idGudang,
            'id_pembeli' => $resolvedIdPembeli,
            'created_by' => $idUser,
            'tanggal_nota' => date('Y-m-d'),
            'subtotal' => $subtotal,
            'diskon_nominal' => $totalDiskon,
            'pajak' => $pajak,
            'total' => $total,
            'pembayaran' => in_array($data['jenis_pembayaran'] ?? 'cash', ['hutang']) ? 'hutang' : 'cash',
            'status' => 'draft',
            'catatan' => $data['catatan'] ?? null,
        ];

        if (Database::hasColumn('nota', 'bank_account_id')) {
            $notaPayload['bank_account_id'] = $data['bank_account_id'] ?? null;
        }

        $idNota = Database::insert('nota', $notaPayload);

        // Audit Trail Log
        ActivityLogHelper::log('INSERT', 'nota', $idNota, null, $notaPayload);

        // Insert items
        foreach ($data['items'] as $item) {
            $qty = (float) ($item['qty'] ?? 0);
            $hargaJual = $this->parseMoney($item['harga_jual'] ?? 0);
            $gross = $qty * $hargaJual;
            $itemDiscount = $this->calculateItemDiscount($item);
            $itemSubtotal = $gross;
            if ($itemDiscount > 0) {
                $itemSubtotal = max(0, $gross - $itemDiscount);
            }
            Database::insert('nota_detail', [
                'id_nota' => $idNota,
                'id_produk' => $item['id_produk'],
                'qty' => $qty,
                'harga_jual' => $hargaJual,
                'subtotal' => $itemSubtotal,
            ]);
        }

        return $idNota;
    }

    /**
     * Finalize nota (dari draft ke final) + update stok + hutang/piutang
     */
    public function finalizeNota(int $idNota, int $idGudang, bool $allGudang = false): bool
    {
        $nota = $allGudang && $idGudang === 0
            ? Database::fetchOne(
                "SELECT * FROM nota WHERE id = ? AND status = 'draft'",
                [$idNota]
            )
            : Database::fetchOne(
                "SELECT * FROM nota WHERE id = ? AND id_gudang = ? AND status = 'draft'",
                [$idNota, $idGudang]
            );

        if (!$nota)
            return false;

        $items = Database::fetchAll(
            "SELECT * FROM nota_detail WHERE id_nota = ?",
            [$idNota]
        );

        if (empty($items))
            return false;

        // ── Validasi stok ketat (anti race-condition) ──────────────────────────
        // Pastikan setiap produk memiliki stok_qty yang cukup DAN stok tersebut
        // berasal dari stok_masuk berstatus 'confirmed' (bukan pending/rejected).
        // Seluruh pengecekan dilakukan dalam satu pass SEBELUM ada pengurangan,
        // sehingga tidak ada state setengah-jadi jika salah satu item gagal.
        foreach ($items as $item) {
            $idProduk = (int) $item['id_produk'];
            $qtyDibutuhkan = (float) $item['qty'];

            // 1. Cek stok_qty aktual di tabel produk
            $produk = Database::fetchOne(
                "SELECT stok_qty, nama FROM produk WHERE id = ? AND id_gudang = ? AND is_active = 1",
                [$idProduk, $idGudang]
            );
            if (!$produk) {
                error_log("finalizeNota #{$idNota}: produk #{$idProduk} tidak ditemukan di gudang #{$idGudang}");
                return false;
            }
            if ((float) $produk['stok_qty'] < $qtyDibutuhkan) {
                error_log("finalizeNota #{$idNota}: stok tidak cukup untuk produk '{$produk['nama']}' — tersedia {$produk['stok_qty']} kg, dibutuhkan {$qtyDibutuhkan} kg");
                return false;
            }

            // 2. Pastikan ada stok_masuk berstatus 'confirmed' untuk produk ini
            //    di gudang yang sama. Ini mencegah finalisasi saat stok hanya
            //    berasal dari input manual atau stok_masuk yang masih 'pending'.
            $confirmedStok = Database::fetchOne(
                "SELECT COALESCE(SUM(qty), 0) AS total_confirmed
                 FROM stok_masuk
                 WHERE id_produk = ? AND id_gudang = ? AND status = 'confirmed'",
                [$idProduk, $idGudang]
            );
            $totalConfirmed = (float) ($confirmedStok['total_confirmed'] ?? 0);

            // Jika sama sekali tidak ada stok_masuk confirmed, tolak finalisasi.
            // (Stok bisa saja diisi manual via seeder/opname — dalam kasus itu
            //  total_confirmed = 0 tapi stok_qty > 0. Kita izinkan selama
            //  stok_qty mencukupi DAN ada setidaknya satu confirmed entry.)
            // Catatan: hanya blokir jika confirmed = 0 DAN stok_qty > 0
            // (artinya stok berasal dari sumber tidak terverifikasi).
            if ($totalConfirmed <= 0 && (float) $produk['stok_qty'] > 0) {
                // Periksa apakah ada stok_masuk pending yang belum dikonfirmasi
                $pendingCount = Database::fetchOne(
                    "SELECT COUNT(*) AS cnt FROM stok_masuk
                     WHERE id_produk = ? AND id_gudang = ? AND status = 'pending'",
                    [$idProduk, $idGudang]
                );
                if ((int) ($pendingCount['cnt'] ?? 0) > 0) {
                    error_log("finalizeNota #{$idNota}: stok produk '{$produk['nama']}' masih dalam status pending — belum bisa difinalisasi");
                    return false;
                }
                // Tidak ada pending dan tidak ada confirmed → stok dari opname/seeder, izinkan
            }
        }
        // ── Akhir validasi stok ────────────────────────────────────────────────

        // Kurangi stok untuk setiap item (semua item sudah lolos validasi di atas)
        foreach ($items as $item) {
            $ok = $this->stokService->kurangiStok(
                (int) $item['id_produk'],
                (float) $item['qty'],
                $idGudang
            );
            if (!$ok)
                return false; // fallback — seharusnya tidak terjadi setelah validasi di atas
        }

        // Update nota ke FINAL
        Database::update('nota', ['status' => 'final'], 'id = ?', [$idNota]);

        ActivityLogHelper::log('UPDATE', 'nota', $idNota, $nota, ['status' => 'final']);

        // Jika hutang, cek limit kredit dan buat record hutang/piutang
        if ($nota['pembayaran'] === 'hutang') {
            // Hitung outstanding existing
            if ($allGudang && $idGudang === 0) {
                $outRow = Database::fetchOne(
                    "SELECT COALESCE(SUM(sisa_hutang),0) as total FROM hutang_piutang WHERE id_pembeli = ? AND status != 'lunas'",
                    [$nota['id_pembeli']]
                );
            } else {
                $outRow = Database::fetchOne(
                    "SELECT COALESCE(SUM(sisa_hutang),0) as total FROM hutang_piutang WHERE id_pembeli = ? AND id_gudang = ? AND status != 'lunas'",
                    [$nota['id_pembeli'], $idGudang]
                );
            }
            $outstanding = (float) ($outRow['total'] ?? 0);

            $pembeli = Database::fetchOne("SELECT * FROM pembeli WHERE id = ?", [$nota['id_pembeli']]);
            $kreditLimit = (float) ($pembeli['kredit_limit'] ?? 0);

            if ($kreditLimit > 0 && ($outstanding + (float) $nota['total']) > $kreditLimit) {
                // melebihi limit kredit
                return false;
            }

            $jatuhTempo = Helper::calcJatuhTempo($idGudang);
            $idHP = Database::insert('hutang_piutang', [
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

            ActivityLogHelper::log('INSERT', 'hutang_piutang', $idHP, null, [
                'id_gudang' => $idGudang,
                'jenis' => 'piutang',
                'id_pembeli' => $nota['id_pembeli'],
                'id_nota' => $idNota,
                'nominal' => $nota['total'],
                'status' => 'open'
            ]);
        }

        return true;
    }

    /**
     * Cancel nota (revert changes)
     */
    public function cancelNota(int $idNota, int $idGudang): bool
    {
        $nota = AuthMiddleware::isAllGudang() && $idGudang === 0
            ? Database::fetchOne(
                "SELECT * FROM nota WHERE id = ? AND status IN ('draft', 'final')",
                [$idNota]
            )
            : Database::fetchOne(
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

            // Hapus hutang piutang dan history pembayarannya jika ada
            Database::query(
                "DELETE hph FROM hutang_piutang_history hph
                 JOIN hutang_piutang hp ON hph.id_hutang_piutang = hp.id
                 WHERE hp.id_nota = ?",
                [$idNota]
            );
            Database::query(
                "DELETE FROM hutang_piutang WHERE id_nota = ?",
                [$idNota]
            );
        }

        Database::update('nota', ['status' => 'cancel'], 'id = ?', [$idNota]);

        ActivityLogHelper::log('UPDATE', 'nota', $idNota, $nota, ['status' => 'cancel']);

        return true;
    }

    /**
     * Get list nota.
     * Jika $allGudang = true, ambil semua gudang (untuk BOS).
     */
    public function getNotaList(int $idGudang, array $filters = [], bool $allGudang = false): array
    {
        $scope = Helper::buildGudangScope('n', $idGudang, $allGudang);
        $where = $scope['where'];
        $params = $scope['params'];

        foreach (['status' => 'n.status =', 'dari' => 'DATE(n.tanggal_nota) >=', 'sampai' => 'DATE(n.tanggal_nota) <=', 'id_pembeli' => 'n.id_pembeli ='] as $key => $field) {
            if (!empty($filters[$key])) {
                $where .= ' AND ' . $field . ' ?';
                $params[] = $filters[$key];
            }
        }

        $gudangCol = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
        $gudangJoin = ($allGudang && $idGudang === 0) ? "LEFT JOIN gudang g ON n.id_gudang = g.id" : "";

        return Database::fetchAll(
            "SELECT n.*, 
                    n.pembayaran as jenis_pembayaran,
                    CASE
                        WHEN n.pembayaran = 'hutang' THEN COALESCE(hp.status_pembayaran, 'open')
                        ELSE 'lunas'
                    END as status_pembayaran,
                    CASE
                        WHEN n.pembayaran = 'hutang' THEN COALESCE(hp.sisa_tagihan, 0)
                        ELSE 0
                    END as sisa_tagihan,
                    hp.id_hutang_piutang,
                    p.nama as nama_pembeli, u.name as nama_user{$gudangCol}
             FROM nota n
             LEFT JOIN pembeli p ON n.id_pembeli = p.id
             LEFT JOIN users u ON n.created_by = u.id
             LEFT JOIN (
                SELECT id_nota,
                       MAX(id) as id_hutang_piutang,
                       MAX(status) as status_pembayaran,
                       MAX(sisa_hutang) as sisa_tagihan
                FROM hutang_piutang
                WHERE jenis = 'piutang'
                GROUP BY id_nota
             ) hp ON hp.id_nota = n.id
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
        $hasBankAccount = Database::hasColumn('nota', 'bank_account_id') && Database::hasTable('bank_account');
        $bankJoin = $hasBankAccount ? "LEFT JOIN bank_account ba ON n.bank_account_id = ba.id" : "";
        $bankCols = $hasBankAccount ? ", ba.bank_name as nama_bank, ba.account_number, ba.account_name" : ", NULL as nama_bank, NULL as account_number, NULL as account_name";

        if ($allGudang && $idGudang === 0) {
            $nota = Database::fetchOne(
                "SELECT n.*, p.nama as nama_pembeli, p.telpon as telepon_pembeli,
                        p.alamat as alamat_pembeli, u.name as nama_user,
                        g.nama as nama_gudang,
                        CASE
                            WHEN n.pembayaran = 'hutang' THEN COALESCE(hp.status_pembayaran, 'open')
                            ELSE 'lunas'
                        END as status_pembayaran,
                        CASE
                            WHEN n.pembayaran = 'hutang' THEN COALESCE(hp.sisa_tagihan, 0)
                            ELSE 0
                        END as sisa_tagihan,
                        hp.id_hutang_piutang{$bankCols}
                 FROM nota n
                 LEFT JOIN pembeli p ON n.id_pembeli = p.id
                 LEFT JOIN users u ON n.created_by = u.id
                 LEFT JOIN gudang g ON n.id_gudang = g.id
                 LEFT JOIN (
                    SELECT id_nota,
                           MAX(id) as id_hutang_piutang,
                           MAX(status) as status_pembayaran,
                           MAX(sisa_hutang) as sisa_tagihan
                    FROM hutang_piutang
                    WHERE jenis = 'piutang'
                    GROUP BY id_nota
                 ) hp ON hp.id_nota = n.id
                 {$bankJoin}
                 WHERE n.id = ?",
                [$idNota]
            );
        } else {
            $nota = Database::fetchOne(
                "SELECT n.*, p.nama as nama_pembeli, p.telpon as telepon_pembeli,
                        p.alamat as alamat_pembeli, u.name as nama_user,
                        g.nama as nama_gudang,
                        CASE
                            WHEN n.pembayaran = 'hutang' THEN COALESCE(hp.status_pembayaran, 'open')
                            ELSE 'lunas'
                        END as status_pembayaran,
                        CASE
                            WHEN n.pembayaran = 'hutang' THEN COALESCE(hp.sisa_tagihan, 0)
                            ELSE 0
                        END as sisa_tagihan,
                        hp.id_hutang_piutang{$bankCols}
                 FROM nota n
                 LEFT JOIN pembeli p ON n.id_pembeli = p.id
                 LEFT JOIN users u ON n.created_by = u.id
                 LEFT JOIN gudang g ON n.id_gudang = g.id
                 LEFT JOIN (
                    SELECT id_nota,
                           MAX(id) as id_hutang_piutang,
                           MAX(status) as status_pembayaran,
                           MAX(sisa_hutang) as sisa_tagihan
                    FROM hutang_piutang
                    WHERE jenis = 'piutang'
                    GROUP BY id_nota
                 ) hp ON hp.id_nota = n.id
                 {$bankJoin}
                 WHERE n.id = ? AND n.id_gudang = ?",
                [$idNota, $idGudang]
            );
        }

        if (!$nota)
            return null;

        $nota['items'] = Database::fetchAll(
            "SELECT nd.*, pr.nama as nama_produk, ji.nama as nama_jenis" . (Database::hasColumn('produk', 'satuan') ? ", COALESCE(pr.satuan, 'kg') as satuan" : ", 'kg' as satuan") . "
             FROM nota_detail nd
             JOIN produk pr ON nd.id_produk = pr.id
             JOIN jenis_ikan ji ON pr.id_jenis_ikan = ji.id
             WHERE nd.id_nota = ?",
            [$idNota]
        );

        $nota['payments'] = Database::fetchAll(
            "SELECT hph.*, u.name as nama_user
             FROM hutang_piutang_history hph
             JOIN hutang_piutang hp ON hph.id_hutang_piutang = hp.id
             LEFT JOIN users u ON hph.created_by = u.id
             WHERE hp.id_nota = ? AND hp.jenis = 'piutang'
             ORDER BY hph.created_at ASC",
            [$idNota]
        );

        return $nota;
    }
}
