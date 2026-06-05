<?php

declare(strict_types=1);

namespace App\Controllers\SaaS;

use App\Middleware\AuthMiddleware;
use App\Utils\Database;
use App\Utils\Response;

/**
 * Centralized Log Controller for SaaS Owner
 * View all tenant activity logs from one place
 */
class CentralizedLogController
{
    /**
     * GET /api/saas/logs - Get centralized logs for all tenants
     * Accessible by: saas_owner only
     */
    public function index(): void
    {
        try {
            $user = AuthMiddleware::handle();
            
            if ($user['role'] !== 'saas_owner') {
                Response::forbidden('Akses ditolak. Hanya SaaS Owner yang dapat melihat log terpusat.');
            }
            
            // Pagination
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? max(10, min(100, (int)$_GET['limit'])) : 50;
            $offset = ($page - 1) * $limit;
            
            // Filters
            $filters = [];
            $params = [];
            
            // Filter by gudang
            if (!empty($_GET['id_gudang'])) {
                $filters[] = "al.id_gudang = ?";
                $params[] = (int)$_GET['id_gudang'];
            }
            
            // Filter by date range
            if (!empty($_GET['start_date'])) {
                $filters[] = "DATE(al.timestamp) >= ?";
                $params[] = $_GET['start_date'];
            }
            if (!empty($_GET['end_date'])) {
                $filters[] = "DATE(al.timestamp) <= ?";
                $params[] = $_GET['end_date'];
            }
            
            // Filter by action
            if (!empty($_GET['action'])) {
                $filters[] = "al.action = ?";
                $params[] = $_GET['action'];
            }
            
            // Filter by role
            if (!empty($_GET['role'])) {
                $filters[] = "u.role = ?";
                $params[] = $_GET['role'];
            }
            
            // Filter by table
            if (!empty($_GET['table_name'])) {
                $filters[] = "al.table_name = ?";
                $params[] = $_GET['table_name'];
            }
            
            // Search by description
            if (!empty($_GET['search'])) {
                $filters[] = "(al.description LIKE ? OR al.action LIKE ? OR u.name LIKE ?)";
                $searchTerm = '%' . $_GET['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = !empty($filters) ? 'WHERE ' . implode(' AND ', $filters) : '';
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total 
                         FROM activity_log al
                         LEFT JOIN users u ON al.id_user = u.id
                         {$whereClause}";
            $totalResult = Database::fetchOne($countSql, $params);
            $total = (int)($totalResult['total'] ?? 0);
            
            // Get logs
            $sql = "SELECT 
                        al.*,
                        u.name as nama_user,
                        u.email as email_user,
                        u.role as role_user,
                        g.nama_gudang as nama_gudang,
                        g.id as gudang_id
                    FROM activity_log al
                    LEFT JOIN users u ON al.id_user = u.id
                    LEFT JOIN gudang g ON al.id_gudang = g.id
                    {$whereClause}
                    ORDER BY al.timestamp DESC, al.id DESC
                    LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $logs = Database::fetchAll($sql, $params);
            
            // Format logs
            foreach ($logs as &$log) {
                // Parse JSON values
                $log['before_value'] = $log['before_value'] ? json_decode($log['before_value'], true) : null;
                $log['after_value'] = $log['after_value'] ? json_decode($log['after_value'], true) : null;
                
                // Build reference URL
                $log['ref_url'] = $this->buildRefUrl($log['table_name'], (int)($log['record_id'] ?? 0));
                
                // Format timestamp
                $log['timestamp_formatted'] = date('d M Y H:i', strtotime($log['timestamp']));
            }
            
            // Calculate pagination
            $totalPages = ceil($total / $limit);
            
            Response::success('Data berhasil diambil.', [
                'logs' => $logs,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_prev' => $page > 1,
                    'has_next' => $page < $totalPages
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("CentralizedLogController::index - Error: " . $e->getMessage());
            Response::error('Terjadi kesalahan saat mengambil data log.', 500);
        }
    }
    
    /**
     * GET /api/saas/logs/export - Export logs to CSV
     * Accessible by: saas_owner only
     */
    public function export(): void
    {
        try {
            $user = AuthMiddleware::handle();
            
            if ($user['role'] !== 'saas_owner') {
                Response::forbidden('Akses ditolak. Hanya SaaS Owner yang dapat export log.');
            }
            
            // Get filters (same as index)
            $filters = [];
            $params = [];
            
            if (!empty($_GET['id_gudang'])) {
                $filters[] = "al.id_gudang = ?";
                $params[] = (int)$_GET['id_gudang'];
            }
            
            if (!empty($_GET['start_date'])) {
                $filters[] = "DATE(al.timestamp) >= ?";
                $params[] = $_GET['start_date'];
            }
            if (!empty($_GET['end_date'])) {
                $filters[] = "DATE(al.timestamp) <= ?";
                $params[] = $_GET['end_date'];
            }
            
            if (!empty($_GET['action'])) {
                $filters[] = "al.action = ?";
                $params[] = $_GET['action'];
            }
            
            if (!empty($_GET['role'])) {
                $filters[] = "u.role = ?";
                $params[] = $_GET['role'];
            }
            
            $whereClause = !empty($filters) ? 'WHERE ' . implode(' AND ', $filters) : '';
            
            // Get total count first
            $countSql = "SELECT COUNT(*) as total 
                         FROM activity_log al
                         LEFT JOIN users u ON al.id_user = u.id
                         {$whereClause}";
            $totalResult = Database::fetchOne($countSql, $params);
            $total = (int)($totalResult['total'] ?? 0);
            
            // Export limit
            $exportLimit = 10000;
            
            // Check if exceeds limit
            if ($total > $exportLimit) {
                Response::json([
                    'success' => false,
                    'message' => "Export dibatasi maksimal {$exportLimit} records.",
                    'error_code' => 'EXPORT_LIMIT_EXCEEDED',
                    'total_records' => $total,
                    'export_limit' => $exportLimit,
                    'suggestion' => 'Silakan gunakan filter untuk mempersempit data yang akan di-export.'
                ], 400);
            }
            
            // Get all matching logs (limit for safety)
            $sql = "SELECT 
                        al.timestamp,
                        g.nama_gudang,
                        u.name as nama_user,
                        u.role as role_user,
                        al.action,
                        al.table_name,
                        al.record_id,
                        al.description,
                        al.ip_address
                    FROM activity_log al
                    LEFT JOIN users u ON al.id_user = u.id
                    LEFT JOIN gudang g ON al.id_gudang = g.id
                    {$whereClause}
                    ORDER BY al.timestamp DESC
                    LIMIT ?";
            
            $params[] = $exportLimit;
            
            $logs = Database::fetchAll($sql, $params);
            
            // Add export metadata header
            header('X-Export-Total: ' . $total);
            header('X-Export-Limit: ' . $exportLimit);
            header('X-Export-Count: ' . count($logs));
            
            // Generate CSV
            $filename = 'centralized_logs_' . date('Y-m-d_His') . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            $output = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 support
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV Headers
            fputcsv($output, [
                'Timestamp',
                'Gudang',
                'User',
                'Role',
                'Action',
                'Table',
                'Record ID',
                'Description',
                'IP Address'
            ]);
            
            // CSV Data
            foreach ($logs as $log) {
                fputcsv($output, [
                    $log['timestamp'],
                    $log['nama_gudang'] ?? '-',
                    $log['nama_user'] ?? '-',
                    $log['role_user'] ?? '-',
                    $log['action'],
                    $log['table_name'] ?? '-',
                    $log['record_id'] ?? '-',
                    $log['description'] ?? '-',
                    $log['ip_address'] ?? '-'
                ]);
            }
            
            fclose($output);
            exit;
            
        } catch (\Exception $e) {
            error_log("CentralizedLogController::export - Error: " . $e->getMessage());
            Response::error('Terjadi kesalahan saat export log.', 500);
        }
    }
    
    /**
     * GET /api/saas/logs/filters - Get available filter options
     * Accessible by: saas_owner only
     */
    public function filters(): void
    {
        try {
            $user = AuthMiddleware::handle();
            
            if ($user['role'] !== 'saas_owner') {
                Response::forbidden('Akses ditolak.');
            }
            
            // Get list of gudangs
            $gudangs = Database::fetchAll(
                "SELECT id, nama_gudang FROM gudang WHERE is_active = 1 ORDER BY nama_gudang"
            );
            
            // Get distinct actions
            $actions = Database::fetchAll(
                "SELECT DISTINCT action FROM activity_log WHERE action IS NOT NULL ORDER BY action"
            );
            
            // Get distinct tables
            $tables = Database::fetchAll(
                "SELECT DISTINCT table_name FROM activity_log WHERE table_name IS NOT NULL ORDER BY table_name"
            );
            
            // Role options
            $roles = [
                ['value' => 'saas_owner', 'label' => 'SaaS Owner'],
                ['value' => 'super_admin', 'label' => 'Super Admin'],
                ['value' => 'bos', 'label' => 'Bos'],
                ['value' => 'admin', 'label' => 'Admin'],
                ['value' => 'financial_admin', 'label' => 'Financial Admin'],
                ['value' => 'checker', 'label' => 'Checker'],
                ['value' => 'helper', 'label' => 'Helper'],
                ['value' => 'viewer', 'label' => 'Viewer']
            ];
            
            Response::success('Filter options berhasil diambil.', [
                'gudangs' => $gudangs,
                'actions' => array_column($actions, 'action'),
                'tables' => array_column($tables, 'table_name'),
                'roles' => $roles
            ]);
            
        } catch (\Exception $e) {
            error_log("CentralizedLogController::filters - Error: " . $e->getMessage());
            Response::error('Terjadi kesalahan.', 500);
        }
    }
    
    /**
     * Build reference URL for specific table and record
     */
    private function buildRefUrl(string $table, int $recordId): ?string
    {
        if ($recordId <= 0 || empty($table)) {
            return null;
        }
        
        $baseUrl = '/peace_seafood';
        
        switch ($table) {
            case 'nota':
                return "{$baseUrl}/penjualan?id={$recordId}";
            case 'stok_masuk':
                return "{$baseUrl}/stok/masuk?id={$recordId}";
            case 'timbangan':
                return "{$baseUrl}/stok/timbangan?id={$recordId}";
            case 'stok_transfer':
                return "{$baseUrl}/stok/transfer?id={$recordId}";
            case 'titipan':
                return "{$baseUrl}/penitipan?id={$recordId}";
            case 'retur':
                return "{$baseUrl}/retur?id={$recordId}";
            case 'produk':
                return "{$baseUrl}/master-data/produk?id={$recordId}";
            case 'gudang':
                return "{$baseUrl}/settings?gudang_id={$recordId}";
            case 'users':
                return "{$baseUrl}/settings?user_id={$recordId}";
            default:
                return null;
        }
    }
}
