<?php
/**
 * Dashboard Page
 * Shows different content based on user role
 */
?>
<style>
    .dashboard-page {
        position: relative;
    }

    .dashboard-page .card,
    .dashboard-page .stat-card {
        overflow: hidden;
        transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease, background 160ms ease;
    }

    .dashboard-page .card:hover,
    .dashboard-page .stat-card:hover {
        transform: translateY(-1px);
    }

    [data-theme="light"] .dashboard-page {
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --border-color: #d7e0ea;
    }

    [data-theme="light"] .dashboard-page .card {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        border-color: rgba(148, 163, 184, 0.22);
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
    }

    [data-theme="light"] .dashboard-page .stat-card {
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid rgba(37, 99, 235, 0.12);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.04);
    }

    [data-theme="light"] .dashboard-page .stat-card--blue {
        background: linear-gradient(180deg, rgba(37, 99, 235, 0.08) 0%, #ffffff 70%);
        border-color: rgba(37, 99, 235, 0.16);
    }

    [data-theme="light"] .dashboard-page .stat-card--green {
        background: linear-gradient(180deg, rgba(16, 185, 129, 0.08) 0%, #ffffff 70%);
        border-color: rgba(16, 185, 129, 0.16);
    }

    [data-theme="light"] .dashboard-page .stat-card--amber {
        background: linear-gradient(180deg, rgba(245, 158, 11, 0.08) 0%, #ffffff 70%);
        border-color: rgba(245, 158, 11, 0.18);
    }

    [data-theme="light"] .dashboard-page .stat-card--red {
        background: linear-gradient(180deg, rgba(239, 68, 68, 0.08) 0%, #ffffff 70%);
        border-color: rgba(239, 68, 68, 0.16);
    }
</style>
<div class="dashboard-page" x-data="dashboardPage()" x-init="init()">

    <!-- Role-based greeting + widgets -->
    <div class="mb-6">
        <h2 class="text-xl font-bold" style="color: var(--text-primary)">
            Selamat datang, <span x-text="user.name"></span> 👋
        </h2>
        <p class="text-sm mt-1" style="color: var(--text-secondary)">
            <span x-text="today"></span> |
            <span class="font-medium" x-text="user.role?.toUpperCase()"></span>
            <span x-show="user.nama_gudang"> — <span x-text="user.nama_gudang"></span></span>
        </p>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="flex items-center justify-center py-20">
        <div class="animate-spin w-8 h-8 rounded-full border-4 border-blue-200"
            style="border-top-color: var(--color-primary)"></div>
    </div>

    <div x-show="!loading" x-cloak>

        <!-- STAT CARDS -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

            <!-- Stok Value -->
            <div class="stat-card stat-card--blue">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-secondary)">Total
                        Stok</p>
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                        style="background: rgba(37,99,235,0.1)">
                        <i data-lucide="package" class="w-4 h-4" style="color: var(--color-primary)"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold" style="color: var(--text-primary)"
                    x-text="formatRupiah(stats.total_stok_value)"></p>
                <p class="text-xs mt-1" style="color: var(--text-secondary)"><span x-text="stats.total_produk"></span>
                    jenis produk</p>
            </div>

            <!-- Penjualan Hari Ini -->
            <div class="stat-card stat-card--green" x-show="user.role !== 'checker'">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-secondary)">
                        Penjualan Hari Ini</p>
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                        style="background: rgba(16,185,129,0.1)">
                        <i data-lucide="receipt" class="w-4 h-4" style="color: var(--color-success)"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold" style="color: var(--text-primary)"
                    x-text="formatRupiah(stats.penjualan_hari_ini)"></p>
                <p class="text-xs mt-1" style="color: var(--text-secondary)"><span x-text="stats.nota_hari_ini"></span>
                    nota</p>
            </div>

            <!-- Total Piutang -->
            <div class="stat-card stat-card--amber" x-show="['bos','admin'].includes(user.role)">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-secondary)">Total
                        Piutang</p>
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                        style="background: rgba(245,158,11,0.1)">
                        <i data-lucide="trending-up" class="w-4 h-4" style="color: var(--color-warning)"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold" style="color: var(--text-primary)"
                    x-text="formatRupiah(stats.total_piutang)"></p>
                <p class="text-xs mt-1" style="color: var(--color-warning)" x-show="stats.overdue_count > 0">
                    <span x-text="stats.overdue_count"></span> jatuh tempo!
                </p>
            </div>

            <!-- Stok Low Alert -->
            <div class="stat-card stat-card--red">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-secondary)">Stok
                        Menipis</p>
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                        style="background: rgba(239,68,68,0.1)">
                        <i data-lucide="alert-triangle" class="w-4 h-4" style="color: var(--color-danger)"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold" style="color: var(--color-danger)" x-text="stats.low_stock_count"></p>
                <p class="text-xs mt-1" style="color: var(--text-secondary)">produk di bawah minimum</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card p-5 mb-6">
            <h3 class="font-semibold mb-4" style="color: var(--text-primary)">Aksi Cepat</h3>
            <div class="flex flex-wrap gap-3">
                <a href="/peace_seafood/stok/masuk" class="btn btn-primary"
                    x-show="['admin','bos'].includes(user.role)">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Input Stok
                </a>
                <a href="/peace_seafood/stok/timbangan" class="btn btn-secondary"
                    x-show="['admin','checker'].includes(user.role)">
                    <i data-lucide="scale" class="w-4 h-4"></i>
                    Timbangan
                    <span class="badge badge-warning ml-1" x-show="stats.pending_timbang > 0"
                        x-text="stats.pending_timbang"></span>
                </a>
                <a href="/peace_seafood/penjualan/create" class="btn btn-success"
                    x-show="['admin','bos'].includes(user.role)">
                    <i data-lucide="file-plus" class="w-4 h-4"></i>
                    Buat Nota
                </a>
                <a href="/peace_seafood/keuangan" class="btn btn-warning" x-show="['bos','admin'].includes(user.role)">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                    Keuangan
                </a>
                <a href="/peace_seafood/laporan" class="btn btn-secondary">
                    <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                    Laporan
                </a>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6" x-show="user.role !== 'checker'">

            <!-- Sales Chart -->
            <div class="card p-5">
                <h3 class="font-semibold mb-4" style="color: var(--text-primary)">Penjualan 7 Hari Terakhir</h3>
                <canvas id="salesChart" height="200"></canvas>
            </div>

            <!-- Stok by Jenis -->
            <div class="card p-5">
                <h3 class="font-semibold mb-4" style="color: var(--text-primary)">Stok per Jenis Ikan</h3>
                <canvas id="stokChart" height="200"></canvas>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Recent Nota -->
            <div class="card" x-show="['bos','admin'].includes(user.role)">
                <div class="flex items-center justify-between p-5 border-b" style="border-color: var(--border-color)">
                    <h3 class="font-semibold" style="color: var(--text-primary)">Nota Terbaru</h3>
                    <a href="/peace_seafood/penjualan" class="text-xs" style="color: var(--color-primary)">Lihat
                        Semua</a>
                </div>
                <div class="p-5">
                    <template x-if="recentNota.length === 0">
                        <p class="text-sm text-center py-4" style="color: var(--text-secondary)">Belum ada nota</p>
                    </template>
                    <template x-for="nota in recentNota" :key="nota.id">
                        <div class="flex items-center justify-between py-2 border-b"
                            style="border-color: var(--border-color)">
                            <div>
                                <p class="text-sm font-medium" style="color: var(--text-primary)" x-text="nota.no_nota">
                                </p>
                                <p class="text-xs" style="color: var(--text-secondary)"
                                    x-text="nota.nama_pembeli || 'Umum'"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold" style="color: var(--color-success)"
                                    x-text="formatRupiah(nota.total)"></p>
                                <span class="badge"
                                    :class="nota.status === 'final' ? 'badge-success' : nota.status === 'draft' ? 'badge-warning' : 'badge-gray'"
                                    x-text="nota.status.toUpperCase()"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="card">
                <div class="flex items-center justify-between p-5 border-b" style="border-color: var(--border-color)">
                    <h3 class="font-semibold" style="color: var(--text-primary)">⚠️ Stok Menipis</h3>
                    <a href="/peace_seafood/stok" class="text-xs" style="color: var(--color-primary)">Lihat Stok</a>
                </div>
                <div class="p-5">
                    <template x-if="lowStockItems.length === 0">
                        <p class="text-sm text-center py-4" style="color: var(--color-success)">✓ Semua stok aman</p>
                    </template>
                    <template x-for="item in lowStockItems" :key="item.id">
                        <div class="flex items-center justify-between py-2 border-b"
                            style="border-color: var(--border-color)">
                            <div>
                                <p class="text-sm font-medium" style="color: var(--text-primary)" x-text="item.nama">
                                </p>
                                <p class="text-xs" style="color: var(--text-secondary)" x-text="item.nama_jenis"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-red-500"
                                    x-text="item.stok_qty + ' ' + (item.satuan || 'kg')"></p>
                                <p class="text-xs" style="color: var(--text-secondary)">min: <span
                                        x-text="item.stok_minimum"></span></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $scripts = <<<'JS'
