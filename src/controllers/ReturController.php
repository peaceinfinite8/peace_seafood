<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ReturService;
use App\Middleware\AuthMiddleware;
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
    }

    public function index(): void
    {
        $gudangId = WarehouseMiddleware::getGudangId();
        $allGudang = AuthMiddleware::isAllGudang();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = max(1, (int) ($_GET['per_page'] ?? 100));
        $offset = ($page - 1) * $perPage;

        $data = $this->returService->getReturList($gudangId, [], $allGudang);
        $total = count($data); // count after fetch since we can't paginate easily with allGudang

        Response::paginated(array_slice($data, $offset, $perPage), $total, $page, $perPage);
    }

    public function create(): void
    {
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, [
            'tipe' => 'required|string',
            'alasan' => 'required|string',
        ]);

        if ($validator->hasErrors()) {
            Response::validationError($validator->getErrors());
        }

        $user = AuthMiddleware::getAuthUser();
        $gudangId = WarehouseMiddleware::getGudangId();
        $result = $this->returService->create($gudangId, (int) $user['id'], $body);

        Response::created($result, 'Retur berhasil dibuat');
    }

    public function store(): void
    {
        $this->create();
    }

    public function show(string $id): void
    {
        $gudangId = WarehouseMiddleware::getGudangId();
        $retur = $this->returService->getReturDetail((int) $id, $gudangId);
        if (!$retur)
            Response::notFound('Retur tidak ditemukan');
        Response::success($retur);
    }

    public function approve(string $id): void
    {
        $gudangId = WarehouseMiddleware::getGudangId();
        $user = AuthMiddleware::getAuthUser();
        $ok = $this->returService->approve((int) $id, $gudangId, (int) $user['id']);
        if (!$ok)
            Response::error(422, 'REJECTED', 'Gagal approve retur');
        Response::success(null, 'Retur berhasil disetujui');
    }

    public function reject(string $id): void
    {
        $body = Helper::getRequestBody();
        $gudangId = WarehouseMiddleware::getGudangId();
        $ok = $this->returService->reject((int) $id, $gudangId, (string) ($body['alasan'] ?? $body['alasan_reject'] ?? ''));
        if (!$ok)
            Response::error(422, 'REJECTED', 'Gagal menolak retur');
        Response::success(null, 'Retur ditolak');
    }
}
