<?php

declare(strict_types=1);

/**
 * Frontend (Web) Router
 * Renders HTML views based on URI
 */

// URI sudah distrip dari /peace_seafood di public/index.php
$uri = $uri ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Helper function to render a view with layout
function renderView(string $view, array $vars = []): void
{
    extract($vars);
    $viewPath = BASE_PATH . '/src/views/' . $view . '.php';

    if (!file_exists($viewPath)) {
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1><p>{$view}</p>";
        return;
    }

    // Capture content
    ob_start();
    include $viewPath;
    $content = ob_get_clean();

    // Include layout
    include BASE_PATH . '/src/views/layouts/app.php';
}

// Route table
$routes = [
    '/'          => ['pages/login', 'Login'],
    '/login'     => ['pages/login', 'Login'],
    '/reset-password' => ['pages/login', 'Reset Password'],
    '/dashboard' => ['pages/dashboard', 'Dashboard', 'dashboard'],

    // Stok
    '/stok'                => ['stok/index', 'Stok & Inventory', 'stok'],
    '/stok/masuk'          => ['stok/masuk', 'Input Stok Masuk', 'stok'],
    '/stok/timbangan'      => ['stok/timbangan', 'Timbangan & Susut', 'stok'],
    '/stok/history'        => ['stok/history', 'History Stok', 'stok'],

    // Penjualan
    '/penjualan'           => ['penjualan/index', 'Penjualan', 'penjualan'],
    '/penjualan/create'    => ['penjualan/create', 'Buat Nota Penjualan', 'penjualan'],

    // Penitipan
    '/penitipan'           => ['penitipan/index', 'Penitipan', 'penitipan'],
    '/penitipan/create'    => ['penitipan/create', 'Terima Titipan', 'penitipan'],

    // Retur
    '/retur'               => ['retur/index', 'Retur', 'retur'],
    '/retur/create'        => ['retur/create', 'Buat Retur', 'retur'],

    // Keuangan
    '/keuangan'            => ['keuangan/index', 'Keuangan', 'keuangan'],

    // Master Data
    '/master-data'         => ['master-data/index', 'Master Data', 'master-data'],
    '/master-data/supplier'    => ['master-data/supplier', 'Data Supplier', 'master-data'],
    '/master-data/pembeli'     => ['master-data/pembeli', 'Data Pembeli', 'master-data'],
    '/master-data/jenis-ikan'  => ['master-data/jenis-ikan', 'Jenis Ikan', 'master-data'],
    '/master-data/produk'      => ['master-data/produk', 'Data Produk', 'master-data'],
    '/migrasi'                 => ['master-data/migrasi', 'Pusat Migrasi Data Bahari', 'migrasi'],

    // Stok Lanjutan
    '/stok-opname'             => ['stok/opname', 'Stok Opname', 'stok-opname'],
    '/stok-transfer'           => ['stok/transfer', 'Stok Transfer', 'stok-transfer'],

    // Checker — Draft Penjualan
    '/checker/draft-penjualan' => ['checker/draft-penjualan', 'Buat Draft Nota', 'checker-draft'],

    // Audit Trail
    '/activity-log'            => ['activity-log/index', 'Activity Log', 'activity-log'],

    // Laporan
    '/laporan'             => ['laporan/index', 'Laporan & Export', 'laporan'],

    // Settings
    '/settings'            => ['settings/index', 'Pengaturan', 'settings'],
];

// ── Halaman yang memerlukan role tertentu (server-side guard) ──────────────
// Key = URI, Value = array role yang diizinkan
$pageRoles = [
    '/settings'      => ['super_admin', 'saas_owner', 'bos'],
    '/activity-log'  => ['super_admin', 'bos'],
    '/laporan'       => ['super_admin', 'bos', 'admin'],
    '/migrasi'       => ['super_admin', 'admin'],
    '/master-data'           => ['super_admin', 'bos', 'admin'],
    '/master-data/supplier'  => ['super_admin', 'bos', 'admin'],
    '/master-data/pembeli'   => ['super_admin', 'bos', 'admin'],
    '/master-data/jenis-ikan'=> ['super_admin', 'bos', 'admin'],
    '/master-data/produk'    => ['super_admin', 'bos', 'admin'],
    '/keuangan'              => ['super_admin', 'bos', 'admin'],
    '/penjualan'             => ['super_admin', 'bos', 'admin'],
    '/penjualan/create'      => ['super_admin', 'admin'],
    '/penitipan'             => ['super_admin', 'bos', 'admin'],
    '/penitipan/create'      => ['super_admin', 'admin'],
    '/retur'                 => ['super_admin', 'bos', 'admin'],
    '/retur/create'          => ['super_admin', 'admin'],

    // Stok & Inventory
    '/stok'                  => ['super_admin', 'bos', 'admin', 'checker'],
    '/stok/masuk'            => ['super_admin', 'admin'],
    '/stok/timbangan'        => ['super_admin', 'admin', 'checker'],
    '/stok/history'          => ['super_admin', 'bos', 'admin'],
    '/stok-opname'           => ['super_admin', 'bos', 'admin', 'checker'],
    '/stok-transfer'         => ['super_admin', 'bos', 'admin', 'checker'],
    '/checker/draft-penjualan' => ['checker'],
];

/**
 * Cek role user dari JWT cookie untuk server-side page guard.
 * Mengembalikan role string atau null jika token tidak ada/invalid.
 */
function getWebUserRole(): ?string
{
    $token = $_COOKIE['auth_token'] ?? null;
    if (!$token) return null;

    try {
        $payload = \App\Utils\JWT::verify($token);
        if (!$payload) return null;

        // Verifikasi user masih aktif di database
        $user = \App\Utils\Database::fetchOne(
            "SELECT role, is_active FROM users WHERE id = ?",
            [$payload['id'] ?? $payload['user_id'] ?? 0]
        );
        if (!$user || !$user['is_active']) return null;

        return $user['role'];
    } catch (\Throwable $e) {
        return null;
    }
}

// Match route
if (isset($routes[$uri])) {
    $route      = $routes[$uri];
    $viewFile   = $route[0];
    $pageTitle  = $route[1];
    $activeMenu = $route[2] ?? '';

    // Login & root = no layout, tidak perlu auth check
    if (in_array($uri, ['/', '/login'])) {
        include BASE_PATH . '/src/views/' . $viewFile . '.php';
    } else {
        // ── Server-side role guard ──────────────────────────────────────────
        if (isset($pageRoles[$uri])) {
            $userRole = getWebUserRole();

            if ($userRole === null) {
                // Tidak ada token atau token invalid → redirect ke login
                header('Location: /peace_seafood/login');
                exit;
            }

            // 'super_admin' bypass all web page guards (operator teknis)
            // 'saas_owner' dan role lain tetap dicek sesuai $pageRoles
            if ($userRole !== 'super_admin') {
                if (!in_array($userRole, $pageRoles[$uri], true)) {
                    http_response_code(403);
                    $roleSafe = htmlspecialchars(strtoupper($userRole ?? ''));
                    $uriSafe  = htmlspecialchars($uri);
                    include BASE_PATH . '/src/views/errors/403.php';
                    exit;
                }
            }
        }
        // ── Akhir role guard ────────────────────────────────────────────────

        renderView($viewFile, compact('pageTitle', 'activeMenu'));
    }
} else {
    // 404
    http_response_code(404);
    include BASE_PATH . '/src/views/errors/404.php';
}
