<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\Database;

class NotificationService
{
    /**
     * Get notifikasi untuk gudang.
     * Jika $allGudang = true, ambil semua gudang (untuk BOS).
     */
    public function getNotifikasi(int $idGudang, bool $unreadOnly = false, bool $allGudang = false): array
    {
        if ($allGudang && $idGudang === 0) {
            $where  = "1=1";
            $params = [];
        } else {
            $where  = "id_gudang = ?";
            $params = [$idGudang];
        }

        if ($unreadOnly) {
            $where .= " AND is_read = 0";
        }

        return Database::fetchAll(
            "SELECT * FROM notifikasi WHERE {$where} ORDER BY created_at DESC LIMIT 50",
            $params
        );
    }

    /**
     * Tandai notifikasi sebagai dibaca.
     */
    public function markAsRead(int $idNotifikasi, int $idGudang, bool $allGudang = false): bool
    {
        if ($allGudang && $idGudang === 0) {
            return Database::update('notifikasi', ['is_read' => 1], 'id = ?', [$idNotifikasi]);
        }
        return Database::update(
            'notifikasi',
            ['is_read' => 1],
            'id = ? AND id_gudang = ?',
            [$idNotifikasi, $idGudang]
        );
    }

    /**
     * Tandai semua notifikasi sebagai dibaca.
     */
    public function markAllAsRead(int $idGudang, bool $allGudang = false): bool
    {
        if ($allGudang && $idGudang === 0) {
            return Database::update('notifikasi', ['is_read' => 1], 'is_read = 0', []);
        }
        return Database::update(
            'notifikasi',
            ['is_read' => 1],
            'id_gudang = ? AND is_read = 0',
            [$idGudang]
        );
    }

    /**
     * Hitung unread notifikasi.
     */
    public function getUnreadCount(int $idGudang, bool $allGudang = false): int
    {
        if ($allGudang && $idGudang === 0) {
            return (int)(Database::fetchOne(
                "SELECT COUNT(*) as cnt FROM notifikasi WHERE is_read = 0",
                []
            )['cnt'] ?? 0);
        }
        return (int)(Database::fetchOne(
            "SELECT COUNT(*) as cnt FROM notifikasi WHERE id_gudang = ? AND is_read = 0",
            [$idGudang]
        )['cnt'] ?? 0);
    }

    /**
     * Buat notifikasi hutang jatuh tempo
     */
    public function checkHutangJatuhTempo(int $idGudang): void
    {
        // Cari hutang yang jatuh tempo dalam 3 hari ke depan
        $hutangList = Database::fetchAll(
            "SELECT hp.*, 
                    COALESCE(s.nama, p.nama) as nama_pihak
             FROM hutang_piutang hp
             LEFT JOIN supplier s ON hp.id_supplier = s.id
             LEFT JOIN pembeli p ON hp.id_pembeli = p.id
             WHERE hp.id_gudang = ? 
               AND hp.status != 'lunas' 
               AND hp.jatuh_tempo IS NOT NULL
               AND hp.jatuh_tempo BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)",
            [$idGudang]
        );

        foreach ($hutangList as $hp) {
            // Cek apakah notifikasi sudah ada
            $existing = Database::fetchOne(
                "SELECT id FROM notifikasi 
                 WHERE id_gudang = ? AND tipe = 'hutang_jatuh_tempo' 
                 AND is_read = 0 AND pesan LIKE ?",
                [$idGudang, '%#' . $hp['id'] . '%']
            );

            if (!$existing) {
                $jenisText = $hp['jenis'] === 'hutang' ? 'Hutang ke' : 'Piutang dari';
                Database::insert('notifikasi', [
                    'id_gudang' => $idGudang,
                    'tipe'      => 'hutang_jatuh_tempo',
                    'pesan'     => "{$jenisText} {$hp['nama_pihak']} (#{$hp['id']}) jatuh tempo pada {$hp['jatuh_tempo']}. Sisa: Rp " . number_format((float)$hp['sisa_hutang'], 0, ',', '.'),
                    'is_read'   => 0,
                ]);
            }
        }
    }

    /**
     * Buat notifikasi custom
     */
    public function create(int $idGudang, string $tipe, string $pesan): int
    {
        return Database::insert('notifikasi', [
            'id_gudang' => $idGudang,
            'tipe'      => $tipe,
            'pesan'     => $pesan,
            'is_read'   => 0,
        ]);
    }
}
