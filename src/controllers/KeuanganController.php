<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\KeuanganService;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Utils\Helper;
use App\Utils\Response;

class KeuanganController
{
    private KeuanganService $service;

    public function __construct()
    {
        $this->service = new KeuanganService();
    }

    public function index(): void
    {
        RoleMiddleware::requirePermission('keuangan.view');
        $gudangContext = $this->resolveGudangContext();
        $idGudang = $gudangContext['id_gudang'];
        $allGudang = $gudangContext['all_gudang'];
        $filters = [
            'jenis' => $_GET['jenis'] ?? null,
            'status' => $_GET['status'] ?? null,
            'dari' => $_GET['dari'] ?? null,
            'sampai' => $_GET['sampai'] ?? null,
        ];
        $data = $this->service->getHutangPiutangList($idGudang, $filters, $allGudang);
        Response::success($data);
    }

    public function create(): void
    {
        RoleMiddleware::requirePermission('keuangan.create');
        $idGudang = AuthMiddleware::resolveGudang();
        $user = AuthMiddleware::getAuthUser();
        $body = Helper::getRequestBody();

        if (empty($body['jenis']) || empty($body['nominal'])) {
            Response::error('Jenis dan nominal wajib diisi', 422);
        }

        $id = $this->service->createHutangPiutang($body, $idGudang, (int) $user['id']);
        Response::created(['id' => $id], 'Data hutang/piutang berhasil ditambahkan');
    }

    public function show(string $id): void
    {
        RoleMiddleware::requirePermission('keuangan.view');
        $gudangContext = $this->resolveGudangContext();
        $idGudang = $gudangContext['id_gudang'];
        $allGudang = $gudangContext['all_gudang'];
        $where = $allGudang
            ? "id = ?"
            : "id = ? AND id_gudang = ?";
        $params = $allGudang
            ? [(int) $id]
            : [(int) $id, $idGudang];
        $data = \App\Utils\Database::fetchOne("SELECT * FROM hutang_piutang WHERE {$where}", $params);
        if (!$data)
            Response::notFound('Data tidak ditemukan');
        Response::success($data);
    }

    public function bayar(): void
    {
        RoleMiddleware::requirePermission('keuangan.bayar');
        $gudangContext = $this->resolveGudangContext();
        $idGudang = $gudangContext['id_gudang'];
        $allGudang = $gudangContext['all_gudang'];
        $user = AuthMiddleware::getAuthUser();
        $body = Helper::getRequestBody();

        if (empty($body['id_hutang_piutang']) || empty($body['nominal_bayar'])) {
            Response::error('ID dan nominal bayar wajib diisi', 422);
        }

        $ok = $this->service->bayar($body, $idGudang, (int) $user['id'], $allGudang);
        if (!$ok)
            Response::error('Gagal proses pembayaran. Periksa data dan sisa hutang.', 422);
        Response::success(null, 'Pembayaran berhasil dicatat');
    }

    public function biaya(): void
    {
        RoleMiddleware::requirePermission('keuangan.view');
        $gudangContext = $this->resolveGudangContext();
        $idGudang = $gudangContext['id_gudang'];
        $allGudang = $gudangContext['all_gudang'];
        $filters = ['dari' => $_GET['dari'] ?? null, 'sampai' => $_GET['sampai'] ?? null];
        $data = $this->service->getBiayaList($idGudang, $filters, $allGudang);
        Response::success($data);
    }

    private function resolveGudangContext(): array
    {
        return [
            'id_gudang' => AuthMiddleware::resolveGudang(),
            'all_gudang' => AuthMiddleware::isAllGudang(),
        ];
    }

    public function storeBiaya(): void
    {
        RoleMiddleware::requirePermission('keuangan.create');
        $user = AuthMiddleware::getAuthUser();
        $idGudang = AuthMiddleware::resolveGudang();
        $body = Helper::getRequestBody();

        if (empty($body['nama_biaya']) || empty($body['nominal'])) {
            Response::error('Nama biaya dan nominal wajib diisi', 422);
        }

        $id = $this->service->createBiaya($body, (int) $user['id'], $idGudang);
        Response::created(['id' => $id], 'Biaya berhasil dicatat');
    }
}
