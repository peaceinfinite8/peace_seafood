-- ============================================================
-- PEACE SEAFOOD — SEEDER DATA
-- Run after schema.sql
-- ============================================================

USE `peace_seafood`;

-- ============================================================
-- STEP 1: Insert BOZ user first (no gudang yet)
-- ============================================================
INSERT INTO `users` (`name`, `email`, `password`, `role`, `id_gudang`, `is_active`) VALUES
('Bos Gudang', 'bos@example.com', '$2y$10$JxE6REWeabOrTEYgXNMtY.Uzml.WIqkr0ZGo7jTMEm95RhN4p5tSi', 'bos', NULL, 1);
-- password: bos123

-- ============================================================
-- STEP 2: Insert Gudang (referencing bos user id=1)
-- ============================================================
INSERT INTO `gudang` (`id_bos`, `nama`, `alamat`, `kota`, `telpon`, `is_active`) VALUES
(1, 'Gudang A - Pusat',     'Jl. Merdeka No. 1',   'Jakarta',   '021-12345678', 1),
(1, 'Gudang B - Cabang',    'Jl. Sudirman No. 5',  'Jakarta',   '021-87654321', 1),
(1, 'Gudang C - Satellite', 'Jl. Thamrin No. 10',  'Tangerang', '021-55555555', 1);

-- ============================================================
-- STEP 3: Insert Admin & Checker users (assigned to gudang)
-- ============================================================
INSERT INTO `users` (`name`, `email`, `password`, `role`, `id_gudang`, `is_active`) VALUES
('Admin Gudang A',   'admin@example.com',   '$2y$10$I8UNIJXElAl9hgW7Un0mru2ziep/hYZ6c6px5FiqIiFltwVMYJB62', 'admin',   1, 1),
('Checker Gudang A', 'checker@example.com', '$2y$10$w.ku/nvdLtSJBRBtGxEDdu30A.7vXrmqTjHHVf7dZGbIMLCSiEZTC', 'checker', 1, 1),
('Admin Gudang B',   'admin2@example.com',  '$2y$10$BoQIaCGlEBBDsO4IHPoUlOTwHI/vz4VOBA3lYJLe2biJyYIXICMAi', 'admin',   2, 1);
-- passwords: admin123, checker123, admin2

-- ============================================================
-- STEP 4: Jenis Ikan
-- ============================================================
INSERT INTO `jenis_ikan` (`nama`, `deskripsi`, `is_active`) VALUES
('Ikan Laut Segar',  'Ikan laut dari nelayan langsung',    1),
('Ikan Darat Segar', 'Ikan dari kolam/budidaya',           1),
('Ikan Beku',        'Ikan yang sudah dibekukan',          1),
('Ikan Olahan',      'Ikan yang sudah diproses/diolah',    1),
('Seafood Lainnya',  'Udang, cumi, kepiting, dll',         1);

-- ============================================================
-- STEP 5: Supplier
-- ============================================================
INSERT INTO `supplier` (`id_gudang`, `nama`, `nama_pemilik`, `kontak_person`, `telpon`, `alamat`, `kota`, `bank_name`, `bank_account`, `bank_owner`, `is_active`) VALUES
(1, 'Supplier Laut Jaya',    'Budi Santoso',    'Adi',     '0812-11111111', 'Jl. Pelabuhan No.1',  'Jakarta',  'BCA',     '1234567890', 'PT LAUT JAYA',     1),
(1, 'Supplier Ikan Nusa',    'Siti Nurhaliza',  'Rini',    '0812-22222222', 'Jl. Perikanan No.5',  'Depok',    'BRI',     '0987654321', 'CV IKAN NUSA',     1),
(1, 'Supplier Seafood Indah','Ahmad Wijaya',    'Bambang', '0812-33333333', 'Jl. Pelindo No.10',   'Medan',    'Mandiri', '1111111111', 'PT SEAFOOD INDAH', 1),
(1, 'Supplier Premium Fish', 'Tina Wijaya',     'Supri',   '0812-44444444', 'Jl. Dermaga No.15',   'Surabaya', 'BNI',     '2222222222', 'PT PREMIUM FISH',  1),
(2, 'Supplier Nusantara',    'Hendra Gunawan',  'Dedi',    '0812-55555555', 'Jl. Nelayan No.3',    'Jakarta',  'BCA',     '3333333333', 'CV NUSANTARA',     1);

