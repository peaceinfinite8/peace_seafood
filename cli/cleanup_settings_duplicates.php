<?php

declare(strict_types=1);

// Cleanup script: backup and remove tenant rows for known global keys
// Usage: php cli/cleanup_settings_duplicates.php

$pdo = require __DIR__ . '/../config/database.php';

$globalKeys = [
    'platform_developer_whatsapp',
    'onboarding_completed'
];

$placeholders = rtrim(str_repeat('?,', count($globalKeys)), ',');

// Prepare backup directory
$backupDir = __DIR__ . '/../storage/exports';
if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
$ts = date('Ymd_His');
$backupFile = $backupDir . "/settings_backup_{$ts}.sql";

echo "Connecting to database...\n";

// Fetch rows to backup
$stmt = $pdo->prepare("SELECT * FROM settings WHERE kunci IN ($placeholders) AND id_gudang IS NOT NULL");
$stmt->execute($globalKeys);
$rows = $stmt->fetchAll();

if (empty($rows)) {
    echo "No tenant rows found for global keys. Nothing to backup/delete.\n";
    exit(0);
}

// Write backup SQL
$fp = fopen($backupFile, 'w');
fwrite($fp, "-- Backup of settings rows (tenant) for keys: " . implode(',', $globalKeys) . "\n");
foreach ($rows as $r) {
    $id = (int)$r['id'];
    $kunci = addslashes($r['kunci']);
    $nilai = addslashes($r['nilai']);
    $idGudang = is_null($r['id_gudang']) ? 'NULL' : (int)$r['id_gudang'];
    $deskripsi = isset($r['deskripsi']) ? addslashes($r['deskripsi']) : '';
    $updated = isset($r['updated_at']) ? $r['updated_at'] : null;
    $sql = "INSERT INTO settings (id, kunci, nilai, id_gudang, deskripsi, updated_at) VALUES ({$id}, '{$kunci}', '{$nilai}', {$idGudang}, '{$deskripsi}', " . ($updated ? "'{$updated}'" : 'NULL') . ");\n";
    fwrite($fp, $sql);
}
fclose($fp);

echo "Backup written to: {$backupFile}\n";

// Delete tenant rows for these keys
$delStmt = $pdo->prepare("DELETE FROM settings WHERE kunci IN ($placeholders) AND id_gudang IS NOT NULL");
$delStmt->execute($globalKeys);
$deleted = $delStmt->rowCount();

echo "Deleted {$deleted} tenant settings rows for specified global keys.\n";

// Verify
$verifyStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM settings WHERE kunci IN ($placeholders) AND id_gudang IS NOT NULL");
$verifyStmt->execute($globalKeys);
$cnt = (int)$verifyStmt->fetchColumn();
if ($cnt === 0) echo "Verification passed: no tenant rows remain for these keys.\n";
else echo "Verification WARNING: {$cnt} tenant rows still exist for these keys.\n";

echo "Cleanup complete. Review backup file before proceeding.\n";

return 0;
