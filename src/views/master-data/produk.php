<?php ?>
<div x-data="produkPage()" x-init="init()">
    <div class="flex items-center gap-4 mb-6">
        <a href="/peace_seafood/master-data" class="btn btn-secondary p-2"><i data-lucide="arrow-left"
                class="w-4 h-4"></i></a>
        <div class="flex-1">
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Data Produk</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Kelola produk, harga, dan stok minimum</p>
        </div>
        <button @click="openAdd()" class="btn btn-primary" x-show="['super_admin','admin'].includes(user.role)">
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
                        <tr :id="'produk-' + p.id" :data-highlight="'produk-' + p.id">
                            <td class="font-medium text-sm" x-text="p.nama"></td>
                            <td><span class="badge badge-info" x-text="p.nama_jenis"></span></td>
                            <td class="text-sm" x-text="'Rp ' + parseFloat(p.harga_beli||0).toLocaleString('id-ID')">
                            </td>
                            <td class="text-sm font-medium" style="color:var(--color-primary)"
                                x-text="'Rp ' + parseFloat(p.harga_jual||0).toLocaleString('id-ID')"></td>
                            <td class="text-sm" x-text="formatQty(p.stok_minimum, p.satuan)"></td>
                            <td>
                                <span class="font-semibold text-sm"
                                    :class="parseFloat(p.stok_qty||0) < parseFloat(p.stok_minimum||0) ? 'text-red-500' : 'text-green-500'"
                                    x-text="formatQty(p.stok_qty, p.satuan)"></span>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button @click="openEdit(p)" class="btn btn-secondary p-1.5"
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
                    <div class="form-group"><label class="form-label">Harga Beli</label><input type="text"
                            inputmode="numeric" :value="form.harga_beli"
                            @input="handleRupiahInput('harga_beli', $event)" class="form-input" placeholder="0"></div>
                    <div class="form-group"><label class="form-label">Harga Jual</label><input type="text"
                            inputmode="numeric" :value="form.harga_jual"
                            @input="handleRupiahInput('harga_jual', $event)" class="form-input" placeholder="0"></div>
                    <div class="form-group"><label class="form-label">Stok Minimum</label><input type="number"
                            x-model="form.stok_minimum" class="form-input" min="0" step="0.1"></div>
                    <input type="hidden" x-model="form.satuan">
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
<?php $scripts = '<script src="/peace_seafood/inline-assets/js/master-data/produk.js"></script>'; ?>