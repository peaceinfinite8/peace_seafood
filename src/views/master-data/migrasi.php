<?php ?>
<div x-data="migrationPage()" x-init="init()">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2" style="color: var(--text-primary)">
                <i data-lucide="ship" class="w-7 h-7 text-blue-500 animate-bounce"></i>
                Pusat Migrasi Data
            </h2>
            <p class="text-sm" style="color: var(--text-secondary)">
                Oceanic Onboarding Hub: Impor data rekapan lama dari Excel/CSV atau scan foto buku catatan lapangan menggunakan AI OCR.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="downloadTemplate('penjualan')" class="btn btn-outline flex items-center gap-2" style="border: 1px solid var(--border-color); color: var(--text-primary)">
                <i data-lucide="download" class="w-4 h-4 text-emerald-500"></i>
                Template Penjualan (CSV)
            </button>
            <button type="button" @click="downloadTemplate('stok')" class="btn btn-outline flex items-center gap-2" style="border: 1px solid var(--border-color); color: var(--text-primary)">
                <i data-lucide="download" class="w-4 h-4 text-blue-500"></i>
                Template Stok Masuk (CSV)
            </button>
        </div>
    </div>

    <!-- Onboarding Overview Alert -->
    <div class="card p-5 mb-6 bg-gradient-to-r from-blue-50/50 to-indigo-50/50 dark:from-slate-800/20 dark:to-indigo-950/10 border-l-4 border-blue-500 shadow-sm">
        <div class="flex gap-4">
            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                <i data-lucide="info" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm" style="color: var(--text-primary)">Pusat Sinkronisasi Riwayat Transaksi</h4>
                <p class="text-xs mt-1 leading-relaxed" style="color: var(--text-secondary)">
                    Semua transaksi yang diimpor akan menggunakan <strong>Penyelaras Tanggal Historis (Backdated Entry)</strong> sesuai tanggal di catatan Anda. Entitas baru (Supplier, Pembeli, Produk) yang belum terdaftar di database akan disinkronkan secara otomatis (Smart Synchronization) dengan profil default tanpa memutus rantai stok.
                </p>
            </div>
        </div>
    </div>

    <!-- Tabs switcher -->
    <div class="keu-tab-group mb-6">
        <button @click="switchTab('excel')"
            class="keu-tab"
            :class="tab === 'excel' ? 'keu-tab--active keu-tab--semua' : 'keu-tab--idle'">
            <span class="flex items-center gap-2">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                CSV / Excel Sync
            </span>
        </button>
        <button @click="switchTab('ocr')"
            class="keu-tab"
            :class="tab === 'ocr' ? 'keu-tab--active keu-tab--semua' : 'keu-tab--idle'">
            <span class="flex items-center gap-2 font-semibold">
                <i data-lucide="sparkles" class="w-4 h-4 text-amber-500"></i>
                Scan Foto Buku (AI OCR)
            </span>
        </button>
    </div>

    <!-- TAB 1: EXCEL HISTORICAL SYNC -->
    <div x-show="tab === 'excel'" class="space-y-6">
        <!-- Upload Card -->
        <div x-show="!previewMode" class="card p-8 text-center border-2 border-dashed border-gray-300 dark:border-slate-700 hover:border-blue-500 transition-colors cursor-pointer relative"
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="handleDrop($event)"
            :class="dragOver ? 'border-blue-500 bg-blue-50/10' : ''">

            <input type="file" id="excelFile" class="hidden" accept=".xlsx,.xls,.csv" @change="handleFileSelect($event)">

            <div class="flex flex-col items-center justify-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-blue-50 dark:bg-slate-800 flex items-center justify-center shadow-inner">
                    <i data-lucide="upload-cloud" class="w-8 h-8 text-blue-500"></i>
                </div>
                <div>
                    <p class="font-bold text-base" style="color: var(--text-primary)">
                        Seret & Letakkan Berkas Di Sini
                    </p>
                    <p class="text-xs mt-1" style="color: var(--text-secondary)">
                        Mendukung berkas CSV (.csv) atau Excel (.xlsx, .xls) sesuai format template
                    </p>
                </div>
                <div>
                    <button class="btn btn-primary px-6" @click="document.getElementById('excelFile').click()">
                        Pilih Berkas Komputer
                    </button>
                </div>
            </div>
        </div>

        <!-- Loader -->
        <div x-show="loading" class="card p-12 text-center flex flex-col items-center justify-center space-y-4">
            <div class="w-12 h-12 rounded-full border-4 border-blue-500/20 border-t-blue-500 animate-spin"></div>
            <p class="text-sm font-semibold" style="color: var(--text-primary)" x-text="loadingText"></p>
        </div>

        <!-- Preview Mode -->
        <div x-show="previewMode && !loading" class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold" style="color: var(--text-primary)">Preview Lembar Impor</h3>
                    <p class="text-xs" style="color: var(--text-secondary)">Periksa dan verifikasi data hasil pembacaan berkas sebelum disimpan.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn btn-outline" @click="cancelPreview()">
                        Batal
                    </button>
                    <button class="btn btn-success flex items-center gap-2" @click="confirmImport()">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Konfirmasi & Proses Impor
                    </button>
                </div>
            </div>

            <!-- Preview Tabs Summary Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="card p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <i data-lucide="truck" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-xs" style="color: var(--text-secondary)">Supplier</p>
                        <p class="text-lg font-extrabold" style="color: var(--text-primary)" x-text="previewData.supplier.length"></p>
                    </div>
                </div>
                <div class="card p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <i data-lucide="users" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div>
                        <p class="text-xs" style="color: var(--text-secondary)">Pembeli</p>
                        <p class="text-lg font-extrabold" style="color: var(--text-primary)" x-text="previewData.pembeli.length"></p>
                    </div>
                </div>
                <div class="card p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                        <i data-lucide="download-cloud" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                    <div>
                        <p class="text-xs" style="color: var(--text-secondary)">Stok Masuk</p>
                        <p class="text-lg font-extrabold" style="color: var(--text-primary)" x-text="previewData.stok.length"></p>
                    </div>
                </div>
                <div class="card p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <i data-lucide="receipt" class="w-5 h-5 text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div>
                        <p class="text-xs" style="color: var(--text-secondary)">Penjualan</p>
                        <p class="text-lg font-extrabold" style="color: var(--text-primary)" x-text="previewData.penjualan.length"></p>
                    </div>
                </div>
            </div>

            <!-- Tabs Section inside Preview -->
            <div class="keu-tab-group mb-4">
                <button @click="previewTab = 'stok'" class="keu-tab" :class="previewTab === 'stok' ? 'keu-tab--active keu-tab--semua' : 'keu-tab--idle'">
                    Stok Masuk
                </button>
                <button @click="previewTab = 'penjualan'" class="keu-tab" :class="previewTab === 'penjualan' ? 'keu-tab--active keu-tab--semua' : 'keu-tab--idle'">
                    Penjualan
                </button>
                <button @click="previewTab = 'supplier'" class="keu-tab" :class="previewTab === 'supplier' ? 'keu-tab--active keu-tab--semua' : 'keu-tab--idle'">
                    Supplier
                </button>
                <button @click="previewTab = 'pembeli'" class="keu-tab" :class="previewTab === 'pembeli' ? 'keu-tab--active keu-tab--semua' : 'keu-tab--idle'">
                    Pembeli
                </button>
            </div>

            <!-- Preview Content tables -->
            <!-- 1. Stok Masuk Table -->
            <div x-show="previewTab === 'stok'" class="card overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b" style="border-color: var(--border-color); background: rgba(0,0,0,0.02)">
                            <th class="p-3 text-xs font-semibold uppercase">Tanggal</th>
                            <th class="p-3 text-xs font-semibold uppercase">Supplier</th>
                            <th class="p-3 text-xs font-semibold uppercase">Produk / Jenis Ikan</th>
                            <th class="p-3 text-xs font-semibold uppercase text-right">Berat Nota</th>
                            <th class="p-3 text-xs font-semibold uppercase text-right">Berat Riil</th>
                            <th class="p-3 text-xs font-semibold uppercase text-right">Harga</th>
                            <th class="p-3 text-xs font-semibold uppercase text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, idx) in previewData.stok" :key="idx">
                            <tr class="border-b hover:bg-gray-50/50 dark:hover:bg-slate-800/30" style="border-color: var(--border-color)">
                                <td class="p-3 text-xs" style="color: var(--text-primary)" x-text="item.tanggal"></td>
                                <td class="p-3 text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span style="color: var(--text-primary)" x-text="item.supplier"></span>
                                        <span class="text-[9px] px-1.5 py-0.5 rounded font-bold"
                                            :class="item.supplier_exist ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                                            x-text="item.supplier_exist ? 'Terdaftar' : 'Smart-Sync (Baru)'"></span>
                                    </div>
                                </td>
                                <td class="p-3 text-xs">
                                    <div class="flex flex-col gap-0.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-semibold" style="color: var(--text-primary)" x-text="item.produk"></span>
                                            <span class="text-[9px] px-1.5 py-0.5 rounded font-bold"
                                                :class="item.produk_exist ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                                                x-text="item.produk_exist ? 'Terdaftar' : 'Baru'"></span>
                                        </div>
                                        <div class="flex items-center gap-1 text-[10px]" style="color: var(--text-secondary)">
                                            <span>Jenis:</span>
                                            <span x-text="item.jenis_ikan"></span>
                                            <span class="text-[8px] px-1 rounded"
                                                :class="item.jenis_exist ? 'bg-green-100/50 text-green-700 dark:bg-green-900/10' : 'bg-amber-100/50 text-amber-700 dark:bg-amber-900/10'"
                                                x-text="item.jenis_exist ? '√' : 'Baru'"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 text-xs text-right font-medium" style="color: var(--text-primary)" x-text="formatQty(item.qty) + ' kg'"></td>
                                <td class="p-3 text-xs text-right font-medium" style="color: var(--text-primary)" x-text="formatQty(item.qty_actual) + ' kg'"></td>
                                <td class="p-3 text-xs text-right font-semibold" style="color: var(--text-primary)" x-text="formatMoney(item.harga_beli)"></td>
                                <td class="p-3 text-xs text-right font-bold text-blue-500" x-text="formatMoney(item.total)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- 2. Penjualan Table -->
            <div x-show="previewTab === 'penjualan'" class="card overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b" style="border-color: var(--border-color); background: rgba(0,0,0,0.02)">
                            <th class="p-3 text-xs font-semibold uppercase">Tanggal</th>
                            <th class="p-3 text-xs font-semibold uppercase">Nota</th>
                            <th class="p-3 text-xs font-semibold uppercase">Pembeli</th>
                            <th class="p-3 text-xs font-semibold uppercase">Produk</th>
                            <th class="p-3 text-xs font-semibold uppercase text-right">Berat</th>
                            <th class="p-3 text-xs font-semibold uppercase text-right">Harga Jual</th>
                            <th class="p-3 text-xs font-semibold uppercase text-right">Potongan</th>
                            <th class="p-3 text-xs font-semibold uppercase text-right">Total</th>
                            <th class="p-3 text-xs font-semibold uppercase">Metode</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, idx) in previewData.penjualan" :key="idx">
                            <tr class="border-b hover:bg-gray-50/50 dark:hover:bg-slate-800/30" style="border-color: var(--border-color)">
                                <td class="p-3 text-xs" style="color: var(--text-primary)" x-text="item.tanggal"></td>
                                <td class="p-3 text-xs font-mono font-bold" style="color: var(--text-primary)" x-text="item.no_nota"></td>
                                <td class="p-3 text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span style="color: var(--text-primary)" x-text="item.pembeli"></span>
                                        <span x-show="item.pembeli !== 'Umum'" class="text-[9px] px-1.5 py-0.5 rounded font-bold"
                                            :class="item.pembeli_exist ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                                            x-text="item.pembeli_exist ? 'Terdaftar' : 'Smart-Sync (Baru)'"></span>
                                    </div>
                                </td>
                                <td class="p-3 text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span style="color: var(--text-primary)" x-text="item.produk"></span>
                                        <span class="text-[9px] px-1.5 py-0.5 rounded font-bold"
                                            :class="item.produk_exist ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                                            x-text="item.produk_exist ? 'Terdaftar' : 'Baru'"></span>
                                    </div>
                                </td>
                                <td class="p-3 text-xs text-right font-medium" style="color: var(--text-primary)" x-text="formatQty(item.qty) + ' kg'"></td>
                                <td class="p-3 text-xs text-right font-semibold" style="color: var(--text-primary)" x-text="formatMoney(item.harga_jual)"></td>
                                <td class="p-3 text-xs text-right text-red-500" x-text="formatMoney(item.diskon)"></td>
                                <td class="p-3 text-xs text-right font-bold text-emerald-500" x-text="formatMoney(item.total)"></td>
                                <td class="p-3 text-xs">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide"
                                        :class="item.pembayaran === 'hutang' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'"
                                        x-text="item.pembayaran"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- 3. Supplier Table -->
            <div x-show="previewTab === 'supplier'" class="card overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b" style="border-color: var(--border-color); background: rgba(0,0,0,0.02)">
                            <th class="p-3 text-xs font-semibold uppercase">Nama</th>
                            <th class="p-3 text-xs font-semibold uppercase">Pemilik</th>
                            <th class="p-3 text-xs font-semibold uppercase">Telepon</th>
                            <th class="p-3 text-xs font-semibold uppercase">Alamat</th>
                            <th class="p-3 text-xs font-semibold uppercase">Rekening</th>
                            <th class="p-3 text-xs font-semibold uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, idx) in previewData.supplier" :key="idx">
                            <tr class="border-b hover:bg-gray-50/50 dark:hover:bg-slate-800/30" style="border-color: var(--border-color)">
                                <td class="p-3 text-xs font-bold" style="color: var(--text-primary)" x-text="item.nama"></td>
                                <td class="p-3 text-xs" style="color: var(--text-primary)" x-text="item.nama_pemilik || '-'"></td>
                                <td class="p-3 text-xs" style="color: var(--text-primary)" x-text="item.telpon || '-'"></td>
                                <td class="p-3 text-xs" style="color: var(--text-primary)" x-text="(item.alamat || '') + ' ' + (item.kota || '')"></td>
                                <td class="p-3 text-xs" style="color: var(--text-primary)">
                                    <span x-text="item.bank_name ? item.bank_name + ' - ' + item.bank_account : '-'"></span>
                                </td>
                                <td class="p-3 text-xs">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                        :class="item.status === 'Terdaftar' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'"
                                        x-text="item.status"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- 4. Pembeli Table -->
            <div x-show="previewTab === 'pembeli'" class="card overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b" style="border-color: var(--border-color); background: rgba(0,0,0,0.02)">
                            <th class="p-3 text-xs font-semibold uppercase">Nama</th>
                            <th class="p-3 text-xs font-semibold uppercase">Tipe</th>
                            <th class="p-3 text-xs font-semibold uppercase">Telepon</th>
                            <th class="p-3 text-xs font-semibold uppercase">Alamat</th>
                            <th class="p-3 text-xs font-semibold uppercase text-right">Limit Kredit</th>
                            <th class="p-3 text-xs font-semibold uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, idx) in previewData.pembeli" :key="idx">
                            <tr class="border-b hover:bg-gray-50/50 dark:hover:bg-slate-800/30" style="border-color: var(--border-color)">
                                <td class="p-3 text-xs font-bold" style="color: var(--text-primary)" x-text="item.nama"></td>
                                <td class="p-3 text-xs font-semibold uppercase" style="color: var(--text-primary)" x-text="item.tipe"></td>
                                <td class="p-3 text-xs" style="color: var(--text-primary)" x-text="item.telpon || '-'"></td>
                                <td class="p-3 text-xs" style="color: var(--text-primary)" x-text="(item.alamat || '') + ' ' + (item.kota || '')"></td>
                                <td class="p-3 text-xs text-right font-medium" style="color: var(--text-primary)" x-text="formatMoney(item.kredit_limit)"></td>
                                <td class="p-3 text-xs">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                        :class="item.status === 'Terdaftar' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'"
                                        x-text="item.status"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: SCAN FOTO BUKU (AI OCR) -->
    <div x-show="tab === 'ocr'" class="space-y-6">
        <!-- OCR Uploader / Radar simulation screen -->
        <div x-show="!ocrPreviewMode && !ocrScanning" class="card p-8 text-center border-2 border-dashed border-gray-300 dark:border-slate-700 hover:border-amber-500 transition-colors cursor-pointer"
            @dragover.prevent="ocrDragOver = true"
            @dragleave.prevent="ocrDragOver = false"
            @drop.prevent="handleOcrDrop($event)">

            <input type="file" id="ocrFile" class="hidden" accept="image/*" @change="handleOcrFileSelect($event)">

            <div class="flex flex-col items-center justify-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-amber-50 dark:bg-slate-800 flex items-center justify-center shadow-inner">
                    <i data-lucide="camera" class="w-8 h-8 text-amber-500 animate-pulse"></i>
                </div>
                <div>
                    <p class="font-bold text-base" style="color: var(--text-primary)">
                        Unggah Foto Catatan Buku / Kertas Lapangan
                    </p>
                    <p class="text-xs mt-1" style="color: var(--text-secondary)">
                        Ambil gambar halaman catatan transaksi manual Anda menggunakan kamera ponsel/tablet
                    </p>
                </div>
                <div>
                    <button class="btn btn-outline border-amber-500/50 hover:bg-amber-500/10 px-6" style="color: var(--text-primary)" @click="document.getElementById('ocrFile').click()">
                        Pilih Foto Catatan
                    </button>
                </div>
            </div>
        </div>

        <!-- Simulated Sonar Wave Scan Animation -->
        <div x-show="ocrScanning" class="card p-8 flex flex-col items-center justify-center space-y-6 relative overflow-hidden min-h-[300px]">
            <!-- Ocean Waves Scanning Effect -->
            <div class="absolute inset-0 bg-gradient-to-t from-blue-500/5 to-transparent pointer-events-none"></div>

            <!-- Sonar Pulse Ring -->
            <div class="relative w-32 h-32 flex items-center justify-center">
                <div class="absolute inset-0 rounded-full border border-blue-500/40 animate-ping"></div>
                <div class="absolute inset-4 rounded-full border border-teal-500/30 animate-pulse"></div>
                <div class="w-20 h-20 rounded-full bg-blue-500/10 flex items-center justify-center shadow-lg border border-blue-500/20">
                    <i data-lucide="scan-eye" class="w-10 h-10 text-blue-500"></i>
                </div>
            </div>

            <div class="text-center z-10 max-w-md">
                <h4 class="font-bold text-base" style="color: var(--text-primary)" x-text="ocrStatusTitle">Membaca Tulisan Tangan...</h4>
                <div class="w-64 h-2 bg-gray-200 dark:bg-slate-800 rounded-full overflow-hidden mt-3 mx-auto">
                    <div class="h-full bg-gradient-to-r from-blue-500 to-teal-500 transition-all duration-300" :style="'width: ' + ocrProgress + '%'"></div>
                </div>
                <p class="text-xs mt-2" style="color: var(--text-secondary)" x-text="ocrStatusDesc">Sonar OCR sedang memproses citra...</p>
            </div>
        </div>

        <!-- OCR Preview Mode (Editable Grid) -->
        <div x-show="ocrPreviewMode && !ocrScanning" class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold" style="color: var(--text-primary)">
                        Hasil Ekstraksi Transaksi
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">AI OCR Verified</span>
                    </h3>
                    <p class="text-xs" style="color: var(--text-secondary)">Perbaiki jika ada kesalahan baca sebelum menyinkronkan data.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn btn-outline" @click="cancelOcrPreview()">
                        Batal
                    </button>
                    <button class="btn btn-success flex items-center gap-2" @click="confirmOcrImport()">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Simpan Transaksi OCR ke Database
                    </button>
                </div>
            </div>

            <!-- OCR Editable Stok Masuk Grid -->
            <div class="card p-6 space-y-4">
                <h4 class="font-bold text-sm border-b pb-2 flex items-center gap-2" style="color: var(--text-primary); border-color: var(--border-color)">
                    <i data-lucide="download-cloud" class="w-4 h-4 text-blue-500"></i>
                    Catatan Stok Masuk (Tangkapan Buku)
                </h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b" style="border-color: var(--border-color); background: rgba(0,0,0,0.02)">
                                <th class="p-3 text-xs font-semibold uppercase w-32">Tanggal</th>
                                <th class="p-3 text-xs font-semibold uppercase">Nama Supplier</th>
                                <th class="p-3 text-xs font-semibold uppercase">Nama Jenis Ikan</th>
                                <th class="p-3 text-xs font-semibold uppercase">Nama Produk</th>
                                <th class="p-3 text-xs font-semibold uppercase text-right w-24">Berat Nota</th>
                                <th class="p-3 text-xs font-semibold uppercase text-right w-24">Berat Riil</th>
                                <th class="p-3 text-xs font-semibold uppercase text-right w-36">Harga (Rp/kg)</th>
                                <th class="p-3 text-xs font-semibold uppercase text-right w-32">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, idx) in previewData.stok" :key="idx">
                                <tr class="border-b hover:bg-gray-50/50 dark:hover:bg-slate-800/30" style="border-color: var(--border-color)">
                                    <td class="p-2">
                                        <input type="date" x-model="item.tanggal" class="form-input text-xs w-full py-1 px-2">
                                    </td>
                                    <td class="p-2">
                                        <div class="flex flex-col gap-1">
                                            <input type="text" x-model="item.supplier" class="form-input text-xs w-full py-1 px-2 font-medium">
                                            <span class="text-[9px] font-bold px-1 py-0.5 rounded self-start"
                                                :class="item.supplier_exist ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'"
                                                x-text="item.supplier_exist ? 'Terdaftar' : 'Baru (Auto-Sync)'"></span>
                                        </div>
                                    </td>
                                    <td class="p-2">
                                        <input type="text" x-model="item.jenis_ikan" class="form-input text-xs w-full py-1 px-2">
                                    </td>
                                    <td class="p-2">
                                        <div class="flex flex-col gap-1">
                                            <input type="text" x-model="item.produk" class="form-input text-xs w-full py-1 px-2">
                                            <span class="text-[9px] font-bold px-1 py-0.5 rounded self-start"
                                                :class="item.produk_exist ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'"
                                                x-text="item.produk_exist ? 'Terdaftar' : 'Baru (Auto-Sync)'"></span>
                                        </div>
                                    </td>
                                    <td class="p-2">
                                        <input type="number" step="any" x-model.number="item.qty" class="form-input text-xs w-full py-1 px-2 text-right font-medium" @input="item.total = item.qty_actual * item.harga_beli">
                                    </td>
                                    <td class="p-2">
                                        <input type="number" step="any" x-model.number="item.qty_actual" class="form-input text-xs w-full py-1 px-2 text-right font-medium" @input="item.total = item.qty_actual * item.harga_beli">
                                    </td>
                                    <td class="p-2">
                                        <input type="number" x-model.number="item.harga_beli" class="form-input text-xs w-full py-1 px-2 text-right font-semibold" @input="item.total = item.qty_actual * item.harga_beli">
                                    </td>
                                    <td class="p-2 text-right text-xs font-bold text-blue-500 pr-4" x-text="formatMoney(item.total)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- OCR Editable Penjualan Grid -->
            <div class="card p-6 space-y-4">
                <h4 class="font-bold text-sm border-b pb-2 flex items-center gap-2" style="color: var(--text-primary); border-color: var(--border-color)">
                    <i data-lucide="receipt" class="w-4 h-4 text-emerald-500"></i>
                    Catatan Penjualan (Tangkapan Buku)
                </h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b" style="border-color: var(--border-color); background: rgba(0,0,0,0.02)">
                                <th class="p-3 text-xs font-semibold uppercase w-32">Tanggal</th>
                                <th class="p-3 text-xs font-semibold uppercase">Nota</th>
                                <th class="p-3 text-xs font-semibold uppercase">Nama Pembeli</th>
                                <th class="p-3 text-xs font-semibold uppercase">Nama Produk</th>
                                <th class="p-3 text-xs font-semibold uppercase text-right w-24">Berat</th>
                                <th class="p-3 text-xs font-semibold uppercase text-right w-36">Harga Jual</th>
                                <th class="p-3 text-xs font-semibold uppercase text-right w-28">Diskon (Rp)</th>
                                <th class="p-3 text-xs font-semibold uppercase text-right w-32">Total</th>
                                <th class="p-3 text-xs font-semibold uppercase w-28">Metode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, idx) in previewData.penjualan" :key="idx">
                                <tr class="border-b hover:bg-gray-50/50 dark:hover:bg-slate-800/30" style="border-color: var(--border-color)">
                                    <td class="p-2">
                                        <input type="date" x-model="item.tanggal" class="form-input text-xs w-full py-1 px-2">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" x-model="item.no_nota" class="form-input text-xs w-full py-1 px-2 font-mono font-bold">
                                    </td>
                                    <td class="p-2">
                                        <div class="flex flex-col gap-1">
                                            <input type="text" x-model="item.pembeli" class="form-input text-xs w-full py-1 px-2">
                                            <span x-show="item.pembeli !== 'Umum'" class="text-[9px] font-bold px-1 py-0.5 rounded self-start"
                                                :class="item.pembeli_exist ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'"
                                                x-text="item.pembeli_exist ? 'Terdaftar' : 'Baru (Auto-Sync)'"></span>
                                        </div>
                                    </td>
                                    <td class="p-2">
                                        <div class="flex flex-col gap-1">
                                            <input type="text" x-model="item.produk" class="form-input text-xs w-full py-1 px-2">
                                            <span class="text-[9px] font-bold px-1 py-0.5 rounded self-start"
                                                :class="item.produk_exist ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'"
                                                x-text="item.produk_exist ? 'Terdaftar' : 'Baru (Auto-Sync)'"></span>
                                        </div>
                                    </td>
                                    <td class="p-2">
                                        <input type="number" step="any" x-model.number="item.qty" class="form-input text-xs w-full py-1 px-2 text-right" @input="item.subtotal = item.qty * item.harga_jual; item.total = Math.max(0, item.subtotal - item.diskon)">
                                    </td>
                                    <td class="p-2">
                                        <input type="number" x-model.number="item.harga_jual" class="form-input text-xs w-full py-1 px-2 text-right" @input="item.subtotal = item.qty * item.harga_jual; item.total = Math.max(0, item.subtotal - item.diskon)">
                                    </td>
                                    <td class="p-2">
                                        <input type="number" x-model.number="item.diskon" class="form-input text-xs w-full py-1 px-2 text-right" @input="item.total = Math.max(0, item.subtotal - item.diskon)">
                                    </td>
                                    <td class="p-2 text-right text-xs font-bold text-emerald-500 pr-4" x-text="formatMoney(item.total)"></td>
                                    <td class="p-2">
                                        <select x-model="item.pembayaran" class="form-input text-xs w-full py-1 px-1">
                                            <option value="cash">CASH</option>
                                            <option value="hutang">HUTANG</option>
                                        </select>
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

