<?php

/** @var string $activeMenu */ ?>
<div x-data="stokTransferPage()" x-init="init()">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Stok Transfer</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Transfer stok antar gudang dan perubahan status
                kirim/terima</p>
        </div>
        <button @click="openCreate()"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"
            x-show="['super_admin','bos','admin','checker'].includes(user.role)">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Buat Transfer
        </button>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-4">
            <p class="text-xs uppercase text-slate-500 dark:text-slate-400">Total</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100" x-text="filtered.length"></p>
        </div>
        <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-4">
            <p class="text-xs uppercase text-slate-500 dark:text-slate-400">Pending</p>
            <p class="text-2xl font-bold text-yellow-500" x-text="filtered.filter(x => x.status === 'pending').length">
            </p>
        </div>
        <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-4">
            <p class="text-xs uppercase text-slate-500 dark:text-slate-400">Sent</p>
            <p class="text-2xl font-bold text-blue-500" x-text="filtered.filter(x => x.status === 'sent').length"></p>
        </div>
        <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-4">
            <p class="text-xs uppercase text-slate-500 dark:text-slate-400">Received</p>
            <p class="text-2xl font-bold text-green-500" x-text="filtered.filter(x => x.status === 'received').length">
            </p>
        </div>
    </div>

    <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-center">
            <input type="text" x-model="search" placeholder="Cari transfer / gudang / produk..."
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white flex-1 min-w-56">
            <select x-model="filterStatus"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white w-auto">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="sent">Sent</option>
                <option value="received">Received</option>
            </select>
            <button
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600"
                @click="loadList()"><i data-lucide="refresh-cw" class="w-4 h-4"></i> Refresh</button>
        </div>
    </div>

    <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
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
                            <td colspan="6" class="text-center py-8 text-slate-500 dark:text-slate-400">Tidak ada data
                            </td>
                        </tr>
                    </template>
                    <template x-for="item in filtered" :key="item.id">
                        <tr>
                            <td x-text="item.nama_gudang_asal || '-' "></td>
                            <td x-text="item.nama_gudang_tujuan || '-' "></td>
                            <td>
                                <div>
                                    <div class="font-medium" x-text="item.nama_produk"></div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400" x-text="item.nama_jenis">
                                    </div>
                                </div>
                            </td>
                            <td x-text="formatNumber(item.qty) + ' kg'"></td>
                            <td><span
                                    class="inline-flex items-center rounded-full bg-slate-200 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200"
                                    :class="item.status === 'received' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-700/30 dark:text-emerald-400' : (item.status === 'sent' ? 'bg-sky-100 text-sky-700 dark:bg-sky-700/30 dark:text-sky-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-700/30 dark:text-amber-400')"
                                    x-text="item.status?.toUpperCase()"></span></td>
                            <td>
                                <div class="flex gap-2">
                                    <button
                                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600 p-1.5"
                                        @click="showDetail(item)"><i data-lucide="eye" class="w-3.5 h-3.5"></i></button>
                                    <button
                                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700 p-1.5"
                                        x-show="item.status === 'pending'"
                                        @click="updateStatus(item.id, 'sent')">Sent</button>
                                    <button
                                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 dark:bg-emerald-700 p-1.5"
                                        x-show="item.status === 'sent' || item.status === 'pending'"
                                        @click="updateStatus(item.id, 'received')">Receive</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6" x-show="showCreateModal"
        x-cloak @click.self="showCreateModal = false">
        <div
            class="max-w-4xl w-full rounded-lg border border-slate-300 bg-white shadow-xl dark:border-slate-600 dark:bg-slate-800">
            <div
                class="border-b border-slate-200 dark:border-slate-700 px-4 py-3 sm:flex sm:items-center sm:justify-between">
                <h3 class="font-bold text-lg">Buat Transfer Stok</h3>
                <button @click="showCreateModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <div class="px-4 py-4 sm:p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="space-y-1.5" x-show="canSelectSource()">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Gudang Asal</label>
                        <select
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            x-model="form.gudang_asal_id" @change="onSourceChange()">
                            <option value="">Pilih gudang asal</option>
                            <template x-for="g in warehouses" :key="g.id">
                                <option :value="g.id" x-text="g.nama"></option>
                            </template>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Gudang
                            Tujuan</label>
                        <select
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            x-model="form.gudang_tujuan_id">
                            <option value="">Pilih gudang tujuan</option>
                            <template x-for="g in warehouses" :key="g.id">
                                <option :value="g.id" x-text="g.nama"></option>
                            </template>
                        </select>
                    </div>
                    <div class="md:col-span-2 space-y-1.5">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Produk</label>
                        <select
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            x-model="form.id_produk">
                            <option value="">Pilih produk</option>
                            <template x-for="p in products" :key="p.id">
                                <option :value="p.id" x-text="p.nama + ' — ' + (p.nama_jenis || '-')"></option>
                            </template>
                        </select>
                    </div>
                    <div class="md:col-span-2 space-y-1.5">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Qty Transfer
                            (kg)</label>
                        <input type="number" min="0" step="0.01"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            x-model="form.qty" placeholder="0">
                    </div>
                </div>

                <div
                    class="border-t border-slate-200 dark:border-slate-700 px-4 py-3 sm:px-6 sm:py-4 flex gap-3 justify-end">
                    <button
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600"
                        @click="showCreateModal = false">Batal</button>
                    <button
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"
                        :disabled="saving" @click="save()"
                        x-text="saving ? 'Menyimpan...' : 'Simpan Transfer'"></button>
                </div>
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
                    <div><span style="color:var(--text-secondary)">Asal:</span> <strong
                            x-text="selectedTransfer.nama_gudang_asal || '-' "></strong></div>
                    <div><span style="color:var(--text-secondary)">Tujuan:</span> <strong
                            x-text="selectedTransfer.nama_gudang_tujuan || '-' "></strong></div>
                    <div><span style="color:var(--text-secondary)">Produk:</span> <strong
                            x-text="selectedTransfer.nama_produk || '-' "></strong></div>
                    <div><span style="color:var(--text-secondary)">Qty:</span> <strong
                            x-text="formatNumber(selectedTransfer.qty) + ' kg'"></strong></div>
                    <div><span style="color:var(--text-secondary)">Status:</span> <strong
                            x-text="selectedTransfer.status?.toUpperCase() || '-' "></strong></div>
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
            const res = await axios.get(`${window.API_BASE_URL}/settings/gudang`, { headers: { Authorization: 'Bearer ' + token } });
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
            const res = await axios.get(`${window.API_BASE_URL}/master/produk?id_gudang=` + this.selectedSourceId, { headers: { Authorization: 'Bearer ' + token } });
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
            const url = this.canSelectSource() && this.selectedSourceId ? `${window.API_BASE_URL}/stok-transfer?id_gudang=` + this.selectedSourceId : `${window.API_BASE_URL}/stok-transfer`;
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
                await axios.post(`${window.API_BASE_URL}/stok-transfer`, this.form, { headers: { Authorization: 'Bearer ' + token } });
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
                await axios.put(`${window.API_BASE_URL}/stok-transfer/` + id + '/status', { status }, { headers: { Authorization: 'Bearer ' + token } });
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