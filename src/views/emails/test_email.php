<?php
// Test email template used by settings SMTP tester
$subject = $subject ?? 'Test Email — Peace Seafood';
$content = '<div style="color:#0f172a">'
    . '<h2 style="margin:0 0 8px;font-size:20px;color:#0f172a">Test Email</h2>'
    . '<p style="margin:0 0 12px;color:#334155">Ini adalah email percobaan untuk memverifikasi konfigurasi SMTP.</p>'
    . '<p style="margin:0 0 0;color:#334155">Jika Anda menerima email ini, konfigurasi SMTP berfungsi.</p>'
    . '</div>';

include __DIR__ . '/base_layout.php';
