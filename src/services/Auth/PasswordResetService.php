<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Utils\Database;
use App\Utils\Email;
use Exception;

/**
 * Password Reset Service
 * Handles forgot password and reset password flows
 */
class PasswordResetService
{
    /**
     * Initiate password reset - send email with reset link
     * 
     * @param string $email User email
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendResetLink(string $email): array
    {
        try {
            // Check if user exists
            $user = Database::fetchOne(
                "SELECT id, name, email FROM users WHERE email = ? AND is_active = 1",
                [$email]
            );
            
            // Security: Always return success even if email doesn't exist (prevent email enumeration)
            if (!$user) {
                error_log("PasswordReset: Reset requested for non-existent email: {$email}");
                return [
                    'success' => true,
                    'message' => 'Jika email terdaftar, link reset password akan dikirim.'
                ];
            }
            
            // Check if there's a recent unused token (prevent spam)
            $recentToken = Database::fetchOne(
                "SELECT id FROM password_resets 
                 WHERE email = ? 
                   AND used_at IS NULL 
                   AND expires_at > NOW()
                   AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                 LIMIT 1",
                [$email]
            );
            
            if ($recentToken) {
                return [
                    'success' => true,
                    'message' => 'Link reset password sudah dikirim. Cek email Anda.'
                ];
            }
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store reset token
            Database::insert('password_resets', [
                'email' => $email,
                'token' => $token,
                'expires_at' => $expiresAt,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
            
            // Send reset email
            self::sendResetEmail($email, $user['name'], $token);
            
            return [
                'success' => true,
                'message' => 'Link reset password telah dikirim ke email Anda.'
            ];
            
        } catch (Exception $e) {
            error_log("PasswordResetService::sendResetLink - Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengirim link reset. Silakan coba lagi.'
            ];
        }
    }
    
    /**
     * Verify reset token validity
     * 
     * @param string $token Reset token
     * @return array ['valid' => bool, 'email' => string|null, 'message' => string]
     */
    public static function verifyToken(string $token): array
    {
        try {
            $reset = Database::fetchOne(
                "SELECT * FROM password_resets 
                 WHERE token = ? AND used_at IS NULL",
                [$token]
            );
            
            if (!$reset) {
                return [
                    'valid' => false,
                    'email' => null,
                    'message' => 'Token tidak valid atau sudah digunakan.'
                ];
            }
            
            // Check if expired
            if (strtotime($reset['expires_at']) < time()) {
                return [
                    'valid' => false,
                    'email' => null,
                    'message' => 'Token sudah kadaluarsa. Silakan request ulang.'
                ];
            }
            
            return [
                'valid' => true,
                'email' => $reset['email'],
                'message' => 'Token valid.'
            ];
            
        } catch (Exception $e) {
            error_log("PasswordResetService::verifyToken - Error: " . $e->getMessage());
            return [
                'valid' => false,
                'email' => null,
                'message' => 'Terjadi kesalahan. Silakan coba lagi.'
            ];
        }
    }
    
    /**
     * Reset password with token
     * 
     * @param string $token Reset token
     * @param string $newPassword New password
     * @return array ['success' => bool, 'message' => string]
     */
    public static function resetPassword(string $token, string $newPassword): array
    {
        try {
            // Verify token
            $verification = self::verifyToken($token);
            if (!$verification['valid']) {
                return [
                    'success' => false,
                    'message' => $verification['message']
                ];
            }
            
            $email = $verification['email'];
            
            // Validate password strength
            $validation = self::validatePasswordStrength($newPassword);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            
            // Update user password
            Database::update('users', ['email' => $email], [
                'password' => $hashedPassword
            ]);
            
            // Mark token as used
            Database::update('password_resets', ['token' => $token], [
                'used_at' => date('Y-m-d H:i:s')
            ]);
            
            // Log activity
            $user = Database::fetchOne("SELECT id, id_gudang FROM users WHERE email = ?", [$email]);
            if ($user) {
                Database::insert('activity_log', [
                    'id_user' => $user['id'],
                    'id_gudang' => $user['id_gudang'],
                    'action' => 'password_reset',
                    'description' => 'Password reset via forgot password flow',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                ]);
            }
            
            // Send confirmation email
            self::sendConfirmationEmail($email);
            
            return [
                'success' => true,
                'message' => 'Password berhasil direset. Silakan login dengan password baru.'
            ];
            
        } catch (Exception $e) {
            error_log("PasswordResetService::resetPassword - Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal reset password. Silakan coba lagi.'
            ];
        }
    }
    
    /**
     * Validate password strength
     */
    private static function validatePasswordStrength(string $password): array
    {
        if (strlen($password) < 8) {
            return ['valid' => false, 'message' => 'Password minimal 8 karakter.'];
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'message' => 'Password harus mengandung huruf besar.'];
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'message' => 'Password harus mengandung huruf kecil.'];
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'message' => 'Password harus mengandung angka.'];
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return ['valid' => false, 'message' => 'Password harus mengandung karakter spesial.'];
        }
        
        return ['valid' => true, 'message' => 'Password valid.'];
    }
    
    /**
     * Send reset email with link
     */
    private static function sendResetEmail(string $email, string $name, string $token): void
    {
        try {
            $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/peace_seafood';
            $resetLink = $baseUrl . '/reset-password?token=' . $token;
            
            $subject = '🔐 Reset Password - Peace Seafood WMS';
            
            ob_start();
            include BASE_PATH . '/src/views/emails/password_reset.php';
            $body = ob_get_clean();
            
            // Send immediately (critical email)
            Email::send($email, $subject, $body);
            
        } catch (Exception $e) {
            error_log("PasswordResetService::sendResetEmail - Error: " . $e->getMessage());
        }
    }
    
    /**
     * Send password reset confirmation email
     */
    private static function sendConfirmationEmail(string $email): void
    {
        try {
            $subject = '✅ Password Berhasil Direset';
            
            $body = "
            <p>Password Anda telah berhasil direset.</p>
            <p>Jika Anda tidak melakukan perubahan ini, segera hubungi administrator.</p>
            <p><strong>Informasi Keamanan:</strong></p>
            <ul>
                <li>Waktu: " . date('d M Y H:i') . " WIB</li>
                <li>IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "</li>
            </ul>
            ";
            
            // Queue this email (non-critical)
            Email::queue($email, $subject, $body);
            
        } catch (Exception $e) {
            error_log("PasswordResetService::sendConfirmationEmail - Error: " . $e->getMessage());
        }
    }
    
    /**
     * Cleanup expired tokens
     * 
     * @return int Number of tokens deleted
     */
    public static function cleanupExpiredTokens(): int
    {
        try {
            $result = Database::query(
                "DELETE FROM password_resets WHERE expires_at < NOW()"
            );
            
            return $result->rowCount();
            
        } catch (Exception $e) {
            error_log("PasswordResetService::cleanupExpiredTokens - Error: " . $e->getMessage());
            return 0;
        }
    }
}
