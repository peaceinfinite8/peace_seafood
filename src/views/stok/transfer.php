<?php

/** @var string $activeMenu */ ?>
<div x-data="stokTransferPage()" x-init="init()">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Stok Transfer</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Transfer stok antar gudang dan perubahan status kirim/terima</p>
        </div>
        <button @click="openCreate()" class="btn btn-primary" x-show="['super_admin','bos','admin','checker'].includes(user.role)">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Buat Transfer
        </button>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card">
            <p class="text-xs uppercase" style="color:var(--text-secondary)">Total</p>
            <p class="text-2xl font-bold" x-text="filtered.length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase" style="color:var(--text-secondary)">Pending</p>
            <p class="text-2xl font-bold text-yellow-500" x-text="filtered.filter(x => x.status === 'pending').length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase" style="color:var(--text-secondary)">Sent</p>
            <p class="text-2xl font-bold text-blue-500" x-text="filtered.filter(x => x.status === 'sent').length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase" style="color:var(--text-secondary)">Received</p>
            <p class="text-2xl font-bold text-green-500" x-text="filtered.filter(x => x.status === 'received').length"></p>
        </div>
    </div>

    <div class="card p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-center">
            <input type="text" x-model="search" placeholder="Cari transfer / gudang / produk..." class="form-input flex-1 min-w-56">
            <select x-model="filterStatus" class="form-input w-auto">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="sent">Sent</option>
                <option value="received">Received</option>
            </select>
            <button class="btn btn-secondary" @click="loadList()"><i data-lucide="refresh-cw" class="w-4 h-4"></i> Refresh</button>
        </div>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Asal</th>
                        <th>Tujuan</th>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="6" class="text-center py-8" style="color:var(--text-secondary)">Tidak ada data</td>
                        </tr>
                    </template>
                    <template x-for="item in filtered" :key="item.id">
                        <tr>
                            <td x-text="item.nama_gudang_asal || '-' "></td>
                            <td x-text="item.nama_gudang_tujuan || '-' "></td>
                            <td>
                                <div>
                                    <div class="font-medium" x-text="item.nama_produk"></div>
                                    <div class="text-xs" style="color:var(--text-secondary)" x-text="item.nama_jenis"></div>
                                </div>
                            </td>
                            <td x-text="formatNumber(item.qty) + ' kg'"></td>
                            <td><span class="badge" :class="item.status === 'received' ? 'badge-success' : (item.status === 'sent' ? 'badge-info' : 'badge-warning')" x-text="item.status?.toUpperCase()"></span></td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn btn-secondary p-1.5" @click="showDetail(item)"><i data-lucide="eye" class="w-3.5 h-3.5"></i></button>
                                    <button class="btn btn-primary p-1.5" x-show="item.status === 'pending'" @click="updateStatus(item.id, 'sent')">Sent</button>
                                    <button class="btn btn-success p-1.5" x-show="item.status === 'sent' || item.status === 'pending'" @click="updateStatus(item.id, 'received')">Receive</button>
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
                <h3 class="font-bold text-lg">Buat Transfer Stok</h3>
                <button @click="showCreateModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="form-group" x-show="canSelectSource()">
                    <label class="form-label">Gudang Asal</label>
                    <select class="form-input" x-model="form.gudang_asal_id" @change="onSourceChange()">
                        <option value="">Pilih gudang asal</option>
                        <template x-for="g in warehouses" :key="g.id">
                            <option :value="g.id" x-text="g.nama"></option>
                        </template>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Gudang Tujuan</label>
                    <select class="form-input" x-model="form.gudang_tujuan_id">
                        <option value="">Pilih gudang tujuan</option>
                        <template x-for="g in warehouses" :key="g.id">
                            <option :value="g.id" x-text="g.nama"></option>
                        </template>
                    </select>
                </div>
                <div class="form-group md:col-span-2">
                    <label class="form-label">Produk</label>
                    <select class="form-input" x-model="form.id_produk">
                        <option value="">Pilih produk</option>
                        <template x-for="p in products" :key="p.id">
                            <option :value="p.id" x-text="p.nama + ' — ' + (p.nama_jenis || '-')"></option>
                        </template>
                    </select>
                </div>
                <div class="form-group md:col-span-2">
                    <label class="form-label">Qty Transfer (kg)</label>
                    <input type="number" min="0" step="0.01" class="form-input" x-model="form.qty" placeholder="0">
                </div>
            </div>

            <div class="flex gap-3 justify-end">
                <button class="btn btn-secondary" @click="showCreateModal = false">Batal</button>
                <button class="btn btn-primary" :disabled="saving" @click="save()" x-text="saving ? 'Menyimpan...' : 'Simpan Transfer'"></button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" x-show="showDetailModal" x-cloak @click.self="showDetailModal = false">
        <div class="modal-box max-w-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg">Detail Transfer</h3>
                <button @click="showDetailModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <template x-if="selectedTransfer">
                <div class="space-y-2 text-sm">
                    <div><span style="color:var(--text-secondary)">Asal:</span> <strong x-text="selectedTransfer.nama_gudang_asal || '-' "></strong></div>
                    <div><span style="color:var(--text-secondary)">Tujuan:</span> <strong x-text="selectedTransfer.nama_gudang_tujuan || '-' "></strong></div>
                    <div><span style="color:var(--text-secondary)">Produk:</span> <strong x-text="selectedTransfer.nama_produk || '-' "></strong></div>
                    <div><span style="color:var(--text-secondary)">Qty:</span> <strong x-text="formatNumber(selectedTransfer.qty) + ' kg'"></strong></div>
                    <div><span style="color:var(--text-secondary)">Status:</span> <strong x-text="selectedTransfer.status?.toUpperCase() || '-' "></strong></div>
                </div>
            </template>
        </div>
    </div>
