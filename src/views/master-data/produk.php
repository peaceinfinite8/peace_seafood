<?php
$initialProduk = \App\Utils\Database::fetchAll(
    "SELECT p.*, p.gambar, j.nama AS nama_jenis
     FROM produk p
     JOIN jenis_ikan j ON j.id = p.id_jenis_ikan
     WHERE p.is_active = 1
     ORDER BY p.nama"
);
?>
<div class="js-fallback">
    <div class="card mb-4">
        <div class="p-4 border-b flex items-center justify-between" style="border-color:var(--border-color)">
            <h3 class="font-semibold text-sm">Data Produk</h3>
            <span class="text-xs" style="color:var(--text-secondary)"><?= count($initialProduk) ?> data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Jenis Ikan</th>
                        <th>Harga Jual</th>
                        <th>Stok Saat Ini</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($initialProduk as $row): ?>
                        <tr>
                            <td class="font-medium text-sm cursor-pointer hover:opacity-80"
                                onclick='window.showProductDetail(<?= json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>)'>
                                <?= htmlspecialchars((string) ($row['nama'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td><span
                                    class="badge badge-info"><?= htmlspecialchars((string) ($row['nama_jenis'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td class="text-sm">Rp <?= number_format((float) ($row['harga_jual'] ?? 0), 0, ',', '.') ?></td>
                            <td class="text-sm"><?= number_format((float) ($row['stok_qty'] ?? 0), 1, ',', '.') ?> kg</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div x-data="produkPage()" x-init="init()">
    <div class="flex items-center gap-4 mb-6">
        <a href="/peace_seafood/master-data" class="btn btn-secondary p-2"><i data-lucide="arrow-left"
                class="w-4 h-4"></i></a>
        <div class="flex-1">
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Data Produk</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Kelola produk, harga, dan stok minimum</p>
        </div>
        <button @click="openAdd()" class="btn btn-primary" x-show="['bos','admin'].includes(user.role)">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Produk
        </button>
    </div>
    <div class="card p-4 mb-4"><input type="text" x-model="search" placeholder="Cari produk..."
            class="form-input max-w-sm"></div>
    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
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
                            <td colspan="7" class="text-center py-8" style="color:var(--text-secondary)">Tidak ada data
                            </td>
                        </tr>
                    </template>
                    <template x-for="p in filtered" :key="p.id">
                        <tr>
                            <td class="font-medium text-sm cursor-pointer hover:opacity-80"
                                @click="window.showProductDetail(p)">
                                <span x-text="p.nama"></span>
                            </td>
                            <td><span class="badge badge-info" x-text="p.nama_jenis"></span></td>
                            <td class="text-sm" x-text="'Rp ' + parseFloat(p.harga_beli||0).toLocaleString('id-ID')">
                            </td>
                            <td class="text-sm font-medium" style="color:var(--color-primary)"
                                x-text="'Rp ' + parseFloat(p.harga_jual||0).toLocaleString('id-ID')"></td>
                            <td class="text-sm" x-text="parseFloat(p.stok_minimum||0) + ' ' + (p.satuan||'kg')"></td>
                            <td>
                                <span class="font-semibold text-sm"
                                    :class="parseFloat(p.stok_qty||0) < parseFloat(p.stok_minimum||0) ? 'text-red-500' : 'text-green-500'"
                                    x-text="parseFloat(p.stok_qty||0) + ' ' + (p.satuan||'kg')"></span>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button @click="openEdit(p)" class="btn btn-secondary p-1.5"
                                        x-show="['bos','admin'].includes(user.role)"><i data-lucide="pencil"
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
    <div class="modal-overlay" x-show="showModal" @click.self="showModal = false" x-cloak>
        <div class="modal-box">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg" x-text="editId ? 'Edit Produk' : 'Tambah Produk'"></h3>
                <button @click="showModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="save()">
                <div class="form-group"><label class="form-label">Nama Produk *</label><input type="text"
                        x-model="form.nama" class="form-input" required></div>
                <div class="form-group">
                    <label class="form-label">Jenis Ikan *</label>
                    <select x-model="form.id_jenis_ikan" class="form-input" required>
                        <option value="">-- Pilih --</option>
                        <template x-for="j in jenisIkan" :key="j.id">
                            <option :value="j.id" x-text="j.nama"></option>
                        </template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group"><label class="form-label">Harga Beli</label><input type="number"
                            x-model="form.harga_beli" class="form-input" min="0" step="100"></div>
                    <div class="form-group"><label class="form-label">Harga Jual</label><input type="number"
                            x-model="form.harga_jual" class="form-input" min="0" step="100"></div>
                    <div class="form-group"><label class="form-label">Stok Minimum</label><input type="number"
                            x-model="form.stok_minimum" class="form-input" min="0" step="0.1"></div>
                    <div class="form-group">
                        <label class="form-label">Satuan</label>
                        <select x-model="form.satuan" class="form-input">
                            <option value="kg">kg</option>
                            <option value="ekor">ekor</option>
                            <option value="box">box</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama File Gambar</label>
                    <input type="text" x-model="form.gambar" class="form-input" placeholder="contoh: lele.webp">
                    <p class="text-xs mt-1" style="color:var(--text-secondary)">File gambar tersedia: cumi.webp, kakap_merah.webp, kakap_merah_beku.webp, lele.webp, nila.webp, tenggiri.webp, tuna.webp, udang_windu.webp</p>
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
function produkPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        items: <?= json_encode($initialProduk, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>, jenisIkan: [], search: '', showModal: false, editId: null, saving: false,
        form: { nama: '', id_jenis_ikan: '', harga_beli: 0, harga_jual: 0, stok_minimum: 0, satuan: 'kg', gambar: '' },
        get filtered() { const q = this.search.toLowerCase(); return this.items.filter(p => !q || p.nama?.toLowerCase().includes(q) || p.nama_jenis?.toLowerCase().includes(q)); },
        async init() {
            try {
                const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
                const [prodRes, jenisRes] = await Promise.all([
                    axios.get('/peace_seafood/api/master/produk', { headers }),
                    axios.get('/peace_seafood/api/master/jenis-ikan', { headers }),
                ]);
                this.items = prodRes.data?.data || this.items; this.jenisIkan = jenisRes.data?.data || [];
                document.querySelectorAll('.js-fallback').forEach(el => el.style.display = 'none');
            } catch(e) {}
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },
        openAdd() { this.editId = null; this.form = { nama: '', id_jenis_ikan: '', harga_beli: 0, harga_jual: 0, stok_minimum: 0, satuan: 'kg', gambar: '' }; this.showModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        openEdit(p) { this.editId = p.id; this.form = { nama: p.nama, id_jenis_ikan: p.id_jenis_ikan, harga_beli: p.harga_beli, harga_jual: p.harga_jual, stok_minimum: p.stok_minimum||0, satuan: p.satuan||'kg', gambar: p.gambar||'' }; this.showModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        async save() {
            if (!this.form.nama || !this.form.id_jenis_ikan) return; this.saving = true;
            try {
                const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
                if (this.editId) { await axios.put('/peace_seafood/api/master/produk/' + this.editId, this.form, { headers }); }
                else { await axios.post('/peace_seafood/api/master/produk', this.form, { headers }); }
                iziToast.success({ title: 'Berhasil', message: 'Data tersimpan', position: 'topRight' }); this.showModal = false;
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/master/produk', { headers: { Authorization: 'Bearer ' + token } });
                this.items = res.data?.data || [];
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
            this.saving = false;
        }
    };
}
</script>
JS;
?>