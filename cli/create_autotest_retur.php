<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Utils\Database;
use App\Services\ReturService;

try {
    $prod = Database::fetchOne('SELECT * FROM produk WHERE nama = ? LIMIT 1', ['AutoTest Produk']);
    if (!$prod) {
        echo json_encode(['error' => 'AutoTest Produk tidak ditemukan']) . PHP_EOL;
        exit(1);
    }
    $idProduk = (int)$prod['id'];
    $idGudang = (int)$prod['id_gudang'];

    $user = Database::fetchOne('SELECT id FROM users LIMIT 1');
    $idUser = $user ? (int)$user['id'] : 1;
    // Set global auth context so AuthMiddleware::getAuthUserId() works in CLI
    $GLOBALS['auth_user'] = ['id' => $idUser];

    $svc = new ReturService();
    $idRetur = $svc->createRetur([
        'id_produk' => $idProduk,
        'tipe' => 'stok',
        'qty' => 1,
        'alasan' => 'Retur test otomatis'
    ], $idUser, $idGudang);

    $approveOk = $svc->approveRetur($idRetur, $idGudang);

    echo json_encode(['success' => true, 'retur_id' => $idRetur, 'approved' => $approveOk]) . PHP_EOL;
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]) . PHP_EOL;
    exit(1);
}
