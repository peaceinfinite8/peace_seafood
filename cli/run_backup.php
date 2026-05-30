<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Utils\Database;

try {
    $pdo = Database::getInstance();
    $tables = [];
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    $sqlDump = "-- Peace Seafood Database Backup\n";
    $sqlDump .= "-- Generated on " . date('Y-m-d H:i:s') . "\n\n";
    $sqlDump .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    foreach ($tables as $table) {
        $sqlDump .= "DROP TABLE IF EXISTS `{$table}`;\n";

        $res = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
        $sqlDump .= $res['Create Table'] . ";\n\n";

        $rows = $pdo->query("SELECT * FROM `{$table}`");
        while ($rowData = $rows->fetch(PDO::FETCH_ASSOC)) {
            $keys = array_keys($rowData);
            $escapedKeys = array_map(function ($k) {
                return "`{$k}`";
            }, $keys);
            $values = array_values($rowData);
            $escapedValues = array_map(function ($v) use ($pdo) {
                if ($v === null) return 'NULL';
                return $pdo->quote((string) $v);
            }, $values);

            $sqlDump .= "INSERT INTO `{$table}` (" . implode(', ', $escapedKeys) . ") VALUES (" . implode(', ', $escapedValues) . ");\n";
        }
        $sqlDump .= "\n";
    }

    $sqlDump .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    $dir = __DIR__ . '/../storage/exports';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $filename = $dir . '/peace_seafood_backup_' . date('Y-m-d_H-i-s') . '.sql';
    file_put_contents($filename, $sqlDump);

    echo json_encode(['success' => true, 'file' => $filename]) . PHP_EOL;
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]) . PHP_EOL;
    exit(1);
}
