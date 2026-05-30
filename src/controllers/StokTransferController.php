<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\StokTransferService;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Utils\Helper;
use App\Utils\Response;

class StokTransferController
{
    private StokTransferService $service;

    public function __construct()
    {
        $this->service = new StokTransferService();
    }

    /**
     * GET /stok-transfer - Daftar transfer stok
     */
    public function index(): void
    {
        RoleMiddleware::requirePermission('stok.view');
        $idGudang  = AuthMiddleware::resolveGudang();
        $allGudang = AuthMiddleware::isAllGudang();

        $data = $this->service->getTransferList($idGudang, $allGudang);
        Response::success($data);
    }

    /**
     * POST /stok-transfer - Buat transaksi transfer baru
     */
    public function create(): void
    {
        RoleMiddleware::requirePermission('stok.create');
        $user     = AuthMiddleware::getAuthUser();
        $idGudang = AuthMiddleware::resolveGudang();
        $body     = Helper::getRequestBody();

        if (in_array($user['role'], ['bos', 'super_admin'], true) && $idGudang === 0 && !empty($body['gudang_asal_id'])) {
            $idGudang = (int)$body['gudang_asal_id'];
        }

        if ($idGudang === 0) {
            Response::error('Gudang asal harus ditentukan', 422);
        }

        if (empty($body['id_produk']) || empty($body['gudang_tujuan_id']) || empty($body['qty'])) {
            Response::error('Data transfer tidak lengkap', 422);
        }

        // Prevent selecting same source and destination warehouse
        $gudangTujuan = (int)($body['gudang_tujuan_id'] ?? 0);
        if ($gudangTujuan === $idGudang) {
            Response::error('Gudang asal dan gudang tujuan tidak boleh sama', 422);
        }

        try {
            $idTransfer = $this->service->createTransfer($body, (int)$user['id'], $idGudang);
            Response::created(['id' => $idTransfer], 'Transaksi transfer berhasil didaftarkan (Pending)');
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * PUT /stok-transfer/{id}/status - Update status transfer (sent/received)
     */
    public function updateStatus(string $id): void
    {
        RoleMiddleware::requirePermission('stok.create'); // Checker / Admin / Bos
        $user     = AuthMiddleware::getAuthUser();
        $idGudang = AuthMiddleware::resolveGudang();
        $body     = Helper::getRequestBody();

        if (empty($body['status'])) {
            Response::error('Status baru wajib ditentukan', 422);
        }

        $newStatus = (string)$body['status'];
        if (!in_array($newStatus, ['sent', 'received'], true)) {
            Response::error('Status tidak valid', 422);
        }

        $ok = $this->service->updateStatus((int)$id, $newStatus, (int)$user['id'], $idGudang);
        if (!$ok) {
            Response::error('Gagal memperbarui status transfer. Pastikan urutan transisi status atau stok mencukupi.', 422);
        }

        Response::success(null, 'Status transfer berhasil diperbarui.');
    }
}
