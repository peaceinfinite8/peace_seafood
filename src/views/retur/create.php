<?php ?>
<div x-data="returCreate()" x-init="init()">

    <div class="flex items-center gap-4 mb-6">
        <a href="/peace_seafood/retur" class="btn btn-secondary p-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Buat Retur Baru</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Retur stok barang atau adjustment piutang/hutang</p>
        </div>
    </div>

    <div class="max-w-2xl">
        <form @submit.prevent="submit()" class="card p-6 space-y-5">

            <!-- Tipe Retur -->
            <div class="form-group">
                <label class="form-label">Tipe Retur <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" x-model="form.tipe" value="stok" class="sr-only">
                        <div class="p-4 rounded-xl border-2 text-center transition-all"
                             :class="form.tipe==='stok' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                             style="border-color: var(--border-color)"
                             :style="form.tipe==='stok' ? 'border-color:#2563eb; background:var(--color-primary-light)' : ''">
                            <i data-lucide="package" class="w-6 h-6 mx-auto mb-2"
                               :style="form.tipe==='stok' ? 'color:#2563eb' : 'color:var(--text-secondary)'"></i>
                            <p class="font-semibold text-sm" :style="form.tipe==='stok' ? 'color:#2563eb' : ''">Retur Stok</p>
                            <p class="text-xs mt-1" style="color:var(--text-secondary)">Barang fisik dikembalikan</p>
                        </div>
                    </label>
                    <label class="cursor-pointer" x-show="['super_admin','admin'].includes(user.role)">
                        <input type="radio" x-model="form.tipe" value="piutang" class="sr-only">
                        <div class="p-4 rounded-xl border-2 text-center transition-all"
                             :style="form.tipe==='piutang' ? 'border-color:#2563eb; background:var(--color-primary-light)' : 'border-color: var(--border-color)'">
                            <i data-lucide="wallet" class="w-6 h-6 mx-auto mb-2"
                               :style="form.tipe==='piutang' ? 'color:#2563eb' : 'color:var(--text-secondary)'"></i>
                            <p class="font-semibold text-sm" :style="form.tipe==='piutang' ? 'color:#2563eb' : ''">Retur Piutang</p>
                            <p class="text-xs mt-1" style="color:var(--text-secondary)">Adjustment hutang/piutang</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- === RETUR STOK === -->
            <template x-if="form.tipe === 'stok'">
                <div class="space-y-4">
                    <div class="form-group">
                        <label class="form-label">Produk <span class="text-red-500">*</span></label>
                        <select x-model="form.id_produk" @change="onProdukChange()" class="form-input" required>
                            <option value="">-- Pilih Produk --</option>
                            <template x-for="p in produkList" :key="p.id">
                                <option :value="p.id"
                                        x-text="p.nama_produk + ' — Stok: ' + parseFloat(p.stok_qty||0).toFixed(1) + ' kg'">
                                </option>
                            </template>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Qty Retur (kg) <span class="text-red-500">*</span></label>
                        <input type="number" x-model="form.qty" step="0.01" min="0.01"
                               :max="selectedProduk?.stok_qty || 99999"
                               class="form-input" placeholder="0.00" required>
                        <p class="text-xs mt-1" style="color:var(--text-secondary)"
                           x-show="selectedProduk">
                            Stok tersedia: <strong x-text="parseFloat(selectedProduk?.stok_qty||0).toFixed(1) + ' kg'"></strong>
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alasan Retur <span class="text-red-500">*</span></label>
                        <select x-model="form.alasan" class="form-input" required>
                            <option value="">-- Pilih Alasan --</option>
                            <option value="barang_rusak">Barang Rusak</option>
                            <option value="kadaluarsa">Kadaluarsa</option>
                            <option value="tidak_sesuai">Tidak Sesuai Pesanan</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Foto Bukti (opsional)</label>
                        <input type="file" x-ref="fotoInput" accept="image/*"
                               class="form-input" style="padding: 0.4rem">
                        <p class="text-xs mt-1" style="color:var(--text-secondary)">Format: JPG/PNG, maks 2MB</p>
                    </div>
                </div>
            </template>

            <!-- === RETUR PIUTANG === -->
            <template x-if="form.tipe === 'piutang'">
                <div class="space-y-4">
                    <div class="form-group">
                        <label class="form-label">Tipe Pihak <span class="text-red-500">*</span></label>
                        <select x-model="form.pihak" class="form-input" required>
                            <option value="">-- Pilih --</option>
                            <option value="supplier">Hutang ke Supplier (dikurangi)</option>
                            <option value="pembeli">Piutang dari Pembeli (dikurangi)</option>
                        </select>
                    </div>

                    <div class="form-group" x-show="form.pihak === 'supplier'">
                        <label class="form-label">Supplier <span class="text-red-500">*</span></label>
                        <select x-model="form.id_supplier" class="form-input">
                            <option value="">-- Pilih Supplier --</option>
                            <template x-for="s in suppliers" :key="s.id">
                                <option :value="s.id" x-text="s.nama"></option>
                            </template>
                        </select>
                    </div>

                    <div class="form-group" x-show="form.pihak === 'pembeli'">
                        <label class="form-label">Pembeli <span class="text-red-500">*</span></label>
                        <select x-model="form.id_pembeli" class="form-input">
                            <option value="">-- Pilih Pembeli --</option>
                            <template x-for="p in pembeli" :key="p.id">
                                <option :value="p.id" x-text="p.nama"></option>
                            </template>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nominal Retur <span class="text-red-500">*</span></label>
                        <input type="number" x-model="form.nominal" step="1000" min="1"
                               class="form-input" placeholder="0" required>
                        <p class="text-xs mt-1" style="color:var(--text-secondary)">
                            Nominal: <strong x-text="'Rp ' + (parseInt(form.nominal)||0).toLocaleString('id-ID')"></strong>
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alasan <span class="text-red-500">*</span></label>
                        <select x-model="form.alasan" class="form-input" required>
                            <option value="">-- Pilih Alasan --</option>
                            <option value="potongan_kualitas">Potongan Kualitas</option>
                            <option value="gratis_marketing">Gratis / Marketing</option>
                            <option value="recall_produk">Recall Produk</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>
            </template>

            <!-- Catatan tambahan -->
            <div class="form-group">
                <label class="form-label">Catatan Tambahan</label>
                <textarea x-model="form.keterangan" class="form-input" rows="2"
                          placeholder="Keterangan tambahan..."></textarea>
            </div>

            <!-- Info -->
            <div class="p-4 rounded-lg text-sm border-l-4 border-yellow-400" style="background:var(--bg-gray)">
                <p class="font-semibold text-yellow-600 mb-1">⏳ Proses Persetujuan</p>
                <p style="color:var(--text-secondary)">Retur akan berstatus <strong>PENDING</strong> dan memerlukan persetujuan dari <strong>Bos</strong> sebelum inventory/hutang diupdate.</p>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-2">
                <a href="/peace_seafood/retur" class="btn btn-secondary flex-1 justify-center">Batal</a>
                <button type="submit" class="btn btn-primary flex-1 justify-center" :disabled="submitting">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span x-text="submitting ? 'Mengirim...' : 'Submit Retur'"></span>
                </button>
            </div>

        </form>
    </div>

