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

    /**
     * GET /api/notifications/unread-count
     * Get unread notification count for bell icon polling
     */
    public function unreadCount(): void
    {
        try {
            $user = AuthMiddleware::getAuthUser();
            $count = \App\Services\Shared\NotificationService::getUnreadCount((int)$user['id']);
            Response::success(['count' => $count]);
        } catch (\Exception $e) {
            error_log("NotifikasiController::unreadCount Error: " . $e->getMessage());
            Response::error('Gagal mengambil jumlah notifikasi', 500);
        }
    }

    /**
     * GET /api/notifications
     * Get all notifications for current user
     */
    public function list(): void
    {
        try {
            $user = AuthMiddleware::getAuthUser();
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $notifications = \App\Services\Shared\NotificationService::getForUser((int)$user['id'], $limit, $offset);
            Response::success($notifications);
        } catch (\Exception $e) {
            error_log("NotifikasiController::list Error: " . $e->getMessage());
            Response::error('Gagal mengambil notifikasi', 500);
        }
    }

    /**
     * POST /api/notifications/{id}/read
     * Mark specific notification as read
     */
    public function markAsRead(string $id): void
    {
        try {
            $user = AuthMiddleware::getAuthUser();
            $success = \App\Services\Shared\NotificationService::markAsRead((int)$id, (int)$user['id']);
            
            if ($success) {
                Response::success(null, 'Notifikasi ditandai sudah dibaca');
            } else {
                Response::notFound('Notifikasi tidak ditemukan');
            }
        } catch (\Exception $e) {
            error_log("NotifikasiController::markAsRead Error: " . $e->getMessage());
            Response::error('Gagal menandai notifikasi', 500);
        }
    }

    /**
     * POST /api/notifications/mark-all-read
     * Mark all notifications as read for current user
     */
    public function markAllAsRead(): void
    {
        try {
            $user = AuthMiddleware::getAuthUser();
            \App\Services\Shared\NotificationService::markAllAsRead((int)$user['id']);
            Response::success(null, 'Semua notifikasi ditandai sudah dibaca');
        } catch (\Exception $e) {
            error_log("NotifikasiController::markAllAsRead Error: " . $e->getMessage());
            Response::error('Gagal menandai notifikasi', 500);
        }
    }
}
