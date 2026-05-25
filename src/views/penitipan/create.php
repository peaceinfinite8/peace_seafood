<?php ?>
<div x-data="penitipanCreate()" x-init="init()">

    <div class="flex items-center gap-4 mb-6">
        <a href="/peace_seafood/penitipan" class="btn btn-secondary p-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Terima Titipan Baru</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Input barang titipan/konsinyasi dari supplier</p>
        </div>
    </div>

    <div class="max-w-2xl">
        <form @submit.prevent="submit()" class="card p-6 space-y-5">

            <!-- Info Banner -->
            <div class="p-4 rounded-lg border-l-4 border-blue-500 text-sm" style="background:var(--bg-gray)">
                <p class="font-semibold text-blue-600 mb-1">ℹ️ Tentang Penitipan</p>
                <p style="color:var(--text-secondary)">Barang titipan adalah barang dari supplier/pihak lain yang dititipkan untuk dijualkan. Komisi dihitung berdasarkan persentase dari total penjualan.</p>
            </div>

            <!-- Supplier/Pengirim -->
            <div class="form-group">
                <label class="form-label">Supplier / Pengirim <span class="text-red-500">*</span></label>
                <select x-model="form.pembeli_id" class="form-input" required>
                    <option value="">-- Pilih Supplier/Pengirim --</option>
                    <template x-for="s in suppliers" :key="s.id">
                        <option :value="s.id" x-text="s.nama"></option>
                    </template>
                </select>
                <p class="text-xs mt-1" style="color:var(--text-secondary)">Pilih dari daftar supplier yang sudah terdaftar</p>
            </div>

            <!-- Produk -->
            <div class="form-group">
                <label class="form-label">Produk / Jenis Ikan <span class="text-red-500">*</span></label>
                <select x-model="form.produk_id" class="form-input" required>
                    <option value="">-- Pilih Produk --</option>
                    <template x-for="p in produkList" :key="p.id">
                        <option :value="p.id" x-text="p.nama_produk + ' (Stok: ' + parseFloat(p.stok_qty||0).toFixed(1) + ' kg)'"></option>
                    </template>
                </select>
            </div>

            <!-- Qty & Harga sejajar -->
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Jumlah (kg) <span class="text-red-500">*</span></label>
                    <input type="number" x-model="form.jumlah" step="0.01" min="0.01"
                           class="form-input" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Titip / kg <span class="text-red-500">*</span></label>
                    <input type="number" x-model="form.harga_titip" step="100" min="0"
                           class="form-input" placeholder="0" required>
                </div>
            </div>

            <!-- Komisi & Tanggal sejajar -->
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Komisi (%)</label>
                    <input type="number" x-model="form.komisi_persen" step="0.1" min="0" max="100"
                           class="form-input" placeholder="0">
                    <p class="text-xs mt-1" style="color:var(--text-secondary)">Persentase komisi gudang</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Masuk <span class="text-red-500">*</span></label>
                    <input type="date" x-model="form.tanggal_masuk" class="form-input" required>
                </div>
            </div>

            <!-- Catatan -->
            <div class="form-group">
                <label class="form-label">Catatan (opsional)</label>
                <textarea x-model="form.catatan" class="form-input" rows="3"
                          placeholder="Keterangan tambahan..."></textarea>
            </div>

            <!-- Preview Kalkulasi -->
            <div class="p-4 rounded-xl border" style="border-color:var(--border-color); background:var(--bg-gray)"
                 x-show="form.jumlah && form.harga_titip">
                <p class="text-sm font-semibold mb-3">📊 Estimasi</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span style="color:var(--text-secondary)">Total Nilai Titipan</span>
                        <strong x-text="'Rp ' + (form.jumlah * form.harga_titip).toLocaleString('id-ID')"></strong>
                    </div>
                    <div class="flex justify-between" x-show="form.komisi_persen > 0">
                        <span style="color:var(--text-secondary)">Komisi Gudang (<span x-text="form.komisi_persen"></span>%)</span>
                        <strong class="text-green-600"
                                x-text="'Rp ' + (form.jumlah * form.harga_titip * form.komisi_persen / 100).toLocaleString('id-ID')"></strong>
                    </div>
                    <div class="flex justify-between border-t pt-2" style="border-color:var(--border-color)" x-show="form.komisi_persen > 0">
                        <span style="color:var(--text-secondary)">Diterima Supplier</span>
                        <strong class="text-blue-600"
                                x-text="'Rp ' + (form.jumlah * form.harga_titip * (1 - form.komisi_persen/100)).toLocaleString('id-ID')"></strong>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-2">
                <a href="/peace_seafood/penitipan" class="btn btn-secondary flex-1 justify-center">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary flex-1 justify-center" :disabled="submitting">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span x-text="submitting ? 'Menyimpan...' : 'Simpan Titipan'"></span>
                </button>
            </div>

        </form>
    </div>

</div>

<?php $scripts = <<<'JS'
<script>
function penitipanCreate() {
    return {
        submitting: false,
        suppliers: [],
        produkList: [],
        form: {
            pembeli_id: '',
            produk_id: '',
            jumlah: '',
            harga_titip: '',
            komisi_persen: 0,
            tanggal_masuk: new Date().toISOString().slice(0, 10),
            catatan: '',
        },

        async init() {
            await Promise.all([this.loadSuppliers(), this.loadProduk()]);
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadSuppliers() {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/master/supplier', { headers: { Authorization: 'Bearer '+token } });
                this.suppliers = res.data?.data || [];
            } catch(e) { console.error(e); }
        },

        async loadProduk() {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/master/produk', { headers: { Authorization: 'Bearer '+token } });
                this.produkList = res.data?.data || [];
            } catch(e) { console.error(e); }
        },

        async submit() {
            if (!this.form.pembeli_id || !this.form.produk_id || !this.form.jumlah || !this.form.harga_titip) {
                iziToast.warning({ title: 'Perhatian', message: 'Lengkapi semua field wajib', position: 'topRight' });
                return;
            }
            this.submitting = true;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/penitipan', this.form, { headers: { Authorization: 'Bearer '+token } });
                iziToast.success({ title: 'Berhasil', message: 'Titipan berhasil disimpan!', position: 'topRight' });
                setTimeout(() => { window.location.href = '/peace_seafood/penitipan'; }, 1000);
            } catch(e) {
                const msg = e.response?.data?.message || 'Gagal menyimpan';
                iziToast.error({ title: 'Error', message: msg, position: 'topRight' });
            }
            this.submitting = false;
        }
    };
}
</script>
JS;
?>
