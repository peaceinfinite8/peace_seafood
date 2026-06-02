<?php
// Magic link approval email to SaaS Owner
$subject = $subject ?? 'Permintaan Pembayaran — Klik untuk Approve';
$tenantName = $tenantName ?? 'Nama Gudang';
$ownerName = $ownerName ?? 'Pemilik';
$amount = $amount ?? '';
$magicUrl = $magicUrl ?? '#';

$content = '<div style="color:#0f172a">'
    . '<h2 style="margin:0 0 8px;font-size:20px;color:#0f172a">🐟 Permintaan Pembayaran</h2>'
    . '<p style="margin:0 0 8px;color:#334155">Gudang: <strong>' . htmlspecialchars($tenantName) . '</strong><br/>Pemilik: <strong>' . htmlspecialchars($ownerName) . '</strong></p>'
    . '<p style="margin:0 0 16px;color:#334155">Nominal: <strong>' . htmlspecialchars($amount) . '</strong></p>'
    . '<div style="text-align:center;margin-top:18px">'
    . '<a href="' . htmlspecialchars($magicUrl) . '" style="display:inline-block;padding:12px 20px;background:linear-gradient(90deg,#38bdf8,#0ea5e9);color:#fff;border-radius:8px;text-decoration:none;font-weight:700">✅ Approve & Buka Akses Gudang</a>'
    . '<p style="margin-top:12px;color:#64748b;font-size:12px">Tombol hanya berlaku sekali dan kadaluarsa sesuai pengaturan.</p>'
    . '</div>'
    . '</div>';

include __DIR__ . '/base_layout.php';
