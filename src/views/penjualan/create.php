<?php ?>
<div x-data="createNotaPage()" x-init="init()">

    <div class="flex items-center gap-4 mb-6">
        <a href="/peace_seafood/penjualan" class="btn btn-secondary p-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Buat Nota Penjualan</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Input transaksi penjualan baru</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Form -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Pembeli -->
            <div class="card p-5">
                <h3 class="font-semibold mb-4" style="color: var(--text-primary)">Informasi Pembeli</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group col-span-2">
                        <label class="form-label">Pembeli</label>
                        <select x-model="form.id_pembeli" class="form-input">
                            <option value="">-- Umum / Tanpa Pembeli --</option>
                            <template x-for="p in pembeli" :key="p.id">
                                <option :value="p.id" x-text="p.nama"></option>
                            </template>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jenis Pembayaran <span class="text-red-500">*</span></label>
                        <select x-model="form.jenis_pembayaran" class="form-input">
                            <option value="cash">CASH</option>
                            <option value="hutang">HUTANG</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Catatan</label>
                        <input type="text" x-model="form.catatan" class="form-input" placeholder="Catatan opsional">
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold" style="color: var(--text-primary)">Item Penjualan</h3>
                    <button @click="addItem()" class="btn btn-primary btn-sm" style="padding: 0.375rem 0.75rem; font-size: 0.8rem">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        Tambah Item
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, idx) in form.items" :key="idx">
                        <div class="p-4 rounded-lg border" style="border-color: var(--border-color)">
                            <div class="grid grid-cols-12 gap-3 items-end">
                                <div class="col-span-4">
                                    <label class="form-label text-xs">Produk</label>
                                    <select x-model="item.id_produk" class="form-input text-sm" @change="setHargaDefault(idx)">
                                        <option value="">-- Pilih --</option>
                                        <template x-for="p in produk" :key="p.id">
                                            <option :value="p.id" x-text="p.nama + ' (stok: ' + parseFloat(p.stok_qty) + ' kg)'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label class="form-label text-xs">Qty (kg)</label>
                                    <input type="number" x-model="item.qty" class="form-input text-sm" 
                                           min="0.01" step="0.01" @input="calcItem(idx)" placeholder="0">
                                </div>
                                <div class="col-span-3">
                                    <label class="form-label text-xs">Harga Jual / kg</label>
                                    <input type="number" x-model="item.harga_jual" class="form-input text-sm" 
                                           min="0" step="100" @input="calcItem(idx)" placeholder="0">
                                </div>
                                <div class="col-span-2">
                                    <label class="form-label text-xs">Subtotal</label>
                                    <p class="text-sm font-bold" style="color: var(--color-primary); padding: 0.5rem 0"
                                       x-text="'Rp ' + parseFloat(item.subtotal||0).toLocaleString('id-ID')"></p>
                                </div>
                                <div class="col-span-1">
                                    <button @click="removeItem(idx)" class="btn btn-danger p-1.5" x-show="form.items.length > 1">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Right: Summary & Submit -->
        <div class="space-y-4">
            <div class="card p-5">
                <h3 class="font-semibold mb-4" style="color: var(--text-primary)">Ringkasan</h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span style="color: var(--text-secondary)">Subtotal</span>
                        <span class="font-medium" x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label text-xs">Diskon (Rp)</label>
                        <input type="number" x-model="form.diskon" class="form-input text-sm" min="0" step="1000" @input="calcTotal()" placeholder="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label text-xs">Pajak (Rp)</label>
                        <input type="number" x-model="form.pajak" class="form-input text-sm" min="0" step="1000" @input="calcTotal()" placeholder="0">
                    </div>
                    
                    <div class="border-t pt-3" style="border-color: var(--border-color)">
                        <div class="flex justify-between items-center">
                            <span class="font-bold">TOTAL</span>
                            <span class="text-xl font-bold" style="color: var(--color-primary)"
                                  x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 space-y-2">
                    <button @click="submitNota('draft')" class="btn btn-secondary w-full" :disabled="saving">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Simpan Draft
                    </button>
                    <button @click="submitNota('final')" class="btn btn-primary w-full" :disabled="saving">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Finalize & Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $scripts = <<<'JS'
<script>
function createNotaPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        form: {
            id_pembeli: '',
            jenis_pembayaran: 'cash',
            catatan: '',
            diskon: 0,
            pajak: 0,
            items: [{ id_produk: '', qty: '', harga_jual: '', subtotal: 0 }],
        },
        produk: [],
        pembeli: [],
        saving: false,

        get subtotal() { return this.form.items.reduce((s, i) => s + parseFloat(i.subtotal || 0), 0); },
        get total() { return this.subtotal - parseFloat(this.form.diskon || 0) + parseFloat(this.form.pajak || 0); },

        async init() {
            await this.loadMasterData();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadMasterData() {
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const [prodRes, pemRes] = await Promise.all([
                    axios.get('/peace_seafood/api/master/produk', { headers }),
                    axios.get('/peace_seafood/api/master/pembeli', { headers }),
                ]);
                this.produk  = prodRes.data?.data || [];
                this.pembeli = pemRes.data?.data || [];
            } catch(e) { console.error(e); }
        },

        addItem() { this.form.items.push({ id_produk: '', qty: '', harga_jual: '', subtotal: 0 }); },
        removeItem(idx) { this.form.items.splice(idx, 1); },
        setHargaDefault(idx) {
            const p = this.produk.find(x => x.id == this.form.items[idx].id_produk);
            if (p) this.form.items[idx].harga_jual = p.harga_jual || '';
            this.calcItem(idx);
        },
        calcItem(idx) {
            const item = this.form.items[idx];
            item.subtotal = parseFloat(item.qty || 0) * parseFloat(item.harga_jual || 0);
        },
        calcTotal() {},

        validate() {
            if (this.form.items.some(i => !i.id_produk || !i.qty || parseFloat(i.qty) <= 0)) {
                iziToast.warning({ title: 'Peringatan', message: 'Lengkapi semua item produk', position: 'topRight' });
                return false;
            }
            return true;
        },

        async submitNota(mode) {
            if (!this.validate()) return;
            this.saving = true;
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const res = await axios.post('/peace_seafood/api/penjualan', this.form, { headers });
                const id = res.data?.data?.id;
                if (mode === 'final' && id) {
                    await axios.post('/peace_seafood/api/penjualan/' + id + '/finalize', {}, { headers });
                }
                iziToast.success({ title: 'Berhasil', message: 'Nota tersimpan!', position: 'topRight' });
                setTimeout(() => window.location.href = '/peace_seafood/penjualan', 1000);
            } catch(e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal simpan nota', position: 'topRight' });
            } finally { this.saving = false; }
        }
    };
}
</script>
JS;
?>
