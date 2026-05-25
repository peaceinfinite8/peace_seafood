<?php

declare(strict_types=1);

use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\StokController;
use App\Controllers\PenjualanController;
use App\Controllers\PenitipanController;
use App\Controllers\ReturController;
use App\Controllers\KeuanganController;
use App\Controllers\LaporanController;
use App\Controllers\MasterDataController;
use App\Controllers\SettingsController;
use App\Controllers\NotifikasiController;
use App\Utils\Response;

require_once BASE_PATH . '/config/constants.php';

$method = $_SERVER['REQUEST_METHOD'];
// Gunakan $uri yang sudah distrip /peace_seafood dari index.php
// Kemudian strip /api prefix
$uri = ltrim($uri ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '');
$uri = preg_replace('#^/peace_seafood#', '', $uri); // fallback jika direct
$uri = preg_replace('#^/public#', '', $uri);        // fallback jika direct ke public/
$uri = preg_replace('#^/api#', '', $uri);
$uri = rtrim($uri, '/') ?: '/';

// ============================================================
// Route Table
// ============================================================
$routes = [
    // Auth (no auth required)
    'POST /auth/login'    => [AuthController::class,    'login',   false],
    'POST /auth/logout'   => [AuthController::class,    'logout',  true],
    'GET  /auth/profile'  => [AuthController::class,    'profile', true],

    // Dashboard
    'GET /dashboard'      => [DashboardController::class, 'index',  true],

    // Stok
    'GET /stok'                  => [StokController::class, 'index',   true],
    'POST /stok/masuk'           => [StokController::class, 'masuk',   true],
    'GET /stok/masuk/{id}'       => [StokController::class, 'showMasuk', true],
    'POST /stok/timbang'         => [StokController::class, 'timbang', true],
    'GET /stok/history'          => [StokController::class, 'history', true],
    'GET /stok/pending-timbang'  => [StokController::class, 'pendingTimbang', true],

    // Penjualan
    'GET /penjualan'                  => [PenjualanController::class, 'index',    true],
    'POST /penjualan'                 => [PenjualanController::class, 'create',   true],
    'GET /penjualan/{id}'             => [PenjualanController::class, 'show',     true],
    'PUT /penjualan/{id}'             => [PenjualanController::class, 'update',   true],
    'POST /penjualan/{id}/finalize'   => [PenjualanController::class, 'finalize', true],
    'POST /penjualan/{id}/cancel'     => [PenjualanController::class, 'cancel',   true],

    // Penitipan
    'GET /penitipan'                  => [PenitipanController::class, 'index',      true],
    'POST /penitipan'                 => [PenitipanController::class, 'create',     true],
    'GET /penitipan/{id}'             => [PenitipanController::class, 'show',       true],
    'POST /penitipan/{id}/jual'       => [PenitipanController::class, 'jual',       true],
    'POST /penitipan/{id}/selesai'    => [PenitipanController::class, 'selesai',    true],
    'GET /penitipan/{id}/settlement'  => [PenitipanController::class, 'settlement', true],

    // Retur
    'GET /retur'                  => [ReturController::class, 'index',   true],
    'POST /retur'                 => [ReturController::class, 'create',  true],
    'GET /retur/{id}'             => [ReturController::class, 'show',    true],
    'POST /retur/{id}/approve'    => [ReturController::class, 'approve', true],
    'POST /retur/{id}/reject'     => [ReturController::class, 'reject',  true],

    // Keuangan
    'GET /keuangan/hutang-piutang'       => [KeuanganController::class, 'index',      true],
    'POST /keuangan/hutang-piutang'      => [KeuanganController::class, 'create',     true],
    'GET /keuangan/hutang-piutang/{id}'  => [KeuanganController::class, 'show',       true],
    'POST /keuangan/bayar'               => [KeuanganController::class, 'bayar',      true],
    'GET /keuangan/biaya'                => [KeuanganController::class, 'biaya',      true],
    'POST /keuangan/biaya'               => [KeuanganController::class, 'storeBiaya', true],

    // Laporan
    'GET /laporan/stok'          => [LaporanController::class, 'stok',        true],
    'GET /laporan/penjualan'     => [LaporanController::class, 'penjualan',   true],
    'GET /laporan/keuangan'      => [LaporanController::class, 'keuangan',    true],
    'GET /laporan/hutang-aging'  => [LaporanController::class, 'hutangAging', true],
    'POST /laporan/export/pdf'   => [LaporanController::class, 'exportPdf',   true],
    'POST /laporan/export/excel' => [LaporanController::class, 'exportExcel', true],

    // Master Data — Supplier
    'GET /master/supplier'          => [MasterDataController::class, 'supplierIndex',   true],
    'POST /master/supplier'         => [MasterDataController::class, 'supplierStore',   true],
    'GET /master/supplier/{id}'     => [MasterDataController::class, 'supplierShow',    true],
    'PUT /master/supplier/{id}'     => [MasterDataController::class, 'supplierUpdate',  true],
    'DELETE /master/supplier/{id}'  => [MasterDataController::class, 'supplierDestroy', true],

    // Master Data — Pembeli
    'GET /master/pembeli'           => [MasterDataController::class, 'pembeliIndex',   true],
    'POST /master/pembeli'          => [MasterDataController::class, 'pembeliStore',   true],
    'GET /master/pembeli/{id}'      => [MasterDataController::class, 'pembeliShow',    true],
    'PUT /master/pembeli/{id}'      => [MasterDataController::class, 'pembeliUpdate',  true],
    'DELETE /master/pembeli/{id}'   => [MasterDataController::class, 'pembeliDestroy', true],

    // Master Data — Jenis Ikan
    'GET /master/jenis-ikan'        => [MasterDataController::class, 'jenisIkanIndex',  true],
    'POST /master/jenis-ikan'       => [MasterDataController::class, 'jenisIkanStore',  true],
    'PUT /master/jenis-ikan/{id}'   => [MasterDataController::class, 'jenisIkanUpdate', true],

    // Master Data — Produk
    'GET /master/produk'            => [MasterDataController::class, 'produkIndex',  true],
    'POST /master/produk'           => [MasterDataController::class, 'produkStore',  true],
    'GET /master/produk/{id}'       => [MasterDataController::class, 'produkShow',   true],
    'PUT /master/produk/{id}'       => [MasterDataController::class, 'produkUpdate', true],

    // Master Data — Harga
    'GET /master/harga'             => [MasterDataController::class, 'hargaIndex', true],
    'POST /master/harga'            => [MasterDataController::class, 'hargaStore', true],

    // Settings
    'GET /settings'                 => [SettingsController::class, 'index',            true],
    'PUT /settings/{kunci}'         => [SettingsController::class, 'update',           true],
    'GET /settings/users'           => [SettingsController::class, 'users',            true],
    'POST /settings/users'          => [SettingsController::class, 'storeUser',        true],
    'PUT /settings/users/{id}'      => [SettingsController::class, 'updateUser',       true],
    'DELETE /settings/users/{id}'   => [SettingsController::class, 'deleteUser',       true],
    'GET /settings/gudang'          => [SettingsController::class, 'gudang',           true],
    'POST /settings/gudang'         => [SettingsController::class, 'storeGudang',      true],
    'PUT /settings/gudang/{id}'     => [SettingsController::class, 'updateGudang',     true],
    'POST /settings/backup'         => [SettingsController::class, 'backup',           true],

    // Notifikasi
    'GET /notifikasi'               => [NotifikasiController::class, 'index',   true],
    'POST /notifikasi/{id}/read'    => [NotifikasiController::class, 'read',    true],
    'POST /notifikasi/read-all'     => [NotifikasiController::class, 'readAll', true],
];

// ============================================================
// Simple Router
// ============================================================
$matched = false;

foreach ($routes as $route => $handler) {
    // Parse "METHOD /path"
    $parts       = explode(' ', trim($route), 2);
    $routeMethod = trim($parts[0]);
    $routePath   = trim($parts[1]);

    // Convert {param} to regex capture group
    $pattern = '#^' . preg_replace('#\{[^}]+\}#', '([^/]+)', $routePath) . '$#';

    if ($method === $routeMethod && preg_match($pattern, $uri, $matches)) {
        array_shift($matches); // remove full match

        [$class, $action, $requireAuth] = $handler;

        // Auth check
        if ($requireAuth) {
            AuthMiddleware::handle();
        }

        $controller = new $class();
        $controller->$action(...$matches);
        $matched = true;
        break;
    }
}

if (!$matched) {
    Response::notFound('Endpoint tidak ditemukan');
}
