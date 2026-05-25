<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Utils\Database;
use App\Services\StokTransferService;

try {
    $prod = Database::fetchOne('SELECT * FROM produk WHERE nama = ? LIMIT 1', ['AutoTest Produk']);
    if (!$prod) {
        echo json_encode(['error' => 'AutoTest Produk tidak ditemukan']) . PHP_EOL;
        exit(1);
    }
    $idProduk = (int)$prod['id'];
    $idGudangAsal = (int)$prod['id_gudang'];

    $tujuan = Database::fetchOne('SELECT id FROM gudang WHERE is_active = 1 AND id != ? LIMIT 1', [$idGudangAsal]);
    if (!$tujuan) {
        echo json_encode(['error' => 'Tidak ada gudang tujuan lain untuk transfer']) . PHP_EOL;
        exit(1);
    }
    $idGudangTujuan = (int)$tujuan['id'];

    $svc = new StokTransferService();
    $idUser = 1; // gunakan user system (pastikan ada user id=1)

    $idTransfer = $svc->createTransfer([
        'id_produk' => $idProduk,
        'gudang_tujuan_id' => $idGudangTujuan,
        'qty' => 2,
    ], $idUser, $idGudangAsal);

    echo json_encode(['success' => true, 'transfer_id' => $idTransfer]) . PHP_EOL;
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]) . PHP_EOL;
    exit(1);
}
