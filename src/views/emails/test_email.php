<?php
/**
 * Test Email Template
 * Simple email to verify SMTP configuration
 */

// Build email content
ob_start();
?>
<div style="text-align:center;margin-bottom:24px;">
    <div style="width:64px;height:64px;margin:0 auto 16px;background:linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
        </svg>
    </div>
    <h2 style="margin:0 0 8px;font-size:24px;color:#0f172a;font-weight:700;">Test Email Berhasil!</h2>
    <p style="margin:0;color:#64748b;font-size:14px;">Email ini dikirim untuk memverifikasi konfigurasi SMTP Anda</p>
</div>

<div style="background:#f8fafc;border-radius:12px;padding:20px;margin-bottom:24px;">
    <h3 style="margin:0 0 12px;font-size:16px;color:#0f172a;font-weight:700;">✅ Konfigurasi SMTP Valid</h3>
    <p style="margin:0;color:#334155;font-size:14px;line-height:1.6;">
        Selamat! Sistem email Anda sudah terkonfigurasi dengan benar dan siap mengirim notifikasi ke tenant.
    </p>
</div>

<div style="margin-bottom:20px;">
    <h4 style="margin:0 0 12px;font-size:14px;color:#0f172a;font-weight:700;">Informasi Test Email:</h4>
    <table style="width:100%;font-size:13px;color:#334155;line-height:1.8;">
        <tr>
            <td style="padding:4px 0;font-weight:600;width:140px;">Waktu Pengiriman:</td>
            <td style="padding:4px 0;"><?php echo date('d F Y, H:i:s'); ?> WIB</td>
        </tr>
        <tr>
            <td style="padding:4px 0;font-weight:600;">Platform:</td>
            <td style="padding:4px 0;">Peace Seafood WMS</td>
        </tr>
        <tr>
            <td style="padding:4px 0;font-weight:600;">Environment:</td>
            <td style="padding:4px 0;"><?php echo $_ENV['APP_ENV'] ?? 'production'; ?></td>
        </tr>
    </table>
</div>

<div style="background:rgba(59, 130, 246, 0.1);border-left:4px solid #3b82f6;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
    <p style="margin:0;color:#1e40af;font-size:13px;font-weight:600;">
        💡 Tips: Jika email ini masuk ke folder Spam, tambahkan alamat pengirim ke contact list Anda.
    </p>
</div>

<div style="text-align:center;margin-top:28px;">
    <p style="margin:0;color:#64748b;font-size:12px;line-height:1.5;">
        Email ini dikirim secara otomatis dari sistem.<br>
        Tidak perlu membalas email ini.
    </p>
</div>
<?php
$content = ob_get_clean();

// Include base layout
include __DIR__ . '/base_layout.php';
?>
