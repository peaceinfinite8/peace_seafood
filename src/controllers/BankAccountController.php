<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BankAccount;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Utils\Database;
use App\Utils\Helper;
use App\Utils\Response;

class BankAccountController
{
    /**
     * GET /settings/bank-accounts
     */
    public function index(): void
    {
        RoleMiddleware::requirePermission('keuangan.view');
        $user = AuthMiddleware::getAuthUser();
        $idUserOwner = (int)$user['id'];

        // Get warehouse ID from query parameter or user's assigned warehouse
        $idGudang = 0;
        if (in_array(($user['role'] ?? ''), ['bos', 'super_admin'], true)) {
            $idGudang = !empty($_GET['id_gudang']) ? (int)$_GET['id_gudang'] : 0;
        } else {
            $idGudang = (int)($user['id_gudang'] ?? 0);
        }

        if (($user['role'] ?? '') !== 'bos' || $idGudang > 0) {
            $gudang = $idGudang > 0
                ? Database::fetchOne("SELECT id_bos FROM gudang WHERE id = ?", [$idGudang])
                : null;
            $idUserOwner = (int)($gudang['id_bos'] ?? $idUserOwner);
        }

        $accounts = BankAccount::getByUser($idUserOwner);
        Response::success($accounts);
    }

    /** POST /settings/bank-accounts */
    public function store(): void
    {
        $user = AuthMiddleware::getAuthUser();
        if (($user['role'] ?? '') !== 'super_admin') {
            Response::forbidden('Hanya Super Admin yang dapat mengelola rekening bank');
        }
        $body = Helper::getRequestBody();

        if (empty($body['bank_name']) || empty($body['account_number']) || empty($body['account_name'])) {
            Response::error('Data tidak lengkap', 422);
        }

        $id = BankAccount::create([
            'id_user' => (int)$user['id'],
            'bank_name' => $body['bank_name'],
            'account_number' => $body['account_number'],
            'account_name' => $body['account_name'],
        ]);

        Response::created(['id' => $id], 'Rekening berhasil ditambahkan');
    }

    /** PUT /settings/bank-accounts/{id} */
    public function update(string $id): void
    {
        $user = AuthMiddleware::getAuthUser();
        if (($user['role'] ?? '') !== 'super_admin') {
            Response::forbidden('Hanya Super Admin yang dapat mengelola rekening bank');
        }
        $body = Helper::getRequestBody();

        $ok = BankAccount::updateAccount((int)$id, $body, (int)$user['id']);
        if (!$ok) Response::error('Gagal memperbarui rekening', 422);
        Response::success(null, 'Rekening diperbarui');
    }

    /** DELETE /settings/bank-accounts/{id} */
    public function delete(string $id): void
    {
        $user = AuthMiddleware::getAuthUser();
        if (($user['role'] ?? '') !== 'super_admin') {
            Response::forbidden('Hanya Super Admin yang dapat mengelola rekening bank');
        }
        $ok = BankAccount::deleteAccount((int)$id, (int)$user['id']);
        if (!$ok) Response::error('Gagal menghapus rekening', 422);
        Response::success(null, 'Rekening dinonaktifkan');
    }
}
