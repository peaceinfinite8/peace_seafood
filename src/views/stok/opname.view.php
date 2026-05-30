<?php

/** @var string $activeMenu */ ?>
<div x-data="stokOpnamePage()" x-init="init()">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Stok Opname</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Draft opname, finalisasi, dan penyesuaian stok fisik
            </p>
        </div>
        <button @click="openCreate()"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"
            x-show="['super_admin','bos','admin','checker'].includes(user.role)">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Buat Opname
        </button>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs uppercase" style="color:var(--text-secondary)">Total</p>
            <p class="text-2xl font-bold" x-text="filtered.length"></p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs uppercase" style="color:var(--text-secondary)">Draft</p>
            <p class="text-2xl font-bold text-yellow-500" x-text="filtered.filter(x => x.status === 'draft').length">
            </p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs uppercase" style="color:var(--text-secondary)">Final</p>
            <p class="text-2xl font-bold text-green-500" x-text="filtered.filter(x => x.status === 'final').length"></p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs uppercase" style="color:var(--text-secondary)">Gudang Aktif</p>
            <p class="text-2xl font-bold" x-text="selectedGudangName || '-' "></p>
        </div>
    </div>

    <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-center">
            <input type="text" x-model="search" placeholder="Cari opname / gudang / user..."
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white flex-1 min-w-56">
            <select x-model="filterStatus"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white w-auto">
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="final">Final</option>
            </select>
            <button
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600"
                @click="loadList()">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                Refresh
            </button>
        </div>
    </div>

    <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-slate-100 dark:bg-slate-700">
                    <tr>
                        <th
                            class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                            Tanggal</th>
                        <th
                            class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                            Gudang</th>
                        <th
                            class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                            Dibuat Oleh</th>
                        <th
                            class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                            Status</th>
                        <th
                            class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="5" class="text-center py-8" style="color:var(--text-secondary)">Tidak ada data
                            </td>
                        </tr>
                    </template>
                    <template x-for="item in filtered" :key="item.id">
                        <tr
                            class="border-b border-slate-200 hover:bg-slate-50 dark:border-slate-600 dark:hover:bg-slate-700/40">
                            <td class="px-4 py-2 text-sm" x-text="formatDate(item.tanggal_opname)"></td>
                            <td class="px-4 py-2 text-sm" x-text="item.nama_gudang || selectedGudangName || '-' "></td>
                            <td class="px-4 py-2 text-sm" x-text="item.nama_user || '-' "></td>
                            <td class="px-4 py-2 text-sm"><span
                                    class="inline-flex items-center rounded-full bg-slate-200 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200"
                                    :class="item.status === 'final' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-700/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-700/30 dark:text-amber-400'"
                                    x-text="item.status?.toUpperCase()"></span></td>
                            <td class="px-4 py-2 text-sm">
                                <div class="flex gap-2">
                                    <button
                                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600 p-1.5"
                                        @click="showDetail(item.id)"><i data-lucide="eye"
                                            class="w-3.5 h-3.5"></i></button>
                                    <button
                                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 dark:bg-emerald-700 p-1.5"
                                        x-show="item.status === 'draft' && ['super_admin','bos','admin'].includes(user.role)"
                                        @click="finalize(item.id)"><i data-lucide="check"
                                            class="w-3.5 h-3.5"></i></button>
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
                <h3 class="font-bold text-lg">Buat Stok Opname</h3>
                <button @click="showCreateModal = false" class="ml-auto"><i data-lucide="x"
                        class="w-5 h-5"></i></button>
            </div>

            <div class="px-4 py-4 sm:p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4" x-show="canSelectGudang()">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Gudang</label>
                        <select
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            x-model="form.id_gudang" @change="onGudangChange()">
                            <option value="">Pilih gudang</option>
                            <template x-for="g in warehouses" :key="g.id">
                                <option :value="g.id" x-text="g.nama"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-0">Item
                            Opname</label>
                        <button type="button"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600"
                            @click="addItem()">Tambah Baris</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(item, index) in form.items" :key="index">
                            <div class="grid grid-cols-12 gap-2 items-center">
                                <select
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white col-span-7"
                                    x-model="item.id_produk">
                                    <option value="">Pilih produk</option>
                                    <template x-for="p in products" :key="p.id">
                                        <option :value="p.id"
                                            x-text="p.nama + ' — ' + (p.nama_jenis || p.nama_jenis_ikan || '-')">
                                        </option>
                                    </template>
                                </select>
                                <input type="number" min="0" step="0.01"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white col-span-4"
                                    x-model="item.qty_fisik" placeholder="Qty fisik">
                                <button type="button"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 dark:bg-rose-700 p-2 col-span-1"
                                    @click="removeItem(index)"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </div>
                        </template>
                    </div>
                </div>

                <div
                    class="border-t border-slate-200 dark:border-slate-700 px-4 py-3 sm:px-6 sm:py-4 flex gap-3 justify-end">
                    <button
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600"
                        @click="showCreateModal = false">Batal</button>
                    <button
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"
                        :disabled="saving" @click="save()" x-text="saving ? 'Menyimpan...' : 'Simpan Opname'"></button>
                </div>
            </div>
        </div>

        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6" x-show="showDetailModal"
            x-cloak @click.self="showDetailModal = false">
            <div
                class="max-w-4xl w-full rounded-lg border border-slate-300 bg-white shadow-xl dark:border-slate-600 dark:bg-slate-800">
                <div
                    class="border-b border-slate-200 dark:border-slate-700 px-4 py-3 sm:flex sm:items-center sm:justify-between">
                    <h3 class="font-bold text-lg"
                        x-text="detail?.nama_gudang ? 'Detail Opname - ' + detail.nama_gudang : 'Detail Opname'"></h3>
                    <button @click="showDetailModal = false" class="ml-auto"><i data-lucide="x"
                            class="w-5 h-5"></i></button>
                </div>
                <div x-show="detail" class="space-y-4 px-4 py-4 sm:p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div><span style="color:var(--text-secondary)">Tanggal:</span> <strong
                                x-text="formatDate(detail?.tanggal_opname)"></strong></div>
                        <div><span style="color:var(--text-secondary)">Gudang:</span> <strong
                                x-text="detail?.nama_gudang || '-' "></strong></div>
                        <div><span style="color:var(--text-secondary)">User:</span> <strong
                                x-text="detail?.nama_user || '-' "></strong></div>
                        <div><span style="color:var(--text-secondary)">Status:</span> <strong
                                x-text="detail?.status?.toUpperCase() || '-' "></strong></div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead class="bg-slate-100 dark:bg-slate-700">
                                <tr>
                                    <th
                                        class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                                        Produk</th>
                                    <th
                                        class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                                        Jenis</th>
                                    <th
                                        class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                                        Qty Sistem</th>
                                    <th
                                        class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                                        Qty Fisik</th>
                                    <th
                                        class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                                        Selisih</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="item in (detail?.items || [])" :key="item.id">
                                    <tr
                                        class="border-b border-slate-200 hover:bg-slate-50 dark:border-slate-600 dark:hover:bg-slate-700/40">
                                        <td class="px-4 py-2 text-sm" x-text="item.nama_produk"></td>
                                        <td class="px-4 py-2 text-sm" x-text="item.nama_jenis"></td>
                                        <td class="px-4 py-2 text-sm" x-text="formatNumber(item.qty_sistem)"></td>
                                        <td class="px-4 py-2 text-sm" x-text="formatNumber(item.qty_fisik)"></td>
                                        <td class="px-4 py-2 text-sm"
                                            :class="parseFloat(item.qty_fisik || 0) - parseFloat(item.qty_sistem || 0) < 0 ? 'text-red-500' : 'text-green-500'"
                                            x-text="formatNumber(parseFloat(item.qty_fisik || 0) - parseFloat(item.qty_sistem || 0))">
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
            const res = await axios.get(`${window.API_BASE_URL}/settings/gudang`, { headers: { Authorization: 'Bearer ' + token } });
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
            const res = await axios.get(`${window.API_BASE_URL}/master/produk?id_gudang=` + this.selectedGudangId, { headers: { Authorization: 'Bearer ' + token } });
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
            const url = this.canSelectGudang() && this.selectedGudangId ? `${window.API_BASE_URL}/stok-opname?id_gudang=` + this.selectedGudangId : `${window.API_BASE_URL}/stok-opname`;
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
                await axios.post(`${window.API_BASE_URL}/stok-opname`, {
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
            const res = await axios.get(`${window.API_BASE_URL}/stok-opname/` + id, { headers: { Authorization: 'Bearer ' + token } });
            this.detail = res.data?.data || null;
            this.showDetailModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async finalize(id) {
            if (!await confirm('Finalisasi opname ini? Stok produk akan disesuaikan.')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.post(`${window.API_BASE_URL}/stok-opname/` + id + '/finalize', {}, { headers: { Authorization: 'Bearer ' + token } });
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