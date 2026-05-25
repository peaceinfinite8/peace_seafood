<?php

declare(strict_types=1);

/**
 * Peace Seafood Database Cleaner / Reset Tool
 * Run via terminal: php database/clean_data.php
 */

define('BASE_PATH', dirname(__DIR__));

echo "=== PEACE SEAFOOD: DATABASE CLEANER ===\n";
echo "Warning: This script will delete ALL transaction and master data (products, suppliers, buyers, etc.) to restore the app to a clean, fresh state.\n";
echo "Essential users, settings, and base categories will be retained.\n\n";

// 1. Parse .env file manually
$envFile = BASE_PATH . '/.env';
if (!file_exists($envFile)) {
    die("Error: .env file not found at: {$envFile}\n");
}

echo "Reading environment configurations...\n";
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

$host = $envVars['DB_HOST'] ?? '127.0.0.1';
$port = $envVars['DB_PORT'] ?? '3306';
$database = $envVars['DB_NAME'] ?? 'peace_seafood';
$user = $envVars['DB_USER'] ?? 'root';
$password = $envVars['DB_PASSWORD'] ?? '';

echo "Connecting to database `{$database}` on `{$host}:{$port}` as `{$user}`...\n";
$dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);
    echo "Connected successfully to database.\n\n";
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage() . "\n");
}

