<?php

declare(strict_types=1);

namespace App\Services\WMS;

use App\Utils\Database;
use App\Utils\Email;

/**
 * Grace Period Service
 * Handles subscription expiration warnings and email reminders
 */
class GracePeriodService
{
    /**
     * Calculate remaining days until subscription expires
     * Returns negative number if already expired
     * 
     * @param int $idGudang Warehouse ID
     * @return int|null Remaining days (null if error/no subscription)
     */
    public static function getRemainingDays(int $idGudang): ?int
    {
        try {
            $gudang = Database::fetchOne(
                "SELECT subscription_until, timezone FROM gudang g
                 LEFT JOIN settings s ON s.kunci = 'timezone' AND s.id_gudang IS NULL
                 WHERE g.id = ? AND g.is_active = 1",
                [$idGudang]
            );

            if (!$gudang || empty($gudang['subscription_until'])) {
                return null;
            }

            // Get timezone (priority: gudang setting > global setting > default)
            $timezone = self::getTimezone();
            
            // IMPORTANT: Treat database DATETIME as UTC
            // MySQL DATETIME doesn't store timezone info, we treat it as UTC
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            
            // Parse subscription_until as UTC
            $expiry = new \DateTime($gudang['subscription_until'], new \DateTimeZone('UTC'));
            
            // Calculate difference
            $interval = $now->diff($expiry);
            
            // Get signed days: positive if future, negative if past
            $days = (int)$interval->format('%r%a');
            
            return $days;
            
        } catch (\Exception $e) {
            error_log("GracePeriodService::getRemainingDays Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get grace banner data for display
     * Returns ['show' => bool, 'color' => 'yellow'|'red', 'days' => int, 'message' => string]
     */
    public static function getBannerData(int $idGudang): array
    {
        try {
            $remainingDays = self::getRemainingDays($idGudang);
            
            if ($remainingDays === null) {
                return ['show' => false];
            }

            // Get thresholds from settings
            $yellowThreshold = (int)self::getSetting('grace_yellow_days', '7');
            $redThreshold = (int)self::getSetting('grace_red_days', '3');

            // If remaining days > yellow threshold, no banner
            if ($remainingDays > $yellowThreshold) {
                return ['show' => false];
            }

            // Determine color and message
            $color = $remainingDays <= $redThreshold ? 'red' : 'yellow';
            
            if ($remainingDays <= 0) {
                $message = 'Masa aktif gudang Anda telah berakhir. Akses dikunci hingga perpanjangan disetujui.';
            } elseif ($remainingDays === 1) {
                $message = 'Akses gudang akan terkunci dalam 1 hari';
            } else {
                $message = "Akses gudang akan terkunci dalam {$remainingDays} hari";
            }

            return [
                'show' => true,
                'color' => $color,
                'days' => $remainingDays,
                'message' => $message,
                'whatsapp' => self::getSetting('platform_whatsapp', '628123456789')
            ];
        } catch (\Exception $e) {
            error_log("GracePeriodService::getBannerData Error: " . $e->getMessage());
            return ['show' => false];
        }
    }

    /**
     * Check and send grace period email reminders
     * Should be called daily via cron or on first request each day
     */
    public static function checkAndSendReminders(): array
    {
        try {
            $emailEnabled = (int)self::getSetting('notification_email_reminder', '1');
            if ($emailEnabled !== 1) {
                return ['success' => true, 'sent' => 0, 'message' => 'Email reminders disabled'];
            }

            $warning1 = (int)self::getSetting('grace_warning_day_1', '7');
            $warning2 = (int)self::getSetting('grace_warning_day_2', '3');
            $warning3 = (int)self::getSetting('grace_warning_day_3', '1');

            // Get all active gudang with subscription_until
            $allGudang = Database::fetchAll(
                "SELECT g.id, g.nama, g.subscription_until, u.email, u.name 
                 FROM gudang g
                 JOIN users u ON g.id_bos = u.id
                 WHERE g.is_active = 1 
                   AND g.subscription_until IS NOT NULL
                   AND g.status_langganan = 'aktif'"
            );

            $sent = 0;
            $timezone = self::getTimezone();
            $today = (new \DateTime('now', new \DateTimeZone($timezone)))->format('Y-m-d');

            foreach ($allGudang as $gudang) {
                $remaining = self::getRemainingDays((int)$gudang['id']);
                if ($remaining === null) continue;

                $trigger = null;
                if ($remaining === $warning1) {
                    $trigger = 'H-7';
                } elseif ($remaining === $warning2) {
                    $trigger = 'H-3';
                } elseif ($remaining === $warning3) {
                    $trigger = 'H-1';
                }

                if ($trigger === null) continue;

                // Check if already sent today
                $alreadySent = Database::fetchOne(
                    "SELECT id FROM grace_email_log 
                     WHERE id_gudang = ? 
                       AND trigger = ? 
                       AND DATE(sent_at) = ?",
                    [$gudang['id'], $trigger, $today]
                );

                if ($alreadySent) continue;

                // Send email
                $emailSent = self::sendGraceReminderEmail(
                    $gudang['email'],
                    $gudang['name'],
                    $gudang['nama'],
                    $remaining,
                    $trigger
                );

                if ($emailSent) {
                    // Log email sent
                    Database::insert('grace_email_log', [
                        'id_gudang' => $gudang['id'],
                        'trigger' => $trigger,
                        'email_to' => $gudang['email']
                    ]);
                    $sent++;
                }
            }

            return ['success' => true, 'sent' => $sent];
        } catch (\Exception $e) {
            error_log("GracePeriodService::checkAndSendReminders Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send grace reminder email to tenant
     */
    private static function sendGraceReminderEmail(
        string $email,
        string $name,
        string $gudangName,
        int $remainingDays,
        string $trigger
    ): bool {
        try {
            $subjects = [
                'H-7' => 'Masa aktif gudang Anda tersisa 7 hari',
                'H-3' => '⚠️ 3 hari lagi — Gudang Anda akan terkunci',
                'H-1' => '🚨 Besok akses gudang Anda terkunci!'
            ];

            $subject = $subjects[$trigger] ?? 'Peringatan Masa Aktif Gudang';

            $whatsapp = self::getSetting('platform_whatsapp', '628123456789');

            // Load email template
            ob_start();
            include __DIR__ . '/../../views/emails/grace_reminder.php';
            $body = ob_get_clean();

            return Email::send($email, $subject, $body);
        } catch (\Exception $e) {
            error_log("GracePeriodService::sendGraceReminderEmail Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get setting value (global or gudang-specific)
     */
    private static function getSetting(string $key, string $default = ''): string
    {
        try {
            $setting = Database::fetchOne(
                "SELECT nilai FROM settings WHERE kunci = ? AND id_gudang IS NULL LIMIT 1",
                [$key]
            );
            return $setting ? $setting['nilai'] : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Get timezone from settings
     */
    private static function getTimezone(): string
    {
        return self::getSetting('timezone', 'Asia/Jakarta');
    }
}


    /**
     * Format UTC datetime to local timezone
     * 
     * @param string $utcDatetime UTC datetime string
     * @param string $format Output format (default: 'd M Y H:i')
     * @return string Formatted datetime in local timezone
     */
    public static function formatDateTimeLocal(string $utcDatetime, string $format = 'd M Y H:i'): string
    {
        try {
            $timezone = self::getTimezone();
            $dt = new \DateTime($utcDatetime, new \DateTimeZone('UTC'));
            $dt->setTimezone(new \DateTimeZone($timezone));
            return $dt->format($format);
        } catch (\Exception $e) {
            error_log("GracePeriodService::formatDateTimeLocal Error: " . $e->getMessage());
            return $utcDatetime;
        }
    }
    
    /**
     * Get current datetime in UTC for database storage
     * 
     * @return string UTC datetime (Y-m-d H:i:s)
     */
    public static function nowUTC(): string
    {
        return (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
    }
}
