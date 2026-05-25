-- ============================================================
-- PEACE SEAFOOD — DATABASE SCHEMA
-- Database: peace_seafood
-- Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- Engine: InnoDB
-- ============================================================

CREATE DATABASE IF NOT EXISTS `peace_seafood`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `peace_seafood`;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. GUDANG (Warehouse)
-- ============================================================
CREATE TABLE IF NOT EXISTS `gudang` (
  `id`         INT            NOT NULL AUTO_INCREMENT,
  `id_bos`     INT            NOT NULL,
  `nama`       VARCHAR(100)   NOT NULL,
  `alamat`     TEXT           NOT NULL,
  `kota`       VARCHAR(50)    NOT NULL,
  `telpon`     VARCHAR(20)    NULL,
  `is_active`  TINYINT        NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_bos`    (`id_bos`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT            NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100)   NOT NULL,
  `email`      VARCHAR(100)   NOT NULL,
  `password`   VARCHAR(255)   NOT NULL,
  `role`       ENUM('super_admin','bos','admin','checker') NOT NULL,
  `id_gudang`  INT            NULL,
  `is_active`  TINYINT        NOT NULL DEFAULT 1,
  `last_login` TIMESTAMP      NULL,
  `created_at` TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email`   (`email`),
  INDEX `idx_role`        (`role`),
  INDEX `idx_id_gudang`   (`id_gudang`),
  CONSTRAINT `fk_users_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add FK from gudang to users (bos)
ALTER TABLE `gudang`
  ADD CONSTRAINT `fk_gudang_bos` FOREIGN KEY (`id_bos`) REFERENCES `users` (`id`);

-- ============================================================
-- 3. SUPPLIER
-- ============================================================
CREATE TABLE IF NOT EXISTS `supplier` (
  `id`            INT          NOT NULL AUTO_INCREMENT,
  `id_gudang`     INT          NOT NULL,
  `nama`          VARCHAR(100) NOT NULL,
  `nama_pemilik`  VARCHAR(100) NULL,
  `kontak_person` VARCHAR(100) NULL,
  `telpon`        VARCHAR(20)  NULL,
  `alamat`        TEXT         NULL,
  `kota`          VARCHAR(50)  NULL,
  `bank_name`     VARCHAR(50)  NULL,
  `bank_account`  VARCHAR(20)  NULL,
  `bank_owner`    VARCHAR(100) NULL,
  `is_active`     TINYINT      NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_gudang` (`id_gudang`),
  INDEX `idx_nama`      (`nama`),
  CONSTRAINT `fk_supplier_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. PEMBELI (Buyer)
-- ============================================================
CREATE TABLE IF NOT EXISTS `pembeli` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `id_gudang`    INT          NOT NULL,
  `nama`         VARCHAR(100) NOT NULL,
  `telpon`       VARCHAR(20)  NULL,
  `alamat`       TEXT         NULL,
  `kota`         VARCHAR(50)  NULL,
  `tipe`         ENUM('retail','bulk','reseller') NOT NULL DEFAULT 'retail',
  `kredit_limit` BIGINT       NOT NULL DEFAULT 0,
  `is_active`    TINYINT      NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_gudang` (`id_gudang`),
  INDEX `idx_nama`      (`nama`),
  CONSTRAINT `fk_pembeli_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. JENIS_IKAN (Fish Type)
-- ============================================================
CREATE TABLE IF NOT EXISTS `jenis_ikan` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `nama`       VARCHAR(100) NOT NULL,
  `deskripsi`  TEXT         NULL,
  `is_active`  TINYINT      NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nama` (`nama`),
  INDEX `idx_nama` (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. PRODUK (Product)
-- ============================================================
CREATE TABLE IF NOT EXISTS `produk` (
  `id`            INT            NOT NULL AUTO_INCREMENT,
  `id_jenis_ikan` INT            NOT NULL,
  `id_gudang`     INT            NOT NULL,
  `nama`          VARCHAR(100)   NOT NULL,
  `deskripsi`     TEXT           NULL,
  `harga_beli`    BIGINT         NOT NULL DEFAULT 0,
  `harga_jual`    BIGINT         NOT NULL DEFAULT 0,
  `stok_qty`      DECIMAL(10,2)  NOT NULL DEFAULT 0,
  `nilai_stok`    BIGINT         NOT NULL DEFAULT 0,
  `stok_minimum`  DECIMAL(10,2)  NOT NULL DEFAULT 0,
  `is_active`     TINYINT        NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_gudang`     (`id_gudang`),
  INDEX `idx_id_jenis_ikan` (`id_jenis_ikan`),
  INDEX `idx_nama`          (`nama`),
  CONSTRAINT `fk_produk_jenis`  FOREIGN KEY (`id_jenis_ikan`) REFERENCES `jenis_ikan` (`id`),
  CONSTRAINT `fk_produk_gudang` FOREIGN KEY (`id_gudang`)     REFERENCES `gudang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. HARGA_HISTORY (Price History)
-- ============================================================
CREATE TABLE IF NOT EXISTS `harga_history` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `id_produk`  INT          NOT NULL,
  `harga_lama` BIGINT       NULL,
  `harga_baru` BIGINT       NOT NULL,
  `tipe`       ENUM('beli','jual') NOT NULL,
  `reason`     VARCHAR(255) NULL,
  `changed_by` INT          NOT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_produk`  (`id_produk`),
  INDEX `idx_tipe`       (`tipe`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `fk_harga_produk`      FOREIGN KEY (`id_produk`)  REFERENCES `produk` (`id`),
  CONSTRAINT `fk_harga_changed_by`  FOREIGN KEY (`changed_by`) REFERENCES `users`  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. STOK_MASUK (Incoming Stock)
-- ============================================================
CREATE TABLE IF NOT EXISTS `stok_masuk` (
  `id`          INT           NOT NULL AUTO_INCREMENT,
  `id_gudang`   INT           NOT NULL,
  `id_produk`   INT           NOT NULL,
  `id_supplier` INT           NOT NULL,
  `qty`         DECIMAL(10,2) NOT NULL,
  `harga_beli`  BIGINT        NOT NULL,
  `total`       BIGINT        GENERATED ALWAYS AS (`qty` * `harga_beli`) STORED,
  `status`      ENUM('pending','confirmed','rejected') NOT NULL DEFAULT 'pending',
  `catatan`     TEXT          NULL,
  `created_by`  INT           NOT NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_gudang`   (`id_gudang`),
  INDEX `idx_id_produk`   (`id_produk`),
  INDEX `idx_id_supplier` (`id_supplier`),
  INDEX `idx_status`      (`status`),
  INDEX `idx_created_at`  (`created_at`),
  CONSTRAINT `fk_stok_gudang`      FOREIGN KEY (`id_gudang`)   REFERENCES `gudang`   (`id`),
  CONSTRAINT `fk_stok_produk`      FOREIGN KEY (`id_produk`)   REFERENCES `produk`   (`id`),
  CONSTRAINT `fk_stok_supplier`    FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id`),
  CONSTRAINT `fk_stok_created_by`  FOREIGN KEY (`created_by`)  REFERENCES `users`    (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. TIMBANGAN (Weighing)
-- ============================================================
CREATE TABLE IF NOT EXISTS `timbangan` (
  `id`             INT           NOT NULL AUTO_INCREMENT,
  `id_stok_masuk`  INT           NOT NULL,
  `id_produk`      INT           NOT NULL,
  `qty_teoritis`   DECIMAL(10,2) NOT NULL,
  `qty_actual`     DECIMAL(10,2) NOT NULL,
  `selisih`        DECIMAL(10,2) GENERATED ALWAYS AS (`qty_teoritis` - `qty_actual`) STORED,
  `persen_susut`   DECIMAL(5,2)  GENERATED ALWAYS AS (
    CASE WHEN `qty_teoritis` > 0
         THEN ((`qty_teoritis` - `qty_actual`) / `qty_teoritis` * 100)
         ELSE 0 END
  ) STORED,
  `alasan_susut`   TEXT          NULL,
  `created_by`     INT           NOT NULL,
  `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_stok_masuk` (`id_stok_masuk`),
  INDEX `idx_id_produk`     (`id_produk`),
  CONSTRAINT `fk_timbang_stok`       FOREIGN KEY (`id_stok_masuk`) REFERENCES `stok_masuk` (`id`),
  CONSTRAINT `fk_timbang_produk`     FOREIGN KEY (`id_produk`)     REFERENCES `produk`     (`id`),
  CONSTRAINT `fk_timbang_created_by` FOREIGN KEY (`created_by`)    REFERENCES `users`      (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. NOTA (Sales Invoice)
-- ============================================================
CREATE TABLE IF NOT EXISTS `nota` (
  `id`             INT          NOT NULL AUTO_INCREMENT,
  `id_gudang`      INT          NOT NULL,
  `id_pembeli`     INT          NOT NULL,
  `no_nota`        VARCHAR(20)  NOT NULL,
  `tanggal_nota`   DATE         NOT NULL,
  `subtotal`       BIGINT       NOT NULL DEFAULT 0,
  `diskon_nominal` BIGINT       NOT NULL DEFAULT 0,
  `pajak`          BIGINT       NOT NULL DEFAULT 0,
  `total`          BIGINT       NOT NULL DEFAULT 0,
  `pembayaran`     ENUM('cash','hutang') NOT NULL,
  `status`         ENUM('draft','final','cancel') NOT NULL DEFAULT 'draft',
  `catatan`        TEXT         NULL,
  `created_by`     INT          NOT NULL,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_no_nota`    (`no_nota`),
  INDEX `idx_id_gudang`      (`id_gudang`),
  INDEX `idx_id_pembeli`     (`id_pembeli`),
  INDEX `idx_status`         (`status`),
  INDEX `idx_tanggal_nota`   (`tanggal_nota`),
  INDEX `idx_pembayaran`     (`pembayaran`),
  CONSTRAINT `fk_nota_gudang`      FOREIGN KEY (`id_gudang`)  REFERENCES `gudang`  (`id`),
  CONSTRAINT `fk_nota_pembeli`     FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id`),
  CONSTRAINT `fk_nota_created_by`  FOREIGN KEY (`created_by`) REFERENCES `users`   (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. NOTA_DETAIL (Invoice Line Items)
-- ============================================================
CREATE TABLE IF NOT EXISTS `nota_detail` (
  `id`         INT           NOT NULL AUTO_INCREMENT,
  `id_nota`    INT           NOT NULL,
  `id_produk`  INT           NOT NULL,
  `qty`        DECIMAL(10,2) NOT NULL,
  `harga_jual` BIGINT        NOT NULL,
  `subtotal`   BIGINT        NOT NULL,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_nota`   (`id_nota`),
  INDEX `idx_id_produk` (`id_produk`),
  CONSTRAINT `fk_detail_nota`   FOREIGN KEY (`id_nota`)   REFERENCES `nota`   (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detail_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. TITIPAN (Consignment)
-- ============================================================
CREATE TABLE IF NOT EXISTS `titipan` (
  `id`              INT           NOT NULL AUTO_INCREMENT,
  `id_gudang`       INT           NOT NULL,
  `id_pengirim`     INT           NOT NULL,
  `no_titipan`      VARCHAR(20)   NOT NULL,
  `tanggal_masuk`   DATE          NOT NULL,
  `qty_total`       DECIMAL(10,2) NOT NULL DEFAULT 0,
  `qty_dijual`      DECIMAL(10,2) NOT NULL DEFAULT 0,
  `qty_tersisa`     DECIMAL(10,2) NOT NULL DEFAULT 0,
  `nominal_total`   BIGINT        NOT NULL DEFAULT 0,
  `nominal_terjual` BIGINT        NOT NULL DEFAULT 0,
  `komisi_persen`   DECIMAL(5,2)  NOT NULL DEFAULT 0,
  `komisi_tipe`     ENUM('potong','bayar_terpisah') NOT NULL DEFAULT 'potong',
  `status`          ENUM('masuk','dijual_sebagian','dijual_semua','selesai') NOT NULL DEFAULT 'masuk',
  `catatan`         TEXT          NULL,
  `created_by`      INT           NOT NULL,
  `created_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_no_titipan`  (`no_titipan`),
  INDEX `idx_id_gudang`       (`id_gudang`),
  INDEX `idx_id_pengirim`     (`id_pengirim`),
  INDEX `idx_status`          (`status`),
  CONSTRAINT `fk_titipan_gudang`      FOREIGN KEY (`id_gudang`)   REFERENCES `gudang`   (`id`),
  CONSTRAINT `fk_titipan_pengirim`    FOREIGN KEY (`id_pengirim`) REFERENCES `supplier` (`id`),
  CONSTRAINT `fk_titipan_created_by`  FOREIGN KEY (`created_by`)  REFERENCES `users`    (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 13. TITIPAN_PENJUALAN (Consignment Sales)
-- ============================================================
CREATE TABLE IF NOT EXISTS `titipan_penjualan` (
  `id`             INT           NOT NULL AUTO_INCREMENT,
  `id_titipan`     INT           NOT NULL,
  `id_penjual`     INT           NULL,
  `id_pembeli`     INT           NULL,
  `qty`            DECIMAL(10,2) NOT NULL,
  `harga_jual`     BIGINT        NOT NULL,
  `nominal`        BIGINT        NOT NULL,
  `komisi_nominal` BIGINT        NOT NULL DEFAULT 0,
  `tanggal_jual`   DATE          NOT NULL,
  `status`         ENUM('pending','terjual') NOT NULL DEFAULT 'pending',
  `created_by`     INT           NOT NULL,
  `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_titipan` (`id_titipan`),
  INDEX `idx_id_penjual` (`id_penjual`),
  INDEX `idx_id_pembeli` (`id_pembeli`),
  INDEX `idx_status`     (`status`),
  CONSTRAINT `fk_titjual_titipan`    FOREIGN KEY (`id_titipan`) REFERENCES `titipan` (`id`),
  CONSTRAINT `fk_titjual_pembeli`    FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id`),
  CONSTRAINT `fk_titjual_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`   (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 14. RETUR (Returns)
-- ============================================================
CREATE TABLE IF NOT EXISTS `retur` (
  `id`          INT           NOT NULL AUTO_INCREMENT,
  `id_gudang`   INT           NOT NULL,
  `id_produk`   INT           NULL,
  `id_supplier` INT           NULL,
  `id_pembeli`  INT           NULL,
  `id_nota`     INT           NULL,
  `tipe`        ENUM('stok','piutang') NOT NULL,
  `qty`         DECIMAL(10,2) NULL,
  `nominal`     BIGINT        NULL,
  `alasan`      VARCHAR(255)  NOT NULL,
  `foto_bukti`  VARCHAR(255)  NULL,
  `status`      ENUM('pending','approved','rejected','posted') NOT NULL DEFAULT 'pending',
  `catatan`     TEXT          NULL,
  `created_by`  INT           NOT NULL,
  `approved_by` INT           NULL,
  `approved_at` TIMESTAMP     NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_gudang`  (`id_gudang`),
  INDEX `idx_tipe`       (`tipe`),
  INDEX `idx_status`     (`status`),
  INDEX `idx_id_produk`  (`id_produk`),
  CONSTRAINT `fk_retur_gudang`      FOREIGN KEY (`id_gudang`)   REFERENCES `gudang`   (`id`),
  CONSTRAINT `fk_retur_produk`      FOREIGN KEY (`id_produk`)   REFERENCES `produk`   (`id`),
  CONSTRAINT `fk_retur_supplier`    FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id`),
  CONSTRAINT `fk_retur_pembeli`     FOREIGN KEY (`id_pembeli`)  REFERENCES `pembeli`  (`id`),
  CONSTRAINT `fk_retur_nota`        FOREIGN KEY (`id_nota`)     REFERENCES `nota`     (`id`),
  CONSTRAINT `fk_retur_created_by`  FOREIGN KEY (`created_by`)  REFERENCES `users`    (`id`),
  CONSTRAINT `fk_retur_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users`    (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 15. HUTANG_PIUTANG (Debt/Credit)
-- ============================================================
CREATE TABLE IF NOT EXISTS `hutang_piutang` (
  `id`            INT          NOT NULL AUTO_INCREMENT,
  `id_gudang`     INT          NOT NULL,
  `jenis`         ENUM('hutang','piutang') NOT NULL,
  `id_supplier`   INT          NULL,
  `id_pembeli`    INT          NULL,
  `id_nota`       INT          NULL,
  `no_referensi`  VARCHAR(50)  NULL,
  `nominal`       BIGINT       NOT NULL,
  `nominal_bayar` BIGINT       NOT NULL DEFAULT 0,
  `sisa_hutang`   BIGINT       GENERATED ALWAYS AS (`nominal` - `nominal_bayar`) STORED,
  `jatuh_tempo`   DATE         NOT NULL,
  `status`        ENUM('open','sebagian','lunas') NOT NULL DEFAULT 'open',
  `catatan`       TEXT         NULL,
  `created_by`    INT          NOT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_gudang`    (`id_gudang`),
  INDEX `idx_jenis`        (`jenis`),
  INDEX `idx_id_supplier`  (`id_supplier`),
  INDEX `idx_id_pembeli`   (`id_pembeli`),
  INDEX `idx_status`       (`status`),
  INDEX `idx_jatuh_tempo`  (`jatuh_tempo`),
  CONSTRAINT `fk_hp_gudang`      FOREIGN KEY (`id_gudang`)   REFERENCES `gudang`   (`id`),
  CONSTRAINT `fk_hp_supplier`    FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id`),
  CONSTRAINT `fk_hp_pembeli`     FOREIGN KEY (`id_pembeli`)  REFERENCES `pembeli`  (`id`),
  CONSTRAINT `fk_hp_nota`        FOREIGN KEY (`id_nota`)     REFERENCES `nota`     (`id`),
  CONSTRAINT `fk_hp_created_by`  FOREIGN KEY (`created_by`)  REFERENCES `users`    (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 16. HUTANG_PIUTANG_HISTORY (Payment History)
-- ============================================================
CREATE TABLE IF NOT EXISTS `hutang_piutang_history` (
  `id`                INT          NOT NULL AUTO_INCREMENT,
  `id_hutang_piutang` INT          NOT NULL,
  `nominal_bayar`     BIGINT       NOT NULL,
  `metode_bayar`      VARCHAR(50)  NULL,
  `keterangan`        TEXT         NULL,
  `created_by`        INT          NOT NULL,
  `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_hutang_piutang` (`id_hutang_piutang`),
  INDEX `idx_created_at`        (`created_at`),
  CONSTRAINT `fk_hph_hutang`      FOREIGN KEY (`id_hutang_piutang`) REFERENCES `hutang_piutang` (`id`),
  CONSTRAINT `fk_hph_created_by` FOREIGN KEY (`created_by`)         REFERENCES `users`          (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 17. BIAYA_OPERASIONAL (Operational Expenses)
-- ============================================================
CREATE TABLE IF NOT EXISTS `biaya_operasional` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `id_gudang`  INT          NOT NULL,
  `kategori`   VARCHAR(50)  NOT NULL,
  `deskripsi`  TEXT         NULL,
  `nominal`    BIGINT       NOT NULL,
  `tanggal`    DATE         NOT NULL,
  `created_by` INT          NOT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_gudang` (`id_gudang`),
  INDEX `idx_kategori`  (`kategori`),
  INDEX `idx_tanggal`   (`tanggal`),
  CONSTRAINT `fk_biaya_gudang`      FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`),
  CONSTRAINT `fk_biaya_created_by`  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 18. NOTIFIKASI (Notifications)
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifikasi` (
  `id`              INT          NOT NULL AUTO_INCREMENT,
  `id_user`         INT          NOT NULL,
  `tipe`            VARCHAR(50)  NOT NULL,
  `judul`           VARCHAR(255) NOT NULL,
  `pesan`           TEXT         NOT NULL,
  `reference_id`    INT          NULL,
  `reference_tipe`  VARCHAR(50)  NULL,
  `is_read`         TINYINT      NOT NULL DEFAULT 0,
  `read_at`         TIMESTAMP    NULL,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_user`    (`id_user`),
  INDEX `idx_tipe`       (`tipe`),
  INDEX `idx_is_read`    (`is_read`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 19. SETTINGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `id_gudang`  INT          NULL,
  `kunci`      VARCHAR(100) NOT NULL,
  `nilai`      TEXT         NOT NULL,
  `deskripsi`  TEXT         NULL,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting` (`id_gudang`, `kunci`),
  INDEX `idx_kunci`      (`kunci`),
  INDEX `idx_id_gudang`  (`id_gudang`),
  CONSTRAINT `fk_settings_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 20. STOK_OPNAME (Physical Inventory Count)
-- ============================================================
CREATE TABLE IF NOT EXISTS `stok_opname` (
  `id`              INT           NOT NULL AUTO_INCREMENT,
  `id_gudang`       INT           NOT NULL,
  `tanggal_opname`  DATE          NOT NULL,
  `status`          ENUM('draft','final') NOT NULL DEFAULT 'draft',
  `created_by`      INT           NOT NULL,
  `created_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_gudang` (`id_gudang`),
  INDEX `idx_tanggal`   (`tanggal_opname`),
  CONSTRAINT `fk_opname_gudang`      FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`),
  CONSTRAINT `fk_opname_created_by`  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
