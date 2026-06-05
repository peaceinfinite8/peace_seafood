-- ============================================================
-- TASK A: Lock Screen & Sistem Pembayaran Manual
-- Migration: Add payment_requests and magic_link_tokens tables
-- Created: 2026-06-05
-- ============================================================

USE `peace_seafood`;

-- ============================================================
-- TABLE: payment_requests
-- Store all payment proof submissions from tenants
-- ============================================================

CREATE TABLE IF NOT EXISTS `payment_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_gudang` INT UNSIGNED NOT NULL,
  `id_bos` INT UNSIGNED NOT NULL,
  `nominal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `bukti_transfer_path` VARCHAR(255) NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `magic_token` VARCHAR(64) NULL,
  `magic_token_expires_at` DATETIME NULL,
  `magic_token_used_at` DATETIME NULL,
  `approved_at` DATETIME NULL,
  `approved_by` INT UNSIGNED NULL,
  `rejected_at` DATETIME NULL,
  `rejected_by` INT UNSIGNED NULL,
  `rejection_reason` TEXT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX `idx_id_gudang` (`id_gudang`),
  INDEX `idx_status` (`status`),
  INDEX `idx_magic_token` (`magic_token`),
  INDEX `idx_created_at` (`created_at` DESC),
  
  CONSTRAINT `fk_payment_requests_gudang` 
    FOREIGN KEY (`id_gudang`) REFERENCES `gudang`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payment_requests_bos` 
    FOREIGN KEY (`id_bos`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INDEX OPTIMIZATION
-- ============================================================

-- Add index to settings for faster platform settings lookup
ALTER TABLE `settings` 
  ADD INDEX IF NOT EXISTS `idx_kunci_gudang` (`kunci`, `id_gudang`);

-- ============================================================
-- NOTES
-- ============================================================
-- 1. magic_token is one-time use, expires based on settings
-- 2. Storage path: storage/uploads/payment_proofs/{id_gudang}_{timestamp}_{filename}
-- 3. Only SaaS Owner can approve/reject via Magic Link or dashboard
-- 4. After approval, subscription_until is extended automatically
