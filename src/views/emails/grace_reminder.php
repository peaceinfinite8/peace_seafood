<?php
// Grace reminder email
$subject = $subject ?? 'Masa aktif gudang Anda segera berakhir';
$daysLeft = $daysLeft ?? null; // optional
$daysText = is_null($daysLeft) ? '' : "Sisa masa aktif: {$daysLeft} hari";
$content = '<div style="text-align:left;color:#0f172a">'
    . '<h2 style="margin:0 0 8px;font-size:20px;color:#0f172a">Peringatan Masa Aktif Gudang</h2>'
    . '<p style="margin:0 0 12px;color:#334155">Halo,</p>'
    . '<p style="margin:0 0 16px;color:#334155">Kami menginformasikan bahwa masa aktif gudang Anda akan segera berakhir. ' . $daysText . '</p>'
    . '<div style="text-align:center;margin-top:18px">'
    . '<a href="#" style="display:inline-block;padding:12px 20px;background:linear-gradient(90deg,#38bdf8,#0ea5e9);color:#fff;border-radius:8px;text-decoration:none;font-weight:600">Perpanjang Sekarang</a>'
    . '</div>'
    . '</div>';

include __DIR__ . '/base_layout.php';
