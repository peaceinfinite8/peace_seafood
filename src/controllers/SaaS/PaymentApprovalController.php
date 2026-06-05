<?php

declare(strict_types=1);

namespace App\Controllers\SaaS;

use App\Services\SaaS\PaymentApprovalService;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;
use App\Utils\Database;

/**
 * Payment Approval Controller
 * Handles payment proof submission and magic link approval
 */
class PaymentApprovalController
{
    /**
     * Submit payment proof (POST /api/wms/submit-payment)
     * Accessible by: bos (when locked)
     */
    public function submitPayment(): void
    {
        try {
            $user = AuthMiddleware::user();
            
            // Only Bos can submit payment
            if ($user['role'] !== 'bos') {
                Response::forbidden('Hanya pemilik gudang yang dapat mengirim bukti pembayaran.');
            }
            
            // Get gudang
            $gudang = Database::fetchOne(
                "SELECT id, nama_gudang, id_bos FROM gudang WHERE id_bos = ?",
                [$user['id']]
            );
            
            if (!$gudang) {
                Response::error('Gudang tidak ditemukan.', 404);
            }
            
            // Validate file upload
            if (empty($_FILES['bukti_transfer'])) {
                Response::error('File bukti pembayaran wajib diupload.', 400);
            }
            
            $file = $_FILES['bukti_transfer'];
            
            // Call service
            $result = PaymentApprovalService::submitPaymentProof(
                (int)$gudang['id'],
                (int)$user['id'],
                $file
            );
            
            if ($result['success']) {
                Response::success($result['message'], ['request_id' => $result['request_id']]);
            } else {
                Response::error($result['message'], 400);
            }
            
        } catch (\Exception $e) {
            error_log("PaymentApprovalController::submitPayment - Error: " . $e->getMessage());
            Response::error('Terjadi kesalahan saat mengirim bukti pembayaran.', 500);
        }
    }
    
    /**
     * Approve payment via magic link (GET /api/saas/approve-payment?token=xxx)
     * Public endpoint (no auth required)
     */
    public function approvePaymentMagicLink(): void
    {
        try {
            $token = $_GET['token'] ?? '';
            
            if (empty($token)) {
                Response::error('Token tidak ditemukan.', 400);
            }
            
            $result = PaymentApprovalService::approvePayment($token);
            
            if ($result['success']) {
                // Redirect to success page
                $this->showApprovalSuccessPage($result['gudang_name']);
            } else {
                // Redirect to error page
                $this->showApprovalErrorPage($result['message']);
            }
            
        } catch (\Exception $e) {
            error_log("PaymentApprovalController::approvePaymentMagicLink - Error: " . $e->getMessage());
            $this->showApprovalErrorPage('Terjadi kesalahan sistem. Silakan hubungi administrator.');
        }
    }
    
    /**
     * Get payment requests list (GET /api/saas/payment-requests)
     * Accessible by: saas_owner only
     */
    public function getPaymentRequests(): void
    {
        try {
            $user = AuthMiddleware::handle();
            
            if ($user['role'] !== 'saas_owner') {
                Response::forbidden('Akses ditolak. Hanya SaaS Owner yang dapat melihat data ini.');
            }
            
            $status = $_GET['status'] ?? 'all'; // all, pending, approved, rejected
            $limit = (int)($_GET['limit'] ?? 50);
            
            $requests = PaymentApprovalService::getPaymentRequests($status, $limit);
            
            Response::success('Data berhasil diambil.', ['requests' => $requests]);
            
        } catch (\Exception $e) {
            error_log("PaymentApprovalController::getPaymentRequests - Error: " . $e->getMessage());
            Response::error('Terjadi kesalahan saat mengambil data.', 500);
        }
    }
    
    /**
     * Get payment settings for lock screen (GET /api/wms/payment-settings)
     * Public endpoint for locked tenants
     */
    public function getPaymentSettings(): void
    {
        try {
            $settings = Database::fetchAll(
                "SELECT kunci, nilai FROM settings 
                 WHERE kunci IN (
                     'payment_bank_name',
                     'payment_bank_holder', 
                     'payment_bank_number',
                     'payment_qris_image',
                     'payment_monthly_price',
                     'payment_max_upload_mb',
                     'payment_allowed_formats',
                     'lock_screen_title',
                     'lock_screen_message',
                     'lock_screen_payment_steps',
                     'lock_screen_after_submit',
                     'platform_whatsapp'
                 ) AND id_gudang IS NULL"
            );
            
            $data = [];
            foreach ($settings as $setting) {
                $data[$setting['kunci']] = $setting['nilai'];
            }
            
            Response::success('Settings berhasil diambil.', $data);
            
        } catch (\Exception $e) {
            error_log("PaymentApprovalController::getPaymentSettings - Error: " . $e->getMessage());
            Response::error('Terjadi kesalahan saat mengambil settings.', 500);
        }
    }
    
    /**
     * Show approval success page (HTML)
     */
    private function showApprovalSuccessPage(string $gudangName): void
    {
        $pageTitle = 'Pembayaran Disetujui';
        $message = "Pembayaran untuk gudang <strong>{$gudangName}</strong> telah disetujui dan akses telah dipulihkan.";
        include BASE_PATH . '/src/views/saas/approval_result.php';
        exit;
    }
    
    /**
     * Show approval error page (HTML)
     */
    private function showApprovalErrorPage(string $errorMessage): void
    {
        $pageTitle = 'Approval Gagal';
        $message = $errorMessage;
        $isError = true;
        include BASE_PATH . '/src/views/saas/approval_result.php';
        exit;
    }
}
