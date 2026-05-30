<?php

/**
 * Main App Layout
 * Usage: include this file, set $pageTitle, $activeMenu, $content vars
 */
try {
    $dbNameSetting = \App\Utils\Database::fetchOne("SELECT nilai FROM settings WHERE kunci = 'company_name' LIMIT 1");
    $dbLogoSetting = \App\Utils\Database::fetchOne("SELECT nilai FROM settings WHERE kunci = 'company_logo_base64' LIMIT 1");
    $dbInitialSetting = \App\Utils\Database::fetchOne("SELECT nilai FROM settings WHERE kunci = 'company_logo_initial' LIMIT 1");
} catch (\Exception $e) {
    $dbNameSetting = null;
    $dbLogoSetting = null;
    $dbInitialSetting = null;
}

$appName = $dbNameSetting ? $dbNameSetting['nilai'] : 'Peace Seafood';
$appLogoBase64 = $dbLogoSetting ? $dbLogoSetting['nilai'] : null;
$appLogoInitial = $dbInitialSetting ? $dbInitialSetting['nilai'] : 'PS';
$baseUrl = '/peace_seafood';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — <?= $appName ?></title>
    <meta name="description" content="Peace Seafood - Sistem Manajemen Gudang Ikan">

    <!-- Tailwind CSS runtime (self-hosted to avoid CDN warning) -->
    <script src="<?= $baseUrl ?>/js/tailwindcss.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" crossorigin="anonymous"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" crossorigin="anonymous"></script>
    <script src="/peace_seafood/inline-assets/js/layouts/app.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" crossorigin="anonymous"></script>

    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js" crossorigin="anonymous"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" crossorigin="anonymous"></script>

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" crossorigin="anonymous"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/variables.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/dark-mode.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/custom.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/ui-theme.css">

    <!-- PWA -->
    <link rel="manifest" href="<?= $baseUrl ?>/manifest.json">

    <link rel="stylesheet" href="/peace_seafood/inline-assets/css/layouts/app.css">
</head>