-- ============================================================
-- STEP 6: Pembeli (Buyer)
-- ============================================================
INSERT INTO `pembeli` (`id_gudang`, `nama`, `telpon`, `alamat`, `kota`, `tipe`, `kredit_limit`, `is_active`) VALUES
(1, 'PT Restoran Mewah',    '021-11111111', 'Jl. Gatot Subroto No.1',  'Jakarta', 'bulk',    50000000,  1),
(1, 'Pasar Senen',          '021-22222222', 'Pasar Senen Blok A',      'Jakarta', 'retail',  10000000,  1),
(1, 'Hotel Grand Indonesia','021-33333333', 'Jl. Thamrin No.1',        'Jakarta', 'bulk',    100000000, 1),
(1, 'Toko Ikan Segar',      '021-44444444', 'Jl. Hayam Wuruk No.5',   'Jakarta', 'retail',  5000000,   1),
(1, 'Restoran Seafood',     '021-55555555', 'Jl. Blora No.10',         'Jakarta', 'bulk',    30000000,  1),
(1, 'Pasar Tradisional',    '021-66666666', 'Pasar Tanah Abang',       'Jakarta', 'retail',  8000000,   1),
(2, 'Catering Nusantara',   '021-77777777', 'Jl. Sudirman No.20',      'Jakarta', 'bulk',    25000000,  1);

-- ============================================================
-- STEP 7: Produk
-- ============================================================
INSERT INTO `produk` (`id_jenis_ikan`, `id_gudang`, `nama`, `deskripsi`, `gambar`, `harga_beli`, `harga_jual`, `stok_qty`, `nilai_stok`, `stok_minimum`, `is_active`) VALUES
(1, 1, 'Ikan Kakap Merah',  'Ikan kakap merah segar berkualitas',  'kakap_merah.webp',      55000, 70000,  0, 0, 50,  1),
(1, 1, 'Ikan Kerapu',       'Ikan kerapu segar pilihan',           NULL,                    75000, 95000,  0, 0, 30,  1),
(1, 1, 'Ikan Tenggiri',     'Ikan tenggiri segar premium',         'tenggiri.webp',         60000, 78000,  0, 0, 40,  1),
(1, 1, 'Ikan Tuna',         'Ikan tuna segar grade A',             'tuna.webp',             65000, 85000,  0, 0, 30,  1),
(2, 1, 'Ikan Nila Segar',   'Ikan nila dari kolam budidaya',       'nila.webp',             30000, 42000,  0, 0, 100, 1),
(2, 1, 'Ikan Lele Segar',   'Ikan lele segar dari kolam',          'lele.webp',             25000, 35000,  0, 0, 100, 1),
(3, 1, 'Ikan Kakap Beku',   'Ikan kakap beku -18°C',               'kakap_merah_beku.webp', 48000, 62000,  0, 0, 50,  1),
(5, 1, 'Udang Windu',       'Udang windu segar ukuran besar',      'udang_windu.webp',      120000, 150000, 0, 0, 25,  1),
(5, 1, 'Cumi-cumi Segar',   'Cumi-cumi segar dari laut',           'cumi.webp',             55000, 72000,  0, 0, 30,  1),
(1, 2, 'Ikan Kakap Merah',  'Ikan kakap merah segar',              'kakap_merah.webp',      55000, 70000,  0, 0, 50,  1),
(2, 2, 'Ikan Nila Segar',   'Ikan nila segar',                     'nila.webp',             30000, 42000,  0, 0, 100, 1);

-- ============================================================
-- STEP 8: Settings (Default per gudang)
-- ============================================================
INSERT INTO `settings` (`id_gudang`, `kunci`, `nilai`, `deskripsi`) VALUES
(1, 'multi_warehouse_aktif',        '0',      'Multi warehouse feature aktif/nonaktif'),
(1, 'stok_minimum_threshold',       '50',     'Default stok minimum dalam kg'),
(1, 'susut_alert_threshold',        '5',      'Alert jika susut > X% dari qty teoritis'),
(1, 'komisi_penitipan_tipe',        'potong', 'Metode komisi: potong atau bayar_terpisah'),
(1, 'komisi_penitipan_persen',      '5',      'Persentase komisi default'),
(1, 'pajak_default_persen',         '0',      'Pajak default untuk nota penjualan'),
(1, 'jatuh_tempo_default_hari',     '30',     'Periode kredit default dalam hari'),
(1, 'session_timeout_menit',        '30',     'Auto-logout setelah inaktif X menit'),
(1, 'onboarding_wizard_aktif',      '1',      'Tampilkan wizard setup pertama kali'),
(1, 'backup_otomatis',              '1',      'Backup database otomatis'),
(1, 'harga_locked_untuk',           'bos',    'Siapa yang bisa ubah harga: bos/admin/semua'),
(1, 'export_permission',            'bos',    'Siapa yang bisa export: bos/admin/semua'),
(2, 'multi_warehouse_aktif',        '0',      'Multi warehouse feature aktif/nonaktif'),
(2, 'stok_minimum_threshold',       '50',     'Default stok minimum dalam kg'),
(2, 'komisi_penitipan_tipe',        'potong', 'Metode komisi'),
(2, 'pajak_default_persen',         '0',      'Pajak default'),
(2, 'jatuh_tempo_default_hari',     '30',     'Periode kredit default'),
(2, 'harga_locked_untuk',           'bos',    'Siapa yang bisa ubah harga'),
(2, 'export_permission',            'bos',    'Siapa yang bisa export');

