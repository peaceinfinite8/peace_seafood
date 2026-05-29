<?php ?>
<div x-data="laporanPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Laporan & Export</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Laporan stok, penjualan, dan keuangan</p>
        </div>
    </div>

    <!-- Filter Period -->
    <div class="card p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="form-label text-xs">Dari Tanggal</label>
                <input type="text" id="laporan-dari" class="form-input" placeholder="Pilih tanggal..." readonly>
            </div>
            <div>
                <label class="form-label text-xs">Sampai Tanggal</label>
                <input type="text" id="laporan-sampai" class="form-input" placeholder="Pilih tanggal..." readonly>
            </div>
            <button @click="loadData()" class="btn btn-primary">
                <i data-lucide="search" class="w-4 h-4"></i>
                Tampilkan
            </button>
            <div class="flex-1"></div>
            <div class="flex gap-2" x-show="['super_admin','bos'].includes(user.role)">
                <button @click="exportCsv()" class="btn btn-secondary">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Export Excel
                </button>
                <button @click="exportPdf()" class="btn btn-secondary">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    Export PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="keu-tab-group mb-4">
        <button @click="activeTab = 'stok'"
            class="keu-tab"
            :class="activeTab === 'stok' ? 'keu-tab--active keu-tab--semua' : 'keu-tab--idle'">
            Stok
        </button>
        <button @click="activeTab = 'penjualan'"
            class="keu-tab"
            :class="activeTab === 'penjualan' ? 'keu-tab--active keu-tab--semua' : 'keu-tab--idle'">
            Penjualan
        </button>
        <button @click="activeTab = 'keuangan'"
            class="keu-tab"
            :class="activeTab === 'keuangan' ? 'keu-tab--active keu-tab--semua' : 'keu-tab--idle'">
            Keuangan
        </button>
        <button @click="activeTab = 'aging'"
            class="keu-tab"
            :class="activeTab === 'aging' ? 'keu-tab--active keu-tab--hutang' : 'keu-tab--idle'">
            Hutang Aging
        </button>
    </div>

    <!-- Stok Report -->
    <div x-show="activeTab === 'stok'" class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Produk</th>
                        <th>Qty Teoritis</th>
                        <th>Qty Actual</th>
                        <th>Harga Beli</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="stokData.length === 0">
                        <tr>
                            <td colspan="8" class="text-center py-8" style="color:var(--text-secondary)">Tidak ada data</td>
                        </tr>
                    </template>
                    <template x-for="row in stokData" :key="row.id">
                        <tr>
                            <td class="text-sm" x-text="new Date(row.created_at).toLocaleDateString('id-ID')"></td>
                            <td class="text-sm" x-text="row.nama_supplier"></td>
                            <td class="text-sm font-medium" x-text="row.nama_produk"></td>
                            <td class="text-sm" x-text="parseFloat(row.qty) + ' kg'"></td>
                            <td class="text-sm" x-text="row.qty_actual ? parseFloat(row.qty_actual) + ' kg' : '-'"></td>
                            <td class="text-sm" x-text="'Rp ' + parseFloat(row.harga_beli||0).toLocaleString('id-ID')"></td>
                            <td class="text-sm font-medium" x-text="'Rp ' + parseFloat(row.total||0).toLocaleString('id-ID')"></td>
                            <td><span class="badge" :class="row.status==='confirmed'?'badge-success':'badge-warning'" x-text="row.status?.toUpperCase()"></span></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Penjualan Report -->
    <div x-show="activeTab === 'penjualan'" class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>No Nota</th>
                        <th>Tanggal</th>
                        <th>Pembeli</th>
                        <th>Subtotal</th>
                        <th>Diskon</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="penjualanData.length === 0">
                        <tr>
                            <td colspan="8" class="text-center py-8" style="color:var(--text-secondary)">Tidak ada data</td>
                        </tr>
                    </template>
                    <template x-for="row in penjualanData" :key="row.id">
                        <tr>
                            <td class="font-mono text-sm" x-text="row.no_nota"></td>
                            <td class="text-sm" x-text="new Date(row.tanggal_nota).toLocaleDateString('id-ID')"></td>
                            <td class="text-sm" x-text="row.nama_pembeli || 'Umum'"></td>
                            <td class="text-sm" x-text="'Rp ' + parseFloat(row.subtotal||0).toLocaleString('id-ID')"></td>
                            <td class="text-sm text-red-500" x-show="row.diskon > 0" x-text="'- Rp ' + parseFloat(row.diskon).toLocaleString('id-ID')"></td>
                            <td class="text-sm" x-show="row.diskon <= 0">-</td>
                            <td class="text-sm font-bold" style="color:var(--color-success)" x-text="'Rp ' + parseFloat(row.total||0).toLocaleString('id-ID')"></td>
                            <td><span class="badge" :class="row.jenis_pembayaran==='cash'?'badge-success':'badge-warning'" x-text="row.jenis_pembayaran?.toUpperCase()"></span></td>
                            <td><span class="badge" :class="row.status==='final'?'badge-success':'badge-gray'" x-text="row.status?.toUpperCase()"></span></td>
                        </tr>
                    </template>
                </tbody>
                <tfoot x-show="penjualanData.length > 0">
                    <tr style="background: var(--color-primary-light)">
                        <td colspan="5" class="font-bold text-sm text-right pr-4">TOTAL PENJUALAN FINAL:</td>
                        <td class="font-bold text-sm" style="color:var(--color-primary)"
                            x-text="'Rp ' + penjualanData.filter(n=>n.status==='final').reduce((s,n)=>s+parseFloat(n.total||0),0).toLocaleString('id-ID')"></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Aging Report -->
    <div x-show="activeTab === 'aging'" class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Pihak</th>
                        <th>Nominal</th>
                        <th>Sisa</th>
                        <th>Jatuh Tempo</th>
                        <th>Status Aging</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="agingData.length === 0">
                        <tr>
                            <td colspan="6" class="text-center py-8" style="color:var(--color-success)">✓ Semua tagihan aman</td>
                        </tr>
                    </template>
                    <template x-for="row in agingData" :key="row.id">
                        <tr>
                            <td><span class="badge" :class="row.jenis==='hutang'?'badge-danger':'badge-success'" x-text="row.jenis?.toUpperCase()"></span></td>
                            <td class="text-sm font-medium" x-text="row.nama_supplier || row.nama_pembeli || '-'"></td>
                            <td class="text-sm" x-text="'Rp ' + parseFloat(row.nominal||0).toLocaleString('id-ID')"></td>
                            <td class="text-sm font-bold text-red-500" x-text="'Rp ' + parseFloat(row.sisa_hutang||0).toLocaleString('id-ID')"></td>
                            <td class="text-sm" x-text="row.jatuh_tempo ? new Date(row.jatuh_tempo).toLocaleDateString('id-ID') : '-'"></td>
                            <td>
                                <span class="badge"
                                    :class="{'badge-danger':row.aging_status==='overdue','badge-warning':row.aging_status==='soon','badge-success':row.aging_status==='ok','badge-gray':row.aging_status==='no_due'}"
                                    x-text="row.aging_status === 'overdue' ? 'JATUH TEMPO' : row.aging_status === 'soon' ? 'SEGERA' : row.aging_status === 'ok' ? 'AMAN' : 'NO DUE'">
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $scripts = <<<'JS'
<script>
function laporanPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        loading: false,
        filters: {
            dari: new Date(new Date().setDate(1)).toISOString().split('T')[0],
            sampai: new Date().toISOString().split('T')[0],
        },
        activeTab: 'penjualan',
        stokData: [],
        penjualanData: [],
        keuanganData: {},
        agingData: [],

        async init() {
            if (!['super_admin', 'bos', 'admin'].includes(this.user.role)) {
                window.location.href = '/peace_seafood/dashboard';
                return;
            }
            await this.loadData();
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
                this.initDatePickers();
            });
        },

        initDatePickers() {
            if (!window.flatpickr) return;

            const locale = {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    longhand:  ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],
                },
                months: {
                    shorthand: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                    longhand:  ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
                },
            };

            const opts = {
                locale,
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true,
            };

            flatpickr('#laporan-dari', {
                ...opts,
                defaultDate: this.filters.dari,
                onChange: ([d]) => { this.filters.dari = d ? flatpickr.formatDate(d, 'Y-m-d') : ''; },
            });

            flatpickr('#laporan-sampai', {
                ...opts,
                defaultDate: this.filters.sampai,
                onChange: ([d]) => { this.filters.sampai = d ? flatpickr.formatDate(d, 'Y-m-d') : ''; },
            });
        },

        async loadData() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const q = `?dari=${this.filters.dari}&sampai=${this.filters.sampai}`;
                const [stokRes, penjRes, agingRes] = await Promise.all([
                    axios.get('/peace_seafood/api/laporan/stok' + q, { headers }),
                    axios.get('/peace_seafood/api/laporan/penjualan' + q, { headers }),
                    axios.get('/peace_seafood/api/laporan/hutang-aging', { headers }),
                ]);
                this.stokData     = stokRes.data?.data || [];
                this.penjualanData = penjRes.data?.data || [];
                this.agingData    = agingRes.data?.data || [];
            } catch(e) { console.error(e); }
            this.loading = false;
        },

        exportCsv() {
            const token = localStorage.getItem('token');
            window.location.href = `/peace_seafood/api/laporan/export-csv?tab=${this.activeTab}&dari=${this.filters.dari}&sampai=${this.filters.sampai}&token=${token}`;
        },
        exportPdf() {
            const token = localStorage.getItem('token');
            window.location.href = `/peace_seafood/api/laporan/export-pdf?tab=${this.activeTab}&dari=${this.filters.dari}&sampai=${this.filters.sampai}&token=${token}`;
        },
    };
}
</script>
JS;
?>