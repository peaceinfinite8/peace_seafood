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
        if (in_array($user['role'], ['bos', 'super_admin'], true)) {
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
            'company_name'              => ['label' => 'Nama Perusahaan', 'keterangan' => 'Nama identitas global perusahaan/gudang'],
            'company_logo_initial'      => ['label' => 'Inisial Logo', 'keterangan' => '2 karakter inisial logo di sidebar (default: PS)'],
            'company_logo_base64'       => ['label' => 'Logo Perusahaan (Gambar)', 'keterangan' => 'Data gambar logo perusahaan dalam format Base64 persegi'],
            'kapasitas_cold_storage_kg' => ['label' => 'Kapasitas Cold Storage (kg)', 'keterangan' => 'Kapasitas maksimal ruang penyimpanan dingin gudang saat ini'],
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
        if ($user['role'] !== 'super_admin') Response::forbidden('Hanya Super Admin yang dapat mengubah pengaturan');

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
            !in_array($user['role'], ['bos', 'super_admin'], true) ? (int)$user['id_gudang'] : null
        );
        Response::success($data);
    }

    /**
     * POST /settings/users - Create new user
     */
    public function storeUser(): void
    {
        $user = AuthMiddleware::getAuthUser();
        if ($user['role'] !== 'super_admin') Response::forbidden('Hanya Super Admin yang dapat menambah user');

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
        if ($user['role'] !== 'super_admin') Response::forbidden('Hanya Super Admin yang dapat mengubah user');

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
        if ($user['role'] !== 'super_admin') Response::forbidden('Hanya Super Admin yang dapat menghapus user');

        $target = Database::fetchOne("SELECT role FROM users WHERE id = ?", [(int)$id]);
        if ($target && in_array($target['role'], ['bos', 'super_admin'], true)) Response::error('Tidak dapat menonaktifkan akun Bos atau Super Admin', 403);

        $this->authService->deleteUser((int)$id);
        Response::success(null, 'User berhasil dinonaktifkan');
    }

    /**
     * GET /settings/gudang - Get all warehouses
     */
    public function gudang(): void
    {
        AuthMiddleware::getAuthUser();
        $data = Database::fetchAll("SELECT g.*, u.name as nama_bos FROM gudang g LEFT JOIN users u ON g.id_bos = u.id ORDER BY g.nama");
        Response::success($data);
    }

    /**
     * POST /settings/gudang - Create warehouse
     */
    public function storeGudang(): void
    {
        $user = AuthMiddleware::getAuthUser();
        if ($user['role'] !== 'super_admin') Response::forbidden('Hanya Super Admin yang dapat menambah gudang');

        $body = Helper::getRequestBody();
        if (empty($body['nama'])) Response::error('Nama gudang wajib diisi', 422);
        if (empty($body['id_bos'])) Response::error('Bos penanggung jawab wajib dipilih', 422);

        $id = Database::insert('gudang', [
            'nama'      => $body['nama'],
            'alamat'    => $body['alamat'] ?? '',
            'kota'      => $body['kota'] ?? '',
            'telpon'    => $body['telpon'] ?? null,
            'id_bos'    => (int)$body['id_bos'],
            'is_active' => isset($body['is_active']) ? (int)$body['is_active'] : 1
        ]);

        Response::created(['id' => $id], 'Gudang berhasil ditambahkan');
    }

    /**
     * PUT /settings/gudang/{id} - Update warehouse
     */
    public function updateGudang(string $id): void
    {
        $user = AuthMiddleware::getAuthUser();
        if ($user['role'] !== 'super_admin') Response::forbidden('Hanya Super Admin yang dapat mengubah gudang');

        $body = Helper::getRequestBody();
        if (empty($body['nama'])) Response::error('Nama gudang wajib diisi', 422);
        if (empty($body['id_bos'])) Response::error('Bos penanggung jawab wajib dipilih', 422);

        Database::update('gudang', [
            'nama'      => $body['nama'],
            'alamat'    => $body['alamat'] ?? '',
            'kota'      => $body['kota'] ?? '',
            'telpon'    => $body['telpon'] ?? null,
            'id_bos'    => (int)$body['id_bos'],
            'is_active' => isset($body['is_active']) ? (int)$body['is_active'] : 1
        ], 'id = ?', [(int)$id]);

        Response::success(null, 'Gudang berhasil diperbarui');
    }

    /**
     * DELETE /settings/gudang/{id} - Delete or deactivate warehouse
     */
    public function deleteGudang(string $id): void
    {
        $user = AuthMiddleware::getAuthUser();
        if ($user['role'] !== 'super_admin') Response::forbidden('Hanya Super Admin yang dapat menghapus gudang');

        $gudangId = (int)$id;

        $dependencyTables = [
            ['users', 'id_gudang'],
            ['supplier', 'id_gudang'],
            ['pembeli', 'id_gudang'],
            ['produk', 'id_gudang'],
            ['stok_masuk', 'id_gudang'],
            ['nota', 'id_gudang'],
            ['titipan', 'id_gudang'],
            ['retur', 'id_gudang'],
            ['hutang_piutang', 'id_gudang'],
            ['biaya_operasional', 'id_gudang'],
            ['settings', 'id_gudang'],
            ['stok_opname', 'id_gudang'],
        ];

        if (Database::hasTable('stok_transfer')) {
            $dependencyTables[] = ['stok_transfer', 'gudang_asal_id'];
            $dependencyTables[] = ['stok_transfer', 'gudang_tujuan_id'];
        }

        $hasDependencies = false;
        foreach ($dependencyTables as [$table, $column]) {
            $row = Database::fetchOne("SELECT id FROM {$table} WHERE {$column} = ? LIMIT 1", [$gudangId]);
            if ($row) {
                $hasDependencies = true;
                break;
            }
        }

        if ($hasDependencies) {
            Database::update('gudang', ['is_active' => 0], 'id = ?', [$gudangId]);
            Response::success(null, 'Gudang memiliki data historis transaksi. Status dinonaktifkan secara aman.');
            return;
        }

        Database::execute("DELETE FROM gudang WHERE id = ?", [$gudangId]);
        Response::success(null, 'Gudang berhasil dihapus dari sistem');
    }

    /**
     * POST /settings/backup - Database backup
     */
    public function backup(): void
    {
        $user = AuthMiddleware::getAuthUser();
        if ($user['role'] !== 'super_admin') Response::forbidden('Hanya Super Admin yang dapat melakukan backup');

        try {
            $pdo = Database::getInstance();
            $tables = [];
            $result = $pdo->query("SHOW TABLES");
            while ($row = $result->fetch(\PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            $sqlDump = "-- Peace Seafood Database Backup\n";
            $sqlDump .= "-- Generated on " . date('Y-m-d H:i:s') . "\n";
            $sqlDump .= "-- Host: " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\n\n";
            $sqlDump .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

            foreach ($tables as $table) {
                $sqlDump .= "DROP TABLE IF EXISTS `{$table}`;\n";

                $res = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                $sqlDump .= $res['Create Table'] . ";\n\n";

                $rows = $pdo->query("SELECT * FROM `{$table}`");
                while ($rowData = $rows->fetch(\PDO::FETCH_ASSOC)) {
                    $keys = array_keys($rowData);
                    $escapedKeys = array_map(fn($k) => "`{$k}`", $keys);
                    $values = array_values($rowData);
                    $escapedValues = array_map(function ($v) use ($pdo) {
                        if ($v === null) return 'NULL';
                        return $pdo->quote((string) $v);
                    }, $values);

                    $sqlDump .= "INSERT INTO `{$table}` (" . implode(', ', $escapedKeys) . ") VALUES (" . implode(', ', $escapedValues) . ");\n";
                }
                $sqlDump .= "\n";
            }

            $sqlDump .= "SET FOREIGN_KEY_CHECKS = 1;\n";

            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="peace_seafood_backup_' . date('Y-m-d') . '.sql"');
            header('Content-Length: ' . strlen($sqlDump));
            echo $sqlDump;
            exit;
        } catch (\Exception $e) {
            Response::error('Gagal membuat backup database: ' . $e->getMessage(), 500);
        }
    }
}
