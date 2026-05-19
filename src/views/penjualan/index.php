<?php ?>
<div x-data="penjualanPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Penjualan</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Kelola nota penjualan</p>
        </div>
        <a href="/peace_seafood/penjualan/create" class="btn btn-primary" x-show="['admin','bos'].includes(user.role)">
            <i data-lucide="file-plus" class="w-4 h-4"></i>
            Buat Nota
        </a>
    </div>

    <!-- Filter -->
    <div class="card p-4 mb-6">
        <div class="flex flex-wrap gap-3">
            <input type="text" x-model="search" placeholder="Cari no nota / pembeli..."
                class="form-input flex-1 min-w-48">
            <input type="text" id="filter-dari" placeholder="Tanggal dari..." class="form-input w-auto" readonly>
            <input type="text" id="filter-sampai" placeholder="Tanggal sampai..." class="form-input w-auto" readonly>
            <select x-model="filterStatus" class="form-input w-auto">
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="final">Final</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <button @click="loadData()" class="btn btn-primary">
                <i data-lucide="search" class="w-4 h-4"></i>
                Cari
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6" x-show="!loading" x-cloak>
        <div class="stat-card">
            <p class="text-xs" style="color: var(--text-secondary)">Total Nota</p>
            <p class="text-2xl font-bold" x-text="notaList.length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs" style="color: var(--text-secondary)">Total Penjualan</p>
            <p class="text-lg font-bold" style="color: var(--color-success)"
                x-text="'Rp ' + notaList.filter(n=>n.status==='final').reduce((s,n)=>s+parseFloat(n.total),0).toLocaleString('id-ID')">
            </p>
        </div>
        <div class="stat-card">
            <p class="text-xs" style="color: var(--text-secondary)">Draft</p>
            <p class="text-2xl font-bold text-yellow-500" x-text="notaList.filter(n=>n.status==='draft').length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs" style="color: var(--text-secondary)">Final</p>
            <p class="text-2xl font-bold text-green-500" x-text="notaList.filter(n=>n.status==='final').length"></p>
        </div>
    </div>

    <!-- Table -->
    <div class="card" x-show="!loading" x-cloak>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>No Nota</th>
                        <th>Tanggal</th>
                        <th>Pembeli</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filteredNota.length === 0">
                        <tr>
                            <td colspan="7" class="text-center py-8" style="color:var(--text-secondary)">Tidak ada data
                            </td>
                        </tr>
                    </template>
                    <template x-for="nota in filteredNota" :key="nota.id">
                        <tr>
                            <td><span class="font-mono text-sm font-medium" x-text="nota.no_nota"></span></td>
                            <td><span class="text-sm" x-text="formatDate(nota.tanggal_nota)"></span></td>
                            <td><span class="text-sm" x-text="nota.nama_pembeli || 'Umum'"></span></td>
                            <td><span class="font-semibold" style="color:var(--color-success)"
                                    x-text="'Rp ' + parseFloat(nota.total).toLocaleString('id-ID')"></span></td>
                            <td>
                                <span class="badge"
                                    :class="nota.jenis_pembayaran === 'cash' ? 'badge-success' : 'badge-warning'"
                                    x-text="nota.jenis_pembayaran?.toUpperCase()"></span>
                            </td>
                            <td>
                                <span class="badge"
                                    :class="{'badge-success':nota.status==='final','badge-warning':nota.status==='draft','badge-gray':nota.status==='cancelled'}"
                                    x-text="nota.status?.toUpperCase()"></span>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button @click="showDetail(nota.id)" class="btn btn-secondary p-1.5" title="Detail">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button x-show="nota.status === 'draft' && ['admin','bos'].includes(user.role)"
                                        @click="finalizeNota(nota.id)" class="btn btn-success p-1.5" title="Finalize">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button x-show="nota.status !== 'cancelled' && ['admin','bos'].includes(user.role)"
                                        @click="cancelNota(nota.id)" class="btn btn-danger p-1.5" title="Cancel">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal-overlay" x-show="showModal" @click.self="showModal = false" x-cloak>
        <div class="modal-box max-w-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg" x-text="'Nota: ' + (detail?.no_nota || '')"></h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <template x-if="detail">
                <div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 text-sm">
                        <div class="space-y-2">
                            <div><span style="color:var(--text-secondary)">No. Nota:</span> <strong
                                    x-text="detail.no_nota"></strong></div>
                            <div><span style="color:var(--text-secondary)">Pembeli:</span> <strong
                                    x-text="detail.nama_pembeli || 'Umum'"></strong></div>
                            <div><span style="color:var(--text-secondary)">Telepon:</span> <strong
                                    x-text="detail.no_telepon || '-' "></strong></div>
                            <div><span style="color:var(--text-secondary)">Alamat:</span> <strong
                                    x-text="detail.alamat_pembeli || '-' "></strong></div>
                            <div x-show="detail.catatan"><span style="color:var(--text-secondary)">Catatan:</span>
                                <div class="mt-1 p-2 bg-gray-50 rounded text-sm" x-text="detail.catatan"></div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div><span style="color:var(--text-secondary)">Tanggal:</span> <strong
                                    x-text="formatDate(detail.tanggal_nota)"></strong></div>
                            <div><span style="color:var(--text-secondary)">Pembayaran:</span> <strong
                                    x-text="detail.jenis_pembayaran?.toUpperCase()"></strong></div>
                            <div><span style="color:var(--text-secondary)">Status:</span> <strong
                                    x-text="detail.status?.toUpperCase()"></strong></div>
                            <div><span style="color:var(--text-secondary)">Sales:</span> <strong
                                    x-text="detail.sales_name || '-' "></strong></div>
                            <div><span style="color:var(--text-secondary)">Pengiriman:</span> <strong
                                    x-text="detail.metode_pengiriman || '-' "></strong></div>
                            <div class="text-xs text-gray-500">
                                <div><span style="color:var(--text-secondary)">Dibuat:</span> <span
                                        x-text="detail.created_at ? formatDate(detail.created_at) + ' ' + (new Date(detail.created_at).toLocaleTimeString()) : '-' "></span>
                                </div>
                                <div><span style="color:var(--text-secondary)">Diubah:</span> <span
                                        x-text="detail.updated_at ? formatDate(detail.updated_at) + ' ' + (new Date(detail.updated_at).toLocaleTimeString()) : '-' "></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto mb-4">
                        <table class="table w-full">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    <th>Diskon</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="item in detail.items" :key="item.id">
                                    <tr>
                                        <td x-text="item.nama_produk"></td>
                                        <td x-text="parseFloat(item.qty) + ' ' + (item.satuan || 'kg')"></td>
                                        <td x-text="'Rp ' + parseFloat(item.harga_jual).toLocaleString('id-ID')"></td>
                                        <td
                                            x-text="item.diskon ? ('- Rp ' + parseFloat(item.diskon).toLocaleString('id-ID')) : '-' ">
                                        </td>
                                        <td x-text="'Rp ' + parseFloat(item.subtotal).toLocaleString('id-ID')"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-right space-y-1 text-sm">
                        <div>Subtotal: <strong
                                x-text="'Rp ' + parseFloat(detail.subtotal||0).toLocaleString('id-ID')"></strong></div>
                        <div x-show="detail.diskon > 0">Diskon Nota: <strong class="text-red-500"
                                x-text="'- Rp ' + parseFloat(detail.diskon).toLocaleString('id-ID')"></strong></div>
                        <div x-show="detail.pajak > 0">Pajak: <strong
                                x-text="'+ Rp ' + parseFloat(detail.pajak).toLocaleString('id-ID')"></strong></div>
                        <div x-show="detail.ongkir > 0">Ongkos Kirim: <strong
                                x-text="'+ Rp ' + parseFloat(detail.ongkir).toLocaleString('id-ID')"></strong></div>
                        <div>Dibayar: <strong
                                x-text="'Rp ' + parseFloat(detail.dibayar || 0).toLocaleString('id-ID')"></strong></div>
                        <div>Kembalian: <strong
                                x-text="'Rp ' + parseFloat((detail.dibayar || 0) - (detail.total || 0)).toLocaleString('id-ID')"></strong>
                        </div>
                        <div class="text-lg font-bold" style="color:var(--color-primary)">
                            Total: <span x-text="'Rp ' + parseFloat(detail.total||0).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<?php $scripts = <<<'JS'
