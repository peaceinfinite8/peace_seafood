<?php

declare(strict_types=1);

/**
 * Peace Seafood Highly Profitable Dummy Data Seeder
 * Run via terminal: php database/seed_profitable.php
 */

define('BASE_PATH', dirname(__DIR__));

echo "=== PEACE SEAFOOD: PROFITABLE DUMMY DATA SEEDER ===\n";
echo "Populating database with realistic, highly profitable business simulation data...\n\n";

// 1. Parse .env file manually
$envFile = BASE_PATH . '/.env';
if (!file_exists($envFile)) {
    die("Error: .env file not found at: {$envFile}\n");
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

$host = $envVars['DB_HOST'] ?? '127.0.0.1';
$port = $envVars['DB_PORT'] ?? '3306';
$database = $envVars['DB_NAME'] ?? 'peace_seafood';
$user = $envVars['DB_USER'] ?? 'root';
$password = $envVars['DB_PASSWORD'] ?? '';

$dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage() . "\n");
}

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // Clean current transactions and master data
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
        'supplier'
    ];

    foreach ($tablesToTruncate as $table) {
        $pdo->exec("TRUNCATE TABLE `{$table}`;");
    }
    echo "Existing transaction tables cleared.\n\n";

    // 2. Seed Clean Masters
    echo "Seeding clean master suppliers and buyers...\n";
    
    // Suppliers
    $pdo->exec("INSERT INTO `supplier` (`id`, `id_gudang`, `nama`, `nama_pemilik`, `telpon`, `alamat`, `kota`, `is_active`) VALUES
    (1, 1, 'CV Cahaya Bahari', 'Pak Joko Nelayan', '0812-4455-6677', 'Kawasan Pelabuhan Samudera Bitung', 'Bitung', 1),
    (2, 1, 'PT Juwana Seafood Indonesia', 'Hendra Wijaya', '0857-2233-4455', 'Jl. Pantai Indah Juwana KM 3', 'Pati', 1);");

    // Buyers
    $pdo->exec("INSERT INTO `pembeli` (`id`, `id_gudang`, `nama`, `telpon`, `alamat`, `kota`, `tipe`, `kredit_limit`, `is_active`) VALUES
    (1, 1, 'Umum', '-', '-', '-', 'retail', 0, 1),
    (2, 1, 'Restoran Ocean Star', '021-554433', 'Jl. Boulevard Kelapa Gading No. 10', 'Jakarta', 'bulk', 150000000, 1),
    (3, 1, 'Catering Bahagia Sejahtera', '0821-8899-22', 'Jl. Kebon Jeruk Baru No. 45', 'Jakarta', 'reseller', 50000000, 1);");

    // Products
    echo "Seeding clean master products with active profits...\n";
    $pdo->exec("INSERT INTO `produk` (`id`, `id_jenis_ikan`, `id_gudang`, `nama`, `deskripsi`, `satuan`, `size`, `grade`, `asal`, `harga_beli`, `harga_jual`, `stok_qty`, `nilai_stok`, `stok_minimum`, `is_active`) VALUES
    (1, 1, 1, 'Cakalang Premium L Bitung', 'Ikan Cakalang Premium Beku Kapal', 'kg', '1 Up', 'Grade A - Beku Kapal', 'Bitung', 30000, 45000, 500.00, 15000000, 100.00, 1),
    (2, 2, 1, 'Tongkol Segar Size 15 Kendari', 'Ikan Tongkol Tangkapan Nelayan', 'kg', 'Size 15', 'Grade A - Beku Kapal', 'Kendari', 22000, 32000, 300.00, 6600000, 100.00, 1),
    (3, 3, 1, 'Salem Impor Premium Jepang', 'Ikan Salem Impor Khusus Konsumsi', 'kg', '150/200', 'Grade A - Beku Kapal', 'Jepang', 28000, 38000, 700.00, 19600000, 100.00, 1),
    (4, 4, 1, 'Bandeng Super Sidoarjo', 'Ikan Bandeng Tambak Unggulan', 'kg', '4-5 pcs/kg', 'Grade A - Beku Kapal', 'Sidoarjo', 24000, 34000, 600.00, 14400000, 100.00, 1);");

    // 3. Seed Chronological Historical Transactions (Profit simulation)
    echo "Generating 7-day chronological transaction trace...\n";

    // Setup dates
    $d6 = date('Y-m-d', strtotime('-6 days'));
    $d5 = date('Y-m-d', strtotime('-5 days'));
    $d4 = date('Y-m-d', strtotime('-4 days'));
    $d3 = date('Y-m-d', strtotime('-3 days'));
    $d2 = date('Y-m-d', strtotime('-2 days'));
    $d1 = date('Y-m-d', strtotime('-1 day'));
    $d0 = date('Y-m-d');

    // 6 Days Ago: Salem Purchase & Sale
    $pdo->exec("INSERT INTO `stok_masuk` (`id`, `id_gudang`, `id_produk`, `id_supplier`, `qty`, `harga_beli`, `status`, `catatan`, `created_by`, `created_at`) VALUES
    (1, 1, 3, 2, 2000.00, 28000, 'confirmed', 'Pasokan Salem Jepang Impor', 3, '{$d6} 08:30:00');");
    $pdo->exec("INSERT INTO `timbangan` (`id_stok_masuk`, `id_produk`, `qty_teoritis`, `qty_actual`, `alasan_susut`, `created_by`, `created_at`) VALUES
    (1, 3, 2000.00, 2000.00, NULL, 4, '{$d6} 09:00:00');");
    
    $pdo->exec("INSERT INTO `nota` (`id`, `id_gudang`, `id_pembeli`, `no_nota`, `tanggal_nota`, `subtotal`, `diskon_nominal`, `pajak`, `total`, `pembayaran`, `status`, `catatan`, `created_by`, `created_at`) VALUES
    (1, 1, 1, 'PS-260519-0001', '{$d6}', 19000000, 0, 0, 19000000, 'cash', 'final', 'Lunas retail', 3, '{$d6} 14:00:00');");
    $pdo->exec("INSERT INTO `nota_detail` (`id_nota`, `id_produk`, `qty`, `harga_jual`, `subtotal`) VALUES
    (1, 3, 500.00, 38000, 19000000);");

    // 5 Days Ago: Cakalang Purchase & Sale
    $pdo->exec("INSERT INTO `stok_masuk` (`id`, `id_gudang`, `id_produk`, `id_supplier`, `qty`, `harga_beli`, `status`, `catatan`, `created_by`, `created_at`) VALUES
    (2, 1, 1, 1, 2000.00, 30000, 'confirmed', 'Pasokan Cakalang Bitung', 3, '{$d5} 08:30:00');");
    $pdo->exec("INSERT INTO `timbangan` (`id_stok_masuk`, `id_produk`, `qty_teoritis`, `qty_actual`, `alasan_susut`, `created_by`, `created_at`) VALUES
    (2, 1, 2000.00, 2000.00, NULL, 4, '{$d5} 09:00:00');");

    $pdo->exec("INSERT INTO `nota` (`id`, `id_gudang`, `id_pembeli`, `no_nota`, `tanggal_nota`, `subtotal`, `diskon_nominal`, `pajak`, `total`, `pembayaran`, `status`, `catatan`, `created_by`, `created_at`) VALUES
    (2, 1, 2, 'PS-260520-0001', '{$d5}', 36000000, 0, 0, 36000000, 'cash', 'final', 'Partai Restoran Ocean', 3, '{$d5} 14:00:00');");
    $pdo->exec("INSERT INTO `nota_detail` (`id_nota`, `id_produk`, `qty`, `harga_jual`, `subtotal`) VALUES
    (2, 1, 800.00, 45000, 36000000);");

    // 4 Days Ago: Tongkol Purchase & Sale
    $pdo->exec("INSERT INTO `stok_masuk` (`id`, `id_gudang`, `id_produk`, `id_supplier`, `qty`, `harga_beli`, `status`, `catatan`, `created_by`, `created_at`) VALUES
    (3, 1, 2, 1, 1500.00, 22000, 'confirmed', 'Pasokan Tongkol Kendari', 3, '{$d4} 08:30:00');");
    $pdo->exec("INSERT INTO `timbangan` (`id_stok_masuk`, `id_produk`, `qty_teoritis`, `qty_actual`, `alasan_susut`, `created_by`, `created_at`) VALUES
    (3, 2, 1500.00, 1500.00, NULL, 4, '{$d4} 09:00:00');");

    $pdo->exec("INSERT INTO `nota` (`id`, `id_gudang`, `id_pembeli`, `no_nota`, `tanggal_nota`, `subtotal`, `diskon_nominal`, `pajak`, `total`, `pembayaran`, `status`, `catatan`, `created_by`, `created_at`) VALUES
    (3, 1, 1, 'PS-260521-0001', '{$d4}', 38400000, 0, 0, 38400000, 'cash', 'final', 'Retail Tunai', 3, '{$d4} 14:00:00');");
    $pdo->exec("INSERT INTO `nota_detail` (`id_nota`, `id_produk`, `qty`, `harga_jual`, `subtotal`) VALUES
    (3, 2, 1200.00, 32000, 38400000);");

    // 3 Days Ago: Salem Purchase & Sale
    $pdo->exec("INSERT INTO `stok_masuk` (`id`, `id_gudang`, `id_produk`, `id_supplier`, `qty`, `harga_beli`, `status`, `catatan`, `created_by`, `created_at`) VALUES
    (4, 1, 3, 2, 2500.00, 28000, 'confirmed', 'Restock Kontainer Salem Jepang', 3, '{$d3} 08:30:00');");
    $pdo->exec("INSERT INTO `timbangan` (`id_stok_masuk`, `id_produk`, `qty_teoritis`, `qty_actual`, `alasan_susut`, `created_by`, `created_at`) VALUES
    (4, 3, 2500.00, 2500.00, NULL, 4, '{$d3} 09:00:00');");

    $pdo->exec("INSERT INTO `nota` (`id`, `id_gudang`, `id_pembeli`, `no_nota`, `tanggal_nota`, `subtotal`, `diskon_nominal`, `pajak`, `total`, `pembayaran`, `status`, `catatan`, `created_by`, `created_at`) VALUES
    (4, 1, 3, 'PS-260522-0001', '{$d3}', 68400000, 0, 0, 68400000, 'cash', 'final', 'Lunas Catering', 3, '{$d3} 14:00:00');");
    $pdo->exec("INSERT INTO `nota_detail` (`id_nota`, `id_produk`, `qty`, `harga_jual`, `subtotal`) VALUES
    (4, 3, 1800.00, 38000, 68400000);");

    // 2 Days Ago: Bandeng Purchase & Sale
    $pdo->exec("INSERT INTO `stok_masuk` (`id`, `id_gudang`, `id_produk`, `id_supplier`, `qty`, `harga_beli`, `status`, `catatan`, `created_by`, `created_at`) VALUES
    (5, 1, 4, 2, 1500.00, 24000, 'confirmed', 'Panen Tambak Bandeng', 3, '{$d2} 08:30:00');");
    $pdo->exec("INSERT INTO `timbangan` (`id_stok_masuk`, `id_produk`, `qty_teoritis`, `qty_actual`, `alasan_susut`, `created_by`, `created_at`) VALUES
    (5, 4, 1500.00, 1500.00, NULL, 4, '{$d2} 09:00:00');");

    $pdo->exec("INSERT INTO `nota` (`id`, `id_gudang`, `id_pembeli`, `no_nota`, `tanggal_nota`, `subtotal`, `diskon_nominal`, `pajak`, `total`, `pembayaran`, `status`, `catatan`, `created_by`, `created_at`) VALUES
    (5, 1, 1, 'PS-260523-0001', '{$d2}', 30600000, 0, 0, 30600000, 'cash', 'final', 'Lunas retail', 3, '{$d2} 14:00:00');");
    $pdo->exec("INSERT INTO `nota_detail` (`id_nota`, `id_produk`, `qty`, `harga_jual`, `subtotal`) VALUES
    (5, 4, 900.00, 34000, 30600000);");

    // Yesterday: Cakalang Purchase & Sale & Credit Sale (Piutang)
    $pdo->exec("INSERT INTO `stok_masuk` (`id`, `id_gudang`, `id_produk`, `id_supplier`, `qty`, `harga_beli`, `status`, `catatan`, `created_by`, `created_at`) VALUES
    (6, 1, 1, 1, 1500.00, 30000, 'confirmed', 'Restock Cakalang Bitung', 3, '{$d1} 08:30:00');");
    $pdo->exec("INSERT INTO `timbangan` (`id_stok_masuk`, `id_produk`, `qty_teoritis`, `qty_actual`, `alasan_susut`, `created_by`, `created_at`) VALUES
    (6, 1, 1500.00, 1500.00, NULL, 4, '{$d1} 09:00:00');");

    $pdo->exec("INSERT INTO `nota` (`id`, `id_gudang`, `id_pembeli`, `no_nota`, `tanggal_nota`, `subtotal`, `diskon_nominal`, `pajak`, `total`, `pembayaran`, `status`, `catatan`, `created_by`, `created_at`) VALUES
    (6, 1, 2, 'PS-260524-0001', '{$d1}', 54000000, 0, 0, 54000000, 'cash', 'final', 'Ocean Star Cash', 3, '{$d1} 14:00:00'),
    (7, 1, 3, 'PS-260524-0002', '{$d1}', 25000000, 0, 0, 25000000, 'hutang', 'final', 'Tempo Piutang Catering', 3, '{$d1} 15:30:00');");
    
    $pdo->exec("INSERT INTO `nota_detail` (`id_nota`, `id_produk`, `qty`, `harga_jual`, `subtotal`) VALUES
    (6, 1, 1200.00, 45000, 54000000),
    (7, 1, 555.50, 45000, 25000000);"); // credit sale represents piutang

    // Seed Piutang Record for Yesterday's Credit Sale
    $pdo->exec("INSERT INTO `hutang_piutang` (`id_gudang`, `jenis`, `id_pembeli`, `id_nota`, `no_referensi`, `nominal`, `nominal_bayar`, `jatuh_tempo`, `status`, `created_by`, `created_at`) VALUES
    (1, 'piutang', 3, 7, 'PS-260524-0002', 25000000, 0, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'open', 3, '{$d1} 15:30:00');");

    // Today: Highly Profitable Cash Sales
    $pdo->exec("INSERT INTO `nota` (`id`, `id_gudang`, `id_pembeli`, `no_nota`, `tanggal_nota`, `subtotal`, `diskon_nominal`, `pajak`, `total`, `pembayaran`, `status`, `catatan`, `created_by`, `created_at`) VALUES
    (8, 1, 1, 'PS-260525-0001', '{$d0}', 57000000, 0, 0, 57000000, 'cash', 'final', 'Retail Tunai Hari Ini', 3, '{$d0} 10:00:00'),
    (9, 1, 2, 'PS-260525-0002', '{$d0}', 45000000, 0, 0, 45000000, 'cash', 'final', 'Ocean Star Cash Hari Ini', 3, '{$d0} 11:30:00');");
    
    $pdo->exec("INSERT INTO `nota_detail` (`id_nota`, `id_produk`, `qty`, `harga_jual`, `subtotal`) VALUES
    (8, 3, 1500.00, 38000, 57000000),
    (9, 1, 1000.00, 45000, 45000000);");

    // Seed operational costs
    echo "Seeding operational costs...\n";
    $pdo->exec("INSERT INTO `biaya_operasional` (`id_gudang`, `kategori`, `deskripsi`, `nominal`, `tanggal`, `created_by`) VALUES
    (1, 'Gaji', 'Gaji bulanan checker & administrasi', 6500000, '{$d4}', 3),
    (1, 'Listrik', 'Token listrik cold storage utama', 2400000, '{$d3}', 3),
    (1, 'Operasional', 'Es balok untuk pengiriman retail', 800000, '{$d2}', 3);");

    // Seed one Open Hutang to show balanced dashboard complexity
    $pdo->exec("INSERT INTO `hutang_piutang` (`id_gudang`, `jenis`, `id_supplier`, `id_nota`, `no_referensi`, `nominal`, `nominal_bayar`, `jatuh_tempo`, `status`, `created_by`, `created_at`) VALUES
    (1, 'hutang', 1, NULL, 'INV-SS-7711', 15000000, 0, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'open', 3, '{$d2} 10:00:00');");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "\n=== PROFITABLE DUMMY DATA SEEDED SUCCESSFULLY ===\n";
    echo "Financial Analysis Generated:\n";
    echo "  - Total Cash Revenue (Penjualan): Rp 348,400,000\n";
    echo "  - Total Confirmed Purchases     : Rp 300,000,000\n";
    echo "  - Total Operating Expenses      : Rp 9,700,000\n";
    echo "  - Net Profit generated (Untung) : +Rp 38,700,000\n";
    echo "  - Total Piutang (Receivables)   : Rp 25,000,000\n";
    echo "  - Total Hutang (Payables)       : Rp 15,000,000\n";

} catch (PDOException $e) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    die("\n[ERROR] Seeding failed: " . $e->getMessage() . "\n");
}
