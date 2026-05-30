/* extracted from pages_dashboard.scripts.1.js */
// extracted from src/views/pages/dashboard.php
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
    draft_pending_count: 0,
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
        user: (() => {
            let u = {};
            try {
                u = JSON.parse(localStorage.getItem('user') || '{}') || {};
            } catch (e) {
                u = {};
            }
            if (u && u.role) u.role = u.role.toLowerCase();
            return u;
        })(),
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
            draft_pending_count: 0,
            sales_chart: [],
            stok_chart: { labels: [], values: [] },
            top_products: [],
            latest_logs: [],
            keuangan_masuk: 0,
            keuangan_keluar: 0,
            laba_rugi: 0,
            // SaaS Metrics
            total_gudang: 0,
            active_gudang: 0,
            expired_gudang: 0,
            pending_onboarding: 0,
            total_sales_all: 0,
            total_sales_count: 0,
        },
        onboardingCompleted: null, // null=loading, true=done, false=belum
        recentNota: [],
        lowStockItems: [],
        observer: null,

        // SaaS States & Forms
        gudang: [],
        preApproveForm: {
            name: '',
            email: '',
            trial_days: '14'
        },
        devWhatsappNumber: '628123456789',
        preApproving: false,
        savingWa: false,

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

                if (this.user.role === 'saas_owner') {
                    const res = await axios.get('/peace_seafood/api/dashboard', { headers });
                    if (res.data?.success) {
                        const data = res.data.data || {};
                        this.stats = { ...this.stats, ...data };
                        this.gudang = data.tenants || [];
                        this.devWhatsappNumber = data.developer_whatsapp || '628123456789';
                        window.dashboardStats = this.stats;
                        window.stats = this.stats;
                    }
                    return;
                }

                // Standard WMS load for other roles
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
                // Cek status onboarding untuk role bos
                if (this.user.role === 'bos' || this.user.role === 'admin' || this.user.role === 'checker') {
                    try {
                        const settingsRes = await axios.get('/peace_seafood/api/settings', { headers });
                        const settings = settingsRes.data.data || [];
                        const ob = settings.find(s => s.kunci === 'onboarding_completed');
                        this.onboardingCompleted = (ob && ob.nilai === '1');
                    } catch (e) {
                        this.onboardingCompleted = true; // Gagal fetch = asumsikan done, jangan block
                    }
                } else {
                    this.onboardingCompleted = true; // super_admin selalu bisa lihat
                }

            } catch (error) {
                if (error.response?.status === 401) {
                    localStorage.clear();
                    window.location.href = '/peace_seafood/login';
                }
            }
        },

        // SaaS Owner Methods
        getBosEmail(idBos) {
            const g = this.gudang.find(x => x.id_bos == idBos);
            return g ? g.email_bos || g.nama_bos || '-' : '-';
        },

        getRemainingDaysText(expiryStr) {
            if (!expiryStr) return 'Belum onboarding / Masa trial tertunda';
            const expiry = new Date(expiryStr.substring(0, 10) + 'T00:00:00');
            const today = new Date();
            today.setHours(0,0,0,0);
            const diffTime = expiry - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            if (diffDays < 0) {
                return `Kedaluwarsa ${Math.abs(diffDays)} hari lalu`;
            } else if (diffDays === 0) {
                return 'Hari ini hari terakhir!';
            } else {
                return `${diffDays} hari tersisa`;
            }
        },

        async updateGudangSubscription(g, newDate) {
            try {
                const token = localStorage.getItem('token');
                await axios.put(`/peace_seafood/api/settings/gudang/${g.id}`, {
                    subscription_until: newDate
                }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                g.subscription_until = newDate;
                iziToast.success({
                    title: 'Sukses',
                    message: 'Masa aktif sewa tenant berhasil diperbarui!',
                    position: 'topRight'
                });
            } catch (e) {
                console.error(e);
                iziToast.error({
                    title: 'Gagal',
                    message: e.response?.data?.message || 'Gagal mengubah masa aktif sewa.',
                    position: 'topRight'
                });
            }
        },

        async updateGudangStatus(g, newStatus) {
            try {
                const token = localStorage.getItem('token');
                await axios.put(`/peace_seafood/api/settings/gudang/${g.id}`, {
                    status_langganan: newStatus
                }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                g.status_langganan = newStatus;
                iziToast.success({
                    title: 'Sukses',
                    message: 'Status langganan tenant berhasil diubah!',
                    position: 'topRight'
                });
            } catch (e) {
                console.error(e);
                iziToast.error({
                    title: 'Gagal',
                    message: e.response?.data?.message || 'Gagal mengubah status langganan.',
                    position: 'topRight'
                });
            }
        },

        async impersonateUser(idBos) {
            if (!idBos) return;
            try {
                Swal.fire({
                    title: 'Masuk Sebagai Tenant...',
                    html: 'Sedang mengalihkan sesi Anda ke akun Bos Tenant.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                const token = localStorage.getItem('token');
                const res = await axios.post('/peace_seafood/api/auth/impersonate', { user_id: idBos }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                if (res.data?.success) {
                    // Save new session
                    localStorage.setItem('token', res.data.data.token);
                    localStorage.setItem('user', JSON.stringify(res.data.data.user));
                    
                    // Redirect to reload WMS dashboard
                    window.location.reload();
                }
            } catch (e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Impersonate Gagal',
                    text: e.response?.data?.message || 'Terjadi kesalahan sistem'
                });
            }
        },

        async runPreApprove() {
            this.preApproving = true;
            try {
                const token = localStorage.getItem('token');
                const res = await axios.post('/peace_seafood/api/settings/pre-approve', this.preApproveForm, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                
                if (res.data?.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Disetujui!',
                        text: `Akun Bos "${this.preApproveForm.name}" telah disetujui. Silakan infokan ke ybs untuk mendaftar (Sign Up) menggunakan email "${this.preApproveForm.email}".`,
                        customClass: { popup: 'swal2-glassmorphic', confirmButton: 'swal2-confirm-btn' },
                        buttonsStyling: false
                    });
                    
                    // Reset form
                    this.preApproveForm.name = '';
                    this.preApproveForm.email = '';
                    this.preApproveForm.trial_days = '14';
                    
                    // Reload dashboard data to show the new tenant in list
                    await this.loadDashboard();
                }
            } catch (e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Persetujuan Gagal',
                    text: e.response?.data?.message || 'Terjadi kesalahan sistem saat memproses persetujuan.',
                    customClass: { popup: 'swal2-glassmorphic', confirmButton: 'swal2-confirm-btn' },
                    buttonsStyling: false
                });
            } finally {
                this.preApproving = false;
            }
        },

        async saveDevWhatsapp() {
            this.savingWa = true;
            try {
                const token = localStorage.getItem('token');
                await axios.put('/peace_seafood/api/settings/platform_developer_whatsapp', {
                    nilai: this.devWhatsappNumber
                }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                
                iziToast.success({
                    title: 'Sukses',
                    message: 'Nomor WhatsApp bantuan platform berhasil disimpan!',
                    position: 'topRight'
                });
            } catch (e) {
                console.error(e);
                iziToast.error({
                    title: 'Gagal',
                    message: e.response?.data?.message || 'Gagal menyimpan nomor WhatsApp bantuan.',
                    position: 'topRight'
                });
            } finally {
                this.savingWa = false;
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
