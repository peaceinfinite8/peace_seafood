<?php ?>
<div x-data="masterDataPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Master Data</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola supplier, pembeli, jenis ikan, dan produk</p>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <a href="${window.APP_BASE_URL}/master-data/supplier"
            class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-5 hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3 bg-blue-100 dark:bg-blue-900/30">
                <i data-lucide="truck" class="w-6 h-6 text-blue-600 dark:text-blue-400"></i>
            </div>
            <h3 class="font-semibold mb-1 text-slate-900 dark:text-slate-100">Supplier</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Kelola data supplier ikan</p>
            <p class="text-lg font-bold mt-2 text-blue-600 dark:text-blue-400" x-text="counts.supplier + ' data'"></p>
        </a>
        <a href="${window.APP_BASE_URL}/master-data/pembeli"
            class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-5 hover:shadow-md transition-shadow group">
            <div
                class="w-12 h-12 rounded-xl flex items-center justify-center mb-3 bg-emerald-100 dark:bg-emerald-900/30">
                <i data-lucide="users" class="w-6 h-6 text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <h3 class="font-semibold mb-1 text-slate-900 dark:text-slate-100">Pembeli</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Kelola data pelanggan</p>
            <p class="text-lg font-bold mt-2 text-emerald-600 dark:text-emerald-400" x-text="counts.pembeli + ' data'">
            </p>
        </a>
        <a href="${window.APP_BASE_URL}/master-data/jenis-ikan"
            class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-5 hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3 bg-cyan-100 dark:bg-cyan-900/30">
                <i data-lucide="fish" class="w-6 h-6 text-cyan-600 dark:text-cyan-400"></i>
            </div>
            <h3 class="font-semibold mb-1 text-slate-900 dark:text-slate-100">Jenis Ikan</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Kategori jenis ikan</p>
            <p class="text-lg font-bold mt-2 text-cyan-600 dark:text-cyan-400" x-text="counts.jenis + ' jenis'"></p>
        </a>
        <a href="${window.APP_BASE_URL}/master-data/produk"
            class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-5 hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3 bg-amber-100 dark:bg-amber-900/30">
                <i data-lucide="package" class="w-6 h-6 text-amber-600 dark:text-amber-400"></i>
            </div>
            <h3 class="font-semibold mb-1 text-slate-900 dark:text-slate-100">Produk</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Data produk & harga</p>
            <p class="text-lg font-bold mt-2 text-amber-600 dark:text-amber-400" x-text="counts.produk + ' produk'"></p>
        </a>
    </div>
</div>

<?php $scripts = <<<'JS'
<script>
function masterDataPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        counts: { supplier: 0, pembeli: 0, jenis: 0, produk: 0 },

        async init() {
            if (!['super_admin','admin'].includes(this.user.role)) {
                window.location.href = `${window.APP_BASE_URL}/dashboard`;
                return;
            }
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            await this.loadCounts();
        },

        async loadCounts() {
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const [supRes, pemRes, jenisRes, prodRes] = await Promise.all([
                    axios.get(`${window.API_BASE_URL}/master/supplier`, { headers }),
                    axios.get(`${window.API_BASE_URL}/master/pembeli`, { headers }),
                    axios.get(`${window.API_BASE_URL}/master/jenis-ikan`, { headers }),
                    axios.get(`${window.API_BASE_URL}/master/produk`, { headers }),
                ]);
                this.counts.supplier = (supRes.data?.data || []).length;
                this.counts.pembeli  = (pemRes.data?.data || []).length;
                this.counts.jenis    = (jenisRes.data?.data || []).length;
                this.counts.produk   = (prodRes.data?.data || []).length;
            } catch(e) { console.error(e); }
        }
    };
}
</script>
JS;
?>