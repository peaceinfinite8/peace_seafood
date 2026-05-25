<?php ?>
<div x-data="settingsPage()" x-init="init()" class="space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
            <h2 class="text-2xl font-bold tracking-tight" style="color: var(--text-primary)">Pengaturan Sistem</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Konfigurasi global platform, akun pengguna, cabang gudang, dan pencadangan database</p>
        </div>
    </div>

    <!-- Main Grid Split Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">

        <!-- Left Sidebar Navigation -->
        <div class="lg:col-span-1 space-y-2">
            <div class="card p-3 space-y-1 shadow-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl">
                <h3 class="text-2xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-3 py-2">Navigasi Pengaturan</h3>

                <button @click="tab = 'umum'"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200"
                    :class="tab === 'umum' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/10' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <span>Umum</span>
                </button>

                <button @click="tab = 'rekening'"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200"
                    :class="tab === 'rekening' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/10' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                    <span>Rekening Bank</span>
                </button>

                <button @click="tab = 'users'"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200"
                    :class="tab === 'users' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/10' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>Manajemen User</span>
                </button>

                <button @click="tab = 'gudang'"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200"
                    :class="tab === 'gudang' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/10' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    <span>Kelola Gudang</span>
                </button>
            </div>
        </div>

        <!-- Right Workspace Panel -->
        <div class="lg:col-span-3">

            <!-- Tab Umum -->
            <div x-show="tab === 'umum'" class="card p-6 shadow-sm rounded-xl space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-lg font-bold" style="color: var(--text-primary)">Pengaturan Aplikasi</h3>
                        <p class="text-xs" style="color: var(--text-secondary)">Konfigurasi operasional gudang, batas minimal stok, susut alerts, dan logo brand</p>
                    </div>
                </div>

                <!-- Dedicated Logo Card at the Top (Always Visible) -->
                <div class="p-5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-4 flex-1">
                        <div class="relative w-16 h-16 rounded-xl border border-slate-300 dark:border-slate-700 flex items-center justify-center overflow-hidden bg-slate-900 shadow-inner group">
                            <template x-if="logoBase64">
                                <img :src="logoBase64" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!logoBase64">
                                <span class="text-xl font-black text-blue-500" x-text="getCompanyInitial()"></span>
                            </template>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold" style="color: var(--text-primary)">Logo Perusahaan / Gudang</h4>
                            <p class="text-xs" style="color: var(--text-secondary)">Logo ini akan tampil di navigasi sidebar utama dan cetakan nota Anda.</p>
                        </div>
                    </div>
                    <div>
                        <button type="button" @click="triggerLogoUpload()" class="btn btn-primary py-2 px-4 text-xs flex items-center gap-2 shadow-lg shadow-blue-500/10">
                            <i data-lucide="camera" class="w-4 h-4"></i>
                            <span>Pilih & Potong Logo Baru</span>
                        </button>
                        <input type="file" id="logo-file-input" @change="onLogoSelected($event)" accept="image/*" class="hidden">
                    </div>
                </div>

                <!-- Empty state -->
                <template x-if="settings.length === 0">
                    <p class="text-sm py-4" style="color: var(--text-secondary)">Memuat pengaturan...</p>
                </template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <template x-for="setting in settings.filter(x => x.kunci !== 'company_logo_base64' && x.kunci !== 'company_logo_initial')" :key="setting.kunci">
                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex flex-col justify-between">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--text-primary)" x-text="setting.label || setting.kunci"></label>

                                <!-- Toggle (boolean: 0/1) -->
                                <template x-if="isToggle(setting.kunci)">
                                    <div class="flex items-center gap-3 mt-1.5">
                                        <button type="button"
                                            @click="setting.nilai = setting.nilai == '1' ? '0' : '1'; saveSetting(setting)"
                                            class="settings-toggle"
                                            :class="setting.nilai == '1' ? 'settings-toggle--on' : 'settings-toggle--off'"
                                            :aria-checked="setting.nilai == '1'"
                                            role="switch">
                                            <span class="settings-toggle__thumb"></span>
                                        </button>
                                        <span class="text-xs font-semibold"
                                            :style="setting.nilai == '1' ? 'color:var(--color-success)' : 'color:var(--text-secondary)'"
                                            x-text="setting.nilai == '1' ? 'Aktif' : 'Nonaktif'">
                                        </span>
                                    </div>
                                </template>

                                <!-- Select dropdown (enum) -->
                                <template x-if="isSelect(setting.kunci)">
                                    <div class="flex gap-2 mt-1.5">
                                        <select x-model="setting.nilai" class="form-input flex-1 py-1 px-2 text-xs">
                                            <template x-for="opt in getSelectOptions(setting.kunci)" :key="opt.value">
                                                <option :value="opt.value" x-text="opt.label"></option>
                                            </template>
                                        </select>
                                        <button @click="saveSetting(setting)" class="btn btn-primary p-2">
                                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </template>

                                <!-- Number / text input (default) -->
                                <template x-if="!isToggle(setting.kunci) && !isSelect(setting.kunci)">
                                    <div class="flex gap-2 mt-1.5">
                                        <input type="text"
                                            x-show="isNumber(setting.kunci)"
                                            :value="formatNumberDot(setting.nilai)"
                                            @input="setting.nilai = $event.target.value.replace(/\D/g, ''); $event.target.value = formatNumberDot(setting.nilai)"
                                            class="form-input flex-1 py-1 px-2 text-xs">
                                        <input type="text"
                                            x-show="!isNumber(setting.kunci)"
                                            x-model="setting.nilai"
                                            class="form-input flex-1 py-1 px-2 text-xs">
                                        <button @click="saveSetting(setting)" class="btn btn-primary p-2">
                                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <p class="text-3xs mt-2" style="color: var(--text-secondary)" x-text="setting.keterangan || ''"></p>
                        </div>
                    </template>
                </div>

                <!-- Database Backup Component Section -->
                <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-800">
                    <h4 class="font-bold text-sm mb-2" style="color: var(--text-primary)">
                        <i data-lucide="database" class="w-4 h-4 inline mr-1 text-blue-500"></i>
                        Pencadangan Sistem & Database
                    </h4>
                    <p class="text-xs mb-4" style="color: var(--text-secondary)">
                        Ambil cadangan penuh seluruh data transaksi operasional, stok cakalang/seafood, data supplier, pembeli, dan logs keamanan ke berkas format SQL langsung yang dapat di-download ke komputer Anda.
                    </p>
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/30">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 rounded-full bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold" style="color: var(--text-primary)">Target Database: peace_seafood</p>
                                <p class="text-3xs text-slate-400 dark:text-slate-500">Koneksi: Aktif & Terlindungi</p>
                            </div>
                        </div>
                        <button type="button" @click="runBackup()" class="btn btn-primary flex items-center gap-2" :disabled="backingUp">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            <span x-text="backingUp ? 'Mengunduh SQL...' : 'Cadangkan Database Sekarang'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab Rekening Bank -->
            <div x-show="tab === 'rekening'" class="space-y-4" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold" style="color: var(--text-primary)">Rekening Bank BOS</h3>
                        <p class="text-xs" style="color: var(--text-secondary)">Kelola rekening tujuan transfer pembayaran nota penjualan timbangan lapangan</p>
                    </div>
                    <button @click="openAddBank()" class="btn btn-primary flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Tambah Rekening</span>
                    </button>
                </div>

                <!-- Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="b in bankAccounts" :key="b.id">
                        <div class="bank-card relative overflow-hidden border transition-all duration-300 group hover:shadow-md"
                            :class="b.is_active ? 'bank-card--active' : 'bank-card--inactive'">

                            <!-- Background pattern -->
                            <div class="absolute -right-6 -bottom-6 w-20 h-20 rounded-full opacity-10 bg-white"></div>

                            <div class="flex justify-between items-start relative z-10">
                                <div class="space-y-4 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xs font-extrabold uppercase tracking-widest px-2 py-0.5 rounded"
                                            :class="b.is_active ? 'bg-blue-600/30 text-blue-400' : 'bg-slate-600/30 text-slate-400'"
                                            x-text="b.bank_name"></span>
                                    </div>
                                    <div>
                                        <h4 class="bank-card__number text-lg font-mono font-bold tracking-wider" x-text="b.account_number"></h4>
                                        <p class="bank-card__label text-3xs mt-1 uppercase tracking-wider">Pemilik Rekening</p>
                                        <p class="bank-card__name text-sm font-semibold truncate" x-text="b.account_name"></p>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end justify-between h-full min-h-[90px]">
                                    <span class="badge py-0.5 px-1.5 text-3xs font-extrabold" :class="b.is_active ? 'badge-success' : 'badge-gray'" x-text="b.is_active ? 'AKTIF' : 'NON-AKTIF'"></span>
                                    <div class="flex gap-1.5 mt-4">
                                        <button @click="openEditBank(b)" class="bank-card__action p-1.5 rounded-lg transition-colors duration-200">
                                            <i data-lucide="pencil" class="w-3 h-3"></i>
                                        </button>
                                        <button @click="deleteBank(b.id)" class="bank-card__action bank-card__action--danger p-1.5 rounded-lg transition-colors duration-200">
                                            <i data-lucide="trash-2" class="w-3 h-3"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Tab Users -->
            <div x-show="tab === 'users'" class="space-y-4" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold" style="color: var(--text-primary)">Manajemen User</h3>
                        <p class="text-xs" style="color: var(--text-secondary)">Pemberian hak akses staf kasir, lapangan (checker), dan bos eksekutif</p>
                    </div>
                    <button @click="openAddUser()" class="btn btn-primary flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Tambah User</span>
                    </button>
                </div>

                <div class="card overflow-hidden shadow-sm rounded-xl">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nama Pengguna</th>
                                    <th>Alamat Email</th>
                                    <th>Role / Jabatan</th>
                                    <th>Alokasi Gudang</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="u in users" :key="u.id">
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                        <td>
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center font-bold text-xs" style="color: var(--color-primary)">
                                                    <span x-text="u.name ? u.name.charAt(0).toUpperCase() : 'U'"></span>
                                                </div>
                                                <span class="font-bold text-sm text-slate-800 dark:text-slate-200" x-text="u.name"></span>
                                            </div>
                                        </td>
                                        <td class="text-sm" x-text="u.email"></td>
                                        <td>
                                            <span class="badge uppercase tracking-wider text-4xs font-black py-0.5 px-2" :class="u.role==='super_admin'?'badge-danger':u.role==='bos'?'badge-danger':u.role==='admin'?'badge-warning':'badge-info'" x-text="u.role"></span>
                                        </td>
                                        <td class="text-sm text-slate-600 dark:text-slate-400" x-text="u.nama_gudang || 'Semua Gudang'"></td>
                                        <td>
                                            <span class="badge text-4xs font-bold" :class="u.is_active?'badge-success':'badge-gray'" x-text="u.is_active?'AKTIF':'NONAKTIF'"></span>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <button @click="openEditUser(u)" class="btn btn-secondary p-1.5"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></button>
                                                <button @click="deleteUser(u.id)" class="btn btn-danger p-1.5" x-show="u.role !== 'bos' && u.role !== 'super_admin'"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab Gudang -->
            <div x-show="tab === 'gudang'" class="space-y-4" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold" style="color: var(--text-primary)">Kelola Cabang Gudang</h3>
                        <p class="text-xs" style="color: var(--text-secondary)">Manajemen cabang/gudang penampungan timbangan dan penanggung jawab</p>
                    </div>
                    <button @click="openAddGudang()" class="btn btn-primary flex items-center gap-2">
                        <i data-lucide="building-2" class="w-4 h-4"></i>
                        <span>Tambah Gudang</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="g in gudang" :key="g.id">
                        <div class="card p-5 border border-slate-200 dark:border-slate-800 transition-all duration-300 hover:shadow-md flex flex-col justify-between rounded-xl">
                            <div>
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                            <i data-lucide="building" class="w-4 h-4"></i>
                                        </div>
                                        <h3 class="font-bold text-base" style="color: var(--text-primary)" x-text="g.nama"></h3>
                                    </div>
                                    <span class="badge text-4xs font-bold" :class="g.is_active ? 'badge-success' : 'badge-gray'" x-text="g.is_active ? 'AKTIF' : 'NONAKTIF'"></span>
                                </div>
                                <div class="space-y-2 text-xs mt-4 pt-1" style="color: var(--text-secondary)">
                                    <p class="flex items-center gap-2">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span x-text="(g.alamat || '') + (g.kota ? ', ' + g.kota : '') || 'Alamat belum diset'"></span>
                                    </p>
                                    <p class="flex items-center gap-2">
                                        <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span x-text="g.telpon || 'No. telpon belum diset'"></span>
                                    </p>
                                    <p class="flex items-center gap-2">
                                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span>Bos Penanggung Jawab: <strong style="color: var(--text-primary)" x-text="g.nama_bos || 'Belum di-assign'"></strong></span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-2 justify-end mt-5 pt-3 border-t border-slate-100 dark:border-slate-800">
                                <button @click="openEditGudang(g)" class="btn btn-secondary py-1 px-2.5 text-3xs flex items-center gap-1">
                                    <i data-lucide="pencil" class="w-3 h-3"></i> Edit
                                </button>
                                <button @click="deleteGudang(g.id)" class="btn btn-danger py-1 px-2.5 text-3xs flex items-center gap-1">
                                    <i data-lucide="trash-2" class="w-3 h-3"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <!-- Modals Section -->

    <!-- Gudang Modal -->
    <div class="modal-overlay" x-show="showGudangModal" @click.self="showGudangModal = false" x-cloak style="z-index: 1000;">
        <div class="modal-box max-w-md rounded-xl">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg" x-text="editGudangId ? 'Edit Gudang' : 'Tambah Gudang'"></h3>
                <button @click="showGudangModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="saveGudang()">
                <div class="form-group">
                    <label class="form-label">Nama Gudang *</label>
                    <input type="text" x-model="gudangForm.nama" class="form-input" placeholder="Nama gudang/cabang baru" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="form-group">
                        <label class="form-label">Kota *</label>
                        <input type="text" x-model="gudangForm.kota" class="form-input" placeholder="Kota lokasi" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telepon/Kontak</label>
                        <input type="text" x-model="gudangForm.telpon" class="form-input" placeholder="No. telpon">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat Lengkap *</label>
                    <textarea x-model="gudangForm.alamat" class="form-input" placeholder="Alamat jalan lengkap gudang" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Bos / Penanggung Jawab *</label>
                    <select x-model="gudangForm.id_bos" class="form-input" required>
                        <option value="">-- Pilih Bos --</option>
                        <template x-for="u in users.filter(x => x.role === 'bos')" :key="u.id">
                            <option :value="u.id" x-text="u.name"></option>
                        </template>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Operasional</label>
                    <select x-model="gudangForm.is_active" class="form-input">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="flex gap-3 mt-5 justify-end">
                    <button type="submit" class="btn btn-primary" :disabled="savingGudang" x-text="savingGudang ? 'Menyimpan...' : (editGudangId ? 'Update' : 'Simpan')"></button>
                    <button type="button" @click="showGudangModal = false" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bank Modal -->
    <div class="modal-overlay" x-show="showBankModal" @click.self="showBankModal = false" x-cloak style="z-index: 1000;">
        <div class="modal-box max-w-sm rounded-xl">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg" x-text="editBankId ? 'Edit Rekening' : 'Tambah Rekening'"></h3>
                <button @click="showBankModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="saveBank()">
                <div class="form-group">
                    <label class="form-label">Nama Bank *</label>
                    <select x-model="bankForm.bank_name" class="form-input" required>
                        <option value="">-- Pilih Bank / E-Wallet --</option>
                        <optgroup label="Bank Umum">
                            <option>BCA</option>
                            <option>Mandiri</option>
                            <option>BNI</option>
                            <option>BRI</option>
                            <option>CIMB Niaga</option>
                            <option>BTN</option>
                            <option>Danamon</option>
                            <option>Permata</option>
                            <option>Maybank</option>
                            <option>OCBC NISP</option>
                            <option>Panin Bank</option>
                        </optgroup>
                        <optgroup label="Bank Digital">
                            <option>Jenius</option>
                            <option>Blu</option>
                            <option>Jago</option>
                            <option>SeaBank</option>
                            <option>Motion</option>
                            <option>Neo Bank</option>
                        </optgroup>
                        <optgroup label="E-Wallet">
                            <option>GoPay</option>
                            <option>OVO</option>
                            <option>Dana</option>
                            <option>ShopeePay</option>
                            <option>LinkAja</option>
                        </optgroup>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nomor Rekening *</label>
                    <input type="text" x-model="bankForm.account_number" class="form-input" placeholder="Masukkan nomor rekening" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Pemilik *</label>
                    <input type="text" x-model="bankForm.account_name" class="form-input" placeholder="Nama terdaftar pemilik bank" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select x-model="bankForm.is_active" class="form-input">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="flex gap-3 mt-5 justify-end">
                    <button type="submit" class="btn btn-primary" :disabled="savingBank" x-text="savingBank ? 'Menyimpan...' : (editBankId ? 'Update' : 'Simpan')"></button>
                    <button type="button" @click="showBankModal = false" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- User Modal -->
    <div class="modal-overlay" x-show="showUserModal" @click.self="showUserModal = false" x-cloak style="z-index: 1000;">
        <div class="modal-box max-w-sm rounded-xl">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg" x-text="editUserId ? 'Edit User' : 'Tambah User'"></h3>
                <button @click="showUserModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="saveUser()">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" x-model="userForm.name" class="form-input" placeholder="Nama user baru" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Aktif *</label>
                    <input type="email" x-model="userForm.email" class="form-input" placeholder="Contoh: user@example.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label" x-text="editUserId ? 'Password (kosongkan jika tidak diubah)' : 'Password *'"></label>
                    <input type="password" x-model="userForm.password" class="form-input" :placeholder="editUserId ? 'Minimal 8 karakter' : 'Masukkan password'" :required="!editUserId">
                </div>
                <div class="form-group">
                    <label class="form-label">Role *</label>
                    <select x-model="userForm.role" class="form-input" required>
                        <option value="super_admin">Super Admin</option>
                        <option value="bos">Bos (Executive Owner)</option>
                        <option value="admin">Admin Gudang</option>
                        <option value="checker">Checker Lapangan</option>
                    </select>
                </div>
                <div class="form-group" x-show="userForm.role !== 'bos' && userForm.role !== 'super_admin'">
                    <label class="form-label">Gudang Alokasi</label>
                    <select x-model="userForm.id_gudang" class="form-input" :required="userForm.role !== 'bos' && userForm.role !== 'super_admin'">
                        <option value="">-- Pilih Gudang Cabang --</option>
                        <template x-for="g in gudang" :key="g.id">
                            <option :value="g.id" x-text="g.nama"></option>
                        </template>
                    </select>
                </div>
                <div class="flex gap-3 mt-5 justify-end">
                    <button type="submit" class="btn btn-primary" :disabled="saving" x-text="saving ? 'Menyimpan...' : (editUserId ? 'Update' : 'Simpan')"></button>
                    <button type="button" @click="showUserModal = false" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- WhatsApp-Style Cropper Modal -->
    <div class="modal-overlay" x-show="showCropModal" @click.self="showCropModal = false" x-cloak style="z-index: 9999;">
        <div class="modal-box max-w-md bg-slate-950 border border-slate-800 text-slate-100 rounded-xl p-5" style="border-radius: 0.75rem; background: var(--bg-light); border: 1px solid var(--border-color); color: var(--text-primary)">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-base flex items-center gap-2" style="color: var(--text-primary)">
                    <i data-lucide="crop" class="w-4 h-4" style="color: var(--color-primary)"></i>
                    Sesuaikan Logo Perusahaan
                </h3>
                <button type="button" @click="showCropModal = false" style="color: var(--text-secondary)">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Crop Workspace Area -->
            <div class="relative w-full aspect-square rounded-lg overflow-hidden flex items-center justify-center bg-black" style="border: 1px solid var(--border-color); height: 320px; position: relative;">
                <!-- Image inside workspace -->
                <img id="crop-image" :src="cropImageSrc" class="max-w-full max-h-full select-none animate-fade-in" style="transform-origin: center center; cursor: move;" :style="`transform: translate(${cropPanX}px, ${cropPanY}px) scale(${cropZoom}) rotate(${cropRotate}deg)`">

                <!-- Circular WhatsApp Overlay Mask -->
                <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(circle, transparent 110px, rgba(15,23,42,0.8) 110px); border: 2px dashed rgba(255,255,255,0.3); top: 0; left: 0; right: 0; bottom: 0;">
                </div>
            </div>

            <!-- Controls -->
            <div class="mt-5 space-y-4">
                <!-- Zoom Slider -->
                <div class="flex items-center gap-3">
                    <i data-lucide="minus" class="w-4 h-4" style="color: var(--text-secondary)"></i>
                    <input type="range" min="0.5" max="3.5" step="0.05" x-model="cropZoom" class="w-full h-1.5 rounded-lg appearance-none cursor-pointer" style="background: var(--border-color); accent-color: var(--color-primary);">
                    <i data-lucide="plus" class="w-4 h-4" style="color: var(--text-secondary)"></i>
                </div>

                <!-- Pan & Rotate Quick Buttons -->
                <div class="flex items-center justify-between gap-3">
                    <button type="button" @click="cropRotate = (cropRotate - 90) % 360" class="btn btn-secondary text-xs flex items-center gap-1 flex-1 justify-center py-2">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        Putar -90°
                    </button>

                    <button type="button" @click="cropRotate = (cropRotate + 90) % 360" class="btn btn-secondary text-xs flex items-center gap-1 flex-1 justify-center py-2">
                        <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i>
                        Putar 90°
                    </button>
                </div>

                <p class="text-3xs text-center" style="color: var(--text-secondary)">Gunakan slider untuk zoom. Seret gambar di dalam lingkaran untuk memposisikan.</p>
            </div>

            <div class="flex gap-3 mt-6 justify-end">
                <button type="button" @click="showCropModal = false" class="btn btn-secondary">Batal</button>
                <button type="button" @click="applyCrop()" class="btn btn-primary">
                    Terapkan & Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<?php $scripts = <<<'JS'
