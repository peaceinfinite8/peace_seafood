<?php /** @var string $activeMenu */ ?>

<div x-data="activityLogPage()" x-init="init()">

    <!-- Header -->
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Activity Log</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Audit trail aktivitas pengguna sistem</p>
        </div>
        <button class="btn btn-secondary" @click="loadData()">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Refresh
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="card p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--text-secondary)">Tampilkan</label>
                <select class="form-input w-36" x-model="limit" @change="loadData()">
                    <option value="50">50 entri</option>
                    <option value="100">100 entri</option>
                    <option value="200">200 entri</option>
                    <option value="500">500 entri</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--text-secondary)">Filter Aksi</label>
                <select class="form-input w-36" x-model="filterAction" @change="applyFilter()">
                    <option value="">Semua Aksi</option>
                    <option value="INSERT">INSERT</option>
                    <option value="UPDATE">UPDATE</option>
                    <option value="DELETE">DELETE</option>
                    <option value="MIGRATION">MIGRATION</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--text-secondary)">Filter Tabel</label>
                <select class="form-input w-44" x-model="filterTable" @change="applyFilter()">
                    <option value="">Semua Tabel</option>
                    <template x-for="t in availableTables" :key="t">
                        <option :value="t" x-text="t"></option>
                    </template>
                </select>
            </div>
            <div class="ml-auto text-sm" style="color:var(--text-secondary)">
                Menampilkan
                <span class="font-semibold" style="color:var(--text-primary)" x-text="filteredLogs.length"></span>
                dari <span x-text="logs.length"></span> entri
            </div>
        </div>
    </div>

    <!-- Tabel Log -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:155px">Waktu</th>
                        <th style="width:150px">Pengguna</th>
                        <th style="width:95px">Aksi</th>
                        <th style="width:130px">Modul</th>
                        <th>Keterangan Perubahan</th>
                        <th style="width:60px" class="text-center">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="6" class="text-center py-10" style="color:var(--text-secondary)">
                                <i data-lucide="loader-2" class="w-5 h-5 inline animate-spin mr-2"></i> Memuat data...
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && filteredLogs.length === 0">
                        <tr>
                            <td colspan="6" class="text-center py-10" style="color:var(--text-secondary)">
                                <i data-lucide="inbox" class="w-5 h-5 inline mr-2"></i> Tidak ada log aktivitas
                            </td>
                        </tr>
                    </template>
                    <template x-for="log in filteredLogs" :key="log.id">
                        <tr>
                            <td class="whitespace-nowrap text-sm" style="color:var(--text-secondary)"
                                x-text="formatDateTime(log.timestamp)"></td>
                            <td>
                                <div class="flex flex-col gap-1">
                                    <span class="font-medium text-sm leading-tight"
                                        x-text="log.nama_user || '-'"></span>
                                    <span class="badge badge-gray self-start text-xs"
                                        x-text="(log.role_user || '').replace(/_/g,' ').toUpperCase()"></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge" :class="{
                                        'badge-success': log.action === 'INSERT',
                                        'badge-warning': log.action === 'UPDATE',
                                        'badge-danger':  log.action === 'DELETE',
                                        'badge-info':    log.action === 'MIGRATION',
                                        'badge-gray':    !['INSERT','UPDATE','DELETE','MIGRATION'].includes(log.action)
                                    }" x-text="log.action">
                                </span>
                            </td>
                            <td>
                                <div class="text-sm font-medium" x-text="tableLabel(log.table_name)"></div>
                                <div class="text-xs" style="color:var(--text-secondary)">
                                    ID #<span x-text="log.record_id"></span>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm leading-relaxed" x-html="buildNarrative(log)"></div>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-ghost btn-xs" title="Lihat data mentah" @click="openDetail(log)">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Detail JSON -->
    <div x-show="detailLog !== null" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background:rgba(0,0,0,0.55); display:none" @click.self="detailLog = null">
        <div class="card w-full max-w-2xl max-h-[85vh] flex flex-col" style="background:var(--bg-card)">
            <div class="flex items-center justify-between p-4 border-b" style="border-color:var(--border-color)">
                <div>
                    <h3 class="font-bold" style="color:var(--text-primary)">Detail Perubahan</h3>
                    <p class="text-xs mt-0.5" style="color:var(--text-secondary)"
                        x-text="detailLog ? (tableLabel(detailLog.table_name) + ' #' + detailLog.record_id + ' — ' + formatDateTime(detailLog.timestamp)) : ''">
                    </p>
                </div>
                <button class="btn btn-ghost btn-sm" @click="detailLog = null">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="overflow-y-auto p-4 space-y-4" x-show="detailLog !== null">
                <!-- Before -->
                <template x-if="detailLog && detailLog.before_value">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide mb-2 flex items-center gap-1"
                            style="color:var(--text-secondary)">
                            <i data-lucide="arrow-left-circle" class="w-3.5 h-3.5"></i> Sebelum
                        </div>
                        <pre class="text-xs p-3 rounded-lg overflow-x-auto leading-relaxed"
                            style="background:var(--bg-secondary);color:var(--text-primary)"
                            x-text="prettyJson(detailLog.before_value)"></pre>
                    </div>
                </template>
                <!-- After -->
                <template x-if="detailLog && detailLog.after_value">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide mb-2 flex items-center gap-1"
                            style="color:var(--text-secondary)">
                            <i data-lucide="arrow-right-circle" class="w-3.5 h-3.5"></i> Sesudah
                        </div>
                        <pre class="text-xs p-3 rounded-lg overflow-x-auto leading-relaxed"
                            style="background:var(--bg-secondary);color:var(--text-primary)"
                            x-text="prettyJson(detailLog.after_value)"></pre>
                    </div>
                </template>
                <!-- Kosong -->
                <template x-if="detailLog && !detailLog.before_value && !detailLog.after_value">
                    <p class="text-sm text-center py-6" style="color:var(--text-secondary)">
                        Tidak ada data detail tersimpan untuk entri ini.
                    </p>
                </template>
            </div>
        </div>
    </div>

</div>

<?php $scripts = '<script src="/peace_seafood/inline-assets/js/activity-log/index.js"></script>'; ?>