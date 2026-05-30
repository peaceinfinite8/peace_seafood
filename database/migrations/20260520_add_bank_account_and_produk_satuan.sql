-- Migration: add bank_account table and produk.satuan column
-- Run this SQL manually or via your migration tool

ALTER TABLE `produk` 
  ADD COLUMN `satuan` VARCHAR(20) NOT NULL DEFAULT 'kg' AFTER `deskripsi`;

CREATE TABLE IF NOT EXISTS `bank_account` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_user` INT NOT NULL,
  `bank_name` VARCHAR(100) NOT NULL,
  `account_number` VARCHAR(50) NOT NULL,
  `account_name` VARCHAR(100) NOT NULL,
  `is_active` TINYINT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_user` (`id_user`),
  CONSTRAINT `fk_bank_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
