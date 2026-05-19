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
        $identifier = trim((string) ($body['email'] ?? $body['username'] ?? ''));

        if ($identifier === '' || empty($body['password'])) {
            Response::error(422, 'VALIDATION_ERROR', 'Email dan password wajib diisi');
        }

        $result = $this->authService->login($identifier, $body['password']);

        if (!$result) {
            Response::unauthorized('Email atau password salah');
        }

        Response::success($result, 'Login berhasil');
    }

    /**
     * POST /auth/logout
     */
    public function logout(): void
    {
        // Client clears the token
        Response::success(null, 'Logout berhasil');
    }

    /**
     * GET /auth/profile
     */
    public function profile(): void
    {
        $user = AuthMiddleware::getAuthUser();
        $userId = (int) $user['id'];

        $fullProfile = $this->authService->getProfile($userId);

        if (!$fullProfile) {
            Response::notFound('User tidak ditemukan');
        }

        Response::success($fullProfile);
    }
}
