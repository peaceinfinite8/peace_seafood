<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Middleware\AuthMiddleware;
use App\Utils\Helper;
use App\Utils\Response;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * POST /auth/login
     */
    public function login(): void
    {
        $body = Helper::getRequestBody();

        if (empty($body['email']) || empty($body['password'])) {
            Response::error(422, 'VALIDATION_ERROR', 'Email dan password wajib diisi');
        }

        $result = $this->authService->login($body['email'], $body['password']);

        if (!$result) {
            Response::unauthorized('Email atau password salah');
        }

        // Set HttpOnly cookie for web router server-side auth guard
        \App\Utils\JWT::setHttpOnlyCookie($result['token']);

        Response::success($result, 'Login berhasil');
    }

    /**
     * POST /auth/logout
     */
    public function logout(): void
    {
        // Clear HttpOnly cookie on logout
        \App\Utils\JWT::clearCookie();
        Response::success(null, 'Logout berhasil');
    }

    /**
     * GET /auth/profile
     */
    public function profile(): void
    {
        $user   = AuthMiddleware::getAuthUser();
        $userId = (int)$user['id'];
        
        $fullProfile = $this->authService->getProfile($userId);
        
        if (!$fullProfile) {
            Response::notFound('User tidak ditemukan');
        }
        
        Response::success($fullProfile);
    }

    /**
     * POST /auth/signup
     */
    public function signup(): void
    {
        $body = \App\Utils\Helper::getRequestBody();

        if (empty($body['email'])) {
            Response::error('Email wajib diisi.', 422);
        }

        $email = trim($body['email']);

        // Check if pre-approved Bos exists
        $user = \App\Utils\Database::fetchOne(
            "SELECT id, name FROM users WHERE email = ? AND registration_status = 'pending_signup'",
            [$email]
        );

        if (!$user) {
            Response::error('Email Anda belum disetujui oleh Developer. Silakan hubungi kami untuk pendaftaran!', 403);
        }

        // Generate strong secure random default password
        $randomCode = strtoupper(bin2hex(random_bytes(3))); // 6 random chars
        $defaultPassword = "Ps#" . $randomCode . "!";
        $hashedPassword = password_hash($defaultPassword, PASSWORD_BCRYPT);

        // Activate the user
        \App\Utils\Database::update('users', [
            'password' => $hashedPassword,
            'is_first_login' => 1,
            'registration_status' => 'active',
            'is_active' => 1
        ], 'id = ?', [$user['id']]);

        // Send Welcome email with default password
        $emailBody = "Halo " . $user['name'] . ",\n\n" .
                     "Pendaftaran akun Bos Anda di Peace Seafood telah sukses disetujui!\n" .
                     "Berikut adalah informasi login sementara Anda:\n\n" .
                     "Email: " . $email . "\n" .
                     "Password Sementara: " . $defaultPassword . "\n\n" .
                     "Silakan gunakan informasi di atas untuk masuk. Demi keamanan, Anda wajib mengubah password sementara tersebut saat login pertama kali.\n\n" .
                     "Salam hangat,\n" .
                     "Peace Seafood Team";

        \App\Utils\Email::send($email, 'Aktivasi Akun Bos Peace Seafood Sukses', $emailBody);

        Response::success(null, 'Pendaftaran berhasil! Password default telah dikirim ke email Gmail Anda.');
    }

    /**
     * POST /auth/change-password
     */
    public function changePassword(): void
    {
        $user = AuthMiddleware::getAuthUser();
        $body = \App\Utils\Helper::getRequestBody();

        if (empty($body['password'])) {
            Response::error('Password baru wajib diisi.', 422);
        }

        $password = $body['password'];

        // Strict Password Strength Check
        $hasUppercase = preg_match('/[A-Z]/', $password);
        $hasLowercase = preg_match('/[a-z]/', $password);
        $hasNumber    = preg_match('/[0-9]/', $password);
        $hasSpecial   = preg_match('/[^a-zA-Z0-9]/', $password);
        $isValidLength = strlen($password) >= 8;

        if (!$hasUppercase || !$hasLowercase || !$hasNumber || !$hasSpecial || !$isValidLength) {
            Response::error('Sandi lemah! Password wajib minimal 8 karakter yang mengandung huruf besar, huruf kecil, angka, dan karakter khusus.', 422);
        }

        // Save new hashed password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        \App\Utils\Database::update('users', [
            'password' => $hashedPassword,
            'is_first_login' => 0
        ], 'id = ?', [$user['id']]);

        Response::success(null, 'Password berhasil diperbarui secara aman!');
    }

    /**
     * POST /auth/forgot-password
     */
    public function forgotPassword(): void
    {
        $body = \App\Utils\Helper::getRequestBody();

        if (empty($body['email'])) {
            Response::error('Email wajib diisi.', 422);
        }

        $email = trim($body['email']);

        // Query database
        $user = \App\Utils\Database::fetchOne("SELECT id, name FROM users WHERE email = ? AND is_active = 1", [$email]);

        if (!$user) {
            // Secure response to prevent user enumeration
            Response::success(null, 'Instruksi reset sandi telah dikirim ke email Anda jika terdaftar.');
        }

        // Generate reset token and expires in 30 minutes
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 1800);

        \App\Utils\Database::update('users', [
            'reset_token' => $token,
            'reset_token_expires_at' => $expiresAt
        ], 'id = ?', [$user['id']]);

        // Send Link via Email
        $config = require dirname(__DIR__, 2) . '/config/app.php';
        $basePath = $config['base_path'];
        $resetLink = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost:8888') . "{$basePath}/reset-password?token=" . $token;
        $emailBody = "Halo " . $user['name'] . ",\n\n" .
                     "Kami menerima permintaan untuk mereset password akun Peace Seafood Anda.\n" .
                     "Silakan klik link di bawah ini untuk mengatur ulang sandi Anda:\n\n" .
                     $resetLink . "\n\n" .
                     "Link di atas hanya berlaku selama 30 menit. Jika Anda tidak merasa meminta hal ini, silakan abaikan email ini.\n\n" .
                     "Salam hangat,\n" .
                     "Peace Seafood Team";

        \App\Utils\Email::send($email, 'Permintaan Reset Password Peace Seafood', $emailBody);

        Response::success(null, 'Instruksi reset sandi telah dikirim ke email Anda.');
    }

    /**
     * POST /auth/reset-password
     */
    public function resetPassword(): void
    {
        $body = \App\Utils\Helper::getRequestBody();

        if (empty($body['token']) || empty($body['password'])) {
            Response::error('Token dan password baru wajib diisi.', 422);
        }

        $token = trim($body['token']);
        $password = $body['password'];

        // Verify token expiry
        $user = \App\Utils\Database::fetchOne(
            "SELECT id FROM users WHERE reset_token = ? AND reset_token_expires_at > CURRENT_TIMESTAMP AND is_active = 1",
            [$token]
        );

        if (!$user) {
            Response::error('Token reset tidak valid atau sudah kedaluwarsa.', 422);
        }

        // Strict Password Strength Check
        $hasUppercase = preg_match('/[A-Z]/', $password);
        $hasLowercase = preg_match('/[a-z]/', $password);
        $hasNumber    = preg_match('/[0-9]/', $password);
        $hasSpecial   = preg_match('/[^a-zA-Z0-9]/', $password);
        $isValidLength = strlen($password) >= 8;

        if (!$hasUppercase || !$hasLowercase || !$hasNumber || !$hasSpecial || !$isValidLength) {
            Response::error('Sandi lemah! Password wajib minimal 8 karakter yang mengandung huruf besar, huruf kecil, angka, dan karakter khusus.', 422);
        }

        // Reset password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        \App\Utils\Database::update('users', [
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_token_expires_at' => null
        ], 'id = ?', [$user['id']]);

        Response::success(null, 'Password berhasil direset! Silakan masuk kembali.');
    }

    /**
     * POST /auth/impersonate - Developer enters tenant session (God Mode)
     */
    public function impersonate(): void
    {
        $user = AuthMiddleware::getAuthUser();
        
        // Strictly check if current logged in user is saas_owner
        if ($user['role'] !== 'saas_owner') {
            Response::forbidden('Hanya SaaS Owner / Developer yang dapat menggunakan fitur Impersonate.');
        }

        $body = \App\Utils\Helper::getRequestBody();
        if (empty($body['user_id'])) {
            Response::error('User ID wajib diisi.', 422);
        }

        $targetUserId = (int)$body['user_id'];

        // Retrieve target user
        $targetUser = \App\Utils\Database::fetchOne(
            "SELECT id, name, email, role, id_gudang, is_active FROM users WHERE id = ?",
            [$targetUserId]
        );

        if (!$targetUser || !$targetUser['is_active']) {
            Response::notFound('User target tidak ditemukan atau tidak aktif.');
        }

        // Generate JWT token for target user
        $token = \App\Utils\JWT::generate([
            'id'        => $targetUser['id'],
            'name'      => $targetUser['name'],
            'email'     => $targetUser['email'],
            'role'      => $targetUser['role'],
            'id_gudang' => $targetUser['id_gudang']
        ]);

        // Set HttpOnly cookie
        \App\Utils\JWT::setHttpOnlyCookie($token);

        // Record Audit log
        \App\Utils\ActivityLogHelper::log(
            'IMPERSONATE',
            'users',
            $targetUser['id'],
            ['developer_id' => $user['id']],
            ['impersonated_user' => $targetUser['name']]
        );

        Response::success([
            'token' => $token,
            'user'  => $targetUser
        ], 'Impersonation sukses! Anda sekarang masuk sebagai ' . $targetUser['name']);
    }
}
