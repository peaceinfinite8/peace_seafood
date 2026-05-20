<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\PenitipanService;
use App\Middleware\AuthMiddleware;
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
    }

    public function index(): void
    {
        $gudangId = WarehouseMiddleware::getGudangId();
        $allGudang = AuthMiddleware::isAllGudang();
        $data = $this->penitipanService->getTitipanList($gudangId, [], $allGudang);
        Response::success($data);
    }

    public function create(): void
    {
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, [
            'pembeli_id' => 'required|integer',
            'jumlah' => 'required|numeric|min:0.01',
            'harga_titip' => 'required|numeric|min:0',
            'tanggal_masuk' => 'required|string',
        ]);

        if ($validator->hasErrors()) {
            Response::validationError($validator->getErrors());
        }

        $user = AuthMiddleware::getAuthUser();
        $gudangId = WarehouseMiddleware::getGudangId();
        $result = $this->penitipanService->terima($gudangId, (int) $user['id'], $body);

        Response::created($result, 'Titipan berhasil diterima');
    }

    public function show(string $id): void
    {
        $gudangId = WarehouseMiddleware::getGudangId();
        $allGudang = AuthMiddleware::isAllGudang();
        $data = $this->penitipanService->getSettlement((int) $id, $gudangId, $allGudang);
        if (!$data) {
            Response::notFound('Titipan tidak ditemukan');
        }
        Response::success($data);
    }

    public function jual(string $id): void
    {
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, [
            'jumlah_terjual' => 'required|numeric|min:0.01',
            'harga_jual' => 'required|numeric|min:0',
            'tanggal' => 'required|string',
        ]);

        if ($validator->hasErrors()) {
            Response::validationError($validator->getErrors());
        }

        // Add titipan_id from URL parameter to body
        $body['titipan_id'] = (int) $id;

        $user = AuthMiddleware::getAuthUser();
        $gudangId = WarehouseMiddleware::getGudangId();
        $result = $this->penitipanService->jual($body, (int) $user['id'], $gudangId);
        Response::success($result, 'Penjualan titipan berhasil dicatat');
    }

    public function selesai(string $id): void
    {
        $gudangId = WarehouseMiddleware::getGudangId();
        $result = $this->penitipanService->settlement((int) $id, $gudangId);
        Response::success($result, 'Settlement berhasil');
    }
}
