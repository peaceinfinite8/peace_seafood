<?php

declare(strict_types=1);

namespace App\Services\Shared;

use App\Utils\Database;

/**
 * Notification Service
 * Handles in-app notifications for all user roles
 */
class NotificationService
{
    /**
     * Create notification for specific user
     */
    public static function create(
        int $idUser,
        string $tipe,
        string $judul,
        string $pesan,
        ?int $referenceId = null,
        ?string $referenceTipe = null,
        ?string $actionUrl = null
    ): int {
        try {
            // Check if in-app notifications are enabled
            $enabled = self::getSetting('notification_inapp', '1');
            if ((int)$enabled !== 1) {
                return 0;
            }

            return Database::insert('notifikasi', [
                'id_user' => $idUser,
                'tipe' => $tipe,
                'judul' => $judul,
                'pesan' => $pesan,
                'reference_id' => $referenceId,
                'reference_tipe' => $referenceTipe,
                'action_url' => $actionUrl,
                'is_read' => 0
            ]);
        } catch (\Exception $e) {
            error_log("NotificationService::create Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Create notifications for all users in a gudang with specific role
     */
    public static function createForRole(
        int $idGudang,
        string $role,
        string $tipe,
        string $judul,
        string $pesan,
        ?int $referenceId = null,
        ?string $referenceTipe = null,
        ?string $actionUrl = null
    ): int {
        try {
            $users = Database::fetchAll(
                "SELECT id FROM users WHERE id_gudang = ? AND role = ? AND is_active = 1",
                [$idGudang, $role]
            );

            $count = 0;
            foreach ($users as $user) {
                $created = self::create(
                    (int)$user['id'],
                    $tipe,
                    $judul,
                    $pesan,
                    $referenceId,
                    $referenceTipe,
                    $actionUrl
                );
                if ($created > 0) $count++;
            }

            return $count;
        } catch (\Exception $e) {
            error_log("NotificationService::createForRole Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Create notifications for all users in a gudang
     */
    public static function createForGudang(
        int $idGudang,
        string $tipe,
        string $judul,
        string $pesan,
        ?int $referenceId = null,
        ?string $referenceTipe = null,
        ?string $actionUrl = null
    ): int {
        try {
            $users = Database::fetchAll(
                "SELECT id FROM users WHERE id_gudang = ? AND is_active = 1",
                [$idGudang]
            );

            $count = 0;
            foreach ($users as $user) {
                $created = self::create(
                    (int)$user['id'],
                    $tipe,
                    $judul,
                    $pesan,
                    $referenceId,
                    $referenceTipe,
                    $actionUrl
                );
                if ($created > 0) $count++;
            }

            return $count;
        } catch (\Exception $e) {
            error_log("NotificationService::createForGudang Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get unread count for user
     */
    public static function getUnreadCount(int $idUser): int
    {
        try {
            $result = Database::fetchOne(
                "SELECT COUNT(*) as count FROM notifikasi 
                 WHERE id_user = ? AND is_read = 0 AND deleted_at IS NULL",
                [$idUser]
            );
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            error_log("NotificationService::getUnreadCount Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get notifications for user (paginated)
     */
    public static function getForUser(int $idUser, int $limit = 50, int $offset = 0): array
    {
        try {
            return Database::fetchAll(
                "SELECT * FROM notifikasi 
                 WHERE id_user = ? AND deleted_at IS NULL
                 ORDER BY created_at DESC 
                 LIMIT ? OFFSET ?",
                [$idUser, $limit, $offset]
            );
        } catch (\Exception $e) {
            error_log("NotificationService::getForUser Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead(int $notificationId, int $idUser): bool
    {
        try {
            // Verify ownership before marking
            $notif = Database::fetchOne(
                "SELECT id FROM notifikasi WHERE id = ? AND id_user = ? AND deleted_at IS NULL",
                [$notificationId, $idUser]
            );

            if (!$notif) {
                return false;
            }

            Database::update(
                'notifikasi',
                [
                    'is_read' => 1,
                    'read_at' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$notificationId]
            );

            return true;
        } catch (\Exception $e) {
            error_log("NotificationService::markAsRead Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark all notifications as read for user
     */
    public static function markAllAsRead(int $idUser): bool
    {
        try {
            Database::update(
                'notifikasi',
                [
                    'is_read' => 1,
                    'read_at' => date('Y-m-d H:i:s')
                ],
                'id_user = ? AND is_read = 0',
                [$idUser]
            );
            return true;
        } catch (\Exception $e) {
            error_log("NotificationService::markAllAsRead Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create grace period notifications for all roles in a gudang
     */
    public static function createGracePeriodNotifications(int $idGudang, int $remainingDays): void
    {
        try {
            $gudang = Database::fetchOne("SELECT nama FROM gudang WHERE id = ?", [$idGudang]);
            if (!$gudang) return;

            $gudangName = $gudang['nama'];

            // Different notification levels based on remaining days
            if ($remainingDays === 7) {
                // H-7: Notify bos only
                self::createForRole(
                    $idGudang,
                    'bos',
                    'grace_period',
                    'Masa Aktif Tersisa 7 Hari',
                    "Gudang {$gudangName} akan terkunci dalam 7 hari. Segera lakukan perpanjangan.",
                    null,
                    null,
                    '/settings'
                );
            } elseif ($remainingDays === 3) {
                // H-3: Notify all users
                $roles = ['bos', 'super_admin', 'admin', 'financial_admin', 'checker', 'helper', 'viewer'];
                foreach ($roles as $role) {
                    self::createForRole(
                        $idGudang,
                        $role,
                        'grace_period',
                        '⚠️ Masa Aktif Tersisa 3 Hari',
                        "Gudang {$gudangName} akan terkunci dalam 3 hari. Hubungi pemilik gudang untuk perpanjangan.",
                        null,
                        null,
                        '/settings'
                    );
                }
            } elseif ($remainingDays === 1) {
                // H-1: Urgent notification
                $roles = ['bos', 'super_admin', 'admin', 'financial_admin', 'checker', 'helper', 'viewer'];
                foreach ($roles as $role) {
                    self::createForRole(
                        $idGudang,
                        $role,
                        'grace_period',
                        '🚨 Masa Aktif Tersisa 1 Hari!',
                        "PERHATIAN: Gudang {$gudangName} akan terkunci besok! Segera lakukan perpanjangan untuk menghindari gangguan operasional.",
                        null,
                        null,
                        '/settings'
                    );
                }
            }
        } catch (\Exception $e) {
            error_log("NotificationService::createGracePeriodNotifications Error: " . $e->getMessage());
        }
    }

    /**
     * Get setting value
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
}