<?php $scripts = <<<'JS'
<script>
function migrationPage() {
    return {
        tab: 'excel', // 'excel' or 'ocr'
        previewMode: false,
        ocrPreviewMode: false,
        dragOver: false,
        ocrDragOver: false,
        loading: false,
        loadingText: 'Memuat berkas...',
        ocrScanning: false,
        ocrProgress: 0,
        ocrStatusTitle: '',
        ocrStatusDesc: '',
        previewTab: 'stok', // 'stok', 'penjualan', 'supplier', 'pembeli'
        
        previewData: {
            supplier: [],
            pembeli: [],
            stok: [],
            penjualan: []
        },

        init() {
            // Check roles
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            if (!['super_admin','admin'].includes(user.role)) {
                window.location.href = '/peace_seafood/dashboard';
                return;
            }
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
            });
        },

        switchTab(target) {
            this.tab = target;
            this.previewMode = false;
            this.ocrPreviewMode = false;
            this.ocrScanning = false;
            this.clearPreviewData();
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
            });
        },

        async downloadTemplate(type) {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.get('/peace_seafood/api/migrasi/template?type=' + type, {
                    headers: { Authorization: 'Bearer ' + token },
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const a = document.createElement('a');
                a.href = url;
                a.download = type === 'stok' ? 'template_migrasi_stok_masuk.csv' : 'template_migrasi_penjualan.csv';
                document.body.appendChild(a);
                a.click();
                setTimeout(() => window.URL.revokeObjectURL(url), 1000);
                a.remove();
            } catch (err) {
                console.error(err);
                iziToast.error({ title: 'Gagal Mengunduh', message: 'Tidak dapat mengunduh template. Pastikan Anda sudah login.', position: 'topRight' });
            }
        },

        clearPreviewData() {
            this.previewData = {
                supplier: [],
                pembeli: [],
                stok: [],
                penjualan: []
            };
        },

        formatQty(qty) {
            if (qty === undefined || qty === null) return '0';
            return parseFloat(qty).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },

        formatMoney(amount) {
            if (amount === undefined || amount === null) return 'Rp 0';
            return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
        },

        cancelPreview() {
            this.previewMode = false;
            this.clearPreviewData();
        },

        cancelOcrPreview() {
            this.ocrPreviewMode = false;
            this.clearPreviewData();
        },

        // EXCEL EVENT HANDLERS
        handleDrop(e) {
            this.dragOver = false;
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.uploadFile(files[0]);
            }
        },

        handleFileSelect(e) {
            const files = e.target.files;
            if (files.length > 0) {
                this.uploadFile(files[0]);
            }
        },

        async uploadFile(file) {
            this.loading = true;
            this.loadingText = 'Mengunggah & menganalisis berkas migrasi...';
            
            const formData = new FormData();
            formData.append('file', file);
            
            try {
                const token = localStorage.getItem('token');
                const res = await axios.post('/peace_seafood/api/migrasi/excel/preview', formData, {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'multipart/form-data'
                    }
                });
                
                this.previewData = res.data.data;
                this.previewMode = true;
                
                // Set active preview tab based on what's available
                if (this.previewData.stok.length > 0) {
                    this.previewTab = 'stok';
                } else if (this.previewData.penjualan.length > 0) {
                    this.previewTab = 'penjualan';
                } else if (this.previewData.supplier.length > 0) {
                    this.previewTab = 'supplier';
                } else {
                    this.previewTab = 'pembeli';
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berkas Berhasil Dibaca!',
                    text: 'Silakan periksa rangkuman preview data di bawah sebelum menyimpan.',
                    timer: 2500,
                    showConfirmButton: false
                });
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Membaca Berkas',
                    text: err.response?.data?.message || 'Pastikan file sesuai template.',
                });
            } finally {
                this.loading = false;
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            }
        },

        async confirmImport() {
            const totalRecords = this.previewData.stok.length + this.previewData.penjualan.length;
            
            const confirm = await Swal.fire({
                title: 'Apakah Anda Yakin?',
                html: `Anda akan mengimpor <strong>${totalRecords} baris transaksi historis</strong> serta menyinkronkan data supplier/pembeli ke dalam database. Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Proses Migrasi!',
                cancelButtonText: 'Batal'
            });

            if (!confirm.isConfirmed) return;

            this.loading = true;
            this.loadingText = 'Menyimpan data transaksi historis ke database...';

            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/migrasi/excel/import', {
                    data: this.previewData
                }, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Migrasi Berhasil!',
                    text: 'Semua entitas dan riwayat keuangan/stok lama telah terintegrasi.',
                    confirmButtonText: 'Mantap!'
                });

                this.previewMode = false;
                this.clearPreviewData();
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Impor Data',
                    text: err.response?.data?.message || 'Terjadi kesalahan sistem saat menyimpan.'
                });
            } finally {
                this.loading = false;
            }
        },

        // OCR EVENT HANDLERS
        handleOcrDrop(e) {
            this.ocrDragOver = false;
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.scanOcrImage(files[0]);
            }
        },

        handleOcrFileSelect(e) {
            const files = e.target.files;
            if (files.length > 0) {
                this.scanOcrImage(files[0]);
            }
        },

        async scanOcrImage(file) {
            this.ocrScanning = true;
            this.ocrProgress = 15;
            this.ocrStatusTitle = 'Mengunggah Gambar Catatan...';
            this.ocrStatusDesc = 'Sonar AI sedang mentransfer berkas citra.';

            // Simulation animation steps
            const timer1 = setTimeout(() => {
                this.ocrProgress = 45;
                this.ocrStatusTitle = 'Membaca Tulisan Tangan...';
                this.ocrStatusDesc = 'AI sedang memindai pola teks & garis baris.';
            }, 800);

            const timer2 = setTimeout(() => {
                this.ocrProgress = 80;
                this.ocrStatusTitle = 'Sinkronisasi Entitas Kamus...';
                this.ocrStatusDesc = 'Mencari kecocokan produk & nama supplier.';
            }, 1800);

            const formData = new FormData();
            formData.append('file', file);

            try {
                const token = localStorage.getItem('token');
                const res = await axios.post('/peace_seafood/api/migrasi/ocr/preview', formData, {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'multipart/form-data'
                    }
                });

                // Complete progress
                this.ocrProgress = 100;
                this.ocrStatusTitle = 'Analisis OCR Selesai!';
                this.ocrStatusDesc = 'Tabel grid berhasil disiapkan.';

                setTimeout(() => {
                    this.previewData = res.data.data;
                    this.ocrScanning = false;
                    this.ocrPreviewMode = true;
                    this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                }, 400);

            } catch (err) {
                clearTimeout(timer1);
                clearTimeout(timer2);
                this.ocrScanning = false;
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal OCR Gambar',
                    text: err.response?.data?.message || 'Pastikan foto memiliki pencahayaan cukup dan teks terbaca.',
                });
            }
        },

        async confirmOcrImport() {
            const confirm = await Swal.fire({
                title: 'Simpan Hasil OCR?',
                text: 'Semua perubahan yang Anda buat di grid tabel akan disimpan secara permanen ke database.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Ya, Simpan Transaksi!',
                cancelButtonText: 'Batal'
            });

            if (!confirm.isConfirmed) return;

            this.ocrScanning = true;
            this.ocrStatusTitle = 'Menyinkronkan Database...';
            this.ocrStatusDesc = 'Memproses penambahan stok & rekapan penjualan.';
            this.ocrProgress = 50;

            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/migrasi/ocr/import', {
                    data: this.previewData
                }, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Disimpan!',
                    text: 'Riwayat transaksi buku logbook Anda telah terdaftar dalam sistem.',
                    confirmButtonText: 'Selesai'
                });

                this.ocrPreviewMode = false;
                this.clearPreviewData();
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Simpan Gagal',
                    text: err.response?.data?.message || 'Terjadi gangguan internal saat memproses.'
                });
            } finally {
                this.ocrScanning = false;
            }
        }
    };
}
</script>
JS;
?>