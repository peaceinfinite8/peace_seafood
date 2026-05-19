<?php ?>
<div x-data="stokHistory()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">History Stok</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Riwayat seluruh pergerakan stok masuk & timbangan</p>
        </div>
        <a href="/peace_seafood/stok" class="btn btn-secondary">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Stok
        </a>
    </div>

    <!-- Filter -->
    <div class="card p-4 mb-6">
        <div class="flex flex-wrap gap-3">
            <input type="text" x-model="search" placeholder="Cari produk / supplier..."
                   class="form-input flex-1 min-w-48">
            <input type="date" x-model="filterDari" class="form-input w-auto">
            <input type="date" x-model="filterSampai" class="form-input w-auto">
            <select x-model="filterStatus" class="form-input w-auto">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
            </select>
            <button @click="loadData()" class="btn btn-primary">
                <i data-lucide="search" class="w-4 h-4"></i> Cari
            </button>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6" x-show="!loading" x-cloak>
        <div class="stat-card">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Total Masuk</p>
            <p class="text-2xl font-bold" x-text="list.length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Total Qty Masuk</p>
            <p class="text-lg font-bold" style="color: var(--color-primary)"
               x-text="list.filter(i=>i.status==='confirmed').reduce((s,i)=>s+parseFloat(i.qty_actual||i.qty||0),0).toFixed(1) + ' kg'"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Pending</p>
            <p class="text-2xl font-bold text-yellow-500"
               x-text="list.filter(i=>i.status==='pending').length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Confirmed</p>
            <p class="text-2xl font-bold text-green-500"
               x-text="list.filter(i=>i.status==='confirmed').length"></p>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="card p-12 text-center" style="color: var(--text-secondary)">
        <p class="text-sm">Memuat history...</p>
    </div>

    <!-- Table -->
    <div class="card" x-show="!loading" x-cloak>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Supplier</th>
                        <th>Qty Input</th>
                        <th>Qty Aktual</th>
                        <th>Susut</th>
                        <th>Harga Beli</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filtered.length === 0">
                        <tr><td colspan="10" class="text-center py-10" style="color:var(--text-secondary)">
                            Tidak ada history stok
                        </td></tr>
                    </template>
                    <template x-for="item in filtered" :key="item.id">
                        <tr>
                            <td><span class="text-sm" x-text="formatDate(item.tanggal_masuk || item.created_at)"></span></td>
                            <td><span class="font-medium text-sm" x-text="item.nama_produk || '-'"></span></td>
                            <td><span class="text-sm" x-text="item.nama_supplier || '-'"></span></td>
                            <td>
                                <span class="text-sm" x-text="parseFloat(item.qty||0).toFixed(1) + ' kg'"></span>
                            </td>
                            <td>
                                <span class="text-sm font-semibold"
                                      :style="item.qty_actual ? 'color:var(--color-success)' : 'color:var(--text-secondary)'"
                                      x-text="item.qty_actual ? parseFloat(item.qty_actual).toFixed(1)+' kg' : '-'"></span>
                            </td>
                            <td>
                                <span class="text-sm"
                                      :class="item.susut > 0 ? 'text-red-500' : 'text-gray-400'"
                                      x-text="item.susut ? parseFloat(item.susut).toFixed(1)+' kg' : '-'"></span>
                            </td>
                            <td>
                                <span class="text-sm" x-text="'Rp '+parseFloat(item.harga_beli||0).toLocaleString('id-ID')"></span>
                            </td>
                            <td>
                                <span class="text-sm font-semibold" style="color:var(--color-primary)"
                                      x-text="'Rp '+(parseFloat(item.qty_actual||item.qty||0)*parseFloat(item.harga_beli||0)).toLocaleString('id-ID')"></span>
                            </td>
                            <td>
                                <span class="badge"
                                      :class="item.status==='confirmed' ? 'badge-success' : 'badge-warning'"
                                      x-text="item.status?.toUpperCase()"></span>
                            </td>
                            <td>
                                <button @click="openDetail(item)" class="btn btn-secondary p-1.5" title="Detail">
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
                            <p class="font-semibold" x-text="parseFloat(selected.qty||0).toFixed(1) + ' kg'"></p>
                        </div>
                        <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Qty Aktual (Timbang)</p>
                            <p class="font-semibold text-green-600" x-text="selected.qty_actual ? parseFloat(selected.qty_actual).toFixed(1)+' kg' : 'Belum ditimbang'"></p>
                        </div>
                        <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Susut</p>
                            <p class="font-semibold" :class="selected.susut > 0 ? 'text-red-500' : ''"
                               x-text="selected.susut ? parseFloat(selected.susut).toFixed(1)+' kg' : '0 kg'"></p>
                        </div>
                        <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Harga Beli</p>
                            <p class="font-semibold" x-text="'Rp '+parseFloat(selected.harga_beli||0).toLocaleString('id-ID')"></p>
                        </div>
                        <div class="p-3 rounded-lg col-span-2" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Total Nilai</p>
                            <p class="font-bold text-lg" style="color:var(--color-primary)"
                               x-text="'Rp '+(parseFloat(selected.qty_actual||selected.qty||0)*parseFloat(selected.harga_beli||0)).toLocaleString('id-ID')"></p>
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
                            <p class="text-xs" style="color:var(--text-secondary)" x-text="formatDate(selected.tanggal_masuk||selected.created_at)"></p>
                        </div>
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
            await this.loadData();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadData() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                let url = '/peace_seafood/api/stok/history?per_page=200';
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
