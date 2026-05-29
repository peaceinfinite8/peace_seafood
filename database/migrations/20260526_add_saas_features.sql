-- ============================================================
-- PEACE SEAFOOD: SAAS MULTI-TENANT & SECURITY UPGRADE MIGRATION
-- Date: 2026-05-26
-- ============================================================

-- 1. Alter users table to support SaaS features
ALTER TABLE `users` 
  MODIFY COLUMN `role` ENUM('saas_owner', 'super_admin', 'bos', 'admin', 'checker') NOT NULL,
  ADD COLUMN `is_first_login` TINYINT NOT NULL DEFAULT 0,
  ADD COLUMN `registration_status` ENUM('active', 'pending_signup') NOT NULL DEFAULT 'active',
  ADD COLUMN `reset_token` VARCHAR(64) NULL,
  ADD COLUMN `reset_token_expires_at` TIMESTAMP NULL;

-- 2. Alter gudang table to support subscription and trial features
ALTER TABLE `gudang`
  ADD COLUMN `subscription_until` DATE NULL,
  ADD COLUMN `status_langganan` ENUM('aktif', 'suspend') NOT NULL DEFAULT 'aktif';

-- 3. Seed default developer WhatsApp support number in global settings
INSERT INTO `settings` (`id_gudang`, `kunci`, `nilai`, `deskripsi`) 
VALUES (NULL, 'platform_developer_whatsapp', '628123456789', 'Nomor WhatsApp resmi Developer/Support untuk penagihan sewa SaaS')
ON DUPLICATE KEY UPDATE `nilai` = `nilai`;
