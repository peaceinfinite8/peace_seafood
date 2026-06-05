<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Utils\Database;
use App\Utils\Email;
use Exception;

/**
 * Payment Approval Service
 * Handles payment proof submission, magic link generation, and approval workflow
 */
class PaymentApprovalService
{
    /**
     * Submit payment proof from tenant
     * 
     * @param int $idGudang
     * @param int $idBos
     * @param array $fileData ['name', 'type', 'tmp_name', 'size']
     * @return array ['success' => bool, 'message' => string, 'request_id' => int]
     */
    public static function submitPaymentProof(int $idGudang, int $idBos, array $fileData): array
    {
        try {
            // 1. Get settings
            $settings = self::getPaymentSettings();
            
            // 2. Validate file
            $validation = self::validateFile($fileData, $settings);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            
            // 3. Upload file
            $uploadResult = self::uploadFile($fileData, $idGudang);
            if (!$uploadResult['success']) {
                return ['success' => false, 'message' => $uploadResult['message']];
            }
            
            // 4. Generate magic token
            $magicToken = bin2hex(random_bytes(32));
            $expiryHours = (int)$settings['magic_link_expiry_hours'];
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiryHours} hours"));
            
            // 5. Save to database
            $nominal = (float)$settings['monthly_price'];
            $requestId = Database::insert('payment_requests', [
                'id_gudang' => $idGudang,
                'id_bos' => $idBos,
                'nominal' => $nominal,
                'bukti_transfer_path' => $uploadResult['path'],
                'status' => 'pending',
                'magic_token' => $magicToken,
                'magic_token_expires_at' => $expiresAt
            ]);
            
            // 6. Send email to SaaS Owner
            self::sendMagicLinkEmail($idGudang, $idBos, $nominal, $uploadResult['path'], $magicToken);
            
            // 7. Create notification for SaaS Owner
            self::createOwnerNotification($idGudang, $requestId);
            
            return [
                'success' => true,
                'message' => 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi.',
                'request_id' => $requestId
            ];
            
        } catch (Exception $e) {
            error_log("PaymentApprovalService::submitPaymentProof - Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengirim bukti pembayaran: ' . $e->getMessage()];
        }
    }
    
