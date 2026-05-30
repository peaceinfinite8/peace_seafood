<?php ?>
<div x-data="timbanganPage()" x-init="init()">
    <div class="flex items-center gap-4 mb-6">
        <a href="${window.APP_BASE_URL}/stok"
            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-2 py-2 text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white"><i
                data-lucide="arrow-left" class="w-4 h-4"></i></a>
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Timbangan & Susut</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Proses timbangan stok masuk yang pending</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pending List -->
        <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700">
                <h3 class="font-semibold text-slate-900 dark:text-slate-100">Menunggu Timbangan</h3>
            </div>
            <div class="p-5">
                <div x-show="pendingList.length === 0" class="text-center py-8 text-emerald-600 dark:text-emerald-400">
                    ✓ Tidak ada stok yang menunggu timbangan
                </div>
                <template x-for="item in pendingList" :key="item.id">
                    <div :id="'timbangan-' + item.id" :data-highlight="'timbangan-' + item.id"
                        class="p-4 mb-3 rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 cursor-pointer hover:shadow-sm transition-all"
                        :class="selectedId == item.id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/40' : ''"
                        @click="selectItem(item)">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-sm text-slate-900 dark:text-slate-100"
                                    x-text="item.nama_produk"></p>
                                <p class="text-xs mt-1 text-slate-500 dark:text-slate-400"
                                    x-text="'Supplier: ' + item.nama_supplier"></p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-sm text-slate-900 dark:text-slate-100"
                                    x-text="formatKg(item.qty, 2)"></p>
                                <span
                                    class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-700/30 dark:text-amber-400">PENDING</span>
                            </div>
                        </div>
                        <div class="flex justify-between mt-2 text-xs text-slate-500 dark:text-slate-400">
                            <span x-text="new Date(item.created_at).toLocaleDateString('id-ID')"></span>
                            <span x-text="'Harga: Rp ' + parseFloat(item.harga_beli||0).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Timbangan Form -->
        <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-5">
            <h3 class="font-semibold mb-4 text-slate-900 dark:text-slate-100">Form Timbangan</h3>
            <div x-show="!selectedId" class="text-center py-8 text-slate-500 dark:text-slate-400">
                Pilih stok yang akan ditimbang dari daftar kiri
            </div>
            <div x-show="selectedId" x-cloak>
                <!-- Selected item info -->
                <div class="p-4 rounded-lg mb-4 bg-blue-50 dark:bg-blue-900/30">
                    <p class="font-semibold text-sm text-blue-700 dark:text-blue-300"
                        x-text="selectedItem?.nama_produk"></p>
                    <p class="text-xs mt-1 text-slate-500 dark:text-slate-400">
                        Qty Teoritis: <strong x-text="formatKg(selectedItem?.qty||0, 2)"></strong>
                    </p>
                </div>

                <form @submit.prevent="submitTimbangan()">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Qty Actual (kg)
                            <span class="text-rose-600">*</span></label>
                        <input type="number" x-model="form.qty_actual"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            min="0" step="0.01" @input="calcSusut()" required>
                    </div>

                    <!-- Susut indicator -->
                    <div class="p-3 rounded-lg mb-4"
                        :class="susut > 0 ? 'bg-rose-50 border-l-4 border-rose-600' : 'bg-emerald-50 border-l-4 border-emerald-600'"
                        x-show="form.qty_actual">
                        <div class="flex justify-between text-sm">
                            <span>Susut:</span>
                            <span :class="susut > 0 ? 'text-rose-600 font-semibold' : 'text-emerald-600 font-semibold'"
                                x-text="(susut > 0 ? '-' : '') + formatKg(Math.abs(susut), 2) + ' (' + susutPersen.toFixed(1) + '%)'"></span>
                        </div>
                    </div>

                    <div class="mb-4" x-show="susut > 0">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Alasan Susut</label>
                        <select x-model="form.alasan_susut"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            <option value="">-- Pilih Alasan --</option>
                            <option value="penguapan">Penguapan</option>
                            <option value="kerusakan">Kerusakan</option>
                            <option value="perbedaan_timbangan">Perbedaan Timbangan</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="inline-flex justify-center items-center gap-2 w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"
                        :disabled="saving">
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
            if (!['super_admin', 'admin', 'checker'].includes(this.user.role)) {
                window.location.href = `${window.APP_BASE_URL}/dashboard`;
                return;
            }
            await this.loadPending();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadPending() {
            const token = localStorage.getItem('token');
            const res = await axios.get(`${window.API_BASE_URL}/stok/pending-timbang`, { headers: { Authorization: 'Bearer ' + token } });
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
                await axios.post(`${window.API_BASE_URL}/stok/timbang`, {
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