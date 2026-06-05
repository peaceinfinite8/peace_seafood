<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Utils\Database;

/**
 * Platform Settings Service
 * Handles all platform-level settings for SaaS Owner
 */
class PlatformSettingsService
{
    /**
     * Get all platform settings grouped by category
     */
    public static function getAllSettings(): array
    {
        try {
            $settings = Database::fetchAll(
                "SELECT kunci, nilai, deskripsi 
                 FROM settings 
                 WHERE id_gudang IS NULL 
                 ORDER BY kunci"
            );

            // Decrypt sensitive fields
            $decrypted = array_map(function ($setting) {
                if ($setting['kunci'] === 'mail_pass' && !empty($setting['nilai'])) {
                    // Keep encrypted, will be shown as ••••
                    $setting['nilai_display'] = '••••••••';
                    $setting['is_encrypted'] = true;
                } else {
                    $setting['nilai_display'] = $setting['nilai'];
                    $setting['is_encrypted'] = false;
                }
                return $setting;
            }, $settings);

            return self::groupByCategory($decrypted);
        } catch (\Exception $e) {
            error_log("PlatformSettingsService::getAllSettings Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single setting value
     */
    public static function getSetting(string $key, string $default = ''): string
    {
        try {
            $setting = Database::fetchOne(
                "SELECT nilai FROM settings WHERE kunci = ? AND id_gudang IS NULL LIMIT 1",
                [$key]
            );
            return $setting ? $setting['nilai'] : $default;
        } catch (\Exception $e) {
            error_log("PlatformSettingsService::getSetting Error: " . $e->getMessage());
            return $default;
        }
    }

    /**
     * Update or create setting
     */
    public static function updateSetting(string $key, string $value): bool
    {
        try {
            // Special handling for encrypted fields
            if ($key === 'mail_pass' && !empty($value) && $value !== '••••••••') {
                $value = self::encrypt($value);
            }

            $existing = Database::fetchOne(
                "SELECT id FROM settings WHERE kunci = ? AND id_gudang IS NULL",
                [$key]
            );

            if ($existing) {
                Database::update(
                    'settings',
                    ['nilai' => $value],
                    'kunci = ? AND id_gudang IS NULL',
                    [$key]
                );
            } else {
                Database::insert('settings', [
                    'kunci' => $key,
                    'nilai' => $value,
                    'id_gudang' => null
                ]);
            }

            return true;
        } catch (\Exception $e) {
            error_log("PlatformSettingsService::updateSetting Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update multiple settings at once
     */
    public static function updateMultiple(array $settings): bool
    {
        try {
            Database::beginTransaction();

            foreach ($settings as $key => $value) {
                if (!self::updateSetting($key, $value)) {
                    throw new \Exception("Failed to update setting: {$key}");
                }
            }

            Database::commit();
            return true;
        } catch (\Exception $e) {
            Database::rollBack();
            error_log("PlatformSettingsService::updateMultiple Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Upload and save image (logo or QRIS)
     */
    public static function uploadImage(array $file, string $settingKey): array
    {
        try {
            // Validate file
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($file['type'], $allowedTypes)) {
                return ['success' => false, 'message' => 'Format file harus JPG atau PNG'];
            }

            if ($file['size'] > $maxSize) {
                return ['success' => false, 'message' => 'Ukuran file maksimal 5MB'];
            }

            // Create upload directory if not exists
            $uploadDir = BASE_PATH . '/storage/uploads/platform';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $settingKey . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . '/' . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return ['success' => false, 'message' => 'Gagal mengupload file'];
            }

            // Convert to base64 for storage
            $imageData = file_get_contents($filepath);
            $base64 = 'data:' . $file['type'] . ';base64,' . base64_encode($imageData);

            // Save to settings
            self::updateSetting($settingKey, $base64);

            // Delete physical file (already stored as base64)
            unlink($filepath);

            return [
                'success' => true,
                'message' => 'Gambar berhasil diupload',
                'data' => ['base64' => $base64]
            ];
        } catch (\Exception $e) {
            error_log("PlatformSettingsService::uploadImage Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Test email configuration by sending test email
     */
    public static function testEmail(string $recipientEmail): array
    {
        try {
            $appEnv = $_ENV['APP_ENV'] ?? 'local';

            // Get email settings
            $mailUser = self::getSetting('mail_user');
            $mailPass = self::getSetting('mail_pass');
            $mailFromName = self::getSetting('mail_from_name', 'Peace Seafood WMS');

            if (empty($mailUser) || empty($mailPass)) {
                return [
                    'success' => false,
                    'message' => 'Email dan App Password belum dikonfigurasi'
                ];
            }

            // Load test email template
            $subject = 'Test Email - Peace Seafood WMS';
            ob_start();
            include BASE_PATH . '/src/views/emails/test_email.php';
            $body = ob_get_clean();

            if ($appEnv === 'local') {
                // Log to file in local environment
                $logDir = BASE_PATH . '/storage/logs';
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0755, true);
                }

                $logFile = $logDir . '/email_mock.log';
                $logContent = sprintf(
                    "[%s] TEST EMAIL\nTo: %s\nSubject: %s\nFrom: %s <%s>\n\n%s\n\n%s\n\n",
                    date('Y-m-d H:i:s'),
                    $recipientEmail,
                    $subject,
                    $mailFromName,
                    $mailUser,
                    strip_tags($body),
                    str_repeat('=', 80)
                );

                file_put_contents($logFile, $logContent, FILE_APPEND);

                return [
                    'success' => true,
                    'message' => 'Test email berhasil (mode local: ditulis ke storage/logs/email_mock.log)'
                ];
            } else {
                // Send actual email in production
                $sent = \App\Utils\Email::send($recipientEmail, $subject, $body);

                if ($sent) {
                    return [
                        'success' => true,
                        'message' => 'Test email berhasil dikirim ke ' . $recipientEmail
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Gagal mengirim email. Periksa konfigurasi SMTP.'
                    ];
                }
            }
        } catch (\Exception $e) {
            error_log("PlatformSettingsService::testEmail Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Group settings by category/tab
     */
    private static function groupByCategory(array $settings): array
    {
        $grouped = [
            'platform' => [],
            'email' => [],
            'payment' => [],
            'system' => []
        ];

        foreach ($settings as $setting) {
            $key = $setting['kunci'];

            if (str_starts_with($key, 'platform_') || str_starts_with($key, 'lock_screen_')) {
                $grouped['platform'][] = $setting;
            } elseif (str_starts_with($key, 'mail_') || str_starts_with($key, 'notification_') || str_starts_with($key, 'digest_')) {
                $grouped['email'][] = $setting;
            } elseif (str_starts_with($key, 'payment_')) {
                $grouped['payment'][] = $setting;
            } else {
                $grouped['system'][] = $setting;
            }
        }

        return $grouped;
    }

    /**
     * Simple encryption for sensitive data
     */
    private static function encrypt(string $value): string
    {
        try {
            $key = $_ENV['APP_KEY'] ?? 'peace-seafood-default-key';
            $iv = openssl_random_pseudo_bytes(16);
            $encrypted = openssl_encrypt($value, 'AES-256-CBC', $key, 0, $iv);
            return base64_encode($iv . $encrypted);
        } catch (\Exception $e) {
            error_log("Encryption error: " . $e->getMessage());
            return $value; // Fallback to plain if encryption fails
        }
    }

    /**
     * Decrypt sensitive data
     */
    public static function decrypt(string $encrypted): string
    {
        try {
            $key = $_ENV['APP_KEY'] ?? 'peace-seafood-default-key';
            $data = base64_decode($encrypted);
            $iv = substr($data, 0, 16);
            $ciphertext = substr($data, 16);
            return openssl_decrypt($ciphertext, 'AES-256-CBC', $key, 0, $iv);
        } catch (\Exception $e) {
            error_log("Decryption error: " . $e->getMessage());
            return ''; // Return empty if decryption fails
        }
    }

    /**
     * Get decrypted mail password for email sending
     */
    public static function getDecryptedMailPassword(): string
    {
        $encrypted = self::getSetting('mail_pass');
        if (empty($encrypted)) {
            return '';
        }
        return self::decrypt($encrypted);
    }
}

