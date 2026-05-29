<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Supplier;
use App\Models\Pembeli;
use App\Models\JenisIkan;
use App\Models\Produk;
use App\Models\HargaHistory;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Utils\Database;
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
        RoleMiddleware::requirePermission('master_data.create');
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, ['nama' => 'required|string']);
        if ($validator->fails())
            Response::error('Validation failed', 422, $validator->errors());

        $user = AuthMiddleware::getAuthUser();
        $body = Helper::normalizeContactFields($body);

        // Pastikan id_gudang terisi
        if (empty($body['id_gudang'])) {
            if (!empty($user['id_gudang'])) {
                $body['id_gudang'] = (int) $user['id_gudang'];
            } else {
                $firstGudangId = Helper::firstActiveGudangId();
                if ($firstGudangId !== null) {
                    $body['id_gudang'] = $firstGudangId;
                } else {
                    Response::error('Gudang tidak ditemukan untuk mengaitkan data ini.', 422);
                }
            }
        }

        $id = (new Supplier())->insert($body);
        Response::created(['id' => $id], 'Supplier berhasil ditambahkan');
    }

    public function supplierShow(string $id): void
    {
        $data = (new Supplier())->findById((int) $id);
        if (!$data)
            Response::notFound('Supplier tidak ditemukan');
        Response::success($data);
    }

    public function supplierUpdate(string $id): void
    {
        RoleMiddleware::requirePermission('master_data.update');
        $body = Helper::getRequestBody();
        $body = Helper::normalizeContactFields($body);

        (new Supplier())->update((int) $id, $body);
        Response::success(null, 'Supplier berhasil diperbarui');
    }

    public function supplierDestroy(string $id): void
    {
        RoleMiddleware::requirePermission('master_data.delete');
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
        RoleMiddleware::requirePermission('master_data.create');
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, ['nama' => 'required|string']);
        if ($validator->fails())
            Response::error('Validation failed', 422, $validator->errors());

        $user = AuthMiddleware::getAuthUser();
        $body = Helper::normalizeContactFields($body);
        $body = Helper::normalizePembeliType($body);

        // Pastikan id_gudang terisi
        if (empty($body['id_gudang'])) {
            if (!empty($user['id_gudang'])) {
                $body['id_gudang'] = (int) $user['id_gudang'];
            } else {
                $firstGudangId = Helper::firstActiveGudangId();
                if ($firstGudangId !== null) {
                    $body['id_gudang'] = $firstGudangId;
                } else {
                    Response::error('Gudang tidak ditemukan untuk mengaitkan data ini.', 422);
                }
            }
        }

        $id = (new Pembeli())->insert($body);
        Response::created(['id' => $id], 'Pembeli berhasil ditambahkan');
    }

    public function pembeliShow(string $id): void
    {
        $data = (new Pembeli())->findById((int) $id);
        if (!$data)
            Response::notFound('Pembeli tidak ditemukan');
        Response::success($data);
    }

    public function pembeliUpdate(string $id): void
    {
        RoleMiddleware::requirePermission('master_data.update');
        $body = Helper::getRequestBody();
        $body = Helper::normalizeContactFields($body);
        $body = Helper::normalizePembeliType($body);

        (new Pembeli())->update((int) $id, $body);
        Response::success(null, 'Pembeli berhasil diperbarui');
    }

    public function pembeliDestroy(string $id): void
    {
        RoleMiddleware::requirePermission('master_data.delete');
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
        RoleMiddleware::requirePermission('master_data.create');
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, ['nama' => 'required|string']);
        if ($validator->fails())
            Response::error('Validation failed', 422, $validator->errors());

        $id = (new JenisIkan())->insert($body);
        Response::created(['id' => $id], 'Jenis ikan berhasil ditambahkan');
    }

    public function jenisIkanUpdate(string $id): void
    {
        RoleMiddleware::requirePermission('master_data.update');
        $body = Helper::getRequestBody();
        (new JenisIkan())->update((int) $id, $body);
        Response::success(null, 'Jenis ikan berhasil diperbarui');
    }

    public function jenisIkanDestroy(string $id): void
    {
        RoleMiddleware::requirePermission('master_data.delete');

        $data = (new JenisIkan())->findById((int) $id);
        if (!$data) {
            Response::notFound('Jenis ikan tidak ditemukan');
        }

        (new JenisIkan())->update((int) $id, ['is_active' => 0]);
        Response::success(null, 'Jenis ikan berhasil dinonaktifkan');
    }

    // ── Produk ────────────────────────────────────────────────

    public function produkIndex(): void
    {
        $user = AuthMiddleware::getAuthUser();
        $idGudang = !empty($_GET['id_gudang']) ? (int) $_GET['id_gudang'] : (int) ($user['id_gudang'] ?? 0);
        $data = Produk::findWithJenis($idGudang, AuthMiddleware::isAllGudang());
        Response::success($data);
    }

    public function produkStore(): void
    {
        RoleMiddleware::requirePermission('master_data.create');
        $user = AuthMiddleware::getAuthUser();
        $body = Helper::getRequestBody();

        if (in_array($user['role'], ['super_admin', 'bos'], true)) {
            $firstGudangId = Helper::firstActiveGudangId(true);
            if ($firstGudangId !== null) {
                $body['id_gudang'] = $firstGudangId;
            } else {
                Response::error('Gudang aktif tidak ditemukan untuk mengaitkan produk.', 422);
            }
        } elseif (empty($body['id_gudang']) && !empty($user['id_gudang'])) {
            $body['id_gudang'] = (int) $user['id_gudang'];
        } elseif (empty($body['id_gudang'])) {
            Response::error('id_gudang wajib diisi.', 422);
        }

        $validator = Validator::make($body, [
            'id_jenis_ikan' => 'required|integer',
            'id_gudang' => 'required|integer',
            'nama' => 'required|string',
            'satuan' => 'nullable|string',
        ]);
        if ($validator->fails())
            Response::error('Validation failed', 422, $validator->errors());

        $id = Produk::insert($body);
        Response::created(['id' => $id], 'Produk berhasil ditambahkan');
    }

    public function produkShow(string $id): void
    {
        $data = Produk::findById((int) $id);
        if (!$data)
            Response::notFound('Produk tidak ditemukan');
        Response::success($data);
    }

    public function produkUpdate(string $id): void
    {
        RoleMiddleware::requirePermission('master_data.update');
        $user = AuthMiddleware::getAuthUser();
        $body = Helper::getRequestBody();

        if (in_array($user['role'], ['super_admin', 'bos'], true)) {
            $firstGudangId = Helper::firstActiveGudangId(true);
            if ($firstGudangId !== null) {
                $body['id_gudang'] = $firstGudangId;
            }
        } elseif (empty($body['id_gudang']) && !empty($user['id_gudang'])) {
            $body['id_gudang'] = (int) $user['id_gudang'];
        }

        if (empty($body['id_gudang'])) {
            Response::error('id_gudang wajib diisi.', 422);
        }

        Produk::updateRecord((int) $id, $body);
        Response::success(null, 'Produk berhasil diperbarui');
    }

    public function pembeliCreditStatus(string $id): void
    {
        $user = AuthMiddleware::getAuthUser();
        $idGudang = AuthMiddleware::resolveGudang();
        $allGudang = AuthMiddleware::isAllGudang();

        $pembeli = Database::fetchOne(
            "SELECT id, nama, kredit_limit FROM pembeli WHERE id = ? AND is_active = 1",
            [(int) $id]
        );
        if (!$pembeli)
            Response::notFound('Pembeli tidak ditemukan');

        if ($allGudang && $idGudang === 0) {
            $row = Database::fetchOne(
                "SELECT COALESCE(SUM(sisa_hutang),0) as outstanding FROM hutang_piutang WHERE id_pembeli = ? AND status != 'lunas'",
                [(int) $id]
            );
        } else {
            $row = Database::fetchOne(
                "SELECT COALESCE(SUM(sisa_hutang),0) as outstanding FROM hutang_piutang WHERE id_pembeli = ? AND id_gudang = ? AND status != 'lunas'",
                [(int) $id, $idGudang]
            );
        }

        $outstanding = (float) ($row['outstanding'] ?? 0);
        $kreditLimit = (float) ($pembeli['kredit_limit'] ?? 0);

        Response::success([
            'id' => (int) $pembeli['id'],
            'nama' => $pembeli['nama'],
            'kredit_limit' => $kreditLimit,
            'outstanding' => $outstanding,
            'available' => max(0, $kreditLimit - $outstanding),
            'is_over' => $kreditLimit > 0 ? ($outstanding >= $kreditLimit) : false,
        ]);
    }

    // ── Harga ─────────────────────────────────────────────────

    public function hargaIndex(): void
    {
        $produkId = (int) ($_GET['produk_id'] ?? 0);
        $data = (new HargaHistory())->findByProduk($produkId);
        Response::success($data);
    }

    public function hargaStore(): void
    {
        RoleMiddleware::requirePermission('harga.create');
        $body = Helper::getRequestBody();

        $validator = Validator::make($body, [
            'produk_id' => 'required|integer',
            'harga_baru' => 'required|numeric|min:0',
            'tipe' => 'required|in:beli,jual',
        ]);
        if ($validator->fails())
            Response::error('Validation failed', 422, $validator->errors());

        $produk = (new Produk())->findById((int) $body['produk_id']);
        if (!$produk)
            Response::notFound('Produk tidak ditemukan');

        $hargaLama = $body['tipe'] === 'beli' ? $produk['harga_beli'] : $produk['harga_jual'];

        (new HargaHistory())->insert([
            'produk_id' => $body['produk_id'],
            'harga_lama' => $hargaLama,
            'harga_baru' => $body['harga_baru'],
            'tipe' => $body['tipe'],
        ]);

        $field = $body['tipe'] === 'beli' ? 'harga_beli' : 'harga_jual';
        (new Produk())->update((int) $body['produk_id'], [$field => $body['harga_baru']]);

        Response::success(null, 'Harga berhasil diperbarui');
    }
}
