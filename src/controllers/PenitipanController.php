<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\PenitipanService;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Middleware\WarehouseMiddleware;
use App\Utils\Helper;
use App\Utils\Response;
use App\Utils\Validator;

class PenitipanController
{
    private PenitipanService $penitipanService;

    public function __construct()
    {
        $this->penitipanService = new PenitipanService();
        (new WarehouseMiddleware())->handle();
    }

    public function index(): void
    {
        RoleMiddleware::requirePermission('penitipan.view');
        $gudangId  = WarehouseMiddleware::getGudangId();
        $allGudang = AuthMiddleware::isAllGudang();
        $data      = $this->penitipanService->getTitipanList($gudangId, [], $allGudang);
        Response::success($data);
    }

    public function create(): void
    {
        RoleMiddleware::requirePermission('penitipan.create');
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, [
            'pembeli_id'   => 'required|integer',
            'produk_id'    => 'required|integer',
            'jumlah'       => 'required|numeric|min:0.01',
            'harga_titip'  => 'required|numeric|min:0',
            'tanggal_masuk' => 'required|string',
        ]);

        if ($validator->fails()) {
            Response::error('Validation failed', 422, $validator->errors());
        }

        $gudangId = WarehouseMiddleware::getGudangId();
        $result   = $this->penitipanService->createTitipan($body, AuthMiddleware::getAuthUserId(), $gudangId);

        Response::created($result, 'Titipan berhasil diterima');
    }

    public function show(string $id): void
    {
        RoleMiddleware::requirePermission('penitipan.view');
        $gudangId = WarehouseMiddleware::getGudangId();
        $data = $this->penitipanService->getSettlement((int)$id, $gudangId);
        if (!$data) Response::notFound('Titipan tidak ditemukan');
        Response::success($data);
    }

    public function jual(): void
    {
        RoleMiddleware::requirePermission('penitipan.update');
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, [
            'titipan_id'    => 'required|integer',
            'jumlah_terjual' => 'required|numeric|min:0.01',
            'harga_jual'    => 'required|numeric|min:0',
            'tanggal'       => 'required|string',
        ]);

        if ($validator->fails()) {
            Response::error('Validation failed', 422, $validator->errors());
        }

        $gudangId = WarehouseMiddleware::getGudangId();
        $result = $this->penitipanService->jualTitipan((int)$body['titipan_id'], $body, $gudangId);
        Response::success($result, 'Penjualan titipan berhasil dicatat');
    }

    public function selesai(string $id): void
    {
        RoleMiddleware::requirePermission('penitipan.update');
        $gudangId = WarehouseMiddleware::getGudangId();
        $ok = $this->penitipanService->selesaikanTitipan((int)$id, $gudangId);
        if (!$ok) Response::error('Gagal menyelesaikan titipan', 422);
        Response::success(null, 'Titipan diselesaikan');
    }

    public function settlement(): void
    {
        RoleMiddleware::requirePermission('penitipan.view');
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, [
            'titipan_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            Response::error('Validation failed', 422, $validator->errors());
        }

        $gudangId = WarehouseMiddleware::getGudangId();
        $result = $this->penitipanService->getSettlement((int)$body['titipan_id'], $gudangId);
        if (!$result) Response::notFound('Settlement tidak ditemukan');
        Response::success($result, 'Settlement berhasil');
    }
}
