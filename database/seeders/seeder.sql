-- ============================================================
-- PEACE SEAFOOD — BRAND NEW PREMIUM SEEDER DATA
-- Focuses on: Ikan Cakalang, Ikan Tongkol, Ikan Salem, Ikan Bandeng
-- ============================================================

USE `peace_seafood`;

SET FOREIGN_KEY_CHECKS = 0;

-- Truncate all tables in proper order
TRUNCATE TABLE `notifikasi`;
TRUNCATE TABLE `hutang_piutang_history`;
TRUNCATE TABLE `hutang_piutang`;
TRUNCATE TABLE `biaya_operasional`;
TRUNCATE TABLE `retur`;
TRUNCATE TABLE `titipan_penjualan`;
TRUNCATE TABLE `titipan`;
TRUNCATE TABLE `nota_detail`;
TRUNCATE TABLE `nota`;
TRUNCATE TABLE `timbangan`;
TRUNCATE TABLE `stok_masuk`;
TRUNCATE TABLE `harga_history`;
TRUNCATE TABLE `produk`;
TRUNCATE TABLE `jenis_ikan`;
TRUNCATE TABLE `pembeli`;
TRUNCATE TABLE `supplier`;
TRUNCATE TABLE `gudang`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `settings`;
TRUNCATE TABLE `stok_opname`;

