<?php

declare(strict_types=1);

namespace App\models;

use App\utils\Database;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): array|false
    {
        return Database::fetchOne(
            "SELECT * FROM users WHERE email = ? AND is_active = 1",
            [$email]
        );
    }

    public static function findWithGudang(int $id): array|false
    {
        return Database::fetchOne(
            "SELECT u.*, g.nama as gudang_nama, g.alamat as gudang_alamat
             FROM users u
             LEFT JOIN gudang g ON g.id = u.id_gudang
             WHERE u.id = ?",
            [$id]
        );
    }

    public static function getByGudang(int $idGudang): array
    {
        return Database::fetchAll(
            "SELECT id, name, email, role, id_gudang, is_active, created_at
             FROM users WHERE id_gudang = ? ORDER BY name",
            [$idGudang]
        );
    }

    public static function getByRole(string $role, ?int $idGudang = null): array
    {
        if ($idGudang) {
            return Database::fetchAll(
                "SELECT id, name, email, role, id_gudang FROM users
                 WHERE role = ? AND id_gudang = ? AND is_active = 1",
                [$role, $idGudang]
            );
        }
        return Database::fetchAll(
            "SELECT id, name, email, role, id_gudang FROM users
             WHERE role = ? AND is_active = 1",
            [$role]
        );
    }

    public static function updateLastLogin(int $id): void
    {
        Database::execute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$id]
        );
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
