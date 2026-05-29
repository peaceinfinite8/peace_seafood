<?php

declare(strict_types=1);

/**
 * Peace Seafood — AutoTest Runner
 *
 * Menjalankan seluruh rangkaian uji coba CLI secara berurutan:
 *   1. Buat gudang test
 *   2. Buat produk test
 *   3. Input stok masuk + timbangan (status → confirmed)
 *   4. Buat nota penjualan + finalisasi (harus berhasil karena stok sudah confirmed)
 *   5. Uji coba modul lain (penitipan, retur, transfer, opname)
 *   6. Cleanup semua data AutoTest
 *
 * Jalankan: php cli/run_autotest.php
 */

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/vendor/autoload.php';

use App\Utils\Database;

// ── Warna terminal ──────────────────────────────────────────────────────────
function clr(string $text, string $color): string
{
    $colors = ['green' => "\033[32m", 'red' => "\033[31m", 'yellow' => "\033[33m",
               'cyan'  => "\033[36m", 'bold' => "\033[1m",  'reset' => "\033[0m"];
    return ($colors[$color] ?? '') . $text . $colors['reset'];
}

function step(string $msg): void  { echo clr("  ▶ {$msg}", 'cyan') . PHP_EOL; }
function ok(string $msg): void    { echo clr("  ✓ {$msg}", 'green') . PHP_EOL; }
function warn(string $msg): void  { echo clr("  ⚠ {$msg}", 'yellow') . PHP_EOL; }
function fail(string $msg): void  { echo clr("  ✗ {$msg}", 'red') . PHP_EOL; }
function hr(): void               { echo str_repeat('─', 60) . PHP_EOL; }

// ── Jalankan script CLI dan kembalikan decoded JSON ─────────────────────────
function runScript(string $script): array
{
    $php    = 'c:\\xamppp\\php\\php.exe';
    $path   = BASE_PATH . '/cli/' . $script;
    // Redirect stderr ke /dev/null agar warning PHP tidak campur dengan JSON output
    $output = shell_exec("\"{$php}\" -d error_reporting=E_ERROR \"{$path}\" 2>&1");
    $lines  = array_filter(array_map('trim', explode(PHP_EOL, $output ?? '')));
    // Ambil baris terakhir yang valid JSON
    foreach (array_reverse(array_values($lines)) as $line) {
        $decoded = json_decode($line, true);
        if (is_array($decoded)) return $decoded;
    }
    return ['raw_output' => $output];
}

// ── Tracker hasil ───────────────────────────────────────────────────────────
$passed = 0;
$failed = 0;
$errors = [];

function assertOk(string $label, bool $condition, string $detail = ''): void
{
    global $passed, $failed, $errors;
    if ($condition) {
        ok($label);
        $passed++;
    } else {
        fail($label . ($detail ? " — {$detail}" : ''));
        $failed++;
        $errors[] = $label;
    }
}

// ════════════════════════════════════════════════════════════════════════════
echo PHP_EOL;
echo clr('╔══════════════════════════════════════════════════════════╗', 'bold') . PHP_EOL;
echo clr('║       PEACE SEAFOOD — AUTOTEST RUNNER                   ║', 'bold') . PHP_EOL;
echo clr('╚══════════════════════════════════════════════════════════╝', 'bold') . PHP_EOL;
echo PHP_EOL;

// ── 0. Cleanup sisa test sebelumnya ─────────────────────────────────────────
hr();
echo clr('[0] Membersihkan sisa data AutoTest sebelumnya...', 'bold') . PHP_EOL;
$php  = 'c:\\xamppp\\php\\php.exe';
$path = BASE_PATH . '/cli/cleanup_autotest.php';
passthru("\"{$php}\" \"{$path}\" 2>&1");
echo PHP_EOL;

// ── 1. Gudang ────────────────────────────────────────────────────────────────
hr();
echo clr('[1] Membuat AutoTest Gudang...', 'bold') . PHP_EOL;
step('Menjalankan create_autotest_gudang.php');
$res = runScript('create_autotest_gudang.php');
assertOk('Gudang berhasil dibuat/ditemukan', isset($res['gudang_id']) && $res['gudang_id'] > 0,
    json_encode($res));
$gudangId = (int)($res['gudang_id'] ?? 0);
echo PHP_EOL;

// ── 2. Produk ────────────────────────────────────────────────────────────────
hr();
echo clr('[2] Membuat AutoTest Produk...', 'bold') . PHP_EOL;
step('Menjalankan create_autotest_product.php');
$res = runScript('create_autotest_product.php');
assertOk('Produk berhasil dibuat', !empty($res['success']) && !empty($res['produk_id']),
    json_encode($res));
$produkId = (int)($res['produk_id'] ?? 0);
echo PHP_EOL;

// ── 3. Stok Masuk + Timbangan ────────────────────────────────────────────────
hr();
echo clr('[3] Input Stok Masuk + Konfirmasi Timbangan...', 'bold') . PHP_EOL;
step('Menjalankan create_autotest_stok_masuk.php');
$res = runScript('create_autotest_stok_masuk.php');
assertOk('Stok masuk berhasil diinput', !empty($res['success']) && !empty($res['stok_masuk_id']),
    json_encode($res));
assertOk('Timbangan berhasil dikonfirmasi (status → confirmed)',
    !empty($res['timbang_confirmed']),
    'timbang_confirmed = ' . json_encode($res['timbang_confirmed'] ?? null));
