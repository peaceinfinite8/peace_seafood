<?php
/**
 * Reusable CRUD table template for master data
 * This renders a full CRUD page for a given entity
 */
?>
<div x-data="supplierPage()" x-init="init()">

    <div class="flex items-center gap-4 mb-6">
        <a href="/peace_seafood/master-data" class="btn btn-secondary p-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <div class="flex-1">
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Data Supplier</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Kelola data pemasok ikan</p>
        </div>
        <button @click="openAdd()" class="btn btn-primary" x-show="['super_admin','admin'].includes(user.role)">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Supplier
        </button>
    </div>

    <!-- Search -->
    <div class="card p-4 mb-4">
        <input type="text" x-model="search" placeholder="Cari supplier..." class="form-input max-w-sm">
    </div>

    <!-- Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr><th>Nama</th><th>Telepon</th><th>Alamat</th><th>Email</th><th x-show="['super_admin','admin'].includes(user.role)">Aksi</th></tr>
                </thead>
                <tbody>
                    <template x-if="filtered.length === 0">
                        <tr><td colspan="5" class="text-center py-8" style="color:var(--text-secondary)">Tidak ada data</td></tr>
                    </template>
                    <template x-for="s in filtered" :key="s.id">
                        <tr>
                            <td class="font-medium text-sm" x-text="s.nama"></td>
                            <td class="text-sm" x-text="s.telpon || '-'"></td>
                            <td class="text-sm" x-text="s.alamat || '-'"></td>
                            <td class="text-sm" x-text="s.email || '-'"></td>
                            <td x-show="['super_admin','admin'].includes(user.role)">
                                <div class="flex gap-2">
                                    <button @click="openEdit(s)" class="btn btn-secondary p-1.5" title="Edit">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button @click="deleteItem(s.id)" class="btn btn-danger p-1.5" title="Hapus">
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

    <!-- Modal -->
    <div class="modal-overlay" x-show="showModal" @click.self="showModal = false" x-cloak>
        <div class="modal-box">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg" x-text="editId ? 'Edit Supplier' : 'Tambah Supplier'"></h3>
                <button @click="showModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="save()">
                <div class="form-group">
                    <label class="form-label">Nama <span style="color: var(--color-danger)">*</span></label>
                    <input type="text" x-model="form.nama" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Telepon</label>
                    <input type="text" x-model="form.telepon" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat</label>
                    <textarea x-model="form.alamat" class="form-input" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" x-model="form.email" class="form-input">
                </div>
                <div class="flex gap-3 mt-4">
                    <button type="submit" class="btn btn-primary" :disabled="saving">
                        <span x-text="saving ? 'Menyimpan...' : (editId ? 'Update' : 'Simpan')"></span>
                    </button>
                    <button type="button" @click="showModal = false" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $scripts = <<<'JS'
<script>
function supplierPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        items: [], search: '', showModal: false, editId: null, saving: false,
        form: { nama: '', telepon: '', alamat: '', email: '' },

        get filtered() { const q = this.search.toLowerCase(); return this.items.filter(s => !q || s.nama?.toLowerCase().includes(q)); },

        async init() {
            await this.load();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async load() {
            const token = localStorage.getItem('token');
            const res = await axios.get('/peace_seafood/api/master/supplier', { headers: { Authorization: 'Bearer ' + token } });
            this.items = res.data?.data || [];
        },

        openAdd() { this.editId = null; this.form = { nama: '', telepon: '', alamat: '', email: '' }; this.showModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        openEdit(s) { this.editId = s.id; this.form = { nama: s.nama, telepon: s.telpon||'', alamat: s.alamat||'', email: s.email||'' }; this.showModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },

        async save() {
            if (!this.form.nama) return;
            this.saving = true;
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                if (this.editId) {
                    await axios.put('/peace_seafood/api/master/supplier/' + this.editId, this.form, { headers });
                } else {
                    await axios.post('/peace_seafood/api/master/supplier', this.form, { headers });
                }
                iziToast.success({ title: 'Berhasil', message: 'Data tersimpan', position: 'topRight' });
                this.showModal = false;
                await this.load();
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
            this.saving = false;
        },

        async deleteItem(id) {
            if (!await confirm('Hapus supplier ini?')) return;
            const token = localStorage.getItem('token');
            await axios.delete('/peace_seafood/api/master/supplier/' + id, { headers: { Authorization: 'Bearer ' + token } });
            iziToast.success({ title: 'Berhasil', message: 'Supplier dihapus', position: 'topRight' });
            await this.load();
        }
    };
}
</script>
JS;
?>
