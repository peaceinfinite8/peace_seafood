<?php
/**
 * Email Template: Payment Request to SaaS Owner (Magic Link)
 * Variables: $gudangName, $bosName, $nominal, $proofUrl, $magicLink, $expiryHours
 */

$gudangName = $gudang['nama_gudang'] ?? 'Unknown';
$bosName = $bos['name'] ?? 'Unknown';
$nominalFormatted = 'Rp ' . number_format($nominal, 0, ',', '.');
$timestamp = date('d F Y, H:i') . ' WIB';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan Pembayaran Baru</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0f172a 100%); min-height: 100vh;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0f172a 100%); padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Email Container -->
                <table role="presentation" style="max-width: 600px; width: 100%; border-collapse: collapse; background: white; border-radius: 16px; box-shadow: 0 25px 60px rgba(0,0,0,0.25); overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%); padding: 32px 24px; text-align: center;">
                            <h1 style="margin: 0; color: white; font-size: 24px; font-weight: 700; letter-spacing: -0.5px;">
                                🐟 PEACE SEAFOOD
                            </h1>
                            <p style="margin: 8px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px; font-weight: 500;">
                                Sistem Manajemen Gudang Ikan
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Alert Banner -->
                    <tr>
                        <td style="background: #fef3c7; padding: 16px 24px; border-left: 4px solid #f59e0b;">
                            <p style="margin: 0; color: #92400e; font-size: 14px; font-weight: 600; display: flex; align-items: center;">
                                <span style="font-size: 18px; margin-right: 8px;">⚠️</span>
                                <span>PERMINTAAN PEMBAYARAN BARU</span>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 32px 24px;">
                            <p style="margin: 0 0 24px 0; color: #1e293b; font-size: 16px; line-height: 1.6;">
                                Tenant berikut telah mengirimkan bukti pembayaran dan menunggu verifikasi Anda:
                            </p>
                            
                            <!-- Info Grid -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin-bottom: 24px; background: #f8fafc; border-radius: 12px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 16px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 13px; font-weight: 600; width: 40%;">Gudang</td>
                                    <td style="padding: 16px; border-bottom: 1px solid #e2e8f0; color: #1e293b; font-size: 14px; font-weight: 700;"><?= htmlspecialchars($gudangName) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 13px; font-weight: 600;">Pemilik</td>
                                    <td style="padding: 16px; border-bottom: 1px solid #e2e8f0; color: #1e293b; font-size: 14px; font-weight: 700;"><?= htmlspecialchars($bosName) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 13px; font-weight: 600;">Nominal</td>
                                    <td style="padding: 16px; border-bottom: 1px solid #e2e8f0; color: #059669; font-size: 16px; font-weight: 700;"><?= $nominalFormatted ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px; color: #64748b; font-size: 13px; font-weight: 600;">Tanggal</td>
                                    <td style="padding: 16px; color: #1e293b; font-size: 14px; font-weight: 700;"><?= $timestamp ?></td>
                                </tr>
                            </table>
                            
                            <!-- Bukti Transfer -->
                            <div style="margin-bottom: 24px; text-align: center; background: #f8fafc; padding: 16px; border-radius: 12px;">
                                <p style="margin: 0 0 12px 0; color: #64748b; font-size: 13px; font-weight: 600;">BUKTI PEMBAYARAN</p>
                                <img src="<?= $proofUrl ?>" alt="Bukti Transfer" style="max-width: 100%; height: auto; border-radius: 8px; border: 2px solid #e2e8f0;">
                            </div>
                            
                            <!-- CTA Button -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="<?= $magicLink ?>" style="display: inline-block; background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%); color: white; text-decoration: none; padding: 16px 32px; border-radius: 12px; font-size: 16px; font-weight: 700; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3); transition: all 0.3s;">
                                            ✅ APPROVE & BUKA AKSES GUDANG
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Important Notice -->
                            <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                                <p style="margin: 0; color: #991b1b; font-size: 13px; line-height: 1.5;">
                                    <strong>⏱️ Penting:</strong><br>
                                    • Tombol hanya bisa digunakan <strong>satu kali</strong><br>
                                    • Kadaluarsa dalam <strong><?= $settings['magic_link_expiry_hours'] ?? 24 ?> jam</strong><br>
                                    • Jika expired, approve manual dari dashboard
                                </p>
                            </div>
                            
                            <p style="margin: 0; color: #64748b; font-size: 13px; line-height: 1.6; text-align: center;">
                                Jika tombol tidak berfungsi, salin link berikut ke browser:<br>
                                <code style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-size: 11px; word-break: break-all; display: inline-block; margin-top: 8px;"><?= $magicLink ?></code>
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
