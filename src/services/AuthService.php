<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Utils\JWT;
use App\Utils\Database;

class AuthService
{
    /**
     * Login user - return token + user data or null
     */
    public function login(string $email, string $password): ?array
    {
        // Support login by email or username and include warehouse name for the UI.
        $user = Database::fetchOne(
            "SELECT u.*, g.nama AS nama_gudang
             FROM users u
             LEFT JOIN gudang g ON u.id_gudang = g.id
             WHERE (u.email = ? OR u.name = ?) AND u.is_active = 1",
            [$email, $email]
        );

        if (!$user)
            return null;
        if (!password_verify($password, $user['password']))
            return null;

        $token = JWT::generate([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'id_gudang' => $user['id_gudang'],
        ]);

        // Remove password from response
        unset($user['password']);

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * Refresh token
     */
    public function refreshToken(array $user): string
    {
        return JWT::generate([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'id_gudang' => $user['id_gudang'] ?? null,
        ]);
    }

    /**
     * Get user profile by ID
     */
    public function getProfile(int $userId): ?array
    {
        $user = Database::fetchOne(
            "SELECT u.*, g.nama as nama_gudang 
             FROM users u 
             LEFT JOIN gudang g ON u.id_gudang = g.id
             WHERE u.id = ? AND u.is_active = 1",
            [$userId]
        );

        if (!$user)
            return null;
        unset($user['password']);
        return $user;
    }

    /**
     * Register/create new user
     */
    public function createUser(array $data): int
    {
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        return Database::insert('users', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'role' => $data['role'],
            'id_gudang' => $data['id_gudang'] ?? null,
            'is_active' => 1,
        ]);
    }

    /**
     * Update user
     */
    public function updateUser(int $userId, array $data): bool
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'id_gudang' => $data['id_gudang'] ?? null,
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        return Database::update('users', $updateData, 'id = ?', [$userId]);
    }

    /**
     * Deactivate user (soft delete)
     */
    public function deleteUser(int $userId): bool
    {
        return Database::update('users', ['is_active' => 0], 'id = ?', [$userId]);
    }

    /**
     * Get all users (for BOZ admin)
     */
    public function getAllUsers(?int $idGudang = null): array
    {
        if ($idGudang) {
            return Database::fetchAll(
                "SELECT u.id, u.name, u.email, u.role, u.id_gudang, u.is_active, g.nama as nama_gudang
                 FROM users u LEFT JOIN gudang g ON u.id_gudang = g.id
                 WHERE u.id_gudang = ? ORDER BY u.name",
                [$idGudang]
            );
        }
        return Database::fetchAll(
            "SELECT u.id, u.name, u.email, u.role, u.id_gudang, u.is_active, g.nama as nama_gudang
             FROM users u LEFT JOIN gudang g ON u.id_gudang = g.id
             ORDER BY u.role, u.name"
        );
    }
}
