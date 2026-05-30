<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\NotificationService;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;

class NotifikasiController
{
    private NotificationService $notifService;

    public function __construct()
    {
        $this->notifService = new NotificationService();
    }

    public function index(): void
    {
        $user      = AuthMiddleware::getAuthUser();
        $idGudang  = AuthMiddleware::resolveGudang();

        if ($user['role'] === 'bos') {
            // Bos memantau hutang seluruh gudang secara agregat
            $this->notifService->checkHutangJatuhTempo(null);
            // Bos memantau eskalasi timbangan yang tertunda > 6 jam
            $this->notifService->checkPendingTimbanganEscalation();
        } else {
            // Admin hanya memantau gudangnya sendiri
            if ($idGudang) {
                $this->notifService->checkHutangJatuhTempo($idGudang);
            }
        }

        $unreadOnly  = isset($_GET['unread']) && $_GET['unread'] === '1';
        $notifikasi  = $this->notifService->getNotifikasi((int)$user['id'], $unreadOnly);
        $unreadCount = $this->notifService->getUnreadCount((int)$user['id']);

        Response::success([
            'notifikasi'   => $notifikasi,
            'unread_count' => $unreadCount,
        ]);
    }

    public function read(string $id): void
    {
        $id = (int)$id;
        $user = AuthMiddleware::getAuthUser();

        $ok = $this->notifService->markAsRead($id, (int)$user['id']);
        if (!$ok) {
            Response::notFound('Notifikasi tidak ditemukan');
        }

        Response::success(null, 'Notifikasi ditandai dibaca');
    }

    public function readAll(): void
    {
        $user = AuthMiddleware::getAuthUser();
        $this->notifService->markAllAsRead((int)$user['id']);
        Response::success(null, 'Semua notifikasi ditandai dibaca');
    }

    public function destroy(string $id): void
    {
        $id = (int)$id;
        $user = AuthMiddleware::getAuthUser();

        $ok = $this->notifService->deleteNotifikasi($id, (int)$user['id']);
        if (!$ok) {
            Response::notFound('Notifikasi tidak ditemukan');
        }

        Response::success(null, 'Notifikasi berhasil dihapus');
    }
}
