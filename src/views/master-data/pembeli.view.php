<?php ?>
<div x-data="pembeliPage()" x-init="init()">
    <div class="flex items-center gap-4 mb-6">
        <a href="${window.APP_BASE_URL}/master-data"
            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-2 py-2 text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white"><i
                data-lucide="arrow-left" class="w-4 h-4"></i></a>
        <div class="flex-1">
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Data Pembeli</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola data pelanggan / buyer</p>
        </div>
        <button @click="openAdd()"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"><i
                data-lucide="plus" class="w-4 h-4"></i> Tambah Pembeli</button>
    </div>
    <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-4 mb-4"><input
            type="text" x-model="search" placeholder="Cari pembeli..."
            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white max-w-sm">
    </div>
    <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Tipe</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="5" class="text-center py-8 text-slate-500 dark:text-slate-400">Tidak ada data
                            </td>
                        </tr>
                    </template>
                    <template x-for="s in filtered" :key="s.id">
                        <tr>
                            <td class="font-medium text-sm" x-text="s.nama"></td>
                            <td class="text-sm" x-text="s.telpon || '-'"></td>
                            <td class="text-sm" x-text="s.alamat || '-'"></td>
                            <td><span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="s.tipe === 'bulk' ? 'bg-blue-100 text-blue-700 dark:bg-blue-700/30 dark:text-blue-400' : (s.tipe === 'reseller' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-700/30 dark:text-emerald-400' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300')"
                                    x-text="(s.tipe||'retail').toUpperCase()"></span></td>
                            <td>
                                <div class="flex gap-2">
                                    <button @click="openEdit(s)"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white"><i
                                            data-lucide="pencil" class="w-3.5 h-3.5"></i></button>
                                    <button @click="deleteItem(s.id)"
                                        class="inline-flex items-center justify-center rounded-lg border border-rose-300 bg-rose-50 px-2 py-1.5 text-rose-700 shadow-sm transition hover:bg-rose-100 dark:border-rose-700 dark:bg-rose-900/20 dark:text-rose-400"><i
                                            data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
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
                <h3 class="font-bold text-lg" x-text="editId ? 'Edit Pembeli' : 'Tambah Pembeli'"></h3>
                <button @click="showModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="save()" class="px-4 py-4 sm:p-6">
                <div class="mb-4"><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nama
                        *</label><input type="text" x-model="form.nama"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        required></div>
                <div class="mb-4"><label
                        class="block text-sm font-medium text-slate-700 dark:text-slate-300">Telepon</label><input
                        type="text" x-model="form.telepon"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                </div>
                <div class="mb-4"><label
                        class="block text-sm font-medium text-slate-700 dark:text-slate-300">Alamat</label><textarea
                        x-model="form.alamat"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        rows="2"></textarea></div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Tipe</label>
                    <select x-model="form.tipe"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                        <option value="retail">Retail</option>
                        <option value="bulk">Bulk</option>
                        <option value="reseller">Reseller</option>
                    </select>
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
function pembeliPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        items: [], search: '', showModal: false, editId: null, saving: false,
        form: { nama: '', telepon: '', alamat: '', tipe: 'retail' },
        get filtered() { const q = this.search.toLowerCase(); return this.items.filter(s => !q || s.nama?.toLowerCase().includes(q)); },
        async init() { await this.load(); this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        async load() { const token = localStorage.getItem('token'); const res = await axios.get(`${window.API_BASE_URL}/master/pembeli`, { headers: { Authorization: 'Bearer ' + token } }); this.items = res.data?.data || []; },
        openAdd() { this.editId = null; this.form = { nama: '', telepon: '', alamat: '', tipe: 'retail' }; this.showModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        openEdit(s) { this.editId = s.id; this.form = { nama: s.nama, telepon: s.telpon||'', alamat: s.alamat||'', tipe: s.tipe||'retail' }; this.showModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        async save() {
            if (!this.form.nama) return; this.saving = true;
            try {
                const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
                if (this.editId) { await axios.put(`${window.API_BASE_URL}/master/pembeli/` + this.editId, this.form, { headers }); }
                else { await axios.post(`${window.API_BASE_URL}/master/pembeli`, this.form, { headers }); }
                iziToast.success({ title: 'Berhasil', message: 'Data tersimpan', position: 'topRight' }); this.showModal = false; await this.load();
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
            this.saving = false;
        },
        async deleteItem(id) {
            if (!await confirm('Hapus pembeli ini?')) return;
            const token = localStorage.getItem('token');
            await axios.delete(`${window.API_BASE_URL}/master/pembeli/` + id, { headers: { Authorization: 'Bearer ' + token } });
            iziToast.success({ title: 'Berhasil', message: 'Pembeli dihapus', position: 'topRight' }); await this.load();
        }
    };
}
</script>
JS;
?>