-- Peace Seafood Database Backup
-- Generated on 2026-05-25 20:30:45

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `before_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`before_value`)),
  `after_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`after_value`)),
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_activity_user` (`id_user`),
  CONSTRAINT `fk_activity_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activity_log` (`id`, `id_user`, `action`, `table_name`, `record_id`, `before_value`, `after_value`, `timestamp`) VALUES ('1', '3', 'INSERT', 'stok_masuk', '16', NULL, '{\"id_gudang\":1,\"id_supplier\":\"3\",\"id_produk\":\"2\",\"qty\":1000,\"harga_beli\":75000,\"status\":\"pending\"}', '2026-05-23 20:35:42');
INSERT INTO `activity_log` (`id`, `id_user`, `action`, `table_name`, `record_id`, `before_value`, `after_value`, `timestamp`) VALUES ('2', '1', 'INSERT', 'stok_masuk', '1', NULL, '{\"id_gudang\":1,\"id_supplier\":3,\"id_produk\":\"2\",\"qty\":2000,\"harga_beli\":22000,\"status\":\"pending\"}', '2026-05-25 23:46:24');
INSERT INTO `activity_log` (`id`, `id_user`, `action`, `table_name`, `record_id`, `before_value`, `after_value`, `timestamp`) VALUES ('3', '1', 'INSERT', 'timbangan', '1', NULL, '{\"id_stok_masuk\":1,\"qty_teoritis\":2000,\"qty_actual\":2000}', '2026-05-25 23:50:46');
INSERT INTO `activity_log` (`id`, `id_user`, `action`, `table_name`, `record_id`, `before_value`, `after_value`, `timestamp`) VALUES ('4', '1', 'UPDATE', 'stok_masuk', '1', '{\"id\":1,\"id_gudang\":1,\"id_produk\":2,\"id_supplier\":3,\"qty\":\"2000.00\",\"harga_beli\":22000,\"total\":44000000,\"status\":\"pending\",\"catatan\":\"\",\"created_by\":1,\"created_at\":\"2026-05-25 23:46:24\",\"updated_at\":\"2026-05-25 23:46:24\"}', '{\"id\":1,\"status\":\"confirmed\"}', '2026-05-25 23:50:46');
INSERT INTO `activity_log` (`id`, `id_user`, `action`, `table_name`, `record_id`, `before_value`, `after_value`, `timestamp`) VALUES ('5', '1', 'INSERT', 'stok_masuk', '2', NULL, '{\"id_gudang\":1,\"id_supplier\":2,\"id_produk\":\"4\",\"qty\":1000,\"harga_beli\":24000,\"status\":\"pending\"}', '2026-05-25 23:51:08');
INSERT INTO `activity_log` (`id`, `id_user`, `action`, `table_name`, `record_id`, `before_value`, `after_value`, `timestamp`) VALUES ('6', '1', 'INSERT', 'stok_masuk', '3', NULL, '{\"id_gudang\":1,\"id_supplier\":1,\"id_produk\":\"4\",\"qty\":4000,\"harga_beli\":24000,\"status\":\"pending\"}', '2026-05-25 23:51:39');
INSERT INTO `activity_log` (`id`, `id_user`, `action`, `table_name`, `record_id`, `before_value`, `after_value`, `timestamp`) VALUES ('7', '1', 'INSERT', 'timbangan', '2', NULL, '{\"id_stok_masuk\":3,\"qty_teoritis\":4000,\"qty_actual\":4000}', '2026-05-25 23:51:53');
INSERT INTO `activity_log` (`id`, `id_user`, `action`, `table_name`, `record_id`, `before_value`, `after_value`, `timestamp`) VALUES ('8', '1', 'UPDATE', 'stok_masuk', '3', '{\"id\":3,\"id_gudang\":1,\"id_produk\":4,\"id_supplier\":1,\"qty\":\"4000.00\",\"harga_beli\":24000,\"total\":96000000,\"status\":\"pending\",\"catatan\":\"\",\"created_by\":1,\"created_at\":\"2026-05-25 23:51:39\",\"updated_at\":\"2026-05-25 23:51:39\"}', '{\"id\":3,\"status\":\"confirmed\"}', '2026-05-25 23:51:53');
INSERT INTO `activity_log` (`id`, `id_user`, `action`, `table_name`, `record_id`, `before_value`, `after_value`, `timestamp`) VALUES ('9', '1', 'INSERT', 'timbangan', '3', NULL, '{\"id_stok_masuk\":2,\"qty_teoritis\":1000,\"qty_actual\":1000}', '2026-05-25 23:52:01');
INSERT INTO `activity_log` (`id`, `id_user`, `action`, `table_name`, `record_id`, `before_value`, `after_value`, `timestamp`) VALUES ('10', '1', 'UPDATE', 'stok_masuk', '2', '{\"id\":2,\"id_gudang\":1,\"id_produk\":4,\"id_supplier\":2,\"qty\":\"1000.00\",\"harga_beli\":24000,\"total\":24000000,\"status\":\"pending\",\"catatan\":\"\",\"created_by\":1,\"created_at\":\"2026-05-25 23:51:08\",\"updated_at\":\"2026-05-25 23:51:08\"}', '{\"id\":2,\"status\":\"confirmed\"}', '2026-05-25 23:52:01');

DROP TABLE IF EXISTS `bank_account`;
CREATE TABLE `bank_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_user` (`id_user`),
  CONSTRAINT `fk_bank_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `bank_account` (`id`, `id_user`, `bank_name`, `account_number`, `account_name`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '1', 'BCA', '108-123-8015', 'Muhammad Fajri Bustomi', '1', '2026-05-25 23:48:51', '2026-05-25 23:48:51');

