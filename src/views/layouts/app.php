<?php
/**
 * Main App Layout
 * Usage: include this file, set $pageTitle, $activeMenu, $content vars
 */
$appName = 'Peace Seafood';
$baseUrl = '/peace_seafood';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — <?= $appName ?></title>
    <meta name="description" content="Peace Seafood - Sistem Manajemen Gudang Ikan">

    <!-- CRITICAL: Initialize theme BEFORE Alpine.js to prevent errors -->
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            document.documentElement.style.setProperty('--current-theme', savedTheme);
        })();
    </script>

    <!-- Tailwind CSS -->
    <!-- Suppressing warning for development - Use Tailwind CLI in production -->
    <script data-tailwind-config="true" src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- iziToast -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/izitoast.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/izitoast.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Custom CSS (bundled when available) -->
    <?php
    $bundledCssFs = BASE_PATH . '/public/build/app.min.css';
    $bundledCssUrl = $baseUrl . '/build/app.min.css';
    $bundledCssV = file_exists($bundledCssFs) ? (string) filemtime($bundledCssFs) : null;
    ?>
    <?php if ($bundledCssV): ?>
        <link rel="stylesheet" href="<?= $bundledCssUrl ?>?v=<?= $bundledCssV ?>">
    <?php else: ?>
        <link rel="stylesheet" href="<?= $baseUrl ?>/css/variables.css">
        <link rel="stylesheet" href="<?= $baseUrl ?>/css/dark-mode.css">
        <link rel="stylesheet" href="<?= $baseUrl ?>/css/custom.css">
    <?php endif; ?>

    <!-- PWA -->
    <link rel="manifest" href="<?= $baseUrl ?>/manifest.json">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27%3E%3Crect width=%27100%27 height=%27100%27 fill=%27%23ffffff%27 rx=%2720%27/%3E%3Cellipse cx=%2750%27 cy=%2750%27 rx=%2728%27 ry=%2720%27 fill=%27%232563eb%27/%3E%3Ccircle cx=%2765%27 cy=%2750%27 r=%2715%27 fill=%27%232563eb%27/%3E%3Cpath d=%27M 22 50 Q 15 40 8 35 Q 10 50 8 65 Q 15 60 22 50 Z%27 fill=%27%230891b2%27 opacity=%270.9%27/%3E%3Ccircle cx=%2770%27 cy=%2748%27 r=%273%27 fill=%27%23ffffff%27/%3E%3Cpath d=%27M 45 28 Q 48 18 50 12 Q 52 18 55 28 Z%27 fill=%27%230891b2%27 opacity=%270.8%27/%3E%3C/svg%3E">

    <script>
        // Tailwind config
        tailwind.config = {
            darkMode: ['class', '[data-theme="dark"]'],
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#2563eb', light: '#dbeafe', dark: '#1e40af' },
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444',
                        info: '#06b6d4',
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --color-primary: #2563eb;
            --color-primary-light: #dbeafe;
            --color-primary-dark: #1e40af;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
            --color-info: #06b6d4;
            --color-primary-soft: rgba(37, 99, 235, 0.08);
            --color-success-soft: rgba(16, 185, 129, 0.08);
            --color-warning-soft: rgba(245, 158, 11, 0.08);
            --color-danger-soft: rgba(239, 68, 68, 0.08);
            --color-info-soft: rgba(6, 182, 212, 0.08);
            --bg-light: #ffffff;
            --bg-gray: #f8fafc;
            --bg-secondary: #f1f5f9;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
        }

        [data-theme="dark"] {
            --bg-light: #1e293b;
            --bg-gray: #0f172a;
            --bg-secondary: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --border-color: #475569;
            --color-primary-soft: rgba(37, 99, 235, 0.18);
            --color-success-soft: rgba(16, 185, 129, 0.16);
            --color-warning-soft: rgba(245, 158, 11, 0.16);
            --color-danger-soft: rgba(239, 68, 68, 0.16);
            --color-info-soft: rgba(6, 182, 212, 0.16);
        }

        body {
            background: var(--bg-gray);
            color: var(--text-primary);
            transition: background 0.3s, color 0.3s;
        }

        .sidebar {
            background: var(--bg-light);
            border-right: 1px solid var(--border-color);
        }

        .card {
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 1rem;
            border-radius: 0.5rem;
            color: var(--text-secondary);
            transition: all 0.2s;
            font-size: 0.875rem;
            text-decoration: none;
        }

        .nav-link:hover {
            background: var(--color-primary-light);
            color: var(--color-primary);
        }

        .nav-link.active {
            background: var(--color-primary-light);
            color: var(--color-primary);
            font-weight: 600;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .badge-info {
            background: rgba(6, 182, 212, 0.1);
            color: #0891b2;
        }

        .badge-gray {
            background: rgba(100, 116, 139, 0.1);
            color: #64748b;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--color-primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--color-primary-dark);
        }

        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--border-color);
        }

        .btn-danger {
            background: var(--color-danger);
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-success {
            background: var(--color-success);
            color: white;
        }

        .btn-warning {
            background: var(--color-warning);
            color: white;
        }

        .form-input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            background: var(--bg-light);
            color: var(--text-primary);
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px var(--color-primary-light);
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.375rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .table th {
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            background: var(--bg-gray);
            border-bottom: 1px solid var(--border-color);
        }

        .table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .table tr:hover td {
            background: var(--bg-gray);
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-box {
            background: var(--bg-light);
            border-radius: 0.75rem;
            padding: 1.5rem;
            width: 100%;
            max-width: 32rem;
            max-height: 90vh;
            overflow-y: auto;
        }

        .stat-card {
            background: var(--bg-light);
            border-radius: 0.75rem;
            padding: 1.25rem;
            border: 1px solid var(--border-color);
        }

        .text-red-500,
        .text-red-600 {
            color: var(--color-danger) !important;
        }

        .text-green-500,
        .text-green-600 {
            color: var(--color-success) !important;
        }

        .text-yellow-500 {
            color: var(--color-warning) !important;
        }

        .text-blue-500 {
            color: var(--color-primary) !important;
        }

        .text-blue-600 {
            color: var(--color-primary-dark) !important;
        }

        .text-gray-400 {
            color: #94a3b8 !important;
        }

        .text-gray-500 {
            color: var(--text-secondary) !important;
        }

        .text-gray-600 {
            color: var(--text-primary) !important;
        }

        .bg-gray-50 {
            background-color: var(--bg-gray) !important;
        }

        .bg-blue-50 {
            background-color: var(--color-primary-soft) !important;
        }

        .bg-green-50 {
            background-color: var(--color-success-soft) !important;
        }

        .bg-yellow-50 {
            background-color: var(--color-warning-soft) !important;
        }

        .bg-red-50 {
            background-color: var(--color-danger-soft) !important;
        }

        .border-gray-200 {
            border-color: var(--border-color) !important;
        }

        .border-blue-500 {
            border-color: var(--color-primary) !important;
        }

        .border-green-500 {
            border-color: var(--color-success) !important;
        }

        .border-yellow-500 {
            border-color: var(--color-warning) !important;
        }

        .border-red-500 {
            border-color: var(--color-danger) !important;
        }

        .hover\:text-gray-600:hover {
            color: var(--text-primary) !important;
        }

        .hover\:bg-red-50:hover {
            background-color: var(--color-danger-soft) !important;
        }

        .hover\:bg-blue-50:hover {
            background-color: var(--color-primary-soft) !important;
        }

        .hover\:bg-green-50:hover {
            background-color: var(--color-success-soft) !important;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen" x-data="appLayout()" x-init="init()">

    <!-- Sidebar -->
    <aside class="sidebar fixed top-0 left-0 h-full w-64 flex flex-col z-30 transition-transform duration-300"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-64 lg:translate-x-0'">

        <!-- Logo -->
        <a href="/peace_seafood/dashboard"
            class="flex items-center gap-3 px-6 py-5 border-b cursor-pointer hover:opacity-80 transition-opacity"
            style="border-color: var(--border-color)">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                style="background: linear-gradient(135deg, #2563eb 0%, #0891b2 100%)">
                <i data-lucide="fish" class="w-5 h-5 text-white"></i>
            </div>
            <div>
                <p class="font-bold text-sm" style="color: var(--text-primary)">Peace Seafood</p>
                <p class="text-xs" style="color: var(--text-secondary)"
                    x-text="currentUser.nama_gudang || 'All Gudang'"></p>
            </div>
        </a>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

            <a href="/peace_seafood/dashboard"
                class="nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                Dashboard
            </a>

            <!-- Stok -->
            <div class="pt-2 pb-1 px-3 text-xs font-semibold uppercase tracking-wider"
                style="color: var(--text-secondary)">Operasional</div>

            <a href="/peace_seafood/stok" class="nav-link <?= ($activeMenu ?? '') === 'stok' ? 'active' : '' ?>"
                x-show="['bos','admin','checker'].includes(currentUser.role)">
                <i data-lucide="package" class="w-4 h-4"></i>
                Stok
            </a>

            <a href="/peace_seafood/penjualan"
                class="nav-link <?= ($activeMenu ?? '') === 'penjualan' ? 'active' : '' ?>"
                x-show="['bos','admin'].includes(currentUser.role)">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                Penjualan
            </a>

            <a href="/peace_seafood/penitipan"
                class="nav-link <?= ($activeMenu ?? '') === 'penitipan' ? 'active' : '' ?>"
                x-show="['bos','admin'].includes(currentUser.role)">
                <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                Penitipan
            </a>

            <a href="/peace_seafood/retur" class="nav-link <?= ($activeMenu ?? '') === 'retur' ? 'active' : '' ?>"
                x-show="['bos','admin'].includes(currentUser.role)">
                <i data-lucide="refresh-ccw" class="w-4 h-4"></i>
                Retur
            </a>

            <a href="/peace_seafood/keuangan" class="nav-link <?= ($activeMenu ?? '') === 'keuangan' ? 'active' : '' ?>"
                x-show="['bos','admin'].includes(currentUser.role)">
                <i data-lucide="wallet" class="w-4 h-4"></i>
                Keuangan
            </a>

            <!-- Master & Laporan -->
            <div class="pt-2 pb-1 px-3 text-xs font-semibold uppercase tracking-wider"
                style="color: var(--text-secondary)">Data & Laporan</div>

            <a href="/peace_seafood/master-data"
                class="nav-link <?= ($activeMenu ?? '') === 'master-data' ? 'active' : '' ?>">
                <i data-lucide="database" class="w-4 h-4"></i>
                Master Data
            </a>

            <a href="/peace_seafood/laporan" class="nav-link <?= ($activeMenu ?? '') === 'laporan' ? 'active' : '' ?>">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                Laporan
            </a>

            <!-- Settings (Bos Only) -->
            <div class="pt-2 pb-1 px-3 text-xs font-semibold uppercase tracking-wider"
                style="color: var(--text-secondary)" x-show="currentUser.role === 'bos'">Pengaturan</div>

            <a href="/peace_seafood/settings" class="nav-link <?= ($activeMenu ?? '') === 'settings' ? 'active' : '' ?>"
                x-show="currentUser.role === 'bos'">
                <i data-lucide="settings" class="w-4 h-4"></i>
                Settings
            </a>
        </nav>

        <!-- User info bottom -->
        <div class="px-4 py-4 border-t" style="border-color: var(--border-color)">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold"
                    style="background: var(--color-primary)">
                    <span x-text="currentUser.name ? currentUser.name.charAt(0).toUpperCase() : 'U'"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate" style="color: var(--text-primary)"
                        x-text="currentUser.name"></p>
                    <p class="text-xs truncate" style="color: var(--text-secondary)"
                        x-text="currentUser.role?.toUpperCase()"></p>
                </div>
                <button @click="logout()" class="p-1 rounded hover:bg-red-50" title="Logout">
                    <i data-lucide="log-out" class="w-4 h-4 text-red-500"></i>
                </button>
            </div>
        </div>
    </aside>

    <!-- Sidebar overlay (mobile) -->
    <div class="fixed inset-0 bg-black/50 z-20 lg:hidden" x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak>
    </div>

    <!-- Main Content -->
    <div class="lg:ml-64 min-h-screen flex flex-col">

        <!-- Top Navbar -->
        <header class="sticky top-0 z-10"
            style="background: var(--bg-light); border-bottom: 1px solid var(--border-color)">
            <div class="flex items-center gap-4 px-4 py-3">

                <!-- Hamburger (mobile) -->
                <button class="lg:hidden p-2 rounded-lg" style="color: var(--text-secondary)"
                    @click="sidebarOpen = !sidebarOpen">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>

                <!-- Page Title -->
                <h1 class="font-semibold text-lg" style="color: var(--text-primary)">
                    <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?>
                </h1>

                <div class="flex-1"></div>

                <!-- Notifications -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open; loadNotif()" class="relative p-2 rounded-lg"
                        style="color: var(--text-secondary)">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-red-500" x-show="unreadCount > 0"
                            x-cloak></span>
                    </button>

                    <!-- Dropdown notif -->
                    <div x-show="open" @click.away="open = false"
                        class="absolute right-0 top-12 w-80 rounded-xl shadow-lg z-50"
                        style="background: var(--bg-light); border: 1px solid var(--border-color)" x-cloak>
                        <div class="p-4 flex items-center justify-between border-b"
                            style="border-color: var(--border-color)">
                            <span class="font-semibold text-sm">Notifikasi</span>
                            <button @click="markAllRead()" class="text-xs" style="color: var(--color-primary)">Tandai
                                Semua Dibaca</button>
                        </div>
                        <div class="max-h-72 overflow-y-auto">
                            <template x-if="notifList.length === 0">
                                <div class="p-6 text-center text-sm" style="color: var(--text-secondary)">
                                    Tidak ada notifikasi
                                </div>
                            </template>
                            <template x-for="n in notifList" :key="n.id">
                                <div class="p-3 border-b hover:opacity-80 cursor-pointer"
                                    style="border-color: var(--border-color)"
                                    :style="n.is_read == 0 ? 'background: var(--color-primary-soft); color: var(--text-primary)' : ''"
                                    @click="markRead(n.id)">
                                    <p class="text-xs" style="color: var(--text-primary)" x-text="n.pesan"></p>
                                    <p class="text-xs mt-1" style="color: var(--text-secondary)" x-text="n.created_at">
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Dark mode toggle -->
                <button @click="toggleTheme()" class="p-2 rounded-lg" style="color: var(--text-secondary)"
                    title="Toggle Dark Mode" x-cloak>
                    <i data-lucide="moon" class="w-5 h-5" x-show="theme === 'light'"></i>
                    <i data-lucide="sun" class="w-5 h-5" x-show="theme === 'dark'"></i>
                </button>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-4 md:p-6">
            <?= $content ?? '' ?>
        </main>

        <!-- Footer -->
        <footer class="px-6 py-3 text-center text-xs"
            style="color: var(--text-secondary); border-top: 1px solid var(--border-color)">
            &copy; <?= date('Y') ?> Peace Seafood — Sistem Manajemen Gudang Ikan
        </footer>
    </div>

    <!-- Custom JS (bundled when available) -->
    <?php
    $bundledJsFs = BASE_PATH . '/public/build/app.min.js';
    $bundledJsUrl = $baseUrl . '/build/app.min.js';
    $bundledJsV = file_exists($bundledJsFs) ? (string) filemtime($bundledJsFs) : null;
    ?>
    <?php if ($bundledJsV): ?>
        <script src="<?= $bundledJsUrl ?>?v=<?= $bundledJsV ?>"></script>
    <?php else: ?>
        <script src="<?= $baseUrl ?>/js/api-client.js"></script>
        <script src="<?= $baseUrl ?>/js/utils.js"></script>
        <script src="<?= $baseUrl ?>/js/auth.js"></script>
    <?php endif; ?>

    <script>
        function appLayout() {
            return {
                sidebarOpen: false,
                theme: localStorage.getItem('theme') || 'light',
                currentUser: JSON.parse(localStorage.getItem('user') || '{}'),
                notifList: [],
                unreadCount: 0,

                init() {
                    /* Ensure theme is initialized before DOM references */
                    this.theme = localStorage.getItem('theme') || 'light';

                    // Apply saved theme
                    document.documentElement.setAttribute('data-theme', this.theme);

                    // Init Lucide icons
                    if (window.lucide) lucide.createIcons();

                    // Check auth
                    const token = localStorage.getItem('token');
                    if (!token) {
                        window.location.href = '/peace_seafood/login';
                        return;
                    }

                    // Setup axios auth header
                    window.API_TOKEN = token;

                    // Load unread count
                    this.loadUnreadCount();
                },

                toggleTheme() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    document.documentElement.setAttribute('data-theme', this.theme);
                    if (window.lucide) lucide.createIcons();
                },

                async loadUnreadCount() {
                    try {
                        const res = await apiClient.get('/notifikasi?unread=1');
                        this.unreadCount = res.data.data?.unread_count || 0;
                    } catch (e) { }
                },

                async loadNotif() {
                    try {
                        const res = await apiClient.get('/notifikasi');
                        this.notifList = res.data.data?.notifikasi || [];
                        this.unreadCount = res.data.data?.unread_count || 0;
                    } catch (e) { }
                },

                async markRead(id) {
                    try {
                        await apiClient.post(`/notifikasi/${id}/read`);
                        const n = this.notifList.find(x => x.id === id);
                        if (n) n.is_read = 1;
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    } catch (e) { }
                },

                async markAllRead() {
                    try {
                        await apiClient.post('/notifikasi/read-all');
                        this.notifList.forEach(n => n.is_read = 1);
                        this.unreadCount = 0;
                    } catch (e) { }
                },

                logout() {
                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                    window.location.href = '/peace_seafood/login';
                }
            };
        }
    </script>

    <!-- Product Detail Modal -->
    <div id="productModal" x-data="productModal()" x-init="init()" x-show="showModal" @click.self="closeModal()"
        @keydown.escape="closeModal()"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" x-transition.opacity
        style="display: none;">
        <div class="bg-white dark:bg-slate-800 rounded-lg max-w-3xl w-full max-h-[85vh] overflow-y-auto" @click.stop
            x-transition>
            <!-- Close button -->
            <div class="sticky top-0 flex justify-end p-4 border-b" style="border-color: var(--border-color);">
                <button @click="closeModal()"
                    class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Modal Content: Image (1/3) + Details (2/3) -->
            <div class="flex flex-col md:flex-row gap-6 p-6">
                <!-- Product Image (1/3) -->
                <div class="w-full md:w-1/3 shrink-0">
                    <div
                        class="w-full aspect-square bg-gray-100 dark:bg-slate-700 rounded-lg overflow-hidden flex items-center justify-center">
                        <!-- img selalu ada di DOM, src dikontrol Alpine -->
                        <img x-show="productImage" :src="productImage || ''" :alt="product.nama || ''"
                            class="max-h-full max-w-full object-contain" @error="productImage = ''">
                        <div x-show="!productImage" class="text-center py-8">
                            <i data-lucide="image-off" class="w-12 h-12 text-gray-300 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-400">Gambar tidak tersedia</p>
                        </div>
                    </div>
                </div>

                <!-- Product Details (2/3) -->
                <div class="flex-1">
                    <h2 class="text-2xl font-bold mb-1" style="color: var(--text-primary);" x-text="product.nama"></h2>
                    <p class="text-sm mb-4" style="color: var(--text-secondary);" x-text="product.nama_jenis"></p>

                    <!-- Details Grid -->
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-3 rounded-lg" style="background: var(--bg-secondary);">
                                <p class="text-xs font-semibold mb-1" style="color: var(--text-secondary);">Harga Beli
                                </p>
                                <p class="font-bold text-lg" style="color: var(--color-primary);"
                                    x-text="formatRupiah(product.harga_beli)"></p>
                            </div>
                            <div class="p-3 rounded-lg" style="background: var(--bg-secondary);">
                                <p class="text-xs font-semibold mb-1" style="color: var(--text-secondary);">Harga Jual
                                </p>
                                <p class="font-bold text-lg" style="color: var(--color-success);"
                                    x-text="formatRupiah(product.harga_jual)"></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-3 rounded-lg" style="background: var(--bg-secondary);">
                                <p class="text-xs font-semibold mb-1" style="color: var(--text-secondary);">Stok</p>
                                <p class="font-bold text-lg" style="color: var(--text-primary);"
                                    x-text="product.stok_qty + ' ' + (product.satuan || 'kg')"></p>
                            </div>
                            <div class="p-3 rounded-lg" style="background: var(--bg-secondary);">
                                <p class="text-xs font-semibold mb-1" style="color: var(--text-secondary);">Min. Stok
                                </p>
                                <p class="font-bold text-lg" style="color: var(--text-primary);"
                                    x-text="product.stok_minimum + ' ' + (product.satuan || 'kg')"></p>
                            </div>
                        </div>

                        <div class="p-3 rounded-lg" style="background: var(--bg-secondary);">
                            <p class="text-xs font-semibold mb-1" style="color: var(--text-secondary);">Nilai Stok</p>
                            <p class="font-bold text-lg" style="color: var(--color-warning);"
                                x-text="formatRupiah(product.nilai_stok)"></p>
                        </div>

                        <div x-show="product.deskripsi" class="p-3 rounded-lg" style="background: var(--bg-secondary);">
                            <p class="text-xs font-semibold mb-2" style="color: var(--text-secondary);">Deskripsi</p>
                            <p class="text-sm" style="color: var(--text-primary);" x-text="product.deskripsi"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?= $scripts ?? '' ?>

    <script>
        // Global Product Modal Handler
        let productModalInstance = null;

        function productModal() {
            return {
                showModal: false,
                product: {},
                productImage: '',

                init() {
                    productModalInstance = this;
                },

                openModal(product) {
                    this.product = product;
                    this.productImage = '';

                    // Gunakan field gambar dari database
                    const gambar = product.gambar || '';
                    if (gambar) {
                        // Images stored in /public/assets/images/ (not products subfolder)
                        this.productImage = '<?= $baseUrl ?>/assets/images/' + gambar;
                    }

                    this.showModal = true;
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                closeModal() {
                    this.showModal = false;
                    this.product = {};
                    this.productImage = '';
                },

                formatRupiah(n) {
                    return 'Rp ' + (parseFloat(n) || 0).toLocaleString('id-ID', { minimumFractionDigits: 0 });
                }
            };
        }

        // Global helper to show product detail
        window.showProductDetail = function (product) {
            if (productModalInstance) {
                productModalInstance.openModal(product);
            } else {
                console.warn('Product modal not initialized yet');
            }
        };

        // Re-init lucide icons after Alpine renders
        document.addEventListener('alpine:initialized', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>

</html>
