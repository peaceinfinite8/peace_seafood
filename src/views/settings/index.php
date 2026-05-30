<?php ?>
<div x-data="settingsPage()" x-init="init()" class="space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
            <h2 class="text-2xl font-bold tracking-tight" style="color: var(--text-primary)">Pengaturan Sistem</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Konfigurasi global platform, akun pengguna, cabang
                gudang, dan pencadangan database</p>
        </div>
    </div>

    <!-- Main Grid Split Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">

        <!-- Left Sidebar Navigation -->
        <div class="lg:col-span-1 space-y-2">
            <div
                class="card p-3 space-y-1 shadow-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl">
                <h3 class="text-2xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-3 py-2">
                    Navigasi Pengaturan</h3>

                <!-- View Mode Toggle: Platform vs Tenant -->
                <div class="px-3 py-2 flex gap-2">
                    <button type="button" @click="switchViewMode('platform')"
                        x-show="['super_admin','saas_owner'].includes(user.role)"
                        :class="viewMode === 'platform' ? 'bg-blue-600 text-white' : 'bg-transparent text-slate-600 dark:text-slate-300'"
                        class="flex-1 text-xs py-1 rounded-md">Platform</button>

                    <button type="button" @click="switchViewMode('tenant')"
                        :class="viewMode === 'tenant' ? 'bg-blue-600 text-white' : 'bg-transparent text-slate-600 dark:text-slate-300'"
                        class="flex-1 text-xs py-1 rounded-md">Tenant</button>
                </div>

                <!-- Select Gudang when in Tenant view for platform users -->
                <div class="px-3 py-2"
                    x-show="viewMode === 'tenant' && (user.role === 'saas_owner' || user.role === 'super_admin')">
                    <label class="text-3xs mb-1 block" style="color:var(--text-secondary)">Pilih Gudang</label>
                    <select x-model="selectedGudangId" class="form-input text-sm" @change="loadSettingsForGudang()">
                        <option value="">-- Pilih Gudang --</option>
                        <template x-for="g in gudang" :key="g.id">
                            <option :value="g.id" x-text="g.nama + ' (' + (g.kota||'') + ')' "></option>
                        </template>
                    </select>
                </div>

                <button @click="tab = 'umum'"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200"
                    :class="tab === 'umum' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/10' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <span>Umum</span>
                </button>

                <button @click="tab = 'rekening'" x-show="viewMode === 'tenant'"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200"
                    :class="tab === 'rekening' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/10' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                    <span>Rekening Bank</span>
                </button>

                <button @click="tab = 'users'" x-show="viewMode === 'tenant'"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200"
                    :class="tab === 'users' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/10' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>Manajemen User</span>
                </button>

                <button @click="tab = 'gudang'" x-show="viewMode === 'tenant'"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200"
                    :class="tab === 'gudang' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/10' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    <span>Kelola Gudang</span>
                </button>

                <button @click="tab = 'saas'"
                    x-show="((viewMode === 'platform') && ['super_admin','saas_owner'].includes(user.role)) || ((viewMode === 'tenant') && user.role === 'bos')"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200"
                    :class="tab === 'saas' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/10' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    x-cloak>
                    <i data-lucide="crown" class="w-4 h-4"></i>
                    <span
                        x-text="['super_admin', 'saas_owner'].includes(user.role) ? 'SaaS Developer Panel' : 'Status Langganan'"></span>
                </button>
            </div>
        </div>

        <!-- Right Workspace Panel -->
        <div class="lg:col-span-3">

            <!-- Tab Umum -->
            <div x-show="tab === 'umum'" class="card p-6 shadow-sm rounded-xl space-y-6"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2">
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-lg font-bold" style="color: var(--text-primary)">Pengaturan Aplikasi</h3>
                        <p class="text-xs" style="color: var(--text-secondary)">Konfigurasi operasional gudang, batas
                            minimal stok, susut alerts, dan logo brand</p>
                    </div>
                </div>

                <!-- Dedicated Logo Card at the Top (Always Visible) -->
                <div
                    class="p-5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-4 flex-1">
                        <div
                            class="relative w-16 h-16 rounded-xl border border-slate-300 dark:border-slate-700 flex items-center justify-center overflow-hidden bg-slate-900 shadow-inner group">
                            <template x-if="logoBase64">
                                <img :src="logoBase64" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!logoBase64">
                                <span class="text-xl font-black text-blue-500" x-text="getCompanyInitial()"></span>
                            </template>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold" style="color: var(--text-primary)">Logo Perusahaan / Gudang
                            </h4>
                            <p class="text-xs" style="color: var(--text-secondary)">Logo ini akan tampil di navigasi
                                sidebar utama dan cetakan nota Anda.</p>
                        </div>
                    </div>
                    <div>
                        <button type="button" @click="triggerLogoUpload()"
                            class="btn btn-primary py-2 px-4 text-xs flex items-center gap-2 shadow-lg shadow-blue-500/10">
                            <i data-lucide="camera" class="w-4 h-4"></i>
                            <span>Pilih & Potong Logo Baru</span>
                        </button>
                        <input type="file" id="logo-file-input" @change="onLogoSelected($event)" accept="image/*"
                            class="hidden">
                    </div>
                </div>

                <!-- Empty state -->
                <template x-if="settings.length === 0">
                    <p class="text-sm py-4" style="color: var(--text-secondary)">Memuat pengaturan...</p>
                </template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <template
                        x-for="setting in settings.filter(x => x.kunci !== 'company_logo_base64' && x.kunci !== 'company_logo_initial')"
                        :key="setting.kunci">
                        <div
                            class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex flex-col justify-between">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider mb-2"
                                    style="color: var(--text-primary)" x-text="setting.label || setting.kunci"></label>

                                <!-- Toggle (boolean: 0/1) -->
                                <template x-if="isToggle(setting.kunci)">
                                    <div class="flex items-center gap-3 mt-1.5">
                                        <button type="button"
                                            @click="setting.nilai = setting.nilai == '1' ? '0' : '1'; saveSetting(setting)"
                                            class="settings-toggle"
                                            :class="setting.nilai == '1' ? 'settings-toggle--on' : 'settings-toggle--off'"
                                            :aria-checked="setting.nilai == '1'" role="switch">
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
                                        <input type="text" x-show="isNumber(setting.kunci)"
                                            :value="formatNumberDot(setting.nilai)"
                                            @input="setting.nilai = $event.target.value.replace(/\D/g, ''); $event.target.value = formatNumberDot(setting.nilai)"
                                            class="form-input flex-1 py-1 px-2 text-xs">
                                        <input type="text" x-show="!isNumber(setting.kunci)" x-model="setting.nilai"
                                            class="form-input flex-1 py-1 px-2 text-xs">
                                        <button @click="saveSetting(setting)" class="btn btn-primary p-2">
                                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <p class="text-3xs mt-2" style="color: var(--text-secondary)"
                                x-text="setting.keterangan || ''"></p>
                        </div>
                    </template>
                </div>

                <!-- Database Backup Component Section -->
                <div x-show="['super_admin', 'saas_owner'].includes(user.role)"
                    class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-800" x-cloak>
                    <h4 class="font-bold text-sm mb-2" style="color: var(--text-primary)">
                        <i data-lucide="database" class="w-4 h-4 inline mr-1 text-blue-500"></i>
                        Pencadangan Sistem & Database
                    </h4>
                    <p class="text-xs mb-4" style="color: var(--text-secondary)">
                        Ambil cadangan penuh seluruh data transaksi operasional, stok cakalang/seafood, data supplier,
                        pembeli, dan logs keamanan ke berkas format SQL langsung yang dapat di-download ke komputer
                        Anda.
                    </p>
                    <div
                        class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/30">
                        <div class="flex items-center gap-3">
                            <div
                                class="p-2.5 rounded-full bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold" style="color: var(--text-primary)">Target Database:
                                    peace_seafood</p>
                                <p class="text-3xs text-slate-400 dark:text-slate-500">Koneksi: Aktif & Terlindungi</p>
                            </div>
                        </div>
                        <button type="button" @click="runBackup()" class="btn btn-primary flex items-center gap-2"
                            :disabled="backingUp">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            <span x-text="backingUp ? 'Mengunduh SQL...' : 'Cadangkan Database Sekarang'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab Rekening Bank -->
            <div x-show="tab === 'rekening' && viewMode === 'tenant'" class="space-y-4" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold" style="color: var(--text-primary)">Rekening Bank BOS</h3>
                        <p class="text-xs" style="color: var(--text-secondary)">Kelola rekening tujuan transfer
                            pembayaran nota penjualan timbangan lapangan</p>
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
                                        <span
                                            class="text-2xs font-extrabold uppercase tracking-widest px-2 py-0.5 rounded"
                                            :class="b.is_active ? 'bg-blue-600/30 text-blue-400' : 'bg-slate-600/30 text-slate-400'"
                                            x-text="b.bank_name"></span>
                                    </div>
                                    <div>
                                        <h4 class="bank-card__number text-lg font-mono font-bold tracking-wider"
                                            x-text="b.account_number"></h4>
                                        <p class="bank-card__label text-3xs mt-1 uppercase tracking-wider">Pemilik
                                            Rekening</p>
                                        <p class="bank-card__name text-sm font-semibold truncate"
                                            x-text="b.account_name"></p>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end justify-between h-full min-h-[90px]">
                                    <span class="badge py-0.5 px-1.5 text-3xs font-extrabold"
                                        :class="b.is_active ? 'badge-success' : 'badge-gray'"
                                        x-text="b.is_active ? 'AKTIF' : 'NON-AKTIF'"></span>
                                    <div class="flex gap-1.5 mt-4">
                                        <button @click="openEditBank(b)"
                                            class="bank-card__action p-1.5 rounded-lg transition-colors duration-200">
                                            <i data-lucide="pencil" class="w-3 h-3"></i>
                                        </button>
                                        <button @click="deleteBank(b.id)"
                                            class="bank-card__action bank-card__action--danger p-1.5 rounded-lg transition-colors duration-200">
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
            <div x-show="tab === 'users' && viewMode === 'tenant'" class="space-y-4" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold" style="color: var(--text-primary)">Manajemen User</h3>
                        <p class="text-xs" style="color: var(--text-secondary)">Pemberian hak akses staf kasir, lapangan
                            (checker), dan bos eksekutif</p>
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
                                                <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center font-bold text-xs"
                                                    style="color: var(--color-primary)">
                                                    <span x-text="u.name ? u.name.charAt(0).toUpperCase() : 'U'"></span>
                                                </div>
                                                <span class="font-bold text-sm text-slate-800 dark:text-slate-200"
                                                    x-text="u.name"></span>
                                            </div>
                                        </td>
                                        <td class="text-sm" x-text="u.email"></td>
                                        <td>
                                            <span class="badge uppercase tracking-wider text-4xs font-black py-0.5 px-2"
                                                :class="u.role==='super_admin'?'badge-danger':u.role==='bos'?'badge-danger':u.role==='admin'?'badge-warning':'badge-info'"
                                                x-text="u.role"></span>
                                        </td>
                                        <td class="text-sm text-slate-600 dark:text-slate-400"
                                            x-text="u.nama_gudang || 'Semua Gudang'"></td>
                                        <td>
                                            <span class="badge text-4xs font-bold"
                                                :class="u.is_active?'badge-success':'badge-gray'"
                                                x-text="u.is_active?'AKTIF':'NONAKTIF'"></span>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <button @click="openEditUser(u)" class="btn btn-secondary p-1.5"><i
                                                        data-lucide="pencil" class="w-3.5 h-3.5"></i></button>
                                                <button @click="deleteUser(u.id)" class="btn btn-danger p-1.5"
                                                    x-show="u.role !== 'bos' && u.role !== 'super_admin'"><i
                                                        data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
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
            <div x-show="tab === 'gudang' && viewMode === 'tenant'" class="space-y-4" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold" style="color: var(--text-primary)">Kelola Cabang Gudang</h3>
                        <p class="text-xs" style="color: var(--text-secondary)">Manajemen cabang/gudang penampungan
                            timbangan dan penanggung jawab</p>
                    </div>
                    <button @click="openAddGudang()" class="btn btn-primary flex items-center gap-2">
                        <i data-lucide="building-2" class="w-4 h-4"></i>
                        <span>Tambah Gudang</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="g in gudang" :key="g.id">
                        <div
                            class="card p-5 border border-slate-200 dark:border-slate-800 transition-all duration-300 hover:shadow-md flex flex-col justify-between rounded-xl">
                            <div>
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                            <i data-lucide="building" class="w-4 h-4"></i>
                                        </div>
                                        <h3 class="font-bold text-base" style="color: var(--text-primary)"
                                            x-text="g.nama"></h3>
                                    </div>
                                    <span class="badge text-4xs font-bold"
                                        :class="g.is_active ? 'badge-success' : 'badge-gray'"
                                        x-text="g.is_active ? 'AKTIF' : 'NONAKTIF'"></span>
                                </div>
                                <div class="space-y-2 text-xs mt-4 pt-1" style="color: var(--text-secondary)">
                                    <p class="flex items-center gap-2">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span
                                            x-text="(g.alamat || '') + (g.kota ? ', ' + g.kota : '') || 'Alamat belum diset'"></span>
                                    </p>
                                    <p class="flex items-center gap-2">
                                        <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span x-text="g.telpon || 'No. telpon belum diset'"></span>
                                    </p>
                                    <p class="flex items-center gap-2">
                                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span>Bos Penanggung Jawab: <strong style="color: var(--text-primary)"
                                                x-text="g.nama_bos || 'Belum di-assign'"></strong></span>
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex gap-2 justify-end mt-5 pt-3 border-t border-slate-100 dark:border-slate-800">
                                <button @click="openEditGudang(g)"
                                    class="btn btn-secondary py-1 px-2.5 text-3xs flex items-center gap-1">
                                    <i data-lucide="pencil" class="w-3 h-3"></i> Edit
                                </button>
                                <button @click="deleteGudang(g.id)"
                                    class="btn btn-danger py-1 px-2.5 text-3xs flex items-center gap-1">
                                    <i data-lucide="trash-2" class="w-3 h-3"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Tab SaaS Developer Panel (Developer) / Status Langganan (Bos) -->
            <div x-show="tab === 'saas'" class="space-y-6" x-cloak x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2">
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800 text-left">
                    <div>
                        <h3 class="text-lg font-bold" style="color: var(--text-primary)"
                            x-text="['super_admin', 'saas_owner'].includes(user.role) ? 'SaaS Developer / Owner Panel' : 'Status Langganan Gudang'">
                        </h3>
                        <p class="text-xs" style="color: var(--text-secondary)"
                            x-text="['super_admin', 'saas_owner'].includes(user.role) ? 'Pusat kendali komersialisasi trial Bos, pre-approval email pendaftaran, bypass impersonate, dan support WhatsApp.' : 'Informasi masa aktif langganan dan kontak pengembang sistem.'">
                        </p>
                    </div>
                </div>

                <!-- ====== BOS ONLY: Subscription Status Card ====== -->
                <div x-show="user.role === 'bos'" x-cloak class="space-y-4">

                    <!-- Kartu Status Trial Per Gudang -->
                    <template x-for="g in gudang" :key="g.id">
                        <div class="card p-5 border rounded-xl text-left transition-all duration-300"
                            :class="getRemainingDaysNum(g.subscription_until) < 0
                                        ? 'border-red-400 dark:border-red-700 bg-red-50/40 dark:bg-red-900/10'
                                        : getRemainingDaysNum(g.subscription_until) <= 7
                                            ? 'border-amber-400 dark:border-amber-700 bg-amber-50/40 dark:bg-amber-900/10'
                                            : 'border-emerald-400 dark:border-emerald-700 bg-emerald-50/20 dark:bg-emerald-900/10'">

                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 rounded-xl"
                                        :class="getRemainingDaysNum(g.subscription_until) < 0
                                                    ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'
                                                    : getRemainingDaysNum(g.subscription_until) <= 7
                                                        ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400'
                                                        : 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400'">
                                        <i data-lucide="building-2" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold" style="color: var(--text-primary)"
                                            x-text="g.nama"></h4>
                                        <p class="text-xs" style="color: var(--text-secondary)"
                                            x-text="g.kota || 'Lokasi belum diset'"></p>
                                    </div>
                                </div>
                                <!-- Status Badge -->
                                <span class="badge text-3xs font-black px-3 py-1 rounded-full"
                                    :class="getRemainingDaysNum(g.subscription_until) < 0
                                                ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                                : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'"
                                    x-text="getRemainingDaysNum(g.subscription_until) < 0 ? '⚠ HABIS' : '✓ AKTIF'">
                                </span>
                            </div>

                            <!-- Subscription Info Grid -->
                            <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div
                                    class="p-3 rounded-xl bg-white/60 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/40">
                                    <p class="text-3xs font-semibold uppercase tracking-wider text-slate-400 mb-1">
                                        Tanggal Berakhir</p>
                                    <p class="text-sm font-bold" style="color: var(--text-primary)"
                                        x-text="g.subscription_until ? new Date(g.subscription_until).toLocaleDateString('id-ID', {day:'numeric',month:'long',year:'numeric'}) : 'Belum dimulai'">
                                    </p>
                                </div>
                                <div
                                    class="p-3 rounded-xl bg-white/60 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/40">
                                    <p class="text-3xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Sisa
                                        Waktu</p>
                                    <p class="text-sm font-bold"
                                        :class="getRemainingDaysNum(g.subscription_until) < 0 ? 'text-red-600 dark:text-red-400' : getRemainingDaysNum(g.subscription_until) <= 7 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400'"
                                        x-text="getRemainingDaysNum(g.subscription_until) < 0 ? 'Masa aktif habis' : getRemainingDaysNum(g.subscription_until) + ' hari lagi'">
                                    </p>
                                </div>
                                <div
                                    class="p-3 rounded-xl bg-white/60 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/40">
                                    <p class="text-3xs font-semibold uppercase tracking-wider text-slate-400 mb-1">
                                        Status Sewa</p>
                                    <p class="text-sm font-bold capitalize" x-text="g.status_langganan || 'aktif'"
                                        :class="(g.status_langganan || 'aktif') === 'aktif' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                                    </p>
                                </div>
                            </div>

                            <!-- Peringatan jika hampir habis -->
                            <div x-show="getRemainingDaysNum(g.subscription_until) >= 0 && getRemainingDaysNum(g.subscription_until) <= 7"
                                class="mt-3 p-3 rounded-lg bg-amber-100/80 dark:bg-amber-900/20 border border-amber-300/50 dark:border-amber-700/30 text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
                                <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0"></i>
                                <span>Masa langganan gudang ini akan segera berakhir. Hubungi pengembang untuk
                                    memperpanjang akses.</span>
                            </div>
                            <div x-show="getRemainingDaysNum(g.subscription_until) < 0"
                                class="mt-3 p-3 rounded-lg bg-red-100/80 dark:bg-red-900/20 border border-red-300/50 dark:border-red-700/30 text-xs text-red-800 dark:text-red-300 flex items-center gap-2">
                                <i data-lucide="lock" class="w-4 h-4 flex-shrink-0"></i>
                                <span>Masa aktif gudang ini telah berakhir. Akses operasional dikunci. Hubungi
                                    pengembang untuk mengaktifkan kembali.</span>
                            </div>

                        </div>
                    </template>

                    <!-- Tombol Hubungi Developer -->
                    <div class="card p-5 border border-slate-200 dark:border-slate-800 rounded-xl text-left">
                        <div class="flex items-center gap-2 pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
                            <i data-lucide="headphones" class="w-4 h-4 text-emerald-500"></i>
                            <h4 class="text-sm font-bold" style="color: var(--text-primary)">Hubungi Tim Pengembang</h4>
                        </div>
                        <p class="text-xs mb-4" style="color: var(--text-secondary)">Untuk perpanjangan langganan,
                            pertanyaan, atau kendala teknis, hubungi pengembang sistem melalui WhatsApp di bawah ini.
                        </p>
                        <a :href="'https://wa.me/' + devWhatsappNumber + '?text=' + encodeURIComponent('Halo Developer, saya ' + user.name + ' ingin menanyakan tentang langganan Peace Seafood WMS.')"
                            target="_blank" rel="noopener noreferrer"
                            class="btn w-full justify-center text-sm py-3 gap-2 font-bold"
                            style="background: #25D366; color: white; border: none; border-radius: 0.75rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                            </svg>
                            Hubungi Pengembang via WhatsApp
                        </a>
                    </div>
                </div>
                <!-- ====== END BOS ONLY ====== -->

                <!-- Pre-Approval Form & WhatsApp Settings Card (Developer Only) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6"
                    x-show="['super_admin', 'saas_owner'].includes(user.role)" x-cloak>

                    <!-- Pre-Approval Form -->
                    <div
                        class="md:col-span-2 card p-5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl space-y-4 text-left">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                            <i data-lucide="user-plus" class="w-4 h-4 text-blue-500"></i>
                            <h4 class="text-sm font-bold" style="color: var(--text-primary)">Pra-Persetujuan
                                (Pre-Approve) Akun Bos Baru</h4>
                        </div>

                        <form @submit.prevent="runPreApprove()" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label text-slate-700 dark:text-slate-300">Nama Lengkap Calon Bos
                                    *</label>
                                <input type="text" x-model="preApproveForm.name" class="form-input text-xs"
                                    placeholder="Contoh: Bos Ronald" required>
                            </div>
                            <div>
                                <label class="form-label text-slate-700 dark:text-slate-300">Email Gmail Calon Bos
                                    *</label>
                                <input type="email" x-model="preApproveForm.email" class="form-input text-xs"
                                    placeholder="Contoh: ronald@gmail.com" required>
                            </div>
                            <div
                                class="sm:col-span-2 flex flex-col sm:flex-row items-stretch sm:items-end justify-between gap-4">
                                <div class="flex-1">
                                    <label class="form-label text-slate-700 dark:text-slate-300">Durasi Masa Trial Uji
                                        Coba *</label>
                                    <select x-model="preApproveForm.trial_days" class="form-input text-xs">
                                        <option value="7">7 Hari (1 Uji Coba)</option>
                                        <option value="14">14 Hari (2 Uji Coba - Default)</option>
                                        <option value="30">30 Hari (1 Bulan)</option>
                                        <option value="60">60 Hari (2 Bulan)</option>
                                        <option value="90">90 Hari (3 Bulan)</option>
                                    </select>
                                </div>
                                <button type="submit"
                                    class="btn btn-primary py-2 px-5 text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-blue-500/10 h-[38px]"
                                    :disabled="preApproving">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span x-text="preApproving ? 'Memproses...' : 'Setujui Email'"></span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Developer WhatsApp Support Setting -->
                    <div
                        class="card p-5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl space-y-4 text-left">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <h4 class="text-sm font-bold" style="color: var(--text-primary)">No. WhatsApp Developer</h4>
                        </div>

                        <div class="space-y-3">
                            <label class="form-label text-slate-500">Nomor WhatsApp Support (Gunakan format 628xxx tanpa
                                tanda + atau 0 di depan)</label>
                            <div class="flex gap-2">
                                <input type="text" x-model="devWhatsappNumber" class="form-input text-xs flex-1"
                                    placeholder="Contoh: 628123456789">
                                <button type="button" @click="saveDevWhatsapp()"
                                    class="btn btn-primary p-2 flex items-center justify-center" :disabled="savingWa">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Gudang SaaS Listing with Expiry Adjustments & Impersonation (Developer Only) -->
                <div class="card overflow-hidden shadow-sm rounded-xl"
                    x-show="['super_admin', 'saas_owner'].includes(user.role)" x-cloak>
                    <div
                        class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 text-left">
                        <h4 class="text-sm font-bold flex items-center gap-1.5" style="color: var(--text-primary)">
                            <i data-lucide="layout-grid" class="w-4.5 h-4.5 text-blue-500"></i>
                            Daftar Gudang Tenant SaaS & Masa Sewa
                        </h4>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table text-left">
                            <thead>
                                <tr>
                                    <th>Nama Gudang</th>
                                    <th>Kota</th>
                                    <th>Milik Bos (Executive)</th>
                                    <th>Masa Trial / Sewa</th>
                                    <th>Status Sewa</th>
                                    <th>Aksi Pengembang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="g in gudang" :key="g.id">
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                        <td class="font-bold text-sm" style="color: var(--text-primary)"
                                            x-text="g.nama"></td>
                                        <td class="text-sm" x-text="g.kota || '-'"></td>
                                        <td>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-semibold" style="color: var(--text-primary)"
                                                    x-text="g.nama_bos || 'Belum di-assign'"></span>
                                                <span class="text-3xs text-slate-400"
                                                    x-text="getBosEmail(g.id_bos)"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <input type="date"
                                                    :value="g.subscription_until ? g.subscription_until.substring(0,10) : ''"
                                                    @change="updateGudangSubscription(g, $event.target.value)"
                                                    class="form-input text-xs py-1 px-2 w-36">
                                                <span class="text-3xs text-slate-400"
                                                    x-text="getRemainingDaysText(g.subscription_until)"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <select :value="g.status_langganan || 'aktif'"
                                                @change="updateGudangStatus(g, $event.target.value)"
                                                class="form-input text-xs py-1 px-2 w-28">
                                                <option value="aktif">Aktif</option>
                                                <option value="suspend">Suspend</option>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <button type="button" @click="impersonateUser(g.id_bos)"
                                                    class="btn btn-secondary py-1 px-2 text-3xs flex items-center gap-1 font-bold bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400 border border-blue-200/50 dark:border-blue-900/20">
                                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                    Masuk Sebagai Bos
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modals Section -->

    <!-- Gudang Modal -->
    <div class="modal-overlay" x-show="showGudangModal" @click.self="showGudangModal = false" x-cloak
        style="z-index: 1000;">
        <div class="modal-box max-w-md rounded-xl">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg" x-text="editGudangId ? 'Edit Gudang' : 'Tambah Gudang'"></h3>
                <button @click="showGudangModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="saveGudang()">
                <div class="form-group">
                    <label class="form-label">Nama Gudang *</label>
                    <input type="text" x-model="gudangForm.nama" class="form-input"
                        placeholder="Nama gudang/cabang baru" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="form-group">
                        <label class="form-label">Kota *</label>
                        <input type="text" x-model="gudangForm.kota" class="form-input" placeholder="Kota lokasi"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telepon/Kontak</label>
                        <input type="text" x-model="gudangForm.telpon" class="form-input" placeholder="No. telpon">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat Lengkap *</label>
                    <textarea x-model="gudangForm.alamat" class="form-input" placeholder="Alamat jalan lengkap gudang"
                        rows="3" required></textarea>
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
                    <button type="submit" class="btn btn-primary" :disabled="savingGudang"
                        x-text="savingGudang ? 'Menyimpan...' : (editGudangId ? 'Update' : 'Simpan')"></button>
                    <button type="button" @click="showGudangModal = false" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bank Modal -->
    <div class="modal-overlay" x-show="showBankModal" @click.self="showBankModal = false" x-cloak
        style="z-index: 1000;">
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
                    <input type="text" x-model="bankForm.account_number" class="form-input"
                        placeholder="Masukkan nomor rekening" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Pemilik *</label>
                    <input type="text" x-model="bankForm.account_name" class="form-input"
                        placeholder="Nama terdaftar pemilik bank" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select x-model="bankForm.is_active" class="form-input">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="flex gap-3 mt-5 justify-end">
                    <button type="submit" class="btn btn-primary" :disabled="savingBank"
                        x-text="savingBank ? 'Menyimpan...' : (editBankId ? 'Update' : 'Simpan')"></button>
                    <button type="button" @click="showBankModal = false" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- User Modal -->
    <div class="modal-overlay" x-show="showUserModal" @click.self="showUserModal = false" x-cloak
        style="z-index: 1000;">
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
                    <input type="email" x-model="userForm.email" class="form-input"
                        placeholder="Contoh: user@example.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label"
                        x-text="editUserId ? 'Password (kosongkan jika tidak diubah)' : 'Password *'"></label>
                    <input type="password" x-model="userForm.password" class="form-input"
                        :placeholder="editUserId ? 'Minimal 8 karakter' : 'Masukkan password'" :required="!editUserId">
                </div>
                <div class="form-group">
                    <label class="form-label">Role *</label>
                    <select x-model="userForm.role" class="form-input" required>
                        <template x-for="r in availableRoles" :key="r.value">
                            <option :value="r.value" x-text="r.label"></option>
                        </template>
                    </select>
                </div>
                <div class="form-group"
                    x-show="userForm.role !== 'bos' && userForm.role !== 'super_admin' && userForm.role !== 'saas_owner'">
                    <label class="form-label">Gudang Alokasi</label>
                    <select x-model="userForm.id_gudang" class="form-input"
                        :required="userForm.role !== 'bos' && userForm.role !== 'super_admin'">
                        <option value="">-- Pilih Gudang Cabang --</option>
                        <template x-for="g in gudang" :key="g.id">
                            <option :value="g.id" x-text="g.nama"></option>
                        </template>
                    </select>
                </div>
                <div class="flex gap-3 mt-5 justify-end">
                    <button type="submit" class="btn btn-primary" :disabled="saving"
                        x-text="saving ? 'Menyimpan...' : (editUserId ? 'Update' : 'Simpan')"></button>
                    <button type="button" @click="showUserModal = false" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- WhatsApp-Style Cropper Modal -->
    <div class="modal-overlay" x-show="showCropModal" @click.self="showCropModal = false" x-cloak
        style="z-index: 9999;">
        <div class="modal-box max-w-md bg-slate-950 border border-slate-800 text-slate-100 rounded-xl p-5"
            style="border-radius: 0.75rem; background: var(--bg-light); border: 1px solid var(--border-color); color: var(--text-primary)">
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
            <div class="relative w-full aspect-square rounded-lg overflow-hidden flex items-center justify-center bg-black"
                style="border: 1px solid var(--border-color); height: 320px; position: relative;">
                <!-- Image inside workspace -->
                <img id="crop-image" :src="cropImageSrc" class="max-w-full max-h-full select-none animate-fade-in"
                    style="transform-origin: center center; cursor: move;"
                    :style="`transform: translate(${cropPanX}px, ${cropPanY}px) scale(${cropZoom}) rotate(${cropRotate}deg)`">

                <!-- Circular WhatsApp Overlay Mask -->
                <div class="absolute inset-0 pointer-events-none"
                    style="background: radial-gradient(circle, transparent 110px, rgba(15,23,42,0.8) 110px); border: 2px dashed rgba(255,255,255,0.3); top: 0; left: 0; right: 0; bottom: 0;">
                </div>
            </div>

            <!-- Controls -->
            <div class="mt-5 space-y-4">
                <!-- Zoom Slider -->
                <div class="flex items-center gap-3">
                    <i data-lucide="minus" class="w-4 h-4" style="color: var(--text-secondary)"></i>
                    <input type="range" min="0.5" max="3.5" step="0.05" x-model="cropZoom"
                        class="w-full h-1.5 rounded-lg appearance-none cursor-pointer"
                        style="background: var(--border-color); accent-color: var(--color-primary);">
                    <i data-lucide="plus" class="w-4 h-4" style="color: var(--text-secondary)"></i>
                </div>

                <!-- Pan & Rotate Quick Buttons -->
                <div class="flex items-center justify-between gap-3">
                    <button type="button" @click="cropRotate = (cropRotate - 90) % 360"
                        class="btn btn-secondary text-xs flex items-center gap-1 flex-1 justify-center py-2">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        Putar -90°
                    </button>

                    <button type="button" @click="cropRotate = (cropRotate + 90) % 360"
                        class="btn btn-secondary text-xs flex items-center gap-1 flex-1 justify-center py-2">
                        <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i>
                        Putar 90°
                    </button>
                </div>

                <p class="text-3xs text-center" style="color: var(--text-secondary)">Gunakan slider untuk zoom. Seret
                    gambar di dalam lingkaran untuk memposisikan.</p>
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

<?php $scripts = '<script src="/peace_seafood/inline-assets/js/settings/index.js"></script>'; ?>