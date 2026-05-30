<?php ?>
<div x-data="stokHistory()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">History Stok</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Riwayat seluruh pergerakan stok masuk & timbangan
            </p>
        </div>
        <a href="${window.APP_BASE_URL}/stok"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Stok
        </a>
    </div>

    <!-- Filter -->
    <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-4 mb-6">
        <div class="flex flex-wrap gap-3">
            <input type="text" x-model="search" placeholder="Cari produk / supplier..."
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white flex-1 min-w-48">
            <input type="date" x-model="filterDari"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white w-auto">
            <input type="date" x-model="filterSampai"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white w-auto">
            <select x-model="filterStatus"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white w-auto">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
            </select>
            <button @click="loadData()"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700">
                <i data-lucide="search" class="w-4 h-4"></i> Cari
            </button>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6" x-show="!loading" x-cloak>
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Total Masuk</p>
            <p class="text-2xl font-bold" x-text="list.length"></p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Total Qty Masuk</p>
            <p class="text-lg font-bold" style="color: var(--color-primary)"
                x-text="formatKg(list.filter(i=>i.status==='confirmed').reduce((s,i)=>s+parseFloat(i.qty_actual||i.qty||0),0), 1)">
            </p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Pending</p>
            <p class="text-2xl font-bold text-yellow-500" x-text="list.filter(i=>i.status==='pending').length"></p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Confirmed</p>
            <p class="text-2xl font-bold text-green-500" x-text="list.filter(i=>i.status==='confirmed').length"></p>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading"
        class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-12 text-center"
        style="color: var(--text-secondary)">
        <p class="text-sm">Memuat history...</p>
    </div>

    <!-- Table -->
    <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800" x-show="!loading"
        x-cloak>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-slate-100 dark:bg-slate-700">
                    <tr>
                        <th
                            class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                            Tanggal</th>
                        <th
                            class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                            Produk</th>
                        <th
                            class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                            Supplier</th>
                        <th
                            class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                            Qty Input</th>
                        <th
                            class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                            Qty Aktual</th>
                        <th
                            class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                            Susut</th>
                        <th
                            class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                            Harga Beli</th>
                        <th
                            class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                            Total</th>
                        <th
                            class="border-b border-slate-200 dark:border-slate-600 px-4 py-2 text-left text-sm font-semibold text-slate-900 dark:text-white">
                            Penanggung Jawab</th>
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
                            <td colspan="11" class="text-center py-10 px-4" style="color:var(--text-secondary)">
                                Tidak ada history stok
                            </td>
                        </tr>
                    </template>
                    <template x-for="item in filtered" :key="item.id">
                        <tr
                            class="border-b border-slate-200 hover:bg-slate-50 dark:border-slate-600 dark:hover:bg-slate-700/40">
                            <td class="px-4 py-2"><span class="text-sm"
                                    x-text="formatDate(item.tanggal_masuk || item.created_at)"></span></td>
                            <td class="px-4 py-2"><span class="font-medium text-sm"
                                    x-text="item.nama_produk || '-'"></span></td>
                            <td class="px-4 py-2"><span class="text-sm" x-text="item.nama_supplier || '-'"></span></td>
                            <td class="px-4 py-2">
                                <span class="text-sm" x-text="formatKg(item.qty||0, 1)"></span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="text-sm font-semibold"
                                    :style="item.qty_actual ? 'color:var(--color-success)' : 'color:var(--text-secondary)'"
                                    x-text="item.qty_actual ? formatKg(item.qty_actual,1) : '-'"></span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="text-sm" :class="item.susut > 0 ? 'text-red-500' : 'text-gray-400'"
                                    x-text="item.susut ? formatKg(item.susut,1) : '-'"></span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="text-sm"
                                    x-text="'Rp '+parseFloat(item.harga_beli||0).toLocaleString('id-ID')"></span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="text-sm font-semibold" style="color:var(--color-primary)"
                                    x-text="'Rp '+(parseFloat(item.qty_actual||item.qty||0)*parseFloat(item.harga_beli||0)).toLocaleString('id-ID')"></span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="text-sm" x-text="item.nama_user || '-' "></span>
                            </td>
                            <td class="px-4 py-2">
                                <span
                                    class="inline-flex items-center rounded-full bg-slate-200 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200"
                                    :class="item.status==='confirmed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-700/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-700/30 dark:text-amber-400'"
                                    x-text="item.status?.toUpperCase()"></span>
                            </td>
                            <td class="px-4 py-2">
                                <button @click="openDetail(item)"
                                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600 p-1.5"
                                    title="Detail">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <!-- Pagination Info -->
        <div class="px-4 py-3 border-t text-sm" style="border-color:var(--border-color); color:var(--text-secondary)">
            Menampilkan <strong x-text="filtered.length"></strong> dari <strong x-text="list.length"></strong> data
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal-overlay" x-show="showModal" @click.self="showModal = false" x-cloak>
        <div class="modal-box max-w-lg">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg">Detail Stok Masuk</h3>
                <button @click="showModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <template x-if="selected">
                <div class="space-y-3 text-sm">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Produk</p>
                            <p class="font-semibold" x-text="selected.nama_produk || '-'"></p>
                        </div>
                        <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Supplier</p>
                            <p class="font-semibold" x-text="selected.nama_supplier || '-'"></p>
                        </div>
                        <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Qty Input</p>
                            <p class="font-semibold" x-text="formatKg(selected.qty||0,1)"></p>
                        </div>
                        <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Qty Aktual (Timbang)</p>
                            <p class="font-semibold text-green-600"
                                x-text="selected.qty_actual ? formatKg(selected.qty_actual,1) : 'Belum ditimbang'"></p>
                        </div>
                        <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Susut</p>
                            <p class="font-semibold" :class="selected.susut > 0 ? 'text-red-500' : ''"
                                x-text="selected.susut ? formatKg(selected.susut,1) : formatKg(0,1)"></p>
                        </div>
                        <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Harga Beli</p>
                            <p class="font-semibold"
                                x-text="'Rp '+parseFloat(selected.harga_beli||0).toLocaleString('id-ID')"></p>
                        </div>
                        <div class="p-3 rounded-lg col-span-2" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Total Nilai</p>
                            <p class="font-bold text-lg" style="color:var(--color-primary)"
                                x-text="'Rp '+(parseFloat(selected.qty_actual||selected.qty||0)*parseFloat(selected.harga_beli||0)).toLocaleString('id-ID')">
                            </p>
                        </div>
                    </div>
                    <div x-show="selected.alasan_susut" class="p-3 rounded-lg" style="background:var(--bg-gray)">
                        <p class="text-xs mb-1" style="color:var(--text-secondary)">Alasan Susut</p>
                        <p x-text="selected.alasan_susut"></p>
                    </div>
                    <div x-show="selected.catatan" class="p-3 rounded-lg" style="background:var(--bg-gray)">
                        <p class="text-xs mb-1" style="color:var(--text-secondary)">Catatan</p>
                        <p x-text="selected.catatan"></p>
                    </div>
                    <div class="p-3 rounded-lg flex items-center justify-between" style="background:var(--bg-gray)">
                        <div>
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Status</p>
                            <span class="badge" :class="selected.status==='confirmed'?'badge-success':'badge-warning'"
                                x-text="selected.status?.toUpperCase()"></span>
                        </div>
                        <div class="text-right">
                            <p class="text-xs" style="color:var(--text-secondary)"
                                x-text="formatDate(selected.tanggal_masuk||selected.created_at)"></p>
                        </div>
                    </div>
                    <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                        <p class="text-xs mb-1" style="color:var(--text-secondary)">Penanggung Jawab</p>
                        <p class="font-semibold" x-text="selected.nama_user || '-' "></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