</div>

<?php $scripts = <<<'JS'
<script>
function stokTransferPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        warehouses: [],
        products: [],
        transfers: [],
        search: '',
        filterStatus: '',
        selectedSourceId: '',
        selectedTransfer: null,
        showCreateModal: false,
        showDetailModal: false,
        saving: false,
        form: { gudang_asal_id: '', gudang_tujuan_id: '', id_produk: '', qty: '1' },

        get filtered() {
            const q = this.search.toLowerCase();
            return this.transfers.filter(row => {
                const matchStatus = !this.filterStatus || row.status === this.filterStatus;
                const matchSearch = !q || [row.nama_gudang_asal, row.nama_gudang_tujuan, row.nama_produk, row.nama_jenis, row.status].some(v => String(v || '').toLowerCase().includes(q));
                return matchStatus && matchSearch;
            });
        },

        canSelectSource() {
            return ['super_admin', 'bos'].includes(this.user.role);
        },

        async init() {
            await this.loadWarehouses();
            this.resolveSource();
            await this.loadProducts();
            await this.loadList();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadWarehouses() {
            const token = localStorage.getItem('token');
            const res = await axios.get('/peace_seafood/api/settings/gudang', { headers: { Authorization: 'Bearer ' + token } });
            this.warehouses = res.data?.data || [];
        },

        resolveSource() {
            const userSource = this.user.id_gudang || '';
            const firstGudang = this.warehouses[0]?.id || '';
            this.selectedSourceId = this.canSelectSource() ? (userSource || firstGudang || '') : userSource;
            this.form.gudang_asal_id = this.selectedSourceId;
        },

        async loadProducts() {
            if (!this.selectedSourceId) {
                this.products = [];
                return;
            }
            const token = localStorage.getItem('token');
            const res = await axios.get('/peace_seafood/api/master/produk?id_gudang=' + this.selectedSourceId, { headers: { Authorization: 'Bearer ' + token } });
            this.products = res.data?.data || [];
            this.form.id_produk = '';
        },

        async onSourceChange() {
            this.selectedSourceId = this.form.gudang_asal_id;
            await this.loadProducts();
        },

        async loadList() {
            const token = localStorage.getItem('token');
            const headers = { Authorization: 'Bearer ' + token };
            const url = this.canSelectSource() && this.selectedSourceId ? '/peace_seafood/api/stok-transfer?id_gudang=' + this.selectedSourceId : '/peace_seafood/api/stok-transfer';
            const res = await axios.get(url, { headers });
            this.transfers = res.data?.data || [];
        },

        openCreate() {
            this.form = { gudang_asal_id: this.selectedSourceId, gudang_tujuan_id: '', id_produk: '', qty: '1' };
            this.showCreateModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        formatNumber(value) {
            const num = parseFloat(value || 0);
            return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },

        async save() {
            if (!this.form.gudang_tujuan_id || !this.form.id_produk || !this.form.qty) {
                iziToast.warning({ title: 'Perhatian', message: 'Gudang tujuan, produk, dan qty wajib diisi', position: 'topRight' });
                return;
            }
            this.saving = true;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/stok-transfer', this.form, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Transfer stok dibuat', position: 'topRight' });
                this.showCreateModal = false;
                await this.loadList();
            } catch (e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal menyimpan transfer', position: 'topRight' });
            }
            this.saving = false;
        },

        showDetail(item) {
            this.selectedTransfer = item;
            this.showDetailModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async updateStatus(id, status) {
            if (!await confirm('Ubah status transfer menjadi ' + status.toUpperCase() + '?')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.put('/peace_seafood/api/stok-transfer/' + id + '/status', { status }, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Status transfer diperbarui', position: 'topRight' });
                await this.loadList();
            } catch (e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal mengubah status', position: 'topRight' });
            }
        }
    };
}
</script>
JS;
?>