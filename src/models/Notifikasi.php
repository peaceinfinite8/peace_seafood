<?php

declare(strict_types=1);

namespace App\Models;

class Notifikasi extends Model
{
    protected string $table = 'notifikasi';

    public function findUnread(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `notifikasi` WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function markAllRead(int $userId): void
    {
        $this->db->prepare("UPDATE `notifikasi` SET is_read = 1 WHERE user_id = ?")->execute([$userId]);
    }

    public function countUnread(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `notifikasi` WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }
}
