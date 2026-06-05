<?php
/**
 * Grace Period Reminder Email Template
 * Variables expected:
 * - $name: User name
 * - $gudangName: Warehouse name
 * - $remainingDays: Days remaining
 * - $trigger: H-7, H-3, or H-1
 * - $whatsapp: WhatsApp number for contact
 */

$urgencyColors = [
    'H-7' => ['bg' => 'rgba(245, 158, 11, 0.12)', 'text' => '#D97706', 'icon' => '⚠️'],
    'H-3' => ['bg' => 'rgba(251, 146, 60, 0.15)', 'text' => '#EA580C', 'icon' => '⚠️'],
    'H-1' => ['bg' => 'rgba(239, 68, 68, 0.15)', 'text' => '#DC2626', 'icon' => '🚨']
];

$colors = $urgencyColors[$trigger] ?? $urgencyColors['H-7'];

$messages = [
    'H-7' => 'Masa aktif gudang Anda akan berakhir dalam <strong>7 hari</strong>.',
    'H-3' => 'Masa aktif gudang Anda akan berakhir dalam <strong>3 hari</strong>!',
    'H-1' => 'PERHATIAN: Masa aktif gudang Anda akan berakhir <strong>BESOK</strong>!'
];

$message = $messages[$trigger] ?? 'Masa aktif gudang Anda akan segera berakhir.';

// Build email content
ob_start();
?>
<div style="background:<?php echo $colors['bg']; ?>;border-left:4px solid <?php echo $colors['text']; ?>;border-radius:8px;padding:16px 20px;margin-bottom:24px;">
    <div style="font-size:32px;margin-bottom:4px;"><?php echo $colors['icon']; ?></div>
    <h2 style="margin:0 0 8px;font-size:20px;color:<?php echo $colors['text']; ?>;font-weight:700;">Peringatan Masa Aktif Gudang</h2>
    <p style="margin:0;color:#334155;font-size:14px;line-height:1.6;"><?php echo $message; ?></p>
</div>

<div style="margin-bottom:20px;">
    <p style="margin:0 0 12px;color:#0f172a;font-size:15px;font-weight:600;">Halo <?php echo htmlspecialchars($name); ?>,</p>
    <p style="margin:0 0 12px;color:#334155;font-size:14px;line-height:1.6;">
        Kami ingin mengingatkan Anda bahwa masa aktif untuk gudang <strong style="color:#0f172a;"><?php echo htmlspecialchars($gudangName); ?></strong> akan berakhir dalam <strong style="color:<?php echo $colors['text']; ?>;"><?php echo $remainingDays; ?> hari</strong>.
    </p>
    <p style="margin:0 0 12px;color:#334155;font-size:14px;line-height:1.6;">
        Setelah masa aktif berakhir, akses ke sistem akan dikunci dan Anda tidak dapat melakukan transaksi operasional hingga perpanjangan disetujui.
    </p>
</div>

<div style="background:#f1f5f9;border-radius:8px;padding:16px;margin-bottom:20px;">
    <h3 style="margin:0 0 12px;font-size:16px;color:#0f172a;font-weight:700;">Apa yang perlu dilakukan?</h3>
    <ol style="margin:0;padding:0 0 0 20px;color:#334155;font-size:14px;line-height:1.8;">
        <li>Hubungi admin platform melalui WhatsApp untuk perpanjangan</li>
        <li>Lakukan pembayaran sesuai nominal yang disepakati</li>
        <li>Upload bukti pembayaran saat sistem terkunci</li>
        <li>Tunggu persetujuan admin untuk aktivasi kembali</li>
    </ol>
</div>

<div style="text-align:center;margin:28px 0;">
    <a href="https://wa.me/<?php echo htmlspecialchars($whatsapp ?? '628123456789'); ?>?text=Halo,%20saya%20ingin%20perpanjang%20masa%20aktif%20gudang%20<?php echo urlencode($gudangName); ?>" 
       target="_blank"
       style="display:inline-block;background:#25D366;color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:8px;font-weight:700;font-size:15px;">
        💬 Hubungi Admin via WhatsApp
    </a>
</div>

<div style="margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0;">
    <p style="margin:0 0 8px;color:#64748b;font-size:13px;line-height:1.5;">
        <strong style="color:#334155;">Butuh bantuan?</strong><br>
        Jangan ragu untuk menghubungi tim support kami jika Anda memiliki pertanyaan.
    </p>
</div>
<?php
$content = ob_get_clean();

// Include base layout
include __DIR__ . '/base_layout.php';
?>
