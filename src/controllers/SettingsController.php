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
        // Hanya super_admin yang boleh akses halaman settings penuh
        // bos boleh baca settings gudangnya sendiri (read-only)
        \App\Middleware\RoleMiddleware::requirePermission('settings.view');

        $user = AuthMiddleware::getAuthUser();

        // Determine which settings to show:
        // - `super_admin` and `saas_owner` view global (id_gudang IS NULL) settings
        // - `bos` views their own warehouse settings + global settings
        // - other roles (admin/checker) see their assigned gudang settings only
        // Allow platform users to optionally request settings for a specific gudang via ?id_gudang=ID
        $requestedGudang = isset($_GET['id_gudang']) ? (int)$_GET['id_gudang'] : null;
        if (in_array($user['role'], ['super_admin', 'saas_owner'], true)) {
            if ($requestedGudang && $requestedGudang > 0) {
                // Show both global and selected gudang settings
                $data = Database::fetchAll(
                    "SELECT kunci, nilai, deskripsi AS keterangan FROM settings WHERE id_gudang IS NULL OR id_gudang = ? GROUP BY kunci ORDER BY kunci",
                    [$requestedGudang]
                );
            } else {
                // platform-level users: show global settings
                $data = Database::fetchAll(
                    "SELECT kunci, nilai, deskripsi AS keterangan FROM settings WHERE id_gudang IS NULL ORDER BY kunci"
                );
            }
        } elseif ($user['role'] === 'bos') {
            $idGudang = (int)($user['id_gudang'] ?? 0);
            if ($idGudang > 0) {
                $data = Database::fetchAll(
                    "SELECT kunci, nilai, deskripsi AS keterangan FROM settings WHERE id_gudang IS NULL OR id_gudang = ? GROUP BY kunci ORDER BY kunci",
                    [$idGudang]
                );
            } else {
                $data = Database::fetchAll(
                    "SELECT kunci, nilai, deskripsi AS keterangan FROM settings WHERE id_gudang IS NULL ORDER BY kunci"
                );
            }
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
        if ($user['role'] !== 'super_admin' && $user['role'] !== 'saas_owner' && $user['role'] !== 'bos') {
            Response::forbidden('Hanya Super Admin / SaaS Owner / Bos yang dapat mengubah pengaturan');
        }

        // Proteksi: Bos tidak boleh mengubah setting global milik developer
        $developerOnlyKeys = ['platform_developer_whatsapp'];
        if ($user['role'] === 'bos' && in_array($kunci, $developerOnlyKeys, true)) {
            Response::forbidden('Pengaturan ini hanya dapat diubah oleh Developer / SaaS Owner.');
        }

        $body = Helper::getRequestBody();
        if (!isset($body['nilai'])) Response::error('Nilai wajib diisi', 422);

        // Support optional ?id_gudang for platform users to update a specific warehouse setting
        $requestedGudang = isset($_GET['id_gudang']) ? (int)$_GET['id_gudang'] : null;

        if (in_array($user['role'], ['super_admin', 'saas_owner'], true) && $requestedGudang && $requestedGudang > 0) {
            // Validate gudang exists
            $g = Database::fetchOne('SELECT id FROM gudang WHERE id = ? AND is_active = 1', [$requestedGudang]);
            if (!$g) Response::error('Gudang tidak ditemukan', 422);

            $existing = Database::fetchOne(
                "SELECT id FROM settings WHERE kunci = ? AND id_gudang = ?",
                [$kunci, $requestedGudang]
            );

            if ($existing) {
                Database::update('settings', ['nilai' => $body['nilai']], 'kunci = ? AND id_gudang = ?', [$kunci, $requestedGudang]);
            } else {
                Database::insert('settings', ['kunci' => $kunci, 'nilai' => $body['nilai'], 'id_gudang' => $requestedGudang]);
            }
        } elseif (in_array($user['role'], ['super_admin', 'saas_owner'], true)) {
            // Platform-level default: global setting (id_gudang IS NULL)
            $existing = Database::fetchOne(
                "SELECT id FROM settings WHERE kunci = ? AND id_gudang IS NULL",
                [$kunci]
            );

            if ($existing) {
                Database::update('settings', ['nilai' => $body['nilai']], 'kunci = ? AND id_gudang IS NULL', [$kunci]);
            } else {
                Database::insert('settings', ['kunci' => $kunci, 'nilai' => $body['nilai'], 'id_gudang' => null]);
            }
        } else {
            // Warehouse-level update: use user's id_gudang (or default 1)
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
        }

        Response::success(null, 'Pengaturan berhasil disimpan');
    }

    /**
     * GET /settings/users - Get all users
     */
    public function users(): void
    {
        $user = AuthMiddleware::getAuthUser();
        $data = $this->authService->getAllUsers($user);
        Response::success($data);
    }

    /**
     * POST /settings/pre-approve - Developer Pre-Approve Bos Email & Setup Trial
     */
    public function preApproveUser(): void
    {
        $user = AuthMiddleware::getAuthUser();
        if (!in_array($user['role'], ['super_admin', 'saas_owner'], true)) {
            Response::forbidden('Hanya SaaS Owner / Super Admin yang dapat menyetujui pendaftaran Bos baru.');
        }

        $body = Helper::getRequestBody();

        if (empty($body['name']) || empty($body['email'])) {
            Response::error('Nama dan Email wajib diisi', 422);
        }

        $name = trim($body['name']);
        $email = trim($body['email']);
        $trialDays = isset($body['trial_days']) ? (int)$body['trial_days'] : 14;

        if ($trialDays <= 0) {
            Response::error('Durasi trial hari tidak valid.', 422);
        }

        // Verify email unique
        $existing = Database::fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            Response::error('Email sudah digunakan di sistem.', 422);
        }

        Database::beginTransaction();
        try {
            // 1. Create Bos user in pending_signup state
            $userId = Database::insert('users', [
                'name' => $name,
                'email' => $email,
                'password' => '', // blank password, will be filled during signup
                'role' => 'bos',
                'id_gudang' => null,
                'registration_status' => 'pending_signup',
                'is_first_login' => 1,
                'is_active' => 1
            ]);

            // 2. Create blank Gudang for this Bos with trial_days
            $gudangId = Database::insert('gudang', [
                'nama' => 'Gudang ' . $name,
                'alamat' => '',
                'kota' => '',
                'id_bos' => $userId,
                'trial_days' => $trialDays,
                'subscription_until' => null, // countdown starts after onboarding selesai!
                'status_langganan' => 'aktif',
                'is_active' => 1
            ]);

            // 3. Connect Bos user to this Gudang
            Database::update('users', [
                'id_gudang' => $gudangId
            ], 'id = ?', [$userId]);

            Database::commit();
            Response::success(['user_id' => $userId, 'gudang_id' => $gudangId], 'Pendaftaran email Bos sukses disetujui dengan trial ' . $trialDays . ' hari!');
        } catch (\Exception $e) {
            Database::rollBack();
            Response::error('Gagal memproses pre-approval: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /onboarding/complete - Save setup, seed fish list, and activate official trial date
     */
    public function completeOnboarding(): void
    {
        $user = AuthMiddleware::getAuthUser();

        if ($user['role'] !== 'bos') {
            Response::forbidden('Hanya Pemilik Gudang (Bos) yang dapat melakukan setup onboarding.');
        }

        $gudangId = (int)($user['id_gudang'] ?? 0);
        if ($gudangId <= 0) {
            Response::error('Akun Bos tidak terhubung dengan Gudang.', 422);
        }

        $body = Helper::getRequestBody();

        if (empty($body['nama_gudang'])) {
            Response::error('Nama gudang wajib diisi.', 422);
        }

        $namaGudang = trim($body['nama_gudang']);
        $alamat = trim($body['alamat'] ?? '');
        $kota = trim($body['kota'] ?? '');
        $ikanPilihan = $body['ikan_pilihan'] ?? [];

        // Verify Bos ownership of this warehouse
        $gudang = Database::fetchOne("SELECT id, trial_days FROM gudang WHERE id = ? AND id_bos = ? AND is_active = 1", [$gudangId, $user['id']]);
        if (!$gudang) {
            Response::forbidden('Anda tidak memiliki izin mengelola gudang ini.');
        }

        Database::beginTransaction();
        try {
            // 1. Calculate & set official trial subscription countdown date
            $trialDays = (int)($gudang['trial_days'] ?? 14);
            $subscriptionUntil = date('Y-m-d', strtotime('+' . $trialDays . ' days'));

            // 2. Update Warehouse Details
            Database::update('gudang', [
                'nama' => $namaGudang,
                'alamat' => $alamat,
                'kota' => $kota,
                'subscription_until' => $subscriptionUntil,
                'status_langganan' => 'aktif'
            ], 'id = ?', [$gudangId]);

            // 3. Seed Chosen Fish List
            if (is_array($ikanPilihan) && !empty($ikanPilihan)) {
                foreach ($ikanPilihan as $namaIkan) {
                    $namaIkan = trim((string)$namaIkan);
                    if (empty($namaIkan)) continue;

                    // Sync Jenis Ikan (global)
                    $jenis = Database::fetchOne("SELECT id FROM jenis_ikan WHERE nama = ? AND is_active = 1", [$namaIkan]);
                    if (!$jenis) {
                        $jenisId = Database::insert('jenis_ikan', [
                            'nama' => $namaIkan,
                            'is_active' => 1
                        ]);
                    } else {
                        $jenisId = (int)$jenis['id'];
                    }

                    // Sync Produk (Warehouse Specific)
                    $produk = Database::fetchOne("SELECT id FROM produk WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$namaIkan, $gudangId]);
                    if (!$produk) {
                        Database::insert('produk', [
                            'id_jenis_ikan' => $jenisId,
                            'id_gudang' => $gudangId,
                            'nama' => $namaIkan,
                            'harga_beli' => 0,
                            'harga_jual' => 0,
                            'stok_qty' => 0,
                            'nilai_stok' => 0,
                            'is_active' => 1
                        ]);
                    }
                }
            }

            // 4. Save onboarding_completed = 1 flag in settings
            $existingSetting = Database::fetchOne(
                "SELECT id FROM settings WHERE id_gudang = ? AND kunci = 'onboarding_completed'",
                [$gudangId]
            );

            if ($existingSetting) {
                Database::update('settings', ['nilai' => '1'], 'id = ?', [$existingSetting['id']]);
            } else {
                Database::insert('settings', [
                    'id_gudang' => $gudangId,
                    'kunci' => 'onboarding_completed',
                    'nilai' => '1',
                    'deskripsi' => 'Flag status onboarding telah selesai untuk gudang ini'
                ]);
            }

            Database::commit();
            Response::success(
                ['subscription_until' => $subscriptionUntil],
                'Setup onboarding sukses! Masa uji coba gratis ' . $trialDays . ' hari Anda resmi dimulai dari sekarang!'
            );
        } catch (\Exception $e) {
            Database::rollBack();
            Response::error('Gagal menyelesaikan onboarding: ' . $e->getMessage(), 500);
        }
    }

    public function storeUser(): void
    {
        $user = AuthMiddleware::getAuthUser();
        if (!in_array($user['role'], ['super_admin', 'saas_owner', 'bos'], true)) {
            Response::forbidden('Hanya Super Admin / SaaS Owner / Bos yang dapat menambah user');
        }

        $body = Helper::getRequestBody();

        if (empty($body['name']) || empty($body['email']) || empty($body['password']) || empty($body['role'])) {
            Response::error('Name, email, password, dan role wajib diisi', 422);
        }

        if ($user['role'] === 'bos') {
            if (in_array($body['role'], ['super_admin', 'saas_owner', 'bos'], true)) {
                Response::forbidden('Bos tidak diperbolehkan membuat role Admin Utama/Bos.');
            }
            $idGudang = (int)($body['id_gudang'] ?? 0);
            $gudangExists = Database::fetchOne("SELECT id FROM gudang WHERE id = ? AND id_bos = ?", [$idGudang, $user['id']]);
            if (!$gudangExists) {
                Response::error('Gudang alokasi tidak valid untuk organisasi Anda.', 422);
            }
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
        if (!in_array($user['role'], ['super_admin', 'saas_owner', 'bos'], true)) {
            Response::forbidden('Hanya Super Admin / SaaS Owner / Bos yang dapat mengubah user');
        }

        $targetUser = Database::fetchOne("SELECT role, id_gudang FROM users WHERE id = ?", [(int)$id]);
        if (!$targetUser) {
            Response::notFound('User tidak ditemukan.');
        }

        $body = Helper::getRequestBody();

        if ($user['role'] === 'bos') {
            $gudangExists = Database::fetchOne("SELECT id FROM gudang WHERE id = ? AND id_bos = ?", [(int)$targetUser['id_gudang'], $user['id']]);
            if (!$gudangExists && (int)$id !== (int)$user['id']) {
                Response::forbidden('Anda tidak memiliki akses untuk mengubah user ini.');
            }

            if (isset($body['role']) && in_array($body['role'], ['super_admin', 'saas_owner', 'bos'], true)) {
                Response::forbidden('Bos tidak diperbolehkan mengubah role menjadi Admin Utama/Bos.');
            }

            if (isset($body['id_gudang'])) {
                $targetGudang = (int)$body['id_gudang'];
                $gudangExists = Database::fetchOne("SELECT id FROM gudang WHERE id = ? AND id_bos = ?", [$targetGudang, $user['id']]);
                if (!$gudangExists) {
                    Response::error('Gudang alokasi tidak valid untuk organisasi Anda.', 422);
                }
            }
        }

        $this->authService->updateUser((int)$id, $body);
        Response::success(null, 'User berhasil diperbarui');
    }

    /**
     * DELETE /settings/users/{id} - Deactivate user
     */
    public function deleteUser(string $id): void
    {
        $user = AuthMiddleware::getAuthUser();
        if (!in_array($user['role'], ['super_admin', 'saas_owner', 'bos'], true)) {
            Response::forbidden('Hanya Super Admin / SaaS Owner / Bos yang dapat menghapus user');
        }

        $target = Database::fetchOne("SELECT role, id_gudang FROM users WHERE id = ?", [(int)$id]);
        if (!$target) {
            Response::notFound('User tidak ditemukan.');
        }

        if ($user['role'] === 'bos') {
            $gudangExists = Database::fetchOne("SELECT id FROM gudang WHERE id = ? AND id_bos = ?", [(int)$target['id_gudang'], $user['id']]);
            if (!$gudangExists) {
                Response::forbidden('Anda tidak memiliki akses untuk menghapus user ini.');
            }
        }

        if (in_array($target['role'], ['bos', 'super_admin', 'saas_owner'], true)) {
            Response::error('Tidak dapat menonaktifkan akun Bos, Super Admin, atau SaaS Owner', 403);
        }

        $this->authService->deleteUser((int)$id);
        Response::success(null, 'User berhasil dinonaktifkan');
    }

    /**
     * GET /settings/gudang - Get all warehouses
     */
    public function gudang(): void
    {
        $user = AuthMiddleware::getAuthUser();
        if ($user['role'] === 'bos') {
            $data = Database::fetchAll(
                "SELECT g.*, u.name as nama_bos FROM gudang g LEFT JOIN users u ON g.id_bos = u.id WHERE g.id_bos = ? ORDER BY g.nama",
                [$user['id']]
            );
        } else {
            $data = Database::fetchAll("SELECT g.*, u.name as nama_bos FROM gudang g LEFT JOIN users u ON g.id_bos = u.id ORDER BY g.nama");
        }
        Response::success($data);
    }

    /**
     * POST /settings/gudang - Create warehouse
     */
    public function storeGudang(): void
    {
        $user = AuthMiddleware::getAuthUser();
        if (!in_array($user['role'], ['super_admin', 'saas_owner', 'bos'], true)) {
            Response::forbidden('Hanya Super Admin / SaaS Owner / Bos yang dapat menambah gudang');
        }

        $body = Helper::getRequestBody();
        if (empty($body['nama'])) Response::error('Nama gudang wajib diisi', 422);

        $idBos = $user['role'] === 'bos' ? (int)$user['id'] : (int)($body['id_bos'] ?? $user['id']);

        $id = Database::insert('gudang', [
            'nama'      => $body['nama'],
            'alamat'    => $body['alamat'] ?? '',
            'kota'      => $body['kota'] ?? '',
            'telpon'    => $body['telpon'] ?? null,
            'id_bos'    => $idBos,
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
        if ($user['role'] === 'bos') {
            $own = Database::fetchOne("SELECT id FROM gudang WHERE id = ? AND id_bos = ?", [(int)$id, $user['id']]);
            if (!$own) Response::forbidden('Anda tidak memiliki akses untuk mengubah gudang ini.');
        } else if (!in_array($user['role'], ['super_admin', 'saas_owner'], true)) {
            Response::forbidden('Hanya Super Admin / SaaS Owner / Bos yang dapat mengubah gudang');
        }

        $body = Helper::getRequestBody();

        // If it's a SaaS subscription/status update (only allowed for super_admin/saas_owner)
        if (isset($body['subscription_until']) || isset($body['status_langganan'])) {
            if (!in_array($user['role'], ['super_admin', 'saas_owner'], true)) {
                Response::forbidden('Hanya Super Admin / SaaS Owner yang dapat memperbarui langganan.');
            }
            $updateData = [];
            if (isset($body['subscription_until'])) {
                $updateData['subscription_until'] = !empty($body['subscription_until']) ? $body['subscription_until'] : null;
            }
            if (isset($body['status_langganan'])) {
                $updateData['status_langganan'] = $body['status_langganan'];
            }
            if (isset($body['is_active'])) {
                $updateData['is_active'] = (int)$body['is_active'];
            }

            if (!empty($updateData)) {
                Database::update('gudang', $updateData, 'id = ?', [(int)$id]);
            }
            Response::success(null, 'Status langganan gudang berhasil diperbarui');
            return;
        }

        if (empty($body['nama'])) Response::error('Nama gudang wajib diisi', 422);

        $updateFields = [
            'nama'      => $body['nama'],
            'alamat'    => $body['alamat'] ?? '',
            'kota'      => $body['kota'] ?? '',
            'telpon'    => $body['telpon'] ?? null,
            'is_active' => isset($body['is_active']) ? (int)$body['is_active'] : 1
        ];

        // Only super_admin / saas_owner can re-assign id_bos
        if (in_array($user['role'], ['super_admin', 'saas_owner'], true)) {
            if (empty($body['id_bos'])) Response::error('Bos penanggung jawab wajib dipilih', 422);
            $updateFields['id_bos'] = (int)$body['id_bos'];
        }

        Database::update('gudang', $updateFields, 'id = ?', [(int)$id]);

        Response::success(null, 'Gudang berhasil diperbarui');
    }

    /**
     * DELETE /settings/gudang/{id} - Delete or deactivate warehouse
     */
    public function deleteGudang(string $id): void
    {
        $user = AuthMiddleware::getAuthUser();
        if ($user['role'] === 'bos') {
            $own = Database::fetchOne("SELECT id FROM gudang WHERE id = ? AND id_bos = ?", [(int)$id, $user['id']]);
            if (!$own) Response::forbidden('Anda tidak memiliki akses untuk menghapus gudang ini.');
        } else if (!in_array($user['role'], ['super_admin', 'saas_owner'], true)) {
            Response::forbidden('Hanya Super Admin / SaaS Owner / Bos yang dapat menghapus gudang');
        }

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
        if (!in_array($user['role'], ['super_admin', 'saas_owner'], true)) Response::forbidden('Hanya Super Admin atau SaaS Owner yang dapat melakukan backup');

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
