<?php ?>
<div x-data="masterDataPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Master Data</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Kelola supplier, pembeli, jenis ikan, dan produk</p>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <a href="/peace_seafood/master-data/supplier" class="card p-5 hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3"
                style="background: rgba(37,99,235,0.1)">
                <i data-lucide="truck" class="w-6 h-6" style="color: var(--color-primary)"></i>
            </div>
            <h3 class="font-semibold mb-1" style="color: var(--text-primary)">Supplier</h3>
            <p class="text-xs" style="color: var(--text-secondary)">Kelola data supplier ikan</p>
            <p class="text-lg font-bold mt-2" style="color: var(--color-primary)" x-text="counts.supplier + ' data'">
            </p>
        </a>
        <a href="/peace_seafood/master-data/pembeli" class="card p-5 hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3"
                style="background: rgba(16,185,129,0.1)">
                <i data-lucide="users" class="w-6 h-6" style="color: var(--color-success)"></i>
            </div>
            <h3 class="font-semibold mb-1" style="color: var(--text-primary)">Pembeli</h3>
            <p class="text-xs" style="color: var(--text-secondary)">Kelola data pelanggan</p>
            <p class="text-lg font-bold mt-2" style="color: var(--color-success)" x-text="counts.pembeli + ' data'"></p>
        </a>
        <a href="/peace_seafood/master-data/jenis-ikan" class="card p-5 hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3"
                style="background: rgba(6,182,212,0.1)">
                <i data-lucide="fish" class="w-6 h-6" style="color: var(--color-info)"></i>
            </div>
            <h3 class="font-semibold mb-1" style="color: var(--text-primary)">Jenis Ikan</h3>
            <p class="text-xs" style="color: var(--text-secondary)">Kategori jenis ikan</p>
            <p class="text-lg font-bold mt-2" style="color: var(--color-info)" x-text="counts.jenis + ' jenis'"></p>
        </a>
        <a href="/peace_seafood/master-data/produk" class="card p-5 hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3"
                style="background: rgba(245,158,11,0.1)">
                <i data-lucide="package" class="w-6 h-6" style="color: var(--color-warning)"></i>
            </div>
            <h3 class="font-semibold mb-1" style="color: var(--text-primary)">Produk</h3>
            <p class="text-xs" style="color: var(--text-secondary)">Data produk & harga</p>
            <p class="text-lg font-bold mt-2" style="color: var(--color-warning)" x-text="counts.produk + ' produk'">
            </p>
        </a>
    </div>
</div>

<?php $scripts = '<script src="/peace_seafood/inline-assets/js/master-data/index.js"></script>'; ?>