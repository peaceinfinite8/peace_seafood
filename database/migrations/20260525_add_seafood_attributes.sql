-- Migration: Add Seafood Atribut Master and Product Specific Attributes
-- Date: 2026-05-25

ALTER TABLE `jenis_ikan`
  ADD COLUMN `allowed_sizes` TEXT NULL AFTER `deskripsi`,
  ADD COLUMN `allowed_grades` TEXT NULL AFTER `allowed_sizes`,
  ADD COLUMN `allowed_origins` TEXT NULL AFTER `allowed_grades`;

ALTER TABLE `produk`
  ADD COLUMN `size` VARCHAR(50) NULL DEFAULT NULL AFTER `satuan`,
  ADD COLUMN `grade` VARCHAR(50) NULL DEFAULT NULL AFTER `size`,
  ADD COLUMN `asal` VARCHAR(100) NULL DEFAULT NULL AFTER `grade`;