<?php $scripts = <<<'JS'
<script>
function stokHistory() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        loading: true,
        list: [],
        search: '',
        filterDari: '',
        filterSampai: '',
        filterStatus: '',
        showModal: false,
        selected: null,

        get filtered() {
            const q = this.search.toLowerCase();
            return this.list.filter(i =>
                (!q || (i.nama_produk||'').toLowerCase().includes(q) ||
                       (i.nama_supplier||'').toLowerCase().includes(q)) &&
                (!this.filterStatus || i.status === this.filterStatus)
            );
        },

        async init() {
            if (!['super_admin', 'bos', 'admin'].includes(this.user.role)) {
                window.location.href = `${window.APP_BASE_URL}/dashboard`;
                return;
            }
            await this.loadData();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadData() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                let url = `${window.API_BASE_URL}/stok/history?per_page=200`;
                if (this.filterDari)   url += '&dari='    + this.filterDari;
                if (this.filterSampai) url += '&sampai='  + this.filterSampai;
                const res = await axios.get(url, { headers: { Authorization: 'Bearer '+token } });
                this.list = res.data?.data || [];
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal memuat history', position: 'topRight' }); }
            this.loading = false;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        openDetail(item) {
            this.selected = item;
            this.showModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        formatDate(d) { return d ? new Date(d).toLocaleDateString('id-ID') : '-'; }
    };
}
</script>
JS;
?>