</div>

<?php $scripts = <<<'JS'
<script>
function returCreate() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        submitting: false,
        produkList: [],
        suppliers: [],
        pembeli: [],
        selectedProduk: null,
        form: {
            tipe: 'stok',
            id_produk: '',
            qty: '',
            id_supplier: '',
            id_pembeli: '',
            pihak: '',
            nominal: '',
            alasan: '',
            keterangan: '',
        },

        async init() {
            await Promise.all([this.loadProduk(), this.loadSuppliers(), this.loadPembeli()]);
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadProduk() {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/master/produk', { headers: { Authorization: 'Bearer '+token } });
                this.produkList = res.data?.data || [];
            } catch(e) {}
        },

        async loadSuppliers() {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/master/supplier', { headers: { Authorization: 'Bearer '+token } });
                this.suppliers = res.data?.data || [];
            } catch(e) {}
        },

        async loadPembeli() {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/master/pembeli', { headers: { Authorization: 'Bearer '+token } });
                this.pembeli = res.data?.data || [];
            } catch(e) {}
        },

        onProdukChange() {
            this.selectedProduk = this.produkList.find(p => p.id == this.form.id_produk) || null;
        },

        async submit() {
            // Validate
            if (!this.form.alasan) {
                iziToast.warning({ title: 'Perhatian', message: 'Alasan retur wajib diisi', position: 'topRight' });
                return;
            }
            if (this.form.tipe === 'stok' && (!this.form.id_produk || !this.form.qty)) {
                iziToast.warning({ title: 'Perhatian', message: 'Produk dan qty wajib diisi', position: 'topRight' });
                return;
            }
            if (this.form.tipe === 'piutang' && !this.form.nominal) {
                iziToast.warning({ title: 'Perhatian', message: 'Nominal wajib diisi', position: 'topRight' });
                return;
            }

            this.submitting = true;
            try {
                const token = localStorage.getItem('token');
                const payload = { ...this.form };
                // Clean unused fields
                if (this.form.tipe === 'stok') {
                    delete payload.id_supplier; delete payload.id_pembeli; delete payload.nominal; delete payload.pihak;
                } else {
                    delete payload.id_produk; delete payload.qty;
                    if (this.form.pihak === 'supplier') delete payload.id_pembeli;
                    else delete payload.id_supplier;
                }
                await axios.post('/peace_seafood/api/retur', payload, { headers: { Authorization: 'Bearer '+token } });
                iziToast.success({ title: 'Berhasil', message: 'Retur berhasil disubmit, menunggu persetujuan Bos', position: 'topRight' });
                setTimeout(() => { window.location.href = '/peace_seafood/retur'; }, 1200);
            } catch(e) {
                const msg = e.response?.data?.message || 'Gagal menyimpan retur';
                iziToast.error({ title: 'Error', message: msg, position: 'topRight' });
            }
            this.submitting = false;
        }
    };
}
</script>
JS;
?>
