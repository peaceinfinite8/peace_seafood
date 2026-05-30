<?php /** @var string $activeMenu */ ?>

<div x-data="checkerDraftPage()" x-init="init()">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-5">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
             style="background: rgba(37,99,235,0.1)">
            <i data-lucide="send" class="w-5 h-5" style="color:var(--color-primary)"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold leading-tight" style="color:var(--text-primary)">Buat Draft Nota</h2>
            <p class="text-xs" style="color:var(--text-secondary)">Pilih ikan → isi jumlah → kirim ke kasir</p>
        </div>
    </div>

    <!-- Step indicator -->
    <div class="flex items-center gap-2 mb-5">
        <div class="flex items-center gap-1.5">
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                 :style="step >= 1 ? 'background:var(--color-primary);color:#fff' : 'background:var(--bg-secondary);color:var(--text-secondary)'">1</div>
            <span class="text-xs font-medium" :style="step >= 1 ? 'color:var(--color-primary)' : 'color:var(--text-secondary)'">Pilih Produk</span>
        </div>
        <div class="flex-1 h-px" style="background:var(--border-color)"></div>
        <div class="flex items-center gap-1.5">
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                 :style="step >= 2 ? 'background:var(--color-primary);color:#fff' : 'background:var(--bg-secondary);color:var(--text-secondary)'">2</div>
            <span class="text-xs font-medium" :style="step >= 2 ? 'color:var(--color-primary)' : 'color:var(--text-secondary)'">Isi Jumlah</span>
        </div>
        <div class="flex-1 h-px" style="background:var(--border-color)"></div>
        <div class="flex items-center gap-1.5">
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                 :style="step >= 3 ? 'background:var(--color-primary);color:#fff' : 'background:var(--bg-secondary);color:var(--text-secondary)'">3</div>
            <span class="text-xs font-medium" :style="step >= 3 ? 'color:var(--color-primary)' : 'color:var(--text-secondary)'">Kirim</span>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         STEP 1 — Pilih Produk
    ══════════════════════════════════════════ -->
    <div x-show="step === 1">
        <div class="card p-4 mb-4">
            <input type="text" x-model="search"
                   class="form-input w-full"
                   placeholder="🔍  Cari nama ikan..."
                   @input="filterProduk()">
        </div>

        <div x-show="loadingProduk" class="text-center py-10" style="color:var(--text-secondary)">
            <i data-lucide="loader-2" class="w-6 h-6 inline animate-spin"></i>
        </div>

        <div x-show="!loadingProduk && filteredProduk.length === 0" class="text-center py-10" style="color:var(--text-secondary)">
            <i data-lucide="fish" class="w-8 h-8 inline mb-2 opacity-30"></i>
            <p class="text-sm">Tidak ada produk tersedia</p>
        </div>

        <!-- Grid produk — mobile-friendly cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <template x-for="p in filteredProduk" :key="p.id">
                <button
                    @click="selectProduk(p)"
                    class="card p-4 text-left transition-all active:scale-95"
                    :class="selectedItems.find(i => i.id_produk == p.id) ? 'ring-2 ring-blue-500' : ''"
                    style="cursor:pointer">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm leading-tight truncate" style="color:var(--text-primary)" x-text="p.nama"></p>
                            <p class="text-xs mt-1" style="color:var(--text-secondary)" x-text="p.nama_jenis || ''"></p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-xs font-bold" style="color:var(--color-success)"
                               x-text="'Rp ' + parseFloat(p.harga_jual||0).toLocaleString('id-ID')"></p>
                            <p class="text-xs mt-0.5" style="color:var(--text-secondary)"
                               x-text="parseFloat(p.stok_qty||0).toLocaleString('id-ID') + ' kg'"></p>
                        </div>
                    </div>
                    <!-- Stok bar -->
                    <div class="mt-2 h-1.5 rounded-full overflow-hidden" style="background:var(--bg-secondary)">
                        <div class="h-full rounded-full transition-all"
                             :style="'width:' + Math.min(100, (parseFloat(p.stok_qty||0) / Math.max(1, parseFloat(p.stok_minimum||1)) * 50)) + '%;background:' + (parseFloat(p.stok_qty||0) <= parseFloat(p.stok_minimum||0) ? 'var(--color-danger)' : 'var(--color-success)')">
                        </div>
                    </div>
                    <!-- Sudah dipilih badge -->
                    <div x-show="selectedItems.find(i => i.id_produk == p.id)"
                         class="mt-2 flex items-center gap-1 text-xs font-semibold" style="color:var(--color-primary)">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                        <span x-text="'Dipilih: ' + (selectedItems.find(i => i.id_produk == p.id)?.qty || 0) + ' kg'"></span>
                    </div>
                </button>
            </template>
        </div>

        <!-- Lanjut ke step 2 -->
        <div class="mt-5" x-show="selectedItems.length > 0">
            <button @click="step = 2" class="btn btn-primary w-full py-3 text-base font-bold">
                <i data-lucide="arrow-right" class="w-5 h-5"></i>
                Lanjut — <span x-text="selectedItems.length"></span> produk dipilih
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         STEP 2 — Isi Jumlah & Harga
    ══════════════════════════════════════════ -->
    <div x-show="step === 2">
        <div class="space-y-3 mb-5">
            <template x-for="(item, idx) in selectedItems" :key="item.id_produk">
                <div class="card p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm truncate" style="color:var(--text-primary)" x-text="item.nama"></p>
                            <p class="text-xs" style="color:var(--text-secondary)"
                               x-text="'Stok: ' + parseFloat(item.stok_qty||0).toLocaleString('id-ID') + ' kg'"></p>
                        </div>
                        <button @click="removeItem(idx)" class="ml-2 p-1.5 rounded-lg" style="color:var(--color-danger);background:rgba(239,68,68,0.08)">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Qty input dengan tombol +/- besar (mobile-friendly) -->
                    <div class="mb-3">
                        <label class="text-xs font-semibold mb-1 block" style="color:var(--text-secondary)">JUMLAH (kg)</label>
                        <div class="flex items-center gap-2">
                            <button @click="item.qty = Math.max(0, parseFloat(item.qty||0) - 1); calcItem(idx)"
                                    class="w-11 h-11 rounded-xl flex items-center justify-center text-xl font-bold flex-shrink-0"
                                    style="background:var(--bg-secondary);color:var(--text-primary)">−</button>
                            <input type="number" x-model="item.qty"
                                   @input="calcItem(idx)"
                                   min="0.01" step="0.5"
                                   class="form-input text-center text-lg font-bold flex-1"
                                   style="height:44px"
                                   placeholder="0">
                            <button @click="item.qty = parseFloat(item.qty||0) + 1; calcItem(idx)"
                                    class="w-11 h-11 rounded-xl flex items-center justify-center text-xl font-bold flex-shrink-0"
                                    style="background:var(--color-primary);color:#fff">+</button>
                        </div>
                        <!-- Quick qty buttons -->
                        <div class="flex gap-1.5 mt-2 flex-wrap">
                            <template x-for="q in [5, 10, 25, 50, 100]" :key="q">
                                <button @click="item.qty = q; calcItem(idx)"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold border transition-all"
                                        :style="parseFloat(item.qty) === q ? 'background:var(--color-primary);color:#fff;border-color:var(--color-primary)' : 'background:var(--bg-secondary);color:var(--text-secondary);border-color:var(--border-color)'"
                                        x-text="q + ' kg'"></button>
                            </template>
                        </div>
                    </div>

                    <!-- Harga jual (read-only, bisa diubah) -->
                    <div>
                        <label class="text-xs font-semibold mb-1 block" style="color:var(--text-secondary)">HARGA JUAL / kg</label>
                        <input type="text"
                               :value="item.harga_jual_fmt"
                               @input="onHargaInput(idx, $event)"
                               inputmode="numeric"
                               class="form-input text-right font-semibold"
                               placeholder="0">
                    </div>

                    <!-- Subtotal -->
                    <div class="mt-3 flex justify-between items-center pt-3 border-t" style="border-color:var(--border-color)">
                        <span class="text-xs" style="color:var(--text-secondary)">Subtotal</span>
                        <span class="font-bold" style="color:var(--color-primary)"
                              x-text="'Rp ' + parseFloat(item.subtotal||0).toLocaleString('id-ID')"></span>
                    </div>
                </div>
            </template>
        </div>

        <!-- Catatan pembeli -->
        <div class="card p-4 mb-4">
            <label class="text-xs font-semibold mb-1 block" style="color:var(--text-secondary)">NAMA PEMBELI (opsional)</label>
            <input type="text" x-model="namaPembeli" class="form-input" placeholder="Kosongkan untuk 'Pembeli Umum'">
            <label class="text-xs font-semibold mb-1 mt-3 block" style="color:var(--text-secondary)">CATATAN</label>
            <textarea x-model="catatan" class="form-input" rows="2" placeholder="Catatan untuk kasir..."></textarea>
        </div>

        <!-- Total -->
        <div class="card p-4 mb-4" style="background:rgba(37,99,235,0.04);border-color:rgba(37,99,235,0.2)">
            <div class="flex justify-between items-center">
                <span class="font-semibold" style="color:var(--text-primary)">TOTAL ESTIMASI</span>
                <span class="text-2xl font-black" style="color:var(--color-primary)"
                      x-text="'Rp ' + totalEstimasi.toLocaleString('id-ID')"></span>
            </div>
            <p class="text-xs mt-1" style="color:var(--text-secondary)">*Harga final ditentukan kasir saat finalisasi</p>
        </div>

        <div class="flex gap-3">
            <button @click="step = 1" class="btn btn-secondary flex-1 py-3">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </button>
            <button @click="step = 3" class="btn btn-primary flex-1 py-3 font-bold"
                    :disabled="!isValid">
                Preview & Kirim
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         STEP 3 — Preview & Konfirmasi Kirim
    ══════════════════════════════════════════ -->
    <div x-show="step === 3">
        <div class="card p-4 mb-4">
            <h3 class="font-bold mb-3" style="color:var(--text-primary)">Ringkasan Draft</h3>
            <template x-for="item in selectedItems" :key="item.id_produk">
                <div class="flex justify-between items-center py-2 border-b" style="border-color:var(--border-color)">
                    <div>
                        <p class="text-sm font-medium" x-text="item.nama"></p>
                        <p class="text-xs" style="color:var(--text-secondary)"
                           x-text="parseFloat(item.qty).toLocaleString('id-ID') + ' kg × Rp ' + parseFloat(item.harga_jual||0).toLocaleString('id-ID')"></p>
                    </div>
                    <span class="font-bold text-sm" style="color:var(--color-primary)"
                          x-text="'Rp ' + parseFloat(item.subtotal||0).toLocaleString('id-ID')"></span>
                </div>
            </template>
            <div class="flex justify-between items-center pt-3">
                <span class="font-bold">Total</span>
                <span class="text-xl font-black" style="color:var(--color-primary)"
                      x-text="'Rp ' + totalEstimasi.toLocaleString('id-ID')"></span>
            </div>
            <div class="mt-3 pt-3 border-t text-sm" style="border-color:var(--border-color)" x-show="namaPembeli || catatan">
                <p x-show="namaPembeli"><span style="color:var(--text-secondary)">Pembeli:</span> <strong x-text="namaPembeli"></strong></p>
                <p x-show="catatan" class="mt-1"><span style="color:var(--text-secondary)">Catatan:</span> <span x-text="catatan"></span></p>
            </div>
        </div>

        <!-- Info -->
        <div class="rounded-xl p-3 mb-5 flex gap-2" style="background:rgba(37,99,235,0.06);border:1px solid rgba(37,99,235,0.15)">
            <i data-lucide="info" class="w-4 h-4 flex-shrink-0 mt-0.5" style="color:var(--color-primary)"></i>
            <p class="text-xs" style="color:var(--text-secondary)">
                Draft ini akan dikirim ke <strong>Admin/Kasir</strong> untuk diproses. Anda akan mendapat konfirmasi setelah nota difinalisasi.
            </p>
        </div>

        <div class="flex gap-3">
            <button @click="step = 2" class="btn btn-secondary flex-1 py-3">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Edit
            </button>
            <button @click="kirimDraft()" class="btn btn-primary flex-1 py-3 font-bold text-base"
                    :disabled="sending">
                <template x-if="!sending">
                    <span class="flex items-center gap-2 justify-center">
                        <i data-lucide="send" class="w-5 h-5"></i>
                        Kirim ke Kasir
                    </span>
                </template>
                <template x-if="sending">
                    <span class="flex items-center gap-2 justify-center">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        Mengirim...
                    </span>
                </template>
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         STEP 4 — Sukses
    ══════════════════════════════════════════ -->
    <div x-show="step === 4" class="text-center py-10">
        <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4"
             style="background:rgba(16,185,129,0.1)">
            <i data-lucide="check-circle" class="w-10 h-10" style="color:var(--color-success)"></i>
        </div>
        <h3 class="text-xl font-bold mb-2" style="color:var(--text-primary)">Draft Terkirim!</h3>
        <p class="text-sm mb-1" style="color:var(--text-secondary)">No Nota: <strong x-text="sentNoNota"></strong></p>
        <p class="text-sm mb-6" style="color:var(--text-secondary)">Admin/Kasir sudah mendapat notifikasi dan akan segera memproses.</p>
        <div class="flex gap-3 justify-center">
            <button @click="resetForm()" class="btn btn-primary px-6">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Buat Draft Baru
            </button>
            <a href="/peace_seafood/dashboard" class="btn btn-secondary px-6">Dashboard</a>
        </div>
    </div>

