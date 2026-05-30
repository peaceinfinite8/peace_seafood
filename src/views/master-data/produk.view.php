<?php ?>
<div x-data="produkPage()" x-init="init()">
    <div class="flex items-center gap-4 mb-6">
        <a href="${window.APP_BASE_URL}/master-data"
            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-2 py-2 text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white"><i
                data-lucide="arrow-left" class="w-4 h-4"></i></a>
        <div class="flex-1">
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Data Produk</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola produk, harga, dan stok minimum</p>
        </div>
        <button @click="openAdd()"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"
            x-show="['super_admin','admin'].includes(user.role)">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Produk
        </button>
    </div>
    <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-4 mb-4"><input
            type="text" x-model="search" placeholder="Cari produk..."
            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white max-w-sm">
    </div>
    <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Jenis Ikan</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Stok Min</th>
                        <th>Stok Saat Ini</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-500 dark:text-slate-400">Tidak ada data
                            </td>
                        </tr>
                    </template>
                    <template x-for="p in filtered" :key="p.id">
                        <tr :id="'produk-' + p.id" :data-highlight="'produk-' + p.id">
                            <td class="font-medium text-sm" x-text="p.nama"></td>
                            <td><span
                                    class="inline-flex items-center rounded-full bg-sky-100 px-2 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-700/30 dark:text-sky-400"
                                    x-text="p.nama_jenis"></span></td>
                            <td class="text-sm" x-text="'Rp ' + parseFloat(p.harga_beli||0).toLocaleString('id-ID')">
                            </td>
                            <td class="text-sm font-medium text-blue-600 dark:text-blue-400"
                                x-text="'Rp ' + parseFloat(p.harga_jual||0).toLocaleString('id-ID')"></td>
                            <td class="text-sm" x-text="formatQty(p.stok_minimum, p.satuan)"></td>
                            <td>
                                <span class="font-semibold text-sm"
                                    :class="parseFloat(p.stok_qty||0) < parseFloat(p.stok_minimum||0) ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'"
                                    x-text="formatQty(p.stok_qty, p.satuan)"></span>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button @click="openEdit(p)"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                                        x-show="['super_admin','admin'].includes(user.role)"><i data-lucide="pencil"
                                            class="w-3.5 h-3.5"></i></button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6" x-show="showModal"
        @click.self="showModal = false" x-cloak>
        <div
            class="w-full max-w-md rounded-lg border border-slate-300 bg-white shadow-xl dark:border-slate-600 dark:bg-slate-800">
            <div
                class="border-b border-slate-200 dark:border-slate-700 px-4 py-3 sm:flex sm:items-center sm:justify-between">
                <h3 class="font-bold text-lg" x-text="editId ? 'Edit Produk' : 'Tambah Produk'"></h3>
                <button @click="showModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="save()" class="px-4 py-4 sm:p-6">
                <div class="mb-4"><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nama
                        Produk *</label><input type="text" x-model="form.nama"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        required></div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Jenis Ikan *</label>
                    <select x-model="form.id_jenis_ikan"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        required>
                        <option value="">-- Pilih --</option>
                        <template x-for="j in jenisIkan" :key="j.id">
                            <option :value="j.id" x-text="j.nama"></option>
                        </template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Harga
                            Beli</label><input type="text" inputmode="numeric" :value="form.harga_beli"
                            @input="handleRupiahInput('harga_beli', $event)"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            placeholder="0"></div>
                    <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Harga
                            Jual</label><input type="text" inputmode="numeric" :value="form.harga_jual"
                            @input="handleRupiahInput('harga_jual', $event)"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            placeholder="0"></div>
                    <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Stok
                            Minimum</label><input type="number" x-model="form.stok_minimum"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            min="0" step="0.1"></div>
                    <input type="hidden" x-model="form.satuan">
                </div>
                <div class="border-t border-slate-200 dark:border-slate-700 px-4 py-3 sm:px-6 sm:py-4 flex gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"
                        :disabled="saving" x-text="saving ? 'Menyimpan...' : (editId ? 'Update' : 'Simpan')"></button>
                    <button type="button" @click="showModal = false"
                        class="inline-flex items-center gap-2 flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $scripts = <<<'JS'
<script>
function produkPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        items: [], jenisIkan: [], search: '', showModal: false, editId: null, saving: false,
        form: { nama: '', id_jenis_ikan: '', harga_beli: '', harga_jual: '', stok_minimum: '', satuan: 'kg' },
        get filtered() { const q = this.search.toLowerCase(); return this.items.filter(p => !q || p.nama?.toLowerCase().includes(q) || p.nama_jenis?.toLowerCase().includes(q)); },
        formatQty(qty, satuan) {
            let q = parseFloat(qty) || 0;
            if (!satuan || satuan.toLowerCase() === 'kg') {
                if (q >= 10000) {
                    return (q / 1000).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' ton';
                } else if (q >= 100) {
                    return (q / 100).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' kintal';
                } else {
                    return q.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' kg';
                }
            }
            return q.toLocaleString('id-ID') + ' ' + satuan;
        },
        formatRupiahInput(value) {
            const numeric = String(value ?? '').replace(/\D/g, '');
            if (!numeric) return '';
            return new Intl.NumberFormat('id-ID').format(Number(numeric));
        },
        handleRupiahInput(field, event) {
            const formatted = this.formatRupiahInput(event.target.value);
            this.form[field] = formatted;
            event.target.value = formatted;
        },
        normalizeRupiah(value) {
            const numeric = String(value ?? '').replace(/\D/g, '');
            return numeric ? Number(numeric) : 0;
        },
        async init() {
            const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
            const [prodRes, jenisRes] = await Promise.all([
                axios.get(`${window.API_BASE_URL}/master/produk`, { headers }),
                axios.get(`${window.API_BASE_URL}/master/jenis-ikan`, { headers }),
            ]);
            this.items = prodRes.data?.data || []; this.jenisIkan = jenisRes.data?.data || [];
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },
        openAdd() { this.editId = null; this.form = { nama: '', id_jenis_ikan: '', harga_beli: '', harga_jual: '', stok_minimum: '', satuan: 'kg' }; this.showModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        openEdit(p) { this.editId = p.id; this.form = { nama: p.nama, id_jenis_ikan: p.id_jenis_ikan, harga_beli: this.formatRupiahInput(p.harga_beli), harga_jual: this.formatRupiahInput(p.harga_jual), stok_minimum: p.stok_minimum ?? '', satuan: 'kg' }; this.showModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        async save() {
            if (!this.form.nama || !this.form.id_jenis_ikan) return; this.saving = true;
            try {
                const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
                const payload = {
                    ...this.form,
                    harga_beli: this.normalizeRupiah(this.form.harga_beli),
                    harga_jual: this.normalizeRupiah(this.form.harga_jual),
                    satuan: 'kg',
                };
                if (this.editId) { await axios.put(`${window.API_BASE_URL}/master/produk/` + this.editId, payload, { headers }); }
                else { await axios.post(`${window.API_BASE_URL}/master/produk`, payload, { headers }); }
                iziToast.success({ title: 'Berhasil', message: 'Data tersimpan', position: 'topRight' }); this.showModal = false;
                const res = await axios.get(`${window.API_BASE_URL}/master/produk`, { headers });
                this.items = res.data?.data || [];
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
            this.saving = false;
        }
    };
}
</script>
JS;
?>