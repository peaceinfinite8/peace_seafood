<?php
$initialPembeli = \App\Utils\Database::fetchAll(
    "SELECT *, telpon AS telepon FROM `pembeli` WHERE `is_active` = 1 ORDER BY `nama` ASC"
);
?>
<div class="js-fallback">
    <div class="card mb-4">
        <div class="p-4 border-b flex items-center justify-between" style="border-color:var(--border-color)">
            <h3 class="font-semibold text-sm">Data Pembeli</h3>
            <span class="text-xs" style="color:var(--text-secondary)"><?= count($initialPembeli) ?> data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Tipe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($initialPembeli as $row): ?>
                        <tr>
                            <td class="font-medium text-sm">
                                <?= htmlspecialchars((string) ($row['nama'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-sm">
                                <?= htmlspecialchars((string) ($row['telepon'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-sm">
                                <?= htmlspecialchars((string) ($row['alamat'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-sm">
                                <?= htmlspecialchars(strtoupper((string) ($row['tipe'] ?? 'umum')), ENT_QUOTES, 'UTF-8') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div x-data="pembeliPage()" x-init="init()">
    <div class="flex items-center gap-4 mb-6">
        <a href="/peace_seafood/master-data" class="btn btn-secondary p-2"><i data-lucide="arrow-left"
                class="w-4 h-4"></i></a>
        <div class="flex-1">
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Data Pembeli</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Kelola data pelanggan / buyer</p>
        </div>
        <button @click="openAdd()" class="btn btn-primary"><i data-lucide="plus" class="w-4 h-4"></i> Tambah
            Pembeli</button>
    </div>
    <div class="card p-4 mb-4"><input type="text" x-model="search" placeholder="Cari pembeli..."
            class="form-input max-w-sm"></div>
    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
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
                            <td colspan="5" class="text-center py-8" style="color:var(--text-secondary)">Tidak ada data
                            </td>
                        </tr>
                    </template>
                    <template x-for="s in filtered" :key="s.id">
                        <tr>
                            <td class="font-medium text-sm" x-text="s.nama"></td>
                            <td class="text-sm" x-text="s.telepon || '-'"></td>
                            <td class="text-sm" x-text="s.alamat || '-'"></td>
                            <td><span class="badge" :class="s.tipe === 'langganan' ? 'badge-success' : 'badge-gray'"
                                    x-text="(s.tipe||'umum').toUpperCase()"></span></td>
                            <td>
                                <div class="flex gap-2">
                                    <button @click="openEdit(s)" class="btn btn-secondary p-1.5"><i data-lucide="pencil"
                                            class="w-3.5 h-3.5"></i></button>
                                    <button @click="deleteItem(s.id)" class="btn btn-danger p-1.5"><i
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
    <div class="modal-overlay" x-show="showModal" @click.self="showModal = false" x-cloak>
        <div class="modal-box">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg" x-text="editId ? 'Edit Pembeli' : 'Tambah Pembeli'"></h3>
                <button @click="showModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="save()">
                <div class="form-group"><label class="form-label">Nama *</label><input type="text" x-model="form.nama"
                        class="form-input" required></div>
                <div class="form-group"><label class="form-label">Telepon</label><input type="text"
                        x-model="form.telepon" class="form-input"></div>
                <div class="form-group"><label class="form-label">Alamat</label><textarea x-model="form.alamat"
                        class="form-input" rows="2"></textarea></div>
                <div class="form-group">
                    <label class="form-label">Tipe</label>
                    <select x-model="form.tipe" class="form-input">
                        <option value="umum">Umum</option>
                        <option value="langganan">Langganan</option>
                        <option value="grosir">Grosir</option>
                    </select>
                </div>
                <div class="flex gap-3 mt-4">
                    <button type="submit" class="btn btn-primary" :disabled="saving"
                        x-text="saving ? 'Menyimpan...' : (editId ? 'Update' : 'Simpan')"></button>
                    <button type="button" @click="showModal = false" class="btn btn-secondary">Batal</button>
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
        items: <?= json_encode($initialPembeli, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>, search: '', showModal: false, editId: null, saving: false,
        form: { nama: '', telepon: '', alamat: '', tipe: 'umum' },
        get filtered() { const q = this.search.toLowerCase(); return this.items.filter(s => !q || s.nama?.toLowerCase().includes(q)); },
        async init() { try { await this.load(); document.querySelectorAll('.js-fallback').forEach(el => el.style.display = 'none'); } catch(e) {} this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        async load() { const token = localStorage.getItem('token'); const res = await axios.get('/peace_seafood/api/master/pembeli', { headers: { Authorization: 'Bearer ' + token } }); this.items = res.data?.data || this.items; },
        openAdd() { this.editId = null; this.form = { nama: '', telepon: '', alamat: '', tipe: 'umum' }; this.showModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        openEdit(s) { this.editId = s.id; this.form = { nama: s.nama, telepon: s.telepon||'', alamat: s.alamat||'', tipe: s.tipe||'umum' }; this.showModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        async save() {
            if (!this.form.nama) return; this.saving = true;
            try {
                const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
                if (this.editId) { await axios.put('/peace_seafood/api/master/pembeli/' + this.editId, this.form, { headers }); }
                else { await axios.post('/peace_seafood/api/master/pembeli', this.form, { headers }); }
                iziToast.success({ title: 'Berhasil', message: 'Data tersimpan', position: 'topRight' }); this.showModal = false; await this.load();
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
            this.saving = false;
        },
        async deleteItem(id) {
            if (!confirm('Hapus pembeli ini?')) return;
            const token = localStorage.getItem('token');
            await axios.delete('/peace_seafood/api/master/pembeli/' + id, { headers: { Authorization: 'Bearer ' + token } });
            iziToast.success({ title: 'Berhasil', message: 'Pembeli dihapus', position: 'topRight' }); await this.load();
        }
    };
}
</script>
JS;
?>