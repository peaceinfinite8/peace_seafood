<?php ?>
<div x-data="returCreate()" x-init="init()">

    <div class="mb-6 flex items-center gap-4">
        <a href="${window.APP_BASE_URL}/retur"
            class="inline-flex items-center justify-center rounded-lg bg-slate-600 p-2 text-white shadow-sm transition hover:opacity-95 dark:bg-slate-700">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Buat Retur Baru</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">Retur stok barang atau adjustment piutang/hutang</p>
        </div>
    </div>

    <div class="max-w-2xl">
        <form @submit.prevent="submit()"
            class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900 space-y-5">

            <!-- Tipe Retur -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-900 dark:text-white">Tipe Retur <span
                        class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" x-model="form.tipe" value="stok" class="sr-only">
                        <div class="rounded-xl border-2 p-4 text-center transition-all"
                            :class="form.tipe==='stok' ? 'border-blue-500 bg-blue-50 dark:border-blue-400 dark:bg-blue-950/20' : 'border-slate-200 dark:border-slate-700'">
                            <i data-lucide="package" class="mx-auto mb-2 h-6 w-6"
                                :class="form.tipe==='stok' ? 'text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400'"></i>
                            <p class="text-sm font-semibold"
                                :class="form.tipe==='stok' ? 'text-blue-600 dark:text-blue-400' : 'text-slate-900 dark:text-white'">
                                Retur Stok</p>
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">Barang fisik dikembalikan</p>
                        </div>
                    </label>
                    <label class="cursor-pointer" x-show="['super_admin','admin'].includes(user.role)">
                        <input type="radio" x-model="form.tipe" value="piutang" class="sr-only">
                        <div class="rounded-xl border-2 p-4 text-center transition-all"
                            :class="form.tipe==='piutang' ? 'border-blue-500 bg-blue-50 dark:border-blue-400 dark:bg-blue-950/20' : 'border-slate-200 dark:border-slate-700'">
                            <i data-lucide="wallet" class="mx-auto mb-2 h-6 w-6"
                                :class="form.tipe==='piutang' ? 'text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400'"></i>
                            <p class="text-sm font-semibold"
                                :class="form.tipe==='piutang' ? 'text-blue-600 dark:text-blue-400' : 'text-slate-900 dark:text-white'">
                                Retur Piutang</p>
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">Adjustment hutang/piutang</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- === RETUR STOK === -->
            <template x-if="form.tipe === 'stok'">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-900 dark:text-white">Produk <span
                                class="text-red-500">*</span></label>
                        <select x-model="form.id_produk" @change="onProdukChange()"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            required>
                            <option value="">-- Pilih Produk --</option>
                            <template x-for="p in produkList" :key="p.id">
                                <option :value="p.id"
                                    x-text="p.nama_produk + ' — Stok: ' + parseFloat(p.stok_qty||0).toFixed(1) + ' kg'">
                                </option>
                            </template>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-900 dark:text-white">Qty Retur (kg) <span
                                class="text-red-500">*</span></label>
                        <input type="number" x-model="form.qty" step="0.01" min="0.01"
                            :max="selectedProduk?.stok_qty || 99999"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            placeholder="0.00" required>
                        <p class="text-xs text-slate-600 dark:text-slate-400" x-show="selectedProduk">
                            Stok tersedia: <strong
                                x-text="parseFloat(selectedProduk?.stok_qty||0).toFixed(1) + ' kg'"></strong>
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-900 dark:text-white">Alasan Retur <span
                                class="text-red-500">*</span></label>
                        <select x-model="form.alasan"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            required>
                            <option value="">-- Pilih Alasan --</option>
                            <option value="barang_rusak">Barang Rusak</option>
                            <option value="kadaluarsa">Kadaluarsa</option>
                            <option value="tidak_sesuai">Tidak Sesuai Pesanan</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-900 dark:text-white">Foto Bukti
                            (opsional)</label>
                        <input type="file" x-ref="fotoInput" accept="image/*"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                        <p class="text-xs text-slate-600 dark:text-slate-400">Format: JPG/PNG, maks 2MB</p>
                    </div>
                </div>
            </template>

            <!-- === RETUR PIUTANG === -->
            <template x-if="form.tipe === 'piutang'">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-900 dark:text-white">Tipe Pihak <span
                                class="text-red-500">*</span></label>
                        <select x-model="form.pihak"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            required>
                            <option value="">-- Pilih --</option>
                            <option value="supplier">Hutang ke Supplier (dikurangi)</option>
                            <option value="pembeli">Piutang dari Pembeli (dikurangi)</option>
                        </select>
                    </div>

                    <div class="space-y-2" x-show="form.pihak === 'supplier'">
                        <label class="block text-sm font-semibold text-slate-900 dark:text-white">Supplier <span
                                class="text-red-500">*</span></label>
                        <select x-model="form.id_supplier"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            <option value="">-- Pilih Supplier --</option>
                            <template x-for="s in suppliers" :key="s.id">
                                <option :value="s.id" x-text="s.nama"></option>
                            </template>
                        </select>
                    </div>

                    <div class="space-y-2" x-show="form.pihak === 'pembeli'">
                        <label class="block text-sm font-semibold text-slate-900 dark:text-white">Pembeli <span
                                class="text-red-500">*</span></label>
                        <select x-model="form.id_pembeli"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            <option value="">-- Pilih Pembeli --</option>
                            <template x-for="p in pembeli" :key="p.id">
                                <option :value="p.id" x-text="p.nama"></option>
                            </template>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-900 dark:text-white">Nominal Retur <span
                                class="text-red-500">*</span></label>
                        <input type="number" x-model="form.nominal" step="1000" min="1"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            placeholder="0" required>
                        <p class="text-xs text-slate-600 dark:text-slate-400">
                            Nominal: <strong
                                x-text="'Rp ' + (parseInt(form.nominal)||0).toLocaleString('id-ID')"></strong>
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-900 dark:text-white">Alasan <span
                                class="text-red-500">*</span></label>
                        <select x-model="form.alasan"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            required>
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
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-900 dark:text-white">Catatan Tambahan</label>
                <textarea x-model="form.keterangan"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                    rows="2" placeholder="Keterangan tambahan..."></textarea>
            </div>

            <!-- Info -->
            <div
                class="rounded-lg border-l-4 border-amber-400 bg-amber-50 p-4 text-sm dark:border-amber-600 dark:bg-amber-950/20">
                <p class="mb-1 font-semibold text-amber-700 dark:text-amber-300">⏳ Proses Persetujuan</p>
                <p class="text-amber-600 dark:text-amber-200">Retur akan berstatus <strong>PENDING</strong> dan
                    memerlukan persetujuan dari <strong>Bos</strong> sebelum inventory/hutang diupdate.</p>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-2">
                <a href="${window.APP_BASE_URL}/retur"
                    class="flex-1 inline-flex items-center justify-center rounded-lg bg-slate-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-slate-700">Batal</a>
                <button type="submit"
                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 disabled:opacity-50 dark:bg-blue-700"
                    :disabled="submitting">
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
                const res = await axios.get(`${window.API_BASE_URL}/master/produk`, { headers: { Authorization: 'Bearer '+token } });
                this.produkList = res.data?.data || [];
            } catch(e) {}
        },

        async loadSuppliers() {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get(`${window.API_BASE_URL}/master/supplier`, { headers: { Authorization: 'Bearer '+token } });
                this.suppliers = res.data?.data || [];
            } catch(e) {}
        },

        async loadPembeli() {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get(`${window.API_BASE_URL}/master/pembeli`, { headers: { Authorization: 'Bearer '+token } });
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
                await axios.post(`${window.API_BASE_URL}/retur`, payload, { headers: { Authorization: 'Bearer '+token } });
                iziToast.success({ title: 'Berhasil', message: 'Retur berhasil disubmit, menunggu persetujuan Bos', position: 'topRight' });
                setTimeout(() => { window.location.href = `${window.APP_BASE_URL}/retur`; }, 1200);
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