</div>

<?php $scripts = <<<'ENDSCRIPT'
<script>
function checkerDraftPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        step: 1,
        loadingProduk: true,
        sending: false,
        produkList: [],
        filteredProduk: [],
        search: '',
        selectedItems: [],
        namaPembeli: '',
        catatan: '',
        sentNoNota: '',

        get totalEstimasi() {
            return this.selectedItems.reduce((s, i) => s + (parseFloat(i.subtotal) || 0), 0);
        },
        get isValid() {
            return this.selectedItems.length > 0 &&
                   this.selectedItems.every(i => parseFloat(i.qty) > 0);
        },

        async init() {
            if (this.user.role !== 'checker') {
                window.location.href = '/peace_seafood/dashboard';
                return;
            }
            await this.loadProduk();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadProduk() {
            this.loadingProduk = true;
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/master/produk', {
                    headers: { Authorization: 'Bearer ' + token }
                });
                this.produkList = (res.data?.data || []).filter(p => p.is_active && parseFloat(p.stok_qty || 0) > 0);
                this.filteredProduk = [...this.produkList];
            } catch (e) {
                iziToast.error({ title: 'Error', message: 'Gagal memuat daftar produk', position: 'topRight' });
            }
            this.loadingProduk = false;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        filterProduk() {
            const q = this.search.toLowerCase().trim();
            this.filteredProduk = q
                ? this.produkList.filter(p => p.nama.toLowerCase().includes(q) || (p.nama_jenis || '').toLowerCase().includes(q))
                : [...this.produkList];
        },

        selectProduk(p) {
            const existing = this.selectedItems.find(i => i.id_produk == p.id);
            if (existing) {
                // Toggle off
                this.selectedItems = this.selectedItems.filter(i => i.id_produk != p.id);
            } else {
                this.selectedItems.push({
                    id_produk:     p.id,
                    nama:          p.nama,
                    stok_qty:      p.stok_qty,
                    stok_minimum:  p.stok_minimum,
                    harga_jual:    parseFloat(p.harga_jual || 0),
                    harga_jual_fmt: parseFloat(p.harga_jual || 0).toLocaleString('id-ID'),
                    qty:           '',
                    subtotal:      0,
                });
            }
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        removeItem(idx) {
            this.selectedItems.splice(idx, 1);
            if (this.selectedItems.length === 0) this.step = 1;
        },

        calcItem(idx) {
            const item = this.selectedItems[idx];
            const qty  = parseFloat(item.qty) || 0;
            const hrg  = parseFloat(item.harga_jual) || 0;
            item.subtotal = Math.round(qty * hrg);
        },

        onHargaInput(idx, event) {
            const raw = event.target.value.replace(/\D/g, '');
            const num = parseInt(raw) || 0;
            this.selectedItems[idx].harga_jual     = num;
            this.selectedItems[idx].harga_jual_fmt = num.toLocaleString('id-ID');
            event.target.value = this.selectedItems[idx].harga_jual_fmt;
            this.calcItem(idx);
        },

        async kirimDraft() {
            if (!this.isValid) return;
            this.sending = true;
            try {
                const token = localStorage.getItem('token');
                const payload = {
                    id_pembeli:       this.namaPembeli || '',
                    jenis_pembayaran: 'cash',
                    catatan:          this.catatan || '',
                    items: this.selectedItems.map(i => ({
                        id_produk:  i.id_produk,
                        qty:        parseFloat(i.qty),
                        harga_jual: parseFloat(i.harga_jual),
                    })),
                };
                const res = await axios.post('/peace_seafood/api/penjualan/draft', payload, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                this.sentNoNota = res.data?.data?.no_nota || '—';
                this.step = 4;
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            } catch (e) {
                iziToast.error({
                    title: 'Gagal Mengirim',
                    message: e.response?.data?.message || 'Terjadi kesalahan. Coba lagi.',
                    position: 'topRight'
                });
            }
            this.sending = false;
        },

        resetForm() {
            this.step = 1;
            this.selectedItems = [];
            this.namaPembeli = '';
            this.catatan = '';
            this.sentNoNota = '';
            this.search = '';
            this.filteredProduk = [...this.produkList];
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },
    };
}
</script>
ENDSCRIPT;
?>
