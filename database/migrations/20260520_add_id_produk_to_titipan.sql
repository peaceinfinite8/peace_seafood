-- Migration: add product reference to titipan
ALTER TABLE `titipan`
  ADD COLUMN `id_produk` INT NULL AFTER `id_pengirim`;

ALTER TABLE `titipan`
  ADD INDEX `idx_id_produk` (`id_produk`);

ALTER TABLE `titipan`
  ADD CONSTRAINT `fk_titipan_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`);
