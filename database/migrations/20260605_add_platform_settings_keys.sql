-- ============================================================
-- TASK F: Platform Settings Manager
-- Migration: Add all platform configuration settings
-- Created: 2026-06-05
-- ============================================================

USE `peace_seafood`;

-- ============================================================
-- INSERT PLATFORM SETTINGS
-- ============================================================

-- Tab 1: Platform Identity
INSERT IGNORE INTO `settings` (`id_gudang`, `kunci`, `nilai`, `deskripsi`) VALUES
(NULL, 'platform_name', 'Peace Seafood WMS', 'Nama platform yang tampil di email dan UI'),
(NULL, 'platform_logo', '', 'Logo platform dalam format Base64 atau URL'),
(NULL, 'platform_color', '#2563eb', 'Warna tema utama platform (hex code)'),
(NULL, 'platform_whatsapp', '628123456789', 'Nomor WhatsApp bisnis untuk support');

-- Lock Screen Messages
INSERT IGNORE INTO `settings` (`id_gudang`, `kunci`, `nilai`, `deskripsi`) VALUES
(NULL, 'lock_screen_title', 'Masa Aktif Anda Telah Habis', 'Judul lock screen saat expired'),
(NULL, 'lock_screen_message', 'Silakan lakukan pembayaran untuk melanjutkan akses ke sistem', 'Pesan utama lock screen'),
(NULL, 'lock_screen_payment_steps', 'Transfer ke rekening yang tertera\nUpload bukti pembayaran\nTunggu verifikasi admin', 'Instruksi pembayaran (gunakan \n untuk line break)'),
(NULL, 'lock_screen_after_submit', 'Terima kasih! Bukti pembayaran Anda sedang diverifikasi. Anda akan menerima email konfirmasi setelah disetujui.', 'Pesan setelah submit bukti bayar');

-- Tab 2: Email & Notification
INSERT IGNORE INTO `settings` (`id_gudang`, `kunci`, `nilai`, `deskripsi`) VALUES
(NULL, 'mail_user', '', 'Email pengirim (Gmail)'),
(NULL, 'mail_pass', '', 'App Password Gmail (terenkripsi)'),
(NULL, 'mail_from_name', 'Peace Seafood WMS', 'Nama pengirim yang tampil di inbox'),
(NULL, 'mail_footer', 'Peace Seafood WMS - Sistem Manajemen Gudang Ikan\nJl. Contoh No. 123, Jakarta', 'Footer email (alamat bisnis, disclaimer)');

-- Notification toggles already added in Task C, just verify:
-- notification_email_reminder, notification_digest, notification_inapp, digest_send_time

-- Tab 3: Payment
INSERT IGNORE INTO `settings` (`id_gudang`, `kunci`, `nilai`, `deskripsi`) VALUES
(NULL, 'payment_bank_name', 'Bank Mandiri', 'Nama bank untuk pembayaran'),
(NULL, 'payment_bank_holder', 'PT Peace Seafood Indonesia', 'Nama pemilik rekening'),
(NULL, 'payment_bank_number', '1234567890', 'Nomor rekening bank'),
(NULL, 'payment_qris_image', '', 'Foto QRIS dalam format Base64 atau URL'),
(NULL, 'payment_monthly_price', '500000', 'Nominal sewa per periode (dalam rupiah)'),
(NULL, 'payment_max_upload_mb', '5', 'Maksimal ukuran file upload bukti (MB)'),
(NULL, 'payment_allowed_formats', 'jpg,png,pdf', 'Format file yang diizinkan (comma separated)'),
(NULL, 'payment_magic_link_expiry_hours', '24', 'Durasi kadaluarsa Magic Link approval (jam)');

-- Tab 4: System
INSERT IGNORE INTO `settings` (`id_gudang`, `kunci`, `nilai`, `deskripsi`) VALUES
(NULL, 'trial_duration_days', '14', 'Durasi trial untuk tenant baru (hari)'),
(NULL, 'renewal_duration_days', '30', 'Durasi perpanjangan per pembayaran (hari)');

-- Grace period settings already added in Task C, just verify:
-- grace_warning_day_1 (7), grace_warning_day_2 (3), grace_warning_day_3 (1)
-- grace_yellow_days (7), grace_red_days (3)

-- Localization
INSERT IGNORE INTO `settings` (`id_gudang`, `kunci`, `nilai`, `deskripsi`) VALUES
(NULL, 'timezone', 'Asia/Jakarta', 'Zona waktu sistem'),
(NULL, 'language', 'id', 'Bahasa sistem (id=Indonesia, en=English)');

-- Maintenance Mode
INSERT IGNORE INTO `settings` (`id_gudang`, `kunci`, `nilai`, `deskripsi`) VALUES
(NULL, 'maintenance_mode', '0', 'Status maintenance mode (0=off, 1=on)'),
(NULL, 'maintenance_message', 'Sistem sedang dalam pemeliharaan. Kami akan kembali sebentar lagi.', 'Pesan yang ditampilkan saat maintenance mode aktif');

-- ============================================================
-- CREATE STORAGE DIRECTORY FOR UPLOADS (via PHP later)
-- ============================================================
-- Directory: storage/uploads/platform/
-- - logo.png
-- - qris.png

