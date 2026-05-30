<?php ?>
<div x-data="jenisIkanPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Jenis Ikan</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola kategori/jenis ikan yang tersedia</p>
        </div>
        <button @click="openAdd()"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"
            x-show="['super_admin','admin'].includes(user.role)">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Jenis Ikan
        </button>
    </div>

    <!-- Search -->
    <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-4 mb-6">
        <div class="flex gap-3">
            <input type="text" x-model="search" placeholder="Cari jenis ikan..."
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white flex-1"
                @input="filterList()">
            <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                <span x-text="filtered.length + ' data'"></span>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading"
        class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-12 text-center text-slate-500 dark:text-slate-400">
        <p class="text-sm">Memuat data...</p>
    </div>

    <!-- Grid Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4" x-show="!loading" x-cloak>
        <template x-if="filtered.length === 0">
            <div
                class="col-span-full rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-10 text-center text-slate-500 dark:text-slate-400">
                <i data-lucide="fish" class="w-10 h-10 mx-auto mb-2 opacity-30"></i>
                <p>Belum ada jenis ikan</p>
            </div>
        </template>
        <template x-for="jenis in filtered" :key="jenis.id">
            <div
                class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 p-4 text-center group hover:shadow-md transition-shadow">
                <div
                    class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center text-xl bg-blue-100 dark:bg-blue-900/30">
                    🐟
                </div>
                <p class="font-semibold text-sm mb-1 truncate text-slate-900 dark:text-slate-100" x-text="jenis.nama">
                </p>
                <p class="text-xs mb-3 text-slate-500 dark:text-slate-400"
                    x-text="jenis.deskripsi || 'Tidak ada deskripsi'"></p>
                <div class="flex gap-1.5 justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                    x-show="['super_admin','admin'].includes(user.role)">
                    <button @click="openEdit(jenis)"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-1.5 py-1.5 text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                        title="Edit">
                        <i data-lucide="pencil" class="w-3 h-3"></i>
                    </button>
                    <button @click="deleteJenis(jenis.id)"
                        class="inline-flex items-center justify-center rounded-lg border border-rose-300 bg-rose-50 px-1.5 py-1.5 text-rose-700 shadow-sm transition hover:bg-rose-100 dark:border-rose-700 dark:bg-rose-900/20 dark:text-rose-400"
                        title="Hapus">
                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Table View -->
    <div class="rounded-lg border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800 mt-6"
        x-show="!loading" x-cloak>
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <h3 class="font-semibold text-sm text-slate-900 dark:text-slate-100">Daftar Lengkap</h3>
            <span class="text-xs text-slate-500 dark:text-slate-400" x-text="filtered.length + ' total jenis'"></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Jenis Ikan</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Produk</th>
                        <th x-show="['super_admin','admin'].includes(user.role)">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="5" class="text-center py-8 text-slate-500 dark:text-slate-400">Tidak ada data
                            </td>
                        </tr>
                    </template>
                    <template x-for="(jenis, idx) in filtered" :key="jenis.id">
                        <tr>
                            <td><span class="text-sm text-slate-900 dark:text-slate-100" x-text="idx + 1"></span></td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">🐟</span>
                                    <span class="font-medium text-sm" x-text="jenis.nama"></span>
                                </div>
                            </td>
                            <td><span class="text-sm" style="color:var(--text-secondary)"
                                    x-text="jenis.deskripsi || '-'"></span></td>
                            <td>
                                <span class=\"inline-flex items-center rounded-full bg-sky-100 px-2 py-1 text-xs
                                    font-semibold text-sky-700 dark:bg-sky-700/30 dark:text-sky-400\"
                                    x-text=\"(jenis.jumlah_produk || 0) + ' produk' \"></span>
                            </td>
                            <td x-show=\"['super_admin','admin'].includes(user.role)\">
                                <div class=\"flex gap-1.5\">\n <button @click=\"openEdit(jenis)\" class=\"inline-flex
                                        items-center justify-center rounded-lg border border-slate-300 bg-white px-2
                                        py-1.5 text-slate-700 shadow-sm transition hover:bg-slate-50
                                        dark:border-slate-600 dark:bg-slate-700 dark:text-white\" title=\"Edit\">\n <i
                                            data-lucide=\"pencil\" class=\"w-3.5 h-3.5\"></i>\n </button>\n <button
                                        @click=\"deleteJenis(jenis.id)\" class=\"inline-flex items-center justify-center
                                        rounded-lg border border-rose-300 bg-rose-50 px-2 py-1.5 text-rose-700 shadow-sm
                                        transition hover:bg-rose-100 dark:border-rose-700 dark:bg-rose-900/20
                                        dark:text-rose-400\" title=\"Hapus\">\n <i data-lucide=\"trash-2\" class=\"w-3.5
                                            h-3.5\"></i>\n </button>\n </div>\n
                            </td>"
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Add/Edit -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6" x-show="showModal"
        @click.self="showModal = false" x-cloak>
        <div
            class="w-full max-w-md rounded-lg border border-slate-300 bg-white shadow-xl dark:border-slate-600 dark:bg-slate-800">
            <div
                class="border-b border-slate-200 dark:border-slate-700 px-4 py-3 sm:flex sm:items-center sm:justify-between">
                <h3 class="font-bold text-lg" x-text="editMode ? 'Edit Jenis Ikan' : 'Tambah Jenis Ikan'"></h3>
                <button @click="showModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="save()" class="px-4 py-4 sm:p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nama Jenis Ikan <span
                            class="text-red-500">*</span></label>
                    <input type="text" x-model="form.nama"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        placeholder="cth: Ikan Kerapu, Ikan Tuna, Udang Vannamei..." required autofocus>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Deskripsi
                        (opsional)</label>
                    <textarea x-model="form.deskripsi"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        rows="1" placeholder="Keterangan singkat..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Master Atribut: Size
                        (Pisahkan dengan koma)</label>
                    <input type="text" x-model="form.allowed_sizes"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        placeholder="cth: 200/300, 300/500, 1 Up, Size 10, Size 20, Polos">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Master Atribut: Grade
                        (Pisahkan dengan koma)</label>
                    <input type="text" x-model="form.allowed_grades"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        placeholder="cth: Grade A - Beku Kapal, Grade B - Beku Darat, Grade C - AC">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Master Atribut: Asal
                        Capture (Pisahkan dengan koma)</label>
                    <input type="text" x-model="form.allowed_origins"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        placeholder="cth: Bitung, Banda, Makassar, Ambon">
                </div>
                <div
                    class="border-t border-slate-200 dark:border-slate-700 px-4 py-3 sm:px-6 sm:py-4 flex gap-3 justify-end">
                    <button type="button" @click="showModal = false"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600">Batal</button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"
                        :disabled="submitting">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span x-text="submitting ? 'Menyimpan...' : (editMode ? 'Update' : 'Simpan')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php $scripts = <<<'JS'
