<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Utils\JWT;
use App\Utils\Database;

class AuthService
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Login user - return token + user data or null
     */
    public function login(string $email, string $password): ?array
    {
        // Support login by email or username
        $user = Database::fetchOne(
            "SELECT * FROM users WHERE (email = ? OR name = ?) AND is_active = 1",
            [$email, $email]
        );

        if (!$user) return null;
        if (!password_verify($password, $user['password'])) return null;

        $token = JWT::generate([
            'id'        => $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
            'id_gudang' => $user['id_gudang'],
        ]);

        // Remove password from response
        unset($user['password']);

        return [
            'token' => $token,
            'user'  => $user,
        ];
    }

    /**
     * Refresh token
     */
    public function refreshToken(array $user): string
    {
        return JWT::generate([
            'id'        => $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
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

        if (!$user) return null;
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
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => $hashedPassword,
            'role'      => $data['role'],
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
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'],
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

    public function getAllUsers(array $requester): array
    {
        $role = $requester['role'] ?? '';

        if ($role === 'super_admin' || $role === 'saas_owner') {
            return Database::fetchAll(
                "SELECT u.id, u.name, u.email, u.role, u.id_gudang, u.is_active, g.nama as nama_gudang
                 FROM users u LEFT JOIN gudang g ON u.id_gudang = g.id
                 ORDER BY u.role, u.name"
            );
        }

        if ($role === 'bos') {
            $bosId = (int)$requester['id'];
            return Database::fetchAll(
                "SELECT u.id, u.name, u.email, u.role, u.id_gudang, u.is_active, g.nama as nama_gudang
                 FROM users u 
                 LEFT JOIN gudang g ON u.id_gudang = g.id
                 WHERE (u.id_gudang IN (SELECT id FROM gudang WHERE id_bos = ?) OR u.id = ?)
                   AND u.role NOT IN ('super_admin', 'saas_owner')
                 ORDER BY u.role, u.name",
                [$bosId, $bosId]
            );
        }

        $idGudang = (int)($requester['id_gudang'] ?? 0);
        return Database::fetchAll(
            "SELECT u.id, u.name, u.email, u.role, u.id_gudang, u.is_active, g.nama as nama_gudang
             FROM users u LEFT JOIN gudang g ON u.id_gudang = g.id
             WHERE u.id_gudang = ? AND u.role NOT IN ('super_admin', 'saas_owner')
             ORDER BY u.name",
            [$idGudang]
        );
    }
}
