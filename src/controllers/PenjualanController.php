<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\PenjualanService;
use App\Middleware\AuthMiddleware;
use App\Utils\Helper;
use App\Utils\Response;

class PenjualanController
{
    private PenjualanService $service;

    public function __construct()
    {
        $this->service = new PenjualanService();
    }

    public function index(): void
    {
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
        $user     = AuthMiddleware::getAuthUser();
        $idGudang = AuthMiddleware::resolveGudang();
        $body     = Helper::getRequestBody();

        if (empty($body['items']) || !is_array($body['items'])) {
            Response::error('Items penjualan tidak boleh kosong', 422);
        }

        $id = $this->service->createNota($body, (int)$user['id'], $idGudang);
        Response::created(['id' => $id, 'no_nota' => null], 'Nota berhasil dibuat');
    }

    public function show(string $id): void
    {
        $idGudang = AuthMiddleware::resolveGudang();
        $data     = $this->service->getNotaDetail((int)$id, $idGudang, AuthMiddleware::isAllGudang());
        if (!$data) Response::notFound('Nota tidak ditemukan');
        Response::success($data);
    }

    public function update(string $id): void
    {
        $idGudang = AuthMiddleware::resolveGudang();
        $body     = Helper::getRequestBody();
        \App\Utils\Database::update('nota', [
            'catatan'          => $body['catatan'] ?? null,
            'jenis_pembayaran' => $body['jenis_pembayaran'] ?? 'cash',
        ], 'id = ? AND id_gudang = ? AND status = ?', [(int)$id, $idGudang, 'draft']);
        Response::success(null, 'Nota diperbarui');
    }

    public function finalize(string $id): void
    {
        $idGudang = AuthMiddleware::resolveGudang();
        $ok       = $this->service->finalizeNota((int)$id, $idGudang);
        if (!$ok) Response::error('Gagal finalize nota. Periksa stok atau status nota.', 422);
        Response::success(null, 'Nota berhasil difinalize');
    }

    public function cancel(string $id): void
    {
        $idGudang = AuthMiddleware::resolveGudang();
        $ok       = $this->service->cancelNota((int)$id, $idGudang);
        if (!$ok) Response::error('Gagal batalkan nota', 422);
        Response::success(null, 'Nota dibatalkan');
    }
}
