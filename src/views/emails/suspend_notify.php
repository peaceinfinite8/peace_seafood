<?php
/**
 * Suspend Notification Email Template
 * Variables expected:
 * - $name: User name
 * - $gudangName: Warehouse name
 * - $reason: Suspension reason
 * - $whatsapp: WhatsApp number for contact
 */

// Build email content
ob_start();
?>
<div style="background:rgba(239, 68, 68, 0.15);border-left:4px solid #DC2626;border-radius:8px;padding:16px 20px;margin-bottom:24px;">
    <div style="font-size:32px;margin-bottom:4px;">🚫</div>
    <h2 style="margin:0 0 8px;font-size:20px;color:#DC2626;font-weight:700;">Akses Gudang Ditangguhkan</h2>
    <p style="margin:0;color:#334155;font-size:14px;line-height:1.6;">Akses sistem Anda telah ditangguhkan sementara oleh administrator platform.</p>
</div>

<div style="margin-bottom:20px;">
    <p style="margin:0 0 12px;color:#0f172a;font-size:15px;font-weight:600;">Halo <?php echo htmlspecialchars($name); ?>,</p>
    <p style="margin:0 0 12px;color:#334155;font-size:14px;line-height:1.6;">
        Kami informasikan bahwa akses gudang <strong style="color:#0f172a;"><?php echo htmlspecialchars($gudangName); ?></strong> telah ditangguhkan oleh administrator platform.
    </p>
    <?php if (!empty($reason)): ?>
    <div style="background:#fff3cd;border-left:4px solid #ffc107;border-radius:8px;padding:12px 16px;margin:16px 0;">
        <p style="margin:0;color:#664d03;font-size:13px;font-weight:600;">Alasan Penangguhan:</p>
        <p style="margin:4px 0 0;color:#664d03;font-size:13px;line-height:1.5;"><?php echo htmlspecialchars($reason); ?></p>
    </div>
    <?php endif; ?>
    <p style="margin:12px 0 0;color:#334155;font-size:14px;line-height:1.6;">
        Selama masa penangguhan ini, Anda tidak dapat mengakses sistem atau melakukan transaksi operasional.
    </p>
</div>

<div style="background:#f1f5f9;border-radius:8px;padding:16px;margin-bottom:20px;">
    <h3 style="margin:0 0 12px;font-size:16px;color:#0f172a;font-weight:700;">Apa yang harus dilakukan?</h3>
    <ol style="margin:0;padding:0 0 0 20px;color:#334155;font-size:14px;line-height:1.8;">
        <li>Hubungi administrator platform untuk klarifikasi</li>
        <li>Selesaikan masalah yang menyebabkan penangguhan</li>
        <li>Tunggu konfirmasi dari administrator untuk aktivasi kembali</li>
    </ol>
</div>

<div style="text-align:center;margin:28px 0;">
    <a href="https://wa.me/<?php echo htmlspecialchars($whatsapp ?? '628123456789'); ?>?text=Halo,%20saya%20ingin%20klarifikasi%20penangguhan%20gudang%20<?php echo urlencode($gudangName); ?>" 
       target="_blank"
       style="display:inline-block;background:#25D366;color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:8px;font-weight:700;font-size:15px;">
        💬 Hubungi Administrator via WhatsApp
    </a>
</div>

<div style="margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0;">
    <p style="margin:0 0 8px;color:#64748b;font-size:13px;line-height:1.5;">
        <strong style="color:#334155;">Butuh bantuan?</strong><br>
        Tim support kami siap membantu Anda menyelesaikan masalah ini.
    </p>
</div>
<?php
$content = ob_get_clean();

// Include base layout
include __DIR__ . '/base_layout.php';
?>
