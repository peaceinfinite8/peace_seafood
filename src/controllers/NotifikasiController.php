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
        $allGudang = AuthMiddleware::isAllGudang();

        // Trigger hutang jatuh tempo check (hanya jika ada gudang spesifik)
        if ($idGudang) {
            $this->notifService->checkHutangJatuhTempo($idGudang);
        }

        $unreadOnly  = isset($_GET['unread']) && $_GET['unread'] === '1';
        $notifikasi  = $this->notifService->getNotifikasi($idGudang, $unreadOnly, $allGudang);
        $unreadCount = $this->notifService->getUnreadCount($idGudang, $allGudang);

        Response::success([
            'notifikasi'   => $notifikasi,
            'unread_count' => $unreadCount,
        ]);
    }

    public function read(int $id): void
    {
        $user     = AuthMiddleware::getAuthUser();
        $idGudang = AuthMiddleware::resolveGudang();

        $ok = $this->notifService->markAsRead($id, $idGudang, AuthMiddleware::isAllGudang());
        if (!$ok) {
            Response::notFound('Notifikasi tidak ditemukan');
        }

        Response::success(null, 'Notifikasi ditandai dibaca');
    }

    public function readAll(): void
    {
        $idGudang = AuthMiddleware::resolveGudang();
        $this->notifService->markAllAsRead($idGudang, AuthMiddleware::isAllGudang());
        Response::success(null, 'Semua notifikasi ditandai dibaca');
    }
}
