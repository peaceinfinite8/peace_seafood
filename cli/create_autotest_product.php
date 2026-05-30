<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Utils\Database;
use App\Models\JenisIkan;
use App\Models\Produk;

try {
    $g = Database::fetchOne("SELECT id FROM gudang WHERE is_active = 1 LIMIT 1");
    if (!$g) {
        echo json_encode(['error' => 'No active gudang found. Create a gudang first.']) . PHP_EOL;
        exit(1);
    }
    $gudangId = (int)$g['id'];

    $jenis = Database::fetchOne('SELECT * FROM jenis_ikan WHERE nama = ? LIMIT 1', ['AutoTest Ikan']);
    if (!$jenis) {
        $jenisId = JenisIkan::create([
            'nama' => 'AutoTest Ikan',
            'deskripsi' => 'Dibuat oleh skrip otomatis untuk testing',
            'is_active' => 1,
        ]);
    } else {
        $jenisId = (int)$jenis['id'];
    }

    $prodData = [
        'id_jenis_ikan' => $jenisId,
        'id_gudang' => $gudangId,
        'nama' => 'AutoTest Produk',
        'deskripsi' => 'Produk test otomatis',
        'harga_beli' => 10000,
        'harga_jual' => 15000,
        'stok_qty' => 10,
        'nilai_stok' => 10000 * 10,
        'stok_minimum' => 1,
        'is_active' => 1,
    ];

    $prodId = Produk::insert($prodData);

    $prod = Database::fetchOne('SELECT p.*, j.nama as nama_jenis FROM produk p JOIN jenis_ikan j ON j.id = p.id_jenis_ikan WHERE p.id = ? LIMIT 1', [$prodId]);

    echo json_encode(['success' => true, 'jenis_id' => $jenisId, 'produk_id' => $prodId, 'produk' => $prod]) . PHP_EOL;
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]) . PHP_EOL;
    exit(1);
}