-- ============================================================
-- 1. USERS
-- ============================================================
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `id_gudang`, `is_active`) VALUES
(1, 'Super Admin', 'superadmin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', NULL, 1),
(2, 'Bos Gudang', 'bos@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bos', NULL, 1),
(3, 'Admin Gudang A', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, 1),
(4, 'Checker Gudang A', 'checker@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'checker', 1, 1),
(5, 'Admin Gudang B', 'admin2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 2, 1);
-- password for all: password

-- ============================================================
-- 2. GUDANG (Warehouse)
-- ============================================================
INSERT INTO `gudang` (`id`, `id_bos`, `nama`, `alamat`, `kota`, `telpon`, `is_active`) VALUES
(1, 2, 'Gudang Utama Bitung', 'Jl. Pelabuhan Samudera No. 42', 'Bitung', '0438-334455', 1),
(2, 2, 'Gudang Cabang Jakarta', 'Jl. Muara Baru Ujung No. 12', 'Jakarta Utara', '021-667788', 1);

-- Apply foreign keys from users back to gudang
UPDATE `users` SET `id_gudang` = 1 WHERE `id` = 3;
UPDATE `users` SET `id_gudang` = 1 WHERE `id` = 4;
UPDATE `users` SET `id_gudang` = 2 WHERE `id` = 5;

-- ============================================================
-- 3. JENIS_IKAN (Fish Type with Attribute Masters)
-- ============================================================
INSERT INTO `jenis_ikan` (`id`, `nama`, `deskripsi`, `allowed_sizes`, `allowed_grades`, `allowed_origins`, `is_active`) VALUES
(1, 'Ikan Cakalang', 'Skipjack Tuna - komoditas utama ekspor & lokal', '200/300, 300/500, 1 Up, Size 10, Size 20, Polos', 'Grade A - Beku Kapal, Grade B - Beku Darat, Grade C - AC', 'Bitung, Banda, Makassar, Ambon', 1),
(2, 'Ikan Tongkol', 'Mackerel Tuna - sangat diminati pasar retail lokal', '200/300, 300/500, 500/800, Size 15, Size 25', 'Grade A - Beku Kapal, Grade B - Beku Darat, Grade C - AC', 'Bitung, Kendari, Sibolga, Bali', 1),
(3, 'Ikan Salem', 'Mackerel - produk impor premium untuk sarden/konsumsi', '100/150, 150/200, 200/300, Size 30, Size 40', 'Grade A - Beku Kapal, Grade B - Beku Darat, Grade C - AC', 'Jepang, Tiongkok, Cile', 1),
(4, 'Ikan Bandeng', 'Milkfish - ikan air payau budidaya unggulan', '2-3 pcs/kg, 4-5 pcs/kg, 6-8 pcs/kg, Jumbo', 'Grade A - Beku Kapal, Grade B - Beku Darat, Grade C - AC', 'Sidoarjo, Juwana, Gresik, Pinrang', 1);

-- ============================================================
-- 4. SUPPLIER
-- ============================================================
INSERT INTO `supplier` (`id`, `id_gudang`, `nama`, `nama_pemilik`, `kontak_person`, `telpon`, `alamat`, `kota`, `bank_name`, `bank_account`, `bank_owner`, `is_active`) VALUES
(1, 1, 'PT Bitung Samudra Sejahtera', 'Hendra Wijaya', 'Budi', '0812-4455-6677', 'Kawasan Industri Pelabuhan Bitung', 'Bitung', 'BCA', '1234567890', 'PT BITUNG SAMUDRA', 1),
(2, 1, 'CV Banda Fishery', 'Syarifuddin', 'Lutfi', '0813-8899-0011', 'Jl. Dermaga Pantai Indah No. 5', 'Banda', 'Mandiri', '0987654321', 'Syarifuddin Banda', 1),
(3, 1, 'PT Juwana Tambak Makmur', 'Bambang Sukijo', 'Rudi', '0857-2233-4455', 'Jl. Tambak Juwana KM 2', 'Pati', 'BRI', '111222333444', 'PT JUWANA TAMBAK', 1),
(4, 2, 'PT Muara Seafood Import', 'Tan Wijaya', 'Alvin', '0811-9988-7766', 'Kawasan Muara Baru No. 100', 'Jakarta', 'BCA', '5554443332', 'PT MUARA IMPORT', 1);

-- ============================================================
-- 5. PEMBELI (Buyer)
-- ============================================================
INSERT INTO `pembeli` (`id`, `id_gudang`, `nama`, `telpon`, `alamat`, `kota`, `tipe`, `kredit_limit`, `is_active`) VALUES
(1, 1, 'PT Restoran Ocean Star', '021-554433', 'Jl. Boulevard Kelapa Gading No. 1', 'Jakarta', 'bulk', 150000000, 1),
(2, 1, 'CV Berkah Seafood Pasar Senen', '0821-2233-44', 'Pasar Senen Blok A No. 12', 'Jakarta', 'retail', 30000000, 1),
(3, 1, 'Catering Mandiri Utama', '0821-8899-22', 'Jl. Kebon Jeruk No. 50', 'Jakarta', 'reseller', 50000000, 1),
(4, 1, 'PT Bandeng Juwana Cabang Depok', '021-778899', 'Jl. Margonda Raya No. 10', 'Depok', 'bulk', 75000000, 1),
(5, 2, 'Distributor Ikan Jakarta Barat', '0815-5566-77', 'Kawasan Pasar Ikan Cengkareng', 'Jakarta', 'bulk', 200000000, 1);

-- ============================================================
-- 6. PRODUK (with dynamic Size, Grade, Origin)
-- ============================================================
INSERT INTO `produk` (`id`, `id_jenis_ikan`, `id_gudang`, `nama`, `deskripsi`, `satuan`, `size`, `grade`, `asal`, `harga_beli`, `harga_jual`, `stok_qty`, `nilai_stok`, `stok_minimum`, `is_active`) VALUES
-- Gudang 1
(1, 1, 1, 'Cakalang 300/500 A Bitung', 'Cakalang Premium Beku Kapal', 'kg', '300/500', 'Grade A - Beku Kapal', 'Bitung', 32000, 45000, 4500.00, 144000000, 100.00, 1),
(2, 1, 1, 'Cakalang 1 Up B Banda', 'Cakalang Beku Darat Banda', 'kg', '1 Up', 'Grade B - Beku Darat', 'Banda', 28000, 38000, 3000.00, 84000000, 100.00, 1),
(3, 1, 1, 'Cakalang Size 10 C Makassar', 'Cakalang AC Makassar', 'kg', 'Size 10', 'Grade C - AC', 'Makassar', 20000, 28000, 0.00, 0, 50.00, 1),
(4, 2, 1, 'Tongkol 300/500 A Kendari', 'Tongkol Beku Kapal Kendari', 'kg', '300/500', 'Grade A - Beku Kapal', 'Kendari', 25000, 35000, 4000.00, 100000000, 100.00, 1),
(5, 2, 1, 'Tongkol 500/800 B Sibolga', 'Tongkol Beku Darat Sibolga', 'kg', '500/800', 'Grade B - Beku Darat', 'Sibolga', 22000, 30000, 0.00, 0, 100.00, 1),
(6, 3, 1, 'Salem 150/200 A Jepang', 'Salem Impor Premium Beku Kapal', 'kg', '150/200', 'Grade A - Beku Kapal', 'Jepang', 30000, 42000, 5000.00, 150000000, 200.00, 1),
(7, 3, 1, 'Salem 200/300 B Tiongkok', 'Salem Impor Beku Darat Tiongkok', 'kg', '200/300', 'Grade B - Beku Darat', 'Tiongkok', 26000, 36000, 0.00, 0, 100.00, 1),
(8, 4, 1, 'Bandeng 4-5 pcs A Sidoarjo', 'Bandeng Sidoarjo Premium', 'kg', '4-5 pcs/kg', 'Grade A - Beku Kapal', 'Sidoarjo', 24000, 33000, 2500.00, 60000000, 100.00, 1),
(9, 4, 1, 'Bandeng Jumbo B Juwana', 'Bandeng Cabut Duri Juwana', 'kg', 'Jumbo', 'Grade B - Beku Darat', 'Juwana', 28000, 39000, 0.00, 0, 50.00, 1),
-- Gudang 2
(10, 1, 2, 'Cakalang 300/500 A Bitung', 'Cakalang Premium Beku Kapal', 'kg', '300/500', 'Grade A - Beku Kapal', 'Bitung', 32000, 45000, 500.00, 16000000, 50.00, 1),
(11, 3, 2, 'Salem 150/200 A Jepang', 'Salem Impor Jepang', 'kg', '150/200', 'Grade A - Beku Kapal', 'Jepang', 30000, 42000, 400.00, 12000000, 50.00, 1);

-- ============================================================
-- 7. SETTINGS
-- ============================================================
INSERT INTO `settings` (`id_gudang`, `kunci`, `nilai`, `deskripsi`) VALUES
-- Gudang 1
(1, 'multi_warehouse_aktif', '1', 'Multi warehouse feature aktif/nonaktif'),
(1, 'stok_minimum_threshold', '100', 'Default batas stok minimum dalam kg'),
(1, 'susut_alert_threshold', '5', 'Peringatan jika susut timbangan melebihi persen ini'),
(1, 'komisi_penitipan_tipe', 'potong', 'Metode komisi: potong atau bayar_terpisah'),
(1, 'komisi_penitipan_persen', '5', 'Persentase komisi default'),
(1, 'pajak_default_persen', '0', 'Pajak penjualan default dalam %'),
(1, 'jatuh_tempo_default_hari', '30', 'Jatuh tempo default untuk pembayaran piutang'),
(1, 'session_timeout_menit', '60', 'Sesi login aktif dalam menit'),
(1, 'onboarding_wizard_aktif', '0', 'Tampilkan wizard panduan user'),
(1, 'backup_otomatis', '1', 'Pencadangan database otomatis'),
(1, 'harga_locked_untuk', 'bos', 'Otoritas ubah harga produk'),
(1, 'export_permission', 'admin', 'Otoritas export file laporan'),
(1, 'company_name', 'Peace Seafood', 'Nama Identitas Gudang/Perusahaan Global'),
(1, 'company_logo_initial', 'PS', 'Inisial Logo Sidebar Utama'),
(1, 'kapasitas_cold_storage_kg', '10000', 'Kapasitas maksimal Cold Storage (kg) untuk indikator gauge'),
-- Gudang 2
(2, 'multi_warehouse_aktif', '1', 'Multi warehouse'),
(2, 'stok_minimum_threshold', '100', 'Stok minimum'),
(2, 'komisi_penitipan_tipe', 'potong', 'Metode komisi'),
(2, 'pajak_default_persen', '0', 'Pajak default'),
(2, 'jatuh_tempo_default_hari', '30', 'Jatuh tempo default'),
(2, 'harga_locked_untuk', 'bos', 'Otoritas ubah harga'),
(2, 'export_permission', 'admin', 'Otoritas export'),
(2, 'kapasitas_cold_storage_kg', '5000', 'Kapasitas maksimal Cold Storage (kg) Cabang');

-- ============================================================
-- 8. STOK_MASUK
-- ============================================================
-- Seeding data data pasokan baru masuk minggu ini (sebagian kecil dari total kapasitas)
INSERT INTO `stok_masuk` (`id`, `id_gudang`, `id_produk`, `id_supplier`, `qty`, `harga_beli`, `status`, `catatan`, `created_by`, `created_at`) VALUES
(1, 1, 1, 1, 500.00, 32000, 'confirmed', 'Pasokan Cakalang Bitung Kapal', 3, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(2, 1, 2, 2, 400.00,  28000, 'confirmed', 'Pasokan Cakalang Banda Darat', 3, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(3, 1, 4, 1, 300.00,  25000, 'confirmed', 'Pasokan Tongkol Kendari Kapal', 3, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(4, 1, 6, 4, 600.00,  30000, 'confirmed', 'Kontainer Salem Jepang Impor', 3, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(5, 1, 8, 3, 400.00,  24000, 'confirmed', 'Panen Tambak Bandeng Sidoarjo', 3, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(6, 1, 1, 1, 500.00,  32000, 'pending',   'Menunggu timbangan fisik masuk', 3, NOW());

-- ============================================================
-- 9. TIMBANGAN (Susut timbangan sesungguhnya)
-- ============================================================
INSERT INTO `timbangan` (`id`, `id_stok_masuk`, `id_produk`, `qty_teoritis`, `qty_actual`, `alasan_susut`, `created_by`, `created_at`) VALUES
(1, 1, 1, 500.00, 500.00, NULL, 4, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(2, 2, 2, 400.00, 400.00,  NULL, 4, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(3, 3, 4, 300.00, 300.00, NULL, 4, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(4, 4, 6, 600.00, 600.00, NULL, 4, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(5, 5, 8, 400.00, 400.00,  NULL, 4, DATE_SUB(NOW(), INTERVAL 2 DAY));

-- ============================================================
-- 10. HARGA_HISTORY
-- ============================================================
INSERT INTO `harga_history` (`id_produk`, `harga_lama`, `harga_baru`, `tipe`, `reason`, `changed_by`, `created_at`) VALUES
(1, NULL,  32000, 'beli', 'Setup awal harga beli', 1, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(1, NULL,  45000, 'jual', 'Setup awal harga jual', 1, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(2, NULL,  28000, 'beli', 'Setup awal harga beli', 1, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(2, NULL,  38000, 'jual', 'Setup awal harga jual', 1, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(4, NULL,  25000, 'beli', 'Setup awal harga beli', 1, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(4, NULL,  35000, 'jual', 'Setup awal harga jual', 1, DATE_SUB(NOW(), INTERVAL 30 DAY));

-- ============================================================
-- 11. NOTA PENJUALAN (Historical Sales)
-- ============================================================
INSERT INTO `nota` (`id`, `id_gudang`, `id_pembeli`, `no_nota`, `tanggal_nota`, `subtotal`, `diskon_nominal`, `pajak`, `total`, `pembayaran`, `status`, `catatan`, `created_by`, `created_at`) VALUES
(1, 1, 1, 'PS-260519-0001', DATE_SUB(CURDATE(), INTERVAL 6 DAY), 27000000, 0, 0, 27000000, 'cash',   'final', 'Lunas tunai di tempat', 3, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(2, 1, 2, 'PS-260520-0001', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 19000000, 1000000, 0, 18000000, 'hutang', 'final', 'Term 30 hari pasar senen', 3, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 1, 3, 'PS-260521-0001', DATE_SUB(CURDATE(), INTERVAL 4 DAY), 28000000, 0, 0, 28000000, 'hutang', 'final', 'Kredit langganan catering', 3, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(4, 1, 1, 'PS-260522-0001', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 50400000, 1400000, 0, 49000000, 'cash', 'final', 'Partai besar restoran', 3, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(5, 1, 4, 'PS-260523-0001', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 19800000, 0, 0, 19800000, 'cash', 'final', 'Lunas Bandeng Juwana', 3, DATE_SUB(NOW(), INTERVAL 2 DAY));

-- ============================================================
-- 12. NOTA_DETAIL
-- ============================================================
INSERT INTO `nota_detail` (`id_nota`, `id_produk`, `qty`, `harga_jual`, `subtotal`) VALUES
(1, 1, 600.00, 45000, 27000000), -- 600 kg Cakalang 300/500 A
(2, 2, 500.00, 38000, 19000000), -- 500 kg Cakalang 1 Up B
(3, 4, 800.00, 35000, 28000000), -- 800 kg Tongkol Kendari A
(4, 6, 1200.00, 42000, 50400000), -- 1200 kg Salem Jepang A
(5, 8, 600.00, 33000, 19800000); -- 600 kg Bandeng Sidoarjo A

-- Update stok setelah dikurangi penjualan historis
UPDATE `produk` SET `stok_qty` = 3900.00, `nilai_stok` = 3900.00 * 32000 WHERE `id` = 1;
UPDATE `produk` SET `stok_qty` = 2500.00, `nilai_stok` = 2500.00 * 28000 WHERE `id` = 2;
UPDATE `produk` SET `stok_qty` = 3200.00, `nilai_stok` = 3200.00 * 25000 WHERE `id` = 4;
UPDATE `produk` SET `stok_qty` = 3800.00, `nilai_stok` = 3800.00 * 30000 WHERE `id` = 6;
UPDATE `produk` SET `stok_qty` = 1900.00, `nilai_stok` = 1900.00 * 24000 WHERE `id` = 8;

-- ============================================================
-- 13. HUTANG PIUTANG
-- ============================================================
INSERT INTO `hutang_piutang` (`id_gudang`, `jenis`, `id_supplier`, `id_pembeli`, `id_nota`, `no_referensi`, `nominal`, `nominal_bayar`, `jatuh_tempo`, `status`, `created_by`, `created_at`) VALUES
-- Piutang Penjualan
(1, 'piutang', NULL, 2, 2, 'PS-260520-0001', 18000000, 0, DATE_ADD(CURDATE(), INTERVAL 25 DAY), 'open', 3, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 'piutang', NULL, 3, 3, 'PS-260521-0001', 28000000, 8000000, DATE_ADD(CURDATE(), INTERVAL 26 DAY), 'sebagian', 3, DATE_SUB(NOW(), INTERVAL 4 DAY)),
-- Hutang Supplier
(1, 'hutang', 1, NULL, NULL, 'INV-BSS-9912', 23500000, 10000000, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'sebagian', 3, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(1, 'hutang', 2, NULL, NULL, 'INV-BANDA-881', 11200000, 0, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'open', 3, DATE_SUB(NOW(), INTERVAL 6 DAY));

-- ============================================================
-- 14. BIAYA_OPERASIONAL
-- ============================================================
INSERT INTO `biaya_operasional` (`id_gudang`, `kategori`, `deskripsi`, `nominal`, `tanggal`, `created_by`) VALUES
(1, 'Gaji', 'Gaji bulanan checker & administrasi', 6500000, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 3),
(1, 'Listrik', 'Token listrik cold storage utama', 2400000, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 3),
(1, 'Operasional', 'Es balok untuk pengiriman retail', 750000, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 3);

SET FOREIGN_KEY_CHECKS = 1;
