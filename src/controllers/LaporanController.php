<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\StokService;
use App\Services\PenjualanService;
use App\Services\KeuanganService;
use App\Services\ExportService;
use App\Middleware\AuthMiddleware;
use App\Utils\Database;
use App\Utils\Response;

class LaporanController
{
    /**
     * GET /laporan/stok
     */
    public function stok(): void
    {
        $user     = AuthMiddleware::getAuthUser();
        $idGudang = AuthMiddleware::resolveGudang();
        $filters  = ['dari' => $_GET['dari'] ?? null, 'sampai' => $_GET['sampai'] ?? null];

        $data = (new StokService())->getHistory($idGudang, $filters, $user['role'] === 'bos');
        Response::success($data);
    }

    /**
     * GET /laporan/penjualan
     */
    public function penjualan(): void
    {
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
        $idGudang = AuthMiddleware::resolveGudang();
        $summary  = (new KeuanganService())->getSummary($idGudang, AuthMiddleware::isAllGudang());
        Response::success($summary);
    }

    /**
     * GET /laporan/hutang-aging
     */
    public function hutangAging(): void
    {
        $idGudang = AuthMiddleware::resolveGudang();
        $data     = (new KeuanganService())->getHutangAging($idGudang, AuthMiddleware::isAllGudang());
        Response::success($data);
    }

    /**
     * POST /laporan/export/pdf
     */
    public function exportPdf(): void
    {
        AuthMiddleware::getAuthUser();
        $idGudang = AuthMiddleware::resolveGudang();
        $allGudang = AuthMiddleware::isAllGudang();
        $tipe     = $_GET['tipe'] ?? 'penjualan';

        $exportService = new ExportService();

        $gudang = $idGudang ? Database::fetchOne("SELECT * FROM gudang WHERE id = ?", [$idGudang]) : null;
        if (!$gudang) $gudang = ['nama' => 'All Gudang'];

        $data = match($tipe) {
            'stok'     => (new StokService())->getHistory($idGudang, [], $allGudang),
            'keuangan' => (new KeuanganService())->getHutangAging($idGudang, $allGudang),
            default    => (new PenjualanService())->getNotaList($idGudang, [], $allGudang),
        };

        $html = $exportService->generateReportHtml($tipe, $data, $gudang);

        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="laporan_' . $tipe . '_' . date('Ymd') . '.pdf"');
            echo $dompdf->output();
            exit;
        }

        header('Content-Type: text/html');
        echo $html;
        exit;
    }

    /**
     * POST /laporan/export/excel
     */
    public function exportExcel(): void
    {
        $idGudang  = AuthMiddleware::resolveGudang();
        $allGudang = AuthMiddleware::isAllGudang();
        $tipe      = $_GET['tipe'] ?? 'penjualan';
        $filters   = ['dari' => $_GET['dari'] ?? null, 'sampai' => $_GET['sampai'] ?? null];

        $exportService = new ExportService();

        $filepath = match($tipe) {
            'stok'     => $exportService->exportStokCsv($idGudang, $filters, $allGudang),
            'keuangan' => $exportService->exportKeuanganCsv($idGudang, $filters, $allGudang),
            default    => $exportService->exportPenjualanCsv($idGudang, $filters, $allGudang),
        };

        if (!file_exists($filepath)) {
            Response::error('Gagal generate export', 500);
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        unlink($filepath);
        exit;
    }
}
