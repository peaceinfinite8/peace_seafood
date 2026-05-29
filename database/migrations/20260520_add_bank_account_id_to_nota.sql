-- Migration: add bank_account_id to nota
ALTER TABLE `nota`
  ADD COLUMN `bank_account_id` INT NULL AFTER `pembayaran`;

ALTER TABLE `nota`
  ADD INDEX `idx_bank_account_id` (`bank_account_id`);
