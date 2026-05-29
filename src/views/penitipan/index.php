<?php ?>
<div x-data="penitipanPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Penitipan</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Kelola barang titipan (konsinyasi)</p>
        </div>
        <a href="/peace_seafood/penitipan/create" class="btn btn-primary"
            x-show="['super_admin','admin'].includes(user.role)">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Terima Titipan
        </a>
    </div>

    <!-- Filter -->
    <div class="card p-4 mb-6">
        <div class="flex flex-wrap gap-3">
            <input type="text" x-model="search" placeholder="Cari supplier / produk..."
                class="form-input flex-1 min-w-48">
            <select x-model="filterStatus" class="form-input w-auto">
                <option value="">Semua Status</option>
                <option value="masuk">Masuk</option>
                <option value="dijual_sebagian">Dijual Sebagian</option>
                <option value="dijual_semua">Dijual Semua</option>
                <option value="selesai">Selesai</option>
            </select>
            <button @click="loadData()" class="btn btn-primary">
                <i data-lucide="search" class="w-4 h-4"></i> Cari
            </button>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6" x-show="!loading" x-cloak>
        <div class="stat-card">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Total Titipan</p>
            <p class="text-2xl font-bold" x-text="list.length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Aktif</p>
            <p class="text-2xl font-bold" style="color: var(--color-info)"
                x-text="list.filter(t => ['masuk','dijual_sebagian'].includes(t.status)).length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Selesai</p>
            <p class="text-2xl font-bold" style="color: var(--color-success)"
                x-text="list.filter(t => t.status === 'selesai').length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Total Qty</p>
            <p class="text-lg font-bold" style="color: var(--color-primary)"
                x-text="list.reduce((s,t) => s + parseFloat(t.jumlah||0), 0).toFixed(1) + ' kg'"></p>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="card p-12 text-center" style="color: var(--text-secondary)">
        <p class="text-sm">Memuat data...</p>
    </div>

    <!-- Table -->
    <div class="card" x-show="!loading" x-cloak>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Supplier/Pengirim</th>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Harga Titip</th>
                        <th>Komisi</th>
                        <th>Tgl Masuk</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="8" class="text-center py-10" style="color:var(--text-secondary)">
                                Tidak ada data penitipan
                            </td>
                        </tr>
                    </template>
                    <template x-for="t in filtered" :key="t.id">
                        <tr>
                            <td><span class="font-medium text-sm"
                                    x-text="t.nama_supplier || t.nama_pembeli || '-'"></span></td>
                            <td><span class="text-sm" x-text="t.nama_produk || '-'"></span></td>
                            <td>
                                <span class="text-sm font-semibold"
                                    x-text="parseFloat(t.jumlah||0).toFixed(1) + ' kg'"></span>
                                <span x-show="t.jumlah_terjual > 0" class="block text-xs"
                                    style="color: var(--color-success)"
                                    x-text="'Terjual: ' + parseFloat(t.jumlah_terjual||0).toFixed(1) + ' kg'"></span>
                            </td>
                            <td><span class="text-sm"
                                    x-text="'Rp ' + parseFloat(t.harga_titip||0).toLocaleString('id-ID')"></span></td>
                            <td><span class="text-sm" x-text="(t.komisi_persen||0) + '%'"></span></td>
                            <td><span class="text-sm" x-text="formatDate(t.tanggal_masuk)"></span></td>
                            <td>
                                <span class="badge" :class="{
                                        'badge-info':    t.status==='masuk',
                                        'badge-warning': t.status==='dijual_sebagian',
                                        'badge-success': t.status==='selesai'||t.status==='dijual_semua'
                                      }" x-text="statusLabel(t.status)"></span>
                            </td>
                            <td>
                                <div class="flex gap-1.5">
                                    <button @click="openDetail(t.id)" class="btn btn-secondary p-1.5" title="Detail">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button
                                        x-show="['masuk','dijual_sebagian'].includes(t.status) && ['super_admin','admin'].includes(user.role)"
                                        @click="openJual(t)" class="btn btn-primary p-1.5" title="Catat Penjualan">
                                        <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button
                                        x-show="t.status==='dijual_semua' && ['super_admin','admin'].includes(user.role)"
                                        @click="doSettlement(t.id)" class="btn btn-success p-1.5" title="Settlement">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
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
    <div class="modal-overlay" x-show="showDetailModal" @click.self="showDetailModal = false" x-cloak>
        <div class="modal-box max-w-2xl">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg">Detail Penitipan</h3>
                <button @click="showDetailModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <template x-if="detail">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                        <p class="text-xs mb-1" style="color:var(--text-secondary)">Supplier/Pengirim</p>
                        <p class="font-semibold" x-text="detail.nama_supplier || detail.nama_pembeli || '-'"></p>
                    </div>
                    <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                        <p class="text-xs mb-1" style="color:var(--text-secondary)">Produk</p>
                        <p class="font-semibold" x-text="detail.nama_produk || '-'"></p>
                    </div>
                    <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                        <p class="text-xs mb-1" style="color:var(--text-secondary)">Jumlah Titipan</p>
                        <p class="font-semibold" x-text="parseFloat(detail.jumlah||0).toFixed(1) + ' kg'"></p>
                    </div>
                    <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                        <p class="text-xs mb-1" style="color:var(--text-secondary)">Sudah Terjual</p>
                        <p class="font-semibold text-green-600"
                            x-text="parseFloat(detail.jumlah_terjual||0).toFixed(1) + ' kg'"></p>
                    </div>
                    <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                        <p class="text-xs mb-1" style="color:var(--text-secondary)">Harga Titip</p>
                        <p class="font-semibold"
                            x-text="'Rp ' + parseFloat(detail.harga_titip||0).toLocaleString('id-ID')"></p>
                    </div>
                    <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                        <p class="text-xs mb-1" style="color:var(--text-secondary)">Komisi</p>
                        <p class="font-semibold text-blue-600" x-text="(detail.komisi_persen||0) + '%'"></p>
                    </div>
                    <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                        <p class="text-xs mb-1" style="color:var(--text-secondary)">Tanggal Masuk</p>
                        <p class="font-semibold" x-text="formatDate(detail.tanggal_masuk)"></p>
                    </div>
                    <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                        <p class="text-xs mb-1" style="color:var(--text-secondary)">Status</p>
                        <span class="badge"
                            :class="{'badge-info':detail.status==='masuk','badge-warning':detail.status==='dijual_sebagian','badge-success':['selesai','dijual_semua'].includes(detail.status)}"
                            x-text="statusLabel(detail.status)"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Modal Catat Penjualan -->
    <div class="modal-overlay" x-show="showJual" @click.self="showJual = false" x-cloak>
        <div class="modal-box">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg">Catat Penjualan Titipan</h3>
                <button @click="showJual = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <template x-if="selectedTitipan">
                <div class="mb-4 p-3 rounded-lg text-sm" style="background:var(--bg-gray)">
                    Produk: <strong x-text="selectedTitipan.nama_produk"></strong> —
                    Sisa: <strong
                        x-text="(parseFloat(selectedTitipan.jumlah||0)-parseFloat(selectedTitipan.jumlah_terjual||0)).toFixed(1)+' kg'"></strong>
                </div>
            </template>
            <form @submit.prevent="submitJual()">
                <div class="form-group">
                    <label class="form-label">Jumlah Terjual (kg) *</label>
                    <input type="number" x-model="jualForm.jumlah_terjual" step="0.01" min="0.01" class="form-input"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Jual / kg *</label>
                    <input type="number" x-model="jualForm.harga_jual" step="100" min="0" class="form-input" required>
                    <p class="text-xs mt-1" style="color:var(--text-secondary)">
                        Total: <strong
                            x-text="'Rp '+(jualForm.jumlah_terjual*jualForm.harga_jual||0).toLocaleString('id-ID')"></strong>
                    </p>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal *</label>
                    <input type="date" x-model="jualForm.tanggal" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Penjual</label>
                    <select x-model="jualForm.penjual" class="form-input">
                        <option value="gudang_penerima">Gudang Penerima</option>
                        <option value="supplier_pengirim">Supplier Pengirim</option>
                    </select>
                </div>
                <div class="flex gap-3 justify-end mt-6">
                    <button type="button" @click="showJual = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary" :disabled="submitting">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span x-text="submitting ? 'Menyimpan...' : 'Simpan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php $scripts = <<<'JS'
