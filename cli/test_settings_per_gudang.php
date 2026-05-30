<?php

declare(strict_types=1);

// Simple test script to verify per-gudang settings behavior
// Usage: php cli/test_settings_per_gudang.php

$pdo = require __DIR__ . '/../config/database.php';

echo "Running per-gudang settings test...\n";

// Pick a gudang id that exists in settings or fallback to 1
$gStmt = $pdo->query("SELECT DISTINCT id_gudang FROM settings WHERE id_gudang IS NOT NULL LIMIT 1");
$found = $gStmt->fetch();
$gudangId = $found ? (int)$found['id_gudang'] : 1;

$testKey = '__test_per_gudang_' . rand(1000, 9999);
$testVal = 'ok_' . time();

// Insert test row
$ins = $pdo->prepare('INSERT INTO settings (kunci, nilai, id_gudang, deskripsi) VALUES (?, ?, ?, ?)');
$ins->execute([$testKey, $testVal, $gudangId, 'automated test row']);
$insertId = $pdo->lastInsertId();

echo "Inserted test row id={$insertId} key={$testKey} for id_gudang={$gudangId}\n";

// Fetch as gudang-specific
$s1 = $pdo->prepare('SELECT * FROM settings WHERE kunci = ? AND id_gudang = ?');
$s1->execute([$testKey, $gudangId]);
$r1 = $s1->fetchAll();
if (count($r1) === 1) echo "PASS: Gudang-specific fetch returned the test row.\n";
else echo "FAIL: Gudang-specific fetch did not return expected row.\n";

// Fetch global
$s2 = $pdo->prepare('SELECT * FROM settings WHERE kunci = ? AND id_gudang IS NULL');
$s2->execute([$testKey]);
$r2 = $s2->fetchAll();
if (count($r2) === 0) echo "PASS: Global fetch did not return the test row (expected).\n";
else echo "FAIL: Global fetch unexpectedly returned rows.\n";

// Cleanup test row
$del = $pdo->prepare('DELETE FROM settings WHERE id = ?');
$del->execute([$insertId]);
echo "Cleaned up test row id={$insertId}\n";

echo "Per-gudang settings test complete.\n";

return 0;
