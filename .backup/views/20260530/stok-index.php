<?php

/** @var string $activeMenu */ ?>
<div x-data="stokPage()" x-init="init()">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Stok & Inventory</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">Kelola inventaris produk ikan</p>
        </div>
        <div class="flex gap-3">
            <a href="${window.APP_BASE_URL}/stok/timbangan"
                class="inline-flex items-center gap-2 rounded-lg bg-slate-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-slate-700">
                <i data-lucide="scale" class="w-4 h-4"></i>
                Timbangan
                <span
                    class="inline-flex items-center rounded-full bg-amber-500 px-2 py-0.5 text-xs font-bold text-white"
                    x-show="pendingCount > 0" x-text="pendingCount" x-cloak></span>
            </a>
            <a href="${window.APP_BASE_URL}/stok/masuk"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"
                x-show="user.role === 'admin' || user.role === 'super_admin'">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Input Stok
            </a>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="mb-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-48">
                <i data-lucide="search"
                    class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400"></i>
                <input type="text" x-model="search" placeholder="Cari produk..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 pl-10 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500">
            </div>
            <select x-model="filterJenis"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                <option value="">Semua Jenis</option>
                <template x-for="j in jenisIkan" :key="j.id">
                    <option :value="j.id" x-text="j.nama"></option>
                </template>
            </select>
            <select x-model="filterStock"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                <option value="">Semua Stok</option>
                <option value="low">Stok Menipis</option>
                <option value="ok">Stok Aman</option>
            </select>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="flex justify-center py-20">
        <div
            class="animate-spin w-8 h-8 rounded-full border-4 border-slate-200 border-t-blue-600 dark:border-slate-700 dark:border-t-blue-500">
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900"
        x-show="!loading" x-cloak>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-900 dark:text-white">Produk</th>
                        <th class="px-4 py-3 font-semibold text-slate-900 dark:text-white">Jenis Ikan</th>
                        <th class="px-4 py-3 font-semibold text-slate-900 dark:text-white">Stok Qty</th>
                        <th class="px-4 py-3 font-semibold text-slate-900 dark:text-white">Stok Min</th>
                        <th class="px-4 py-3 font-semibold text-slate-900 dark:text-white">Harga Beli</th>
                        <th class="px-4 py-3 font-semibold text-slate-900 dark:text-white">Harga Jual</th>
                        <th class="px-4 py-3 font-semibold text-slate-900 dark:text-white">Nilai Stok</th>
                        <th class="px-4 py-3 font-semibold text-slate-900 dark:text-white">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <template x-if="filteredItems.length === 0">
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                Tidak ada data produk
                            </td>
                        </tr>
                    </template>
                    <template x-for="item in filteredItems" :key="item.id">
                        <tr
                            class="border-b border-slate-200 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800/50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-sm text-slate-900 dark:text-white" x-text="item.nama"></p>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400"
                                    x-text="item.nama_jenis"></span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-semibold"
                                    :class="item.is_low_stock ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'"
                                    x-text="formatWeight(item.stok_qty, item.satuan)"></span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-slate-600 dark:text-slate-400"
                                    x-text="formatWeight(item.stok_minimum || 0, item.satuan)"></span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-slate-900 dark:text-white"
                                    x-text="'Rp ' + parseFloat(item.harga_beli || 0).toLocaleString('id-ID')"></span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-blue-600 dark:text-blue-400"
                                    x-text="'Rp ' + parseFloat(item.harga_jual || 0).toLocaleString('id-ID')"></span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white"
                                    x-text="'Rp ' + parseFloat(item.stok_value || 0).toLocaleString('id-ID')"></span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold"
                                    :class="item.is_low_stock ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'"
                                    x-text="item.is_low_stock ? 'MENIPIS' : 'AMAN'"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="flex flex-wrap gap-6 border-t border-slate-200 px-4 py-4 dark:border-slate-700">
            <div>
                <span class="text-xs text-slate-600 dark:text-slate-400">Total Produk: </span>
                <span class="text-sm font-bold text-slate-900 dark:text-white" x-text="filteredItems.length"></span>
            </div>
            <div>
                <span class="text-xs text-slate-600 dark:text-slate-400">Total Nilai Stok: </span>
                <span class="text-sm font-bold text-blue-600 dark:text-blue-400"
                    x-text="'Rp ' + filteredItems.reduce((s,i) => s + parseFloat(i.stok_value||0), 0).toLocaleString('id-ID')"></span>
            </div>
            <div>
                <span class="text-xs text-rose-600 dark:text-rose-400">Stok Menipis: </span>
                <span class="text-sm font-bold text-rose-600 dark:text-rose-400"
                    x-text="filteredItems.filter(i=>i.is_low_stock).length + ' produk'"></span>
            </div>
        </div>
    </div>
</div>

<?php $scripts = <<<'JS'
<script>
function stokPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        loading: true,
        items: [],
        jenisIkan: [],
        search: '',
        filterJenis: '',
        filterStock: '',
        pendingCount: 0,

        formatWeight(qty, satuan = 'kg') {
            let q = parseFloat(qty || 0);
            if (!satuan || satuan.toLowerCase() === 'kg') {
                if (q >= 10000) {
                    return (q / 1000).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' ton';
                } else if (q >= 100) {
                    return (q / 100).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' kintal';
                } else {
                    // use global helper for consistent kg formatting
                    return formatKg(q, 2);
                }
            }
            return q.toLocaleString('id-ID') + ' ' + satuan;
        },


        get filteredItems() {
            return this.items.filter(i => {
                const q = this.search.toLowerCase();
                const matchSearch = !q || i.nama?.toLowerCase().includes(q) || i.nama_jenis?.toLowerCase().includes(q);
                const matchJenis  = !this.filterJenis || i.id_jenis_ikan == this.filterJenis;
                const matchStock  = !this.filterStock || 
                    (this.filterStock === 'low' && i.is_low_stock) ||
                    (this.filterStock === 'ok'  && !i.is_low_stock);
                return matchSearch && matchJenis && matchStock;
            });
        },

        async init() {
            await this.loadData();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadData() {
            this.loading = true;
            try {
                const token   = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const [stokRes, pendingRes, jenisRes] = await Promise.all([
                    axios.get(`${window.API_BASE_URL}/stok`, { headers }),
                    axios.get(`${window.API_BASE_URL}/stok/pending-timbang`, { headers }),
                    axios.get(`${window.API_BASE_URL}/master/jenis-ikan`, { headers }),
                ]);
                this.items        = stokRes.value?.data?.data || stokRes.data?.data || [];
                this.pendingCount = pendingRes.value?.data?.data?.length || pendingRes.data?.data?.length || 0;
                this.jenisIkan    = jenisRes.value?.data?.data || jenisRes.data?.data || [];
            } catch(e) {
                if (e.response?.status === 401) { localStorage.clear(); window.location.href = `${window.APP_BASE_URL}/login`; }
                console.error(e);
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
JS;
?>