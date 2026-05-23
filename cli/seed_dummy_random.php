<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$root = dirname(__DIR__);

loadEnvironment($root . '/.env');

$pdo = require $root . '/config/database.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$users = fetchUsers($pdo);
$bosId = requireUserId($users, ['bos@example.com'], 'bos');
$adminId = requireUserId($users, ['admin@example.com', 'admin2@example.com'], 'admin');
$checkerId = requireUserId($users, ['checker@example.com'], 'checker');

$tablesToReset = [
    'notifikasi',
    'stok_opname',
    'biaya_operasional',
    'hutang_piutang_history',
    'hutang_piutang',
    'retur',
    'titipan_penjualan',
    'titipan',
    'nota_detail',
    'nota',
    'harga_history',
    'timbangan',
    'stok_masuk',
    'settings',
    'produk',
    'pembeli',
    'supplier',
    'jenis_ikan',
    'gudang',
];

try {
    resetTables($pdo, $tablesToReset);

    $pdo->beginTransaction();

    $gudang = seedGudang($pdo, $bosId);
    $jenisIkan = seedJenisIkan($pdo);
    $supplier = seedSupplier($pdo, $gudang);
    $pembeli = seedPembeli($pdo, $gudang);
    $produk = seedProduk($pdo, $gudang, $jenisIkan);
    seedSettings($pdo, $gudang);

    $stokContext = seedStokMasukAndTimbangan($pdo, $gudang, $produk, $supplier, [$adminId, $checkerId, $bosId]);
    seedHargaHistory($pdo, $produk, [$bosId, $adminId]);

    $notaContext = seedNotaAndDetails($pdo, $gudang, $produk, $pembeli, [$adminId, $checkerId, $bosId]);
    seedHutangPiutang($pdo, $gudang, $supplier, $pembeli, $notaContext, [$bosId, $adminId], $stokContext['purchaseRows']);
    seedHutangPiutangHistory($pdo, [$bosId, $adminId]);

    seedTitipanAndSales($pdo, $gudang, $supplier, $pembeli, $produk, [$adminId, $checkerId]);
    seedRetur($pdo, $gudang, $produk, $supplier, $pembeli, $notaContext['notaRows'], [$adminId, $bosId, $checkerId]);
    seedBiayaOperasional($pdo, $gudang, [$adminId, $checkerId, $bosId]);
    seedNotifikasi($pdo, $users, $notaContext['notaRows'], $stokContext['stokRows']);
    seedStokOpname($pdo, $gudang, [$adminId, $checkerId, $bosId]);

    $pdo->commit();

    echo "Random dummy seeder completed successfully.\n";
    echo "Seeded tables: gudang, jenis_ikan, supplier, pembeli, produk, settings, stok_masuk, timbangan, harga_history, nota, nota_detail, hutang_piutang, hutang_piutang_history, titipan, titipan_penjualan, retur, biaya_operasional, notifikasi, stok_opname.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Seeder failed: " . $e->getMessage() . "\n");
    fwrite(STDERR, $e->getTraceAsString() . "\n");
    exit(1);
}

function loadEnvironment(string $envPath): void
{
    if (!is_file($envPath)) {
        return;
    }

    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key === '') {
            continue;
        }

        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key . '=' . $value);
    }
}

