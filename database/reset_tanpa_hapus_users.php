<?php

declare(strict_types=1);

/**
 * Peace Seafood — Reset Data Dummy (TANPA HAPUS USERS)
 * 
 * Script ini membersihkan:
 *   - Semua data transaksi (nota, stok_masuk, timbangan, hutang_piutang, dll)
 *   - Data master dummy (supplier, pembeli, produk, jenis_ikan, gudang)
 *   - Log aktivitas, notifikasi, biaya operasional
 * 
 * Yang TIDAK dihapus:
 *   - Tabel `users` — AMAN, tidak disentuh sama sekali
 * 
 * Jalankan via terminal: php database/reset_tanpa_hapus_users.php
 */

define('BASE_PATH', dirname(__DIR__));

echo "=======================================================\n";
echo "  PEACE SEAFOOD — RESET DATA DUMMY (USERS AMAN)\n";
echo "=======================================================\n\n";

// 1. Baca .env
$envFile = BASE_PATH . '/.env';
if (!file_exists($envFile)) {
    die("[ERROR] File .env tidak ditemukan di: {$envFile}\n");
}

$envVars = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    $parts = explode('=', $line, 2);
    if (count($parts) === 2) {
        $key = trim($parts[0]);
        $val = trim($parts[1], " \t\n\r\0\x0B\"'");
        $envVars[$key] = $val;
    }
}

$host     = $envVars['DB_HOST']     ?? '127.0.0.1';
$port     = $envVars['DB_PORT']     ?? '3306';
$database = $envVars['DB_NAME']     ?? 'peace_seafood';
$user     = $envVars['DB_USER']     ?? 'root';
$password = $envVars['DB_PASSWORD'] ?? '';

echo "[INFO] Menghubungkan ke database `{$database}` di {$host}:{$port}...\n";

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]
    );
    echo "[OK]   Koneksi berhasil.\n\n";
} catch (PDOException $e) {
    die("[ERROR] Koneksi gagal: " . $e->getMessage() . "\n");
}

// 2. Backup data users sebelum apapun dilakukan
echo "[INFO] Membaca dan mem-backup data users yang ada...\n";
$existingUsers = $pdo->query("SELECT * FROM `users`")->fetchAll(PDO::FETCH_ASSOC);

if (empty($existingUsers)) {
    echo "[WARN] Tidak ada user ditemukan di database. Proses tetap dilanjutkan.\n";
} else {
    echo "[OK]   Ditemukan " . count($existingUsers) . " user:\n";
    foreach ($existingUsers as $u) {
        echo "       - [{$u['role']}] {$u['name']} ({$u['email']}) | gudang: " . ($u['id_gudang'] ?? 'NULL') . "\n";
    }
}

