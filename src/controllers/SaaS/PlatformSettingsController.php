<?php

declare(strict_types=1);

namespace App\Controllers\SaaS;

use App\Middleware\AuthMiddleware;
use App\Services\SaaS\PlatformSettingsService;
use App\Utils\Helper;
use App\Utils\Response;

/**
 * Platform Settings Controller
 * Manages SaaS platform configuration
 */
class PlatformSettingsController
{
    /**
     * GET /api/saas/settings
     * Get all platform settings
     */
    public function index(): void
    {
        try {
            $user = AuthMiddleware::getAuthUser();

            if ($user['role'] !== 'saas_owner') {
                Response::forbidden('Hanya SaaS Owner yang dapat mengakses pengaturan platform');
                return;
            }

            $settings = PlatformSettingsService::getAllSettings();
            Response::success($settings);
        } catch (\Exception $e) {
            error_log("PlatformSettingsController::index Error: " . $e->getMessage());
            Response::error('Gagal mengambil pengaturan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/saas/settings/save
     * Save platform settings (single or multiple)
     */
    public function save(): void
    {
        try {
            $user = AuthMiddleware::getAuthUser();

            if ($user['role'] !== 'saas_owner') {
                Response::forbidden('Hanya SaaS Owner yang dapat mengubah pengaturan platform');
                return;
            }

            $body = Helper::getRequestBody();

            if (empty($body)) {
                Response::error('Data pengaturan tidak boleh kosong', 422);
                return;
            }

            // Check if single setting or multiple
            if (isset($body['key']) && isset($body['value'])) {
                // Single setting
                $success = PlatformSettingsService::updateSetting($body['key'], $body['value']);
            } else {
                // Multiple settings
                $success = PlatformSettingsService::updateMultiple($body);
            }

            if ($success) {
                Response::success(null, 'Pengaturan berhasil disimpan');
            } else {
                Response::error('Gagal menyimpan pengaturan', 500);
            }
        } catch (\Exception $e) {
            error_log("PlatformSettingsController::save Error: " . $e->getMessage());
            Response::error('Gagal menyimpan pengaturan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/saas/settings/upload
     * Upload image (logo or QRIS)
     */
    public function upload(): void
    {
        try {
            $user = AuthMiddleware::getAuthUser();

            if ($user['role'] !== 'saas_owner') {
                Response::forbidden('Hanya SaaS Owner yang dapat mengupload gambar');
                return;
            }

            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                Response::error('File gambar tidak valid', 422);
                return;
            }

            if (!isset($_POST['setting_key'])) {
                Response::error('Setting key tidak boleh kosong', 422);
                return;
            }

            $settingKey = $_POST['setting_key'];
            $allowedKeys = ['platform_logo', 'payment_qris_image'];

            if (!in_array($settingKey, $allowedKeys)) {
                Response::error('Setting key tidak valid', 422);
                return;
            }

            $result = PlatformSettingsService::uploadImage($_FILES['image'], $settingKey);

            if ($result['success']) {
                Response::success($result['data'] ?? null, $result['message']);
            } else {
                Response::error($result['message'], 422);
            }
        } catch (\Exception $e) {
            error_log("PlatformSettingsController::upload Error: " . $e->getMessage());
            Response::error('Gagal mengupload gambar: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/saas/settings/test-email
     * Send test email to verify SMTP configuration
     */
    public function testEmail(): void
    {
        try {
            $user = AuthMiddleware::getAuthUser();

            if ($user['role'] !== 'saas_owner') {
                Response::forbidden('Hanya SaaS Owner yang dapat melakukan test email');
                return;
            }

            $body = Helper::getRequestBody();
            $recipientEmail = $body['email'] ?? $user['email'] ?? '';

            if (empty($recipientEmail)) {
                Response::error('Email penerima tidak boleh kosong', 422);
                return;
            }

            // Validate email format
            if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                Response::error('Format email tidak valid', 422);
                return;
            }

            $result = PlatformSettingsService::testEmail($recipientEmail);

            if ($result['success']) {
                Response::success(null, $result['message']);
            } else {
                Response::error($result['message'], 500);
            }
        } catch (\Exception $e) {
            error_log("PlatformSettingsController::testEmail Error: " . $e->getMessage());
            Response::error('Gagal mengirim test email: ' . $e->getMessage(), 500);
        }
    }
}

