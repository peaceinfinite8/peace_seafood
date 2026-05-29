<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\Database;

class NotificationService
{
    public function getNotifikasi(int $idUser, bool $unreadOnly = false): array
    {
        $where = "id_user = ?";
        $params = [$idUser];

        if ($unreadOnly) {
            $where .= " AND is_read = 0";
        }

        try {
            $list = Database::fetchAll(
                "SELECT * FROM notifikasi WHERE {$where} ORDER BY created_at DESC LIMIT 50",
                $params
            );
        } catch (\PDOException $e) {
            error_log('NotificationService::getNotifikasi DB error: ' . $e->getMessage());
            return [];
        }

        // Dynamically format YYYY-MM-DD date patterns inside messages to match pages (j/n/Y)
        foreach ($list as &$n) {
            if (isset($n['pesan'])) {
                $n['pesan'] = preg_replace_callback(
                    '/\b(\d{4})-(\d{2})-(\d{2})\b/',
                    function ($matches) {
                        $year = $matches[1];
                        $month = (int)$matches[2];
                        $day = (int)$matches[3];
                        return "{$day}/{$month}/{$year}";
                    },
                    $n['pesan']
                );
            }
        }
        unset($n);

        return $list;
    }

    /**
     * Tandai notifikasi sebagai dibaca.
     */
    public function markAsRead(int $idNotifikasi, int $idUser): bool
    {
        return Database::update(
            'notifikasi',
            ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
            'id = ? AND id_user = ?',
            [$idNotifikasi, $idUser]
        );
    }

    /**
     * Tandai semua notifikasi sebagai dibaca untuk user tertentu.
     */
    public function markAllAsRead(int $idUser): bool
    {
        return Database::update(
            'notifikasi',
            ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
            'id_user = ? AND is_read = 0',
            [$idUser]
        );
    }