echo "\n[INFO] Memulai proses pembersihan data dummy...\n";
echo "-------------------------------------------------------\n";

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 3. Hapus semua data transaksi dan master (KECUALI users)
    $tablesToTruncate = [
        'activity_log',
        'notifikasi',
        'hutang_piutang_history',
        'hutang_piutang',
        'biaya_operasional',
        'retur',
        'titipan_penjualan',
        'titipan',
        'nota_detail',
        'nota',
        'timbangan',
        'stok_masuk',
        'stok_opname',
        'harga_history',
        'produk',
        'pembeli',
        'supplier',
        'jenis_ikan',
        'settings',
        'gudang',
        // USERS TIDAK ADA DI SINI — SENGAJA DIKOSONGKAN
    ];

    foreach ($tablesToTruncate as $table) {
        $pdo->exec("TRUNCATE TABLE `{$table}`;");
        echo "  [TRUNCATED] `{$table}`\n";
    }

    echo "\n[INFO] Semua tabel transaksi & master berhasil dikosongkan.\n";
    echo "[INFO] Tabel `users` TIDAK disentuh.\n\n";

    // 4. Seed ulang gudang (diperlukan karena users.id_gudang FK ke gudang)
    echo "[INFO] Menyemai ulang data gudang...\n";
    $pdo->exec("INSERT INTO `gudang` (`id`, `id_bos`, `nama`, `alamat`, `kota`, `telpon`, `is_active`) VALUES
        (1, 2, 'Gudang Utama Bitung',    'Jl. Pelabuhan Samudera No. 42', 'Bitung',        '0438-334455', 1),
        (2, 2, 'Gudang Cabang Jakarta',  'Jl. Muara Baru Ujung No. 12',   'Jakarta Utara', '021-667788',  1)
    ;");
    echo "  [OK] Gudang Utama Bitung & Gudang Cabang Jakarta ditambahkan.\n";

    // 5. Seed ulang settings
    echo "[INFO] Menyemai ulang konfigurasi settings...\n";
    $pdo->exec("INSERT INTO `settings` (`id_gudang`, `kunci`, `nilai`, `deskripsi`) VALUES
        (1, 'multi_warehouse_aktif',      '1',            'Multi warehouse feature aktif/nonaktif'),
        (1, 'stok_minimum_threshold',     '100',          'Default batas stok minimum dalam kg'),
        (1, 'susut_alert_threshold',      '5',            'Peringatan jika susut timbangan melebihi persen ini'),
        (1, 'komisi_penitipan_tipe',      'potong',       'Metode komisi: potong atau bayar_terpisah'),
        (1, 'komisi_penitipan_persen',    '5',            'Persentase komisi default'),
        (1, 'pajak_default_persen',       '0',            'Pajak penjualan default dalam %'),
        (1, 'jatuh_tempo_default_hari',   '30',           'Jatuh tempo default untuk pembayaran piutang'),
        (1, 'session_timeout_menit',      '60',           'Sesi login aktif dalam menit'),
        (1, 'onboarding_wizard_aktif',    '0',            'Tampilkan wizard panduan user'),
        (1, 'backup_otomatis',            '1',            'Pencadangan database otomatis'),
        (1, 'harga_locked_untuk',         'bos',          'Otoritas ubah harga produk'),
        (1, 'export_permission',          'admin',        'Otoritas export file laporan'),
        (1, 'company_name',               'Peace Seafood','Nama Identitas Gudang/Perusahaan Global'),
        (1, 'company_logo_initial',       'PS',           'Inisial Logo Sidebar Utama'),
        (1, 'kapasitas_cold_storage_kg',  '10000',        'Kapasitas maksimal Cold Storage (kg) untuk indikator gauge'),
        (2, 'multi_warehouse_aktif',      '1',            'Multi warehouse'),
        (2, 'stok_minimum_threshold',     '100',          'Stok minimum'),
        (2, 'komisi_penitipan_tipe',      'potong',       'Metode komisi'),
        (2, 'pajak_default_persen',       '0',            'Pajak default'),
        (2, 'jatuh_tempo_default_hari',   '30',           'Jatuh tempo default'),
        (2, 'harga_locked_untuk',         'bos',          'Otoritas ubah harga'),
        (2, 'export_permission',          'admin',        'Otoritas export'),
        (2, 'kapasitas_cold_storage_kg',  '5000',         'Kapasitas maksimal Cold Storage (kg) Cabang')
    ;");
    echo "  [OK] Settings default untuk Gudang 1 & 2 ditambahkan.\n";

    // 6. Re-enable FK dan verifikasi users masih ada
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "\n[INFO] Memverifikasi data users masih aman...\n";
    $verifyUsers = $pdo->query("SELECT id, name, email, role, id_gudang, is_active FROM `users` ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($verifyUsers)) {
        echo "[WARN] Tidak ada user ditemukan setelah proses! Periksa database.\n";
    } else {
        echo "[OK]   " . count($verifyUsers) . " user masih ada dan aman:\n";
        foreach ($verifyUsers as $u) {
            $gudangInfo = $u['id_gudang'] ? "gudang #{$u['id_gudang']}" : "semua gudang";
            $status     = $u['is_active'] ? 'aktif' : 'nonaktif';
            echo "       ✓ [{$u['role']}] {$u['name']} ({$u['email']}) — {$gudangInfo} — {$status}\n";
        }
    }

    // 7. Verifikasi tabel transaksi kosong
    echo "\n[INFO] Memverifikasi tabel transaksi sudah kosong...\n";
    $checkTables = ['nota', 'stok_masuk', 'supplier', 'pembeli', 'produk', 'hutang_piutang', 'notifikasi'];
    foreach ($checkTables as $tbl) {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$tbl}`")->fetchColumn();
        $status = ($count == 0) ? '[OK]   KOSONG' : "[WARN] Masih ada {$count} baris";
        echo "  {$status} — `{$tbl}`\n";
    }

    echo "\n=======================================================\n";
    echo "  SELESAI — DATABASE BERHASIL DIBERSIHKAN\n";
    echo "=======================================================\n";
    echo "\nRingkasan:\n";
    echo "  ✓ Semua data transaksi dihapus (nota, stok, hutang, dll)\n";
    echo "  ✓ Data master dummy dihapus (supplier, pembeli, produk)\n";
    echo "  ✓ Gudang & settings di-reset ke kondisi bersih\n";
    echo "  ✓ Semua akun users TETAP AMAN dan tidak diubah\n";
    echo "\nAplikasi siap digunakan dengan data nyata.\n\n";

} catch (PDOException $e) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    die("\n[ERROR] Proses gagal: " . $e->getMessage() . "\n");
}
