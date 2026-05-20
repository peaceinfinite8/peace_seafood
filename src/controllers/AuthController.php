<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Middleware\AuthMiddleware;
use App\Utils\Helper;
use App\Utils\Response;
use App\Utils\Session;
use App\Utils\JWT;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
        
        // Initialize session
        Session::init();
    }

    /**
     * POST /auth/login
     */
    public function login(): void
    {
        $body = Helper::getRequestBody();
        $identifier = trim((string) ($body['email'] ?? $body['username'] ?? ''));

        if ($identifier === '' || empty($body['password'])) {
            Response::error(422, 'VALIDATION_ERROR', 'Email dan password wajib diisi');
        }

        $result = $this->authService->login($identifier, $body['password']);

        if (!$result) {
            Response::unauthorized('Email atau password salah');
        }

        // Store user data in session
        Session::set('user_id', $result['user']['id']);
        Session::set('user_email', $result['user']['email']);
        Session::set('user_role', $result['user']['role']);
        Session::set('user_name', $result['user']['name']);
        Session::set('id_gudang', $result['user']['id_gudang']);
        Session::set('authenticated', true);
        
        // Regenerate session ID for security
        Session::regenerate();

        // Set HTTP-only cookie with token (30 minutes = 1800 seconds)
        JWT::setHttpOnlyCookie($result['token'], 1800);

        // Add session info to response
        $result['session'] = [
            'timeout_minutes' => 30,
            'expires_at' => date('Y-m-d H:i:s', time() + 1800),
        ];

        Response::success($result, 'Login berhasil');
    }

    /**
     * POST /auth/logout
     */
    public function logout(): void
    {
        // Clear session
        Session::destroy();
        
        // Clear auth cookie
        JWT::clearCookie();
        
        Response::success(null, 'Logout berhasil');
    }

    /**
     * GET /auth/profile
     */
    public function profile(): void
    {
        // Validate session
        if (!Session::isValid()) {
            Response::unauthorized('Session expired');
        }

        $user = AuthMiddleware::getAuthUser();
        $userId = (int) $user['id'];

        $fullProfile = $this->authService->getProfile($userId);

        if (!$fullProfile) {
            Response::notFound('User tidak ditemukan');
        }

        // Add session info
        $fullProfile['session_info'] = [
            'remaining_time_seconds' => Session::getRemainingTime(),
            'remaining_time_minutes' => round(Session::getRemainingTime() / 60, 1),
        ];

        Response::success($fullProfile);
    }

    /**
     * GET /auth/session-info
     * Get current session information
     */
    public function sessionInfo(): void
    {
        if (!Session::isValid()) {
            Response::unauthorized('Session expired');
        }

        $info = Session::getInfo();
        
        // Add user info from session
        $info['user'] = [
            'id' => Session::get('user_id'),
            'email' => Session::get('user_email'),
            'role' => Session::get('user_role'),
            'name' => Session::get('user_name'),
            'id_gudang' => Session::get('id_gudang'),
        ];

        Response::success($info);
    }

    /**
     * POST /auth/refresh
     * Refresh session and token
     */
    public function refresh(): void
    {
        if (!Session::isValid()) {
            Response::unauthorized('Session expired');
        }

        $user = AuthMiddleware::getAuthUser();
        
        // Generate new token
        $newToken = $this->authService->refreshToken($user);
        
        // Update session last activity
        Session::set('last_activity', time());
        
        // Set new cookie
        JWT::setHttpOnlyCookie($newToken, 1800);

        Response::success([
            'token' => $newToken,
            'expires_at' => date('Y-m-d H:i:s', time() + 1800),
            'remaining_time_seconds' => Session::getRemainingTime(),
        ], 'Token refreshed successfully');
    }
}
