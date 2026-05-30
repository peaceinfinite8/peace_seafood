<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Utils\Database;
use App\Services\StokOpnameService;

try {
    $prod = Database::fetchOne('SELECT * FROM produk WHERE nama = ? LIMIT 1', ['AutoTest Produk']);
    if (!$prod) {
        echo json_encode(['error' => 'AutoTest Produk tidak ditemukan']) . PHP_EOL;
        exit(1);
    }
    $idProduk = (int)$prod['id'];
    $idGudang = (int)$prod['id_gudang'];

    $user = Database::fetchOne('SELECT id FROM users LIMIT 1');
    if (!$user) {
        echo json_encode(['error' => 'Tidak ada user di tabel users']) . PHP_EOL;
        exit(1);
    }
    $idUser = (int)$user['id'];
    // Set global auth context so AuthMiddleware::getAuthUserId() works in CLI
    $GLOBALS['auth_user'] = ['id' => $idUser];

    $svc = new StokOpnameService();
    $idOpname = $svc->createOpname(['items' => [['id_produk' => $idProduk, 'qty_fisik' => 5]]], $idUser, $idGudang);

    echo json_encode(['success' => true, 'opname_id' => $idOpname]) . PHP_EOL;
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]) . PHP_EOL;
    exit(1);
}
