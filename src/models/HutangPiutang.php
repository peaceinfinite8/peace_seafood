<?php

declare(strict_types=1);

namespace App\models;

use App\utils\Database;

class HutangPiutang extends Model
{
    protected static string $table = 'hutang_piutang';

    public static function getList(int $idGudang, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = "WHERE hp.id_gudang = ?";
        $params = [$idGudang];

        if (!empty($filters['jenis'])) {
            $where   .= " AND hp.jenis = ?";
            $params[] = $filters['jenis'];
        }
        if (!empty($filters['status'])) {
            $where   .= " AND hp.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['id_supplier'])) {
            $where   .= " AND hp.id_supplier = ?";
            $params[] = $filters['id_supplier'];
        }
        if (!empty($filters['id_pembeli'])) {
            $where   .= " AND hp.id_pembeli = ?";
            $params[] = $filters['id_pembeli'];
        }

        $offset = ($page - 1) * $perPage;

        $rows = Database::fetchAll(
            "SELECT hp.*,
                    s.nama as supplier_nama,
                    pb.nama as pembeli_nama,
                    n.no_nota,
                    u.name as created_by_name
             FROM hutang_piutang hp
             LEFT JOIN supplier s ON s.id = hp.id_supplier
             LEFT JOIN pembeli pb ON pb.id = hp.id_pembeli
             LEFT JOIN nota n ON n.id = hp.id_nota
             JOIN users u ON u.id = hp.created_by
             {$where}
             ORDER BY hp.jatuh_tempo ASC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        $total = (int)(Database::fetchOne(
            "SELECT COUNT(*) as cnt FROM hutang_piutang hp {$where}",
            $params
        )['cnt'] ?? 0);

        return ['data' => $rows, 'total' => $total];
    }

    public static function getSummary(int $idGudang): array
    {
        return Database::fetchOne(
            "SELECT
                COALESCE(SUM(CASE WHEN jenis='hutang' AND status!='lunas' THEN sisa_hutang ELSE 0 END), 0) as total_hutang,
                COALESCE(SUM(CASE WHEN jenis='piutang' AND status!='lunas' THEN sisa_hutang ELSE 0 END), 0) as total_piutang,
                COUNT(CASE WHEN jenis='hutang' AND status!='lunas' THEN 1 END) as count_hutang,
                COUNT(CASE WHEN jenis='piutang' AND status!='lunas' THEN 1 END) as count_piutang
             FROM hutang_piutang
             WHERE id_gudang = ?",
            [$idGudang]
        ) ?: ['total_hutang' => 0, 'total_piutang' => 0, 'count_hutang' => 0, 'count_piutang' => 0];
    }

    public static function getJatuhTempoBesok(int $idGudang, int $days = 3): array
    {
        return Database::fetchAll(
            "SELECT hp.*,
                    s.nama as supplier_nama,
                    pb.nama as pembeli_nama
             FROM hutang_piutang hp
             LEFT JOIN supplier s ON s.id = hp.id_supplier
             LEFT JOIN pembeli pb ON pb.id = hp.id_pembeli
             WHERE hp.id_gudang = ? AND hp.status != 'lunas'
             AND hp.jatuh_tempo BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
             ORDER BY hp.jatuh_tempo ASC",
            [$idGudang, $days]
        );
    }

    public static function getTopHutang(int $idGudang, int $limit = 5): array
    {
        return Database::fetchAll(
            "SELECT hp.id_supplier, s.nama as supplier_nama,
                    SUM(hp.sisa_hutang) as total_hutang
             FROM hutang_piutang hp
             JOIN supplier s ON s.id = hp.id_supplier
             WHERE hp.id_gudang = ? AND hp.jenis = 'hutang' AND hp.status != 'lunas'
             GROUP BY hp.id_supplier, s.nama
             ORDER BY total_hutang DESC LIMIT ?",
            [$idGudang, $limit]
        );
    }

    public static function bayar(int $id, int $nominalBayar, string $metodeBayar = '', string $keterangan = '', int $createdBy = 0): bool
    {
        Database::beginTransaction();
        try {
            // Update hutang_piutang
            Database::execute(
                "UPDATE hutang_piutang SET
                    nominal_bayar = nominal_bayar + ?,
                    status = CASE
                        WHEN (nominal_bayar + ?) >= nominal THEN 'lunas'
                        WHEN (nominal_bayar + ?) > 0 THEN 'sebagian'
                        ELSE 'open'
                    END,
                    updated_at = NOW()
                 WHERE id = ?",
                [$nominalBayar, $nominalBayar, $nominalBayar, $id]
            );

            // Insert history
            Database::execute(
                "INSERT INTO hutang_piutang_history (id_hutang_piutang, nominal_bayar, metode_bayar, keterangan, created_by)
                 VALUES (?, ?, ?, ?, ?)",
                [$id, $nominalBayar, $metodeBayar, $keterangan, $createdBy]
            );

            Database::commit();
            return true;
        } catch (\Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }
}
