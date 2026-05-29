<?php ?>
<div x-data="stokMasukPage()" x-init="init()">

    <div class="flex items-center gap-4 mb-6">
        <a href="/peace_seafood/stok" class="btn btn-secondary p-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Input Stok Masuk</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Catat penerimaan stok dari supplier</p>
        </div>
    </div>

    <div class="card p-6 max-w-2xl">
        <form @submit.prevent="submit()" novalidate>

            <!-- Penanggung Jawab -->
            <div class="form-group">
                <label class="form-label">Penanggung Jawab</label>
                <input type="text" class="form-input" :value="user.name || '-'" readonly>
                <p class="text-xs mt-1" style="color: var(--text-secondary)">Akan tersimpan sebagai petugas penerima stok dari supplier ke gudang.</p>
            </div>

            <!-- Supplier -->
            <div class="form-group relative animate-fade-in" x-data="{ open: false }" @click.outside="open = false">
                <label class="form-label">Supplier <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" 
                           x-model="supplierQuery" 
                           @focus="open = true"
                           @input="onSupplierInput()"
                           class="form-input pr-10" 
                           placeholder="Ketik nama supplier baru atau pilih dari daftar..." 
                           required>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none opacity-60">
                        <i data-lucide="chevrons-up-down" class="w-4 h-4" style="color: var(--text-secondary)"></i>
                    </div>
                </div>
                
                <!-- Dropdown Menu -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                    class="absolute z-50 mt-1 w-full rounded-lg shadow-xl border overflow-hidden" 
                     style="background: var(--bg-light, #ffffff); border-color: var(--border-color, #e2e8f0); max-height: 250px; overflow-y: auto;"
                     x-cloak>
                    <div class="p-1.5 flex flex-col gap-1">
                        <!-- If query is not empty and no exact match exists, show dynamic insert option -->
                        <template x-if="supplierQuery && !isExactSupplierMatch()">
                            <button type="button" 
                                    @click="selectNewSupplier(supplierQuery)" 
                                    class="w-full text-left px-3 py-2.5 text-xs font-semibold rounded-md flex items-center gap-2 transition duration-150"
                                    style="color: var(--color-primary, #3b82f6); background: var(--color-primary-light, rgba(59, 130, 246, 0.1));">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                <span>Gunakan &quot;<span x-text="supplierQuery"></span>&quot; (Buat Supplier Baru)</span>
                            </button>
                        </template>

                        <!-- List of matching suppliers -->
                        <template x-for="s in filteredSuppliers()" :key="s.id">
                            <button type="button" 
                                    @click="selectSupplier(s)" 
                                    class="w-full text-left px-3 py-2 text-sm rounded-md flex justify-between items-center transition duration-150 hover:bg-slate-100 dark:hover:bg-slate-700/40"
                                    style="color: var(--text-primary)">
                                <span x-text="s.nama" class="font-medium"></span>
                                <span class="text-xs opacity-60" x-text="s.kota || 'Supplier'"></span>
                            </button>
                        </template>

                        <!-- Empty state -->
                        <template x-if="filteredSuppliers().length === 0 && !supplierQuery">
                            <div class="px-3 py-3 text-sm italic text-center opacity-60">
                                Tidak ada supplier terdaftar
                            </div>
                        </template>
                    </div>
                </div>
                
                <p class="text-red-500 text-xs mt-1" x-show="errors.id_supplier" x-text="errors.id_supplier" x-cloak></p>
            </div>

            <!-- Produk -->
            <div class="form-group">
                <label class="form-label">Produk <span class="text-red-500">*</span></label>
                <select x-model="form.id_produk" class="form-input" required @change="setProdukInfo()">
                    <option value="">-- Pilih Produk --</option>
                    <template x-for="p in produk" :key="p.id">
                        <option :value="p.id" x-text="p.nama + ' (' + p.nama_jenis + ')'"></option>
                    </template>
                </select>
                <p class="text-red-500 text-xs mt-1" x-show="errors.id_produk" x-text="errors.id_produk" x-cloak></p>
            </div>

            <!-- Qty & Harga -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Qty / Jumlah Stok Masuk <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <span class="text-[10px] mb-1 block text-center" style="color: var(--text-secondary)">Ton</span>
                            <input type="number" x-model="form.qty_ton" class="form-input text-center"
                                placeholder="0" min="0" step="1"
                                @input="updateQtyFromSplit()">
                        </div>
                        <div>
                            <span class="text-[10px] mb-1 block text-center" style="color: var(--text-secondary)">Kintal</span>
                            <input type="number" x-model="form.qty_kuintal" class="form-input text-center"
                                placeholder="0" min="0" step="1"
                                @input="updateQtyFromSplit()">
                        </div>
                        <div>
                            <span class="text-[10px] mb-1 block text-center" style="color: var(--text-secondary)">Kg</span>
                            <input type="number" x-model="form.qty_kg" class="form-input text-center"
                                placeholder="0" min="0" step="0.01"
                                @input="updateQtyFromSplit()">
                        </div>
                    </div>
                    <!-- Real-time total helper -->
                    <div class="text-xs mt-1.5 font-semibold" style="color: var(--color-primary)" x-show="parseFloat(form.qty) > 0">
                        Total Konversi: <span x-text="formatQty(form.qty || 0)"></span>
                    </div>
                    <p class="text-red-500 text-xs mt-1" x-show="errors.qty" x-text="errors.qty" x-cloak></p>
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Beli / kg <span class="text-red-500">*</span></label>
                    <div style="height: 19px" class="hidden md:block"></div>
                    <input type="number" x-model="form.harga_beli" class="form-input"
                        placeholder="0" min="0" step="100"
                        @input="calcTotal()" required>
                    <p class="text-red-500 text-xs mt-1" x-show="errors.harga_beli" x-text="errors.harga_beli" x-cloak></p>
                </div>
            </div>

            <!-- Auto-calculated Total -->
            <div class="mb-4 p-4 rounded-lg" style="background: var(--color-primary-light)">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-semibold" style="color: var(--color-primary)">Total</span>
                    <span class="text-lg font-bold" style="color: var(--color-primary)"
                        x-text="'Rp ' + (parseFloat(form.qty||0) * parseFloat(form.harga_beli||0)).toLocaleString('id-ID')"></span>
                </div>
            </div>

            <!-- Catatan -->
            <div class="form-group">
                <label class="form-label">Catatan (opsional)</label>
                <textarea x-model="form.catatan" class="form-input" rows="3"
                    placeholder="Catatan tambahan..."></textarea>
            </div>

            <!-- Submit -->
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary" :disabled="saving">
                    <template x-if="!saving">
                        <span style="display:flex;align-items:center;gap:0.5rem">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Simpan Stok Masuk
                        </span>
                    </template>
                    <template x-if="saving"><span>Menyimpan...</span></template>
                </button>
                <a href="/peace_seafood/stok" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php $scripts = <<<'JS'
