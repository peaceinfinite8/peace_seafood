<?php
// Suspend notification to tenant
$subject = $subject ?? 'Akses Gudang Ditangguhkan';
$reason = $reason ?? '';
$content = '<div style="color:#0f172a">'
    . '<h2 style="margin:0 0 8px;font-size:20px;color:#0f172a">Akses Ditangguhkan</h2>'
    . '<p style="margin:0 0 12px;color:#334155">Halo,</p>'
    . '<p style="margin:0 0 16px;color:#334155">Akses gudang Anda telah ditangguhkan oleh SaaS Owner. ' . htmlspecialchars($reason) . '</p>'
    . '<div style="text-align:center;margin-top:18px">'
    . '<a href="#" style="display:inline-block;padding:10px 16px;background:#ef4444;color:#fff;border-radius:8px;text-decoration:none;font-weight:600">Hubungi Support</a>'
    . '</div>'
    . '</div>';

include __DIR__ . '/base_layout.php';