DROP TABLE IF EXISTS `biaya_operasional`;
CREATE TABLE `biaya_operasional` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gudang` int(11) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `nominal` bigint(20) NOT NULL,
  `tanggal` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_gudang` (`id_gudang`),
  KEY `idx_kategori` (`kategori`),
  KEY `idx_tanggal` (`tanggal`),
  KEY `fk_biaya_created_by` (`created_by`),
  CONSTRAINT `fk_biaya_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_biaya_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `gudang`;
CREATE TABLE `gudang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_bos` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `kota` varchar(50) NOT NULL,
  `telpon` varchar(20) DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_bos` (`id_bos`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `fk_gudang_bos` FOREIGN KEY (`id_bos`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gudang` (`id`, `id_bos`, `nama`, `alamat`, `kota`, `telpon`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '2', 'Gudang Utama Bitung', 'Jl. Pelabuhan Samudera No. 42', 'Bitung', '0438-334455', '1', '2026-05-26 00:58:30', '2026-05-26 00:58:30');
INSERT INTO `gudang` (`id`, `id_bos`, `nama`, `alamat`, `kota`, `telpon`, `is_active`, `created_at`, `updated_at`) VALUES ('2', '2', 'Gudang Cabang Jakarta', 'Jl. Muara Baru Ujung No. 12', 'Jakarta Utara', '021-667788', '0', '2026-05-26 00:58:30', '2026-05-26 01:20:19');
INSERT INTO `gudang` (`id`, `id_bos`, `nama`, `alamat`, `kota`, `telpon`, `is_active`, `created_at`, `updated_at`) VALUES ('3', '1', 'AutoTest Gudang', 'Alamat Test', 'Test City', '000-000-000', '1', '2026-05-26 01:27:54', '2026-05-26 01:27:54');

DROP TABLE IF EXISTS `harga_history`;
CREATE TABLE `harga_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produk` int(11) NOT NULL,
  `harga_lama` bigint(20) DEFAULT NULL,
  `harga_baru` bigint(20) NOT NULL,
  `tipe` enum('beli','jual') NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `changed_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_produk` (`id_produk`),
  KEY `idx_tipe` (`tipe`),
  KEY `idx_created_at` (`created_at`),
  KEY `fk_harga_changed_by` (`changed_by`),
  CONSTRAINT `fk_harga_changed_by` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_harga_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `hutang_piutang`;
CREATE TABLE `hutang_piutang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gudang` int(11) NOT NULL,
  `jenis` enum('hutang','piutang') NOT NULL,
  `id_supplier` int(11) DEFAULT NULL,
  `id_pembeli` int(11) DEFAULT NULL,
  `id_nota` int(11) DEFAULT NULL,
  `no_referensi` varchar(50) DEFAULT NULL,
  `nominal` bigint(20) NOT NULL,
  `nominal_bayar` bigint(20) NOT NULL DEFAULT 0,
  `sisa_hutang` bigint(20) GENERATED ALWAYS AS (`nominal` - `nominal_bayar`) STORED,
  `jatuh_tempo` date NOT NULL,
  `status` enum('open','sebagian','lunas') NOT NULL DEFAULT 'open',
  `catatan` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_gudang` (`id_gudang`),
  KEY `idx_jenis` (`jenis`),
  KEY `idx_id_supplier` (`id_supplier`),
  KEY `idx_id_pembeli` (`id_pembeli`),
  KEY `idx_status` (`status`),
  KEY `idx_jatuh_tempo` (`jatuh_tempo`),
  KEY `fk_hp_nota` (`id_nota`),
  KEY `fk_hp_created_by` (`created_by`),
  CONSTRAINT `fk_hp_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_hp_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`),
  CONSTRAINT `fk_hp_nota` FOREIGN KEY (`id_nota`) REFERENCES `nota` (`id`),
  CONSTRAINT `fk_hp_pembeli` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id`),
  CONSTRAINT `fk_hp_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `hutang_piutang_history`;
CREATE TABLE `hutang_piutang_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_hutang_piutang` int(11) NOT NULL,
  `nominal_bayar` bigint(20) NOT NULL,
  `metode_bayar` varchar(50) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_hutang_piutang` (`id_hutang_piutang`),
  KEY `idx_created_at` (`created_at`),
  KEY `fk_hph_created_by` (`created_by`),
  CONSTRAINT `fk_hph_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_hph_hutang` FOREIGN KEY (`id_hutang_piutang`) REFERENCES `hutang_piutang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `jenis_ikan`;
CREATE TABLE `jenis_ikan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `allowed_sizes` text DEFAULT NULL,
  `allowed_grades` text DEFAULT NULL,
  `allowed_origins` text DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nama` (`nama`),
  KEY `idx_nama` (`nama`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `jenis_ikan` (`id`, `nama`, `deskripsi`, `allowed_sizes`, `allowed_grades`, `allowed_origins`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'AutoTest Ikan', 'Dibuat oleh tes otomatis', '', '', '', '0', '2026-05-26 01:23:52', '2026-05-26 01:30:20');

DROP TABLE IF EXISTS `nota`;
CREATE TABLE `nota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gudang` int(11) NOT NULL,
  `id_pembeli` int(11) NOT NULL,
  `no_nota` varchar(20) NOT NULL,
  `tanggal_nota` date NOT NULL,
  `subtotal` bigint(20) NOT NULL DEFAULT 0,
  `diskon_nominal` bigint(20) NOT NULL DEFAULT 0,
  `pajak` bigint(20) NOT NULL DEFAULT 0,
  `total` bigint(20) NOT NULL DEFAULT 0,
  `pembayaran` enum('cash','hutang') NOT NULL,
  `bank_account_id` int(11) DEFAULT NULL,
  `status` enum('draft','final','cancel') NOT NULL DEFAULT 'draft',
  `catatan` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_no_nota` (`no_nota`),
  KEY `idx_id_gudang` (`id_gudang`),
  KEY `idx_id_pembeli` (`id_pembeli`),
  KEY `idx_status` (`status`),
  KEY `idx_tanggal_nota` (`tanggal_nota`),
  KEY `idx_pembayaran` (`pembayaran`),
  KEY `fk_nota_created_by` (`created_by`),
  KEY `idx_bank_account_id` (`bank_account_id`),
  CONSTRAINT `fk_nota_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_nota_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`),
  CONSTRAINT `fk_nota_pembeli` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `nota` (`id`, `id_gudang`, `id_pembeli`, `no_nota`, `tanggal_nota`, `subtotal`, `diskon_nominal`, `pajak`, `total`, `pembayaran`, `bank_account_id`, `status`, `catatan`, `created_by`, `created_at`, `updated_at`) VALUES ('1', '1', '1', 'PS-260525-0001', '2026-05-25', '15000', '0', '0', '15000', 'cash', NULL, 'final', NULL, '1', '2026-05-26 01:29:31', '2026-05-26 01:29:31');

DROP TABLE IF EXISTS `nota_detail`;
CREATE TABLE `nota_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_nota` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `harga_jual` bigint(20) NOT NULL,
  `subtotal` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_nota` (`id_nota`),
  KEY `idx_id_produk` (`id_produk`),
  CONSTRAINT `fk_detail_nota` FOREIGN KEY (`id_nota`) REFERENCES `nota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detail_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `nota_detail` (`id`, `id_nota`, `id_produk`, `qty`, `harga_jual`, `subtotal`, `created_at`) VALUES ('1', '1', '1', '1.00', '15000', '15000', '2026-05-26 01:29:31');

DROP TABLE IF EXISTS `notifikasi`;
CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `tipe` varchar(50) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_tipe` varchar(50) DEFAULT NULL,
  `is_read` tinyint(4) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_user` (`id_user`),
  KEY `idx_tipe` (`tipe`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `pembeli`;
CREATE TABLE `pembeli` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gudang` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `telpon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kota` varchar(50) DEFAULT NULL,
  `tipe` enum('retail','bulk','reseller') NOT NULL DEFAULT 'retail',
  `kredit_limit` bigint(20) NOT NULL DEFAULT 0,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_gudang` (`id_gudang`),
  KEY `idx_nama` (`nama`),
  CONSTRAINT `fk_pembeli_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pembeli` (`id`, `id_gudang`, `nama`, `telpon`, `alamat`, `kota`, `tipe`, `kredit_limit`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '1', 'Pembeli Umum', NULL, NULL, NULL, 'retail', '0', '1', '2026-05-26 01:29:31', '2026-05-26 01:29:31');

DROP TABLE IF EXISTS `produk`;
CREATE TABLE `produk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_jenis_ikan` int(11) NOT NULL,
  `id_gudang` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `satuan` varchar(20) NOT NULL DEFAULT 'kg',
  `size` varchar(50) DEFAULT NULL,
  `grade` varchar(50) DEFAULT NULL,
  `asal` varchar(100) DEFAULT NULL,
  `harga_beli` bigint(20) NOT NULL DEFAULT 0,
  `harga_jual` bigint(20) NOT NULL DEFAULT 0,
  `stok_qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `nilai_stok` bigint(20) NOT NULL DEFAULT 0,
  `stok_minimum` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_gudang` (`id_gudang`),
  KEY `idx_id_jenis_ikan` (`id_jenis_ikan`),
  KEY `idx_nama` (`nama`),
  CONSTRAINT `fk_produk_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`),
  CONSTRAINT `fk_produk_jenis` FOREIGN KEY (`id_jenis_ikan`) REFERENCES `jenis_ikan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `produk` (`id`, `id_jenis_ikan`, `id_gudang`, `nama`, `deskripsi`, `satuan`, `size`, `grade`, `asal`, `harga_beli`, `harga_jual`, `stok_qty`, `nilai_stok`, `stok_minimum`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '1', '1', 'AutoTest Produk', 'Produk test otomatis', 'kg', NULL, NULL, NULL, '10000', '15000', '8.00', '70000', '1.00', '1', '2026-05-26 01:26:12', '2026-05-26 01:30:03');

DROP TABLE IF EXISTS `retur`;
CREATE TABLE `retur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gudang` int(11) NOT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `id_supplier` int(11) DEFAULT NULL,
  `id_pembeli` int(11) DEFAULT NULL,
  `id_nota` int(11) DEFAULT NULL,
  `tipe` enum('stok','piutang') NOT NULL,
  `qty` decimal(10,2) DEFAULT NULL,
  `nominal` bigint(20) DEFAULT NULL,
  `alasan` varchar(255) NOT NULL,
  `foto_bukti` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','posted') NOT NULL DEFAULT 'pending',
  `catatan` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_gudang` (`id_gudang`),
  KEY `idx_tipe` (`tipe`),
  KEY `idx_status` (`status`),
  KEY `idx_id_produk` (`id_produk`),
  KEY `fk_retur_supplier` (`id_supplier`),
  KEY `fk_retur_pembeli` (`id_pembeli`),
  KEY `fk_retur_nota` (`id_nota`),
  KEY `fk_retur_created_by` (`created_by`),
  KEY `fk_retur_approved_by` (`approved_by`),
  CONSTRAINT `fk_retur_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_retur_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_retur_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`),
  CONSTRAINT `fk_retur_nota` FOREIGN KEY (`id_nota`) REFERENCES `nota` (`id`),
  CONSTRAINT `fk_retur_pembeli` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id`),
  CONSTRAINT `fk_retur_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`),
  CONSTRAINT `fk_retur_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `retur` (`id`, `id_gudang`, `id_produk`, `id_supplier`, `id_pembeli`, `id_nota`, `tipe`, `qty`, `nominal`, `alasan`, `foto_bukti`, `status`, `catatan`, `created_by`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES ('1', '1', '1', NULL, NULL, NULL, 'stok', '1.00', NULL, 'Retur test otomatis', NULL, 'pending', NULL, '1', NULL, NULL, '2026-05-26 01:30:03', '2026-05-26 01:30:03');

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gudang` int(11) DEFAULT NULL,
  `kunci` varchar(100) NOT NULL,
  `nilai` text NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting` (`id_gudang`,`kunci`),
  KEY `idx_kunci` (`kunci`),
  KEY `idx_id_gudang` (`id_gudang`),
  CONSTRAINT `fk_settings_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('1', '1', 'multi_warehouse_aktif', '1', 'Multi warehouse feature aktif/nonaktif', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('2', '1', 'stok_minimum_threshold', '100', 'Default batas stok minimum dalam kg', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('3', '1', 'susut_alert_threshold', '5', 'Peringatan jika susut timbangan melebihi persen ini', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('4', '1', 'komisi_penitipan_tipe', 'potong', 'Metode komisi: potong atau bayar_terpisah', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('5', '1', 'komisi_penitipan_persen', '5', 'Persentase komisi default', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('6', '1', 'pajak_default_persen', '0', 'Pajak penjualan default dalam %', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('7', '1', 'jatuh_tempo_default_hari', '30', 'Jatuh tempo default untuk pembayaran piutang', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('8', '1', 'session_timeout_menit', '60', 'Sesi login aktif dalam menit', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('9', '1', 'onboarding_wizard_aktif', '0', 'Tampilkan wizard panduan user', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('10', '1', 'backup_otomatis', '1', 'Pencadangan database otomatis', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('11', '1', 'harga_locked_untuk', 'bos', 'Otoritas ubah harga produk', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('12', '1', 'export_permission', 'admin', 'Otoritas export file laporan', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('13', '1', 'company_name', 'Peace Seafood', 'Nama Identitas Gudang/Perusahaan Global', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('14', '1', 'company_logo_initial', 'PS', 'Inisial Logo Sidebar Utama', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('15', '1', 'kapasitas_cold_storage_kg', '10000', 'Kapasitas maksimal Cold Storage (kg) untuk indikator gauge', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('16', '2', 'multi_warehouse_aktif', '1', 'Multi warehouse', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('17', '2', 'stok_minimum_threshold', '100', 'Stok minimum', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('18', '2', 'komisi_penitipan_tipe', 'potong', 'Metode komisi', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('19', '2', 'pajak_default_persen', '0', 'Pajak default', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('20', '2', 'jatuh_tempo_default_hari', '30', 'Jatuh tempo default', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('21', '2', 'harga_locked_untuk', 'bos', 'Otoritas ubah harga', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('22', '2', 'export_permission', 'admin', 'Otoritas export', '2026-05-26 00:58:30');
INSERT INTO `settings` (`id`, `id_gudang`, `kunci`, `nilai`, `deskripsi`, `updated_at`) VALUES ('23', '2', 'kapasitas_cold_storage_kg', '5000', 'Kapasitas maksimal Cold Storage (kg) Cabang', '2026-05-26 00:58:30');

DROP TABLE IF EXISTS `stok_masuk`;
CREATE TABLE `stok_masuk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gudang` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `id_supplier` int(11) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `harga_beli` bigint(20) NOT NULL,
  `total` bigint(20) GENERATED ALWAYS AS (`qty` * `harga_beli`) STORED,
  `status` enum('pending','confirmed','rejected') NOT NULL DEFAULT 'pending',
  `catatan` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_gudang` (`id_gudang`),
  KEY `idx_id_produk` (`id_produk`),
  KEY `idx_id_supplier` (`id_supplier`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `fk_stok_created_by` (`created_by`),
  CONSTRAINT `fk_stok_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_stok_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`),
  CONSTRAINT `fk_stok_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`),
  CONSTRAINT `fk_stok_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `stok_opname`;
CREATE TABLE `stok_opname` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gudang` int(11) NOT NULL,
  `tanggal_opname` date NOT NULL,
  `status` enum('draft','final') NOT NULL DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_gudang` (`id_gudang`),
  KEY `idx_tanggal` (`tanggal_opname`),
  KEY `fk_opname_created_by` (`created_by`),
  CONSTRAINT `fk_opname_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_opname_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `stok_opname` (`id`, `id_gudang`, `tanggal_opname`, `status`, `created_by`, `created_at`) VALUES ('1', '1', '2026-05-25', 'draft', '1', '2026-05-26 01:28:35');

DROP TABLE IF EXISTS `stok_opname_detail`;
CREATE TABLE `stok_opname_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_stok_opname` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `qty_sistem` decimal(10,2) NOT NULL,
  `qty_fisik` decimal(10,2) NOT NULL,
  `selisih` decimal(10,2) GENERATED ALWAYS AS (`qty_fisik` - `qty_sistem`) STORED,
  PRIMARY KEY (`id`),
  KEY `fk_opnamedetail_opname` (`id_stok_opname`),
  KEY `fk_opnamedetail_produk` (`id_produk`),
  CONSTRAINT `fk_opnamedetail_opname` FOREIGN KEY (`id_stok_opname`) REFERENCES `stok_opname` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_opnamedetail_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `stok_opname_detail` (`id`, `id_stok_opname`, `id_produk`, `qty_sistem`, `qty_fisik`, `selisih`) VALUES ('1', '1', '1', '10.00', '5.00', '-5.00');

DROP TABLE IF EXISTS `stok_transfer`;
CREATE TABLE `stok_transfer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gudang_asal_id` int(11) NOT NULL,
  `gudang_tujuan_id` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `status` enum('pending','sent','received') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_transfer_asal` (`gudang_asal_id`),
  KEY `fk_transfer_tujuan` (`gudang_tujuan_id`),
  KEY `fk_transfer_produk` (`id_produk`),
  CONSTRAINT `fk_transfer_asal` FOREIGN KEY (`gudang_asal_id`) REFERENCES `gudang` (`id`),
  CONSTRAINT `fk_transfer_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`),
  CONSTRAINT `fk_transfer_tujuan` FOREIGN KEY (`gudang_tujuan_id`) REFERENCES `gudang` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `stok_transfer` (`id`, `gudang_asal_id`, `gudang_tujuan_id`, `id_produk`, `qty`, `status`, `created_at`, `updated_at`) VALUES ('1', '1', '3', '1', '2.00', 'pending', '2026-05-26 01:27:58', '2026-05-26 01:27:58');

DROP TABLE IF EXISTS `supplier`;
CREATE TABLE `supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gudang` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nama_pemilik` varchar(100) DEFAULT NULL,
  `kontak_person` varchar(100) DEFAULT NULL,
  `telpon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kota` varchar(50) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `bank_account` varchar(20) DEFAULT NULL,
  `bank_owner` varchar(100) DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_gudang` (`id_gudang`),
  KEY `idx_nama` (`nama`),
  CONSTRAINT `fk_supplier_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `timbangan`;
CREATE TABLE `timbangan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_stok_masuk` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `qty_teoritis` decimal(10,2) NOT NULL,
  `qty_actual` decimal(10,2) NOT NULL,
  `selisih` decimal(10,2) GENERATED ALWAYS AS (`qty_teoritis` - `qty_actual`) STORED,
  `persen_susut` decimal(5,2) GENERATED ALWAYS AS (case when `qty_teoritis` > 0 then (`qty_teoritis` - `qty_actual`) / `qty_teoritis` * 100 else 0 end) STORED,
  `alasan_susut` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_stok_masuk` (`id_stok_masuk`),
  KEY `idx_id_produk` (`id_produk`),
  KEY `fk_timbang_created_by` (`created_by`),
  CONSTRAINT `fk_timbang_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_timbang_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`),
  CONSTRAINT `fk_timbang_stok` FOREIGN KEY (`id_stok_masuk`) REFERENCES `stok_masuk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `titipan`;
CREATE TABLE `titipan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gudang` int(11) NOT NULL,
  `id_pengirim` int(11) NOT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `no_titipan` varchar(20) NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `qty_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty_dijual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty_tersisa` decimal(10,2) NOT NULL DEFAULT 0.00,
  `nominal_total` bigint(20) NOT NULL DEFAULT 0,
  `nominal_terjual` bigint(20) NOT NULL DEFAULT 0,
  `komisi_persen` decimal(5,2) NOT NULL DEFAULT 0.00,
  `komisi_tipe` enum('potong','bayar_terpisah') NOT NULL DEFAULT 'potong',
  `status` enum('masuk','dijual_sebagian','dijual_semua','selesai') NOT NULL DEFAULT 'masuk',
  `catatan` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_no_titipan` (`no_titipan`),
  KEY `idx_id_gudang` (`id_gudang`),
  KEY `idx_id_pengirim` (`id_pengirim`),
  KEY `idx_status` (`status`),
  KEY `fk_titipan_created_by` (`created_by`),
  KEY `idx_id_produk` (`id_produk`),
  CONSTRAINT `fk_titipan_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_titipan_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`),
  CONSTRAINT `fk_titipan_pengirim` FOREIGN KEY (`id_pengirim`) REFERENCES `supplier` (`id`),
  CONSTRAINT `fk_titipan_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `titipan_penjualan`;
CREATE TABLE `titipan_penjualan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_titipan` int(11) NOT NULL,
  `id_penjual` int(11) DEFAULT NULL,
  `id_pembeli` int(11) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL,
  `harga_jual` bigint(20) NOT NULL,
  `nominal` bigint(20) NOT NULL,
  `komisi_nominal` bigint(20) NOT NULL DEFAULT 0,
  `tanggal_jual` date NOT NULL,
  `status` enum('pending','terjual') NOT NULL DEFAULT 'pending',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_id_titipan` (`id_titipan`),
  KEY `idx_id_penjual` (`id_penjual`),
  KEY `idx_id_pembeli` (`id_pembeli`),
  KEY `idx_status` (`status`),
  KEY `fk_titjual_created_by` (`created_by`),
  CONSTRAINT `fk_titjual_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_titjual_pembeli` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id`),
  CONSTRAINT `fk_titjual_titipan` FOREIGN KEY (`id_titipan`) REFERENCES `titipan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','bos','admin','checker') NOT NULL,
  `id_gudang` int(11) DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_id_gudang` (`id_gudang`),
  CONSTRAINT `fk_users_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `id_gudang`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES ('1', 'Super Admin', 'superadmin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', NULL, '1', NULL, '2026-05-25 23:24:57', '2026-05-25 23:24:57');
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `id_gudang`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES ('2', 'Bos Gudang', 'bos@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bos', NULL, '1', NULL, '2026-05-25 23:24:57', '2026-05-25 23:24:57');
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `id_gudang`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES ('3', 'Admin Gudang A', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '1', '1', NULL, '2026-05-25 23:24:57', '2026-05-25 23:24:57');
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `id_gudang`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES ('4', 'Checker Gudang A', 'checker@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'checker', '1', '1', NULL, '2026-05-25 23:24:57', '2026-05-25 23:24:57');
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `id_gudang`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES ('5', 'Admin Gudang B', 'admin2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2', '1', NULL, '2026-05-25 23:24:57', '2026-05-25 23:24:57');

SET FOREIGN_KEY_CHECKS = 1;
