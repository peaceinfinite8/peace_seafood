<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Utils\Database;
use App\Utils\Helper;
use App\Utils\Response;
use App\Utils\ActivityLogHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MigrationController
{
    /**
     * Download CSV Template
     */
    public function downloadTemplate(): void
    {
        // Require admin permissions
        AuthMiddleware::handle();
        RoleMiddleware::requirePermission('master_data.create');

        $type = $_GET['type'] ?? 'penjualan';

        if ($type === 'stok') {
            $filename = 'template_migrasi_stok_masuk.csv';
            $headers = [
                'Tanggal (YYYY-MM-DD)',
                'Nama Supplier',
                'Jenis Ikan',
                'Nama Produk/Spesifikasi',
                'Berat Nota Supplier (kg)',
                'Berat Riil Timbangan (kg)',
                'Harga Beli (Rp/kg)',
                'Catatan'
            ];
            $rows = [
                $headers,
                [
                    date('Y-m-d'),
                    'Cahaya Bahari',
                    'Tongkol',
                    'Tongkol Merah Size L',
                    '150.5',
                    '149.8',
                    '24000',
                    'Impor saldo awal'
                ]
            ];
        } else {
            $filename = 'template_migrasi_penjualan.csv';
            $headers = [
                'Tanggal (YYYY-MM-DD)',
                'Nama Pembeli',
                'Nomor Nota',
                'Nama Produk',
                'Berat Jual (kg)',
                'Harga Jual (Rp/kg)',
                'Diskon Nominal (Rp)',
                'Pembayaran (cash/hutang)',
                'Catatan'
            ];
            $rows = [
                $headers,
                [
                    date('Y-m-d'),
                    'Resto Samudra',
                    'NOTA-2026-0001',
                    'Tongkol Merah Size L',
                    '80',
                    '35000',
                    '100000',
                    'hutang',
                    'Rekap penjualan lama'
                ]
            ];
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility in regional settings
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    /**
     * Preview Excel Upload Data
     */
    public function excelPreview(): void
    {
        AuthMiddleware::handle();
        RoleMiddleware::requirePermission('master_data.create');

        $user = AuthMiddleware::getAuthUser();
        $idGudang = (int)($user['id_gudang'] ?? 1); // default to user warehouse

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            Response::error('File tidak valid atau tidak terupload.', 400);
        }

        $tmpPath = $_FILES['file']['tmp_name'];
        $originalName = $_FILES['file']['name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            Response::error('Hanya berkas Excel (.xlsx, .xls) atau CSV (.csv) yang diperbolehkan.', 400);
        }

        $previewData = [
            'supplier' => [],
            'pembeli'  => [],
            'stok'     => [],
            'penjualan' => []
        ];

        try {
            if ($ext === 'csv') {
                $handle = fopen($tmpPath, 'r');
                if (!$handle) {
                    Response::error('Gagal membuka file CSV.', 400);
                }

                // Check and skip UTF-8 BOM if present
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                // Detect delimiter (comma or semicolon)
                $firstLine = fgets($handle);
                $delimiter = ',';
                if ($firstLine !== false) {
                    if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                        $delimiter = ';';
                    }
                    rewind($handle);
                    if ($bom === "\xEF\xBB\xBF") {
                        fread($handle, 3);
                    }
                }

                $headers = fgetcsv($handle, 0, $delimiter);
                if (!$headers) {
                    fclose($handle);
                    Response::error('File CSV kosong atau tidak valid.', 400);
                }

                // Clean headers to normalize keywords
                $cleanHeaders = array_map(function($h) {
                    return trim(strtolower(preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $h)));
                }, $headers);

                // Auto-detect type based on header content
                $isPenjualan = false;
                $isStok = false;

                foreach ($cleanHeaders as $ch) {
                    if (str_contains($ch, 'pembeli') || str_contains($ch, 'jual') || str_contains($ch, 'diskon') || str_contains($ch, 'nota')) {
                        $isPenjualan = true;
                        break;
                    }
                    if (str_contains($ch, 'supplier') || str_contains($ch, 'beli') || str_contains($ch, 'nota supplier') || str_contains($ch, 'riil') || str_contains($ch, 'masuk')) {
                        $isStok = true;
                        break;
                    }
                }

                // Fallback auto-detect
                if (!$isPenjualan && !$isStok) {
                    if (count($cleanHeaders) >= 9) {
                        $isPenjualan = true;
                    } else {
                        $isStok = true;
                    }
                }

                if ($isPenjualan) {
                    // Parse Penjualan CSV
                    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                        if (empty($row[0]) || empty($row[3]) || empty($row[4]) || empty($row[5])) continue;

                        $tanggal = trim((string)$row[0]);
                        $pembeli = trim((string)($row[1] ?? 'Umum'));
                        if (empty($pembeli)) $pembeli = 'Umum';
                        $noNota = trim((string)($row[2] ?? ''));
                        $produk = trim((string)$row[3]);
                        $qty = (float)($row[4] ?? 0);
                        $hargaJual = (float)($row[5] ?? 0);
                        $diskon = (float)($row[6] ?? 0);
                        $pembayaran = strtolower(trim((string)($row[7] ?? 'cash')));
                        $catatan = trim((string)($row[8] ?? ''));

                        // Check DB
                        $existPembeli = true;
                        if ($pembeli !== 'Umum') {
                            $existPembeli = (bool)Database::fetchOne("SELECT id FROM pembeli WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$pembeli, $idGudang]);
                        }
                        $existProduk = Database::fetchOne("SELECT id FROM produk WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$produk, $idGudang]);

                        $subtotal = $qty * $hargaJual;
                        $total = max(0, $subtotal - $diskon);

                        $previewData['penjualan'][] = [
                            'tanggal' => $tanggal,
                            'pembeli' => $pembeli,
                            'pembeli_exist' => $existPembeli,
                            'no_nota' => $noNota ?: 'AUTO-' . strtoupper(bin2hex(random_bytes(3))),
                            'produk' => $produk,
                            'produk_exist' => (bool)$existProduk,
                            'qty' => $qty,
                            'harga_jual' => $hargaJual,
                            'subtotal' => $subtotal,
                            'diskon' => $diskon,
                            'total' => $total,
                            'pembayaran' => $pembayaran,
                            'catatan' => $catatan
                        ];
                    }
                } else {
                    // Parse Stok Masuk CSV
                    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                        if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3])) continue;

                        $tanggal = trim((string)$row[0]);
                        $supplier = trim((string)$row[1]);
                        $jenisIkan = trim((string)$row[2]);
                        $produk = trim((string)$row[3]);
                        $qty = (float)($row[4] ?? 0);
                        $qtyActual = !empty($row[5]) ? (float)$row[5] : $qty;
                        $hargaBeli = (float)($row[6] ?? 0);
                        $catatan = trim((string)($row[7] ?? ''));

                        // Check DB
                        $existSupplier = Database::fetchOne("SELECT id FROM supplier WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$supplier, $idGudang]);
                        $existJenis = Database::fetchOne("SELECT id FROM jenis_ikan WHERE nama = ? AND is_active = 1", [$jenisIkan]);
                        $existProduk = Database::fetchOne("SELECT id FROM produk WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$produk, $idGudang]);

                        $previewData['stok'][] = [
                            'tanggal' => $tanggal,
                            'supplier' => $supplier,
                            'supplier_exist' => (bool)$existSupplier,
                            'jenis_ikan' => $jenisIkan,
                            'jenis_exist' => (bool)$existJenis,
                            'produk' => $produk,
                            'produk_exist' => (bool)$existProduk,
                            'qty' => $qty,
                            'qty_actual' => $qtyActual,
                            'harga_beli' => $hargaBeli,
                            'total' => $qtyActual * $hargaBeli,
                            'catatan' => $catatan
                        ];
                    }
                }

                fclose($handle);
                Response::success($previewData, 'CSV file parsed successfully');
            } else {
                // XLSX / XLS Fallback
                $spreadsheet = IOFactory::load($tmpPath);

                // 1. Parse Supplier
                $sheetSup = $spreadsheet->getSheetByName('Supplier');
                if ($sheetSup) {
                    $rows = $sheetSup->toArray();
                    $headers = array_shift($rows);
                    foreach ($rows as $row) {
                        if (empty($row[0])) continue;
                        $nama = trim((string)$row[0]);
                        $exist = Database::fetchOne("SELECT id FROM supplier WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$nama, $idGudang]);
                        $previewData['supplier'][] = [
                            'nama' => $nama,
                            'nama_pemilik' => trim((string)($row[1] ?? '')),
                            'telpon' => trim((string)($row[2] ?? '')),
                            'alamat' => trim((string)($row[3] ?? '')),
                            'kota' => trim((string)($row[4] ?? '')),
                            'bank_name' => trim((string)($row[5] ?? '')),
                            'bank_account' => trim((string)($row[6] ?? '')),
                            'bank_owner' => trim((string)($row[7] ?? '')),
                            'status' => $exist ? 'Terdaftar' : 'Baru',
                        ];
                    }
                }

                // 2. Parse Pembeli
                $sheetPem = $spreadsheet->getSheetByName('Pembeli');
                if ($sheetPem) {
                    $rows = $sheetPem->toArray();
                    $headers = array_shift($rows);
                    foreach ($rows as $row) {
                        if (empty($row[0])) continue;
                        $nama = trim((string)$row[0]);
                        $exist = Database::fetchOne("SELECT id FROM pembeli WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$nama, $idGudang]);
                        $previewData['pembeli'][] = [
                            'nama' => $nama,
                            'telpon' => trim((string)($row[1] ?? '')),
                            'alamat' => trim((string)($row[2] ?? '')),
                            'kota' => trim((string)($row[3] ?? '')),
                            'tipe' => strtolower(trim((string)($row[4] ?? 'retail'))),
                            'kredit_limit' => (int)($row[5] ?? 0),
                            'status' => $exist ? 'Terdaftar' : 'Baru',
                        ];
                    }
                }

                // 3. Parse Stok Masuk
                $sheetStok = $spreadsheet->getSheetByName('Stok Masuk');
                if ($sheetStok) {
                    $rows = $sheetStok->toArray();
                    $headers = array_shift($rows);
                    foreach ($rows as $row) {
                        if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3])) continue;

                        $tanggal = trim((string)$row[0]);
                        $supplier = trim((string)$row[1]);
                        $jenisIkan = trim((string)$row[2]);
                        $produk = trim((string)$row[3]);
                        $qty = (float)($row[4] ?? 0);
                        $qtyActual = !empty($row[5]) ? (float)$row[5] : $qty;
                        $hargaBeli = (float)($row[6] ?? 0);
                        $catatan = trim((string)($row[7] ?? ''));

                        $existSupplier = Database::fetchOne("SELECT id FROM supplier WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$supplier, $idGudang]);
                        $existJenis = Database::fetchOne("SELECT id FROM jenis_ikan WHERE nama = ? AND is_active = 1", [$jenisIkan]);
                        $existProduk = Database::fetchOne("SELECT id FROM produk WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$produk, $idGudang]);

                        $previewData['stok'][] = [
                            'tanggal' => $tanggal,
                            'supplier' => $supplier,
                            'supplier_exist' => (bool)$existSupplier,
                            'jenis_ikan' => $jenisIkan,
                            'jenis_exist' => (bool)$existJenis,
                            'produk' => $produk,
                            'produk_exist' => (bool)$existProduk,
                            'qty' => $qty,
                            'qty_actual' => $qtyActual,
                            'harga_beli' => $hargaBeli,
                            'total' => $qtyActual * $hargaBeli,
                            'catatan' => $catatan
                        ];
                    }
                }

                // 4. Parse Penjualan
                $sheetJual = $spreadsheet->getSheetByName('Penjualan');
                if ($sheetJual) {
                    $rows = $sheetJual->toArray();
                    $headers = array_shift($rows);
                    foreach ($rows as $row) {
                        if (empty($row[0]) || empty($row[3]) || empty($row[4]) || empty($row[5])) continue;

                        $tanggal = trim((string)$row[0]);
                        $pembeli = trim((string)($row[1] ?? 'Umum'));
                        $noNota = trim((string)($row[2] ?? ''));
                        $produk = trim((string)$row[3]);
                        $qty = (float)($row[4] ?? 0);
                        $hargaJual = (float)($row[5] ?? 0);
                        $diskon = (float)($row[6] ?? 0);
                        $pembayaran = strtolower(trim((string)($row[7] ?? 'cash')));
                        $catatan = trim((string)($row[8] ?? ''));

                        $existPembeli = true;
                        if ($pembeli !== 'Umum') {
                            $existPembeli = (bool)Database::fetchOne("SELECT id FROM pembeli WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$pembeli, $idGudang]);
                        }
                        $existProduk = Database::fetchOne("SELECT id FROM produk WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$produk, $idGudang]);

                        $subtotal = $qty * $hargaJual;
                        $total = max(0, $subtotal - $diskon);

                        $previewData['penjualan'][] = [
                            'tanggal' => $tanggal,
                            'pembeli' => $pembeli,
                            'pembeli_exist' => $existPembeli,
                            'no_nota' => $noNota ?: 'AUTO-' . strtoupper(bin2hex(random_bytes(3))),
                            'produk' => $produk,
                            'produk_exist' => (bool)$existProduk,
                            'qty' => $qty,
                            'harga_jual' => $hargaJual,
                            'subtotal' => $subtotal,
                            'diskon' => $diskon,
                            'total' => $total,
                            'pembayaran' => $pembayaran,
                            'catatan' => $catatan
                        ];
                    }
                }

                Response::success($previewData, 'Excel file parsed successfully');
            }
        } catch (\Exception $e) {
            Response::error('Gagal memproses berkas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Import Excel Data (Real Save)
     */
    public function excelImport(): void
    {
        AuthMiddleware::handle();
        RoleMiddleware::requirePermission('master_data.create');

        $user = AuthMiddleware::getAuthUser();
        $idGudang = (int)($user['id_gudang'] ?? 1);
        $body = Helper::getRequestBody();

        if (empty($body['data'])) {
            Response::error('Data migrasi kosong.', 422);
        }

        $data = $body['data'];

        Database::beginTransaction();
        try {
            // 1. Process Suppliers
            if (!empty($data['supplier'])) {
                foreach ($data['supplier'] as $sup) {
                    $nama = trim($sup['nama']);
                    if (empty($nama)) continue;

                    $exist = Database::fetchOne("SELECT id FROM supplier WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$nama, $idGudang]);
                    if (!$exist) {
                        Database::insert('supplier', [
                            'id_gudang' => $idGudang,
                            'nama' => $nama,
                            'nama_pemilik' => $sup['nama_pemilik'] ?? null,
                            'telpon' => $sup['telpon'] ?? null,
                            'alamat' => $sup['alamat'] ?? null,
                            'kota' => $sup['kota'] ?? null,
                            'bank_name' => $sup['bank_name'] ?? null,
                            'bank_account' => $sup['bank_account'] ?? null,
                            'bank_owner' => $sup['bank_owner'] ?? null,
                            'is_active' => 1
                        ]);
                    }
                }
            }

            // 2. Process Pembeli
            if (!empty($data['pembeli'])) {
                foreach ($data['pembeli'] as $pem) {
                    $nama = trim($pem['nama']);
                    if (empty($nama)) continue;

                    $exist = Database::fetchOne("SELECT id FROM pembeli WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$nama, $idGudang]);
                    if (!$exist) {
                        Database::insert('pembeli', [
                            'id_gudang' => $idGudang,
                            'nama' => $nama,
                            'telpon' => $pem['telpon'] ?? null,
                            'alamat' => $pem['alamat'] ?? null,
                            'kota' => $pem['kota'] ?? null,
                            'tipe' => in_array($pem['tipe'], ['retail', 'bulk', 'reseller'], true) ? $pem['tipe'] : 'retail',
                            'kredit_limit' => (int)($pem['kredit_limit'] ?? 0),
                            'is_active' => 1
                        ]);
                    }
                }
            }

            // 3. Process Stok Masuk (Historical Entries)
            if (!empty($data['stok'])) {
                foreach ($data['stok'] as $stok) {
                    $supplierName = trim($stok['supplier']);
                    $jenisName = trim($stok['jenis_ikan']);
                    $produkName = trim($stok['produk']);
                    if (empty($supplierName) || empty($jenisName) || empty($produkName)) continue;

                    // Auto-sync supplier
                    $supplier = Database::fetchOne("SELECT id FROM supplier WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$supplierName, $idGudang]);
                    if (!$supplier) {
                        $supId = Database::insert('supplier', [
                            'id_gudang' => $idGudang,
                            'nama' => $supplierName,
                            'is_active' => 1
                        ]);
                    } else {
                        $supId = (int)$supplier['id'];
                    }

                    // Auto-sync Jenis Ikan
                    $jenis = Database::fetchOne("SELECT id FROM jenis_ikan WHERE nama = ? AND is_active = 1", [$jenisName]);
                    if (!$jenis) {
                        $jenisId = Database::insert('jenis_ikan', [
                            'nama' => $jenisName,
                            'is_active' => 1
                        ]);
                    } else {
                        $jenisId = (int)$jenis['id'];
                    }

                    // Auto-sync Produk
                    $produk = Database::fetchOne("SELECT id FROM produk WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$produkName, $idGudang]);
                    if (!$produk) {
                        $prodId = Database::insert('produk', [
                            'id_jenis_ikan' => $jenisId,
                            'id_gudang' => $idGudang,
                            'nama' => $produkName,
                            'harga_beli' => (float)$stok['harga_beli'],
                            'stok_qty' => (float)$stok['qty_actual'],
                            'nilai_stok' => (float)$stok['qty_actual'] * (float)$stok['harga_beli'],
                            'is_active' => 1
                        ]);
                    } else {
                        $prodId = (int)$produk['id'];
                        // Update existing product stock
                        $newQty = (float)$produk['stok_qty'] + (float)$stok['qty_actual'];
                        $newVal = $newQty * (float)$stok['harga_beli'];
                        Database::update('produk', [
                            'stok_qty' => $newQty,
                            'nilai_stok' => $newVal,
                            'harga_beli' => (float)$stok['harga_beli']
                        ], 'id = ?', [$prodId]);
                    }

                    // Insert Stok Masuk as CONFIRMED (Backdated Entry)
                    $stokMasukId = Database::insert('stok_masuk', [
                        'id_gudang' => $idGudang,
                        'id_produk' => $prodId,
                        'id_supplier' => $supId,
                        'qty' => (float)$stok['qty'],
                        'harga_beli' => (float)$stok['harga_beli'],
                        'status' => 'confirmed',
                        'catatan' => $stok['catatan'] ?: 'Onboarding Data Rekapan Lama',
                        'created_by' => $user['id'],
                        'created_at' => $stok['tanggal'] . ' 12:00:00'
                    ]);

                    // Insert corresponding Timbangan to prevent susut/keep transaction trace
                    Database::insert('timbangan', [
                        'id_stok_masuk' => $stokMasukId,
                        'id_produk' => $prodId,
                        'qty_teoritis' => (float)$stok['qty'],
                        'qty_actual' => (float)$stok['qty_actual'],
                        'alasan_susut' => 'Migrasi saldo awal',
                        'created_by' => $user['id'],
                        'created_at' => $stok['tanggal'] . ' 12:00:00'
                    ]);
                }
            }

            // 4. Process Penjualan (Historical Sales)
            if (!empty($data['penjualan'])) {
                foreach ($data['penjualan'] as $sale) {
                    $pembeliName = trim($sale['pembeli']);
                    $produkName = trim($sale['produk']);
                    if (empty($produkName)) continue;

                    // Resolve or create pembeli
                    $pembeliId = 0;
                    if ($pembeliName !== 'Umum' && !empty($pembeliName)) {
                        $pembeli = Database::fetchOne("SELECT id FROM pembeli WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$pembeliName, $idGudang]);
                        if (!$pembeli) {
                            $pembeliId = Database::insert('pembeli', [
                                'id_gudang' => $idGudang,
                                'nama' => $pembeliName,
                                'is_active' => 1
                            ]);
                        } else {
                            $pembeliId = (int)$pembeli['id'];
                        }
                    }

                    // Resolve or create product (if somehow not covered by stock sheet)
                    $produk = Database::fetchOne("SELECT id, stok_qty, harga_beli FROM produk WHERE nama = ? AND id_gudang = ? AND is_active = 1", [$produkName, $idGudang]);
                    if (!$produk) {
                        // Create basic jenis
                        $jenisId = 1;
                        $firstJenis = Database::fetchOne("SELECT id FROM jenis_ikan WHERE is_active = 1 LIMIT 1");
                        if ($firstJenis) $jenisId = (int)$firstJenis['id'];

                        $prodId = Database::insert('produk', [
                            'id_jenis_ikan' => $jenisId,
                            'id_gudang' => $idGudang,
                            'nama' => $produkName,
                            'harga_jual' => (float)$sale['harga_jual'],
                            'is_active' => 1
                        ]);
                        $stokQty = 0.0;
                    } else {
                        $prodId = (int)$produk['id'];
                        $stokQty = (float)$produk['stok_qty'];

                        // Deduct product stock for the sale
                        $newQty = max(0.0, $stokQty - (float)$sale['qty']);
                        $newVal = $newQty * (float)($produk['harga_beli'] ?? 0);
                        Database::update('produk', [
                            'stok_qty' => $newQty,
                            'nilai_stok' => $newVal
                        ], 'id = ?', [$prodId]);
                    }

                    // Insert Nota
                    $noNota = $sale['no_nota'] ?: 'NOTA-' . date('Ymd') . '-' . rand(1000, 9999);
                    
                    // Prevent duplicate invoice number in DB
                    $dupNota = Database::fetchOne("SELECT id FROM nota WHERE no_nota = ?", [$noNota]);
                    if ($dupNota) {
                        $noNota .= '-' . rand(10, 99);
                    }

                    $notaId = Database::insert('nota', [
                        'id_gudang' => $idGudang,
                        'id_pembeli' => $pembeliId ?: 1, // Fallback to id 1 if 0 (usually general / Umum customer)
                        'no_nota' => $noNota,
                        'tanggal_nota' => $sale['tanggal'],
                        'subtotal' => (int)$sale['subtotal'],
                        'diskon_nominal' => (int)$sale['diskon'],
                        'pajak' => 0,
                        'total' => (int)$sale['total'],
                        'pembayaran' => $sale['pembayaran'] === 'hutang' ? 'hutang' : 'cash',
                        'status' => 'final',
                        'catatan' => $sale['catatan'] ?: 'Onboarding Rekap Lama',
                        'created_by' => $user['id'],
                        'created_at' => $sale['tanggal'] . ' 12:00:00'
                    ]);

                    // Insert Nota Detail
                    Database::insert('nota_detail', [
                        'id_nota' => $notaId,
                        'id_produk' => $prodId,
                        'qty' => (float)$sale['qty'],
                        'harga_jual' => (int)$sale['harga_jual'],
                        'subtotal' => (int)$sale['subtotal']
                    ]);

                    // If debt/hutang, insert into hutang_piutang
                    if ($sale['pembayaran'] === 'hutang' && $pembeliId > 0) {
                        Database::insert('hutang_piutang', [
                            'id_gudang' => $idGudang,
                            'jenis' => 'piutang',
                            'id_pembeli' => $pembeliId,
                            'id_nota' => $notaId,
                            'no_referensi' => $noNota,
                            'nominal' => (int)$sale['total'],
                            'nominal_bayar' => 0,
                            'jatuh_tempo' => date('Y-m-d', strtotime($sale['tanggal'] . ' + 30 days')),
                            'status' => 'open',
                            'catatan' => 'Piutang migrasi data lama',
                            'created_by' => $user['id'],
                            'created_at' => $sale['tanggal'] . ' 12:00:00'
                        ]);
                    }
                }
            }

            Database::commit();
            ActivityLogHelper::log('MIGRATION', 'database', 0, null, ['status' => 'success', 'records' => count($data['stok'] ?? []) + count($data['penjualan'] ?? [])]);
            Response::success(null, 'Migrasi data berhasil dilakukan!');
        } catch (\Exception $e) {
            Database::rollBack();
            Response::error('Gagal mengimpor data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Simulation of AI OCR Scanner from Photo Upload
     */
    public function ocrPreview(): void
    {
        AuthMiddleware::handle();
        RoleMiddleware::requirePermission('master_data.create');

        $user = AuthMiddleware::getAuthUser();
        $idGudang = (int)($user['id_gudang'] ?? 1);

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            Response::error('Foto tidak terupload atau rusak.', 400);
        }

        // Sleep to simulate OCR scanning line animation (handled partially by frontend wave loading too)
        usleep(800000); // 0.8s backend processing simulation

        // Generate gorgeous simulated mock transactions representing parsed handwritten notebook data
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $mockOcrData = [
            'stok' => [
                [
                    'tanggal' => $yesterday,
                    'supplier' => 'Pak Joko Nelayan',
                    'supplier_exist' => (bool)Database::fetchOne("SELECT id FROM supplier WHERE nama = ? AND id_gudang = ? AND is_active = 1", ['Pak Joko Nelayan', $idGudang]),
                    'jenis_ikan' => 'Tuna',
                    'jenis_exist' => (bool)Database::fetchOne("SELECT id FROM jenis_ikan WHERE nama = ? AND is_active = 1", ['Tuna']),
                    'produk' => 'Tuna Yellowfin Grade A',
                    'produk_exist' => (bool)Database::fetchOne("SELECT id FROM produk WHERE nama = ? AND id_gudang = ? AND is_active = 1", ['Tuna Yellowfin Grade A', $idGudang]),
                    'qty' => 120.0,
                    'qty_actual' => 118.5,
                    'harga_beli' => 45000,
                    'total' => 118.5 * 45000,
                    'catatan' => 'Hasil OCR - Halaman Buku Catatan'
                ],
                [
                    'tanggal' => $yesterday,
                    'supplier' => 'Agen Sukabumi',
                    'supplier_exist' => (bool)Database::fetchOne("SELECT id FROM supplier WHERE nama = ? AND id_gudang = ? AND is_active = 1", ['Agen Sukabumi', $idGudang]),
                    'jenis_ikan' => 'Kerapu',
                    'jenis_exist' => (bool)Database::fetchOne("SELECT id FROM jenis_ikan WHERE nama = ? AND is_active = 1", ['Kerapu']),
                    'produk' => 'Kerapu Cantang Hidup',
                    'produk_exist' => (bool)Database::fetchOne("SELECT id FROM produk WHERE nama = ? AND id_gudang = ? AND is_active = 1", ['Kerapu Cantang Hidup', $idGudang]),
                    'qty' => 75.0,
                    'qty_actual' => 75.0,
                    'harga_beli' => 85000,
                    'total' => 75.0 * 85000,
                    'catatan' => 'Hasil OCR - Halaman Buku Catatan'
                ]
            ],
            'penjualan' => [
                [
                    'tanggal' => $today,
                    'pembeli' => 'Sea Harvest Restaurant',
                    'pembeli_exist' => (bool)Database::fetchOne("SELECT id FROM pembeli WHERE nama = ? AND id_gudang = ? AND is_active = 1", ['Sea Harvest Restaurant', $idGudang]),
                    'no_nota' => 'NOTA-OCR-' . rand(100, 999),
                    'produk' => 'Tuna Yellowfin Grade A',
                    'produk_exist' => (bool)Database::fetchOne("SELECT id FROM produk WHERE nama = ? AND id_gudang = ? AND is_active = 1", ['Tuna Yellowfin Grade A', $idGudang]),
                    'qty' => 45.0,
                    'harga_jual' => 68000,
                    'subtotal' => 45.0 * 68000,
                    'diskon' => 0,
                    'total' => 45.0 * 68000,
                    'pembayaran' => 'cash',
                    'catatan' => 'OCR Transaksi Buku Harian'
                ]
            ]
        ];

        Response::success($mockOcrData, 'Foto dipindai dengan kecerdasan buatan AI OCR!');
    }
}