<script>
function penitipanPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        loading: true,
        list: [],
        search: '',
        filterStatus: '',
        showDetailModal: false,
        detail: null,
        showJual: false,
        selectedTitipan: null,
        submitting: false,
        jualForm: { titipan_id: null, jumlah_terjual: '', harga_jual: '', tanggal: new Date().toISOString().slice(0,10), penjual: 'gudang_penerima' },

        get filtered() {
            const q = this.search.toLowerCase();
            return this.list.filter(t =>
                (!q || (t.nama_supplier||'').toLowerCase().includes(q) ||
                       (t.nama_pembeli||'').toLowerCase().includes(q) ||
                       (t.nama_produk||'').toLowerCase().includes(q)) &&
                (!this.filterStatus || t.status === this.filterStatus)
            );
        },

        async init() {
            if (!['super_admin', 'bos', 'admin'].includes(this.user.role)) {
                window.location.href = '/peace_seafood/dashboard';
                return;
            }
            await this.loadData();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadData() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/penitipan', { headers: { Authorization: 'Bearer '+token } });
                this.list = res.data?.data || [];
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal memuat data', position: 'topRight' }); }
            this.loading = false;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async openDetail(id) {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/penitipan/' + id, { headers: { Authorization: 'Bearer '+token } });
                this.detail = res.data?.data;
                this.showDetailModal = true;
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal memuat detail', position: 'topRight' }); }
        },

        openJual(titipan) {
            this.selectedTitipan = titipan;
            this.jualForm = { titipan_id: titipan.id, jumlah_terjual: '', harga_jual: titipan.harga_titip||'', tanggal: new Date().toISOString().slice(0,10), penjual: 'gudang_penerima' };
            this.showJual = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async submitJual() {
            this.submitting = true;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/penitipan/' + this.jualForm.titipan_id + '/jual', this.jualForm, { headers: { Authorization: 'Bearer '+token } });
                iziToast.success({ title: 'Berhasil', message: 'Penjualan titipan dicatat!', position: 'topRight' });
                this.showJual = false;
                await this.loadData();
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
            this.submitting = false;
        },

        async doSettlement(id) {
            if (!await confirm('Lakukan settlement untuk titipan ini?')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/penitipan/' + id + '/selesai', { titipan_id: id }, { headers: { Authorization: 'Bearer '+token } });
                iziToast.success({ title: 'Berhasil', message: 'Settlement selesai!', position: 'topRight' });
                await this.loadData();
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal', position: 'topRight' }); }
        },

        statusLabel(s) {
            return { masuk:'Masuk', dijual_sebagian:'Dijual Sebagian', dijual_semua:'Dijual Semua', selesai:'Selesai' }[s] || s;
        },

        formatDate(d) { return d ? new Date(d).toLocaleDateString('id-ID') : '-'; }
    };
}
</script>
JS;
?>