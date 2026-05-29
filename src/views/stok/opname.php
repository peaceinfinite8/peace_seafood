<?php

/** @var string $activeMenu */ ?>
<div x-data="stokOpnamePage()" x-init="init()">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Stok Opname</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Draft opname, finalisasi, dan penyesuaian stok fisik</p>
        </div>
        <button @click="openCreate()" class="btn btn-primary" x-show="['super_admin','bos','admin','checker'].includes(user.role)">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Buat Opname
        </button>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card">
            <p class="text-xs uppercase" style="color:var(--text-secondary)">Total</p>
            <p class="text-2xl font-bold" x-text="filtered.length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase" style="color:var(--text-secondary)">Draft</p>
            <p class="text-2xl font-bold text-yellow-500" x-text="filtered.filter(x => x.status === 'draft').length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase" style="color:var(--text-secondary)">Final</p>
            <p class="text-2xl font-bold text-green-500" x-text="filtered.filter(x => x.status === 'final').length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase" style="color:var(--text-secondary)">Gudang Aktif</p>
            <p class="text-2xl font-bold" x-text="selectedGudangName || '-' "></p>
        </div>
    </div>

    <div class="card p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-center">
            <input type="text" x-model="search" placeholder="Cari opname / gudang / user..." class="form-input flex-1 min-w-56">
            <select x-model="filterStatus" class="form-input w-auto">
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="final">Final</option>
            </select>
            <button class="btn btn-secondary" @click="loadList()">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                Refresh
            </button>
        </div>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Gudang</th>
                        <th>Dibuat Oleh</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="5" class="text-center py-8" style="color:var(--text-secondary)">Tidak ada data</td>
                        </tr>
                    </template>
                    <template x-for="item in filtered" :key="item.id">
                        <tr>
                            <td x-text="formatDate(item.tanggal_opname)"></td>
                            <td x-text="item.nama_gudang || selectedGudangName || '-' "></td>
                            <td x-text="item.nama_user || '-' "></td>
                            <td><span class="badge" :class="item.status === 'final' ? 'badge-success' : 'badge-warning'" x-text="item.status?.toUpperCase()"></span></td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn btn-secondary p-1.5" @click="showDetail(item.id)"><i data-lucide="eye" class="w-3.5 h-3.5"></i></button>
                                    <button class="btn btn-success p-1.5" x-show="item.status === 'draft' && ['super_admin','bos','admin'].includes(user.role)" @click="finalize(item.id)"><i data-lucide="check" class="w-3.5 h-3.5"></i></button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" x-show="showCreateModal" x-cloak @click.self="showCreateModal = false">
        <div class="modal-box max-w-4xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg">Buat Stok Opname</h3>
                <button @click="showCreateModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4" x-show="canSelectGudang()">
                <div class="form-group">
                    <label class="form-label">Gudang</label>
                    <select class="form-input" x-model="form.id_gudang" @change="onGudangChange()">
                        <option value="">Pilih gudang</option>
                        <template x-for="g in warehouses" :key="g.id">
                            <option :value="g.id" x-text="g.nama"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="form-label mb-0">Item Opname</label>
                    <button type="button" class="btn btn-secondary" @click="addItem()">Tambah Baris</button>
                </div>
                <div class="space-y-2">
                    <template x-for="(item, index) in form.items" :key="index">
                        <div class="grid grid-cols-12 gap-2 items-center">
                            <select class="form-input col-span-7" x-model="item.id_produk">
                                <option value="">Pilih produk</option>
                                <template x-for="p in products" :key="p.id">
                                    <option :value="p.id" x-text="p.nama + ' — ' + (p.nama_jenis || p.nama_jenis_ikan || '-')"></option>
                                </template>
                            </select>
                            <input type="number" min="0" step="0.01" class="form-input col-span-4" x-model="item.qty_fisik" placeholder="Qty fisik">
                            <button type="button" class="btn btn-danger p-2 col-span-1 justify-center" @click="removeItem(index)"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex gap-3 justify-end">
                <button class="btn btn-secondary" @click="showCreateModal = false">Batal</button>
                <button class="btn btn-primary" :disabled="saving" @click="save()" x-text="saving ? 'Menyimpan...' : 'Simpan Opname'"></button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" x-show="showDetailModal" x-cloak @click.self="showDetailModal = false">
        <div class="modal-box max-w-4xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg" x-text="detail?.nama_gudang ? 'Detail Opname - ' + detail.nama_gudang : 'Detail Opname'"></h3>
                <button @click="showDetailModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div x-show="detail" class="space-y-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    <div><span style="color:var(--text-secondary)">Tanggal:</span> <strong x-text="formatDate(detail?.tanggal_opname)"></strong></div>
                    <div><span style="color:var(--text-secondary)">Gudang:</span> <strong x-text="detail?.nama_gudang || '-' "></strong></div>
                    <div><span style="color:var(--text-secondary)">User:</span> <strong x-text="detail?.nama_user || '-' "></strong></div>
                    <div><span style="color:var(--text-secondary)">Status:</span> <strong x-text="detail?.status?.toUpperCase() || '-' "></strong></div>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Jenis</th>
                                <th>Qty Sistem</th>
                                <th>Qty Fisik</th>
                                <th>Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in (detail?.items || [])" :key="item.id">
                                <tr>
                                    <td x-text="item.nama_produk"></td>
                                    <td x-text="item.nama_jenis"></td>
                                    <td x-text="formatNumber(item.qty_sistem)"></td>
                                    <td x-text="formatNumber(item.qty_fisik)"></td>
                                    <td :class="parseFloat(item.qty_fisik || 0) - parseFloat(item.qty_sistem || 0) < 0 ? 'text-red-500' : 'text-green-500'" x-text="formatNumber(parseFloat(item.qty_fisik || 0) - parseFloat(item.qty_sistem || 0))"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $scripts = <<<'JS'
