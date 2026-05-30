<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Utils\Database;

try {
    $rows = Database::fetchAll('SELECT t.*, ga.nama as gudang_asal, gt.nama as gudang_tujuan, p.nama as produk_nama FROM stok_transfer t JOIN gudang ga ON ga.id = t.gudang_asal_id JOIN gudang gt ON gt.id = t.gudang_tujuan_id JOIN produk p ON p.id = t.id_produk ORDER BY t.id DESC');
    echo json_encode($rows, JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]) . PHP_EOL;
    exit(1);
}
