<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Utils\Database;
use App\Services\PenitipanService;

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

    $svc = new PenitipanService();

    $data = [
        'id_pengirim' => $idUser,
        'id_produk' => $idProduk,
        'qty' => 3,
        'harga_titip' => 8000,
        'komisi_persen' => 10,
        'catatan' => 'AutoTest titipan'
    ];

    $idTitipan = $svc->createTitipan($data, $idUser, $idGudang);

    // Sell one item from titipan
    $jualOk = $svc->jualTitipan($idTitipan, ['qty_terjual' => 1, 'harga_jual' => 10000, 'id_pembeli' => null], $idGudang);

    // Attempt to finalize/selesaikan
    $selesaiOk = $svc->selesaikanTitipan($idTitipan, $idGudang);

    echo json_encode(['success' => true, 'titipan_id' => $idTitipan, 'jual_ok' => $jualOk, 'selesai_ok' => $selesaiOk]) . PHP_EOL;
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]) . PHP_EOL;
    exit(1);
}
