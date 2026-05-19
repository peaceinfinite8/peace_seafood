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
            
            <!-- Supplier -->
            <div class="form-group">
                <label class="form-label">Supplier <span class="text-red-500">*</span></label>
                <select x-model="form.id_supplier" class="form-input" required>
                    <option value="">-- Pilih Supplier --</option>
                    <template x-for="s in suppliers" :key="s.id">
                        <option :value="s.id" x-text="s.nama"></option>
                    </template>
                </select>
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
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Qty (kg) <span class="text-red-500">*</span></label>
                    <input type="number" x-model="form.qty" class="form-input" 
                           placeholder="0" min="0.01" step="0.01"
                           @input="calcTotal()" required>
                    <p class="text-red-500 text-xs mt-1" x-show="errors.qty" x-text="errors.qty" x-cloak></p>
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Beli / kg <span class="text-red-500">*</span></label>
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
        form: { id_supplier: '', id_produk: '', qty: '', harga_beli: '', catatan: '' },
        errors: {},
        saving: false,
        suppliers: [],
        produk: [],

        async init() {
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

        setProdukInfo() {
            const p = this.produk.find(x => x.id == this.form.id_produk);
            if (p) this.form.harga_beli = p.harga_beli || '';
        },

        validate() {
            this.errors = {};
            if (!this.form.id_supplier) this.errors.id_supplier = 'Supplier wajib dipilih';
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
