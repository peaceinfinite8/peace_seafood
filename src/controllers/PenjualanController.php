<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\PenjualanService;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Utils\Helper;
use App\Utils\Response;
use App\Utils\Database;

class PenjualanController
{
    private PenjualanService $service;

    public function __construct()
    {
        $this->service = new PenjualanService();
    }

    public function index(): void
    {
        RoleMiddleware::requirePermission('penjualan.view');
        $idGudang = AuthMiddleware::resolveGudang();
        $filters  = [
            'status'     => $_GET['status']     ?? null,
            'dari'       => $_GET['dari']        ?? null,
            'sampai'     => $_GET['sampai']      ?? null,
            'id_pembeli' => $_GET['id_pembeli']  ?? null,
        ];
        $data = $this->service->getNotaList($idGudang, $filters, AuthMiddleware::isAllGudang());
        Response::success($data);
    }

    public function create(): void
    {
        RoleMiddleware::requirePermission('penjualan.create');
        $user     = AuthMiddleware::getAuthUser();
        $idGudang = AuthMiddleware::resolveGudang();
        $body     = Helper::getRequestBody();

        // Allow BOS and Super Admin to pass id_gudang in request when resolveGudang returned 0
        if (in_array($user['role'], ['bos', 'super_admin'], true) && $idGudang === 0 && !empty($body['id_gudang'])) {
            $idGudang = (int)$body['id_gudang'];
        }

        if (empty($body['items']) || !is_array($body['items'])) {
            Response::error('Items penjualan tidak boleh kosong', 422);
        }

        // Validate bank account for transfer payments: ensure it belongs to gudang owner (bos)
        if (($body['jenis_pembayaran'] ?? '') === 'transfer') {
            if (empty($body['bank_account_id'])) {
                Response::error('Bank tujuan wajib diisi untuk pembayaran transfer', 422);
            }
            if (!Database::hasTable('bank_account')) {
                Response::error('Fitur rekening bank belum tersedia pada database ini', 422);
            }
            $g = Database::fetchOne("SELECT id_bos FROM gudang WHERE id = ?", [$idGudang]);
            $idBos = $g['id_bos'] ?? null;
            $acct = Database::fetchOne("SELECT * FROM bank_account WHERE id = ? AND id_user = ? AND is_active = 1", [(int)$body['bank_account_id'], $idBos]);
            if (!$acct) {
                Response::error('Rekening bank tidak ditemukan untuk gudang ini', 422);
            }
        }

        $id = $this->service->createNota($body, (int)$user['id'], $idGudang);
        Response::created(['id' => $id, 'no_nota' => null], 'Nota berhasil dibuat');
    }

    public function show(string $id): void
    {
        RoleMiddleware::requirePermission('penjualan.view');
        $idGudang = AuthMiddleware::resolveGudang();
        $data     = $this->service->getNotaDetail((int)$id, $idGudang, AuthMiddleware::isAllGudang());
        if (!$data) Response::notFound('Nota tidak ditemukan');
        Response::success($data);
    }

    public function update(string $id): void
    {
        RoleMiddleware::requirePermission('penjualan.update');
        $idGudang = AuthMiddleware::resolveGudang();
        $body     = Helper::getRequestBody();
        \App\Utils\Database::update('nota', [
            'catatan'     => $body['catatan'] ?? null,
            'pembayaran'  => $body['jenis_pembayaran'] ?? 'cash',
        ], 'id = ? AND id_gudang = ? AND status = ?', [(int)$id, $idGudang, 'draft']);
        Response::success(null, 'Nota diperbarui');
    }

    public function finalize(string $id): void
    {
        RoleMiddleware::requirePermission('penjualan.update');
        $idGudang   = AuthMiddleware::resolveGudang();
        $allGudang  = AuthMiddleware::isAllGudang();
        $ok         = $this->service->finalizeNota((int)$id, $idGudang, $allGudang);
        if (!$ok) Response::error('Gagal finalize nota. Periksa stok, limit kredit, atau status nota.', 422);
        Response::success(null, 'Nota berhasil difinalize');
    }

    public function cancel(string $id): void
    {
        RoleMiddleware::requirePermission('penjualan.cancel');
        $idGudang = AuthMiddleware::resolveGudang();
        $ok       = $this->service->cancelNota((int)$id, $idGudang);
        if (!$ok) Response::error('Gagal batalkan nota', 422);
        Response::success(null, 'Nota dibatalkan');
    }

    /**
     * GET /penjualan/{id}/pdf - Export Nota & Surat Jalan ke PDF
     */
    public function exportPdf(string $id): void
    {
        RoleMiddleware::requirePermission('penjualan.view');
        $idGudang  = AuthMiddleware::resolveGudang();
        $allGudang = AuthMiddleware::isAllGudang();

        $exportService = new \App\Services\ExportService();
        $pdfOutput     = $exportService->exportNotaPdf((int)$id, $idGudang, $allGudang);

        if ($pdfOutput === null) {
            Response::error('Gagal men-generate PDF nota atau nota tidak ditemukan.', 404);
        }

        // Set headers for file download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Nota_Penjualan_' . $id . '.pdf"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        echo $pdfOutput;
        exit;
    }
}
