<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\StokService;
use App\Middleware\AuthMiddleware;
use App\Utils\Helper;
use App\Utils\Response;

class StokController
{
    private StokService $stokService;

    public function __construct()
    {
        $this->stokService = new StokService();
    }

    /**
     * GET /stok - List inventory
     */
    public function index(): void
    {
        $user     = AuthMiddleware::getAuthUser();
        $idGudang = $this->resolveGudang($user);

        // BOS tanpa filter gudang → ambil semua gudang
        $data = $this->stokService->getInventory($idGudang, $user['role'] === 'bos');
        Response::success($data);
    }

    /**
     * POST /stok/masuk - Input stok masuk (status pending)
     */
    public function masuk(): void
    {
        $user     = AuthMiddleware::getAuthUser();
        $idGudang = $this->resolveGudang($user);
        $body     = Helper::getRequestBody();

        if (empty($body['id_supplier']) || empty($body['id_produk']) || empty($body['qty']) || empty($body['harga_beli'])) {
            Response::error('Data tidak lengkap', 422, ['required' => ['id_supplier', 'id_produk', 'qty', 'harga_beli']]);
        }

        $id = $this->stokService->inputStokMasuk($body, (int)$user['id'], $idGudang);
        Response::created(['id' => $id], 'Stok masuk berhasil dicatat. Menunggu timbangan.');
    }

    /**
     * GET /stok/masuk/{id} - Detail stok masuk
     */
    public function showMasuk(string $id): void
    {
        $user     = AuthMiddleware::getAuthUser();
        $idGudang = $this->resolveGudang($user);

        $data = \App\Utils\Database::fetchOne(
            "SELECT sm.*, p.nama as nama_produk, s.nama as nama_supplier
             FROM stok_masuk sm
             JOIN produk p ON sm.id_produk = p.id
             JOIN supplier s ON sm.id_supplier = s.id
             WHERE sm.id = ? AND sm.id_gudang = ?",
            [(int)$id, $idGudang]
        );

        if (!$data) Response::notFound('Stok masuk tidak ditemukan');
        Response::success($data);
    }

    /**
     * POST /stok/timbang - Proses timbangan
     */
    public function timbang(): void
    {
        $user     = AuthMiddleware::getAuthUser();
        $this->resolveGudang($user);
        $body     = Helper::getRequestBody();

        if (empty($body['id_stok_masuk']) || !isset($body['qty_actual'])) {
            Response::error('Data tidak lengkap', 422);
        }

        $ok = $this->stokService->timbangStok((int)$body['id_stok_masuk'], $body, (int)$user['id']);
        
        if (!$ok) Response::error('Gagal proses timbangan. Pastikan stok berstatus pending.', 422);
        
        Response::success(null, 'Timbangan dikonfirmasi. Stok diperbarui.');
    }

    /**
     * GET /stok/history - History stok masuk
     */
    public function history(): void
    {
        $user     = AuthMiddleware::getAuthUser();
        $idGudang = $this->resolveGudang($user);
        $filters  = [
            'dari'      => $_GET['dari'] ?? null,
            'sampai'    => $_GET['sampai'] ?? null,
            'id_produk' => $_GET['id_produk'] ?? null,
        ];

        $data = $this->stokService->getHistory($idGudang, $filters, $user['role'] === 'bos');
        Response::success($data);
    }

    /**
     * GET /stok/pending-timbang - List pending timbangan
     */
    public function pendingTimbang(): void
    {
        $user     = AuthMiddleware::getAuthUser();
        $idGudang = $this->resolveGudang($user);

        $data = $this->stokService->getPendingTimbangan($idGudang, $user['role'] === 'bos');
        Response::success($data);
    }

    /**
     * Get gudang ID from user.
     * BOS: gunakan ?id_gudang jika ada, otherwise 0 (semua gudang).
     * Admin/Checker: selalu pakai id_gudang dari user.
     */
    private function resolveGudang(array $user): int
    {
        if ($user['role'] === 'bos') {
            return !empty($_GET['id_gudang']) ? (int)$_GET['id_gudang'] : 0;
        }
        return (int)($user['id_gudang'] ?? 0);
    }
}
