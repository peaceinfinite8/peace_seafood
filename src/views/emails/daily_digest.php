<?php
// Daily digest for SaaS Owner
$subject = $subject ?? 'Ringkasan Harian Aktivitas Tenant';
$items = $items ?? [];

$listHtml = '<ul style="padding-left:18px;margin:0;color:#334155">';
foreach ($items as $it) {
    $listHtml .= '<li style="margin:6px 0">' . htmlspecialchars($it) . '</li>';
}
$listHtml .= '</ul>';

$content = '<div style="color:#0f172a">'
    . '<h2 style="margin:0 0 8px;font-size:20px;color:#0f172a">Ringkasan Harian</h2>'
    . '<p style="margin:0 0 12px;color:#334155">Berikut adalah ringkasan aktivitas tenant untuk hari ini.</p>'
    . $listHtml
    . '</div>';

include __DIR__ . '/base_layout.php';