<script>
function jenisIkanPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        loading: true,
        list: [],
        filtered: [],
        search: '',
        showModal: false,
        editMode: false,
        submitting: false,
        editId: null,
        form: { nama: '', deskripsi: '' },

        async init() {
            await this.loadData();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadData() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get(`${window.API_BASE_URL}/master/jenis-ikan`, { headers: { Authorization: 'Bearer '+token } });
                this.list = res.data?.data || [];
                this.filterList();
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal memuat data', position: 'topRight' }); }
            this.loading = false;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        filterList() {
            const q = this.search.toLowerCase();
            this.filtered = q ? this.list.filter(j => (j.nama||'').toLowerCase().includes(q)) : [...this.list];
        },

        openAdd() {
            this.editMode = false;
            this.editId = null;
            this.form = { nama: '', deskripsi: '', allowed_sizes: '', allowed_grades: '', allowed_origins: '' };
            this.showModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        openEdit(jenis) {
            this.editMode = true;
            this.editId = jenis.id;
            this.form = { 
                nama: jenis.nama, 
                deskripsi: jenis.deskripsi || '', 
                allowed_sizes: jenis.allowed_sizes || '', 
                allowed_grades: jenis.allowed_grades || '', 
                allowed_origins: jenis.allowed_origins || '' 
            };
            this.showModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async save() {
            if (!this.form.nama.trim()) {
                iziToast.warning({ title: 'Perhatian', message: 'Nama jenis ikan wajib diisi', position: 'topRight' });
                return;
            }
            this.submitting = true;
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer '+token };
                if (this.editMode) {
                    await axios.put(`${window.API_BASE_URL}/master/jenis-ikan/` + this.editId, this.form, { headers });
                    iziToast.success({ title: 'Berhasil', message: 'Jenis ikan diperbarui!', position: 'topRight' });
                } else {
                    await axios.post(`${window.API_BASE_URL}/master/jenis-ikan`, this.form, { headers });
                    iziToast.success({ title: 'Berhasil', message: 'Jenis ikan ditambahkan!', position: 'topRight' });
                }
                this.showModal = false;
                await this.loadData();
            } catch(e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal menyimpan', position: 'topRight' });
            }
            this.submitting = false;
        },

        async deleteJenis(id) {
            if (!await confirm('Hapus jenis ikan ini? Data yang terkait mungkin terpengaruh.')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.delete(`${window.API_BASE_URL}/master/jenis-ikan/` + id, { headers: { Authorization: 'Bearer '+token } });
                iziToast.success({ title: 'Berhasil', message: 'Jenis ikan dihapus', position: 'topRight' });
                await this.loadData();
            } catch(e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal menghapus', position: 'topRight' });
            }
        }
    };
}
</script>
JS;
?>