<script>
function dashboardPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        today: new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
        loading: true,
        stats: {
            total_stok_value: 0, total_produk: 0,
            penjualan_hari_ini: 0, nota_hari_ini: 0,
            total_piutang: 0, overdue_count: 0,
            low_stock_count: 0, pending_timbang: 0,
        },
        recentNota: [],
        lowStockItems: [],

        async init() {
            if (!localStorage.getItem('token')) {
                window.location.href = '/peace_seafood/login';
                return;
            }
            await this.loadDashboard();
            this.loading = false;
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
                this.renderCharts();
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
                    const d = dashRes.value.data?.data || {};
                    this.stats = { ...this.stats, ...d };
                }
                if (notaRes.status === 'fulfilled') {
                    this.recentNota = (notaRes.value.data?.data || []).slice(0, 5);
                }
                if (stokRes.status === 'fulfilled') {
                    const items = stokRes.value.data?.data || [];
                    this.lowStockItems = items.filter(i => i.is_low_stock == 1).slice(0, 5);
                    this.stats.low_stock_count = this.lowStockItems.length;
                    this.stats.total_produk = items.length;
                    this.stats.total_stok_value = items.reduce((s, i) => s + parseFloat(i.stok_value || 0), 0);
                }
            } catch(e) {
                if (e.response?.status === 401) {
                    localStorage.clear();
                    window.location.href = '/peace_seafood/login';
                }
            }
        },

        renderCharts() {
            // Sales Chart
            const salesCtx = document.getElementById('salesChart');
            if (salesCtx) {
                const labels = [];
                for (let i = 6; i >= 0; i--) {
                    const d = new Date();
                    d.setDate(d.getDate() - i);
                    labels.push(d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }));
                }
                new Chart(salesCtx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Penjualan',
                            data: this.stats.sales_chart || Array(7).fill(0),
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37,99,235,0.1)',
                            fill: true,
                            tension: 0.4,
                        }]
                    },
                    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });
            }

            // Stok Chart
            const stokCtx = document.getElementById('stokChart');
            if (stokCtx && this.stats.stok_chart) {
                new Chart(stokCtx, {
                    type: 'doughnut',
                    data: {
                        labels: this.stats.stok_chart?.labels || [],
                        datasets: [{
                            data: this.stats.stok_chart?.values || [],
                            backgroundColor: ['#2563eb','#10b981','#f59e0b','#ef4444','#06b6d4','#8b5cf6'],
                        }]
                    },
                    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
                });
            }
        },

        formatRupiah(n) {
            return 'Rp ' + (parseFloat(n) || 0).toLocaleString('id-ID', { minimumFractionDigits: 0 });
        }
    };
}
</script>
JS;
?>