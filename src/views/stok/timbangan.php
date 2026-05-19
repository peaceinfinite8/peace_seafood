<?php ?>
<div x-data="timbanganPage()" x-init="init()">
    <div class="flex items-center gap-4 mb-6">
        <a href="/peace_seafood/stok" class="btn btn-secondary p-2"><i data-lucide="arrow-left" class="w-4 h-4"></i></a>
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Timbangan & Susut</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Proses timbangan stok masuk yang pending</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pending List -->
        <div class="card">
            <div class="p-5 border-b" style="border-color: var(--border-color)">
                <h3 class="font-semibold" style="color: var(--text-primary)">Menunggu Timbangan</h3>
            </div>
            <div class="p-5">
                <div x-show="pendingList.length === 0" class="text-center py-8" style="color: var(--color-success)">
                    ✓ Tidak ada stok yang menunggu timbangan
                </div>
                <template x-for="item in pendingList" :key="item.id">
                    <div class="p-4 mb-3 rounded-lg border cursor-pointer hover:shadow-sm transition-all"
                         style="border-color: var(--border-color)"
                         :style="selectedId == item.id ? 'border-color: var(--color-primary); background: var(--color-primary-light)' : ''"
                         @click="selectItem(item)">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-sm" x-text="item.nama_produk"></p>
                                <p class="text-xs mt-1" style="color: var(--text-secondary)" x-text="'Supplier: ' + item.nama_supplier"></p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-sm" x-text="parseFloat(item.qty) + ' kg'"></p>
                                <span class="badge badge-warning text-xs">PENDING</span>
                            </div>
                        </div>
                        <div class="flex justify-between mt-2 text-xs" style="color: var(--text-secondary)">
                            <span x-text="new Date(item.created_at).toLocaleDateString('id-ID')"></span>
                            <span x-text="'Harga: Rp ' + parseFloat(item.harga_beli||0).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Timbangan Form -->
        <div class="card p-5">
            <h3 class="font-semibold mb-4" style="color: var(--text-primary)">Form Timbangan</h3>
            <div x-show="!selectedId" class="text-center py-8" style="color: var(--text-secondary)">
                Pilih stok yang akan ditimbang dari daftar kiri
            </div>
            <div x-show="selectedId" x-cloak>
                <!-- Selected item info -->
                <div class="p-4 rounded-lg mb-4" style="background: var(--color-primary-light)">
                    <p class="font-semibold text-sm" style="color: var(--color-primary)" x-text="selectedItem?.nama_produk"></p>
                    <p class="text-xs mt-1" style="color: var(--text-secondary)">
                        Qty Teoritis: <strong x-text="parseFloat(selectedItem?.qty||0) + ' kg'"></strong>
                    </p>
                </div>

                <form @submit.prevent="submitTimbangan()">
                    <div class="form-group">
                        <label class="form-label">Qty Actual (kg) <span class="text-red-500">*</span></label>
                        <input type="number" x-model="form.qty_actual" class="form-input" 
                               min="0" step="0.01" @input="calcSusut()" required>
                    </div>

                    <!-- Susut indicator -->
                    <div class="p-3 rounded-lg mb-4" 
                         :style="susut < 0 ? 'background: rgba(239,68,68,0.1)' : 'background: rgba(16,185,129,0.1)'"
                         x-show="form.qty_actual">
                        <div class="flex justify-between text-sm">
                            <span>Susut:</span>
                            <span :class="susut > 0 ? 'text-red-500 font-bold' : 'text-green-500 font-bold'"
                                  x-text="(susut > 0 ? '-' : '') + Math.abs(susut).toFixed(2) + ' kg (' + susutPersen.toFixed(1) + '%)'"></span>
                        </div>
                    </div>

                    <div class="form-group" x-show="susut > 0">
                        <label class="form-label">Alasan Susut</label>
                        <select x-model="form.alasan_susut" class="form-input">
                            <option value="">-- Pilih Alasan --</option>
                            <option value="penguapan">Penguapan</option>
                            <option value="kerusakan">Kerusakan</option>
                            <option value="perbedaan_timbangan">Perbedaan Timbangan</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-full" :disabled="saving">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span x-text="saving ? 'Memproses...' : 'Konfirmasi Timbangan'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $scripts = <<<'JS'
<script>
function timbanganPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        pendingList: [],
        selectedId: null,
        selectedItem: null,
        form: { qty_actual: '', alasan_susut: '' },
        saving: false,

        get susut() {
            if (!this.selectedItem || !this.form.qty_actual) return 0;
            return parseFloat(this.selectedItem.qty) - parseFloat(this.form.qty_actual);
        },
        get susutPersen() {
            if (!this.selectedItem || !this.form.qty_actual || !parseFloat(this.selectedItem.qty)) return 0;
            return (this.susut / parseFloat(this.selectedItem.qty)) * 100;
        },

        async init() {
            await this.loadPending();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadPending() {
            const token = localStorage.getItem('token');
            const res = await axios.get('/peace_seafood/api/stok/pending-timbang', { headers: { Authorization: 'Bearer ' + token } });
            this.pendingList = res.data?.data || [];
        },

        selectItem(item) {
            this.selectedId = item.id;
            this.selectedItem = item;
            this.form = { qty_actual: item.qty, alasan_susut: '' };
        },

        calcSusut() {},

        async submitTimbangan() {
            if (!this.form.qty_actual) { iziToast.warning({ title: 'Peringatan', message: 'Qty actual wajib diisi', position: 'topRight' }); return; }
            this.saving = true;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/stok/timbang', {
                    id_stok_masuk: this.selectedId, ...this.form,
                }, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Timbangan dikonfirmasi! Stok diupdate.', position: 'topRight' });
                this.selectedId = null; this.selectedItem = null; this.form = { qty_actual: '', alasan_susut: '' };
                await this.loadPending();
            } catch(e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' });
            } finally { this.saving = false; }
        }
    };
}
</script>
JS;
?>
