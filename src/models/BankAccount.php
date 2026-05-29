<?php

declare(strict_types=1);

namespace App\Models;

use App\utils\Database;

class BankAccount extends Model
{
    protected static string $table = 'bank_account';

    public static function getByUser(int $idUser): array
    {
        if (!Database::hasTable('bank_account')) {
            return [];
        }

        return Database::fetchAll(
            "SELECT * FROM bank_account WHERE id_user = ? AND is_active = 1 ORDER BY created_at DESC",
            [$idUser]
        );
    }

    public static function getOne(int $id, int $idUser): ?array
    {
        if (!Database::hasTable('bank_account')) {
            return null;
        }

        return Database::fetchOne(
            "SELECT * FROM bank_account WHERE id = ? AND id_user = ?",
            [$id, $idUser]
        ) ?: null;
    }

    public static function create(array $data): int
    {
        if (!Database::hasTable('bank_account')) {
            return 0;
        }

        return Database::insert('bank_account', [
            'id_user'       => $data['id_user'],
            'bank_name'     => $data['bank_name'],
            'account_number' => $data['account_number'],
            'account_name'  => $data['account_name'],
            'is_active'     => $data['is_active'] ?? 1,
        ]);
    }

    public static function updateAccount(int $id, array $data, int $idUser): bool
    {
        if (!Database::hasTable('bank_account')) {
            return false;
        }

        return Database::update('bank_account', [
            'bank_name'     => $data['bank_name'],
            'account_number' => $data['account_number'],
            'account_name'  => $data['account_name'],
            'is_active'     => $data['is_active'] ?? 1,
        ], 'id = ? AND id_user = ?', [$id, $idUser]);
    }

    public static function deleteAccount(int $id, int $idUser): bool
    {
        if (!Database::hasTable('bank_account')) {
            return false;
        }

        return Database::update('bank_account', ['is_active' => 0], 'id = ? AND id_user = ?', [$id, $idUser]);
    }
}
