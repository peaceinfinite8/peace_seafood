<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Supplier;
use App\Models\Pembeli;
use App\Models\JenisIkan;
use App\Models\Produk;
use App\Models\HargaHistory;
use App\Middleware\RoleMiddleware;
use App\Utils\Helper;
use App\Utils\Response;
use App\Utils\Validator;

class MasterDataController
{
    // ── Supplier ──────────────────────────────────────────────

    public function supplierIndex(): void
    {
        $data = (new Supplier())->findActive();
        Response::success($data);
    }

    public function supplierStore(): void
    {
        RoleMiddleware::require(['superadmin', 'admin']);
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, ['nama' => 'required|string']);
        if ($validator->fails()) Response::error('Validation failed', 422, $validator->errors());

        $id = (new Supplier())->insert($body);
        Response::created(['id' => $id], 'Supplier berhasil ditambahkan');
    }

    public function supplierUpdate(string $id): void
    {
        RoleMiddleware::require(['superadmin', 'admin']);
        $body = Helper::getRequestBody();
        (new Supplier())->update((int) $id, $body);
        Response::success(null, 'Supplier berhasil diperbarui');
    }

    public function supplierDestroy(string $id): void
    {
        RoleMiddleware::require(['superadmin', 'admin']);
        (new Supplier())->update((int) $id, ['is_active' => 0]);
        Response::success(null, 'Supplier berhasil dinonaktifkan');
    }

    // ── Pembeli ───────────────────────────────────────────────

    public function pembeliIndex(): void
    {
        $data = (new Pembeli())->findActive();
        Response::success($data);
    }

    public function pembeliStore(): void
    {
        RoleMiddleware::require(['superadmin', 'admin']);
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, ['nama' => 'required|string']);
        if ($validator->fails()) Response::error('Validation failed', 422, $validator->errors());

        $id = (new Pembeli())->insert($body);
        Response::created(['id' => $id], 'Pembeli berhasil ditambahkan');
    }

    public function pembeliUpdate(string $id): void
    {
        RoleMiddleware::require(['superadmin', 'admin']);
        $body = Helper::getRequestBody();
        (new Pembeli())->update((int) $id, $body);
        Response::success(null, 'Pembeli berhasil diperbarui');
    }

    public function pembeliDestroy(string $id): void
    {
        RoleMiddleware::require(['superadmin', 'admin']);
        (new Pembeli())->update((int) $id, ['is_active' => 0]);
        Response::success(null, 'Pembeli berhasil dinonaktifkan');
    }

    // ── Jenis Ikan ────────────────────────────────────────────

    public function jenisIkanIndex(): void
    {
        $data = (new JenisIkan())->findActive();
        Response::success($data);
    }

    public function jenisIkanStore(): void
    {
        RoleMiddleware::require(['superadmin', 'admin']);
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, ['nama' => 'required|string']);
        if ($validator->fails()) Response::error('Validation failed', 422, $validator->errors());

        $id = (new JenisIkan())->insert($body);
        Response::created(['id' => $id], 'Jenis ikan berhasil ditambahkan');
    }

    public function jenisIkanUpdate(string $id): void
    {
        RoleMiddleware::require(['superadmin', 'admin']);
        $body = Helper::getRequestBody();
        (new JenisIkan())->update((int) $id, $body);
        Response::success(null, 'Jenis ikan berhasil diperbarui');
    }

    // ── Produk ────────────────────────────────────────────────

    public function produkIndex(): void
    {
        $data = (new Produk())->findWithJenis();
        Response::success($data);
    }

    public function produkStore(): void
    {
        RoleMiddleware::require(['superadmin', 'admin']);
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, [
            'jenis_ikan_id' => 'required|integer',
            'nama'          => 'required|string',
        ]);
        if ($validator->fails()) Response::error('Validation failed', 422, $validator->errors());

        $id = (new Produk())->insert($body);
        Response::created(['id' => $id], 'Produk berhasil ditambahkan');
    }

    public function produkUpdate(string $id): void
    {
        RoleMiddleware::require(['superadmin', 'admin']);
        $body = Helper::getRequestBody();
        (new Produk())->update((int) $id, $body);
        Response::success(null, 'Produk berhasil diperbarui');
    }

    // ── Harga ─────────────────────────────────────────────────

    public function hargaIndex(): void
    {
        $produkId = (int) ($_GET['produk_id'] ?? 0);
        $data     = (new HargaHistory())->findByProduk($produkId);
        Response::success($data);
    }

    public function hargaStore(): void
    {
        RoleMiddleware::require(['superadmin', 'admin']);
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, [
            'produk_id' => 'required|integer',
            'harga_baru' => 'required|numeric|min:0',
            'tipe'       => 'required|in:beli,jual',
        ]);
        if ($validator->fails()) Response::error('Validation failed', 422, $validator->errors());

        $produk = (new Produk())->findById((int) $body['produk_id']);
        if (!$produk) Response::notFound('Produk tidak ditemukan');

        $hargaLama = $body['tipe'] === 'beli' ? $produk['harga_beli'] : $produk['harga_jual'];

        (new HargaHistory())->insert([
            'produk_id'  => $body['produk_id'],
            'harga_lama' => $hargaLama,
            'harga_baru' => $body['harga_baru'],
            'tipe'       => $body['tipe'],
        ]);

        $field = $body['tipe'] === 'beli' ? 'harga_beli' : 'harga_jual';
        (new Produk())->update((int) $body['produk_id'], [$field => $body['harga_baru']]);

        Response::success(null, 'Harga berhasil diperbarui');
    }
}
