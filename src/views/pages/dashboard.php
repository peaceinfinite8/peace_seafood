<?php

/**
 * Dashboard Page
 */
?>

<style>
    .dashboard-shell {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .dashboard-section {
        padding: 20px;
    }

    .dashboard-section-header {
        margin-bottom: 16px;
    }

    .dashboard-section-title {
        font-size: 16px;
        font-weight: 500;
        color: var(--text-primary);
        margin: 0;
    }

    .dashboard-section-subtitle {
        font-size: 12px;
        color: var(--text-secondary);
        margin: 4px 0 0;
    }

    .dashboard-chart-card {
        background: var(--bg-light);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
    }

    .dashboard-chart-wrap {
        position: relative;
        width: 100%;
    }

    .dashboard-canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .kpi-card {
        background: var(--bg-light);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        padding: 1rem;
    }

    .kpi-card h4 {
        color: var(--text-secondary);
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .kpi-card .value {
        color: var(--text-primary);
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .muted-border {
        border-color: var(--border-color);
    }

    @media (max-width: 768px) {
        .dashboard-section {
            padding: 16px;
        }

        .section-chart-260 {
            height: 200px !important;
        }

        .section-chart-200 {
            height: 180px !important;
        }
    }
</style>

<div x-data="dashboardPage()" x-init="init()" class="dashboard-shell">
    <div class="mb-2">
        <h2 class="text-xl font-bold" style="color: var(--text-primary)">
            Selamat datang, <span x-text="user.name || 'User'"></span> | <span class="uppercase font-semibold text-sm" style="color: var(--color-primary)" x-text="(user.role || '').toUpperCase()"></span>
        </h2>
        <p class="text-sm mt-1" style="color: var(--text-secondary)">
            <span x-text="today"></span>
            <span x-show="user.nama_gudang"> - <span class="font-medium" x-text="user.nama_gudang"></span></span>
        </p>
    </div>

    <div x-show="loading" class="flex items-center justify-center py-20">
        <div class="animate-spin w-8 h-8 rounded-full border-4 border-blue-200" style="border-top-color: var(--color-primary)"></div>
    </div>

    <div x-show="!loading" x-cloak class="space-y-6">
        <section class="dashboard-section card">
            <div class="dashboard-section-header">
                <p class="dashboard-section-title">Ringkasan Utama</p>
                <p class="dashboard-section-subtitle">Delapan KPI inti untuk memantau operasi harian.</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="kpi-card" x-show="['bos','super_admin'].includes(user.role)">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h4>Keuangan Masuk</h4>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(16,185,129,0.1)">
                            <i data-lucide="arrow-down-left" class="w-4 h-4" style="color: var(--color-success)"></i>
                        </div>
                    </div>
                    <div class="value" style="color: var(--color-success)" x-text="formatRupiah(stats.keuangan_masuk)"></div>
                    <p class="text-xs mt-1" style="color: var(--text-secondary)">Total cash + terima piutang</p>
                </div>

                <div class="kpi-card" x-show="['bos','super_admin'].includes(user.role)">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h4>Keuangan Keluar</h4>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(239,68,68,0.1)">
                            <i data-lucide="arrow-up-right" class="w-4 h-4" style="color: var(--color-danger)"></i>
                        </div>
                    </div>
                    <div class="value" style="color: var(--color-danger)" x-text="formatRupiah(stats.keuangan_keluar)"></div>
                    <p class="text-xs mt-1" style="color: var(--text-secondary)">Beli stok + operasional + bayar hutang</p>
                </div>

                <div class="kpi-card" :style="stats.laba_rugi >= 0 ? 'border-color: rgba(16,185,129,0.35)' : 'border-color: rgba(239,68,68,0.35)'">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h4>Laba / Rugi Bersih</h4>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center" :style="stats.laba_rugi >= 0 ? 'background: rgba(16,185,129,0.1)' : 'background: rgba(239,68,68,0.1)'">
                            <i data-lucide="activity" class="w-4 h-4" :style="stats.laba_rugi >= 0 ? 'color: var(--color-success)' : 'color: var(--color-danger)'"></i>
                        </div>
                    </div>
                    <div class="value" :style="stats.laba_rugi >= 0 ? 'color: var(--color-success)' : 'color: var(--color-danger)'" x-text="formatRupiah(stats.laba_rugi)"></div>
                    <p class="text-xs mt-1" style="color: var(--text-secondary)">Selisih bersih inflow - outflow</p>
                </div>

                <div class="kpi-card">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h4>Total Stok</h4>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(37,99,235,0.1)">
                            <i data-lucide="package" class="w-4 h-4" style="color: var(--color-primary)"></i>
                        </div>
                    </div>
                    <div class="value" x-text="formatKg(stats.total_stok_qty)"></div>
                    <p class="text-xs mt-1" style="color: var(--text-secondary)"><span x-text="stats.total_produk"></span> jenis produk</p>
                </div>

                <div class="kpi-card">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h4>Kapasitas Cold Storage</h4>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(6,182,212,0.1)">
                            <i data-lucide="database" class="w-4 h-4" style="color: var(--color-info)"></i>
                        </div>
                    </div>
                    <div class="value" x-text="formatPercent((stats.total_stok_qty / (stats.cold_storage_capacity || 10000)) * 100)"></div>
                    <div class="w-full rounded-full h-2 mt-2" style="background: rgba(148,163,184,0.2)">
                        <div class="h-2 rounded-full transition-all duration-500" :class="((stats.total_stok_qty / (stats.cold_storage_capacity || 10000)) * 100) > 80 ? 'bg-red-500' : 'bg-cyan-500'" :style="'width: ' + Math.min(100, ((stats.total_stok_qty / (stats.cold_storage_capacity || 10000)) * 100)) + '%' "></div>
                    </div>
                    <p class="text-xs mt-1.5" style="color: var(--text-secondary)"><span x-text="formatKg(stats.total_stok_qty)"></span> / <span x-text="formatKg(stats.cold_storage_capacity || 10000)"></span></p>
                </div>

                <div class="kpi-card" x-show="user.role !== 'checker'">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h4>Penjualan Hari Ini</h4>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(16,185,129,0.1)">
                            <i data-lucide="receipt" class="w-4 h-4" style="color: var(--color-success)"></i>
                        </div>
                    </div>
                    <div class="value" x-text="formatRupiah(stats.penjualan_hari_ini)"></div>
                    <p class="text-xs mt-1" style="color: var(--text-secondary)"><span x-text="stats.nota_hari_ini"></span> nota</p>
                </div>

                <div class="kpi-card" x-show="['bos','admin','super_admin'].includes(user.role)">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h4>Total Piutang</h4>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(245,158,11,0.1)">
                            <i data-lucide="trending-up" class="w-4 h-4" style="color: var(--color-warning)"></i>
                        </div>
                    </div>
                    <div class="value" x-text="formatRupiah(stats.total_piutang)"></div>
                    <p class="text-xs mt-1" style="color: var(--color-warning)" x-show="stats.overdue_count > 0"><span x-text="stats.overdue_count"></span> jatuh tempo!</p>
                </div>

                <div class="kpi-card">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h4>Stok Menipis</h4>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(239,68,68,0.1)">
                            <i data-lucide="alert-triangle" class="w-4 h-4" style="color: var(--color-danger)"></i>
                        </div>
                    </div>
                    <div class="value" style="color: var(--color-danger)" x-text="stats.low_stock_count"></div>
                    <p class="text-xs mt-1" style="color: var(--text-secondary)">produk di bawah minimum</p>
                </div>
            </div>
        </section>

        <section class="dashboard-section card">
            <div class="dashboard-section-header">
                <p class="dashboard-section-title">Keuangan</p>
                <p class="dashboard-section-subtitle">Komposisi arus kas dan ringkasan laba rugi.</p>
            </div>

            <div class="flex flex-col lg:flex-row gap-6">
                <div class="dashboard-chart-card p-4 lg:w-3/5 w-full">
                    <div class="dashboard-section-header">
                        <p class="dashboard-section-title">Laba/Rugi Bersih</p>
                        <p class="dashboard-section-subtitle">Waterfall sederhana untuk membaca perubahan kas bersih.</p>
                    </div>
                    <div class="dashboard-chart-wrap section-chart-260" style="height: 260px;">
                        <canvas id="financeWaterfallChart" class="dashboard-canvas"></canvas>
                    </div>
                </div>

                <div class="dashboard-chart-card p-4 lg:w-2/5 w-full">
                    <div class="dashboard-section-header">
                        <p class="dashboard-section-title">Komposisi Kas</p>
                        <p class="dashboard-section-subtitle">Masuk versus keluar pada periode berjalan.</p>
                    </div>
                    <div class="dashboard-chart-wrap section-chart-260" style="height: 260px;">
                        <canvas id="cashCompositionChart" class="dashboard-canvas"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-section card">
            <div class="dashboard-section-header">
                <p class="dashboard-section-title">Penjualan</p>
                <p class="dashboard-section-subtitle">Tren 7 hari terakhir dan produk terlaris berdasarkan volume.</p>
            </div>

            <div class="flex flex-col lg:flex-row gap-6">
                <div class="dashboard-chart-card p-4 lg:w-3/5 w-full">
                    <div class="dashboard-section-header">
                        <p class="dashboard-section-title">Tren Penjualan 7 Hari</p>
                        <p class="dashboard-section-subtitle">Area chart dengan gradient fill.</p>
                    </div>
                    <div class="dashboard-chart-wrap section-chart-260" style="height: 260px;">
                        <canvas id="salesTrendChart" class="dashboard-canvas"></canvas>
                    </div>
                </div>

                <div class="dashboard-chart-card p-4 lg:w-2/5 w-full">
                    <div class="dashboard-section-header">
                        <p class="dashboard-section-title">Top 5 Produk Terlaris</p>
                        <p class="dashboard-section-subtitle">Volume kilogram dari produk yang paling sering terjual.</p>
                    </div>
                    <div class="dashboard-chart-wrap section-chart-260" style="height: 260px;">
                        <canvas id="topProductsChart" class="dashboard-canvas"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-section card">
            <div class="dashboard-section-header">
                <p class="dashboard-section-title">Stok</p>
                <p class="dashboard-section-subtitle">Kapasitas, komposisi kategori, dan riwayat stok masuk 7 hari terakhir.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="dashboard-chart-card p-4">
                    <div class="dashboard-section-header">
                        <p class="dashboard-section-title">Kapasitas Cold Storage</p>
                        <p class="dashboard-section-subtitle">Gauge setengah lingkaran untuk memantau pemakaian ruang.</p>
                    </div>
                    <div class="dashboard-chart-wrap section-chart-200" style="height: 200px;">
                        <canvas id="coldStorageGaugeChart" class="dashboard-canvas"></canvas>
                    </div>
                </div>

                <div class="dashboard-chart-card p-4">
                    <div class="dashboard-section-header">
                        <p class="dashboard-section-title">Stok per Kategori</p>
                        <p class="dashboard-section-subtitle">Komposisi kategori utama stok ikan dan seafood.</p>
                    </div>
                    <div class="dashboard-chart-wrap section-chart-200" style="height: 200px;">
                        <canvas id="stockCategoryChart" class="dashboard-canvas"></canvas>
                    </div>
                </div>

                <div class="dashboard-chart-card p-4">
                    <div class="dashboard-section-header">
                        <p class="dashboard-section-title">Riwayat Stok Masuk 7 Hari</p>
                        <p class="dashboard-section-subtitle">Tren inbound stok dalam satuan kilogram.</p>
                    </div>
                    <div class="dashboard-chart-wrap section-chart-200" style="height: 200px;">
                        <canvas id="incomingStockChart" class="dashboard-canvas"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <div class="flex flex-col gap-6">
            <div class="card" x-show="['bos','admin','super_admin'].includes(user.role)">
                <div class="flex items-center justify-between p-5 border-b muted-border" style="border-color: var(--border-color)">
                    <h3 class="font-semibold" style="color: var(--text-primary)">Nota Terbaru</h3>
                    <a href="/peace_seafood/penjualan" class="text-xs" style="color: var(--color-primary)">Lihat Semua</a>
                </div>
                <div class="p-5">
                    <template x-if="recentNota.length === 0">
                        <p class="text-sm text-center py-4" style="color: var(--text-secondary)">Belum ada nota</p>
                    </template>
                    <template x-for="nota in recentNota" :key="nota.id">
                        <div class="flex items-center justify-between py-2 border-b muted-border" style="border-color: var(--border-color)">
                            <div>
                                <p class="text-sm font-medium" style="color: var(--text-primary)" x-text="nota.no_nota"></p>
                                <p class="text-xs" style="color: var(--text-secondary)" x-text="nota.nama_pembeli || 'Umum'"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold" style="color: var(--color-success)" x-text="formatRupiah(nota.total)"></p>
                                <span class="badge" :class="nota.status === 'final' ? 'badge-success' : nota.status === 'draft' ? 'badge-warning' : 'badge-gray'" x-text="(nota.status || '-').toUpperCase()"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="card" x-show="['bos','super_admin','admin'].includes(user.role)">
                    <div class="flex items-center justify-between p-5 border-b muted-border" style="border-color: var(--border-color)">
                        <h3 class="font-semibold" style="color: var(--text-primary)">Produk Terlaris</h3>
                        <span class="text-xs" style="color: var(--text-secondary)">Top 5 (Qty)</span>
                    </div>
                    <div class="p-5">
                        <template x-if="!stats.top_products || stats.top_products.length === 0">
                            <p class="text-sm text-center py-4" style="color: var(--text-secondary)">Belum ada data penjualan</p>
                        </template>
                        <template x-for="(prod, index) in stats.top_products || []" :key="index">
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium" style="color: var(--text-primary)">
                                        <span class="font-bold mr-1" style="color: var(--color-primary)" x-text="(index + 1) + '.'"></span>
                                        <span x-text="prod.nama"></span>
                                    </span>
                                    <span class="text-xs font-semibold" style="color: var(--text-secondary)" x-text="formatKg(prod.total_qty)"></span>
                                </div>
                                <div class="w-full rounded-full h-1.5" style="background: rgba(148,163,184,0.2)">
                                    <div class="h-1.5 rounded-full" style="background: var(--color-primary)" :style="'width: ' + Math.min(100, Math.max(10, (parseFloat(prod.total_qty) / (parseFloat(stats.top_products[0]?.total_qty) || 1)) * 100)) + '%' "></div>
                                </div>
                                <div class="text-right mt-0.5">
                                    <span class="text-2xs font-semibold" style="color: var(--color-success); font-size: 0.7rem;" x-text="formatRupiah(prod.total_nominal)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="card">
                    <div class="flex items-center justify-between p-5 border-b muted-border" style="border-color: var(--border-color)">
                        <h3 class="font-semibold" style="color: var(--text-primary)">Stok Menipis</h3>
                        <a href="/peace_seafood/stok" class="text-xs" style="color: var(--color-primary)">Lihat Stok</a>
                    </div>
                    <div class="p-5">
                        <template x-if="lowStockItems.length === 0">
                            <p class="text-sm text-center py-4" style="color: var(--color-success)">Semua stok aman</p>
                        </template>
                        <template x-for="item in lowStockItems" :key="item.id">
                            <div class="flex items-center justify-between py-2 border-b muted-border" style="border-color: var(--border-color)">
                                <div>
                                    <p class="text-sm font-medium" style="color: var(--text-primary)" x-text="item.nama"></p>
                                    <p class="text-xs" style="color: var(--text-secondary)" x-text="item.nama_jenis"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold" style="color: var(--color-danger)" x-text="item.stok_qty + ' ' + (item.satuan || 'kg')"></p>
                                    <p class="text-xs" style="color: var(--text-secondary)">min: <span x-text="item.stok_minimum"></span></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="card" x-show="['bos','super_admin','admin'].includes(user.role)">
                    <div class="flex items-center justify-between p-5 border-b muted-border" style="border-color: var(--border-color)">
                        <h3 class="font-semibold flex items-center gap-2" style="color: var(--text-primary)">
                            <i data-lucide="eye" class="w-4 h-4" style="color: var(--color-primary)"></i>
                            Log Aktivitas Terkini
                        </h3>
                        <span class="badge badge-gray">Real-time</span>
                    </div>
                    <div class="overflow-x-auto p-5">
                        <table class="table text-sm">
                            <thead>
                                <tr style="border-bottom: 1.5px solid var(--border-color);">
                                    <th style="padding: 10px 8px; text-align: left; color: var(--text-secondary);">Waktu</th>
                                    <th style="padding: 10px 8px; text-align: left; color: var(--text-secondary);">User</th>
                                    <th style="padding: 10px 8px; text-align: left; color: var(--text-secondary);">Aktivitas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="!stats.latest_logs || stats.latest_logs.length === 0">
                                    <tr>
                                        <td colspan="3" class="text-center py-4" style="color: var(--text-secondary)">Belum ada aktivitas tercatat</td>
                                    </tr>
                                </template>
                                <template x-for="log in stats.latest_logs || []" :key="log.id || log.record_id || log.timestamp">
                                    <tr style="border-bottom: 1px solid var(--border-color);">
                                        <td class="whitespace-nowrap font-mono text-xs" style="color: var(--text-secondary); padding: 8px;" x-text="formatDateTime(log.timestamp)"></td>
                                        <td style="padding: 8px;"><span class="font-semibold" style="color: var(--text-primary)" x-text="log.nama_user"></span></td>
                                        <td style="color: var(--text-secondary); padding: 8px;">
                                            <span class="badge mr-2" :class="log.action === 'INSERT' ? 'badge-success' : (log.action === 'UPDATE' ? 'badge-warning' : 'badge-gray')" x-text="log.action"></span>
                                            <span x-text="formatActivity(log)"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $scripts = <<<'JS'
<script>
window.dashboardStats = window.dashboardStats || {
    total_produk: 0,
    total_stok_qty: 0,
    total_stok_value: 0,
    penjualan_hari_ini: 0,
    nota_hari_ini: 0,
    total_piutang: 0,
    total_hutang: 0,
    overdue_count: 0,
    low_stock_count: 0,
    pending_timbang: 0,
    sales_chart: [],
    stok_chart: { labels: [], values: [] },
    top_products: [],
    latest_logs: [],
    keuangan_masuk: 0,
    keuangan_keluar: 0,
    laba_rugi: 0,
};
window.stats = window.dashboardStats;

function dashboardPage() {
    const activeCharts = {};
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        today: new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
        loading: true,
        stats: {
            total_produk: 0,
            total_stok_qty: 0,
            total_stok_value: 0,
            penjualan_hari_ini: 0,
            nota_hari_ini: 0,
            total_piutang: 0,
            total_hutang: 0,
            overdue_count: 0,
            low_stock_count: 0,
            pending_timbang: 0,
            sales_chart: [],
            stok_chart: { labels: [], values: [] },
            top_products: [],
            latest_logs: [],
            keuangan_masuk: 0,
            keuangan_keluar: 0,
            laba_rugi: 0,
        },
        recentNota: [],
        lowStockItems: [],
        observer: null,

        async init() {
            if (!localStorage.getItem('token')) {
                window.location.href = '/peace_seafood/login';
                return;
            }

            await this.loadDashboard();
            this.loading = false;

            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
                this.setupThemeObserver();
                this.renderChartsDeferred();
            });
        },

        async loadDashboard() {
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };

                const [dashRes, notaRes, stokRes] = await Promise.allSettled([
                    axios.get('/peace_seafood/api/dashboard', { headers }),
                    axios.get('/peace_seafood/api/penjualan?per_page=5', { headers }),
                    axios.get('/peace_seafood/api/stok', { headers }),
                ]);

                if (dashRes.status === 'fulfilled') {
                    const data = dashRes.value.data?.data || {};
                    this.stats = { ...this.stats, ...data };
                    window.dashboardStats = this.stats;
                    window.stats = this.stats;
                }

                if (notaRes.status === 'fulfilled') {
                    this.recentNota = (notaRes.value.data?.data || []).slice(0, 5);
                }

                if (stokRes.status === 'fulfilled') {
                    const items = stokRes.value.data?.data || [];
                    this.lowStockItems = items.filter((item) => item.is_low_stock == 1).slice(0, 5);
                }
            } catch (error) {
                if (error.response?.status === 401) {
                    localStorage.clear();
                    window.location.href = '/peace_seafood/login';
                }
            }
        },

        renderChartsDeferred() {
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    this.destroyCharts();
                    this.renderCharts();
                });
            });
        },

        getThemeColors() {
            const isDark = document.documentElement.classList.contains('dark') || document.documentElement.getAttribute('data-theme') === 'dark';
            return {
                isDark,
                textColor: isDark ? '#e5e7eb' : '#374151',
                secondaryTextColor: isDark ? '#cbd5e1' : '#6b7280',
                gridColor: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)',
                borderColor: isDark ? 'rgba(255,255,255,0.14)' : 'rgba(0,0,0,0.08)',
            };
        },

        themeOptions() {
            const theme = this.getThemeColors();
            return {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 500 },
                plugins: {
                    legend: {
                        labels: {
                            color: theme.textColor,
                            usePointStyle: true,
                            boxWidth: 10,
                            boxHeight: 10,
                        }
                    },
                    tooltip: {
                        backgroundColor: theme.isDark ? 'rgba(15,23,42,0.96)' : 'rgba(255,255,255,0.98)',
                        titleColor: theme.textColor,
                        bodyColor: theme.textColor,
                        borderColor: theme.borderColor,
                        borderWidth: 1,
                    }
                }
            };
        },

        formatMoney(value) {
            return (parseFloat(value) || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
        },

        formatRupiah(value) {
            return 'Rp ' + this.formatMoney(value);
        },

        formatKg(value) {
            return (Math.round(parseFloat(value) || 0)).toLocaleString('id-ID') + ' kg';
        },

        formatPercent(value) {
            return (parseFloat(value) || 0).toFixed(1) + '%';
        },

        formatDateTime(value) {
            if (!value) return '-';
            const cleanValue = value.includes(' ') ? value.replace(' ', 'T') : value;
            const date = new Date(cleanValue);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) + ' ' + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
        },

        formatActivity(log) {
            const action = (log.action || '').toUpperCase();
            const table = (log.table_name || '').toLowerCase();
            const id = log.record_id;

            if (action === 'INSERT') {
                if (table === 'nota') return `membuat Nota Penjualan #${id}`;
                if (table === 'stok_masuk') return `menginput Stok Masuk #${id}`;
                if (table === 'timbangan') return `mencatat Timbangan hasil susut #${id}`;
                if (table === 'stok_opname') return `memulai sesi Stok Opname #${id}`;
                if (table === 'stok_transfer') return `mengajukan Transfer Stok #${id}`;
                if (table === 'biaya_operasional') return `mencatat Biaya Operasional #${id}`;
                return `menambahkan data baru pada tabel ${table} #${id}`;
            }

            if (action === 'UPDATE') {
                if (table === 'nota') return `mengubah/memproses Nota Penjualan #${id}`;
                if (table === 'stok_masuk') return `mengonfirmasi Timbangan masuk #${id}`;
                if (table === 'stok_opname') return `menyelesaikan Stok Opname & sinkronisasi #${id}`;
                if (table === 'stok_transfer') return `memperbarui status Transfer Stok #${id}`;
                return `memperbarui data ${table} #${id}`;
            }

            if (action === 'DELETE') {
                return `menghapus data ${table} #${id}`;
            }

            return `melakukan aksi ${action} pada ${table} #${id}`;
        },

        destroyCharts() {
            Object.values(activeCharts).forEach((chart) => chart && chart.destroy && chart.destroy());
            for (let key in activeCharts) {
                delete activeCharts[key];
            }
        },

        renderCharts() {
            if (!window.Chart) return;

            const theme = this.getThemeColors();

            // compute finance values from stats
            const income = Number(this.stats.keuangan_masuk || 0);
            const expense = Number(this.stats.keuangan_keluar || 0);
            const profit = Number(this.stats.laba_rugi ?? (income - expense));

            const waterfallCanvas = document.getElementById('financeWaterfallChart');
            if (waterfallCanvas && typeof waterfallCanvas.getContext === 'function') {
                // Ensure mathematical correctness [min, max] where min <= max for floating bar chart in Chart.js
                const waterfallData = [
                    [0, income],
                    [income - expense, income],
                    profit >= 0 ? [0, profit] : [profit, 0]
                ];
                activeCharts.financeWaterfall = SafeChart(waterfallCanvas, {
                    type: 'bar',
                    data: {
                        labels: ['Pemasukan', 'Pengeluaran', 'Hasil'],
                        datasets: [{
                            label: 'Nilai',
                            data: waterfallData,
                            backgroundColor: ['rgba(16,185,129,0.85)', 'rgba(239,68,68,0.85)', profit >= 0 ? 'rgba(16,185,129,0.65)' : 'rgba(239,68,68,0.65)'],
                            borderColor: ['#10b981', '#ef4444', profit >= 0 ? '#10b981' : '#ef4444'],
                            borderWidth: 1,
                            borderRadius: 8,
                            barPercentage: 0.7,
                            categoryPercentage: 0.72,
                        }]
                    },
                    options: {
                        ...this.themeOptions(),
                        scales: {
                            x: { 
                                ticks: { color: theme.textColor }, 
                                grid: { display: false },
                                border: { display: false }
                            },
                            y: {
                                ticks: {
                                    color: theme.secondaryTextColor,
                                    callback: (value) => 'Rp ' + (Number(value) / 1000000).toFixed(0) + 'jt',
                                },
                                grid: { color: theme.gridColor },
                                border: { display: false }
                            }
                        },
                        plugins: {
                            ...this.themeOptions().plugins,
                            legend: { display: false },
                            tooltip: {
                                ...this.themeOptions().plugins.tooltip,
                                callbacks: {
                                    label(context) {
                                        const value = context.raw;
                                        const endValue = Array.isArray(value) ? value[1] : value;
                                        return 'Rp ' + Math.abs(endValue).toLocaleString('id-ID');
                                    }
                                }
                            },
                        }
                    }
                });
            }

            const cashCanvas = document.getElementById('cashCompositionChart');
            if (cashCanvas && typeof cashCanvas.getContext === 'function') {
                activeCharts.cashComposition = SafeChart(cashCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Uang Masuk', 'Uang Keluar'],
                        datasets: [{
                            data: [income, expense],
                            backgroundColor: ['rgba(16,185,129,0.9)', 'rgba(239,68,68,0.9)'],
                            borderColor: theme.isDark ? '#0f172a' : '#ffffff',
                            borderWidth: 2,
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        ...this.themeOptions(),
                        cutout: '65%',
                        plugins: {
                            ...this.themeOptions().plugins,
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: theme.textColor,
                                    usePointStyle: true,
                                    boxWidth: 10,
                                }
                            },
                        }
                    }
                });
            }

            const salesCanvas = document.getElementById('salesTrendChart');
            if (salesCanvas && typeof salesCanvas.getContext === 'function') {
                const ctx = salesCanvas.getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 260);
                gradient.addColorStop(0, 'rgba(37,99,235,0.35)');
                gradient.addColorStop(1, 'rgba(37,99,235,0.02)');

                const labels = (this.stats.sales_chart_labels && this.stats.sales_chart_labels.length) ? this.stats.sales_chart_labels : ['H-6','H-5','H-4','H-3','H-2','H-1','Hari ini'];
                const dataPoints = (this.stats.sales_chart && this.stats.sales_chart.length) ? this.stats.sales_chart : [0,0,0,0,0,0,0];

                activeCharts.salesTrend = SafeChart(salesCanvas, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Penjualan',
                            data: dataPoints,
                            fill: true,
                            tension: 0.4,
                            borderColor: '#2563eb',
                            backgroundColor: gradient,
                            pointBackgroundColor: '#2563eb',
                            pointBorderColor: '#2563eb',
                            pointRadius: 3,
                            pointHoverRadius: 5,
                        }]
                    },
                    options: {
                        ...this.themeOptions(),
                        plugins: {
                            ...this.themeOptions().plugins,
                            legend: { display: false },
                            tooltip: {
                                ...this.themeOptions().plugins.tooltip,
                                callbacks: {
                                    label(context) {
                                        return 'Rp ' + Number(context.parsed.y || 0).toLocaleString('id-ID');
                                    }
                                }
                            },
                        },
                        scales: {
                            x: { 
                                ticks: { color: theme.textColor }, 
                                grid: { display: false },
                                border: { display: false }
                            },
                            y: {
                                ticks: {
                                    color: theme.secondaryTextColor,
                                    callback: (value) => 'Rp ' + (Number(value) / 1000000).toFixed(0) + 'jt',
                                },
                                grid: { color: theme.gridColor },
                                border: { display: false }
                            }
                        }
                    }
                });
            }

            const topProductsCanvas = document.getElementById('topProductsChart');
            if (topProductsCanvas && typeof topProductsCanvas.getContext === 'function') {
                const topLabels = (this.stats.top_products || []).map(p => p.nama || 'N/A');
                const topData = (this.stats.top_products || []).map(p => Number(p.total_qty || p.total || 0));
                const colors = ['rgba(37,99,235,0.9)', 'rgba(6,182,212,0.9)', 'rgba(16,185,129,0.9)', 'rgba(245,158,11,0.9)', 'rgba(139,92,246,0.9)'];

                activeCharts.topProducts = SafeChart(topProductsCanvas, {
                    type: 'bar',
                    data: {
                        labels: topLabels.length ? topLabels : ['-','-','-','-','-'],
                        datasets: [{
                            label: 'Qty (kg)',
                            data: topData.length ? topData : [0,0,0,0,0],
                            backgroundColor: colors.slice(0, Math.max(5, topLabels.length)),
                            borderRadius: 8,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        ...this.themeOptions(),
                        indexAxis: 'y',
                        plugins: {
                            ...this.themeOptions().plugins,
                            legend: { display: false },
                        },
                        scales: {
                            x: { 
                                ticks: { color: theme.secondaryTextColor }, 
                                grid: { color: theme.gridColor },
                                border: { display: false }
                            },
                            y: { 
                                ticks: { color: theme.textColor }, 
                                grid: { display: false },
                                border: { display: false }
                            }
                        }
                    }
                });
            }

            const gaugeCanvas = document.getElementById('coldStorageGaugeChart');
            if (gaugeCanvas && typeof gaugeCanvas.getContext === 'function') {
                const used = Number(this.stats.total_stok_qty || 0);
                const capacity = Number(this.stats.cold_storage_capacity || 10000);
                const remaining = Math.max(0, capacity - used);
                const percent = capacity > 0 ? ((used / capacity) * 100) : 0;

                const gaugePlugin = {
                    id: 'centerTextGauge',
                    afterDraw: (chart) => {
                        const { ctx, chartArea } = chart;
                        if (!chartArea) return;
                        const valueText = percent.toFixed(1) + '%';
                        ctx.save();
                        ctx.font = '700 24px Inter, sans-serif';
                        ctx.fillStyle = theme.textColor;
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(valueText, (chartArea.left + chartArea.right) / 2, chartArea.bottom - 16);
                        ctx.font = '500 11px Inter, sans-serif';
                        ctx.fillStyle = theme.secondaryTextColor;
                        ctx.fillText('terpakai', (chartArea.left + chartArea.right) / 2, chartArea.bottom + 2);
                        ctx.restore();
                    }
                };

                activeCharts.coldStorageGauge = SafeChart(gaugeCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Terpakai', 'Tersisa'],
                        datasets: [{
                            data: [used, remaining],
                            backgroundColor: ['rgba(6,182,212,0.95)', 'rgba(148,163,184,0.35)'],
                            borderColor: theme.isDark ? '#0f172a' : '#ffffff',
                            borderWidth: 2,
                            hoverOffset: 2,
                        }]
                    },
                    options: {
                        ...this.themeOptions(),
                        cutout: '72%',
                        rotation: -90,
                        circumference: 180,
                        plugins: {
                            ...this.themeOptions().plugins,
                            legend: { display: false },
                            tooltip: {
                                ...this.themeOptions().plugins.tooltip,
                                callbacks: {
                                    label(context) {
                                        return context.label + ': ' + Number(context.parsed).toLocaleString('id-ID') + ' kg';
                                    }
                                }
                            },
                        }
                    },
                    plugins: [gaugePlugin]
                });
            }

            const stockCategoryCanvas = document.getElementById('stockCategoryChart');
            if (stockCategoryCanvas && typeof stockCategoryCanvas.getContext === 'function') {
                const labels = (this.stats.stok_chart && this.stats.stok_chart.labels) ? this.stats.stok_chart.labels : ['-'];
                const values = (this.stats.stok_chart && this.stats.stok_chart.values) ? this.stats.stok_chart.values : [0];

                activeCharts.stockCategory = SafeChart(stockCategoryCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: ['rgba(37,99,235,0.9)', 'rgba(16,185,129,0.9)', 'rgba(245,158,11,0.9)', 'rgba(6,182,212,0.9)'],
                            borderColor: theme.isDark ? '#0f172a' : '#ffffff',
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        ...this.themeOptions(),
                        cutout: '60%',
                        plugins: {
                            ...this.themeOptions().plugins,
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: theme.textColor,
                                    usePointStyle: true,
                                }
                            }
                        }
                    }
                });
            }

            const incomingStockCanvas = document.getElementById('incomingStockChart');
            if (incomingStockCanvas && typeof incomingStockCanvas.getContext === 'function') {
                const labels = (this.stats.sales_chart_labels && this.stats.sales_chart_labels.length) ? this.stats.sales_chart_labels : ['H-6','H-5','H-4','H-3','H-2','H-1','Hari ini'];
                const dataPoints = (this.stats.incoming_stock && this.stats.incoming_stock.length) ? this.stats.incoming_stock : [0,0,0,0,0,0,0];

                activeCharts.incomingStock = SafeChart(incomingStockCanvas, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Stok Masuk (kg)',
                            data: dataPoints,
                            fill: true,
                            tension: 0.35,
                            borderColor: '#14b8a6',
                            backgroundColor: (ctx) => {
                                const chart = ctx.chart;
                                const area = chart.chartArea;
                                if (!area) return 'rgba(20,184,166,0.15)';
                                const gradient = chart.ctx.createLinearGradient(0, area.top, 0, area.bottom);
                                gradient.addColorStop(0, 'rgba(20,184,166,0.35)');
                                gradient.addColorStop(1, 'rgba(20,184,166,0.04)');
                                return gradient;
                            },
                            pointBackgroundColor: '#14b8a6',
                            pointBorderColor: '#14b8a6',
                            pointRadius: 3,
                        }]
                    },
                    options: {
                        ...this.themeOptions(),
                        plugins: {
                            ...this.themeOptions().plugins,
                            legend: { display: false },
                        },
                        scales: {
                            x: { 
                                ticks: { color: theme.textColor }, 
                                grid: { display: false },
                                border: { display: false }
                            },
                            y: { 
                                ticks: { color: theme.secondaryTextColor }, 
                                grid: { color: theme.gridColor },
                                border: { display: false }
                            }
                        }
                    }
                });
            }
        },

        updateChartTheme() {
            const theme = this.getThemeColors();
            Object.values(activeCharts).forEach((chart) => {
                if (!chart) return;

                if (chart.options?.scales?.x?.ticks) chart.options.scales.x.ticks.color = theme.textColor;
                if (chart.options?.scales?.x?.grid) chart.options.scales.x.grid.color = theme.gridColor;
                if (chart.options?.scales?.y?.ticks) chart.options.scales.y.ticks.color = theme.secondaryTextColor;
                if (chart.options?.scales?.y?.grid) chart.options.scales.y.grid.color = theme.gridColor;

                if (chart.options?.plugins?.legend?.labels) {
                    chart.options.plugins.legend.labels.color = theme.textColor;
                }

                if (chart.options?.plugins?.tooltip) {
                    chart.options.plugins.tooltip.backgroundColor = theme.isDark ? 'rgba(15,23,42,0.96)' : 'rgba(255,255,255,0.98)';
                    chart.options.plugins.tooltip.titleColor = theme.textColor;
                    chart.options.plugins.tooltip.bodyColor = theme.textColor;
                    chart.options.plugins.tooltip.borderColor = theme.borderColor;
                }

                chart.update('none');
            });
        },

        setupThemeObserver() {
            if (this.observer) return;

            this.observer = new MutationObserver(() => {
                this.updateChartTheme();
            });

            this.observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class', 'data-theme'],
            });
        },
    };
}
</script>
JS;
?>