function fetchUsers(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT id, email, role FROM users ORDER BY id');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function requireUserId(array $users, array $emails, string $role): int
{
    foreach ($emails as $email) {
        foreach ($users as $user) {
            if (strcasecmp((string) $user['email'], $email) === 0) {
                return (int) $user['id'];
            }
        }
    }

    foreach ($users as $user) {
        if (($user['role'] ?? '') === $role) {
            return (int) $user['id'];
        }
    }

    throw new RuntimeException(sprintf('User with role %s or email %s not found. Run the user seeder first.', $role, implode(', ', $emails)));
}

function resetTables(PDO $pdo, array $tables): void
{
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE `{$table}`");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
}

function insertRow(PDO $pdo, string $table, array $data): int
{
    $columns = implode('`, `', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    $stmt = $pdo->prepare("INSERT INTO `{$table}` (`{$columns}`) VALUES ({$placeholders})");
    $stmt->execute(array_values($data));
    return (int) $pdo->lastInsertId();
}

function randomChoice(array $items): mixed
{
    return $items[random_int(0, count($items) - 1)];
}

function randomDateTime(int $daysBackMin, int $daysBackMax, string $timeStart = '08:00:00', string $timeEnd = '17:30:00'): string
{
    if ($daysBackMin > $daysBackMax) {
        [$daysBackMin, $daysBackMax] = [$daysBackMax, $daysBackMin];
    }

    $daysBack = random_int($daysBackMin, $daysBackMax);
    $date = new DateTimeImmutable(sprintf('-%d days', $daysBack));

    $startSeconds = timePartsToSeconds($timeStart);
    $endSeconds = timePartsToSeconds($timeEnd);
    $seconds = random_int($startSeconds, $endSeconds);

    return $date->setTime(0, 0, 0)->modify('+' . $seconds . ' seconds')->format('Y-m-d H:i:s');
}

function timePartsToSeconds(string $time): int
{
    [$hour, $minute, $second] = array_map('intval', explode(':', $time));
    return ($hour * 3600) + ($minute * 60) + $second;
}

function moneyStep(int $min, int $max, int $step = 1000): int
{
    $value = random_int((int) ceil($min / $step), (int) floor($max / $step));
    return $value * $step;
}

function seedGudang(PDO $pdo, int $bosId): array
{
    $rows = [
        ['id_bos' => $bosId, 'nama' => 'Gudang Utama', 'alamat' => 'Jl. Pelabuhan No. 1', 'kota' => 'Jakarta', 'telpon' => '021-5001001', 'is_active' => 1],
        ['id_bos' => $bosId, 'nama' => 'Gudang Timur', 'alamat' => 'Jl. Dermaga No. 18', 'kota' => 'Tangerang', 'telpon' => '021-5001002', 'is_active' => 1],
        ['id_bos' => $bosId, 'nama' => 'Gudang Barat', 'alamat' => 'Jl. Ikan Segar No. 9', 'kota' => 'Bekasi', 'telpon' => '021-5001003', 'is_active' => 1],
    ];

    $ids = [];
    foreach ($rows as $row) {
        $ids[] = insertRow($pdo, 'gudang', $row);
    }

    return $ids;
}

function seedJenisIkan(PDO $pdo): array
{
    $items = [
        ['nama' => 'Ikan Laut Segar', 'deskripsi' => 'Ikan laut hasil tangkapan harian', 'is_active' => 1],
        ['nama' => 'Ikan Darat Segar', 'deskripsi' => 'Ikan budidaya dari tambak lokal', 'is_active' => 1],
        ['nama' => 'Ikan Beku', 'deskripsi' => 'Produk beku suhu rendah', 'is_active' => 1],
        ['nama' => 'Seafood Segar', 'deskripsi' => 'Udang, cumi, dan kerang segar', 'is_active' => 1],
        ['nama' => 'Seafood Olahan', 'deskripsi' => 'Produk olahan siap masak', 'is_active' => 1],
        ['nama' => 'Ikan Premium', 'deskripsi' => 'Kelas premium untuk restoran', 'is_active' => 1],
    ];

    $ids = [];
    foreach ($items as $item) {
        $ids[] = insertRow($pdo, 'jenis_ikan', $item);
    }

    return $ids;
}

function seedSupplier(PDO $pdo, array $gudangIds): array
{
    $names = [
        ['PT Laut Nusantara', 'Budi Santoso', 'Adi', '0812-1000-1001', 'Jakarta'],
        ['CV Samudra Jaya', 'Siti Rahma', 'Rina', '0812-1000-1002', 'Tangerang'],
        ['UD Marina Segar', 'Ahmad Wijaya', 'Dewi', '0812-1000-1003', 'Bekasi'],
        ['PT Bahari Mandiri', 'Tina Lestari', 'Sari', '0812-1000-1004', 'Bogor'],
        ['CV Ikan Prima', 'Hendra Gunawan', 'Dian', '0812-1000-1005', 'Depok'],
        ['UD Seafood Makmur', 'Maya Putri', 'Nia', '0812-1000-1006', 'Jakarta'],
        ['PT Pelabuhan Utama', 'Rudi Hartono', 'Tono', '0812-1000-1007', 'Bekasi'],
        ['CV Segar Bersama', 'Nur Aini', 'Lina', '0812-1000-1008', 'Tangerang'],
    ];

    $banks = ['BCA', 'BRI', 'BNI', 'Mandiri'];
    $ids = [];

    foreach ($names as $index => $supplierData) {
        [$nama, $pemilik, $cp, $telpon, $kota] = $supplierData;
        $ids[] = insertRow($pdo, 'supplier', [
            'id_gudang' => $gudangIds[$index % count($gudangIds)],
            'nama' => $nama,
            'nama_pemilik' => $pemilik,
            'kontak_person' => $cp,
            'telpon' => $telpon,
            'alamat' => 'Jl. ' . $nama . ' No. ' . random_int(1, 30),
            'kota' => $kota,
            'bank_name' => randomChoice($banks),
            'bank_account' => (string) random_int(1000000000, 9999999999),
            'bank_owner' => $nama,
            'is_active' => 1,
        ]);
    }

    return $ids;
}

function seedPembeli(PDO $pdo, array $gudangIds): array
{
    $buyers = [
        ['PT Restoran Bahari', '021-2000-1001', 'bulk', 50000000],
        ['Hotel Laut Biru', '021-2000-1002', 'bulk', 100000000],
        ['Pasar Ikan Segar', '021-2000-1003', 'retail', 10000000],
        ['Toko Seafood Maju', '021-2000-1004', 'retail', 15000000],
        ['Rumah Makan Nelayan', '021-2000-1005', 'bulk', 30000000],
        ['Catering Nusantara', '021-2000-1006', 'bulk', 25000000],
        ['Distributor Ikan Prima', '021-2000-1007', 'reseller', 75000000],
        ['Warung Segar', '021-2000-1008', 'retail', 5000000],
    ];

    $ids = [];
    foreach ($buyers as $index => [$nama, $telpon, $tipe, $limit]) {
        $ids[] = insertRow($pdo, 'pembeli', [
            'id_gudang' => $gudangIds[$index % count($gudangIds)],
            'nama' => $nama,
            'telpon' => $telpon,
            'alamat' => 'Jl. ' . $nama . ' No. ' . random_int(1, 50),
            'kota' => randomChoice(['Jakarta', 'Tangerang', 'Bekasi', 'Depok']),
            'tipe' => $tipe,
            'kredit_limit' => $limit,
            'is_active' => 1,
        ]);
    }

    return $ids;
}

function seedProduk(PDO $pdo, array $gudangIds, array $jenisIkanIds): array
{
    $catalog = [
        ['Ikan Kakap Merah', 'kakap_merah.webp', 55000, 72000, 50],
        ['Ikan Kerapu', 'kerapu.webp', 78000, 98000, 30],
        ['Ikan Tenggiri', 'tenggiri.webp', 61000, 80000, 40],
        ['Ikan Tuna', 'tuna.webp', 68000, 88000, 35],
        ['Ikan Nila', 'nila.webp', 29000, 42000, 120],
        ['Ikan Lele', 'lele.webp', 24000, 35000, 140],
        ['Udang Windu', 'udang_windu.webp', 118000, 152000, 25],
        ['Cumi Segar', 'cumi.webp', 52000, 73000, 45],
        ['Kepiting Bakau', 'kepiting.webp', 145000, 190000, 20],
        ['Kerang Hijau', 'kerang.webp', 22000, 33000, 150],
        ['Ikan Dori', 'dori.webp', 46000, 64000, 60],
        ['Fillet Salmon', 'salmon.webp', 160000, 210000, 18],
    ];

    $ids = [];
    foreach ($catalog as $index => [$nama, $gambar, $hargaBeli, $hargaJual, $stokMinimum]) {
        $ids[] = insertRow($pdo, 'produk', [
            'id_jenis_ikan' => $jenisIkanIds[$index % count($jenisIkanIds)],
            'id_gudang' => $gudangIds[$index % count($gudangIds)],
            'nama' => $nama,
            'deskripsi' => 'Produk dummy acak untuk ' . $nama,
            'gambar' => $gambar,
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaJual,
            'stok_qty' => 0,
            'nilai_stok' => 0,
            'stok_minimum' => $stokMinimum,
            'is_active' => 1,
        ]);
    }

    return $ids;
}

function seedSettings(PDO $pdo, array $gudangIds): void
{
    $settings = [
        ['multi_warehouse_aktif', '1', 'Aktifkan multi gudang'],
        ['stok_minimum_threshold', '50', 'Batas minimum stok'],
        ['susut_alert_threshold', '5', 'Ambang susut'],
        ['komisi_penitipan_tipe', 'potong', 'Metode komisi titipan'],
        ['komisi_penitipan_persen', '5', 'Persentase komisi titipan'],
        ['pajak_default_persen', '0', 'Pajak default'],
        ['jatuh_tempo_default_hari', '30', 'Jatuh tempo default'],
        ['session_timeout_menit', '30', 'Timeout sesi'],
        ['harga_locked_untuk', 'bos', 'Role pengubah harga'],
        ['export_permission', 'bos', 'Role export data'],
    ];

    foreach ($gudangIds as $gudangId) {
        foreach ($settings as [$kunci, $nilai, $deskripsi]) {
            insertRow($pdo, 'settings', [
                'id_gudang' => $gudangId,
                'kunci' => $kunci,
                'nilai' => $nilai,
                'deskripsi' => $deskripsi,
            ]);
        }
    }
}

function seedStokMasukAndTimbangan(PDO $pdo, array $gudangIds, array $produkIds, array $supplierIds, array $userIds): array
{
    $purchaseRows = [];
    $stokRows = [];
    $stockByProduct = [];

    for ($i = 0; $i < 18; $i++) {
        $produkId = $produkIds[array_rand($produkIds)];
        $gudangId = $gudangIds[$i % count($gudangIds)];
        $supplierId = $supplierIds[$i % count($supplierIds)];
        $qty = random_int(20, 250);

        $hargaBeli = match (true) {
            $produkId <= 4 => moneyStep(52000, 90000),
            $produkId <= 8 => moneyStep(22000, 65000),
            default => moneyStep(30000, 180000),
        };

        $status = randomChoice(['confirmed', 'confirmed', 'confirmed', 'pending', 'rejected']);
        $createdBy = randomChoice($userIds);

        $stokId = insertRow($pdo, 'stok_masuk', [
            'id_gudang' => $gudangId,
            'id_produk' => $produkId,
            'id_supplier' => $supplierId,
            'qty' => $qty,
            'harga_beli' => $hargaBeli,
            'status' => $status,
            'catatan' => 'Stok dummy acak ' . strtoupper($status),
            'created_by' => $createdBy,
            'created_at' => randomDateTime(12, 2),
        ]);

        $stokRows[] = $stokId;

        if ($status === 'confirmed') {
            $susutPercent = random_int(0, 3);
            $actualQty = round($qty * (100 - $susutPercent) / 100, 2);

            insertRow($pdo, 'timbangan', [
                'id_stok_masuk' => $stokId,
                'id_produk' => $produkId,
                'qty_teoritis' => $qty,
                'qty_actual' => $actualQty,
                'alasan_susut' => $susutPercent > 0 ? 'Susut normal saat penerimaan' : null,
                'created_by' => randomChoice($userIds),
                'created_at' => randomDateTime(12, 2),
            ]);

            $stockByProduct[$produkId] = ($stockByProduct[$produkId] ?? 0) + $actualQty;
            $purchaseRows[] = [
                'id_gudang' => $gudangId,
                'id_supplier' => $supplierId,
                'nominal' => (int) round($actualQty * $hargaBeli),
                'created_at' => randomDateTime(12, 2),
            ];
        }
    }

    foreach ($stockByProduct as $produkId => $qty) {
        $stmt = $pdo->prepare('SELECT harga_beli FROM produk WHERE id = ?');
        $stmt->execute([$produkId]);
        $harga = (int) ($stmt->fetchColumn() ?: 0);
        $pdo->prepare('UPDATE produk SET stok_qty = ?, nilai_stok = ? WHERE id = ?')->execute([
            $qty,
            (int) round($qty * $harga),
            $produkId,
        ]);
    }

    return [
        'stokRows' => $stokRows,
        'purchaseRows' => $purchaseRows,
    ];
}

function seedHargaHistory(PDO $pdo, array $produkIds, array $userIds): void
{
    foreach ($produkIds as $index => $produkId) {
        $stmt = $pdo->prepare('SELECT harga_beli, harga_jual FROM produk WHERE id = ?');
        $stmt->execute([$produkId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            continue;
        }

        $createdBy = $userIds[$index % count($userIds)];
        $hargaBeli = (int) $row['harga_beli'];
        $hargaJual = (int) $row['harga_jual'];
        $oldBeli = max(1000, (int) round($hargaBeli * random_int(85, 95) / 100));
        $oldJual = max(1000, (int) round($hargaJual * random_int(85, 95) / 100));

        insertRow($pdo, 'harga_history', [
            'id_produk' => $produkId,
            'harga_lama' => $oldBeli,
            'harga_baru' => $hargaBeli,
            'tipe' => 'beli',
            'reason' => 'Penyesuaian harga beli acak',
            'changed_by' => $createdBy,
            'created_at' => randomDateTime(20, 3),
        ]);

        insertRow($pdo, 'harga_history', [
            'id_produk' => $produkId,
            'harga_lama' => $oldJual,
            'harga_baru' => $hargaJual,
            'tipe' => 'jual',
            'reason' => 'Penyesuaian harga jual acak',
            'changed_by' => $createdBy,
            'created_at' => randomDateTime(20, 3),
        ]);
    }
}

function seedNotaAndDetails(PDO $pdo, array $gudangIds, array $produkIds, array $pembeliIds, array $userIds): array
{
    $notaRows = [];
    $piutangRows = [];
    $stockSnapshot = [];

    foreach ($produkIds as $produkId) {
        $stmt = $pdo->prepare('SELECT stok_qty, harga_jual, id_gudang FROM produk WHERE id = ?');
        $stmt->execute([$produkId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stockSnapshot[$produkId] = [
            'qty' => (float) ($row['stok_qty'] ?? 0),
            'harga_jual' => (int) ($row['harga_jual'] ?? 0),
            'id_gudang' => (int) ($row['id_gudang'] ?? 1),
        ];
    }

    for ($i = 0; $i < 12; $i++) {
        $gudangId = $gudangIds[$i % count($gudangIds)];
        $pembeliId = $pembeliIds[$i % count($pembeliIds)];
        $createdBy = randomChoice($userIds);
        $payment = randomChoice(['cash', 'cash', 'hutang']);
        $isDraft = $i === 10;

        $notaId = insertRow($pdo, 'nota', [
            'id_gudang' => $gudangId,
            'id_pembeli' => $pembeliId,
            'no_nota' => sprintf('PS-%s-%04d', date('ym'), $i + 1),
            'tanggal_nota' => date('Y-m-d', strtotime('-' . random_int(1, 12) . ' days')),
            'subtotal' => 0,
            'diskon_nominal' => 0,
            'pajak' => 0,
            'total' => 0,
            'pembayaran' => $payment,
            'status' => $isDraft ? 'draft' : 'final',
            'catatan' => 'Nota dummy acak ' . ($isDraft ? 'draft' : 'final'),
            'created_by' => $createdBy,
            'created_at' => randomDateTime(11, 1),
        ]);

        $itemCount = random_int(2, 4);
        $selectedProducts = array_rand($stockSnapshot, min($itemCount, count($stockSnapshot)));
        if (!is_array($selectedProducts)) {
            $selectedProducts = [$selectedProducts];
        }

        $subtotal = 0;
        foreach ($selectedProducts as $produkId) {
            $available = max(0, (int) floor($stockSnapshot[$produkId]['qty']));
            if ($available <= 0) {
                continue;
            }

            $qty = random_int(1, max(1, min(15, $available)));
            $hargaJual = $stockSnapshot[$produkId]['harga_jual'];
            $lineSubtotal = $qty * $hargaJual;

            insertRow($pdo, 'nota_detail', [
                'id_nota' => $notaId,
                'id_produk' => $produkId,
                'qty' => $qty,
                'harga_jual' => $hargaJual,
                'subtotal' => $lineSubtotal,
            ]);

            $subtotal += $lineSubtotal;
            $stockSnapshot[$produkId]['qty'] -= $qty;
            $pdo->prepare('UPDATE produk SET stok_qty = stok_qty - ?, nilai_stok = GREATEST(nilai_stok - ?, 0) WHERE id = ?')
                ->execute([$qty, (int) round($qty * $hargaJual), $produkId]);
        }

        $diskon = (int) round($subtotal * random_int(0, 8) / 100);
        $total = max(0, $subtotal - $diskon);

        $pdo->prepare('UPDATE nota SET subtotal = ?, diskon_nominal = ?, pajak = 0, total = ? WHERE id = ?')
            ->execute([$subtotal, $diskon, $total, $notaId]);

        $notaRows[] = [
            'id' => $notaId,
            'id_gudang' => $gudangId,
            'id_pembeli' => $pembeliId,
            'total' => $total,
            'pembayaran' => $payment,
            'status' => $isDraft ? 'draft' : 'final',
        ];

        if (!$isDraft && $payment === 'hutang') {
            $piutangRows[] = [
                'id_gudang' => $gudangId,
                'id_pembeli' => $pembeliId,
                'id_nota' => $notaId,
                'nominal' => $total,
                'nominal_bayar' => random_int(0, (int) round($total * 0.6)),
                'jatuh_tempo' => date('Y-m-d', strtotime('+' . random_int(15, 45) . ' days')),
                'created_by' => $createdBy,
            ];
        }
    }

    return [
        'notaRows' => $notaRows,
        'piutangRows' => $piutangRows,
    ];
}

function seedHutangPiutang(PDO $pdo, array $gudangIds, array $supplierIds, array $pembeliIds, array $notaContext, array $userIds, array $purchaseRows): void
{
    foreach ($notaContext['piutangRows'] as $row) {
        $status = $row['nominal_bayar'] <= 0 ? 'open' : ($row['nominal_bayar'] >= $row['nominal'] ? 'lunas' : 'sebagian');
        insertRow($pdo, 'hutang_piutang', [
            'id_gudang' => $row['id_gudang'],
            'jenis' => 'piutang',
            'id_supplier' => null,
            'id_pembeli' => $row['id_pembeli'],
            'id_nota' => $row['id_nota'],
            'no_referensi' => 'NT-' . $row['id_nota'],
            'nominal' => $row['nominal'],
            'nominal_bayar' => $row['nominal_bayar'],
            'jatuh_tempo' => $row['jatuh_tempo'],
            'status' => $status,
            'catatan' => 'Piutang dari nota dummy acak',
            'created_by' => $row['created_by'],
            'created_at' => randomDateTime(10, 1),
        ]);
    }

    foreach ($purchaseRows as $index => $row) {
        $nominal = $row['nominal'];
        $nominalBayar = random_int(0, (int) round($nominal * 0.8));
        $status = $nominalBayar <= 0 ? 'open' : ($nominalBayar >= $nominal ? 'lunas' : 'sebagian');

        insertRow($pdo, 'hutang_piutang', [
            'id_gudang' => $row['id_gudang'],
            'jenis' => 'hutang',
            'id_supplier' => $supplierIds[$index % count($supplierIds)],
            'id_pembeli' => null,
            'id_nota' => null,
            'no_referensi' => 'STK-' . date('ymd') . '-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
            'nominal' => $nominal,
            'nominal_bayar' => $nominalBayar,
            'jatuh_tempo' => date('Y-m-d', strtotime('+' . random_int(10, 35) . ' days')),
            'status' => $status,
            'catatan' => 'Hutang supplier dari stok masuk dummy',
            'created_by' => randomChoice($userIds),
            'created_at' => $row['created_at'],
        ]);
    }
}

function seedHutangPiutangHistory(PDO $pdo, array $userIds): void
{
    $records = $pdo->query('SELECT id, nominal, nominal_bayar FROM hutang_piutang ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

    foreach ($records as $record) {
        $remaining = (int) $record['nominal'] - (int) $record['nominal_bayar'];
        if ($remaining <= 0) {
            continue;
        }

        $payment = random_int((int) max(1, round($remaining * 0.25)), $remaining);
        insertRow($pdo, 'hutang_piutang_history', [
            'id_hutang_piutang' => (int) $record['id'],
            'nominal_bayar' => $payment,
            'metode_bayar' => randomChoice(['cash', 'transfer', 'bank transfer']),
            'keterangan' => 'Pembayaran dummy acak',
            'created_by' => randomChoice($userIds),
            'created_at' => randomDateTime(8, 1),
        ]);
    }
}

function seedTitipanAndSales(PDO $pdo, array $gudangIds, array $supplierIds, array $pembeliIds, array $produkIds, array $userIds): void
{
    $titipanIds = [];
    for ($i = 0; $i < 5; $i++) {
        $gudangId = $gudangIds[$i % count($gudangIds)];
        $supplierId = $supplierIds[$i % count($supplierIds)];
        $qtyTotal = random_int(30, 120);
        $qtyDijual = random_int(0, $qtyTotal);
        $qtyTersisa = $qtyTotal - $qtyDijual;
        $nominalTotal = $qtyTotal * random_int(30000, 120000);
        $nominalTerjual = $qtyDijual * random_int(30000, 120000);

        $titipanIds[] = insertRow($pdo, 'titipan', [
            'id_gudang' => $gudangId,
            'id_pengirim' => $supplierId,
            'no_titipan' => sprintf('TP-%s-%04d', date('ym'), $i + 1),
            'tanggal_masuk' => date('Y-m-d', strtotime('-' . random_int(1, 10) . ' days')),
            'qty_total' => $qtyTotal,
            'qty_dijual' => $qtyDijual,
            'qty_tersisa' => $qtyTersisa,
            'nominal_total' => $nominalTotal,
            'nominal_terjual' => $nominalTerjual,
            'komisi_persen' => random_int(3, 8),
            'komisi_tipe' => randomChoice(['potong', 'bayar_terpisah']),
            'status' => $qtyTersisa === 0 ? 'selesai' : ($qtyDijual > 0 ? 'dijual_sebagian' : 'masuk'),
            'catatan' => 'Titipan dummy acak',
            'created_by' => randomChoice($userIds),
            'created_at' => randomDateTime(10, 1),
        ]);
    }

    foreach ($titipanIds as $index => $titipanId) {
        $qty = random_int(5, 25);
        $hargaJual = random_int(35000, 140000);
        $nominal = $qty * $hargaJual;
        $komisi = (int) round($nominal * random_int(3, 8) / 100);

        insertRow($pdo, 'titipan_penjualan', [
            'id_titipan' => $titipanId,
            'id_penjual' => null,
            'id_pembeli' => $pembeliIds[$index % count($pembeliIds)],
            'qty' => $qty,
            'harga_jual' => $hargaJual,
            'nominal' => $nominal,
            'komisi_nominal' => $komisi,
            'tanggal_jual' => date('Y-m-d', strtotime('-' . random_int(1, 9) . ' days')),
            'status' => randomChoice(['pending', 'terjual']),
            'created_by' => randomChoice($userIds),
            'created_at' => randomDateTime(9, 1),
        ]);
    }
}

function seedRetur(PDO $pdo, array $gudangIds, array $produkIds, array $supplierIds, array $pembeliIds, array $notaRows, array $userIds): void
{
    foreach (range(1, 6) as $index) {
        $isStock = $index <= 3;
        $gudangId = $gudangIds[$index % count($gudangIds)];
        $produkId = $produkIds[$index % count($produkIds)];
        $supplierId = $isStock ? $supplierIds[$index % count($supplierIds)] : null;
        $pembeliId = $isStock ? null : $pembeliIds[$index % count($pembeliIds)];
        $notaId = $isStock ? null : $notaRows[$index % count($notaRows)]['id'];
        $qty = $isStock ? random_int(1, 8) : null;
        $nominal = $isStock ? (int) round(($qty ?? 0) * random_int(35000, 100000)) : random_int(150000, 1500000);

        insertRow($pdo, 'retur', [
            'id_gudang' => $gudangId,
            'id_produk' => $produkId,
            'id_supplier' => $supplierId,
            'id_pembeli' => $pembeliId,
            'id_nota' => $notaId,
            'tipe' => $isStock ? 'stok' : 'piutang',
            'qty' => $qty,
            'nominal' => $nominal,
            'alasan' => $isStock ? 'Barang rusak saat penerimaan' : 'Keluhan kualitas barang dari pembeli',
            'foto_bukti' => null,
            'status' => randomChoice(['pending', 'approved', 'rejected', 'posted']),
            'catatan' => 'Retur dummy acak',
            'created_by' => randomChoice($userIds),
            'approved_by' => randomChoice($userIds),
            'approved_at' => randomDateTime(6, 1),
            'created_at' => randomDateTime(6, 1),
            'updated_at' => randomDateTime(6, 1),
        ]);
    }
}

function seedBiayaOperasional(PDO $pdo, array $gudangIds, array $userIds): void
{
    $categories = ['transport', 'gaji', 'listrik', 'maintenance', 'peralatan', 'komunikasi', 'air', 'lainnya'];
    foreach (range(1, 10) as $index) {
        insertRow($pdo, 'biaya_operasional', [
            'id_gudang' => $gudangIds[$index % count($gudangIds)],
            'kategori' => $categories[array_rand($categories)],
            'deskripsi' => 'Biaya operasional dummy #' . $index,
            'nominal' => random_int(100000, 8500000),
            'tanggal' => date('Y-m-d', strtotime('-' . random_int(1, 14) . ' days')),
            'created_by' => randomChoice($userIds),
            'created_at' => randomDateTime(14, 1),
        ]);
    }
}

function seedNotifikasi(PDO $pdo, array $users, array $notaRows, array $stokRows): void
{
    $userIds = array_map(static fn(array $user): int => (int) $user['id'], $users);
    $messages = [
        ['stok_minimum', 'Stok minimum tercapai', 'Periksa stok produk yang mendekati batas minimum'],
        ['penjualan', 'Nota penjualan baru', 'Ada nota baru yang berhasil difinalkan'],
        ['hutang', 'Hutang belum lunas', 'Ada pembayaran yang masih tersisa'],
        ['retur', 'Retur menunggu review', 'Ada retur yang masih pending'],
        ['titipan', 'Titipan baru masuk', 'Ada titipan yang perlu dipantau'],
        ['operasional', 'Biaya operasional dicatat', 'Biaya operasional baru telah disimpan'],
    ];

    for ($i = 0; $i < 12; $i++) {
        [$tipe, $judul, $pesan] = $messages[$i % count($messages)];
        insertRow($pdo, 'notifikasi', [
            'id_user' => $userIds[$i % count($userIds)],
            'tipe' => $tipe,
            'judul' => $judul,
            'pesan' => $pesan . ' #' . ($i + 1),
            'reference_id' => $i % 2 === 0 ? $notaRows[$i % count($notaRows)]['id'] : $stokRows[$i % count($stokRows)],
            'reference_tipe' => $i % 2 === 0 ? 'nota' : 'stok_masuk',
            'is_read' => random_int(0, 1),
            'read_at' => random_int(0, 1) ? randomDateTime(5, 1) : null,
            'created_at' => randomDateTime(5, 1),
        ]);
    }
}

function seedStokOpname(PDO $pdo, array $gudangIds, array $userIds): void
{
    foreach (range(1, 4) as $index) {
        insertRow($pdo, 'stok_opname', [
            'id_gudang' => $gudangIds[$index % count($gudangIds)],
            'tanggal_opname' => date('Y-m-d', strtotime('-' . random_int(1, 14) . ' days')),
            'status' => randomChoice(['draft', 'final']),
            'created_by' => randomChoice($userIds),
            'created_at' => randomDateTime(14, 1),
        ]);
    }
}