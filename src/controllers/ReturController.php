<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ReturService;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Middleware\WarehouseMiddleware;
use App\Utils\Helper;
use App\Utils\Response;
use App\Utils\Validator;

class ReturController
{
    private ReturService $returService;

    public function __construct()
    {
        $this->returService = new ReturService();
        (new WarehouseMiddleware())->handle();
    }

    public function index(): void
    {
        RoleMiddleware::requirePermission('retur.view');
        $gudangId  = WarehouseMiddleware::getGudangId();
        $allGudang = AuthMiddleware::isAllGudang();
        ['page' => $page, 'perPage' => $perPage, 'offset' => $offset] = Helper::getPaginationParams();

        $data  = $this->returService->getReturList($gudangId, [], $allGudang);
        $total = count($data); // count after fetch since we can't paginate easily with allGudang

        Response::paginated(array_slice($data, $offset, $perPage), $total, $page, $perPage);
    }

    public function create(): void
    {
        RoleMiddleware::requirePermission('retur.create');
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, [
            'nota_id'  => 'required|integer',
            'tanggal'  => 'required|string',
            'alasan'   => 'required|string',
        ]);

        if ($validator->fails()) {
            Response::error('Validation failed', 422, $validator->errors());
        }

        $gudangId = WarehouseMiddleware::getGudangId();
        $result   = $this->returService->createRetur($body, AuthMiddleware::getAuthUserId(), $gudangId);

        Response::created($result, 'Retur berhasil dibuat');
    }

    public function show(string $id): void
    {
        RoleMiddleware::requirePermission('retur.view');
        $gudangId = WarehouseMiddleware::getGudangId();
        $retur = $this->returService->getReturDetail((int) $id, $gudangId);
        if (!$retur) Response::notFound('Retur tidak ditemukan');
        Response::success($retur);
    }

    public function update(string $id): void
    {
        RoleMiddleware::requirePermission('retur.approve');
        $body   = Helper::getRequestBody();
        $gudangId = WarehouseMiddleware::getGudangId();
        $result = $this->returService->approveRetur((int) $id, $gudangId);
        Response::success($result, 'Retur berhasil diperbarui');
    }

    public function approve(string $id): void
    {
        RoleMiddleware::requirePermission('retur.approve');
        $gudangId = WarehouseMiddleware::getGudangId();
        $ok = $this->returService->approveRetur((int)$id, $gudangId);
        if (!$ok) Response::error('Gagal approve retur', 422);
        Response::success(null, 'Retur di-approve');
    }

    public function reject(string $id): void
    {
        RoleMiddleware::requirePermission('retur.reject');
        $body = Helper::getRequestBody();
        $gudangId = WarehouseMiddleware::getGudangId();
        $alasanReject = $body['alasan_reject'] ?? $body['alasan'] ?? '';
        $ok = $this->returService->rejectRetur((int)$id, $gudangId, $alasanReject);
        if (!$ok) Response::error('Gagal reject retur', 422);
        Response::success(null, 'Retur direject');
    }
}
