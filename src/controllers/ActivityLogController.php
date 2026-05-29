<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Utils\Database;
use App\Utils\Response;

class ActivityLogController
{
    /**
     * GET /activity-log - Daftar log audit trail
     */
    public function index(): void
    {
        RoleMiddleware::requirePermission('settings.view'); // super_admin & bos saja

        $limit = isset($_GET['limit']) ? max(10, min(500, (int)$_GET['limit'])) : 100;

        $logs = Database::fetchAll(
            "SELECT al.*, u.name as nama_user, u.role as role_user
             FROM activity_log al
             JOIN users u ON al.id_user = u.id
             ORDER BY al.timestamp DESC, al.id DESC
             LIMIT {$limit}"
        );

        // Format JSON value fields so UI renders them clean and provide a clickable ref when possible
        foreach ($logs as &$log) {
            $log['before_value'] = $log['before_value'] ? json_decode($log['before_value'], true) : null;
            $log['after_value']  = $log['after_value'] ? json_decode($log['after_value'], true) : null;

            // Build a reference URL so UI can link to the related resource
            $refUrl = null;
            $id = (int)($log['record_id'] ?? 0);
            switch ($log['table_name']) {
                case 'nota':
                    $refUrl = "/peace_seafood/penjualan?id={$id}";
                    break;
                case 'stok_masuk':
                    $refUrl = "/peace_seafood/stok/masuk?id={$id}";
                    break;
                case 'timbangan':
                    $refUrl = "/peace_seafood/stok/timbangan?id={$id}";
                    break;
                case 'stok_transfer':
                    $refUrl = "/peace_seafood/stok/transfer?id={$id}";
                    break;
                case 'titipan':
                    $refUrl = "/peace_seafood/penitipan?id={$id}";
                    break;
                case 'retur':
                    $refUrl = "/peace_seafood/retur?id={$id}";
                    break;
                default:
                    $refUrl = $id > 0 ? "/peace_seafood/?id={$id}" : null;
            }

            $log['ref'] = $refUrl ? ['url' => $refUrl, 'id' => $id] : null;
        }

        Response::success($logs);
    }

    /**
     * GET /activity-log/resource?table=...&id=...
     * Return lightweight human-friendly resource details for modal.
     */
    public function resource(): void
    {
        RoleMiddleware::requirePermission('settings.view');

        $table = $_GET['table'] ?? '';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$table || $id <= 0) {
            Response::error('Parameter tidak lengkap', 422);
        }

        try {
            $payload = null;
            switch ($table) {
                case 'nota':
                    $payload = Database::fetchOne('SELECT n.id, n.no_nota, n.tanggal_nota, n.total, n.status, p.nama as nama_pembeli FROM nota n LEFT JOIN pembeli p ON n.id_pembeli = p.id WHERE n.id = ?', [$id]);
                    $payload['items'] = Database::fetchAll('SELECT nd.id, nd.id_produk, nd.qty, nd.harga_jual, pr.nama as nama_produk FROM nota_detail nd LEFT JOIN produk pr ON nd.id_produk = pr.id WHERE nd.id_nota = ?', [$id]);
                    break;
                case 'produk':
                    $payload = Database::fetchOne('SELECT id, nama, stok_qty, harga_beli, harga_jual FROM produk WHERE id = ?', [$id]);
                    break;
                case 'stok_masuk':
                    $payload = Database::fetchOne('SELECT sm.id, sm.id_produk, p.nama as nama_produk, sm.qty, sm.harga_beli, s.nama as nama_supplier, sm.status FROM stok_masuk sm LEFT JOIN produk p ON sm.id_produk = p.id LEFT JOIN supplier s ON sm.id_supplier = s.id WHERE sm.id = ?', [$id]);
                    break;
                case 'timbangan':
                    $payload = Database::fetchOne('SELECT t.id, t.id_stok_masuk, t.id_produk, p.nama as nama_produk, t.qty_teoritis, t.qty_actual, t.alasan_susut, t.created_by FROM timbangan t LEFT JOIN produk p ON t.id_produk = p.id WHERE t.id = ?', [$id]);
                    break;
                case 'stok_transfer':
                    $payload = Database::fetchOne('SELECT st.id, st.gudang_asal_id, st.gudang_tujuan_id, g1.nama as gudang_asal, g2.nama as gudang_tujuan, st.id_produk, p.nama as nama_produk, st.qty, st.status FROM stok_transfer st LEFT JOIN gudang g1 ON st.gudang_asal_id = g1.id LEFT JOIN gudang g2 ON st.gudang_tujuan_id = g2.id LEFT JOIN produk p ON st.id_produk = p.id WHERE st.id = ?', [$id]);
                    break;
                case 'titipan':
                    $payload = Database::fetchOne('SELECT t.id, t.no_titipan, t.id_produk, p.nama as nama_produk, t.qty_total, t.qty_tersisa, t.status FROM titipan t LEFT JOIN produk p ON t.id_produk = p.id WHERE t.id = ?', [$id]);
                    break;
                case 'retur':
                    $payload = Database::fetchOne('SELECT r.id, r.id_produk, p.nama as nama_produk, r.qty, r.tipe, r.status FROM retur r LEFT JOIN produk p ON r.id_produk = p.id WHERE r.id = ?', [$id]);
                    break;
                default:
                    Response::error('Resource type tidak dikenali', 422);
            }

            if (!$payload) {
                Response::error('Resource tidak ditemukan', 404);
            }

            Response::success($payload);
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
