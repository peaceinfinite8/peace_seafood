<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\StokService;
use App\Services\PenjualanService;
use App\Services\KeuanganService;
use App\Services\ExportService;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Utils\Database;
use App\Utils\Response;

class LaporanController
{
    /**
     * GET /laporan/stok
     */
    public function stok(): void
    {
        RoleMiddleware::requirePermission('laporan.view');
        $user     = AuthMiddleware::getAuthUser();
        $idGudang = AuthMiddleware::resolveGudang();
        $filters  = ['dari' => $_GET['dari'] ?? null, 'sampai' => $_GET['sampai'] ?? null];

        $data = (new StokService())->getHistory($idGudang, $filters, AuthMiddleware::isAllGudang());
        Response::success($data);
    }

    /**
     * GET /laporan/penjualan
     */
    public function penjualan(): void
    {
        RoleMiddleware::requirePermission('laporan.view');
        $idGudang = AuthMiddleware::resolveGudang();
        $filters  = [
            'dari'   => $_GET['dari']   ?? null,
            'sampai' => $_GET['sampai'] ?? null,
            'status' => $_GET['status'] ?? null,
        ];

        $data = (new PenjualanService())->getNotaList($idGudang, $filters, AuthMiddleware::isAllGudang());
        Response::success($data);
    }

    /**
     * GET /laporan/keuangan
     */
    public function keuangan(): void
    {
        RoleMiddleware::requirePermission('laporan.view');
        $idGudang = AuthMiddleware::resolveGudang();
        $summary  = (new KeuanganService())->getSummary($idGudang, AuthMiddleware::isAllGudang());
        Response::success($summary);
    }

    /**
     * GET /laporan/hutang-aging
     */
    public function hutangAging(): void
    {
        RoleMiddleware::requirePermission('laporan.view');
        $idGudang = AuthMiddleware::resolveGudang();
        $data     = (new KeuanganService())->getHutangAging($idGudang, AuthMiddleware::isAllGudang());
        Response::success($data);
    }

    /**
     * GET/POST /laporan/export/pdf
     */
    public function exportPdf(): void
    {
        RoleMiddleware::requirePermission('laporan.export');
        $user      = AuthMiddleware::getAuthUser();
        $idGudang  = AuthMiddleware::resolveGudang();
        $allGudang = AuthMiddleware::isAllGudang();

        $tipe      = $_GET['tab'] ?? $_GET['tipe'] ?? 'penjualan';
        $dari      = $_GET['dari'] ?? '';
        $sampai    = $_GET['sampai'] ?? '';

        $exportService = new ExportService();

        try {
            $pdfContent = $exportService->exportLaporanPdf($dari, $sampai, $tipe, $idGudang, $allGudang);

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="laporan_' . $tipe . '_' . date('Ymd') . '.pdf"');
            echo $pdfContent;
            exit;
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * GET/POST /laporan/export/excel
     */
    public function exportExcel(): void
    {
        RoleMiddleware::requirePermission('laporan.export');
        $user      = AuthMiddleware::getAuthUser();
        $idGudang  = AuthMiddleware::resolveGudang();
        $allGudang = AuthMiddleware::isAllGudang();

        $tipe      = $_GET['tab'] ?? $_GET['tipe'] ?? 'penjualan';
        $dari      = $_GET['dari'] ?? '';
        $sampai    = $_GET['sampai'] ?? '';

        $exportService = new ExportService();

        try {
            $filepath = $exportService->exportLaporanXlsx($dari, $sampai, $tipe, $idGudang, $allGudang);

            if (!file_exists($filepath)) {
                Response::error('Gagal generate export', 500);
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
            header('Content-Length: ' . filesize($filepath));
            header('Cache-Control: max-age=0');
            readfile($filepath);
            unlink($filepath);
            exit;
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
