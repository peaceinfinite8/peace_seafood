<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\StokService;
use App\Services\KeuanganService;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Utils\Database;
use App\Utils\Response;

class DashboardController
{
    public function index(): void
    {
        RoleMiddleware::requirePermission('dashboard.view');
        $user     = AuthMiddleware::getAuthUser();
        $userRole = strtolower($user['role'] ?? '');

        if ($userRole === 'saas_owner') {
            $totalGudang = (int)(\App\Utils\Database::fetchOne("SELECT COUNT(*) as cnt FROM gudang")['cnt'] ?? 0);
            $todayStr = date('Y-m-d');
            // Aktif: masa sewa masih berlaku DAN status aktif
            $activeGudang = (int)(\App\Utils\Database::fetchOne(
                "SELECT COUNT(*) as cnt FROM gudang WHERE subscription_until >= ? AND status_langganan = 'aktif'",
                [$todayStr]
            )['cnt'] ?? 0);
            // Expired: masa sewa habis (bukan NULL) ATAU di-suspend
            $expiredGudang = (int)(\App\Utils\Database::fetchOne(
                "SELECT COUNT(*) as cnt FROM gudang WHERE (subscription_until IS NOT NULL AND subscription_until < ?) OR status_langganan = 'suspend'",
                [$todayStr]
            )['cnt'] ?? 0);
            // Belum onboarding: subscription_until masih NULL dan status aktif
            $pendingOnboarding = (int)(\App\Utils\Database::fetchOne(
                "SELECT COUNT(*) as cnt FROM gudang WHERE subscription_until IS NULL AND status_langganan = 'aktif'"
            )['cnt'] ?? 0);
            
            $totalUsers = (int)(\App\Utils\Database::fetchOne("SELECT COUNT(*) as cnt FROM users")['cnt'] ?? 0);
            $pendingInvites = (int)(\App\Utils\Database::fetchOne("SELECT COUNT(*) as cnt FROM users WHERE registration_status = 'pending_signup'")['cnt'] ?? 0);
            
            $totalSalesAll = (float)(\App\Utils\Database::fetchOne("SELECT SUM(total) as tot FROM nota WHERE status = 'final'")['tot'] ?? 0);
            $totalSalesCount = (int)(\App\Utils\Database::fetchOne("SELECT COUNT(*) as cnt FROM nota WHERE status = 'final'")['cnt'] ?? 0);

            // Fetch tenants list
            $tenants = \App\Utils\Database::fetchAll("
                SELECT g.id, g.nama as nama_gudang, g.kota, g.subscription_until, g.status_langganan,
                       (SELECT name FROM users WHERE id_gudang = g.id AND role = 'bos' LIMIT 1) as nama_bos,
                       (SELECT email FROM users WHERE id_gudang = g.id AND role = 'bos' LIMIT 1) as email_bos,
                       (SELECT COUNT(*) FROM users WHERE id_gudang = g.id) as user_count,
                       (SELECT COUNT(*) FROM nota WHERE id_gudang = g.id) as sales_count
                FROM gudang g
                ORDER BY g.id DESC
            ");

            // Fetch platform setting
            $wa = \App\Utils\Database::fetchOne("SELECT nilai FROM settings WHERE kunci = 'platform_developer_whatsapp' LIMIT 1");
            $whatsapp = $wa ? $wa['nilai'] : '628123456789';

            \App\Utils\Response::success([
                'is_saas_dashboard'   => true,
                'total_gudang'         => $totalGudang,
                'active_gudang'        => $activeGudang,
                'expired_gudang'       => $expiredGudang,
                'pending_onboarding'   => $pendingOnboarding,
                'total_users'          => $totalUsers,
                'pending_invites'      => $pendingInvites,
                'total_sales_all'      => $totalSalesAll,
                'total_sales_count'    => $totalSalesCount,
                'tenants'              => $tenants,
                'developer_whatsapp'   => $whatsapp
            ]);
            return;
        }

        $idGudang = $this->resolveGudang($user);
        $isBos    = in_array($userRole, ['bos', 'super_admin', 'saas_owner'], true);

        $stokService     = new StokService();
        $keuanganService = new KeuanganService();

        // Stok summary — BOS tanpa filter gudang ambil semua
        $inventory      = $stokService->getInventory($idGudang, $isBos);
        // Count unique product names for "jenis produk" and low-stock items per unique product
        $productNames    = array_map(fn($i) => $i['nama'], $inventory);
        $uniqueProducts  = array_values(array_unique($productNames));
        $totalProduk     = count($uniqueProducts);
        $totalStokValue  = array_sum(array_map(fn($i) => (float)($i['stok_value'] ?? 0), $inventory));
        $totalStokQty    = array_sum(array_map(fn($i) => (float)($i['stok_qty'] ?? 0), $inventory));
        $lowStockItems   = array_filter($inventory, fn($i) => !empty($i['is_low_stock']));
        $lowStockNames   = array_unique(array_map(fn($i) => $i['nama'], $lowStockItems));
        $lowStockCount   = count($lowStockNames);

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

        // Top 5 Produk Terlaris
        if ($isBos && $idGudang === 0) {
            $topProducts = Database::fetchAll(
                "SELECT p.nama, SUM(nd.qty) as total_qty, SUM(nd.subtotal) as total_nominal
                 FROM nota_detail nd
                 JOIN nota n ON nd.id_nota = n.id
                 JOIN produk p ON nd.id_produk = p.id
                 WHERE n.status = 'final'
                 GROUP BY nd.id_produk, p.nama
                 ORDER BY total_qty DESC
                 LIMIT 5"
            );
        } else {
            $topProducts = Database::fetchAll(
                "SELECT p.nama, SUM(nd.qty) as total_qty, SUM(nd.subtotal) as total_nominal
                 FROM nota_detail nd
                 JOIN nota n ON nd.id_nota = n.id
                 JOIN produk p ON nd.id_produk = p.id
                 WHERE n.id_gudang = ? AND n.status = 'final'
                 GROUP BY nd.id_produk, p.nama
                 ORDER BY total_qty DESC
                 LIMIT 5",
                [$idGudang]
            );
        }

        // Chart data - penjualan 7 hari & stok masuk 7 hari
        $salesLabels = [];
        $salesChart = [];
        $incomingStockChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $salesLabels[] = date('d M', strtotime($date));
            if ($isBos && $idGudang === 0) {
                $totalSales = Database::fetchOne(
                    "SELECT COALESCE(SUM(total), 0) as total FROM nota WHERE DATE(tanggal_nota) = ? AND status = 'final'",
                    [$date]
                )['total'] ?? 0;
            } else {
                $totalSales = Database::fetchOne(
                    "SELECT COALESCE(SUM(total), 0) as total FROM nota WHERE id_gudang = ? AND DATE(tanggal_nota) = ? AND status = 'final'",
                    [$idGudang, $date]
                )['total'] ?? 0;
            }
            $salesChart[] = (float)$totalSales;

            if ($isBos && $idGudang === 0) {
                $totalIncoming = Database::fetchOne(
                    "SELECT COALESCE(SUM(t.qty_actual), 0) as total 
                     FROM timbangan t
                     JOIN stok_masuk sm ON t.id_stok_masuk = sm.id
                     WHERE DATE(sm.created_at) = ?",
                    [$date]
                )['total'] ?? 0;
            } else {
                $totalIncoming = Database::fetchOne(
                    "SELECT COALESCE(SUM(t.qty_actual), 0) as total 
                     FROM timbangan t
                     JOIN stok_masuk sm ON t.id_stok_masuk = sm.id
                     WHERE sm.id_gudang = ? AND DATE(sm.created_at) = ?",
                    [$idGudang, $date]
                )['total'] ?? 0;
            }
            $incomingStockChart[] = (float)$totalIncoming;
        }

        // Chart data - stok per jenis ikan, derived from the same inventory source to keep BOS consistent.
        $stokByJenisMap = [];
        foreach ($inventory as $item) {
            $namaJenis = $item['nama_jenis'] ?? 'Tanpa Jenis';
            $stokByJenisMap[$namaJenis] = ($stokByJenisMap[$namaJenis] ?? 0) + (float)($item['stok_qty'] ?? 0);
        }
        $stokByJenis = [];
        foreach ($stokByJenisMap as $nama => $total) {
            if ($total > 0) {
                $stokByJenis[] = ['nama' => $nama, 'total' => $total];
            }
        }

        // Log Aktivitas Terkini (Audit Trail)
        $latestLogs = Database::fetchAll(
            "SELECT al.timestamp, al.action, al.table_name, al.record_id, u.name as nama_user
             FROM activity_log al
             JOIN users u ON al.id_user = u.id
             ORDER BY al.timestamp DESC, al.id DESC
             LIMIT 5"
        );

        // Fetch cold storage capacity dynamically
        if ($isBos && $idGudang === 0) {
            $capacityRows = Database::fetchAll(
                "SELECT nilai FROM settings WHERE kunci = 'kapasitas_cold_storage_kg'"
            );
            $coldStorageCapacity = 0;
            foreach ($capacityRows as $row) {
                $coldStorageCapacity += (float)($row['nilai'] ?? 0);
            }
            if ($coldStorageCapacity <= 0) {
                $coldStorageCapacity = 15000; // Default fallback
            }
        } else {
            $capacityRow = Database::fetchOne(
                "SELECT nilai FROM settings WHERE id_gudang = ? AND kunci = 'kapasitas_cold_storage_kg'",
                [$idGudang]
            );
            $coldStorageCapacity = isset($capacityRow['nilai']) ? (float)$capacityRow['nilai'] : 10000;
            if ($coldStorageCapacity <= 0) {
                $coldStorageCapacity = 10000; // Default fallback
            }
        }

        // Draft nota dari Checker yang belum difinalisasi
        if ($isBos && $idGudang === 0) {
            $draftPendingRow = Database::fetchOne(
                "SELECT COUNT(*) as cnt FROM nota WHERE status = 'draft' AND catatan LIKE '%[Draft oleh Checker%'"
            );
        } else {
            $draftPendingRow = Database::fetchOne(
                "SELECT COUNT(*) as cnt FROM nota WHERE id_gudang = ? AND status = 'draft' AND catatan LIKE '%[Draft oleh Checker%'",
                [$idGudang]
            );
        }
        $draftPendingCount = (int)($draftPendingRow['cnt'] ?? 0);

        Response::success([
            'total_produk'         => $totalProduk,
            'total_stok_value'     => $totalStokValue,
            'total_stok_qty'       => $totalStokQty,
            'low_stock_count'      => $lowStockCount,
            'penjualan_hari_ini'   => $penjualanHariIni,
            'nota_hari_ini'        => count($notaHariIni),
            'pending_timbang'      => $pendingTimbang,
            'total_piutang'        => $keuSummary['total_piutang'],
            'total_hutang'         => $keuSummary['total_hutang'],
            'overdue_count'        => $keuSummary['overdue_count'],
            'keuangan_masuk'       => $keuSummary['keuangan_masuk'],
            'keuangan_keluar'      => $keuSummary['keuangan_keluar'],
            'laba_rugi'            => $keuSummary['laba_rugi'],
            'top_products'         => $topProducts,
            'sales_chart_labels'   => $salesLabels,
            'sales_chart'          => $salesChart,
            'incoming_stock'       => $incomingStockChart,
            'stok_chart'           => [
                'labels' => array_column($stokByJenis, 'nama'),
                'values' => array_map(fn($x) => (float)$x['total'], $stokByJenis),
            ],
            'latest_logs'          => $latestLogs,
            'cold_storage_capacity'=> $coldStorageCapacity,
            'draft_pending_count'  => $draftPendingCount,
        ]);
    }

    private function resolveGudang(array $user): int
    {
        $role = strtolower($user['role'] ?? '');
        if (in_array($role, ['bos', 'super_admin', 'saas_owner'], true)) {
            return !empty($_GET['id_gudang']) ? (int)$_GET['id_gudang'] : 0;
        }
        return (int)($user['id_gudang'] ?? 0);
    }
}
