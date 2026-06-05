<?php
/**
 * Daily Digest Email Template for SaaS Owner
 * Variables expected:
 * - $date: Current date
 * - $summary: Array with statistics
 *   - total_tenants: Total tenant count
 *   - active_tenants: Active tenant count
 *   - expiring_soon: Tenants expiring in 7 days
 *   - expired_today: Tenants expired today
 *   - new_signups: New signups today
 *   - payment_requests: Payment requests pending
 * - $activities: Array of recent activities
 */

$summary = $summary ?? [];
$activities = $activities ?? [];
$date = $date ?? date('d F Y');

// Build email content
ob_start();
?>
<div style="margin-bottom:24px;">
    <h2 style="margin:0 0 8px;font-size:24px;color:#0f172a;font-weight:700;">Laporan Harian Platform</h2>
    <p style="margin:0;color:#64748b;font-size:14px;"><?php echo htmlspecialchars($date); ?></p>
</div>

<!-- Summary Cards -->
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:24px;">
    <!-- Total Tenants -->
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;">
        <div style="font-size:12px;color:#64748b;font-weight:600;margin-bottom:8px;">TOTAL TENANT</div>
        <div style="font-size:28px;font-weight:700;color:#0f172a;"><?php echo $summary['total_tenants'] ?? 0; ?></div>
    </div>
    
    <!-- Active -->
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:16px;">
        <div style="font-size:12px;color:#15803d;font-weight:600;margin-bottom:8px;">AKTIF</div>
        <div style="font-size:28px;font-weight:700;color:#16a34a;"><?php echo $summary['active_tenants'] ?? 0; ?></div>
    </div>
    
    <!-- Expiring Soon -->
    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:16px;">
        <div style="font-size:12px;color:#c2410c;font-weight:600;margin-bottom:8px;">AKAN KADALUARSA (7 HARI)</div>
        <div style="font-size:28px;font-weight:700;color:#ea580c;"><?php echo $summary['expiring_soon'] ?? 0; ?></div>
    </div>
    
    <!-- Expired Today -->
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:16px;">
        <div style="font-size:12px;color:#991b1b;font-weight:600;margin-bottom:8px;">KADALUARSA HARI INI</div>
        <div style="font-size:28px;font-weight:700;color:#dc2626;"><?php echo $summary['expired_today'] ?? 0; ?></div>
    </div>
</div>

<!-- New Signups Today -->
<?php if (isset($summary['new_signups']) && $summary['new_signups'] > 0): ?>
<div style="background:rgba(59, 130, 246, 0.1);border-left:4px solid #3b82f6;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
    <div style="font-size:14px;color:#1e40af;font-weight:600;margin-bottom:4px;">🎉 Pendaftaran Baru Hari Ini</div>
    <div style="font-size:13px;color:#334155;">
        <strong style="font-size:20px;color:#2563eb;"><?php echo $summary['new_signups']; ?></strong> tenant baru mendaftar hari ini
    </div>
</div>
<?php endif; ?>

<!-- Payment Requests Pending -->
<?php if (isset($summary['payment_requests']) && $summary['payment_requests'] > 0): ?>
<div style="background:rgba(245, 158, 11, 0.1);border-left:4px solid #f59e0b;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
    <div style="font-size:14px;color:#d97706;font-weight:600;margin-bottom:4px;">💰 Permintaan Pembayaran Pending</div>
    <div style="font-size:13px;color:#334155;">
        <strong style="font-size:20px;color:#f59e0b;"><?php echo $summary['payment_requests']; ?></strong> permintaan pembayaran menunggu persetujuan Anda
    </div>
</div>
<?php endif; ?>

<!-- Recent Activities -->
<?php if (!empty($activities) && count($activities) > 0): ?>
<div style="margin-top:28px;">
    <h3 style="margin:0 0 16px;font-size:16px;color:#0f172a;font-weight:700;">Aktivitas Terkini</h3>
    <div style="background:#f8fafc;border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;">
        <?php foreach (array_slice($activities, 0, 10) as $idx => $activity): ?>
        <div style="padding:12px 16px;border-bottom:1px solid #e2e8f0;<?php echo $idx % 2 === 0 ? 'background:#ffffff;' : ''; ?>">
            <div style="display:flex;align-items:start;gap:12px;">
                <div style="flex-shrink:0;width:8px;height:8px;border-radius:50%;background:#3b82f6;margin-top:6px;"></div>
                <div style="flex:1;">
                    <div style="font-size:13px;color:#334155;line-height:1.6;">
                        <strong style="color:#0f172a;"><?php echo htmlspecialchars($activity['user'] ?? 'User'); ?></strong>
                        <?php echo htmlspecialchars($activity['action'] ?? ''); ?>
                    </div>
                    <div style="font-size:11px;color:#64748b;margin-top:2px;">
                        <?php echo htmlspecialchars($activity['timestamp'] ?? ''); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Call to Action -->
<div style="text-align:center;margin:32px 0 24px;">
    <a href="<?php echo htmlspecialchars($_ENV['APP_URL'] ?? 'http://localhost'); ?>/peace_seafood/dashboard" 
       target="_blank"
       style="display:inline-block;background:linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:8px;font-weight:700;font-size:15px;box-shadow:0 4px 12px rgba(59, 130, 246, 0.3);">
        📊 Buka Dashboard Platform
    </a>
</div>

<div style="margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0;text-align:center;">
    <p style="margin:0;color:#64748b;font-size:12px;line-height:1.5;">
        Laporan ini dikirim otomatis setiap hari untuk membantu Anda memantau platform.<br>
        Anda dapat mengatur preferensi notifikasi di pengaturan platform.
    </p>
</div>
<?php
$content = ob_get_clean();

// Include base layout
include __DIR__ . '/base_layout.php';
?>
