<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\Database;

class ReturService
{
    /**
     * Buat retur baru (status PENDING)
     */
    public function create(int $idGudang, int $idUser, array $data): int
    {
        return Database::insert('retur', [
            'id_gudang' => $idGudang,
            'tipe' => $data['tipe'],
            'id_supplier' => $data['id_supplier'] ?? null,
            'id_pembeli' => $data['id_pembeli'] ?? null,
            'id_nota' => $data['nota_id'] ?? $data['id_nota'] ?? null,
            'id_produk' => $data['id_produk'] ?? null,
            'qty' => $data['qty'] ?? null,
            'nominal' => $data['nominal'] ?? null,
            'alasan' => $data['alasan'],
            'foto_bukti' => null,
            'status' => 'pending',
            'catatan' => $data['keterangan'] ?? $data['catatan'] ?? null,
            'created_by' => $idUser,
        ]);
    }

    /**
     * Approve retur - proses sesuai tipe
     * 
     * RETUR STOK: Menambah kembali stok ke inventory (barang dikembalikan)
     * RETUR PIUTANG: Mengurangi hutang/piutang (adjustment finansial)
     */
    public function approve(int $idRetur, int $idGudang, int $idUser = 0): bool
    {
        $sql = "SELECT * FROM retur WHERE id = ?";
        $params = [$idRetur];
        if ($idGudang > 0) {
            $sql .= " AND id_gudang = ?";
            $params[] = $idGudang;
        }
        $sql .= " AND status = 'pending'";

        $retur = Database::fetchOne($sql, $params);

        if (!$retur)
            return false;

        if ($retur['tipe'] === 'stok') {
            /**
             * RETUR STOK: Tambah kembali ke inventory
             * 
             * Formula:
             * - Qty Baru = Qty Lama + Qty Retur
             * - Nilai Tambahan = Qty Retur × Harga Beli Rata-rata
             * - Nilai Stok Baru = Nilai Stok Lama + Nilai Tambahan
             * 
             * Contoh:
             * Stok: 120 kg @ Rp 53.333/kg = Rp 6.400.000
             * Retur: 10 kg (barang rusak dikembalikan)
             * Nilai tambahan: 10 × Rp 53.333 = Rp 533.330
             * Stok baru: 130 kg = Rp 6.400.000 + Rp 533.330 = Rp 6.933.330
             */
            if ($retur['id_produk'] && $retur['qty']) {
                $produk = Database::fetchOne(
                    "SELECT * FROM produk WHERE id = ? AND id_gudang = ?",
                    [(int) $retur['id_produk'], $idGudang]
                );

                if ($produk) {
                    $qtyRetur = (float) $retur['qty'];
                    $stokLama = (float) $produk['stok_qty'];
                    $nilaiStokLama = (float) $produk['nilai_stok'];
                    $hargaRataRata = (float) $produk['harga_beli'];

                    // Tambah stok dan nilai
                    $stokBaru = $stokLama + $qtyRetur;
                    $nilaiTambahan = $qtyRetur * $hargaRataRata;
                    $nilaiStokBaru = $nilaiStokLama + $nilaiTambahan;

                    Database::update('produk', [
                        'stok_qty' => $stokBaru,
                        'nilai_stok' => $nilaiStokBaru,
                        // harga_beli tetap (tidak berubah)
                    ], 'id = ?', [(int) $retur['id_produk']]);
                }
            }
        } elseif ($retur['tipe'] === 'piutang') {
            /**
             * RETUR PIUTANG: Kurangi hutang/piutang
             * 
             * Formula:
             * - Nominal Baru = Nominal Lama - Nominal Retur
             * - Sisa Hutang Baru = Sisa Hutang Lama - Nominal Retur
             * - Status = 'lunas' jika Sisa Hutang <= 0
             *          = 'sebagian' jika Sudah Ada Pembayaran
             *          = 'open' jika Belum Ada Pembayaran
             * 
             * Contoh:
             * Hutang awal: Rp 10.000.000
             * Sudah bayar: Rp 3.000.000
             * Sisa hutang: Rp 7.000.000
             * Retur: Rp 2.000.000 (potongan kualitas)
             * 
             * Nominal baru: Rp 10.000.000 - Rp 2.000.000 = Rp 8.000.000
             * Sisa hutang baru: Rp 7.000.000 - Rp 2.000.000 = Rp 5.000.000
             * Status: 'sebagian' (karena sudah ada pembayaran Rp 3.000.000)
             */
            if ($retur['id_nota'] && $retur['nominal']) {
                $hp = Database::fetchOne(
                    "SELECT * FROM hutang_piutang WHERE id_nota = ? AND status != 'lunas' AND status != 'cancelled'",
                    [(int) $retur['id_nota']]
                );

                if ($hp) {
                    $nominalLama = (float) $hp['nominal'];
                    $sisaHutangLama = (float) ($hp['sisa_hutang'] ?? $nominalLama);
                    $nominalBayar = (float) ($hp['nominal_bayar'] ?? 0);
                    $nominalRetur = (float) $retur['nominal'];

                    // Kurangi nominal (nominal_bayar tidak berubah). sisa_hutang adalah GENERATED (nominal - nominal_bayar).
                    $nominalBaru = max(0, $nominalLama - $nominalRetur);

                    // Tentukan status berdasarkan sisa_hutang baru (nominal - nominal_bayar)
                    $nominalBayarExisting = (float) $nominalBayar;
                    $sisaHutangBaru = max(0, $nominalBaru - $nominalBayarExisting);
                    if ($sisaHutangBaru <= 0) {
                        $status = 'lunas';
                    } elseif ($nominalBayarExisting > 0) {
                        $status = 'sebagian';
                    } else {
                        $status = 'open';
                    }

                    Database::update('hutang_piutang', [
                        'nominal' => $nominalBaru,
                        'status' => $status,
                    ], 'id = ?', [(int) $hp['id']]);

                    // Simpan history retur (gunakan kolom yang ada pada schema)
                    Database::insert('hutang_piutang_history', [
                        'id_hutang_piutang' => $hp['id'],
                        'nominal_bayar' => $nominalRetur * -1, // negative to indicate reduction
                        'metode_bayar' => null,
                        'keterangan' => "Retur #{$idRetur}: {$retur['alasan']}",
                        'created_by' => $idUser,
                    ]);
                }
            }
        }

        Database::update('retur', [
            'status' => 'approved',
            'approved_by' => $idUser ?: null,
            'approved_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$idRetur]);

        return true;
    }

    /**
     * Reject retur
     */
    public function reject(int $idRetur, int $idGudang, string $alasan = ''): bool
    {
        $condition = "id = ?";
        $params = [$idRetur];
        if ($idGudang > 0) {
            $condition .= " AND id_gudang = ?";
            $params[] = $idGudang;
        }
        $condition .= " AND status = 'pending'";

        return Database::update(
            'retur',
            [
                'status' => 'rejected',
                'catatan' => $alasan,
            ],
            $condition,
            $params
        );
    }

    /**
     * Get list retur.
     * Jika $allGudang = true, ambil semua gudang (untuk BOS).
     */
    public function getReturList(int $idGudang, array $filters = [], bool $allGudang = false): array
    {
        if ($allGudang && $idGudang === 0) {
            $where = "1=1";
            $params = [];
        } else {
            $where = "r.id_gudang = ?";
            $params = [$idGudang];
        }

        if (!empty($filters['tipe'])) {
            $where .= " AND r.tipe = ?";
            $params[] = $filters['tipe'];
        }
        if (!empty($filters['status'])) {
            $where .= " AND r.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['dari'])) {
            $where .= " AND DATE(r.created_at) >= ?";
            $params[] = $filters['dari'];
        }

        $gudangCol = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
        $gudangJoin = ($allGudang && $idGudang === 0) ? "LEFT JOIN gudang g ON r.id_gudang = g.id" : "";

        return Database::fetchAll(
            "SELECT r.*,
                    s.nama as nama_supplier,
                    p.nama as nama_pembeli,
                    pr.nama as nama_produk,
                    u.name as nama_user,
                    ub.name as approved_by_nama{$gudangCol}
             FROM retur r
             LEFT JOIN supplier s ON r.id_supplier = s.id
             LEFT JOIN pembeli p ON r.id_pembeli = p.id
             LEFT JOIN produk pr ON r.id_produk = pr.id
             LEFT JOIN users u ON r.created_by = u.id
             LEFT JOIN users ub ON r.approved_by = ub.id
             {$gudangJoin}
             WHERE {$where}
             ORDER BY r.created_at DESC",
            $params
        );
    }

    /**
     * Get detail retur
     */
    public function getReturDetail(int $idRetur, int $idGudang): ?array
    {
        $sql = "SELECT r.*,
                    s.nama as nama_supplier,
                    p.nama as nama_pembeli,
                    pr.nama as nama_produk,
                    u.name as nama_user,
                    ub.name as approved_by_nama
             FROM retur r
             LEFT JOIN supplier s ON r.id_supplier = s.id
             LEFT JOIN pembeli p ON r.id_pembeli = p.id
             LEFT JOIN produk pr ON r.id_produk = pr.id
             LEFT JOIN users u ON r.created_by = u.id
             LEFT JOIN users ub ON r.approved_by = ub.id
             WHERE r.id = ?";
        $params = [$idRetur];
        if ($idGudang > 0) {
            $sql .= " AND r.id_gudang = ?";
            $params[] = $idGudang;
        }

        return Database::fetchOne($sql, $params);
    }
}