<script>
function stokMasukPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        form: { id_supplier: '', id_produk: '', qty: '', qty_ton: '', qty_kuintal: '', qty_kg: '', harga_beli: '', catatan: '' },
        errors: {},
        saving: false,
        suppliers: [],
        produk: [],
        supplierQuery: '',

        async init() {
            if (!['super_admin', 'admin'].includes(this.user.role)) {
                window.location.href = '/peace_seafood/dashboard';
                return;
            }
            await this.loadMasterData();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadMasterData() {
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const [supRes, prodRes] = await Promise.all([
                    axios.get('/peace_seafood/api/master/supplier', { headers }),
                    axios.get('/peace_seafood/api/master/produk', { headers }),
                ]);
                this.suppliers = supRes.data?.data || [];
                this.produk    = prodRes.data?.data || [];
            } catch(e) { console.error(e); }
        },

        filteredSuppliers() {
            if (!this.supplierQuery) return this.suppliers;
            const q = this.supplierQuery.toLowerCase();
            return this.suppliers.filter(s => s.nama.toLowerCase().includes(q));
        },

        isExactSupplierMatch() {
            if (!this.supplierQuery) return false;
            const q = this.supplierQuery.trim().toLowerCase();
            return this.suppliers.some(s => s.nama.toLowerCase() === q);
        },

        selectSupplier(s) {
            this.form.id_supplier = s.id;
            this.supplierQuery = s.nama;
            this.errors.id_supplier = '';
        },

        selectNewSupplier(query) {
            this.form.id_supplier = query.trim();
            this.supplierQuery = query.trim();
            this.errors.id_supplier = '';
        },

        onSupplierInput() {
            const trimmed = this.supplierQuery.trim();
            if (!trimmed) {
                this.form.id_supplier = '';
                return;
            }
            const exact = this.suppliers.find(s => s.nama.toLowerCase() === trimmed.toLowerCase());
            if (exact) {
                this.form.id_supplier = exact.id;
            } else {
                this.form.id_supplier = trimmed;
            }
        },

        setProdukInfo() {
            const p = this.produk.find(x => x.id == this.form.id_produk);
            if (p) this.form.harga_beli = p.harga_beli || '';
        },

        updateQtyFromSplit() {
            const ton = parseFloat(this.form.qty_ton) || 0;
            const kuintal = parseFloat(this.form.qty_kuintal) || 0;
            const kg = parseFloat(this.form.qty_kg) || 0;
            this.form.qty = (ton * 1000) + (kuintal * 100) + kg;
            this.calcTotal();
        },

        formatQty(qty) {
            let q = parseFloat(qty) || 0;
            if (q >= 10000) {
                return (q / 1000).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' ton';
            } else if (q >= 100) {
                return (q / 100).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' kintal';
            } else {
                return q.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' kg';
            }
        },

        calcTotal() {
            // Re-evaluates totals reactively
        },

        validate() {
            this.errors = {};
            if (!this.form.id_supplier) this.errors.id_supplier = 'Supplier wajib dipilih atau diisi';
            if (!this.form.id_produk)   this.errors.id_produk   = 'Produk wajib dipilih';
            if (!this.form.qty || parseFloat(this.form.qty) <= 0) this.errors.qty = 'Qty harus lebih dari 0';
            if (!this.form.harga_beli || parseFloat(this.form.harga_beli) <= 0) this.errors.harga_beli = 'Harga beli harus diisi';
            return Object.keys(this.errors).length === 0;
        },

        async submit() {
            if (!this.validate()) return;
            this.saving = true;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/stok/masuk', this.form, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Stok masuk tersimpan! Menunggu timbangan.', position: 'topRight' });
                setTimeout(() => window.location.href = '/peace_seafood/stok', 1000);
            } catch(e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal menyimpan', position: 'topRight' });
            } finally {
                this.saving = false;
            }
        }
    };
}
</script>
JS;
?>