// 2. Perform Clean up with foreign keys temporarily disabled
echo "Initializing database truncation...\n";
try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    $tablesToTruncate = [
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
        'harga_history',
        'stok_opname',
        'produk',
        'pembeli',
        'supplier',
        'jenis_ikan',
        'users',
        'gudang',
        'settings'
    ];

    foreach ($tablesToTruncate as $table) {
        $pdo->exec("TRUNCATE TABLE `{$table}`;");
        echo "  [TRUNCATED] Table `{$table}` cleared.\n";
    }

    echo "\nSeeding essential clean base data...\n";

    // A. Seed Users
    echo "  -> Seeding clean users...\n";
    $pdo->exec("INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `id_gudang`, `is_active`) VALUES
    (1, 'Super Admin', 'superadmin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', NULL, 1),
    (2, 'Bos Gudang', 'bos@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bos', NULL, 1),
    (3, 'Admin Gudang A', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, 1),
    (4, 'Checker Gudang A', 'checker@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'checker', 1, 1),
    (5, 'Admin Gudang B', 'admin2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 2, 1);");

    // B. Seed Gudang
    echo "  -> Seeding clean warehouses...\n";
    $pdo->exec("INSERT INTO `gudang` (`id`, `id_bos`, `nama`, `alamat`, `kota`, `telpon`, `is_active`) VALUES
    (1, 2, 'Gudang Utama Bitung', 'Jl. Pelabuhan Samudera No. 42', 'Bitung', '0438-334455', 1),
    (2, 2, 'Gudang Cabang Jakarta', 'Jl. Muara Baru Ujung No. 12', 'Jakarta Utara', '021-667788', 1);");

    // Connect users to warehouses
    $pdo->exec("UPDATE `users` SET `id_gudang` = 1 WHERE `id` = 3;");
    $pdo->exec("UPDATE `users` SET `id_gudang` = 1 WHERE `id` = 4;");
    $pdo->exec("UPDATE `users` SET `id_gudang` = 2 WHERE `id` = 5;");

    // C. Seed Settings
    echo "  -> Seeding default configurations...\n";
    $pdo->exec("INSERT INTO `settings` (`id_gudang`, `kunci`, `nilai`, `deskripsi`) VALUES
    (1, 'multi_warehouse_aktif', '1', 'Multi warehouse feature aktif/nonaktif'),
    (1, 'stok_minimum_threshold', '100', 'Default batas stok minimum dalam kg'),
    (1, 'susut_alert_threshold', '5', 'Peringatan jika susut timbangan melebihi persen ini'),
    (1, 'komisi_penitipan_tipe', 'potong', 'Metode komisi: potong atau bayar_terpisah'),
    (1, 'komisi_penitipan_persen', '5', 'Persentase komisi default'),
    (1, 'pajak_default_persen', '0', 'Pajak penjualan default dalam %'),
    (1, 'jatuh_tempo_default_hari', '30', 'Jatuh tempo default untuk pembayaran piutang'),
    (1, 'session_timeout_menit', '60', 'Sesi login aktif dalam menit'),
    (1, 'onboarding_wizard_aktif', '0', 'Tampilkan wizard panduan user'),
    (1, 'backup_otomatis', '1', 'Pencadangan database otomatis'),
    (1, 'harga_locked_untuk', 'bos', 'Otoritas ubah harga produk'),
    (1, 'export_permission', 'admin', 'Otoritas export file laporan'),
    (1, 'company_name', 'Peace Seafood', 'Nama Identitas Gudang/Perusahaan Global'),
    (1, 'company_logo_initial', 'PS', 'Inisial Logo Sidebar Utama'),
    (1, 'kapasitas_cold_storage_kg', '10000', 'Kapasitas maksimal Cold Storage (kg) untuk indikator gauge'),
    (2, 'multi_warehouse_aktif', '1', 'Multi warehouse'),
    (2, 'stok_minimum_threshold', '100', 'Stok minimum'),
    (2, 'komisi_penitipan_tipe', 'potong', 'Metode komisi'),
    (2, 'pajak_default_persen', '0', 'Pajak default'),
    (2, 'jatuh_tempo_default_hari', '30', 'Jatuh tempo default'),
    (2, 'harga_locked_untuk', 'bos', 'Otoritas ubah harga'),
    (2, 'export_permission', 'admin', 'Otoritas export'),
    (2, 'kapasitas_cold_storage_kg', '5000', 'Kapasitas maksimal Cold Storage (kg) Cabang');");

    // D. Seed Standard Fish Categories
    echo "  -> Seeding base fish categories...\n";
    $pdo->exec("INSERT INTO `jenis_ikan` (`id`, `nama`, `deskripsi`, `allowed_sizes`, `allowed_grades`, `allowed_origins`, `is_active`) VALUES
    (1, 'Ikan Cakalang', 'Skipjack Tuna - komoditas utama ekspor & lokal', '200/300, 300/500, 1 Up, Size 10, Size 20, Polos', 'Grade A - Beku Kapal, Grade B - Beku Darat, Grade C - AC', 'Bitung, Banda, Makassar, Ambon', 1),
    (2, 'Ikan Tongkol', 'Mackerel Tuna - sangat diminati pasar retail lokal', '200/300, 300/500, 500/800, Size 15, Size 25', 'Grade A - Beku Kapal, Grade B - Beku Darat, Grade C - AC', 'Bitung, Kendari, Sibolga, Bali', 1),
    (3, 'Ikan Salem', 'Mackerel - produk impor premium untuk sarden/konsumsi', '100/150, 150/200, 200/300, Size 30, Size 40', 'Grade A - Beku Kapal, Grade B - Beku Darat, Grade C - AC', 'Jepang, Tiongkok, Cile', 1),
    (4, 'Ikan Bandeng', 'Milkfish - ikan air payau budidaya unggulan', '2-3 pcs/kg, 4-5 pcs/kg, 6-8 pcs/kg, Jumbo', 'Grade A - Beku Kapal, Grade B - Beku Darat, Grade C - AC', 'Sidoarjo, Juwana, Gresik, Pinrang', 1);");

    // E. Seed Default Fallback Customers
    echo "  -> Seeding clean master buyers...\n";
    $pdo->exec("INSERT INTO `pembeli` (`id`, `id_gudang`, `nama`, `telpon`, `alamat`, `kota`, `tipe`, `kredit_limit`, `is_active`) VALUES
    (1, 1, 'Umum', '-', '-', '-', 'retail', 0, 1),
    (2, 1, 'Restoran Ocean Star', '021-554433', 'Jl. Boulevard Kelapa Gading No. 10', 'Jakarta', 'bulk', 150000000, 1),
    (3, 1, 'Catering Bahagia Sejahtera', '0821-8899-22', 'Jl. Kebon Jeruk Baru No. 45', 'Jakarta', 'reseller', 50000000, 1);");

    // F. Seed Base Suppliers
    echo "  -> Seeding clean master suppliers...\n";
    $pdo->exec("INSERT INTO `supplier` (`id`, `id_gudang`, `nama`, `nama_pemilik`, `telpon`, `alamat`, `kota`, `is_active`) VALUES
    (1, 1, 'CV Cahaya Bahari', 'Pak Joko Nelayan', '0812-4455-6677', 'Kawasan Pelabuhan Samudera Bitung', 'Bitung', 1),
    (2, 1, 'PT Juwana Seafood Indonesia', 'Hendra Wijaya', '0857-2233-4455', 'Jl. Pantai Indah Juwana KM 3', 'Pati', 1);");

    // G. Seed Base Products
    echo "  -> Seeding clean master products...\n";
    $pdo->exec("INSERT INTO `produk` (`id`, `id_jenis_ikan`, `id_gudang`, `nama`, `deskripsi`, `satuan`, `size`, `grade`, `asal`, `harga_beli`, `harga_jual`, `stok_qty`, `nilai_stok`, `stok_minimum`, `is_active`) VALUES
    (1, 1, 1, 'Cakalang Premium L Bitung', 'Ikan Cakalang Premium Beku Kapal', 'kg', '1 Up', 'Grade A - Beku Kapal', 'Bitung', 30000, 45000, 500.00, 15000000, 100.00, 1),
    (2, 2, 1, 'Tongkol Segar Size 15 Kendari', 'Ikan Tongkol Tangkapan Nelayan', 'kg', 'Size 15', 'Grade A - Beku Kapal', 'Kendari', 22000, 32000, 300.00, 6600000, 100.00, 1),
    (3, 3, 1, 'Salem Impor Premium Jepang', 'Ikan Salem Impor Khusus Konsumsi', 'kg', '150/200', 'Grade A - Beku Kapal', 'Jepang', 28000, 38000, 700.00, 19600000, 100.00, 1),
    (4, 4, 1, 'Bandeng Super Sidoarjo', 'Ikan Bandeng Tambak Unggulan', 'kg', '4-5 pcs/kg', 'Grade A - Beku Kapal', 'Sidoarjo', 24000, 34000, 600.00, 14400000, 100.00, 1);");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "\n=== APPLICATION DATABASE CLEANED AND RESET SUCCESSFULLY ===\n";
    echo "Default accounts to log in:\n";
    echo "  - Super Admin : superadmin@example.com / password\n";
    echo "  - Bos Gudang  : bos@example.com / password\n";
    echo "  - Admin A     : admin@example.com / password\n";
    echo "  - Checker A   : checker@example.com / password\n";
    echo "  - Admin B     : admin2@example.com / password\n";

} catch (PDOException $e) {
    // Re-enable in case of failure
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    die("\n[ERROR] Database Clean up failed: " . $e->getMessage() . "\n");
}
