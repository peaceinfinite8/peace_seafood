<?php ?>
<div x-data="settingsPage()" x-init="init()" class="space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Pengaturan Sistem</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Konfigurasi global platform, akun pengguna, cabang
                gudang, dan pencadangan database</p>
        </div>
    </div>

    <!-- Main Grid Split Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">

        <!-- Left Sidebar Navigation -->
        <div class="lg:col-span-1 space-y-2">
            <div
                class="rounded-xl border border-slate-200 bg-white p-3 space-y-1 shadow-sm dark:border-slate-800 dark:bg-slate-900">
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
                    <label class="text-3xs mb-1 block text-slate-500 dark:text-slate-400">Pilih Gudang</label>
                    <select x-model="selectedGudangId"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        @change="loadSettingsForGudang()">
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
            <div x-show="tab === 'umum'"
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-6"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2">
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Pengaturan Aplikasi</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Konfigurasi operasional gudang, batas
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
                            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">Logo Perusahaan / Gudang
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Logo ini akan tampil di navigasi
                                sidebar utama dan cetakan nota Anda.</p>
                        </div>
                    </div>
                    <div>
                        <button type="button" @click="triggerLogoUpload()"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700 shadow-lg shadow-blue-500/10">
                            <i data-lucide="camera" class="w-4 h-4"></i>
                            <span>Pilih & Potong Logo Baru</span>
                        </button>
                        <input type="file" id="logo-file-input" @change="onLogoSelected($event)" accept="image/*"
                            class="hidden">
                    </div>
                </div>

                <!-- Empty state -->
                <template x-if="settings.length === 0">
                    <p class="text-sm py-4 text-slate-500 dark:text-slate-400">Memuat pengaturan...</p>
                </template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <template
                        x-for="setting in settings.filter(x => x.kunci !== 'company_logo_base64' && x.kunci !== 'company_logo_initial')"
                        :key="setting.kunci">
                        <div
                            class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex flex-col justify-between">
                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-slate-100"
                                    x-text="setting.label || setting.kunci"></label>

                                <!-- Toggle (boolean: 0/1) -->
                                <template x-if="isToggle(setting.kunci)">
                                    <div class="flex items-center gap-3 mt-1.5">
                                        <button type="button"
                                            @click="setting.nilai = setting.nilai == '1' ? '0' : '1'; saveSetting(setting)"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200"
                                            :class="setting.nilai == '1' ? 'bg-blue-600' : 'bg-slate-300 dark:bg-slate-600'"
                                            :aria-checked="setting.nilai == '1'" role="switch">
                                            <span
                                                class="inline-block h-5 w-5 rounded-full bg-white shadow transition-transform duration-200"
                                                :class="setting.nilai == '1' ? 'translate-x-5' : 'translate-x-1'"></span>
                                        </button>
                                        <span class="text-xs font-semibold"
                                            :class="setting.nilai == '1' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400'"
                                            x-text="setting.nilai == '1' ? 'Aktif' : 'Nonaktif'">
                                        </span>
                                    </div>
                                </template>

                                <!-- Select dropdown (enum) -->
                                <template x-if="isSelect(setting.kunci)">
                                    <div class="flex gap-2 mt-1.5">
                                        <select x-model="setting.nilai"
                                            class="flex-1 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                            <template x-for="opt in getSelectOptions(setting.kunci)" :key="opt.value">
                                                <option :value="opt.value" x-text="opt.label"></option>
                                            </template>
                                        </select>
                                        <button @click="saveSetting(setting)"
                                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 p-2 text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700">
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
                                            class="flex-1 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                        <input type="text" x-show="!isNumber(setting.kunci)" x-model="setting.nilai"
                                            class="flex-1 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                        <button @click="saveSetting(setting)"
                                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 p-2 text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700">
                                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <p class="mt-2 text-3xs text-slate-500 dark:text-slate-400"
                                x-text="setting.keterangan || ''"></p>
                        </div>
                    </template>
                </div>

                <!-- Database Backup Component Section -->
                <div x-show="['super_admin', 'saas_owner'].includes(user.role)"
                    class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-800" x-cloak>
                    <h4 class="mb-2 text-sm font-bold text-slate-900 dark:text-slate-100">
                        <i data-lucide="database" class="w-4 h-4 inline mr-1 text-blue-500"></i>
                        Pencadangan Sistem & Database
                    </h4>
                    <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">
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
                                <p class="text-xs font-bold text-slate-900 dark:text-slate-100">Target Database:
                                    peace_seafood</p>
                                <p class="text-3xs text-slate-400 dark:text-slate-500">Koneksi: Aktif & Terlindungi</p>
                            </div>
                        </div>
                        <button type="button" @click="runBackup()"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"
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
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Rekening Bank BOS</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Kelola rekening tujuan transfer pembayaran
                            nota penjualan timbangan lapangan</p>
                    </div>
                    <button @click="openAddBank()"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Tambah Rekening</span>
                    </button>
                </div>

                <!-- Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="b in bankAccounts" :key="b.id">
                        <div class="relative overflow-hidden rounded-xl border p-5 transition-all duration-300 group hover:shadow-md"
                            :class="b.is_active ? 'border-blue-200 bg-blue-50/50 dark:border-blue-900/30 dark:bg-slate-900' : 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900'">

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
                                        <h4 class="text-lg font-mono font-bold tracking-wider text-slate-900 dark:text-slate-100"
                                            x-text="b.account_number"></h4>
                                        <p
                                            class="mt-1 text-3xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            Pemilik Rekening</p>
                                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100"
                                            x-text="b.account_name"></p>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end justify-between h-full min-h-[90px]">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-extrabold"
                                        :class="b.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300'"
                                        x-text="b.is_active ? 'AKTIF' : 'NON-AKTIF'"></span>
                                    <div class="flex gap-1.5 mt-4">
                                        <button @click="openEditBank(b)"
                                            class="inline-flex items-center justify-center rounded-lg p-1.5 text-slate-600 transition-colors duration-200 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                                            <i data-lucide="pencil" class="w-3 h-3"></i>
                                        </button>
                                        <button @click="deleteBank(b.id)"
                                            class="inline-flex items-center justify-center rounded-lg p-1.5 text-rose-600 transition-colors duration-200 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-900/20">
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
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Manajemen User</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pemberian hak akses staf kasir, lapangan
                            (checker), dan bos eksekutif</p>
                    </div>
                    <button @click="openAddUser()"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Tambah User</span>
                    </button>
                </div>

                <div
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
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
                                                <div
                                                    class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-blue-600 dark:bg-slate-800 dark:text-blue-400">
                                                    <span x-text="u.name ? u.name.charAt(0).toUpperCase() : 'U'"></span>
                                                </div>
                                                <span class="font-bold text-sm text-slate-800 dark:text-slate-200"
                                                    x-text="u.name"></span>
                                            </div>
                                        </td>
                                        <td class="text-sm" x-text="u.email"></td>
                                        <td>
                                            <span
                                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-wider"
                                                :class="u.role==='super_admin'||u.role==='bos' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400' : u.role==='admin' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400'"
                                                x-text="u.role"></span>
                                        </td>
                                        <td class="text-sm text-slate-600 dark:text-slate-400"
                                            x-text="u.nama_gudang || 'Semua Gudang'"></td>
                                        <td>
                                            <span
                                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold"
                                                :class="u.is_active?'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400':'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300'"
                                                x-text="u.is_active?'AKTIF':'NONAKTIF'"></span>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <button @click="openEditUser(u)"
                                                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white p-1.5 text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600"><i
                                                        data-lucide="pencil" class="w-3.5 h-3.5"></i></button>
                                                <button @click="deleteUser(u.id)"
                                                    class="inline-flex items-center justify-center rounded-lg border border-rose-300 bg-rose-50 p-1.5 text-rose-700 shadow-sm transition hover:bg-rose-100 dark:border-rose-900/30 dark:bg-rose-900/20 dark:text-rose-400 dark:hover:bg-rose-900/30"
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
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Kelola Cabang Gudang</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Manajemen cabang/gudang penampungan
                            timbangan dan penanggung jawab</p>
                    </div>
                    <button @click="openAddGudang()"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700">
                        <i data-lucide="building-2" class="w-4 h-4"></i>
                        <span>Tambah Gudang</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="g in gudang" :key="g.id">
                        <div
                            class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 transition-all duration-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                            <div>
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                            <i data-lucide="building" class="w-4 h-4"></i>
                                        </div>
                                        <h3 class="font-bold text-base text-slate-900 dark:text-slate-100"
                                            x-text="g.nama"></h3>
                                    </div>
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold"
                                        :class="g.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300'"
                                        x-text="g.is_active ? 'AKTIF' : 'NONAKTIF'"></span>
                                </div>
                                <div class="mt-4 space-y-2 pt-1 text-xs text-slate-500 dark:text-slate-400">
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
                                        <span>Bos Penanggung Jawab: <strong class="text-slate-900 dark:text-slate-100"
                                                x-text="g.nama_bos || 'Belum di-assign'"></strong></span>
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex gap-2 justify-end mt-5 pt-3 border-t border-slate-100 dark:border-slate-800">
                                <button @click="openEditGudang(g)"
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-[10px] font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600">
                                    <i data-lucide="pencil" class="w-3 h-3"></i> Edit
                                </button>
                                <button @click="deleteGudang(g.id)"
                                    class="inline-flex items-center gap-1 rounded-lg border border-rose-300 bg-rose-50 px-2.5 py-1 text-[10px] font-semibold text-rose-700 shadow-sm transition hover:bg-rose-100 dark:border-rose-900/30 dark:bg-rose-900/20 dark:text-rose-400 dark:hover:bg-rose-900/30">
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
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100"
                            x-text="['super_admin', 'saas_owner'].includes(user.role) ? 'SaaS Developer / Owner Panel' : 'Status Langganan Gudang'">
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400"
                            x-text="['super_admin', 'saas_owner'].includes(user.role) ? 'Pusat kendali komersialisasi trial Bos, pre-approval email pendaftaran, bypass impersonate, dan support WhatsApp.' : 'Informasi masa aktif langganan dan kontak pengembang sistem.'">
                        </p>
                    </div>
                </div>

                <!-- ====== BOS ONLY: Subscription Status Card ====== -->
                <div x-show="user.role === 'bos'" x-cloak class="space-y-4">

                    <!-- Kartu Status Trial Per Gudang -->
                    <template x-for="g in gudang" :key="g.id">
                        <div class="rounded-xl border p-5 text-left transition-all duration-300"
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
                                        <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100"
                                            x-text="g.nama"></h4>
                                        <p class="text-xs text-slate-500 dark:text-slate-400"
                                            x-text="g.kota || 'Lokasi belum diset'"></p>
                                    </div>
                                </div>
                                <!-- Status Badge -->
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black"
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
                                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100"
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
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-5 text-left dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-center gap-2 pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
                            <i data-lucide="headphones" class="w-4 h-4 text-emerald-500"></i>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">Hubungi Tim Pengembang</h4>
                        </div>
                        <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">Untuk perpanjangan langganan,
                            pertanyaan, atau kendala teknis, hubungi pengembang sistem melalui WhatsApp di bawah ini.
                        </p>
                        <a :href="'https://wa.me/' + devWhatsappNumber + '?text=' + encodeURIComponent('Halo Developer, saya ' + user.name + ' ingin menanyakan tentang langganan Peace Seafood WMS.')"
                            target="_blank" rel="noopener noreferrer"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#25D366] px-4 py-3 text-sm font-bold text-white transition hover:opacity-95">
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
                        class="md:col-span-2 rounded-xl border border-slate-200 bg-white p-5 text-left space-y-4 dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                            <i data-lucide="user-plus" class="w-4 h-4 text-blue-500"></i>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">Pra-Persetujuan
                                (Pre-Approve) Akun Bos Baru</h4>
                        </div>

                        <form @submit.prevent="runPreApprove()" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Nama
                                    Lengkap Calon Bos *</label>
                                <input type="text" x-model="preApproveForm.name"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                                    placeholder="Contoh: Bos Ronald" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Email
                                    Gmail Calon Bos *</label>
                                <input type="email" x-model="preApproveForm.email"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                                    placeholder="Contoh: ronald@gmail.com" required>
                            </div>
                            <div
                                class="sm:col-span-2 flex flex-col sm:flex-row items-stretch sm:items-end justify-between gap-4">
                                <div class="flex-1">
                                    <label
                                        class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Durasi
                                        Masa Trial Uji Coba *</label>
                                    <select x-model="preApproveForm.trial_days"
                                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                        <option value="7">7 Hari (1 Uji Coba)</option>
                                        <option value="14">14 Hari (2 Uji Coba - Default)</option>
                                        <option value="30">30 Hari (1 Bulan)</option>
                                        <option value="60">60 Hari (2 Bulan)</option>
                                        <option value="90">90 Hari (3 Bulan)</option>
                                    </select>
                                </div>
                                <button type="submit"
                                    class="inline-flex h-[38px] items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-5 py-2 text-xs font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700 shadow-lg shadow-blue-500/10"
                                    :disabled="preApproving">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span x-text="preApproving ? 'Memproses...' : 'Setujui Email'"></span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Developer WhatsApp Support Setting -->
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-5 text-left space-y-4 dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">No. WhatsApp Developer</h4>
                        </div>

                        <div class="space-y-3">
                            <label class="mb-1 block text-xs font-medium text-slate-500">Nomor WhatsApp Support (Gunakan
                                format 628xxx tanpa tanda + atau 0 di depan)</label>
                            <div class="flex gap-2">
                                <input type="text" x-model="devWhatsappNumber"
                                    class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                                    placeholder="Contoh: 628123456789">
                                <button type="button" @click="saveDevWhatsapp()"
                                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 p-2 text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"
                                    :disabled="savingWa">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Gudang SaaS Listing with Expiry Adjustments & Impersonation (Developer Only) -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    x-show="['super_admin', 'saas_owner'].includes(user.role)" x-cloak>
                    <div
                        class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 text-left">
                        <h4 class="text-sm font-bold flex items-center gap-1.5 text-slate-900 dark:text-slate-100">
                            <i data-lucide="layout-grid" class="w-4.5 h-4.5 text-blue-500"></i>
                            Daftar Gudang Tenant SaaS & Masa Sewa
                        </h4>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
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
                                        <td class="font-bold text-sm text-slate-900 dark:text-slate-100"
                                            x-text="g.nama"></td>
                                        <td class="text-sm" x-text="g.kota || '-'"></td>
                                        <td>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100"
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
                                                    class="w-36 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                                <span class="text-3xs text-slate-400"
                                                    x-text="getRemainingDaysText(g.subscription_until)"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <select :value="g.status_langganan || 'aktif'"
                                                @change="updateGudangStatus(g, $event.target.value)"
                                                class="w-28 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                                <option value="aktif">Aktif</option>
                                                <option value="suspend">Suspend</option>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <button type="button" @click="impersonateUser(g.id_bos)"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-blue-200/50 bg-blue-50 px-2 py-1 text-[10px] font-bold text-blue-600 transition hover:bg-blue-100 dark:border-blue-900/20 dark:bg-blue-950/20 dark:text-blue-400">
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
    <div class="fixed inset-0 z-[1000] flex items-center justify-center bg-black/50 px-4 py-6" x-show="showGudangModal"
        @click.self="showGudangModal = false" x-cloak>
        <div
            class="w-full max-w-md rounded-xl border border-slate-200 bg-white p-5 shadow-xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg" x-text="editGudangId ? 'Edit Gudang' : 'Tambah Gudang'"></h3>
                <button @click="showGudangModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="saveGudang()">
                <div class="space-y-1">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Nama Gudang
                        *</label>
                    <input type="text" x-model="gudangForm.nama"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        placeholder="Nama gudang/cabang baru" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Kota *</label>
                        <input type="text" x-model="gudangForm.kota"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            placeholder="Kota lokasi" required>
                    </div>
                    <div class="space-y-1">
                        <label
                            class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Telepon/Kontak</label>
                        <input type="text" x-model="gudangForm.telpon"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            placeholder="No. telpon">
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Alamat Lengkap
                        *</label>
                    <textarea x-model="gudangForm.alamat"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        placeholder="Alamat jalan lengkap gudang" rows="3" required></textarea>
                </div>
                <div class="space-y-1">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Bos / Penanggung
                        Jawab *</label>
                    <select x-model="gudangForm.id_bos"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        required>
                        <option value="">-- Pilih Bos --</option>
                        <template x-for="u in users.filter(x => x.role === 'bos')" :key="u.id">
                            <option :value="u.id" x-text="u.name"></option>
                        </template>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Status
                        Operasional</label>
                    <select x-model="gudangForm.is_active"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="flex gap-3 mt-5 justify-end">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"
                        :disabled="savingGudang"
                        x-text="savingGudang ? 'Menyimpan...' : (editGudangId ? 'Update' : 'Simpan')"></button>
                    <button type="button" @click="showGudangModal = false"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bank Modal -->
    <div class="fixed inset-0 z-[1000] flex items-center justify-center bg-black/50 px-4 py-6" x-show="showBankModal"
        @click.self="showBankModal = false" x-cloak>
        <div
            class="w-full max-w-sm rounded-xl border border-slate-200 bg-white p-5 shadow-xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg" x-text="editBankId ? 'Edit Rekening' : 'Tambah Rekening'"></h3>
                <button @click="showBankModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="saveBank()">
                <div class="space-y-1">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Nama Bank *</label>
                    <select x-model="bankForm.bank_name"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        required>
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
                <div class="space-y-1">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Nomor Rekening
                        *</label>
                    <input type="text" x-model="bankForm.account_number"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        placeholder="Masukkan nomor rekening" required>
                </div>
                <div class="space-y-1">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Nama Pemilik
                        *</label>
                    <input type="text" x-model="bankForm.account_name"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        placeholder="Nama terdaftar pemilik bank" required>
                </div>
                <div class="space-y-1">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Status</label>
                    <select x-model="bankForm.is_active"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="flex gap-3 mt-5 justify-end">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"
                        :disabled="savingBank"
                        x-text="savingBank ? 'Menyimpan...' : (editBankId ? 'Update' : 'Simpan')"></button>
                    <button type="button" @click="showBankModal = false"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- User Modal -->
    <div class="fixed inset-0 z-[1000] flex items-center justify-center bg-black/50 px-4 py-6" x-show="showUserModal"
        @click.self="showUserModal = false" x-cloak>
        <div
            class="w-full max-w-sm rounded-xl border border-slate-200 bg-white p-5 shadow-xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg" x-text="editUserId ? 'Edit User' : 'Tambah User'"></h3>
                <button @click="showUserModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="saveUser()">
                <div class="space-y-1">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Nama Lengkap
                        *</label>
                    <input type="text" x-model="userForm.name"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        placeholder="Nama user baru" required>
                </div>
                <div class="space-y-1">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Email Aktif
                        *</label>
                    <input type="email" x-model="userForm.email"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        placeholder="Contoh: user@example.com" required>
                </div>
                <div class="space-y-1">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300"
                        x-text="editUserId ? 'Password (kosongkan jika tidak diubah)' : 'Password *'"></label>
                    <input type="password" x-model="userForm.password"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        :placeholder="editUserId ? 'Minimal 8 karakter' : 'Masukkan password'" :required="!editUserId">
                </div>
                <div class="space-y-1">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Role *</label>
                    <select x-model="userForm.role"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        required>
                        <template x-for="r in availableRoles" :key="r.value">
                            <option :value="r.value" x-text="r.label"></option>
                        </template>
                    </select>
                </div>
                <div class="space-y-1"
                    x-show="userForm.role !== 'bos' && userForm.role !== 'super_admin' && userForm.role !== 'saas_owner'">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Gudang
                        Alokasi</label>
                    <select x-model="userForm.id_gudang"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        :required="userForm.role !== 'bos' && userForm.role !== 'super_admin'">
                        <option value="">-- Pilih Gudang Cabang --</option>
                        <template x-for="g in gudang" :key="g.id">
                            <option :value="g.id" x-text="g.nama"></option>
                        </template>
                    </select>
                </div>
                <div class="flex gap-3 mt-5 justify-end">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700"
                        :disabled="saving"
                        x-text="saving ? 'Menyimpan...' : (editUserId ? 'Update' : 'Simpan')"></button>
                    <button type="button" @click="showUserModal = false"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- WhatsApp-Style Cropper Modal -->
    <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 px-4 py-6" x-show="showCropModal"
        @click.self="showCropModal = false" x-cloak>
        <div class="w-full max-w-md rounded-xl border border-slate-800 bg-slate-950 p-5 text-slate-100 shadow-xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="flex items-center gap-2 text-base font-bold text-slate-100">
                    <i data-lucide="crop" class="w-4 h-4 text-blue-500"></i>
                    Sesuaikan Logo Perusahaan
                </h3>
                <button type="button" @click="showCropModal = false"
                    class="text-slate-400 transition hover:text-slate-200">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Crop Workspace Area -->
            <div
                class="relative flex h-[320px] w-full aspect-square items-center justify-center overflow-hidden rounded-lg border border-slate-800 bg-black">
                <!-- Image inside workspace -->
                <img id="crop-image" :src="cropImageSrc" class="max-w-full max-h-full select-none animate-fade-in"
                    style="transform-origin: center center; cursor: move;"
                    :style="`transform: translate(${cropPanX}px, ${cropPanY}px) scale(${cropZoom}) rotate(${cropRotate}deg)`">

                <!-- Circular WhatsApp Overlay Mask -->
                <div class="absolute inset-0 pointer-events-none rounded-lg border-2 border-dashed border-white/30"
                    style="background: radial-gradient(circle, transparent 110px, rgba(15,23,42,0.8) 110px);">
                </div>
            </div>

            <!-- Controls -->
            <div class="mt-5 space-y-4">
                <!-- Zoom Slider -->
                <div class="flex items-center gap-3">
                    <i data-lucide="minus" class="w-4 h-4 text-slate-400"></i>
                    <input type="range" min="0.5" max="3.5" step="0.05" x-model="cropZoom"
                        class="w-full h-1.5 rounded-lg appearance-none cursor-pointer bg-slate-700 accent-blue-500">
                    <i data-lucide="plus" class="w-4 h-4 text-slate-400"></i>
                </div>

                <!-- Pan & Rotate Quick Buttons -->
                <div class="flex items-center justify-between gap-3">
                    <button type="button" @click="cropRotate = (cropRotate - 90) % 360"
                        class="inline-flex flex-1 items-center justify-center gap-1 rounded-lg border border-slate-600 bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-100 transition hover:bg-slate-700">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        Putar -90°
                    </button>

                    <button type="button" @click="cropRotate = (cropRotate + 90) % 360"
                        class="inline-flex flex-1 items-center justify-center gap-1 rounded-lg border border-slate-600 bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-100 transition hover:bg-slate-700">
                        <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i>
                        Putar 90°
                    </button>
                </div>

                <p class="text-center text-[10px] text-slate-400">Gunakan slider untuk zoom. Seret gambar di dalam
                    lingkaran untuk memposisikan.</p>
            </div>

            <div class="flex gap-3 mt-6 justify-end">
                <button type="button" @click="showCropModal = false"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-600 bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-100 transition hover:bg-slate-700">Batal</button>
                <button type="button" @click="applyCrop()"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 dark:bg-blue-700">
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
        viewMode: 'tenant', // 'platform' or 'tenant' - UI-only switch
        settings: [],
        selectedGudangId: '',
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
        
        // SaaS Developer States
        preApproving: false,
        savingWa: false,
        devWhatsappNumber: '628123456789',
        preApproveForm: { name: '', email: '', trial_days: '14' },

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
            if (this.user.role !== 'super_admin' && this.user.role !== 'saas_owner' && this.user.role !== 'bos') {
                window.location.href = `${window.APP_BASE_URL}/dashboard`;
                return;
            }
            await this.loadAll();
            // Default view mode: platform for saas_owner/super_admin, tenant otherwise
            if (['super_admin','saas_owner'].includes(this.user.role)) this.viewMode = 'platform';
            else this.viewMode = 'tenant';
            
            // Prefill WhatsApp Number
            const wa = this.settings.find(x => x.kunci === 'platform_developer_whatsapp');
            if (wa && wa.nilai) {
                this.devWhatsappNumber = wa.nilai;
            }
            // Default selected gudang for platform users: first in list
            if (['super_admin','saas_owner'].includes(this.user.role)) {
                if (this.gudang && this.gudang.length) this.selectedGudangId = this.gudang[0].id;
            } else if (this.user.role === 'bos') {
                this.selectedGudangId = this.user.id_gudang || '';
            }
            // If tenant view and a gudang selected, load its settings
            if (this.viewMode === 'tenant' && this.selectedGudangId) await this.loadSettingsForGudang();
            
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadAll() {
            const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
            const [setRes, usrRes, gudRes, bankRes] = await Promise.all([
                axios.get(`${window.API_BASE_URL}/settings`, { headers }),
                axios.get(`${window.API_BASE_URL}/settings/users`, { headers }),
                axios.get(`${window.API_BASE_URL}/settings/gudang`, { headers }),
                axios.get(`${window.API_BASE_URL}/settings/bank-accounts`, { headers }),
            ]);
            this.settings = setRes.data?.data || [];
            this.users    = usrRes.data?.data || [];
            this.gudang   = gudRes.data?.data || [];
            this.bankAccounts = bankRes.data?.data || [];
            
            this.syncLogoFromSettings();
        },

        syncLogoFromSettings() {
            const logo = this.settings.find(x => x.kunci === 'company_logo_base64');
            this.logoBase64 = logo ? logo.nilai : '';
        },

        async loadSettingsForPlatform() {
            const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
            const res = await axios.get(`${window.API_BASE_URL}/settings`, { headers });
            this.settings = res.data?.data || [];
            this.syncLogoFromSettings();
        },

        async loadSettingsForGudang() {
            try {
                const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
                if (!this.selectedGudangId) {
                    // reload default server-provided settings
                    const res = await axios.get(`${window.API_BASE_URL}/settings`, { headers });
                    this.settings = res.data?.data || [];
                } else {
                    const res = await axios.get(`${window.API_BASE_URL}/settings`, { headers, params: { id_gudang: this.selectedGudangId } });
                    this.settings = res.data?.data || [];
                }
                this.syncLogoFromSettings();
            } catch (e) {
                console.error(e);
            }
        },

        async switchViewMode(mode) {
            this.viewMode = mode;
            this.tab = mode === 'platform' ? 'saas' : 'umum';
            if (mode === 'tenant') {
                await this.loadSettingsForGudang();
            } else {
                await this.loadSettingsForPlatform();
            }
        },

        formatNumberDot(val) {
            if (val === undefined || val === null || val === '') return '0';
            const num = parseInt(String(val).replace(/\D/g, '')) || 0;
            return num.toLocaleString('id-ID');
        },

        get availableRoles() {
            if (['super_admin', 'saas_owner'].includes(this.user.role)) {
                return [
                    { value: 'super_admin', label: 'Super Admin' },
                    { value: 'saas_owner', label: 'SaaS Owner' },
                    { value: 'bos', label: 'Bos (Executive Owner)' },
                    { value: 'admin', label: 'Admin Gudang' },
                    { value: 'checker', label: 'Checker Lapangan' },
                ];
            }
            return [
                { value: 'admin', label: 'Admin Gudang' },
                { value: 'checker', label: 'Checker Lapangan' },
            ];
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
                const headers = { Authorization: 'Bearer ' + token };
                // If editing a specific gudang as platform user, pass id_gudang param
                const params = {};
                if (this.viewMode === 'tenant' && this.selectedGudangId) params.id_gudang = this.selectedGudangId;
                await axios.put(`${window.API_BASE_URL}/settings/` + setting.kunci + (Object.keys(params).length ? '?id_gudang=' + params.id_gudang : ''), { nilai: setting.nilai }, { headers });
                iziToast.success({ title: 'Berhasil', message: 'Setting disimpan', position: 'topRight' });
                // Reload only settings (not all lists) to reflect canonical server values
                if (this.viewMode === 'tenant' && this.selectedGudangId) await this.loadSettingsForGudang();
                else await this.loadSettingsForPlatform();
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal simpan', position: 'topRight' }); }
        },

        openAddBank() { this.editBankId = null; this.bankForm = { bank_name: '', account_number: '', account_name: '', is_active: '1' }; this.showBankModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        openEditBank(b) { this.editBankId = b.id; this.bankForm = { bank_name: b.bank_name, account_number: b.account_number, account_name: b.account_name, is_active: String(b.is_active ?? '1') }; this.showBankModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },

        async saveBank() {
            this.savingBank = true;
            try {
                const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
                this.bankForm.is_active = String(this.bankForm.is_active || '1');
                if (this.editBankId) { await axios.put(`${window.API_BASE_URL}/settings/bank-accounts/` + this.editBankId, this.bankForm, { headers }); }
                else { await axios.post(`${window.API_BASE_URL}/settings/bank-accounts`, this.bankForm, { headers }); }
                iziToast.success({ title: 'Berhasil', message: 'Rekening tersimpan', position: 'topRight' });
                this.showBankModal = false; await this.reloadBanks();
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
            this.savingBank = false;
        },

        async reloadBanks() {
            const token = localStorage.getItem('token');
            const res = await axios.get(`${window.API_BASE_URL}/settings/bank-accounts`, { headers: { Authorization: 'Bearer ' + token } });
            this.bankAccounts = res.data?.data || [];
        },

        async deleteBank(id) {
            if (!await confirm('Nonaktifkan rekening ini?')) return;
            const token = localStorage.getItem('token');
            await axios.delete(`${window.API_BASE_URL}/settings/bank-accounts/` + id, { headers: { Authorization: 'Bearer ' + token } });
            iziToast.success({ title: 'Berhasil', message: 'Rekening dinonaktifkan', position: 'topRight' });
            await this.loadAll();
        },

        openAddUser() { this.editUserId = null; this.userForm = { name: '', email: '', password: '', role: 'admin', id_gudang: '' }; this.showUserModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        openEditUser(u) { this.editUserId = u.id; this.userForm = { name: u.name, email: u.email, password: '', role: u.role, id_gudang: u.id_gudang||'' }; this.showUserModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },

        async saveUser() {
            this.saving = true;
            try {
                const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
                if (this.editUserId) { await axios.put(`${window.API_BASE_URL}/settings/users/` + this.editUserId, this.userForm, { headers }); }
                else { await axios.post(`${window.API_BASE_URL}/settings/users`, this.userForm, { headers }); }
                iziToast.success({ title: 'Berhasil', message: 'User tersimpan', position: 'topRight' });
                this.showUserModal = false; await this.loadAll();
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
            this.saving = false;
        },

        async deleteUser(id) {
            if (!await confirm('Hapus user ini?')) return;
            const token = localStorage.getItem('token');
            await axios.delete(`${window.API_BASE_URL}/settings/users/` + id, { headers: { Authorization: 'Bearer ' + token } });
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
                    await axios.put(`${window.API_BASE_URL}/settings/gudang/` + this.editGudangId, this.gudangForm, { headers });
                } else {
                    await axios.post(`${window.API_BASE_URL}/settings/gudang`, this.gudangForm, { headers });
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
                await axios.delete(`${window.API_BASE_URL}/settings/gudang/` + id, { headers: { Authorization: 'Bearer ' + token } });
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
                    url: `${window.API_BASE_URL}/settings/backup`,
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

        /* ── SaaS Developer Methods ── */
        async runPreApprove() {
            this.preApproving = true;
            try {
                const token = localStorage.getItem('token');
                const res = await axios.post(`${window.API_BASE_URL}/settings/pre-approve`, this.preApproveForm, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                iziToast.success({ title: 'Berhasil', message: res.data?.message || 'Email Bos sukses disetujui!', position: 'topRight' });
                this.preApproveForm = { name: '', email: '', trial_days: '14' };
                await this.loadAll();
            } catch (e) {
                const msg = e.response?.data?.message || 'Gagal pra-persetujuan.';
                iziToast.error({ title: 'Gagal', message: msg, position: 'topRight' });
            } finally {
                this.preApproving = false;
            }
        },

        async saveDevWhatsapp() {
            this.savingWa = true;
            try {
                const token = localStorage.getItem('token');
                await axios.put(`${window.API_BASE_URL}/settings/platform_developer_whatsapp`, {
                    nilai: this.devWhatsappNumber
                }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                iziToast.success({ title: 'Berhasil', message: 'No. WhatsApp Developer diperbarui!', position: 'topRight' });
                await this.loadAll();
            } catch (e) {
                iziToast.error({ title: 'Gagal', message: 'Gagal memperbarui WhatsApp Developer.', position: 'topRight' });
            } finally {
                this.savingWa = false;
            }
        },

        getBosEmail(idBos) {
            const u = this.users.find(x => x.id === idBos);
            return u ? u.email : '';
        },

        getRemainingDaysText(expiryStr) {
            if (!expiryStr) return '(Trial Belum Dimulai)';
            const diffTime = new Date(expiryStr) - new Date();
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            if (diffDays < 0) return '(Masa Aktif Habis)';
            return `(${diffDays} hari tersisa)`;
        },

        getRemainingDaysNum(expiryStr) {
            if (!expiryStr) return 999; // belum dimulai → tampil normal (tidak expired)
            const diffTime = new Date(expiryStr) - new Date();
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        },

        async updateGudangSubscription(gudangObj, newDate) {
            try {
                const token = localStorage.getItem('token');
                await axios.put(`${window.API_BASE_URL}/settings/gudang/` + gudangObj.id, {
                    nama: gudangObj.nama,
                    id_bos: gudangObj.id_bos,
                    alamat: gudangObj.alamat,
                    kota: gudangObj.kota,
                    telpon: gudangObj.telpon,
                    is_active: gudangObj.is_active,
                    subscription_until: newDate,
                    status_langganan: gudangObj.status_langganan
                }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                iziToast.success({ title: 'Berhasil', message: 'Masa aktif sewa diperbarui!', position: 'topRight' });
                gudangObj.subscription_until = newDate;
            } catch (e) {
                iziToast.error({ title: 'Gagal', message: 'Gagal memperbarui masa aktif.', position: 'topRight' });
            }
        },

        async updateGudangStatus(gudangObj, newStatus) {
            try {
                const token = localStorage.getItem('token');
                await axios.put(`${window.API_BASE_URL}/settings/gudang/` + gudangObj.id, {
                    nama: gudangObj.nama,
                    id_bos: gudangObj.id_bos,
                    alamat: gudangObj.alamat,
                    kota: gudangObj.kota,
                    telpon: gudangObj.telpon,
                    is_active: gudangObj.is_active,
                    subscription_until: gudangObj.subscription_until,
                    status_langganan: newStatus
                }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                iziToast.success({ title: 'Berhasil', message: 'Status sewa gudang diperbarui!', position: 'topRight' });
                gudangObj.status_langganan = newStatus;
            } catch (e) {
                iziToast.error({ title: 'Gagal', message: 'Gagal memperbarui status sewa.', position: 'topRight' });
            }
        },

        async impersonateUser(idBos) {
            if (!idBos) return;
            try {
                const token = localStorage.getItem('token');
                const res = await axios.post(`${window.API_BASE_URL}/auth/impersonate`, {
                    user_id: idBos
                }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                const data = res.data?.data;
                if (data?.token) {
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    iziToast.success({ title: 'Sukses', message: 'Impersonating Bos, mengalihkan...', position: 'topRight' });
                    setTimeout(() => {
                        window.location.href = `${window.APP_BASE_URL}/dashboard`;
                    }, 1000);
                }
            } catch (e) {
                const msg = e.response?.data?.message || 'Gagal impersonate.';
                iziToast.error({ title: 'Gagal', message: msg, position: 'topRight' });
            }
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