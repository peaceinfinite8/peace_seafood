<?php

declare(strict_types=1);

/**
 * Role & Permission Definitions
 */

return [
    'super_admin' => [
        'dashboard.*',
        'stok.*',
        'penjualan.*',
        'penitipan.*',
        'retur.*',
        'keuangan.*',
        'laporan.*',
        'master_data.*',
        'harga.*',
        'settings.*',
        'user.*',
        'export.*',
        'notifikasi.*',
    ],
    'bos' => [
        'dashboard.view',
        'stok.view',
        'stok.history',
        'penjualan.view',
        'penitipan.view',
        'retur.view',
        'keuangan.view',
        'laporan.view',
        'laporan.export',
        'notifikasi.*',
    ],
    'admin' => [
        'dashboard.view',
        'stok.view',
        'stok.history',
        'stok.create',
        'stok.update',
        'stok.timbang',
        'penjualan.view',
        'penjualan.create',
        'penjualan.update',
        'penjualan.cancel',
        'penitipan.view',
        'penitipan.create',
        'penitipan.update',
        'retur.view',
        'retur.create',
        'retur.approve',
        'retur.reject',
        'keuangan.view',
        'laporan.view',
        'master_data.view',
        'notifikasi.view',
        'notifikasi.read',
    ],
    'checker' => [
        'dashboard.view',
        'stok.view',
        'stok.timbang',
        'notifikasi.view',
        'notifikasi.read',
    ],
];
