<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\StokOpnameService;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Utils\Helper;
use App\Utils\Response;

class StokOpnameController
{
    private StokOpnameService $service;

    public function __construct()
    {
        $this->service = new StokOpnameService();
    }

    /**
     * GET /stok-opname - Daftar sesi opname
     */
    public function index(): void
    {
        RoleMiddleware::requirePermission('stok.view');
        $gudangContext = $this->resolveGudangContext();
        $idGudang = $gudangContext['id_gudang'];
        $allGudang = $gudangContext['all_gudang'];

        $data = $this->service->getOpnameList($idGudang, $allGudang);
        Response::success($data);
    }

    /**
     * POST /stok-opname - Buat sesi opname draft baru
     */
    public function create(): void
    {
        RoleMiddleware::requirePermission('stok.create');
        $user = AuthMiddleware::getAuthUser();
        $idGudang = AuthMiddleware::resolveGudang();
        $body = Helper::getRequestBody();

        if (in_array($user['role'], ['bos', 'super_admin'], true) && $idGudang === 0 && !empty($body['id_gudang'])) {
            $idGudang = (int) $body['id_gudang'];
        }

        if ($idGudang === 0) {
            Response::error('Gudang harus ditentukan', 422);
        }

        if (empty($body['items']) || !is_array($body['items'])) {
            Response::error('Item opname tidak boleh kosong', 422);
        }

        $idOpname = $this->service->createOpname($body, (int) $user['id'], $idGudang);
        Response::created(['id' => $idOpname], 'Sesi stok opname berhasil dibuat (Draft)');
    }

    /**
     * GET /stok-opname/{id} - Detail sesi opname
     */
    public function show(string $id): void
    {
        RoleMiddleware::requirePermission('stok.view');
        $idGudang = AuthMiddleware::resolveGudang();

        $data = $this->service->getOpnameDetail((int) $id, $idGudang);
        if (!$data) {
            Response::notFound('Sesi stok opname tidak ditemukan');
        }
        Response::success($data);
    }

    /**
     * POST /stok-opname/{id}/finalize - Finalisasi sesi opname & sesuaikan stok fisik
     */
    public function finalize(string $id): void
    {
        RoleMiddleware::requirePermission('stok.timbang'); // Hanya admin/bos yang bisa finalize opname
        $idGudang = AuthMiddleware::resolveGudang();

        // Cari tahu gudang sesi opname ini
        $so = \App\Utils\Database::fetchOne("SELECT id_gudang FROM stok_opname WHERE id = ?", [(int) $id]);
        if (!$so) {
            Response::notFound('Sesi stok opname tidak ditemukan');
        }

        $soGudang = (int) $so['id_gudang'];
        if ($idGudang !== 0 && $idGudang !== $soGudang) {
            Response::error('Akses ditolak untuk gudang ini', 403);
        }

        $ok = $this->service->finalizeOpname((int) $id, $soGudang);
        if (!$ok) {
            Response::error('Gagal memfinalisasi opname. Pastikan statusnya masih draft.', 422);
        }

        Response::success(null, 'Stok opname berhasil difinalisasi. Stok produk telah disesuaikan.');
    }

    private function resolveGudangContext(): array
    {
        return [
            'id_gudang' => AuthMiddleware::resolveGudang(),
            'all_gudang' => AuthMiddleware::isAllGudang(),
        ];
    }
}
