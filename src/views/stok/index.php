<?php /** @var string $activeMenu */ ?>
<div x-data="stokPage()" x-init="init()">
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Stok & Inventory</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Kelola inventaris produk ikan</p>
        </div>
        <div class="flex gap-3">
            <a href="/peace_seafood/stok/timbangan" class="btn btn-secondary">
                <i data-lucide="scale" class="w-4 h-4"></i>
                Timbangan
                <span class="badge badge-warning" x-show="pendingCount > 0" x-text="pendingCount" x-cloak></span>
            </a>
            <a href="/peace_seafood/stok/masuk" class="btn btn-primary"
               x-show="user.role === 'admin' || user.role === 'bos'">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Input Stok
            </a>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="card p-4 mb-6">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-48">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" style="color: var(--text-secondary)"></i>
                <input type="text" x-model="search" placeholder="Cari produk..." 
                       class="form-input pl-10">
            </div>
            <select x-model="filterJenis" class="form-input w-auto">
                <option value="">Semua Jenis</option>
                <template x-for="j in jenisIkan" :key="j.id">
                    <option :value="j.id" x-text="j.nama"></option>
                </template>
            </select>
            <select x-model="filterStock" class="form-input w-auto">
                <option value="">Semua Stok</option>
                <option value="low">Stok Menipis</option>
                <option value="ok">Stok Aman</option>
            </select>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="flex justify-center py-20">
        <div class="animate-spin w-8 h-8 rounded-full border-4 border-blue-200" style="border-top-color: var(--color-primary)"></div>
    </div>

    <!-- Inventory Table -->
    <div class="card" x-show="!loading" x-cloak>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Jenis Ikan</th>
                        <th>Stok Qty</th>
                        <th>Stok Min</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Nilai Stok</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filteredItems.length === 0">
                        <tr>
                            <td colspan="8" class="text-center py-8" style="color: var(--text-secondary)">
                                Tidak ada data produk
                            </td>
                        </tr>
                    </template>
                    <template x-for="item in filteredItems" :key="item.id">
                        <tr>
                            <td>
                                <p class="font-medium text-sm" style="color: var(--text-primary)" x-text="item.nama"></p>
                            </td>
                            <td>
                                <span class="badge badge-info" x-text="item.nama_jenis"></span>
                            </td>
                            <td>
                                <span class="font-semibold" 
                                      :style="item.is_low_stock ? 'color: var(--color-danger)' : 'color: var(--color-success)'"
                                      x-text="parseFloat(item.stok_qty).toLocaleString('id-ID') + ' ' + (item.satuan || 'kg')"></span>
                            </td>
                            <td>
                                <span class="text-sm" style="color: var(--text-secondary)"
                                      x-text="parseFloat(item.stok_minimum || 0).toLocaleString('id-ID') + ' ' + (item.satuan || 'kg')"></span>
                            </td>
                            <td>
                                <span class="text-sm" x-text="'Rp ' + parseFloat(item.harga_beli || 0).toLocaleString('id-ID')"></span>
                            </td>
                            <td>
                                <span class="text-sm font-medium" style="color: var(--color-primary)"
                                      x-text="'Rp ' + parseFloat(item.harga_jual || 0).toLocaleString('id-ID')"></span>
                            </td>
                            <td>
                                <span class="text-sm font-semibold" x-text="'Rp ' + parseFloat(item.stok_value || 0).toLocaleString('id-ID')"></span>
                            </td>
                            <td>
                                <span class="badge"
                                      :class="item.is_low_stock ? 'badge-danger' : 'badge-success'"
                                      x-text="item.is_low_stock ? 'MENIPIS' : 'AMAN'"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        
        <!-- Summary -->
        <div class="p-4 border-t flex flex-wrap gap-6" style="border-color: var(--border-color)">
            <div>
                <span class="text-xs" style="color: var(--text-secondary)">Total Produk: </span>
                <span class="text-sm font-bold" x-text="filteredItems.length"></span>
            </div>
            <div>
                <span class="text-xs" style="color: var(--text-secondary)">Total Nilai Stok: </span>
                <span class="text-sm font-bold" style="color: var(--color-primary)"
                      x-text="'Rp ' + filteredItems.reduce((s,i) => s + parseFloat(i.stok_value||0), 0).toLocaleString('id-ID')"></span>
            </div>
            <div>
                <span class="text-xs" style="color: var(--color-danger)">Stok Menipis: </span>
                <span class="text-sm font-bold text-red-500" x-text="filteredItems.filter(i=>i.is_low_stock).length + ' produk'"></span>
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
                    axios.get('/peace_seafood/api/stok', { headers }),
                    axios.get('/peace_seafood/api/stok/pending-timbang', { headers }),
                    axios.get('/peace_seafood/api/master/jenis-ikan', { headers }),
                ]);
                this.items        = stokRes.value?.data?.data || stokRes.data?.data || [];
                this.pendingCount = pendingRes.value?.data?.data?.length || 0;
                this.jenisIkan    = jenisRes.value?.data?.data || jenisRes.data?.data || [];
            } catch(e) {
                if (e.response?.status === 401) { localStorage.clear(); window.location.href = '/peace_seafood/login'; }
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
