<?php

declare(strict_types=1);

namespace App\Models;

use App\utils\Database;

class Nota extends Model
{
    protected static string $table = 'nota';

    public static function getList(int $idGudang, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = "WHERE n.id_gudang = ?";
        $params = [$idGudang];

        if (!empty($filters['status'])) {
            $where   .= " AND n.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['pembayaran'])) {
            $where   .= " AND n.pembayaran = ?";
            $params[] = $filters['pembayaran'];
        }
        if (!empty($filters['date_from'])) {
            $where   .= " AND n.tanggal_nota >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where   .= " AND n.tanggal_nota <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['id_pembeli'])) {
            $where   .= " AND n.id_pembeli = ?";
            $params[] = $filters['id_pembeli'];
        }
        if (!empty($filters['search'])) {
            $where   .= " AND (n.no_nota LIKE ? OR p.nama LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }

        $offset = ($page - 1) * $perPage;

        $rows = Database::fetchAll(
            "SELECT n.*, pb.nama as pembeli_nama, u.name as created_by_name
             FROM nota n
             JOIN pembeli pb ON pb.id = n.id_pembeli
             JOIN users u ON u.id = n.created_by
             {$where}
             ORDER BY n.created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        $total = (int)(Database::fetchOne(
            "SELECT COUNT(*) as cnt FROM nota n
             JOIN pembeli pb ON pb.id = n.id_pembeli
             {$where}",
            $params
        )['cnt'] ?? 0);

        return ['data' => $rows, 'total' => $total];
    }

    public static function getWithDetails(int $id): array|false
    {
        $nota = Database::fetchOne(
            "SELECT n.*, pb.nama as pembeli_nama, pb.telpon as pembeli_telpon,
                    pb.alamat as pembeli_alamat, g.nama as gudang_nama,
                    g.alamat as gudang_alamat, g.telpon as gudang_telpon,
                    u.name as created_by_name
             FROM nota n
             JOIN pembeli pb ON pb.id = n.id_pembeli
             JOIN gudang g ON g.id = n.id_gudang
             JOIN users u ON u.id = n.created_by
             WHERE n.id = ?",
            [$id]
        );

        if (!$nota) return false;

        $nota['items'] = Database::fetchAll(
            "SELECT nd.*, p.nama as produk_nama, j.nama as jenis_ikan_nama
             FROM nota_detail nd
             JOIN produk p ON p.id = nd.id_produk
             JOIN jenis_ikan j ON j.id = p.id_jenis_ikan
             WHERE nd.id_nota = ?",
            [$id]
        );

        return $nota;
    }

    public static function getTodaySummary(int $idGudang): array
    {
        return Database::fetchOne(
            "SELECT
                COUNT(*) as total_nota,
                COALESCE(SUM(total), 0) as total_nilai,
                COALESCE(SUM(CASE WHEN pembayaran='cash' THEN total ELSE 0 END), 0) as total_cash,
                COALESCE(SUM(CASE WHEN pembayaran='hutang' THEN total ELSE 0 END), 0) as total_hutang
             FROM nota
             WHERE id_gudang = ? AND DATE(created_at) = CURDATE() AND status = 'final'",
            [$idGudang]
        ) ?: ['total_nota' => 0, 'total_nilai' => 0, 'total_cash' => 0, 'total_hutang' => 0];
    }

    public static function getMonthlySummary(int $idGudang, int $year, int $month): array
    {
        return Database::fetchOne(
            "SELECT
                COUNT(*) as total_nota,
                COALESCE(SUM(total), 0) as total_nilai
             FROM nota
             WHERE id_gudang = ? AND YEAR(tanggal_nota) = ? AND MONTH(tanggal_nota) = ?
             AND status = 'final'",
            [$idGudang, $year, $month]
        ) ?: ['total_nota' => 0, 'total_nilai' => 0];
    }

    public static function getSalesTrend(int $idGudang, int $days = 7): array
    {
        return Database::fetchAll(
            "SELECT DATE(tanggal_nota) as tanggal,
                    COALESCE(SUM(total), 0) as total_nilai,
                    COUNT(*) as jumlah_nota
             FROM nota
             WHERE id_gudang = ?
             AND tanggal_nota >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             AND status = 'final'
             GROUP BY DATE(tanggal_nota)
             ORDER BY tanggal ASC",
            [$idGudang, $days]
        );
    }

    public static function getRecentTransactions(int $idGudang, int $limit = 10): array
    {
        return Database::fetchAll(
            "SELECT n.id, n.no_nota, n.tanggal_nota, n.total, n.pembayaran, n.status,
                    pb.nama as pembeli_nama
             FROM nota n
             JOIN pembeli pb ON pb.id = n.id_pembeli
             WHERE n.id_gudang = ? AND n.status = 'final'
             ORDER BY n.created_at DESC LIMIT ?",
            [$idGudang, $limit]
        );
    }
}
