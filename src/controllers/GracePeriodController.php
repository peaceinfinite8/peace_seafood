<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\WMS\GracePeriodService;
use App\Utils\Response;

/**
 * Grace Period Controller
 * Handles grace period banner and reminder endpoints
 */
class GracePeriodController
{
    /**
     * GET /api/grace-period/banner
     * Get banner data for current user's gudang
     */
    public function getBanner(): void
    {
        try {
            $user = AuthMiddleware::getAuthUser();
            
            // SaaS Owner doesn't have gudang, no banner
            if ($user['role'] === 'saas_owner' || empty($user['id_gudang'])) {
                Response::success(['show' => false]);
                return;
            }

            $idGudang = (int)$user['id_gudang'];
            $bannerData = GracePeriodService::getBannerData($idGudang);
            
            Response::success($bannerData);
        } catch (\Exception $e) {
            error_log("GracePeriodController::getBanner Error: " . $e->getMessage());
            Response::error('Gagal mengambil data banner: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/grace-period/check-reminders
     * Manually trigger reminder check (for testing or cron)
     * Only accessible by saas_owner/super_admin
     */
    public function checkReminders(): void
    {
        try {
            $user = AuthMiddleware::getAuthUser();
            
            if (!in_array($user['role'], ['saas_owner', 'super_admin'], true)) {
                Response::forbidden('Hanya SaaS Owner / Super Admin yang dapat menjalankan check reminders');
                return;
            }

            $result = GracePeriodService::checkAndSendReminders();
            
            if ($result['success']) {
                Response::success($result, "Berhasil memeriksa dan mengirim {$result['sent']} email reminder");
            } else {
                Response::error('Gagal memeriksa reminders: ' . ($result['error'] ?? 'Unknown error'), 500);
            }
        } catch (\Exception $e) {
            error_log("GracePeriodController::checkReminders Error: " . $e->getMessage());
            Response::error('Gagal menjalankan check reminders: ' . $e->getMessage(), 500);
        }
    }
}