-- ============================================================
-- STEP 9: Sample Stok Masuk (7 hari terakhir)
-- ============================================================
INSERT INTO `stok_masuk` (`id_gudang`, `id_produk`, `id_supplier`, `qty`, `harga_beli`, `status`, `created_by`, `created_at`) VALUES
(1, 1, 1, 500, 55000, 'confirmed', 2, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(1, 2, 1, 200, 75000, 'confirmed', 2, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(1, 3, 2, 300, 60000, 'confirmed', 2, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 5, 2, 800, 30000, 'confirmed', 2, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 8, 3, 100, 120000,'confirmed', 2, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 1, 1, 300, 55000, 'confirmed', 2, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 4, 4, 150, 65000, 'confirmed', 2, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 6, 2, 400, 25000, 'pending',   2, NOW());

-- ============================================================
-- STEP 10: Timbangan (untuk stok_masuk yang confirmed)
-- ============================================================
INSERT INTO `timbangan` (`id_stok_masuk`, `id_produk`, `qty_teoritis`, `qty_actual`, `alasan_susut`, `created_by`, `created_at`) VALUES
(1, 1, 500, 495, 'Kemasan sedikit bocor',    3, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(2, 2, 200, 200, NULL,                       3, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(3, 3, 300, 297, 'Evaporasi normal',         3, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(4, 5, 800, 790, 'Kualitas jelek 10kg',      3, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(5, 8, 100, 100, NULL,                       3, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(6, 1, 300, 298, 'Sedikit susut perjalanan', 3, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(7, 4, 150, 150, NULL,                       3, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- ============================================================
-- STEP 11: Update stok produk setelah timbangan confirmed
-- ============================================================
UPDATE `produk` SET `stok_qty` = 793,  `nilai_stok` = 793  * 55000  WHERE `id` = 1;  -- Kakap Merah (495+298)
UPDATE `produk` SET `stok_qty` = 200,  `nilai_stok` = 200  * 75000  WHERE `id` = 2;  -- Kerapu
UPDATE `produk` SET `stok_qty` = 297,  `nilai_stok` = 297  * 60000  WHERE `id` = 3;  -- Tenggiri
UPDATE `produk` SET `stok_qty` = 150,  `nilai_stok` = 150  * 65000  WHERE `id` = 4;  -- Tuna
UPDATE `produk` SET `stok_qty` = 790,  `nilai_stok` = 790  * 30000  WHERE `id` = 5;  -- Nila
UPDATE `produk` SET `stok_qty` = 100,  `nilai_stok` = 100  * 120000 WHERE `id` = 8;  -- Udang Windu

-- ============================================================
-- STEP 12: Harga History (initial prices)
-- ============================================================
INSERT INTO `harga_history` (`id_produk`, `harga_lama`, `harga_baru`, `tipe`, `reason`, `changed_by`, `created_at`) VALUES
(1, NULL,  55000, 'beli', 'Harga awal setup',          1, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(1, NULL,  70000, 'jual', 'Harga awal setup',          1, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(2, NULL,  75000, 'beli', 'Harga awal setup',          1, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(2, NULL,  95000, 'jual', 'Harga awal setup',          1, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(3, NULL,  60000, 'beli', 'Harga awal setup',          1, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(3, NULL,  78000, 'jual', 'Harga awal setup',          1, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(1, 50000, 55000, 'beli', 'Harga naik dari supplier',  1, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(1, 65000, 70000, 'jual', 'Adjustment market',         1, DATE_SUB(NOW(), INTERVAL 10 DAY));

-- ============================================================
-- STEP 13: Sample Nota Penjualan
-- ============================================================
INSERT INTO `nota` (`id_gudang`, `id_pembeli`, `no_nota`, `tanggal_nota`, `subtotal`, `diskon_nominal`, `pajak`, `total`, `pembayaran`, `status`, `created_by`, `created_at`) VALUES
(1, 1, 'PS-250510-0001', DATE_SUB(CURDATE(), INTERVAL 7 DAY), 5250000, 250000, 0, 5000000, 'cash',   'final', 2, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(1, 2, 'PS-250511-0001', DATE_SUB(CURDATE(), INTERVAL 6 DAY), 2940000, 0,      0, 2940000, 'hutang', 'final', 2, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(1, 3, 'PS-250512-0001', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 9500000, 500000, 0, 9000000, 'hutang', 'final', 2, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 1, 'PS-250513-0001', DATE_SUB(CURDATE(), INTERVAL 4 DAY), 3500000, 0,      0, 3500000, 'cash',   'final', 2, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 4, 'PS-250514-0001', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 1680000, 0,      0, 1680000, 'cash',   'final', 2, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 2, 'PS-250515-0001', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 4200000, 200000, 0, 4000000, 'hutang', 'final', 2, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 5, 'PS-250516-0001', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 7800000, 0,      0, 7800000, 'cash',   'final', 2, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- ============================================================
-- STEP 14: Nota Detail
-- ============================================================
INSERT INTO `nota_detail` (`id_nota`, `id_produk`, `qty`, `harga_jual`, `subtotal`) VALUES
(1, 1, 50, 70000, 3500000),
(1, 3, 25, 70000, 1750000),
(2, 5, 70, 42000, 2940000),
(3, 2, 50, 95000, 4750000),
(3, 8, 31, 150000,4650000),
(4, 1, 50, 70000, 3500000),
(5, 5, 40, 42000, 1680000),
(6, 3, 60, 70000, 4200000),
(7, 1, 60, 70000, 4200000),
(7, 9, 50, 72000, 3600000);

-- Update stok setelah penjualan
UPDATE `produk` SET `stok_qty` = `stok_qty` - 50  WHERE `id` = 1;  -- nota 1
UPDATE `produk` SET `stok_qty` = `stok_qty` - 25  WHERE `id` = 3;  -- nota 1
UPDATE `produk` SET `stok_qty` = `stok_qty` - 70  WHERE `id` = 5;  -- nota 2
UPDATE `produk` SET `stok_qty` = `stok_qty` - 50  WHERE `id` = 2;  -- nota 3
UPDATE `produk` SET `stok_qty` = `stok_qty` - 31  WHERE `id` = 8;  -- nota 3
UPDATE `produk` SET `stok_qty` = `stok_qty` - 50  WHERE `id` = 1;  -- nota 4
UPDATE `produk` SET `stok_qty` = `stok_qty` - 40  WHERE `id` = 5;  -- nota 5
UPDATE `produk` SET `stok_qty` = `stok_qty` - 60  WHERE `id` = 3;  -- nota 6
UPDATE `produk` SET `stok_qty` = `stok_qty` - 60  WHERE `id` = 1;  -- nota 7

-- ============================================================
-- STEP 15: Hutang Piutang (dari nota hutang)
-- ============================================================
INSERT INTO `hutang_piutang` (`id_gudang`, `jenis`, `id_supplier`, `id_pembeli`, `id_nota`, `nominal`, `nominal_bayar`, `jatuh_tempo`, `status`, `created_by`) VALUES
(1, 'piutang', NULL, 2, 2, 2940000, 0,       DATE_ADD(CURDATE(), INTERVAL 23 DAY), 'open',     2),
(1, 'piutang', NULL, 3, 3, 9000000, 3000000, DATE_ADD(CURDATE(), INTERVAL 25 DAY), 'sebagian', 2),
(1, 'piutang', NULL, 2, 6, 4000000, 0,       DATE_ADD(CURDATE(), INTERVAL 28 DAY), 'open',     2),
(1, 'hutang',  1,    NULL, NULL, 5000000, 2000000, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'sebagian', 2),
(1, 'hutang',  2,    NULL, NULL, 3000000, 0,       DATE_ADD(CURDATE(), INTERVAL 20 DAY), 'open',     2),
(1, 'hutang',  3,    NULL, NULL, 8000000, 5000000, DATE_ADD(CURDATE(), INTERVAL 5  DAY), 'sebagian', 2);

-- ============================================================
-- STEP 16: Biaya Operasional
-- ============================================================
INSERT INTO `biaya_operasional` (`id_gudang`, `kategori`, `deskripsi`, `nominal`, `tanggal`, `created_by`) VALUES
(1, 'Gaji',      'Gaji karyawan bulan ini',    5000000, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 2),
(1, 'Listrik',   'Tagihan listrik gudang',     1500000, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 2),
(1, 'Transport', 'Ongkos kirim ke pembeli',    800000,  DATE_SUB(CURDATE(), INTERVAL 2 DAY), 2),
(1, 'Lainnya',   'Perlengkapan gudang',        350000,  DATE_SUB(CURDATE(), INTERVAL 1 DAY), 2);

-- ============================================================
-- STEP 17: Hutang Piutang History (payment records)
-- ============================================================
INSERT INTO `hutang_piutang_history` (`id_hutang_piutang`, `nominal_bayar`, `metode_bayar`, `keterangan`, `created_by`, `created_at`) VALUES
(1, 0,        NULL,       'Belum ada pembayaran',                       2, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(2, 3000000,  'transfer', 'Pembayaran sebagian dari pembeli 3',         2, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(4, 2000000,  'cash',     'Bayar sebagian ke supplier 1',               2, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(6, 5000000,  'transfer', 'Pembayaran sebagian untuk supplier 3',       2, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- ============================================================
-- STEP 18: Titipan (Consignments)
-- ============================================================
INSERT INTO `titipan` (`id_gudang`, `id_pengirim`, `no_titipan`, `tanggal_masuk`, `qty_total`, `qty_dijual`, `qty_tersisa`, `nominal_total`, `nominal_terjual`, `komisi_persen`, `komisi_tipe`, `status`, `catatan`, `created_by`, `created_at`) VALUES
(1, 1, 'TTP-20260501-0001', DATE_SUB(CURDATE(), INTERVAL 10 DAY), 100, 30, 70, 100 * 55000, 30 * 70000, 5.00, 'potong', 'dijual_sebagian', 'Titipan dari Supplier Laut Jaya', 2, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(1, 2, 'TTP-20260505-0001', DATE_SUB(CURDATE(), INTERVAL 5 DAY),  50,  0,  50,  50 * 30000, 0,            7.00, 'potong', 'masuk',           'Titipan Nila dari Supplier Ikan Nusa', 2, DATE_SUB(NOW(), INTERVAL 5 DAY));

-- ============================================================
-- STEP 19: Titipan Penjualan (Consignment Sales)
-- ============================================================
INSERT INTO `titipan_penjualan` (`id_titipan`, `id_penjual`, `id_pembeli`, `qty`, `harga_jual`, `nominal`, `komisi_nominal`, `tanggal_jual`, `status`, `created_by`, `created_at`) VALUES
(1, NULL, 1, 30, 70000, 30 * 70000, FLOOR((30 * 70000) * 5.00 / 100), DATE_SUB(CURDATE(), INTERVAL 8 DAY), 'terjual', 2, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(2, NULL, 5, 10, 42000, 10 * 42000, FLOOR((10 * 42000) * 7.00 / 100), DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'pending',  2, DATE_SUB(NOW(), INTERVAL 3 DAY));

-- ============================================================
-- STEP 20: Retur (Returns)
-- ============================================================
INSERT INTO `retur` (`id_gudang`, `id_produk`, `id_supplier`, `id_pembeli`, `id_nota`, `tipe`, `qty`, `nominal`, `alasan`, `status`, `catatan`, `created_by`, `approved_by`, `approved_at`, `created_at`) VALUES
(1, 1, NULL, NULL, NULL, 'stok', 5, 5 * 55000, 'Ikan rusak saat perjalanan', 'approved', 'Retur diterima, dikembalikan ke supplier', 2, 3, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, NULL, 1, NULL, NULL, 'stok', 10, 10 * 55000, 'Quality issue on batch',       'pending',  'Menunggu verifikasi quality',           2, NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- ============================================================
-- STEP 21: Notifikasi (Notifications)
-- ============================================================
INSERT INTO `notifikasi` (`id_user`, `tipe`, `judul`, `pesan`, `reference_id`, `reference_tipe`, `is_read`, `created_at`) VALUES
(2, 'info',    'Stok rendah', 'Stok Kakap Merah mencapai ambang minimum', 1, 'produk', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'reminder','Hutang jatuh tempo', 'Hutang ke supplier 3 akan jatuh tempo dalam 5 hari', 6, 'hutang_piutang', 0, NOW());

-- ============================================================
-- STEP 22: Stok Opname (Inventory Count)
-- ============================================================
INSERT INTO `stok_opname` (`id_gudang`, `tanggal_opname`, `status`, `created_by`, `created_at`) VALUES
(1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'final', 2, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, CURDATE(), 'draft', 3, NOW());
