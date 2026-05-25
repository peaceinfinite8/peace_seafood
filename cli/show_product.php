<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Utils\Database;

$prod = Database::fetchOne('SELECT * FROM produk WHERE nama = ? LIMIT 1', ['AutoTest Produk']);
echo json_encode($prod, JSON_PRETTY_PRINT) . PHP_EOL;
