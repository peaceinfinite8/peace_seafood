<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Middleware\AuthMiddleware;
use App\Utils\Database;
use App\Utils\Helper;
use App\Utils\Response;

class SettingsController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * GET /settings - Get all app settings
     */
    public function index(): void
    {
        $user = AuthMiddleware::getAuthUser();

        // Bos sees all; admin/checker sees their warehouse settings
        if ($user['role'] === 'bos') {
            $data = Database::fetchAll(
                "SELECT kunci, nilai, deskripsi AS keterangan FROM settings WHERE id_gudang IS NULL OR id_gudang = 1 GROUP BY kunci ORDER BY kunci"
            );
        } else {
            $idGudang = (int)($user['id_gudang'] ?? 1);
            $data = Database::fetchAll(
                "SELECT kunci, nilai, deskripsi AS keterangan FROM settings WHERE id_gudang = ? ORDER BY kunci",
                [$idGudang]
            );
        }

        // Add human-readable labels
        $labels = [
            'multi_warehouse_aktif'     => ['label' => 'Multi-Warehouse', 'keterangan' => 'Aktifkan fitur multi gudang/cabang'],
            'stok_minimum_threshold'    => ['label' => 'Batas Stok Minimum (kg)', 'keterangan' => 'Kirim notifikasi saat stok di bawah angka ini'],
            'susut_alert_threshold'     => ['label' => 'Alert Susut (%)', 'keterangan' => 'Peringatan jika susut melebihi persentase ini'],
            'komisi_penitipan_tipe'     => ['label' => 'Tipe Komisi Penitipan', 'keterangan' => 'Metode komisi: potong atau bayar_terpisah'],
            'komisi_penitipan_persen'   => ['label' => 'Komisi Penitipan (%)', 'keterangan' => 'Persentase komisi penitipan default'],
            'pajak_default_persen'      => ['label' => 'Pajak Default (%)', 'keterangan' => 'Pajak yang diterapkan pada nota penjualan'],
            'jatuh_tempo_default_hari'  => ['label' => 'Jatuh Tempo Default (Hari)', 'keterangan' => 'Periode kredit default untuk hutang/piutang'],
            'session_timeout_menit'     => ['label' => 'Timeout Sesi (Menit)', 'keterangan' => 'Auto-logout setelah tidak aktif selama X menit'],
            'harga_locked_untuk'        => ['label' => 'Harga Dikunci untuk Role', 'keterangan' => 'Siapa yang dapat mengubah harga: bos/admin/semua'],
            'export_permission'         => ['label' => 'Izin Export Laporan', 'keterangan' => 'Siapa yang dapat export laporan: bos/admin/semua'],
            'backup_otomatis'           => ['label' => 'Backup Otomatis', 'keterangan' => 'Aktifkan backup database otomatis'],
            'onboarding_wizard_aktif'   => ['label' => 'Wizard Onboarding', 'keterangan' => 'Tampilkan panduan setup saat pertama login'],
        ];

        $result = array_map(function ($item) use ($labels) {
            $item['label']      = $labels[$item['kunci']]['label']      ?? ucwords(str_replace('_', ' ', $item['kunci']));
            $item['keterangan'] = $item['keterangan'] ?? ($labels[$item['kunci']]['keterangan'] ?? '');
            return $item;
        }, $data);

        Response::success($result);
    }

    /**
     * PUT /settings/{kunci} - Update a setting
     */
    public function update(string $kunci): void
    {
        $user = AuthMiddleware::getAuthUser();
        if ($user['role'] !== 'bos') Response::forbidden('Hanya Bos yang dapat mengubah pengaturan');

        $body = Helper::getRequestBody();
        if (!isset($body['nilai'])) Response::error('Nilai wajib diisi', 422);

        $idGudang = (int)($user['id_gudang'] ?? 1);

        $existing = Database::fetchOne(
            "SELECT id FROM settings WHERE kunci = ? AND id_gudang = ?",
            [$kunci, $idGudang]
        );

        if ($existing) {
            Database::update('settings', ['nilai' => $body['nilai']], 'kunci = ? AND id_gudang = ?', [$kunci, $idGudang]);
        } else {
            Database::insert('settings', ['kunci' => $kunci, 'nilai' => $body['nilai'], 'id_gudang' => $idGudang]);
        }

        Response::success(null, 'Pengaturan berhasil disimpan');
    }

    /**
     * GET /settings/users - Get all users
     */
    public function users(): void
    {
        $user = AuthMiddleware::getAuthUser();
        $data = $this->authService->getAllUsers(
            $user['role'] !== 'bos' ? (int)$user['id_gudang'] : null
        );
        Response::success($data);
    }

    /**
     * POST /settings/users - Create new user
     */
    public function storeUser(): void
    {
        $user = AuthMiddleware::getAuthUser();
        if ($user['role'] !== 'bos') Response::forbidden('Hanya Bos yang dapat menambah user');

        $body = Helper::getRequestBody();

        if (empty($body['name']) || empty($body['email']) || empty($body['password']) || empty($body['role'])) {
            Response::error('Name, email, password, dan role wajib diisi', 422);
        }

        // Check email unique
        $existing = Database::fetchOne("SELECT id FROM users WHERE email = ?", [$body['email']]);
        if ($existing) Response::error('Email sudah digunakan', 422);

        $id = $this->authService->createUser($body);
        Response::created(['id' => $id], 'User berhasil dibuat');
    }

    /**
     * PUT /settings/users/{id} - Update user
     */
    public function updateUser(string $id): void
    {
        $user = AuthMiddleware::getAuthUser();
        if ($user['role'] !== 'bos') Response::forbidden('Hanya Bos yang dapat mengubah user');

        $body = Helper::getRequestBody();
        $this->authService->updateUser((int)$id, $body);
        Response::success(null, 'User berhasil diperbarui');
    }

    /**
     * DELETE /settings/users/{id} - Deactivate user
     */
    public function deleteUser(string $id): void
    {
        $user = AuthMiddleware::getAuthUser();
        if ($user['role'] !== 'bos') Response::forbidden('Hanya Bos yang dapat menghapus user');

        $target = Database::fetchOne("SELECT role FROM users WHERE id = ?", [(int)$id]);
        if ($target && $target['role'] === 'bos') Response::error('Tidak dapat menonaktifkan akun Bos', 403);

        $this->authService->deleteUser((int)$id);
        Response::success(null, 'User berhasil dinonaktifkan');
    }

    /**
     * GET /settings/gudang - Get all warehouses
     */
    public function gudang(): void
    {
        AuthMiddleware::getAuthUser();
        $data = Database::fetchAll("SELECT * FROM gudang ORDER BY nama");
        Response::success($data);
    }

    /**
     * POST /settings/gudang - Create warehouse
     */
    public function storeGudang(): void
    {
        $user = AuthMiddleware::getAuthUser();
        if ($user['role'] !== 'bos') Response::forbidden('Hanya Bos yang dapat menambah gudang');

        $body = Helper::getRequestBody();
        if (empty($body['nama'])) Response::error('Nama gudang wajib diisi', 422);

        $id = Database::insert('gudang', [
            'nama'    => $body['nama'],
            'alamat'  => $body['alamat'] ?? null,
            'telepon' => $body['telepon'] ?? null,
        ]);

        Response::created(['id' => $id], 'Gudang berhasil ditambahkan');
    }

    /**
     * PUT /settings/gudang/{id} - Update warehouse
     */
    public function updateGudang(string $id): void
    {
        $user = AuthMiddleware::getAuthUser();
        if ($user['role'] !== 'bos') Response::forbidden('Hanya Bos yang dapat mengubah gudang');

        $body = Helper::getRequestBody();
        Database::update('gudang', [
            'nama'    => $body['nama'] ?? null,
            'alamat'  => $body['alamat'] ?? null,
            'telepon' => $body['telepon'] ?? null,
        ], 'id = ?', [(int)$id]);

        Response::success(null, 'Gudang berhasil diperbarui');
    }

    /**
     * POST /settings/backup - Database backup
     */
    public function backup(): void
    {
        $user = AuthMiddleware::getAuthUser();
        if ($user['role'] !== 'bos') Response::forbidden('Hanya Bos yang dapat melakukan backup');

        // Simple backup notification - full backup can be done via phpMyAdmin
        Response::success(['message' => 'Silakan gunakan phpMyAdmin untuk backup database secara manual']);
    }
}
