<?php
$initialJenisIkan = \App\Utils\Database::fetchAll(
    "SELECT ji.*, (SELECT COUNT(*) FROM produk p WHERE p.id_jenis_ikan = ji.id AND p.is_active = 1) AS jumlah_produk
     FROM `jenis_ikan` ji
     WHERE ji.is_active = 1
     ORDER BY ji.nama ASC"
);
?>
<div class="js-fallback">
    <div class="card mb-4">
        <div class="p-4 border-b flex items-center justify-between" style="border-color:var(--border-color)">
            <h3 class="font-semibold text-sm">Jenis Ikan</h3>
            <span class="text-xs" style="color:var(--text-secondary)"><?= count($initialJenisIkan) ?> data</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 p-4">
            <?php foreach ($initialJenisIkan as $row): ?>
                <div class="card p-4 text-center">
                    <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center text-xl"
                        style="background: var(--color-primary-light)">🐟</div>
                    <p class="font-semibold text-sm mb-1 truncate">
                        <?= htmlspecialchars((string) ($row['nama'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-xs mb-2" style="color:var(--text-secondary)">
                        <?= htmlspecialchars((string) ($row['deskripsi'] ?? 'Tidak ada deskripsi'), ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <span class="badge badge-info"><?= (int) ($row['jumlah_produk'] ?? 0) ?> produk</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<div x-data="jenisIkanPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Jenis Ikan</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Kelola kategori/jenis ikan yang tersedia</p>
        </div>
        <button @click="openAdd()" class="btn btn-primary" x-show="['bos','admin'].includes(user.role)">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Jenis Ikan
        </button>
    </div>

    <!-- Search -->
    <div class="card p-4 mb-6">
        <div class="flex gap-3">
            <input type="text" x-model="search" placeholder="Cari jenis ikan..." class="form-input flex-1"
                @input="filterList()">
            <div class="flex items-center gap-2 text-sm" style="color:var(--text-secondary)">
                <span x-text="filtered.length + ' data'"></span>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="card p-12 text-center" style="color: var(--text-secondary)">
        <p class="text-sm">Memuat data...</p>
    </div>

    <!-- Grid Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4" x-show="!loading" x-cloak>
        <template x-if="filtered.length === 0">
            <div class="col-span-full card p-10 text-center" style="color:var(--text-secondary)">
                <i data-lucide="fish" class="w-10 h-10 mx-auto mb-2 opacity-30"></i>
                <p>Belum ada jenis ikan</p>
            </div>
        </template>
        <template x-for="jenis in filtered" :key="jenis.id">
            <div class="card p-4 text-center group hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center text-xl"
                    style="background: var(--color-primary-light)">
                    🐟
                </div>
                <p class="font-semibold text-sm mb-1 truncate" x-text="jenis.nama"></p>
                <p class="text-xs mb-3" style="color:var(--text-secondary)"
                    x-text="jenis.deskripsi || 'Tidak ada deskripsi'"></p>
                <div class="flex gap-1.5 justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                    x-show="['bos','admin'].includes(user.role)">
                    <button @click="openEdit(jenis)" class="btn btn-secondary p-1.5" title="Edit">
                        <i data-lucide="pencil" class="w-3 h-3"></i>
                    </button>
                    <button @click="deleteJenis(jenis.id)" class="btn btn-danger p-1.5" title="Hapus">
                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Table View -->
    <div class="card mt-6" x-show="!loading" x-cloak>
        <div class="p-4 border-b flex items-center justify-between" style="border-color:var(--border-color)">
            <h3 class="font-semibold text-sm">Daftar Lengkap</h3>
            <span class="text-xs" style="color:var(--text-secondary)" x-text="filtered.length + ' total jenis'"></span>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Jenis Ikan</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Produk</th>
                        <th x-show="['bos','admin'].includes(user.role)">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="5" class="text-center py-8" style="color:var(--text-secondary)">Tidak ada data
                            </td>
                        </tr>
                    </template>
                    <template x-for="(jenis, idx) in filtered" :key="jenis.id">
                        <tr>
                            <td class="text-sm" x-text="idx + 1"></td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">🐟</span>
                                    <span class="font-medium text-sm" x-text="jenis.nama"></span>
                                </div>
                            </td>
                            <td><span class="text-sm" style="color:var(--text-secondary)"
                                    x-text="jenis.deskripsi || '-'"></span></td>
                            <td>
                                <span class="badge badge-info" x-text="(jenis.jumlah_produk || 0) + ' produk'"></span>
                            </td>
                            <td x-show="['bos','admin'].includes(user.role)">
                                <div class="flex gap-1.5">
                                    <button @click="openEdit(jenis)" class="btn btn-secondary p-1.5" title="Edit">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button @click="deleteJenis(jenis.id)" class="btn btn-danger p-1.5" title="Hapus">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Add/Edit -->
    <div class="modal-overlay" x-show="showModal" @click.self="showModal = false" x-cloak>
        <div class="modal-box">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg" x-text="editMode ? 'Edit Jenis Ikan' : 'Tambah Jenis Ikan'"></h3>
                <button @click="showModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="save()">
                <div class="form-group">
                    <label class="form-label">Nama Jenis Ikan <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.nama" class="form-input"
                        placeholder="cth: Ikan Kerapu, Ikan Tuna, Udang Vannamei..." required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi (opsional)</label>
                    <textarea x-model="form.deskripsi" class="form-input" rows="2"
                        placeholder="Keterangan singkat..."></textarea>
                </div>
                <div class="flex gap-3 justify-end mt-6">
                    <button type="button" @click="showModal = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary" :disabled="submitting">
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
        list: <?= json_encode($initialJenisIkan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        filtered: <?= json_encode($initialJenisIkan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        search: '',
        showModal: false,
        editMode: false,
        submitting: false,
        editId: null,
        form: { nama: '', deskripsi: '' },

        async init() {
            try {
                await this.loadData();
                document.querySelectorAll('.js-fallback').forEach(el => el.style.display = 'none');
            } catch(e) {}
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadData() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/master/jenis-ikan', { headers: { Authorization: 'Bearer '+token } });
                this.list = res.data?.data || this.list;
                this.filterList();
            } catch(e) {
                iziToast.error({ title: 'Error', message: 'Gagal memuat data', position: 'topRight' });
                this.filterList();
            }
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
            this.form = { nama: '', deskripsi: '' };
            this.showModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        openEdit(jenis) {
            this.editMode = true;
            this.editId = jenis.id;
            this.form = { nama: jenis.nama, deskripsi: jenis.deskripsi || '' };
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
                    await axios.put('/peace_seafood/api/master/jenis-ikan/' + this.editId, this.form, { headers });
                    iziToast.success({ title: 'Berhasil', message: 'Jenis ikan diperbarui!', position: 'topRight' });
                } else {
                    await axios.post('/peace_seafood/api/master/jenis-ikan', this.form, { headers });
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
            if (!confirm('Hapus jenis ikan ini? Data yang terkait mungkin terpengaruh.')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.delete('/peace_seafood/api/master/jenis-ikan/' + id, { headers: { Authorization: 'Bearer '+token } });
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