$stokMasukId = (int)($res['stok_masuk_id'] ?? 0);
echo PHP_EOL;

// ── 3b. Verifikasi status stok_masuk = confirmed ─────────────────────────────
if ($stokMasukId > 0) {
    step("Memverifikasi status stok_masuk #{$stokMasukId} di database...");
    $sm = Database::fetchOne("SELECT status FROM stok_masuk WHERE id = ?", [$stokMasukId]);
    assertOk("Status stok_masuk #{$stokMasukId} = 'confirmed'",
        ($sm['status'] ?? '') === 'confirmed',
        "status aktual = '" . ($sm['status'] ?? 'NULL') . "'");
    echo PHP_EOL;
}

// ── 4. Penjualan + Finalisasi ────────────────────────────────────────────────
hr();
echo clr('[4] Membuat Nota Penjualan + Finalisasi...', 'bold') . PHP_EOL;
step('Menjalankan create_autotest_penjualan.php');
$res = runScript('create_autotest_penjualan.php');
assertOk('Nota penjualan berhasil dibuat', !empty($res['success']) && !empty($res['nota_id']),
    json_encode($res));
assertOk('Nota berhasil difinalisasi (stok confirmed tersedia)',
    !empty($res['finalized']),
    'finalized = ' . json_encode($res['finalized'] ?? null));
$notaId = (int)($res['nota_id'] ?? 0);
echo PHP_EOL;

// ── 5. Penitipan ─────────────────────────────────────────────────────────────
hr();
echo clr('[5] Modul Penitipan...', 'bold') . PHP_EOL;
step('Menjalankan create_autotest_penitipan.php');
$res = runScript('create_autotest_penitipan.php');
assertOk('Penitipan berhasil dibuat', !empty($res['success']), json_encode($res));
echo PHP_EOL;

// ── 6. Retur ─────────────────────────────────────────────────────────────────
hr();
echo clr('[6] Modul Retur...', 'bold') . PHP_EOL;
step('Menjalankan create_autotest_retur.php');
$res = runScript('create_autotest_retur.php');
assertOk('Retur berhasil dibuat', !empty($res['success']), json_encode($res));
echo PHP_EOL;

// ── 7. Transfer Stok ─────────────────────────────────────────────────────────
hr();
echo clr('[7] Modul Transfer Stok...', 'bold') . PHP_EOL;
step('Menjalankan create_autotest_transfer.php');
$res = runScript('create_autotest_transfer.php');
assertOk('Transfer stok berhasil dibuat', !empty($res['success']), json_encode($res));
echo PHP_EOL;

// ── 8. Stok Opname ───────────────────────────────────────────────────────────
hr();
echo clr('[8] Modul Stok Opname...', 'bold') . PHP_EOL;
step('Menjalankan create_autotest_opname.php');
$res = runScript('create_autotest_opname.php');
assertOk('Stok opname berhasil dibuat', !empty($res['success']), json_encode($res));
echo PHP_EOL;

// ── 9. Cleanup ───────────────────────────────────────────────────────────────
hr();
echo clr('[9] Membersihkan semua data AutoTest...', 'bold') . PHP_EOL;
step('Menjalankan cleanup_autotest.php');
passthru("\"{$php}\" \"{$path}\" 2>&1");
echo PHP_EOL;

// ── 10. Verifikasi database bersih ───────────────────────────────────────────
hr();
echo clr('[10] Verifikasi database bersih dari sisa AutoTest...', 'bold') . PHP_EOL;
$checks = [
    ['produk',           "SELECT COUNT(*) as c FROM produk WHERE nama = 'AutoTest Produk'"],
    ['supplier',         "SELECT COUNT(*) as c FROM supplier WHERE nama = 'AutoTest Supplier'"],
    ['jenis_ikan',       "SELECT COUNT(*) as c FROM jenis_ikan WHERE nama = 'AutoTest Ikan'"],
    ['stok_masuk',       "SELECT COUNT(*) as c FROM stok_masuk WHERE catatan LIKE '%AutoTest%'"],
    ['nota',             "SELECT COUNT(*) as c FROM nota WHERE catatan LIKE '%AutoTest%'"],
    ['activity_log',     "SELECT COUNT(*) as c FROM activity_log WHERE before_value LIKE '%AutoTest%' OR after_value LIKE '%AutoTest%'"],
];
foreach ($checks as [$label, $sql]) {
    $row = Database::fetchOne($sql);
    $count = (int)($row['c'] ?? 0);
    assertOk("Tabel {$label} bersih dari data AutoTest", $count === 0, "{$count} baris tersisa");
}
echo PHP_EOL;

// ── Ringkasan ────────────────────────────────────────────────────────────────
hr();
echo clr('HASIL AUTOTEST', 'bold') . PHP_EOL;
echo clr("  ✓ Passed : {$passed}", 'green') . PHP_EOL;
if ($failed > 0) {
    echo clr("  ✗ Failed : {$failed}", 'red') . PHP_EOL;
    echo clr('  Daftar gagal:', 'red') . PHP_EOL;
    foreach ($errors as $e) {
        echo clr("    - {$e}", 'red') . PHP_EOL;
    }
} else {
    echo clr('  Semua test lulus. Database bersih. Siap deploy!', 'green') . PHP_EOL;
}
hr();
echo PHP_EOL;

exit($failed > 0 ? 1 : 0);
