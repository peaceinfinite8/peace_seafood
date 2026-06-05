-- ============================================================
-- TASK C: Grace Period Alert & Notification System
-- Migration: Add Grace Email Log and Notifications Tables
-- Created: 2026-06-05
-- ============================================================

USE `peace_seafood`;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABLE: grace_email_log
-- Purpose: Track grace period reminder emails to prevent duplicate sends
-- ============================================================
CREATE TABLE IF NOT EXISTS `grace_email_log` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `id_gudang`  INT          NOT NULL,
  `trigger`    ENUM('H-7','H-3','H-1') NOT NULL,
  `sent_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email_to`   VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_id_gudang` (`id_gudang`),
  INDEX `idx_trigger`   (`trigger`),
  INDEX `idx_sent_at`   (`sent_at`),
  CONSTRAINT `fk_grace_log_gudang` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: notifications (Enhanced from existing notifikasi)
-- Purpose: Store in-app notifications for all users per role
-- ============================================================
-- Check if table already exists, if yes, just add missing columns
ALTER TABLE `notifikasi`
  ADD COLUMN IF NOT EXISTS `action_url` VARCHAR(255) NULL AFTER `reference_tipe`,
  ADD INDEX IF NOT EXISTS `idx_created_at_desc` (`created_at` DESC);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- INSERT DEFAULT GRACE PERIOD SETTINGS
-- ============================================================
INSERT IGNORE INTO `settings` (`id_gudang`, `kunci`, `nilai`, `deskripsi`) VALUES
(NULL, 'grace_yellow_days', '7', 'Threshold hari untuk banner peringatan kuning'),
(NULL, 'grace_red_days', '3', 'Threshold hari untuk banner peringatan merah'),
(NULL, 'grace_warning_day_1', '7', 'Hari pertama kirim email reminder (H-7)'),
(NULL, 'grace_warning_day_2', '3', 'Hari kedua kirim email reminder (H-3)'),
(NULL, 'grace_warning_day_3', '1', 'Hari ketiga kirim email reminder (H-1)'),
(NULL, 'notification_email_reminder', '1', 'Toggle email reminder otomatis (0=off, 1=on)'),
(NULL, 'notification_digest', '1', 'Toggle email digest harian ke SaaS Owner (0=off, 1=on)'),
(NULL, 'notification_inapp', '1', 'Toggle notifikasi in-app bell icon (0=off, 1=on)'),
(NULL, 'digest_send_time', '08:00', 'Jam kirim digest harian (format HH:MM)'),
(NULL, 'timezone', 'Asia/Jakarta', 'Zona waktu sistem (default WIB)'),
(NULL, 'platform_whatsapp', '628123456789', 'Nomor WhatsApp platform untuk bantuan');