    /**
     * Approve payment via Magic Link or manual
     * 
     * @param string $magicToken
     * @return array ['success' => bool, 'message' => string, 'gudang_name' => string]
     */
    public static function approvePayment(string $magicToken): array
    {
        // Start transaction to prevent race conditions
        Database::beginTransaction();
        
        try {
            // 1. Find payment request with row locking (FOR UPDATE)
            $request = Database::fetchOne(
                "SELECT pr.*, g.nama_gudang, u.name as nama_bos, u.email as email_bos
                 FROM payment_requests pr
                 JOIN gudang g ON pr.id_gudang = g.id
                 JOIN users u ON pr.id_bos = u.id
                 WHERE pr.magic_token = ? AND pr.status = 'pending'
                 FOR UPDATE",
                [$magicToken]
            );
            
            if (!$request) {
                Database::rollBack();
                return ['success' => false, 'message' => 'Token tidak valid atau sudah digunakan.'];
            }
            
            // 2. Check expiry
            if (strtotime($request['magic_token_expires_at']) < time()) {
                Database::rollBack();
                return ['success' => false, 'message' => 'Token sudah kadaluarsa. Silakan approve manual dari dashboard.'];
            }
            
            // 3. Check if already used
            if ($request['magic_token_used_at']) {
                Database::rollBack();
                return ['success' => false, 'message' => 'Token sudah pernah digunakan.'];
            }
            
            // 4. Get renewal duration from settings
            $renewalDays = Database::fetchOne(
                "SELECT nilai FROM settings WHERE kunci = 'renewal_duration_days' AND id_gudang IS NULL"
            );
            $days = (int)($renewalDays['nilai'] ?? 30);
            
            // 5. Calculate new subscription date
            $currentExpiry = Database::fetchOne(
                "SELECT subscription_until FROM gudang WHERE id = ?",
                [$request['id_gudang']]
            );
            
            $baseDate = $currentExpiry['subscription_until'] ?? date('Y-m-d H:i:s');
            if (strtotime($baseDate) < time()) {
                $baseDate = date('Y-m-d H:i:s'); // Start from now if already expired
            }
            $newExpiry = date('Y-m-d H:i:s', strtotime($baseDate . " +{$days} days"));
            
            // 6. Update payment request
            Database::update('payment_requests', ['id' => $request['id']], [
                'status' => 'approved',
                'magic_token_used_at' => date('Y-m-d H:i:s'),
                'approved_at' => date('Y-m-d H:i:s'),
                'approved_by' => self::getSaasOwnerId()
            ]);
            
            // 7. Update gudang subscription
            Database::update('gudang', ['id' => $request['id_gudang']], [
                'subscription_until' => $newExpiry,
                'status_langganan' => 'active'
            ]);
            
            // 8. Create notification for Bos
            \App\Services\Shared\NotificationService::createNotification(
                $request['id_bos'],
                'payment_approved',
                'Pembayaran Disetujui',
                "Masa aktif gudang {$request['nama_gudang']} telah diperpanjang hingga " . date('d M Y', strtotime($newExpiry)),
                null,
                $request['id_gudang']
            );
            
            // 9. Send email to Bos
            self::sendApprovalEmail($request['email_bos'], $request['nama_gudang'], $newExpiry);
            
            // 10. Log activity
            self::logAudit($request['id_gudang'], "Payment approved for gudang {$request['nama_gudang']}, extended until {$newExpiry}");
            
            // Commit transaction
            Database::commit();
            
            return [
                'success' => true,
                'message' => 'Pembayaran disetujui dan akses gudang dipulihkan.',
                'gudang_name' => $request['nama_gudang']
            ];
            
        } catch (Exception $e) {
            Database::rollBack();
            error_log("PaymentApprovalService::approvePayment - Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyetujui pembayaran: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get payment requests list (for SaaS Owner dashboard)
     */
    public static function getPaymentRequests(string $status = 'all', int $limit = 50): array
    {
        try {
            $sql = "SELECT pr.*, g.nama_gudang, u.name as nama_bos, u.email as email_bos
                    FROM payment_requests pr
                    JOIN gudang g ON pr.id_gudang = g.id
                    JOIN users u ON pr.id_bos = u.id";
            
            $params = [];
            if ($status !== 'all') {
                $sql .= " WHERE pr.status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY pr.created_at DESC LIMIT ?";
            $params[] = $limit;
            
            return Database::fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("PaymentApprovalService::getPaymentRequests - Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validate uploaded file with security checks
     */
    private static function validateFile(array $fileData, array $settings): array
    {
        // Check if file exists
        if (empty($fileData['tmp_name']) || !is_uploaded_file($fileData['tmp_name'])) {
            return ['valid' => false, 'message' => 'File tidak ditemukan.'];
        }
        
        // Check file size
        $maxSize = (int)$settings['max_upload_mb'] * 1024 * 1024; // Convert MB to bytes
        if ($fileData['size'] > $maxSize) {
            return ['valid' => false, 'message' => "Ukuran file maksimal {$settings['max_upload_mb']} MB."];
        }
        
        // Check file extension
        $allowedFormats = explode(',', $settings['allowed_formats']);
        $extension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedFormats)) {
            return ['valid' => false, 'message' => "Format file harus: " . implode(', ', $allowedFormats)];
        }
        
        // Security Check 1: Validate MIME type (not just extension)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileData['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimeTypes = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'application/pdf' => ['pdf']
        ];
        
        $isValidMime = false;
        foreach ($allowedMimeTypes as $mime => $exts) {
            if ($mimeType === $mime && in_array($extension, $exts)) {
                $isValidMime = true;
                break;
            }
        }
        
        if (!$isValidMime) {
            error_log("File upload security: MIME type mismatch. Detected: {$mimeType}, Extension: {$extension}");
            return ['valid' => false, 'message' => 'File tidak valid. Tipe file tidak sesuai dengan ekstensi.'];
        }
        
        // Security Check 2: For images, verify it's actually an image by trying to load it
        if (str_starts_with($mimeType, 'image/')) {
            $imageInfo = @getimagesize($fileData['tmp_name']);
            if ($imageInfo === false) {
                error_log("File upload security: File claims to be image but getimagesize() failed");
                return ['valid' => false, 'message' => 'File gambar tidak valid atau corrupt.'];
            }
        }
        
        // Security Check 3: Check for PHP code injection in file content
        $fileContent = file_get_contents($fileData['tmp_name']);
        if (preg_match('/<\?php|<\?=|<script/i', $fileContent)) {
            error_log("File upload security: Potential code injection detected");
            return ['valid' => false, 'message' => 'File ditolak karena alasan keamanan.'];
        }
        
        // Security Check 4: Check file name for malicious patterns
        $fileName = basename($fileData['name']);
        if (preg_match('/\.(php|phtml|php3|php4|php5|phar|exe|sh|bat|cmd)$/i', $fileName)) {
            error_log("File upload security: Dangerous file extension in name: {$fileName}");
            return ['valid' => false, 'message' => 'Nama file tidak diperbolehkan.'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Upload file to storage with security measures
     */
    private static function uploadFile(array $fileData, int $idGudang): array
    {
        try {
            $uploadDir = BASE_PATH . '/storage/uploads/payment_proofs';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
            $filename = $idGudang . '_' . time() . '_' . uniqid() . '.' . $extension;
            $targetPath = $uploadDir . '/' . $filename;
            
            // Security: For images, strip metadata and re-encode to remove potential exploits
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fileData['tmp_name']);
            finfo_close($finfo);
            
            if (str_starts_with($mimeType, 'image/')) {
                // Load image, strip metadata, re-save
                $image = null;
                
                if ($mimeType === 'image/jpeg') {
                    $image = @imagecreatefromjpeg($fileData['tmp_name']);
                } elseif ($mimeType === 'image/png') {
                    $image = @imagecreatefrompng($fileData['tmp_name']);
                }
                
                if ($image !== false && $image !== null) {
                    // Re-save image without metadata
                    if ($mimeType === 'image/jpeg') {
                        imagejpeg($image, $targetPath, 90);
                    } elseif ($mimeType === 'image/png') {
                        imagepng($image, $targetPath, 9);
                    }
                    imagedestroy($image);
                    
                    error_log("File upload: Stripped metadata from image {$filename}");
                } else {
                    // Fallback to normal upload if image processing fails
                    if (!move_uploaded_file($fileData['tmp_name'], $targetPath)) {
                        return ['success' => false, 'message' => 'Gagal mengupload file.'];
                    }
                }
            } else {
                // PDF or other non-image files - normal upload
                if (!move_uploaded_file($fileData['tmp_name'], $targetPath)) {
                    return ['success' => false, 'message' => 'Gagal mengupload file.'];
                }
            }
            
            // Set secure file permissions (read-only for web server)
            chmod($targetPath, 0644);
            
            return ['success' => true, 'path' => 'storage/uploads/payment_proofs/' . $filename];
            
        } catch (Exception $e) {
            error_log("PaymentApprovalService::uploadFile - Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengupload file: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get payment settings from database
     */
    private static function getPaymentSettings(): array
    {
        $keys = [
            'payment_monthly_price',
            'payment_max_upload_mb',
            'payment_allowed_formats',
            'payment_magic_link_expiry_hours',
            'payment_bank_name',
            'payment_bank_holder',
            'payment_bank_number',
            'payment_qris_image'
        ];
        
        $settings = [];
        foreach ($keys as $key) {
            $result = Database::fetchOne(
                "SELECT nilai FROM settings WHERE kunci = ? AND id_gudang IS NULL",
                [$key]
            );
            $keyShort = str_replace('payment_', '', $key);
            $settings[$keyShort] = $result['nilai'] ?? '';
        }
        
        // Set defaults if empty
        $settings['monthly_price'] = $settings['monthly_price'] ?: '500000';
        $settings['max_upload_mb'] = $settings['max_upload_mb'] ?: '5';
        $settings['allowed_formats'] = $settings['allowed_formats'] ?: 'jpg,png,pdf';
        $settings['magic_link_expiry_hours'] = $settings['magic_link_expiry_hours'] ?: '24';
        
        return $settings;
    }
    
    /**
     * Send magic link email to SaaS Owner
     */
    private static function sendMagicLinkEmail(int $idGudang, int $idBos, float $nominal, string $proofPath, string $token): void
    {
        try {
            $gudang = Database::fetchOne("SELECT nama_gudang FROM gudang WHERE id = ?", [$idGudang]);
            $bos = Database::fetchOne("SELECT name, email FROM users WHERE id = ?", [$idBos]);
            
            $ownerEmail = Database::fetchOne(
                "SELECT email FROM users WHERE role = 'saas_owner' LIMIT 1"
            );
            
            if (!$ownerEmail) {
                error_log("PaymentApprovalService::sendMagicLinkEmail - SaaS Owner email not found");
                return;
            }
            
            $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/peace_seafood';
            $magicLink = $baseUrl . '/api/saas/approve-payment?token=' . $token;
            $proofUrl = $baseUrl . '/' . $proofPath;
            
            $subject = '🐟 Permintaan Pembayaran Baru - ' . $gudang['nama_gudang'];
            
            ob_start();
            include BASE_PATH . '/src/views/emails/payment_request_owner.php';
            $body = ob_get_clean();
            
            Email::send($ownerEmail['email'], $subject, $body);
            
        } catch (Exception $e) {
            error_log("PaymentApprovalService::sendMagicLinkEmail - Error: " . $e->getMessage());
        }
    }
    
    /**
     * Send approval confirmation email to Bos
     */
    private static function sendApprovalEmail(string $email, string $gudangName, string $newExpiry): void
    {
        try {
            $subject = '✅ Pembayaran Disetujui - Akses Gudang Dipulihkan';
            
            ob_start();
            include BASE_PATH . '/src/views/emails/payment_approved.php';
            $body = ob_get_clean();
            
            Email::send($email, $subject, $body);
            
        } catch (Exception $e) {
            error_log("PaymentApprovalService::sendApprovalEmail - Error: " . $e->getMessage());
        }
    }
    
    /**
     * Create notification for SaaS Owner
     */
    private static function createOwnerNotification(int $idGudang, int $requestId): void
    {
        try {
            $gudang = Database::fetchOne("SELECT nama_gudang FROM gudang WHERE id = ?", [$idGudang]);
            $owner = Database::fetchOne("SELECT id FROM users WHERE role = 'saas_owner' LIMIT 1");
            
            if ($owner) {
                \App\Services\Shared\NotificationService::createNotification(
                    $owner['id'],
                    'payment_request',
                    'Permintaan Pembayaran Baru',
                    "Gudang {$gudang['nama_gudang']} mengirim bukti pembayaran. Klik untuk verifikasi.",
                    "/saas/payments?request_id={$requestId}",
                    null
                );
            }
        } catch (Exception $e) {
            error_log("PaymentApprovalService::createOwnerNotification - Error: " . $e->getMessage());
        }
    }
    
    /**
     * Log audit trail
     */
    private static function logAudit(int $idGudang, string $message): void
    {
        try {
            $ownerId = self::getSaasOwnerId();
            Database::insert('activity_log', [
                'id_user' => $ownerId,
                'id_gudang' => $idGudang,
                'action' => 'payment_approved',
                'description' => $message,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
            ]);
        } catch (Exception $e) {
            error_log("PaymentApprovalService::logAudit - Error: " . $e->getMessage());
        }
    }
    
    /**
     * Get SaaS Owner ID dynamically
     * 
     * @return int|null
     */
    private static function getSaasOwnerId(): ?int
    {
        static $ownerId = null;
        
        if ($ownerId === null) {
            try {
                $owner = Database::fetchOne(
                    "SELECT id FROM users WHERE role = 'saas_owner' LIMIT 1"
                );
                $ownerId = $owner ? (int)$owner['id'] : null;
            } catch (Exception $e) {
                error_log("PaymentApprovalService::getSaasOwnerId - Error: " . $e->getMessage());
                return null;
            }
        }
        
        return $ownerId;
    }
}