<script>
function settingsPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        tab: 'umum',
        settings: [],
        users: [],
        gudang: [],
        bankAccounts: [],
        showUserModal: false,
        showBankModal: false,
        showGudangModal: false,
        editUserId: null,
        editBankId: null,
        editGudangId: null,
        saving: false,
        savingBank: false,
        savingGudang: false,
        backingUp: false,
        logoBase64: '',
        userForm: { name: '', email: '', password: '', role: 'admin', id_gudang: '' },
        bankForm: { bank_name: '', account_number: '', account_name: '', is_active: '1' },
        gudangForm: { nama: '', alamat: '', kota: '', telpon: '', id_bos: '', is_active: '1' },
        showCropModal: false,
        cropImageSrc: '',
        cropZoom: 1.0,
        cropRotate: 0,
        cropPanX: 0,
        cropPanY: 0,
        isDraggingLogo: false,
        dragStartX: 0,
        dragStartY: 0,

        async init() {
            if (this.user.role !== 'super_admin') { window.location.href = '/peace_seafood/dashboard'; return; }
            await this.loadAll();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadAll() {
            const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
            const [setRes, usrRes, gudRes, bankRes] = await Promise.all([
                axios.get('/peace_seafood/api/settings', { headers }),
                axios.get('/peace_seafood/api/settings/users', { headers }),
                axios.get('/peace_seafood/api/settings/gudang', { headers }),
                axios.get('/peace_seafood/api/settings/bank-accounts', { headers }),
            ]);
            this.settings = setRes.data?.data || [];
            this.users    = usrRes.data?.data || [];
            this.gudang   = gudRes.data?.data || [];
            this.bankAccounts = bankRes.data?.data || [];
            
            const logo = this.settings.find(x => x.kunci === 'company_logo_base64');
            this.logoBase64 = logo ? logo.nilai : '';
        },

        /* ── Field type helpers ── */
        formatNumberDot(val) {
            if (val === undefined || val === null || val === '') return '0';
            const num = parseInt(String(val).replace(/\D/g, '')) || 0;
            return num.toLocaleString('id-ID');
        },

        isToggle(kunci) {
            return ['multi_warehouse_aktif', 'backup_otomatis', 'onboarding_wizard_aktif'].includes(kunci);
        },

        isSelect(kunci) {
            return ['komisi_penitipan_tipe', 'harga_locked_untuk', 'export_permission'].includes(kunci);
        },

        isNumber(kunci) {
            return ['stok_minimum_threshold', 'susut_alert_threshold', 'komisi_penitipan_persen',
                    'pajak_default_persen', 'jatuh_tempo_default_hari', 'session_timeout_menit', 'kapasitas_cold_storage_kg'].includes(kunci);
        },

        getSelectOptions(kunci) {
            const map = {
                komisi_penitipan_tipe: [
                    { value: 'potong',         label: 'Potong Langsung — supplier bayar net setelah komisi' },
                    { value: 'bayar_terpisah', label: 'Bayar Terpisah — supplier bayar full, komisi diklaim terpisah' },
                ],
                harga_locked_untuk: [
                    { value: 'bos',   label: 'Bos Only (default — paling aman)' },
                    { value: 'admin', label: 'Bos & Admin' },
                    { value: 'semua', label: 'Semua User (tidak disarankan)' },
                ],
                export_permission: [
                    { value: 'bos',   label: 'Bos Only (default — paling aman)' },
                    { value: 'admin', label: 'Bos & Admin' },
                    { value: 'semua', label: 'Semua User' },
                ],
            };
            return map[kunci] || [];
        },

        async saveSetting(setting) {
            try {
                const token = localStorage.getItem('token');
                await axios.put('/peace_seafood/api/settings/' + setting.kunci, { nilai: setting.nilai }, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Setting disimpan', position: 'topRight' });
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal simpan', position: 'topRight' }); }
        },

        openAddBank() { this.editBankId = null; this.bankForm = { bank_name: '', account_number: '', account_name: '', is_active: '1' }; this.showBankModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        openEditBank(b) { this.editBankId = b.id; this.bankForm = { bank_name: b.bank_name, account_number: b.account_number, account_name: b.account_name, is_active: String(b.is_active ?? '1') }; this.showBankModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },

        async saveBank() {
            this.savingBank = true;
            try {
                const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
                this.bankForm.is_active = String(this.bankForm.is_active || '1');
                if (this.editBankId) { await axios.put('/peace_seafood/api/settings/bank-accounts/' + this.editBankId, this.bankForm, { headers }); }
                else { await axios.post('/peace_seafood/api/settings/bank-accounts', this.bankForm, { headers }); }
                iziToast.success({ title: 'Berhasil', message: 'Rekening tersimpan', position: 'topRight' });
                this.showBankModal = false; await this.reloadBanks();
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
            this.savingBank = false;
        },

        async reloadBanks() {
            const token = localStorage.getItem('token');
            const res = await axios.get('/peace_seafood/api/settings/bank-accounts', { headers: { Authorization: 'Bearer ' + token } });
            this.bankAccounts = res.data?.data || [];
        },

        async deleteBank(id) {
            if (!await confirm('Nonaktifkan rekening ini?')) return;
            const token = localStorage.getItem('token');
            await axios.delete('/peace_seafood/api/settings/bank-accounts/' + id, { headers: { Authorization: 'Bearer ' + token } });
            iziToast.success({ title: 'Berhasil', message: 'Rekening dinonaktifkan', position: 'topRight' });
            await this.loadAll();
        },

        openAddUser() { this.editUserId = null; this.userForm = { name: '', email: '', password: '', role: 'admin', id_gudang: '' }; this.showUserModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        openEditUser(u) { this.editUserId = u.id; this.userForm = { name: u.name, email: u.email, password: '', role: u.role, id_gudang: u.id_gudang||'' }; this.showUserModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },

        async saveUser() {
            this.saving = true;
            try {
                const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
                if (this.editUserId) { await axios.put('/peace_seafood/api/settings/users/' + this.editUserId, this.userForm, { headers }); }
                else { await axios.post('/peace_seafood/api/settings/users', this.userForm, { headers }); }
                iziToast.success({ title: 'Berhasil', message: 'User tersimpan', position: 'topRight' });
                this.showUserModal = false; await this.loadAll();
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
            this.saving = false;
        },

        async deleteUser(id) {
            if (!await confirm('Hapus user ini?')) return;
            const token = localStorage.getItem('token');
            await axios.delete('/peace_seafood/api/settings/users/' + id, { headers: { Authorization: 'Bearer ' + token } });
            iziToast.success({ title: 'Berhasil', message: 'User dihapus', position: 'topRight' }); await this.loadAll();
        },

        /* ── Gudang CRUD Methods ── */
        openAddGudang() {
            this.editGudangId = null;
            this.gudangForm = { nama: '', alamat: '', kota: '', telpon: '', id_bos: '', is_active: '1' };
            this.showGudangModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        openEditGudang(g) {
            this.editGudangId = g.id;
            this.gudangForm = {
                nama: g.nama,
                alamat: g.alamat || '',
                kota: g.kota || '',
                telpon: g.telpon || '',
                id_bos: g.id_bos || '',
                is_active: String(g.is_active ?? '1')
            };
            this.showGudangModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async saveGudang() {
            this.savingGudang = true;
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                if (this.editGudangId) {
                    await axios.put('/peace_seafood/api/settings/gudang/' + this.editGudangId, this.gudangForm, { headers });
                } else {
                    await axios.post('/peace_seafood/api/settings/gudang', this.gudangForm, { headers });
                }
                iziToast.success({ title: 'Berhasil', message: 'Gudang berhasil disimpan', position: 'topRight' });
                this.showGudangModal = false;
                await this.loadAll();
            } catch(e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal menyimpan gudang', position: 'topRight' });
            }
            this.savingGudang = false;
        },

        async deleteGudang(id) {
            if (!await confirm('Apakah Anda yakin ingin menghapus gudang ini? Jika sudah ada transaksi, gudang akan dinonaktifkan secara aman.')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.delete('/peace_seafood/api/settings/gudang/' + id, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Gudang berhasil dihapus/dinonaktifkan', position: 'topRight' });
                await this.loadAll();
            } catch(e) {
                iziToast.error({ title: 'Error', message: 'Gagal memproses penghapusan gudang', position: 'topRight' });
            }
        },

        /* ── Database Backup ── */
        async runBackup() {
            this.backingUp = true;
            try {
                const token = localStorage.getItem('token');
                const response = await axios({
                    url: '/peace_seafood/api/settings/backup',
                    method: 'POST',
                    responseType: 'blob',
                    headers: { Authorization: 'Bearer ' + token }
                });
                
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'peace_seafood_backup_' + new Date().toISOString().split('T')[0] + '.sql');
                document.body.appendChild(link);
                link.click();
                link.remove();
                
                iziToast.success({ title: 'Berhasil', message: 'Backup database berhasil diunduh', position: 'topRight' });
            } catch(e) {
                iziToast.error({ title: 'Error', message: 'Gagal membuat backup database', position: 'topRight' });
            }
            this.backingUp = false;
        },

        /* ── Premium Logo Cropper Methods ── */
        getCompanyInitial() {
            const s = this.settings.find(x => x.kunci === 'company_logo_initial');
            return s ? s.nilai : 'PS';
        },

        triggerLogoUpload() {
            const input = document.getElementById('logo-file-input');
            if (input) input.click();
        },

        onLogoSelected(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (event) => {
                this.cropImageSrc = event.target.result;
                this.cropZoom = 1.0;
                this.cropRotate = 0;
                this.cropPanX = 0;
                this.cropPanY = 0;
                this.showCropModal = true;
                this.setupDragEvents();
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            };
            reader.readAsDataURL(file);
        },

        setupDragEvents() {
            this.$nextTick(() => {
                const img = document.getElementById('crop-image');
                if (!img) return;
                
                // Mouse events
                img.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    this.isDraggingLogo = true;
                    this.dragStartX = e.clientX - this.cropPanX;
                    this.dragStartY = e.clientY - this.cropPanY;
                });
                
                window.addEventListener('mousemove', (e) => {
                    if (!this.isDraggingLogo) return;
                    this.cropPanX = e.clientX - this.dragStartX;
                    this.cropPanY = e.clientY - this.dragStartY;
                });
                
                window.addEventListener('mouseup', () => {
                    this.isDraggingLogo = false;
                });
                
                // Touch events for mobile
                img.addEventListener('touchstart', (e) => {
                    if (e.touches.length !== 1) return;
                    this.isDraggingLogo = true;
                    this.dragStartX = e.touches[0].clientX - this.cropPanX;
                    this.dragStartY = e.touches[0].clientY - this.cropPanY;
                });
                
                img.addEventListener('touchmove', (e) => {
                    if (!this.isDraggingLogo || e.touches.length !== 1) return;
                    this.cropPanX = e.touches[0].clientX - this.dragStartX;
                    this.cropPanY = e.touches[0].clientY - this.dragStartY;
                });
                
                img.addEventListener('touchend', () => {
                    this.isDraggingLogo = false;
                });
            });
        },

        applyCrop() {
            const img = new Image();
            img.src = this.cropImageSrc;
            img.onload = async () => {
                const canvas = document.createElement('canvas');
                canvas.width = 500;
                canvas.height = 500;
                const ctx = canvas.getContext('2d');
                if (!ctx) return;
                
                ctx.fillStyle = '#0f172a';
                ctx.fillRect(0, 0, 500, 500);
                
                ctx.save();
                ctx.translate(250, 250);
                ctx.rotate((this.cropRotate * Math.PI) / 180);
                const scale = parseFloat(this.cropZoom);
                
                let dw = img.width;
                let dh = img.height;
                const maxDim = Math.max(dw, dh);
                if (maxDim > 0) {
                     dw = (dw / maxDim) * 450 * scale;
                     dh = (dh / maxDim) * 450 * scale;
                }
                
                ctx.drawImage(img, -dw/2 + this.cropPanX * scale, -dh/2 + this.cropPanY * scale, dw, dh);
                ctx.restore();
                
                const croppedBase64 = canvas.toDataURL('image/jpeg', 0.85);
                this.logoBase64 = croppedBase64;
                
                let logoSetting = this.settings.find(x => x.kunci === 'company_logo_base64');
                if (!logoSetting) {
                    logoSetting = { kunci: 'company_logo_base64', nilai: croppedBase64 };
                    this.settings.push(logoSetting);
                } else {
                    logoSetting.nilai = croppedBase64;
                }
                
                await this.saveSetting(logoSetting);
                this.showCropModal = false;
                
                // Dispatch event so other pages or elements (e.g. sidebar) immediately update
                localStorage.setItem('company_logo_base64', croppedBase64);
                window.dispatchEvent(new Event('logo-updated'));
            };
        },
    };
}
</script>
JS;
?>