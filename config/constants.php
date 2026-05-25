<?php

declare(strict_types=1);

/**
 * Application Constants
 */

// Roles
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_BOS',     'bos');
define('ROLE_ADMIN',   'admin');
define('ROLE_CHECKER', 'checker');

// Nota status
define('NOTA_DRAFT',    'draft');
define('NOTA_FINAL',    'final');
define('NOTA_CANCEL',   'cancel');

// Stok masuk status
define('STOK_PENDING',   'pending');
define('STOK_CONFIRMED', 'confirmed');
define('STOK_REJECTED',  'rejected');

// Hutang piutang status
define('HP_OPEN',     'open');
define('HP_SEBAGIAN', 'sebagian');
define('HP_LUNAS',    'lunas');

// Hutang piutang jenis
define('HP_HUTANG',  'hutang');
define('HP_PIUTANG', 'piutang');

// Retur status
define('RETUR_PENDING',  'pending');
define('RETUR_APPROVED', 'approved');
define('RETUR_REJECTED', 'rejected');
define('RETUR_POSTED',   'posted');

// Retur tipe
define('RETUR_STOK',    'stok');
define('RETUR_PIUTANG', 'piutang');

// Titipan status
define('TITIPAN_MASUK',          'masuk');
define('TITIPAN_DIJUAL_SEBAGIAN','dijual_sebagian');
define('TITIPAN_DIJUAL_SEMUA',   'dijual_semua');
define('TITIPAN_SELESAI',        'selesai');

// Komisi tipe
define('KOMISI_POTONG',         'potong');
define('KOMISI_BAYAR_TERPISAH', 'bayar_terpisah');

// Pembayaran
define('BAYAR_CASH',   'cash');
define('BAYAR_HUTANG', 'hutang');

// Harga tipe
define('HARGA_BELI', 'beli');
define('HARGA_JUAL', 'jual');

// Nota number prefix
define('NOTA_PREFIX', 'PS');

// Titipan number prefix
define('TITIPAN_PREFIX', 'TT');

// Notification types
define('NOTIF_STOK_MINIMUM',        'stok_minimum_alert');
define('NOTIF_HUTANG_JATUH_TEMPO',  'hutang_jatuh_tempo_reminder');
define('NOTIF_RETUR_PENDING',       'retur_pending_approval');
define('NOTIF_ERROR_SYSTEM',        'transaction_error');
define('NOTIF_MAINTENANCE',         'system_maintenance');

// Pagination
define('DEFAULT_PER_PAGE', 20);
define('MAX_PER_PAGE',     100);

// Timezone
define('APP_TIMEZONE', 'Asia/Jakarta');
