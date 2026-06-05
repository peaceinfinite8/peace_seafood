<?php
/**
 * Email Template: Payment Approved - Access Restored
 * Variables: $gudangName, $newExpiry
 */

$expiryFormatted = date('d F Y', strtotime($newExpiry));
$daysAdded = Database::fetchOne("SELECT nilai FROM settings WHERE kunci = 'renewal_duration_days' AND id_gudang IS NULL");
$days = $daysAdded['nilai'] ?? 30;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Disetujui</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0f172a 100%); min-height: 100vh;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0f172a 100%); padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Email Container -->
                <table role="presentation" style="max-width: 600px; width: 100%; border-collapse: collapse; background: white; border-radius: 16px; box-shadow: 0 25px 60px rgba(0,0,0,0.25); overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 32px 24px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 16px;">✅</div>
                            <h1 style="margin: 0; color: white; font-size: 24px; font-weight: 700; letter-spacing: -0.5px;">
                                PEMBAYARAN DISETUJUI
                            </h1>
                            <p style="margin: 8px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px; font-weight: 500;">
                                Akses Gudang Anda Telah Dipulihkan
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 32px 24px;">
                            <p style="margin: 0 0 24px 0; color: #1e293b; font-size: 16px; line-height: 1.6; text-align: center;">
                                Selamat! Pembayaran Anda telah diverifikasi dan disetujui.
                            </p>
                            
                            <!-- Success Box -->
                            <div style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-radius: 12px; padding: 24px; margin-bottom: 24px; text-align: center;">
                                <p style="margin: 0 0 8px 0; color: #065f46; font-size: 14px; font-weight: 600;">
                                    Gudang: <strong><?= htmlspecialchars($gudangName) ?></strong>
                                </p>
                                <p style="margin: 0 0 16px 0; color: #047857; font-size: 13px;">
                                    Masa aktif diperpanjang <strong><?= $days ?> hari</strong>
                                </p>
                                <div style="background: white; border-radius: 8px; padding: 16px; display: inline-block;">
                                    <p style="margin: 0 0 4px 0; color: #64748b; font-size: 12px; font-weight: 600;">BERLAKU HINGGA</p>
                                    <p style="margin: 0; color: #059669; font-size: 20px; font-weight: 700;">
                                        <?= $expiryFormatted ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Info List -->
                            <div style="background: #f8fafc; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                                <p style="margin: 0 0 12px 0; color: #1e293b; font-size: 14px; font-weight: 600;">
                                    Apa yang bisa Anda lakukan sekarang:
                                </p>
                                <ul style="margin: 0; padding-left: 20px; color: #475569; font-size: 14px; line-height: 1.8;">
                                    <li>Akses penuh ke semua fitur sistem</li>
                                    <li>Kelola stok dan transaksi seperti biasa</li>
                                    <li>Tambah atau edit data master</li>
                                    <li>Cetak laporan dan nota penjualan</li>
                                </ul>
                            </div>
                            
                            <!-- CTA Button -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/peace_seafood/dashboard" style="display: inline-block; background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%); color: white; text-decoration: none; padding: 16px 32px; border-radius: 12px; font-size: 16px; font-weight: 700; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);">
                                            🚀 MULAI BEKERJA
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 24px 0 0 0; color: #64748b; font-size: 13px; line-height: 1.6; text-align: center;">
                                Terima kasih atas kepercayaan Anda menggunakan Peace Seafood WMS.<br>
                                Jika ada pertanyaan, hubungi tim support kami.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: #f8fafc; padding: 24px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0 0 8px 0; color: #64748b; font-size: 12px;">
                                Email otomatis dari sistem Peace Seafood WMS
                            </p>
                            <p style="margin: 0; color: #94a3b8; font-size: 11px;">
                                © <?= date('Y') ?> Peace Seafood. All rights reserved.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
