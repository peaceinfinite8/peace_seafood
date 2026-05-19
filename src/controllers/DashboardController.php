<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\StokService;
use App\Services\KeuanganService;
use App\Middleware\AuthMiddleware;
use App\Utils\Database;
use App\Utils\Response;

class DashboardController
{
    public function index(): void
    {
        $user     = AuthMiddleware::getAuthUser();
        $idGudang = $this->resolveGudang($user);
        $isBos    = $user['role'] === 'bos';

        $stokService     = new StokService();
        $keuanganService = new KeuanganService();

        // Stok summary — BOS tanpa filter gudang ambil semua
        $inventory      = $stokService->getInventory($idGudang, $isBos);
        $totalProduk    = count($inventory);
        $totalStokValue = array_sum(array_column($inventory, 'stok_value'));
        $lowStockItems  = array_filter($inventory, fn($i) => $i['is_low_stock']);
        $lowStockCount  = count($lowStockItems);

        // Penjualan hari ini
        $today = date('Y-m-d');
        if ($isBos && $idGudang === 0) {
            $notaHariIni = Database::fetchAll(
                "SELECT * FROM nota WHERE DATE(tanggal_nota) = ? AND status = 'final'",
                [$today]
            );
        } else {
            $notaHariIni = Database::fetchAll(
                "SELECT * FROM nota WHERE id_gudang = ? AND DATE(tanggal_nota) = ? AND status = 'final'",
                [$idGudang, $today]
            );
        }
        $penjualanHariIni = array_sum(array_column($notaHariIni, 'total'));

        // Pending timbangan
        $pendingTimbang = count($stokService->getPendingTimbangan($idGudang, $isBos));

        // Keuangan summary
        $keuSummary = $keuanganService->getSummary($idGudang, $isBos);

        // Chart data - penjualan 7 hari
        $salesChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            if ($isBos && $idGudang === 0) {
                $total = Database::fetchOne(
                    "SELECT COALESCE(SUM(total), 0) as total FROM nota WHERE DATE(tanggal_nota) = ? AND status = 'final'",
                    [$date]
                )['total'] ?? 0;
            } else {
                $total = Database::fetchOne(
                    "SELECT COALESCE(SUM(total), 0) as total FROM nota WHERE id_gudang = ? AND DATE(tanggal_nota) = ? AND status = 'final'",
                    [$idGudang, $date]
                )['total'] ?? 0;
            }
            $salesChart[] = (float)$total;
        }

        // Chart data - stok per jenis ikan
        if ($isBos && $idGudang === 0) {
            // BOS: semua gudang, tanpa filter id_gudang
            $stokByJenis = Database::fetchAll(
                "SELECT ji.nama, COALESCE(SUM(p.stok_qty), 0) as total
                 FROM jenis_ikan ji
                 LEFT JOIN produk p ON p.id_jenis_ikan = ji.id
                 GROUP BY ji.id, ji.nama
                 HAVING total > 0",
                []
            );
        } else {
            $stokByJenis = Database::fetchAll(
                "SELECT ji.nama, COALESCE(SUM(p.stok_qty), 0) as total
                 FROM jenis_ikan ji
                 LEFT JOIN produk p ON p.id_jenis_ikan = ji.id AND p.id_gudang = ?
                 GROUP BY ji.id, ji.nama
                 HAVING total > 0",
                [$idGudang]
            );
        }

        Response::success([
            'total_produk'         => $totalProduk,
            'total_stok_value'     => $totalStokValue,
            'low_stock_count'      => $lowStockCount,
            'penjualan_hari_ini'   => $penjualanHariIni,
            'nota_hari_ini'        => count($notaHariIni),
            'pending_timbang'      => $pendingTimbang,
            'total_piutang'        => $keuSummary['total_piutang'],
            'total_hutang'         => $keuSummary['total_hutang'],
            'overdue_count'        => $keuSummary['overdue_count'],
            'sales_chart'          => $salesChart,
            'stok_chart'           => [
                'labels' => array_column($stokByJenis, 'nama'),
                'values' => array_map(fn($x) => (float)$x['total'], $stokByJenis),
            ],
        ]);
    }

    private function resolveGudang(array $user): int
    {
        if ($user['role'] === 'bos') {
            return !empty($_GET['id_gudang']) ? (int)$_GET['id_gudang'] : 0;
        }
        return (int)($user['id_gudang'] ?? 0);
    }
}
