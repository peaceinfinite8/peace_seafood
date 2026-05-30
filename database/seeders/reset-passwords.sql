-- ============================================================
-- RESET PASSWORDS — semua user ke password: "password"
-- Jalankan di phpMyAdmin atau MySQL CLI jika user tidak bisa login
-- ============================================================

USE `peace_seafood`;

-- Hash bcrypt untuk kata sandi "password"
-- Diverifikasi dengan PHP password_verify()
UPDATE `users` SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE `email` IN (
    'bos@example.com',
    'admin@example.com',
    'admin2@example.com',
    'checker@example.com'
);

-- Verifikasi
SELECT `id`, `name`, `email`, `role`, `is_active` FROM `users` ORDER BY `role`, `name`;
