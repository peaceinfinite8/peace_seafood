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
    
    // Laporan
    '/laporan'             => ['laporan/index', 'Laporan & Export', 'laporan'],
    
    // Settings
    '/settings'            => ['settings/index', 'Pengaturan', 'settings'],
];

// Match route
if (isset($routes[$uri])) {
    $route      = $routes[$uri];
    $viewFile   = $route[0];
    $pageTitle  = $route[1];
    $activeMenu = $route[2] ?? '';
    
    // Login & root = no layout
    if (in_array($uri, ['/', '/login'])) {
        include BASE_PATH . '/src/views/' . $viewFile . '.php';
    } else {
        renderView($viewFile, compact('pageTitle', 'activeMenu'));
    }
} else {
    // 404
    http_response_code(404);
    echo '<!DOCTYPE html><html><body style="font-family:sans-serif;text-align:center;padding:3rem">';
    echo '<h1>404</h1><p>Halaman tidak ditemukan</p>';
    echo '<a href="/peace_seafood/dashboard" style="color:#2563eb">Kembali ke Dashboard</a>';
    echo '</body></html>';
}
