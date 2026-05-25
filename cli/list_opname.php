<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Utils\Database;

try {
    $rows = Database::fetchAll('SELECT so.*, g.nama as gudang, u.name as user FROM stok_opname so LEFT JOIN gudang g ON g.id = so.id_gudang LEFT JOIN users u ON u.id = so.created_by ORDER BY so.id DESC');
    echo json_encode($rows, JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]) . PHP_EOL;
    exit(1);
}
