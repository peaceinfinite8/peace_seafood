<?php ?>
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
                            <td class="text-sm" x-text="s.telpon || '-'"></td>
                            <td class="text-sm" x-text="s.alamat || '-'"></td>
                            <td><span class="badge"
                                    :class="s.tipe === 'bulk' ? 'badge-primary' : (s.tipe === 'reseller' ? 'badge-success' : 'badge-gray')"
                                    x-text="(s.tipe||'retail').toUpperCase()"></span></td>
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
                        <option value="retail">Retail</option>
                        <option value="bulk">Bulk</option>
                        <option value="reseller">Reseller</option>
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
<?php $scripts = '<script src="/peace_seafood/inline-assets/js/master-data/pembeli.js"></script>'; ?>