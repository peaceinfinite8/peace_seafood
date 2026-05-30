<?php ?>
<div x-data="jenisIkanPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Jenis Ikan</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Kelola kategori/jenis ikan yang tersedia</p>
        </div>
        <button @click="openAdd()" class="btn btn-primary" x-show="['super_admin','admin'].includes(user.role)">
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
                    x-show="['super_admin','admin'].includes(user.role)">
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
                        <th x-show="['super_admin','admin'].includes(user.role)">Aksi</th>
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
                            <td x-show="['super_admin','admin'].includes(user.role)">
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
                    <textarea x-model="form.deskripsi" class="form-input" rows="1"
                        placeholder="Keterangan singkat..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Master Atribut: Size (Pisahkan dengan koma)</label>
                    <input type="text" x-model="form.allowed_sizes" class="form-input"
                        placeholder="cth: 200/300, 300/500, 1 Up, Size 10, Size 20, Polos">
                </div>
                <div class="form-group">
                    <label class="form-label">Master Atribut: Grade (Pisahkan dengan koma)</label>
                    <input type="text" x-model="form.allowed_grades" class="form-input"
                        placeholder="cth: Grade A - Beku Kapal, Grade B - Beku Darat, Grade C - AC">
                </div>
                <div class="form-group">
                    <label class="form-label">Master Atribut: Asal Capture (Pisahkan dengan koma)</label>
                    <input type="text" x-model="form.allowed_origins" class="form-input"
                        placeholder="cth: Bitung, Banda, Makassar, Ambon">
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

<?php $scripts = '<script src="/peace_seafood/inline-assets/js/master-data/jenis-ikan.js"></script>'; ?>