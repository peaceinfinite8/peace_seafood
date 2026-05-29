<?php

declare(strict_types=1);

namespace App\Services;

use App\Middleware\AuthMiddleware;
use App\Utils\Database;
use App\Utils\Helper;

class ReturService
{
    /**
     * Buat retur baru (status PENDING)
     */
    public function createRetur(array $data, int $idUser, int $idGudang): int
    {
        return Database::insert('retur', [
            'id_gudang' => $idGudang,
            'id_produk' => $data['id_produk'] ?? null,
            'id_supplier' => $data['id_supplier'] ?? null,
            'id_pembeli' => $data['id_pembeli'] ?? null,
            'id_nota' => $data['id_nota'] ?? null,
            'tipe' => $data['tipe'],
            'qty' => $data['qty'] ?? null,
            'nominal' => $data['nominal'] ?? null,
            'alasan' => $data['alasan'],
            'foto_bukti' => $data['foto_bukti'] ?? null,
            'status' => 'pending',
            'catatan' => $data['catatan'] ?? null,
            'created_by' => $idUser,
        ]);
    }

    /**
     * Approve retur - proses sesuai tipe
     */
    public function approveRetur(int $idRetur, int $idGudang): bool
    {
        $retur = Database::fetchOne(
            "SELECT * FROM retur WHERE id = ? AND id_gudang = ? AND status = 'pending'",
            [$idRetur, $idGudang]
        );

        if (!$retur)
            return false;

        if ($retur['tipe'] === 'stok') {
            // Kurangi stok produk
            if ($retur['id_produk'] && $retur['qty']) {
                Database::query(
                    "UPDATE produk SET 
                        stok_qty = GREATEST(0, stok_qty - ?),
                        nilai_stok = GREATEST(0, stok_qty - ?) * harga_beli
                     WHERE id = ? AND id_gudang = ?",
                    [
                        (float) $retur['qty'],
                        (float) $retur['qty'],
                        (int) $retur['id_produk'],
                        $idGudang,
                    ]
                );
            }
        } elseif ($retur['tipe'] === 'piutang') {
            // Adjust hutang/piutang by modifying nominal instead of generated sisa_hutang
            if ($retur['id_nota'] && $retur['nominal']) {
                $hp = Database::fetchOne(
                    "SELECT * FROM hutang_piutang WHERE id_nota = ? AND status != 'lunas'",
                    [(int) $retur['id_nota']]
                );

                if ($hp) {
                    $returNominal = (float) $retur['nominal'];
                    $nominalBaru = max((float) $hp['nominal_bayar'], (float) $hp['nominal'] - $returNominal);
                    $status = ($nominalBaru - (float) $hp['nominal_bayar']) <= 0 ? 'lunas' : 'sebagian';

                    Database::update('hutang_piutang', [
                        'nominal' => $nominalBaru,
                        'status' => $status,
                    ], 'id = ?', [(int) $hp['id']]);

                    // Simpan history sesuai kolom tabel hutang_piutang_history
                    Database::insert('hutang_piutang_history', [
                        'id_hutang_piutang' => $hp['id'],
                        'nominal_bayar' => 0,
                        'metode_bayar' => 'retur',
                        'keterangan' => "Retur #{$idRetur}: {$retur['alasan']} (Nilai: Rp " . number_format($returNominal, 0, ',', '.') . ")",
                        'created_by' => AuthMiddleware::getAuthUserId() ?? (int) $retur['created_by'],
                    ]);
                }
            }
        }

        Database::update('retur', [
            'status' => 'approved',
            'approved_by' => AuthMiddleware::getAuthUserId(),
            'approved_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$idRetur]);

        return true;
    }

    /**
     * Reject retur
     */
    public function rejectRetur(int $idRetur, int $idGudang, string $alasan = ''): bool
    {
        return Database::update(
            'retur',
            [
                'status' => 'rejected',
                'catatan' => $alasan,
            ],
            'id = ? AND id_gudang = ? AND status = \'pending\'',
            [$idRetur, $idGudang]
        );
    }

    /**
     * Get list retur.
     * Jika $allGudang = true, ambil semua gudang (untuk BOS).
     */
    public function getReturList(int $idGudang, array $filters = [], bool $allGudang = false): array
    {
        $scope = Helper::buildGudangScope('r', $idGudang, $allGudang);
        $where = $scope['where'];
        $params = $scope['params'];

        foreach (['tipe' => 'r.tipe =', 'status' => 'r.status =', 'dari' => 'DATE(r.created_at) >='] as $key => $field) {
            if (!empty($filters[$key])) {
                $where .= ' AND ' . $field . ' ?';
                $params[] = $filters[$key];
            }
        }

        $gudangCol = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
        $gudangJoin = ($allGudang && $idGudang === 0) ? "LEFT JOIN gudang g ON r.id_gudang = g.id" : "";

        return Database::fetchAll(
            "SELECT r.*,
                    s.nama as nama_supplier,
                    p.nama as nama_pembeli,
                    pr.nama as nama_produk,
                    u.name as nama_user,
                    au.name as approved_by_nama{$gudangCol}
             FROM retur r
             LEFT JOIN supplier s ON r.id_supplier = s.id
             LEFT JOIN pembeli p ON r.id_pembeli = p.id
             LEFT JOIN produk pr ON r.id_produk = pr.id
                  LEFT JOIN users u ON r.created_by = u.id
             LEFT JOIN users au ON r.approved_by = au.id
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
        return Database::fetchOne(
            "SELECT r.*,
                    s.nama as nama_supplier,
                    p.nama as nama_pembeli,
                    pr.nama as nama_produk,
                    u.name as nama_user,
                    au.name as approved_by_nama
             FROM retur r
             LEFT JOIN supplier s ON r.id_supplier = s.id
             LEFT JOIN pembeli p ON r.id_pembeli = p.id
             LEFT JOIN produk pr ON r.id_produk = pr.id
             LEFT JOIN users u ON r.created_by = u.id
             LEFT JOIN users au ON r.approved_by = au.id
             WHERE r.id = ? AND r.id_gudang = ?",
            [$idRetur, $idGudang]
        );
    }
}