<script>
function penjualanPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        loading: true,
        notaList: [],
        search: '',
        filterDari: '',
        filterSampai: '',
        filterStatus: '',
        showModal: false,
        detail: null,

        get filteredNota() {
            const q = this.search.toLowerCase();
            return this.notaList.filter(n =>
                (!q || n.no_nota?.toLowerCase().includes(q) || n.nama_pembeli?.toLowerCase().includes(q)) &&
                (!this.filterStatus || n.status === this.filterStatus)
            );
        },

        async init() {
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

            const fpDari = flatpickr('#filter-dari', {
                ...opts,
                onChange: ([d]) => { this.filterDari = d ? flatpickr.formatDate(d, 'Y-m-d') : ''; },
            });

            const fpSampai = flatpickr('#filter-sampai', {
                ...opts,
                onChange: ([d]) => { this.filterSampai = d ? flatpickr.formatDate(d, 'Y-m-d') : ''; },
            });
        },

        async loadData() {
            this.loading = true;
            try {
                const token   = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                let url = '/peace_seafood/api/penjualan?per_page=100';
                if (this.filterDari)   url += '&dari=' + this.filterDari;
                if (this.filterSampai) url += '&sampai=' + this.filterSampai;
                const res = await axios.get(url, { headers });
                this.notaList = res.data?.data || [];
            } catch(e) { console.error(e); }
            this.loading = false;
        },

        async showDetail(id) {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/penjualan/' + id, { headers: { Authorization: 'Bearer ' + token } });
                this.detail = res.data?.data;
                this.showModal = true;
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal load detail', position: 'topRight' }); }
        },

        async finalizeNota(id) {
            if (!confirm('Finalize nota ini? Stok akan dikurangi.')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/penjualan/' + id + '/finalize', {}, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Nota difinalize!', position: 'topRight' });
                await this.loadData();
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
        },

        async cancelNota(id) {
            if (!confirm('Batalkan nota ini?')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/penjualan/' + id + '/cancel', {}, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Nota dibatalkan', position: 'topRight' });
                await this.loadData();
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal', position: 'topRight' }); }
        },

        formatDate(d) { return d ? new Date(d).toLocaleDateString('id-ID') : '-'; }
    };
}
</script>
JS;
?>