    /**
     * Hitung unread notifikasi untuk user tertentu.
     */
    public function getUnreadCount(int $idUser): int
    {
        try {
            return (int)(Database::fetchOne(
                "SELECT COUNT(*) as cnt FROM notifikasi WHERE id_user = ? AND is_read = 0",
                [$idUser]
            )['cnt'] ?? 0);
        } catch (\PDOException $e) {
            error_log('NotificationService::getUnreadCount DB error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Buat notifikasi hutang jatuh tempo
     */
    public function checkHutangJatuhTempo(?int $idGudang = null): void
    {
        // 1. Otomatis tandai dibaca notifikasi hutang_jatuh_tempo yang statusnya sudah lunas
        try {
            Database::query(
                "UPDATE notifikasi n
                 JOIN hutang_piutang hp ON n.reference_id = hp.id AND n.reference_tipe = 'hutang_piutang'
                 SET n.is_read = 1, n.read_at = NOW()
                 WHERE n.tipe = 'hutang_jatuh_tempo' 
                   AND n.is_read = 0 
                   AND hp.status = 'lunas'"
            );
        } catch (\PDOException $e) {
            error_log('NotificationService::checkHutangJatuhTempo update join error: ' . $e->getMessage());
            // continue - non-fatal for notification generation
        }

        // 2. Ambil hutang yang belum lunas
        $where = "hp.status != 'lunas' AND hp.jatuh_tempo IS NOT NULL";
        $params = [];

        if ($idGudang !== null) {
            $where .= " AND hp.id_gudang = ?";
            $params[] = $idGudang;
        }

        try {
            $hutangList = Database::fetchAll(
                "SELECT hp.*, 
                        COALESCE(s.nama, p.nama) as nama_pihak
                 FROM hutang_piutang hp
                 LEFT JOIN supplier s ON hp.id_supplier = s.id
                 LEFT JOIN pembeli p ON hp.id_pembeli = p.id
                 WHERE {$where}",
                $params
            );
        } catch (\PDOException $e) {
            error_log('NotificationService::checkHutangJatuhTempo select error: ' . $e->getMessage());
            $hutangList = [];
        }

        $today = date('Y-m-d');

        foreach ($hutangList as $hp) {
            // Hitung sisa hari menuju jatuh tempo
            $jatuhTempoStr = $hp['jatuh_tempo'];
            $daysRemaining = (int)ceil((strtotime($jatuhTempoStr) - strtotime($today)) / 86400);

            // Kita hanya memproses jika:
            // a. Mepet: 0 s/d 5 hari menuju jatuh tempo (H-5 sampai H-0)
            // b. Lewat: < 0 hari (Overdue)
            if ($daysRemaining <= 5) {
                // Cek apakah hari ini sudah dikirim notifikasi untuk hutang ini (termasuk yang sudah dibaca)
                // Cek is_read 0 or 1 - hanya skip jika sudah ada notif hari ini
                try {
                    $existingToday = Database::fetchOne(
                        "SELECT id FROM notifikasi 
                             WHERE tipe = 'hutang_jatuh_tempo' 
                             AND reference_id = ? 
                             AND reference_tipe = 'hutang_piutang'
                             AND DATE(created_at) = CURDATE()
                             LIMIT 1",
                        [$hp['id']]
                    );
                } catch (\PDOException $e) {
                    error_log('NotificationService::checkHutangJatuhTempo existingToday error: ' . $e->getMessage());
                    $existingToday = null;
                }

                if (!$existingToday) {
                    $jenisText = $hp['jenis'] === 'hutang' ? 'Hutang ke' : 'Piutang dari';
                    $formattedDate = date('j/n/Y', strtotime($hp['jatuh_tempo']));
                    $formattedSisa = number_format((float)$hp['sisa_hutang'], 0, ',', '.');

                    if ($daysRemaining === 0) {
                        $judul = "🚨 Jatuh Tempo Hari Ini: " . ($hp['jenis'] === 'hutang' ? 'Hutang' : 'Piutang');
                        $pesan = "Peringatan Hari Ini! {$jenisText} {$hp['nama_pihak']} (#{$hp['id']}) jatuh tempo HARI INI ({$formattedDate}). Sisa: Rp {$formattedSisa}";
                    } elseif ($daysRemaining > 0) {
                        $judul = "⚠️ H-{$daysRemaining} Jatuh Tempo " . ($hp['jenis'] === 'hutang' ? 'Hutang' : 'Piutang');
                        $pesan = "Peringatan H-{$daysRemaining}! {$jenisText} {$hp['nama_pihak']} (#{$hp['id']}) jatuh tempo pada {$formattedDate} ({$daysRemaining} hari lagi). Sisa: Rp {$formattedSisa}";
                    } else {
                        $daysOverdue = abs($daysRemaining);
                        $judul = "🔴 Lewat Jatuh Tempo: " . ($hp['jenis'] === 'hutang' ? 'Hutang' : 'Piutang');
                        $pesan = "⚠ Telah Lewat Jatuh Tempo! {$jenisText} {$hp['nama_pihak']} (#{$hp['id']}) sudah lewat jatuh tempo sejak {$formattedDate} (terlambat {$daysOverdue} hari). Sisa: Rp {$formattedSisa}";
                    }

                    // Tandai notifikasi unread LAMA (bukan hari ini) untuk referensi hutang ini sebagai dibaca
                    // agar tidak menumpuk di inbox, tapi yang hari ini dibiarkan
                    try {
                        Database::query(
                            "UPDATE notifikasi 
                             SET is_read = 1, read_at = NOW() 
                             WHERE tipe = 'hutang_jatuh_tempo' 
                               AND reference_id = ? 
                               AND reference_tipe = 'hutang_piutang'
                               AND is_read = 0
                               AND DATE(created_at) < CURDATE()",
                            [$hp['id']]
                        );
                    } catch (\PDOException $e) {
                        error_log('NotificationService::checkHutangJatuhTempo mark old error: ' . $e->getMessage());
                    }

                    // Kirim ke Bos dan Admin gudang terkait
                    $this->sendNotification(['bos', 'admin'], $hp['id_gudang'], 'hutang_jatuh_tempo', $judul, $pesan, $hp['id'], 'hutang_piutang');
                }
            }
        }
    }

    /**
     * Cek timbangan pending yang melewati batas waktu dan eskalasi ke Bos
     */
    public function checkPendingTimbanganEscalation(): void
    {
        // Cari timbangan pending yang dibuat > 6 jam lalu dan belum ada notifikasi eskalasi
        $pendingList = Database::fetchAll(
            "SELECT sm.*, 
                    g.nama as nama_gudang,
                    p.nama as nama_produk,
                    u.name as nama_checker
             FROM stok_masuk sm
             JOIN gudang g ON sm.id_gudang = g.id
             JOIN produk p ON sm.id_produk = p.id
             JOIN users u ON sm.id_user = u.id
             WHERE sm.status = 'pending'
               AND sm.created_at < DATE_SUB(NOW(), INTERVAL 6 HOUR)"
        );

        foreach ($pendingList as $item) {
            $existing = Database::fetchOne(
                "SELECT id FROM notifikasi 
                 WHERE tipe = 'timbangan_eskalasi' 
                 AND is_read = 0 AND pesan LIKE ?",
                ['%#' . $item['id'] . '%']
            );

            if (!$existing) {
                $pesan = "Eskalasi: Timbangan pending untuk {$item['nama_produk']} ({$item['qty']} kg) di {$item['nama_gudang']} belum diproses oleh Admin selama lebih dari 6 jam (diinput oleh Checker {$item['nama_checker']}).";
                $judul = "⚠️ Eskalasi Timbangan Pending";

                // Kirim khusus ke BOS (role 'bos') untuk supervisi
                $this->sendNotification(['bos'], null, 'timbangan_eskalasi', $judul, $pesan, $item['id'], 'stok_masuk');
            }
        }
    }

    /**
     * Kirim/dispatch notifikasi ke user tertentu berdasarkan Role dan Gudang.
     */
    public function sendNotification(array $roles, ?int $idGudang, string $tipe, string $judul, string $pesan, ?int $referenceId = null, ?string $referenceTipe = null): void
    {
        // Cari semua user aktif yang memiliki role target
        $query = "SELECT id, role, id_gudang FROM users WHERE is_active = 1";
        $params = [];

        if (!empty($roles)) {
            $placeholders = implode(', ', array_fill(0, count($roles), '?'));
            $query .= " AND role IN ({$placeholders})";
            $params = array_merge($params, $roles);
        }

        $users = Database::fetchAll($query, $params);

        foreach ($users as $user) {
            // Filter gudang untuk role selain 'bos'
            if ($user['role'] !== 'bos' && $idGudang !== null && (int)$user['id_gudang'] !== $idGudang) {
                continue;
            }

            Database::insert('notifikasi', [
                'id_user'        => $user['id'],
                'tipe'           => $tipe,
                'judul'          => $judul,
                'pesan'          => $pesan,
                'reference_id'   => $referenceId,
                'reference_tipe' => $referenceTipe,
                'is_read'        => 0,
            ]);
        }
    }

    /**
     * Buat notifikasi langsung untuk user tertentu.
     */
    public function createForUser(int $idUser, string $tipe, string $judul, string $pesan, ?int $referenceId = null, ?string $referenceTipe = null): int
    {
        return Database::insert('notifikasi', [
            'id_user'        => $idUser,
            'tipe'           => $tipe,
            'judul'          => $judul,
            'pesan'          => $pesan,
            'reference_id'   => $referenceId,
            'reference_tipe' => $referenceTipe,
            'is_read'        => 0,
        ]);
    }

    /**
     * Hapus/Delete notifikasi dari database.
     */
    public function deleteNotifikasi(int $idNotifikasi, int $idUser): bool
    {
        return Database::execute(
            "DELETE FROM notifikasi WHERE id = ? AND id_user = ?",
            [$idNotifikasi, $idUser]
        );
    }
}