<body class="min-h-screen" x-data="appLayout()" x-init="init()">

    <!-- Realistic Coastal Animated Background Container -->
    <div class="coastal-bg-container" id="coastal-bg" aria-hidden="true">
        <!-- Sun/Moon Glow Orb -->
        <div class="coastal-celestial-orb"></div>

        <!-- Night Stars (Active in Night Mode) -->
        <div class="coastal-stars-container">
            <div class="micro-star star-1"></div>
            <div class="micro-star star-2"></div>
            <div class="micro-star star-3"></div>
            <div class="micro-star star-4"></div>
            <div class="micro-star star-5"></div>
        </div>

        <!-- Wind Breezes (Angin Sepoi-Sepoi) -->
        <div class="coastal-wind-container">
            <div class="wind-streak streak-1"></div>
            <div class="wind-streak streak-2"></div>
            <div class="wind-streak streak-3"></div>
        </div>

        <!-- Beach Shore & Waves -->
        <div class="coastal-shore">
            <!-- Sand Bed -->
            <div class="beach-sand"></div>

            <!-- Wave Layer 1 (Deepest water) -->
            <svg class="coastal-wave wave-back" viewBox="0 0 1440 160" preserveAspectRatio="none">
                <path d="M0,80 C320,130 640,30 960,100 C1280,170 1360,90 1440,80 L1440,160 L0,160 Z" />
            </svg>

            <!-- Wave Layer 2 (Mid-water) -->
            <svg class="coastal-wave wave-mid" viewBox="0 0 1440 160" preserveAspectRatio="none">
                <path d="M0,60 C360,110 720,20 1080,80 C1240,110 1360,50 1440,60 L1440,160 L0,160 Z" />
            </svg>

            <!-- Wave Layer 3 & Sea Foam (Front edge) -->
            <svg class="coastal-wave wave-front" viewBox="0 0 1440 160" preserveAspectRatio="none">
                <path d="M0,40 C280,90 560,10 840,70 C1120,130 1280,50 1440,40 L1440,160 L0,160 Z" />
            </svg>
        </div>
    </div>

    <!-- Nautical Page Loader Overlay -->
    <div id="nautical-page-loader" class="nautical-loader-overlay">
        <div class="loader-compass"></div>
        <div class="loader-text">MEMUAT SISTEM BAHARI...</div>
    </div>

    <!-- Sidebar -->
    <aside class="sidebar fixed top-0 left-0 h-full w-64 flex flex-col z-30 transition-transform duration-300"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-64 lg:translate-x-0'">

        <!-- Logo -->
        <div class="flex items-center gap-3 px-6 py-5 border-b" style="border-color: var(--border-color)">
            <?php if ($appLogoBase64): ?>
                <div class="w-9 h-9 rounded-lg overflow-hidden flex items-center justify-center">
                    <img src="<?= $appLogoBase64 ?>" class="w-full h-full object-cover">
                </div>
            <?php else: ?>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: var(--color-primary)">
                    <span class="text-white font-bold text-sm"><?= htmlspecialchars($appLogoInitial) ?></span>
                </div>
            <?php endif; ?>
            <div>
                <p class="font-bold text-sm truncate max-w-[140px]" style="color: var(--text-primary)">
                    <?= htmlspecialchars($appName) ?>
                </p>
                <p class="text-xs" style="color: var(--text-secondary)"
                    x-text="currentUser ? currentUser.nama_gudang || 'All Gudang' : 'All Gudang'"></p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

            <a href="/peace_seafood/dashboard"
                class="nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                Dashboard
            </a>

            <!-- Stok -->
            <div class="pt-2 pb-1 px-3 text-xs font-semibold uppercase tracking-wider"
                style="color: var(--text-secondary)" x-show="currentUser.role !== 'saas_owner'">Operasional</div>

            <a href="/peace_seafood/stok" class="nav-link <?= ($activeMenu ?? '') === 'stok' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin','checker'].includes(currentUser.role)">
                <i data-lucide="package" class="w-4 h-4"></i>
                Stok
            </a>

            <a href="/peace_seafood/penjualan"
                class="nav-link <?= ($activeMenu ?? '') === 'penjualan' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin'].includes(currentUser.role)">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                Penjualan
            </a>

            <!-- Checker: tombol kirim draft nota — menonjol dengan badge -->
            <a href="/peace_seafood/checker/draft-penjualan"
                class="nav-link <?= ($activeMenu ?? '') === 'checker-draft' ? 'active' : '' ?>"
                x-show="currentUser.role === 'checker'" style="position:relative">
                <i data-lucide="send" class="w-4 h-4"></i>
                Kirim Draft Nota
                <span class="ml-auto px-1.5 py-0.5 rounded-full text-xs font-bold"
                    style="background:var(--color-primary);color:#fff;font-size:10px">BARU</span>
            </a>

            <a href="/peace_seafood/keuangan" class="nav-link <?= ($activeMenu ?? '') === 'keuangan' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin'].includes(currentUser.role)">
                <i data-lucide="wallet" class="w-4 h-4"></i>
                Keuangan
            </a>

            <!-- Master & Laporan -->
            <div class="pt-2 pb-1 px-3 text-xs font-semibold uppercase tracking-wider"
                style="color: var(--text-secondary)" x-show="currentUser.role !== 'saas_owner'">Data & Laporan</div>

            <a href="/peace_seafood/master-data"
                class="nav-link <?= ($activeMenu ?? '') === 'master-data' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin'].includes(currentUser.role)">
                <i data-lucide="database" class="w-4 h-4"></i>
                Master Data
            </a>

            <a href="/peace_seafood/migrasi" class="nav-link <?= ($activeMenu ?? '') === 'migrasi' ? 'active' : '' ?>"
                x-show="['super_admin','admin'].includes(currentUser.role)">
                <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                Migrasi Data
            </a>

            <a href="/peace_seafood/laporan" class="nav-link <?= ($activeMenu ?? '') === 'laporan' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin'].includes(currentUser.role)">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                Laporan
            </a>

            <!-- Fitur jarang dipakai -->
            <div class="pt-2 pb-1 px-3 text-xs font-semibold uppercase tracking-wider"
                style="color: var(--text-secondary)" x-show="currentUser.role !== 'saas_owner'">Fitur Tambahan</div>

            <a href="/peace_seafood/penitipan"
                class="nav-link <?= ($activeMenu ?? '') === 'penitipan' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin'].includes(currentUser.role)">
                <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                Penitipan
            </a>

            <a href="/peace_seafood/retur" class="nav-link <?= ($activeMenu ?? '') === 'retur' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin'].includes(currentUser.role)">
                <i data-lucide="refresh-ccw" class="w-4 h-4"></i>
                Retur
            </a>

            <a href="/peace_seafood/stok-opname"
                class="nav-link <?= ($activeMenu ?? '') === 'stok-opname' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin','checker'].includes(currentUser.role)">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                Stok Opname
            </a>

            <a href="/peace_seafood/stok-transfer"
                class="nav-link <?= ($activeMenu ?? '') === 'stok-transfer' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin','checker'].includes(currentUser.role)">
                <i data-lucide="move-horizontal" class="w-4 h-4"></i>
                Stok Transfer
            </a>

            <a href="/peace_seafood/activity-log"
                class="nav-link <?= ($activeMenu ?? '') === 'activity-log' ? 'active' : '' ?>"
                x-show="['super_admin','bos'].includes(currentUser.role)">
                <i data-lucide="history" class="w-4 h-4"></i>
                Activity Log
            </a>

            <!-- Settings -->
            <div class="pt-2 pb-1 px-3 text-xs font-semibold uppercase tracking-wider"
                style="color: var(--text-secondary)"
                x-show="['super_admin', 'saas_owner', 'bos'].includes(currentUser.role)">Pengaturan</div>

            <a href="/peace_seafood/settings" class="nav-link <?= ($activeMenu ?? '') === 'settings' ? 'active' : '' ?>"
                x-show="['super_admin', 'saas_owner', 'bos'].includes(currentUser.role)">
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
                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false"
                    @close-notif-dropdown.window="open = false">
                    <button @click="open = !open; if(open) loadNotif();"
                        class="relative p-2 rounded-xl transition-all duration-200 hover:bg-gray-100/60 dark:hover:bg-slate-800/40"
                        style="color: var(--text-secondary)">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        <span
                            class="absolute -top-0.5 -right-0.5 w-5 h-5 flex items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-md animate-pulse"
                            x-show="unreadCount > 0" x-text="unreadCount" x-cloak></span>
                    </button>

                    <!-- Dropdown notif - @click.away disabled when dialogs open -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                        class="absolute right-0 top-12 w-96 rounded-2xl shadow-2xl z-50 backdrop-blur-xl"
                        style="background: var(--bg-light); border: 1px solid var(--border-color); box-shadow: 0 20px 40px -8px rgba(0,0,0,0.15), 0 8px 16px -4px rgba(0,0,0,0.08);"
                        x-cloak @click.outside="if(!window._swalOpen) open = false">

                        <!-- Header Dropdown -->
                        <div class="px-4 pt-4 pb-3 flex items-center justify-between"
                            style="border-bottom: 1px solid var(--border-color)">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                                    style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)">
                                    <i data-lucide="bell" class="w-3.5 h-3.5 text-white"></i>
                                </div>
                                <span class="font-bold text-sm" style="color: var(--text-primary)">Notifikasi</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold text-white"
                                    style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 2px 4px rgba(239,68,68,0.4);"
                                    x-show="unreadCount > 0" x-text="unreadCount + ' Baru'"></span>
                            </div>
                            <button @click="markAllRead()"
                                class="text-xs font-semibold px-2.5 py-1 rounded-lg transition-all hover:shadow-sm"
                                style="color: var(--color-primary); background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.15);">
                                Tandai Semua Dibaca
                            </button>
                        </div>

                        <!-- Tab Controls -->
                        <div class="flex text-xs font-semibold"
                            style="border-bottom: 1px solid var(--border-color); background: rgba(0,0,0,0.02);">
                            <button @click="notifTab = 'unread'"
                                class="flex-1 py-2.5 text-center border-b-2 transition-all duration-150"
                                :class="notifTab === 'unread' ? 'border-blue-500 text-blue-600 font-bold bg-blue-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50/50'">
                                <span class="flex items-center justify-center gap-1">
                                    <i data-lucide="inbox" class="w-3 h-3"></i> Belum Dibaca
                                </span>
                            </button>
                            <button @click="notifTab = 'all'"
                                class="flex-1 py-2.5 text-center border-b-2 transition-all duration-150"
                                :class="notifTab === 'all' ? 'border-blue-500 text-blue-600 font-bold bg-blue-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50/50'">
                                <span class="flex items-center justify-center gap-1">
                                    <i data-lucide="list" class="w-3 h-3"></i> Semua
                                </span>
                            </button>
                        </div>

                        <!-- Dropdown List -->
                        <div class="max-h-[400px] overflow-y-auto custom-scrollbar-thin">
                            <template x-if="filteredNotifList().length === 0">
                                <div class="p-8 text-center flex flex-col items-center justify-center">
                                    <div
                                        class="w-14 h-14 rounded-full bg-blue-50/50 dark:bg-slate-800/40 flex items-center justify-center mb-3 border border-blue-100/30 dark:border-slate-700/10 relative">
                                        <i data-lucide="bell"
                                            class="w-6 h-6 text-primary animate-[pulse_2s_infinite]"></i>
                                        <span
                                            class="absolute top-0 right-0 w-2.5 h-2.5 rounded-full bg-blue-400"></span>
                                    </div>
                                    <span class="font-semibold text-xs mb-1" style="color: var(--text-primary)">Kotak
                                        Masuk Bersih</span>
                                    <p class="text-[10px] leading-relaxed max-w-[200px]"
                                        style="color: var(--text-secondary)">
                                        Semua sunyi di sini. Seluruh pekerjaan Anda telah diselesaikan dengan sempurna!
                                    </p>
                                </div>
                            </template>

                            <template x-for="n in filteredNotifList()" :key="n.id">
                                <div class="relative group"
                                    :class="n._deleting ? 'opacity-30 pointer-events-none transition-opacity duration-500' : ''">
                                    <div class="px-4 py-3.5 border-b transition-all duration-200 cursor-pointer flex gap-3"
                                        :class="n.is_read == 0 ? 'bg-blue-50/50 dark:bg-blue-950/15' : 'hover:bg-gray-50/40 dark:hover:bg-slate-800/10'"
                                        style="border-color: var(--border-color)" @click="handleNotifClick(n)">

                                        <!-- Unread left indicator bar -->
                                        <div class="absolute left-0 top-0 bottom-0 w-0.5 rounded-r bg-blue-500 transition-all duration-300"
                                            :class="n.is_read == 0 ? 'opacity-100' : 'opacity-0'"></div>

                                        <!-- Left: Icon circle -->
                                        <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center border"
                                            :class="getNotifBgClass(n.tipe)">
                                            <i :data-lucide="getNotifIcon(n.tipe)" class="w-4 h-4"
                                                :class="getNotifIconColorClass(n.tipe)"></i>
                                        </div>

                                        <!-- Middle: Content -->
                                        <div class="flex-1 min-w-0 pr-10">
                                            <div class="flex items-center gap-1.5 mb-0.5">
                                                <span class="font-bold text-xs" style="color: var(--text-primary)"
                                                    x-text="n.judul"></span>
                                                <!-- Unread pulse dot -->
                                                <span
                                                    class="w-1.5 h-1.5 rounded-full bg-blue-500 flex-shrink-0 animate-pulse"
                                                    x-show="n.is_read == 0"></span>
                                            </div>
                                            <p class="text-[11px] leading-relaxed mb-1.5"
                                                style="color: var(--text-secondary)" x-text="n.pesan"></p>
                                            <div class="flex items-center gap-1 text-[9px]"
                                                style="color: var(--text-secondary)">
                                                <i data-lucide="clock" class="w-3 h-3"></i>
                                                <span x-text="formatTime(n.created_at)"></span>
                                                <!-- Navigation hint -->
                                                <span x-show="getNotifLink(n)" class="ml-1 text-blue-400 font-medium">·
                                                    Klik untuk buka →</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action buttons on hover - positioned over item -->
                                    <div
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-200 z-20 flex items-center gap-1">
                                        <!-- Mark Read (if unread) -->
                                        <button x-show="n.is_read == 0" @click.stop="quickDismiss(n.id)"
                                            class="w-7 h-7 rounded-full shadow-lg border flex items-center justify-center transition-all duration-150 hover:scale-110"
                                            style="background: white; border-color: #d1fae5;" title="Tandai dibaca">
                                            <i data-lucide="check" class="w-3.5 h-3.5" style="color: #059669"></i>
                                        </button>
                                        <!-- Delete button (always on hover) -->
                                        <button @click.stop="deleteNotif(n.id)"
                                            class="w-7 h-7 rounded-full shadow-lg border flex items-center justify-center transition-all duration-150 hover:scale-110"
                                            style="background: white; border-color: #fecaca;" title="Hapus notifikasi">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5" style="color: #ef4444"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Footer -->
                        <div class="px-4 py-2.5 text-center"
                            style="border-top: 1px solid var(--border-color); background: rgba(0,0,0,0.02);">
                            <p class="text-[10px]" style="color: var(--text-secondary);">Klik notifikasi untuk navigasi
                                otomatis &amp; tandai dibaca</p>
                        </div>
                    </div>
                </div>

                <!-- Dark mode toggle -->
                <button @click="toggleTheme()" class="p-2 rounded-lg" style="color: var(--text-secondary)"
                    title="Toggle Dark Mode">
                    <i data-lucide="moon" class="w-5 h-5 theme-toggle-icon-light"></i>
                    <i data-lucide="sun" class="w-5 h-5 theme-toggle-icon-dark"></i>
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
            &copy; <?= date('Y') ?> <?= htmlspecialchars($appName) ?> — Sistem Manajemen Gudang Ikan
        </footer>
    </div>

    <!-- Custom JS -->
    <script src="<?= $baseUrl ?>/js/api-client.js"></script>
    <script src="<?= $baseUrl ?>/js/utils.js"></script>
    <script src="<?= $baseUrl ?>/js/chart-config.js"></script>
    <script src="<?= $baseUrl ?>/js/ui-theme.js"></script>

    <!-- ═══════════════════════════════════════════════════════════════
         FLOATING CALCULATOR — FAB + POPUP
         Shortcut: Alt+C to toggle
    ═══════════════════════════════════════════════════════════════ -->
    <div x-data="floatingCalc()" x-init="initCalc()" @keydown.window="handleKey($event)">

        <!-- FAB Button -->
        <button @click="open = !open" title="Kalkulator (Alt+C)"
            class="fixed z-50 flex items-center justify-center rounded-full shadow-lg transition-all duration-300"
            style="
                bottom: 28px; right: 28px;
                width: 52px; height: 52px;
                background: var(--color-primary);
                color: white;
                border: none;
                cursor: pointer;
                box-shadow: 0 4px 20px rgba(37,99,235,0.4);
            " :style="open ? 'transform: rotate(45deg); background: var(--color-danger)' : ''">
            <svg x-show="!open" style="width:22px;height:22px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" />
            </svg>
            <svg x-show="!open" x-cloak style="display:none"></svg>
            <!-- X icon when open -->
            <svg x-show="open" x-cloak style="width:22px;height:22px" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Calculator Popup -->
        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95" @click.outside="open = false" class="fixed z-50"
            style="bottom: 92px; right: 28px; width: 280px;">
            <div style="
                background: var(--bg-light);
                border: 1px solid var(--border-color);
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 20px 60px rgba(0,0,0,0.18), 0 0 0 1px rgba(255,255,255,0.05) inset;
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
            ">
                <!-- Header -->
                <div style="
                    padding: 12px 16px 8px;
                    display: flex; align-items: center; justify-content: space-between;
                    border-bottom: 1px solid var(--border-color);
                ">
                    <span
                        style="font-size:12px; font-weight:600; color: var(--text-secondary); letter-spacing:0.05em; text-transform:uppercase;">Kalkulator</span>
                    <span style="font-size:10px; color: var(--text-secondary); opacity:0.6">Alt+C</span>
                </div>

                <!-- Display -->
                <div style="padding: 12px 16px 8px; text-align: right;">
                    <div style="font-size:11px; color: var(--text-secondary); min-height:16px; margin-bottom:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                        x-text="expression || '&nbsp;'"></div>
                    <div style="font-size:28px; font-weight:700; color: var(--text-primary); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; line-height:1.2;"
                        x-text="display"></div>
                </div>

                <!-- Buttons Grid -->
                <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:6px; padding: 8px 12px 14px;">

                    <!-- Row 1: AC, +/-, %, ÷ -->
                    <template x-for="btn in buttons" :key="btn.label">
                        <button @click="pressBtn(btn)" style="
                                height: 52px;
                                border-radius: 12px;
                                border: none;
                                font-size: 15px;
                                font-weight: 600;
                                cursor: pointer;
                                transition: all 0.12s ease;
                            " :style="getBtnStyle(btn)" @mousedown="$el.style.transform='scale(0.92)'"
                            @mouseup="$el.style.transform='scale(1)'" @mouseleave="$el.style.transform='scale(1)'"
                            x-text="btn.label"></button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <?= $scripts ?? '' ?>

    <!-- =========================================================================
         🔒 SAAS BYPASS GUARD & SUBSCRIPTION SYSTEM OVERLAYS
         ========================================================================= -->

    <!-- 🔒 1. FORCE CHANGE PASSWORD OVERLAY (FIRST LOGIN SANITY GUARD) -->
    <div class="fixed inset-0 bg-slate-950/95 backdrop-blur-xl z-[9999] flex items-center justify-center p-4"
        x-show="showFirstLoginModal" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div
            class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 md:p-8 shadow-2xl relative overflow-hidden">
            <!-- Ocean sparkle bg effects -->
            <div class="absolute -top-24 -left-24 w-48 h-48 rounded-full bg-cyan-500/10 blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-24 -right-24 w-48 h-48 rounded-full bg-emerald-500/10 blur-3xl animate-pulse">
            </div>

            <div class="relative text-center mb-6">
                <div
                    class="w-14 h-14 bg-gradient-to-br from-cyan-400 to-sky-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-sky-500/20">
                    <i data-lucide="shield-check" class="w-7 h-7 text-white"></i>
                </div>
                <h3 class="text-xl font-extrabold text-white tracking-tight">Ganti Password Default</h3>
                <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">Demi keamanan sistem, Anda wajib mengubah sandi
                    sementara Anda sekarang sebelum menggunakan dashboard operasional.</p>
            </div>

            <form @submit.prevent="forceChangePassword()" class="space-y-4 relative">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Password
                        Baru</label>
                    <div class="relative">
                        <input type="password" x-model="firstLoginPass"
                            class="w-full h-11 bg-slate-950 border border-slate-800 rounded-xl px-4 text-sm text-white focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                            placeholder="Masukkan password baru">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Konfirmasi
                        Password Baru</label>
                    <div class="relative">
                        <input type="password" x-model="firstLoginPassConfirm"
                            class="w-full h-11 bg-slate-950 border border-slate-800 rounded-xl px-4 text-sm text-white focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                            placeholder="Ulangi password baru">
                    </div>
                </div>

                <!-- Password Strength List -->
                <div class="space-y-1.5 p-3 rounded-2xl border border-slate-800 bg-slate-950/40 text-left">
                    <span class="block text-[9px] font-bold uppercase tracking-wider text-slate-500 mb-1">Kekuatan Sandi
                        Baru:</span>
                    <div class="flex items-center gap-2 text-xs"
                        :class="forcePasswordChecks.length ? 'text-emerald-400 font-medium' : 'text-slate-500'">
                        <i data-lucide="check" class="w-4 h-4"></i> Minimal 8 karakter
                    </div>
                    <div class="flex items-center gap-2 text-xs"
                        :class="forcePasswordChecks.upper ? 'text-emerald-400 font-medium' : 'text-slate-500'">
                        <i data-lucide="check" class="w-4 h-4"></i> Huruf besar (A-Z)
                    </div>
                    <div class="flex items-center gap-2 text-xs"
                        :class="forcePasswordChecks.lower ? 'text-emerald-400 font-medium' : 'text-slate-500'">
                        <i data-lucide="check" class="w-4 h-4"></i> Huruf kecil (a-z)
                    </div>
                    <div class="flex items-center gap-2 text-xs"
                        :class="forcePasswordChecks.number ? 'text-emerald-400 font-medium' : 'text-slate-500'">
                        <i data-lucide="check" class="w-4 h-4"></i> Angka (0-9)
                    </div>
                    <div class="flex items-center gap-2 text-xs"
                        :class="forcePasswordChecks.special ? 'text-emerald-400 font-medium' : 'text-slate-500'">
                        <i data-lucide="check" class="w-4 h-4"></i> Karakter khusus (@, #, $, dll)
                    </div>
                </div>

                <button type="submit"
                    class="w-full h-11 rounded-xl bg-gradient-to-r from-cyan-500 to-sky-500 hover:opacity-95 transition-all text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-lg shadow-sky-500/20 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="!isForcePasswordStrong || firstLoginPass !== firstLoginPassConfirm || forceLoginLoading">
                    <span x-show="!forceLoginLoading" class="flex items-center gap-2">
                        Aktifkan Akun Sekarang
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </span>
                    <span x-show="forceLoginLoading" class="flex items-center gap-2">
                        <span
                            class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
                        Mengaktifkan...
                    </span>
                </button>
            </form>
        </div>
    </div>

    <!-- 🏢 2. ONBOARDING WIZARD 3 LANGKAH (SMART TRIAL ACTIVATOR) -->
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-xl z-[9990] flex items-center justify-center p-4 overflow-y-auto"
        x-show="showOnboardingWizard" x-cloak>
        <div
            class="w-full max-w-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl relative overflow-hidden flex flex-col my-8 max-h-[90vh]">
            <!-- Header & Steps tracker -->
            <div
                class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-950/20">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-blue-500/10 text-blue-500 rounded-xl flex items-center justify-center font-bold">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white leading-tight">Konfigurasi Gudang Baru</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Selesaikan setup singkat untuk
                            mulai trial gratis.</p>
                    </div>
                </div>

                <!-- Steps badges + tombol tutup -->
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold transition-colors"
                        :class="onboardingStep === 1 ? 'bg-blue-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500'">1</span>
                    <span class="w-4 h-0.5 bg-slate-200 dark:bg-slate-700"></span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold transition-colors"
                        :class="onboardingStep === 2 ? 'bg-blue-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500'">2</span>
                    <span class="w-4 h-0.5 bg-slate-200 dark:bg-slate-700"></span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold transition-colors"
                        :class="onboardingStep === 3 ? 'bg-blue-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500'">3</span>
                    <!-- Tombol tutup wizard -->
                    <button type="button" @click="showOnboardingWizard = false"
                        class="ml-3 w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 transition-all"
                        title="Tutup wizard (isi nanti)">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Steps Body -->
            <div class="p-6 md:p-8 overflow-y-auto flex-1 custom-scrollbar-thin text-left">

                <!-- STEP 1: IDENTITAS GUDANG -->
                <div x-show="onboardingStep === 1" class="space-y-5">
                    <div class="text-center max-w-md mx-auto mb-6">
                        <h4 class="text-base font-bold text-slate-800 dark:text-white">Langkah 1: Profil Gudang Ikan
                            Anda</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Masukkan data identitas gudang Anda
                            untuk dicantumkan pada cetak struk nota kasir thermal.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label text-slate-700 dark:text-slate-300">Nama Gudang /
                                Perusahaan</label>
                            <input type="text" x-model="onboardingForm.nama_gudang" class="form-input"
                                placeholder="Contoh: Gudang Hasil Bahari">
                        </div>
                        <div>
                            <label class="form-label text-slate-700 dark:text-slate-300">Kota Lokasi Gudang</label>
                            <input type="text" x-model="onboardingForm.kota" class="form-input"
                                placeholder="Contoh: Manado">
                        </div>
                    </div>
                    <div>
                        <label class="form-label text-slate-700 dark:text-slate-300">Alamat Lengkap Gudang</label>
                        <textarea x-model="onboardingForm.alamat" rows="2" class="form-input py-2"
                            placeholder="Tulis alamat operasional lengkap..."></textarea>
                    </div>

                    <!-- Company Logo Uploader -->
                    <div>
                        <label class="form-label text-slate-700 dark:text-slate-300">Logo Perusahaan (Optional)</label>
                        <div
                            class="border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl p-4 flex flex-col items-center justify-center bg-slate-50/50 dark:bg-slate-950/20 transition-all hover:border-blue-400 cursor-pointer relative">
                            <input type="file" @change="handleLogoUpload($event)"
                                class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                            <template x-if="onboardingForm.logo_base64">
                                <div class="flex items-center gap-3">
                                    <img :src="onboardingForm.logo_base64"
                                        class="w-12 h-12 rounded-lg object-cover border border-slate-200 dark:border-slate-800">
                                    <div class="text-left">
                                        <p class="text-xs font-bold text-emerald-500">Logo Berhasil Dipasang</p>
                                        <p class="text-[10px] text-slate-500">Klik untuk mengganti gambar</p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!onboardingForm.logo_base64">
                                <div class="text-center">
                                    <i data-lucide="image" class="w-6 h-6 text-slate-400 mx-auto mb-1.5"></i>
                                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">Pilih / Drag
                                        foto logo (.png/.jpg)</p>
                                    <p class="text-[9px] text-slate-500 mt-0.5">Maksimal resolusi persegi 500x500px</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: CHECKLIST IKAN PILIHAN -->
                <div x-show="onboardingStep === 2" class="space-y-5">
                    <div class="text-center max-w-md mx-auto mb-6">
                        <h4 class="text-base font-bold text-slate-800 dark:text-white">Langkah 2: Jenis Ikan yang Anda
                            Kelola</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pilih ikan laut/tawar lokal yang akan
                            otomatis dimasukkan sebagai basis Master Data Anda.</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <template x-for="fish in availableFishes" :key="fish">
                            <label
                                class="flex items-center gap-3 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 cursor-pointer transition-all hover:bg-blue-50/20 hover:border-blue-400/50">
                                <input type="checkbox" :value="fish" x-model="onboardingForm.ikan_pilihan"
                                    class="rounded border-slate-300 text-blue-500 focus:ring-blue-500 w-4 h-4">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="fish"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- STEP 3: EXCEL COLUMNS MAPPER -->
                <div x-show="onboardingStep === 3" class="space-y-5">
                    <div class="text-center max-w-md mx-auto mb-4">
                        <h4 class="text-base font-bold text-slate-800 dark:text-white">Langkah 3: Integrasi Excel Column
                            Mapper</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Uji pemetaan kolom Excel Anda ke
                            format sistem gudang agar migrasi data transaksional masa lalu berjalan instan.</p>
                    </div>

                    <!-- Drag & Drop Uploader -->
                    <div
                        class="border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl p-6 flex flex-col items-center justify-center bg-slate-50/50 dark:bg-slate-950/20 transition-all hover:border-blue-400 cursor-pointer relative">
                        <input type="file" @change="handleExcelUpload($event)"
                            class="absolute inset-0 opacity-0 cursor-pointer" accept=".xlsx,.xls,.csv">
                        <div class="text-center" x-show="!excelUploaded">
                            <i data-lucide="file-spreadsheet" class="w-8 h-8 text-blue-500 mx-auto mb-2"></i>
                            <p class="text-xs font-bold text-slate-700 dark:text-white">Upload File Excel Penjualan /
                                Stok Anda</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Mendukung format .xlsx, .xls, atau .csv</p>
                        </div>
                        <div class="text-center flex items-center gap-3" x-show="excelUploaded" x-cloak>
                            <div
                                class="w-10 h-10 bg-emerald-500/10 text-emerald-500 rounded-lg flex items-center justify-center">
                                <i data-lucide="check" class="w-5 h-5"></i>
                            </div>
                            <div class="text-left">
                                <p class="text-xs font-bold text-emerald-500" x-text="excelFileName"></p>
                                <p class="text-[10px] text-slate-500">Berhasil diunggah! Kolom terbaca otomatis.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic columns mapper UI -->
                    <div
                        class="p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 text-left">
                        <span class="block text-xs font-bold text-slate-700 dark:text-white mb-3">Petakan Kolom Excel
                            Anda:</span>

                        <div class="space-y-3">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-slate-200/50 dark:border-slate-800/40">
                                <div class="text-xs font-bold text-slate-600 dark:text-slate-400">
                                    <span class="text-red-500">*</span> Kolom Tanggal Transaksi:
                                </div>
                                <select x-model="onboardingForm.mapper.tanggal" class="form-input sm:w-48 py-1 text-xs">
                                    <template x-for="col in excelColumns" :key="col">
                                        <option :value="col" x-text="col"></option>
                                    </template>
                                </select>
                            </div>

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-slate-200/50 dark:border-slate-800/40">
                                <div class="text-xs font-bold text-slate-600 dark:text-slate-400">
                                    <span class="text-red-500">*</span> Kolom Nama Jenis Ikan:
                                </div>
                                <select x-model="onboardingForm.mapper.jenis_ikan"
                                    class="form-input sm:w-48 py-1 text-xs">
                                    <template x-for="col in excelColumns" :key="col">
                                        <option :value="col" x-text="col"></option>
                                    </template>
                                </select>
                            </div>

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-slate-200/50 dark:border-slate-800/40">
                                <div class="text-xs font-bold text-slate-600 dark:text-slate-400">
                                    <span class="text-red-500">*</span> Kolom Volume / Berat (kg):
                                </div>
                                <select x-model="onboardingForm.mapper.berat" class="form-input sm:w-48 py-1 text-xs">
                                    <template x-for="col in excelColumns" :key="col">
                                        <option :value="col" x-text="col"></option>
                                    </template>
                                </select>
                            </div>

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-slate-200/50 dark:border-slate-800/40">
                                <div class="text-xs font-bold text-slate-600 dark:text-slate-400">
                                    <span class="text-red-500">*</span> Kolom Harga Satuan (Rp):
                                </div>
                                <select x-model="onboardingForm.mapper.harga" class="form-input sm:w-48 py-1 text-xs">
                                    <template x-for="col in excelColumns" :key="col">
                                        <option :value="col" x-text="col"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="text-xs font-bold text-slate-600 dark:text-slate-400">
                                    Kolom Nama Pembeli / Supplier:
                                </div>
                                <select x-model="onboardingForm.mapper.pembeli" class="form-input sm:w-48 py-1 text-xs">
                                    <template x-for="col in excelColumns" :key="col">
                                        <option :value="col" x-text="col"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Buttons -->
            <div
                class="p-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 flex items-center justify-between">
                <!-- Kiri: Kembali (step > 1) atau Lewati (step 1) -->
                <div>
                    <button type="button"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-semibold text-xs transition-all hover:bg-slate-100 dark:hover:bg-slate-800"
                        @click="onboardingStep = Math.max(1, onboardingStep - 1)" x-show="onboardingStep > 1" x-cloak>
                        ← Kembali
                    </button>
                    <button type="button"
                        class="px-5 py-2.5 rounded-xl border border-amber-200 dark:border-amber-900/40 text-amber-600 dark:text-amber-400 font-semibold text-xs transition-all hover:bg-amber-50 dark:hover:bg-amber-950/20"
                        @click="showOnboardingWizard = false" x-show="onboardingStep === 1" x-cloak>
                        Lewati, isi nanti →
                    </button>
                </div>

                <!-- Kanan: Lanjutkan / Selesai -->
                <button type="button"
                    class="px-5 py-2.5 rounded-xl bg-blue-500 hover:bg-blue-600 transition-all text-white font-semibold text-xs flex items-center gap-2 shadow-lg shadow-blue-500/20 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="onboardingStep === 1 && !onboardingForm.nama_gudang" @click="handleOnboardingNext()">
                    <span x-text="onboardingStep === 3 ? 'Selesai & Aktifkan Trial' : 'Lanjutkan'"></span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- 🔒 2.5. ONBOARDING LOCK SCREEN FOR WMS PAGES (EXCEPT DASHBOARD) -->
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-xl z-[9990] flex items-center justify-center p-4 select-none"
        x-show="onboardingCompleted === false && currentUser.role !== 'saas_owner' && activeMenu !== 'dashboard'"
        x-cloak>
        <div
            class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-2xl relative text-center space-y-6">
            <!-- Ocean sparkle bg effects -->
            <div class="absolute -top-24 -left-24 w-48 h-48 rounded-full bg-blue-500/10 blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-24 -right-24 w-48 h-48 rounded-full bg-indigo-500/10 blur-3xl animate-pulse">
            </div>

            <div class="relative">
                <div
                    class="w-16 h-16 bg-blue-500/10 text-blue-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-500/5">
                    <i data-lucide="lock" class="w-8 h-8"></i>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Akses Fitur Terkunci
                </h3>

                <!-- Message for Bos -->
                <template x-if="currentUser.role === 'bos'">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                        Gudang Anda belum dikonfigurasi. Anda harus menyelesaikan setup singkat di Dashboard terlebih
                        dahulu sebelum dapat menggunakan fitur ini.
                    </p>
                </template>

                <!-- Message for Admin/Checker -->
                <template x-if="['admin', 'checker'].includes(currentUser.role)">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                        Akses gudang ini dibatasi karena konfigurasi awal belum diselesaikan oleh pemilik gudang (Bos).
                        Silakan hubungi pemilik gudang Anda untuk menyelesaikan setup.
                    </p>
                </template>
            </div>

            <div class="flex flex-col gap-3 pt-2 relative">
                <!-- Action for Bos -->
                <template x-if="currentUser.role === 'bos'">
                    <a href="/peace_seafood/dashboard"
                        class="w-full h-11 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-lg shadow-blue-500/20 transition-all hover:scale-[1.01] text-decoration-none"
                        style="text-decoration:none">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        Ke Dashboard &amp; Setup Sekarang
                    </a>
                </template>

                <button @click="logout()"
                    class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/60 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold text-sm flex items-center justify-center gap-2 transition-all">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Keluar Sesi
                </button>
            </div>
        </div>
    </div>

    <!-- 🔒 3. PREMIUM SAAS BILLING SUSPEND & SUBSCRIPTION LOCK SCREEN (402 OVERLAY) -->
    <div class="fixed inset-0 bg-[#020617]/97 backdrop-blur-2xl z-[99999] flex items-center justify-center p-4 select-none"
        x-show="saasLocked" x-cloak>
        <div class="w-full max-w-lg text-center space-y-8 p-6">

            <!-- Lock icon with premium red & gold glow effects -->
            <div class="relative w-24 h-24 mx-auto">
                <div
                    class="absolute inset-0 bg-gradient-to-tr from-red-600 to-amber-500 rounded-full blur-2xl opacity-60 animate-pulse">
                </div>
                <div
                    class="relative w-24 h-24 rounded-full bg-gradient-to-tr from-red-600 to-amber-500 flex items-center justify-center border border-white/20 shadow-xl">
                    <i data-lucide="lock" class="w-11 h-11 text-white animate-[bounce_2s_infinite]"></i>
                </div>
            </div>

            <!-- Headline -->
            <div class="space-y-3">
                <span
                    class="px-4 py-1.5 rounded-full bg-red-500/10 text-red-400 font-extrabold text-[10px] uppercase tracking-widest border border-red-500/20 inline-block">Akses
                    Gudang Dikunci</span>
                <h2 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Masa Sewa / Trial Berakhir
                </h2>
                <p class="text-xs md:text-sm text-slate-400 max-w-md mx-auto leading-relaxed" x-text="saasLockReason">
                </p>
            </div>

            <!-- Benefits Checklist of commercial platform WMS -->
            <div class="max-w-sm mx-auto p-4 rounded-2xl border border-slate-800 bg-slate-900/40 text-left space-y-2.5">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Fitur Pro yang
                    Terkunci:</span>
                <div class="flex items-center gap-2.5 text-xs text-slate-300">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 flex-shrink-0"></i>
                    Notifikasi Limit Stok Minimum &amp; Jatuh Tempo
                </div>
                <div class="flex items-center gap-2.5 text-xs text-slate-300">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 flex-shrink-0"></i>
                    Struk Thermal &amp; Integrasi Kasir Bahari
                </div>
                <div class="flex items-center gap-2.5 text-xs text-slate-300">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 flex-shrink-0"></i>
                    Export Laporan PDF/Excel &amp; Excel Mapper
                </div>
                <div class="flex items-center gap-2.5 text-xs text-slate-300">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 flex-shrink-0"></i>
                    Multi-Gudang &amp; God-Mode Impersonate
                </div>
            </div>

            <!-- Call to Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 max-w-md mx-auto pt-2">
                <a :href="'https://wa.me/' + developerWhatsapp + '?text=Halo%20Developer%20Peace%20Seafood,%20saya%20tertarik%20untuk%20mengaktifkan%20sewa/perpanjang%20masa%20aktif%20WMS%20gudang%20saya.'"
                    target="_blank"
                    class="w-full sm:flex-1 h-12 bg-[#25D366] hover:bg-[#20ba5a] text-white font-bold text-sm rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/10 transition-all hover:scale-[1.02]">
                    <i data-lucide="message-circle" class="w-5 h-5"></i>
                    Hubungi Developer (WA)
                </a>

                <button @click="logout()"
                    class="w-full sm:w-auto px-6 h-12 border border-slate-800 bg-slate-900/60 hover:bg-slate-800 text-slate-300 font-bold text-sm rounded-xl flex items-center justify-center gap-2 transition-all">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Keluar Sesi
                </button>
            </div>
        </div>
    </div>
</body>

</html>