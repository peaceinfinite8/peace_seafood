<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Utils\Database;

try {
    $user = Database::fetchOne('SELECT id FROM users LIMIT 1');
    $idBos = $user ? (int)$user['id'] : 1;

    $exists = Database::fetchOne('SELECT id FROM gudang WHERE nama = ? LIMIT 1', ['AutoTest Gudang']);
    if ($exists) {
        echo json_encode(['gudang_id' => (int)$exists['id']]) . PHP_EOL;
        exit(0);
    }

    $id = Database::insert('gudang', [
        'id_bos' => $idBos,
        'nama' => 'AutoTest Gudang',
        'alamat' => 'Alamat Test',
        'kota' => 'Test City',
        'telpon' => '000-000-000',
        'is_active' => 1,
    ]);

    echo json_encode(['gudang_id' => $id]) . PHP_EOL;
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]) . PHP_EOL;
    exit(1);
}
