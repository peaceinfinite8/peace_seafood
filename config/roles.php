<?php

declare(strict_types=1);

/**
 * Role & Permission Definitions
 */

return [
    'bos' => [
        'dashboard.*',
        'stok.view',
        'penjualan.view',
        'penitipan.view',
        'retur.view',
        'retur.approve',
        'keuangan.view',
        'laporan.*',
        'master_data.view',
        'settings.*',
        'user.*',
        'export.*',
        'notifikasi.*',
    ],
    'admin' => [
        'dashboard.view',
        'stok.view',
        'stok.create',
        'stok.update',
        'penjualan.view',
        'penjualan.create',
        'penjualan.update',
        'penjualan.cancel',
        'penitipan.view',
        'penitipan.create',
        'penitipan.update',
        'retur.view',
        'retur.create',
        'keuangan.view',
        'keuangan.create',
        'keuangan.bayar',
        'laporan.view',
        'laporan.export',
        'master_data.view',
        'notifikasi.view',
        'notifikasi.read',
    ],
    'checker' => [
        'dashboard.stok',
        'stok.view',
        'stok.timbang',
        'laporan.stok',
        'master_data.view',
        'notifikasi.view',
        'notifikasi.read',
    ],
];