<script>
function stokOpnamePage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        warehouses: [],
        products: [],
        sessions: [],
        search: '',
        filterStatus: '',
        selectedGudangId: '',
        selectedGudangName: '',
        showCreateModal: false,
        showDetailModal: false,
        saving: false,
        detail: null,
        form: { id_gudang: '', items: [{ id_produk: '', qty_fisik: '' }] },

        get filtered() {
            const q = this.search.toLowerCase();
            return this.sessions.filter(row => {
                const matchStatus = !this.filterStatus || row.status === this.filterStatus;
                const matchSearch = !q || [row.nama_gudang, row.nama_user, row.status, row.tanggal_opname].some(v => String(v || '').toLowerCase().includes(q));
                return matchStatus && matchSearch;
            });
        },

        canSelectGudang() {
            return ['super_admin', 'bos'].includes(this.user.role);
        },

        async init() {
            await this.loadWarehouses();
            this.resolveGudang();
            await this.loadProducts();
            await this.loadList();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadWarehouses() {
            const token = localStorage.getItem('token');
            const res = await axios.get('/peace_seafood/api/settings/gudang', { headers: { Authorization: 'Bearer ' + token } });
            this.warehouses = res.data?.data || [];
        },

        resolveGudang() {
            const userGudang = this.user.id_gudang || '';
            const firstGudang = this.warehouses[0]?.id || '';
            this.selectedGudangId = this.canSelectGudang() ? (userGudang || firstGudang || '') : userGudang;
            this.selectedGudangName = this.warehouses.find(g => String(g.id) === String(this.selectedGudangId))?.nama || this.user.nama_gudang || 'All Gudang';
            if (!this.selectedGudangId && this.warehouses.length > 0) {
                this.selectedGudangId = this.warehouses[0].id;
            }
        },

        async loadProducts() {
            if (!this.selectedGudangId) {
                this.products = [];
                return;
            }
            const token = localStorage.getItem('token');
            const res = await axios.get('/peace_seafood/api/master/produk?id_gudang=' + this.selectedGudangId, { headers: { Authorization: 'Bearer ' + token } });
            this.products = res.data?.data || [];
            this.form.id_gudang = this.selectedGudangId;
        },

        async onGudangChange() {
            this.selectedGudangId = this.form.id_gudang;
            this.selectedGudangName = this.warehouses.find(g => String(g.id) === String(this.selectedGudangId))?.nama || '-';
            await this.loadProducts();
            this.form.items = [{ id_produk: '', qty_fisik: '' }];
        },

        async loadList() {
            const token = localStorage.getItem('token');
            const headers = { Authorization: 'Bearer ' + token };
            const url = this.canSelectGudang() && this.selectedGudangId ? '/peace_seafood/api/stok-opname?id_gudang=' + this.selectedGudangId : '/peace_seafood/api/stok-opname';
            const res = await axios.get(url, { headers });
            this.sessions = res.data?.data || [];
        },

        openCreate() {
            this.form = { id_gudang: this.selectedGudangId, items: [{ id_produk: '', qty_fisik: '' }] };
            this.showCreateModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        addItem() {
            this.form.items.push({ id_produk: '', qty_fisik: '' });
        },

        removeItem(index) {
            this.form.items.splice(index, 1);
            if (this.form.items.length === 0) this.addItem();
        },

        formatDate(value) {
            if (!value) return '-';
            return new Date(value).toLocaleDateString('id-ID');
        },

        formatNumber(value) {
            const num = parseFloat(value || 0);
            return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },

        async save() {
            const items = this.form.items.filter(item => item.id_produk && item.qty_fisik !== '');
            if (!this.form.id_gudang || items.length === 0) {
                iziToast.warning({ title: 'Perhatian', message: 'Gudang dan item opname wajib diisi', position: 'topRight' });
                return;
            }

            this.saving = true;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/stok-opname', {
                    id_gudang: this.form.id_gudang,
                    items: items,
                }, { headers: { Authorization: 'Bearer ' + token } });

                iziToast.success({ title: 'Berhasil', message: 'Sesi opname dibuat', position: 'topRight' });
                this.showCreateModal = false;
                await this.loadList();
            } catch (e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal menyimpan opname', position: 'topRight' });
            }
            this.saving = false;
        },

        async showDetail(id) {
            const token = localStorage.getItem('token');
            const res = await axios.get('/peace_seafood/api/stok-opname/' + id, { headers: { Authorization: 'Bearer ' + token } });
            this.detail = res.data?.data || null;
            this.showDetailModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async finalize(id) {
            if (!await confirm('Finalisasi opname ini? Stok produk akan disesuaikan.')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/stok-opname/' + id + '/finalize', {}, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Opname difinalisasi', position: 'topRight' });
                await this.loadList();
            } catch (e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal finalize opname', position: 'topRight' });
            }
        }
    };
}
</script>
JS;
?>