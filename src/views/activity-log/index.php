<?php

/** @var string $activeMenu */
?>
<div x-data="activityLogPage()" x-init="init()">

    {{-- ── Header ── --}}
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Activity Log</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Audit trail aktivitas pengguna sistem</p>
        </div>
        <button class="btn btn-secondary" @click="loadData()">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Refresh
        </button>
    </div>

    {{-- ── Filter Bar ── --}}
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
                Menampilkan <span class="font-semibold" style="color:var(--text-primary)" x-text="filteredLogs.length"></span>
                dari <span x-text="logs.length"></span> entri
            </div>
        </div>
    </div>

    {{-- ── Tabel Log ── --}}
    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:150px">Waktu</th>
                        <th style="width:140px">Pengguna</th>
                        <th style="width:90px">Aksi</th>
                        <th style="width:130px">Modul</th>
                        <th style="width:120px">Ref</th>
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
                        <tr class="hover:bg-opacity-50" style="transition:background 0.15s">
                            <td class="whitespace-nowrap text-sm" style="color:var(--text-secondary)" x-text="formatDateTime(log.timestamp)"></td>
                            <td>
                                <div class="font-medium text-sm" x-text="log.nama_user || '-'"></div>
                                <div class="text-xs mt-0.5">
                                    <span class="badge badge-gray" x-text="(log.role_user || '').replace('_',' ').toUpperCase()"></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge"
                                    :class="{
                                        'badge-success': log.action === 'INSERT',
                                        'badge-warning': log.action === 'UPDATE',
                                        'badge-danger':  log.action === 'DELETE',
                                        'badge-info':    log.action === 'MIGRATION',
                                        'badge-gray':    !['INSERT','UPDATE','DELETE','MIGRATION'].includes(log.action)
                                    }"
                                    x-text="log.action">
                                </span>
                            </td>
                            <td>
                                <div class="text-sm font-medium" x-text="tableLabel(log.table_name)"></div>
                                <div class="text-xs" style="color:var(--text-secondary)">
                                    #<span x-text="log.record_id"></span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap">
                                <div x-show="log.ref">
                                    <button class="text-sm text-primary underline btn-ghost" @click="openRef(log)">Lihat #<span x-text="log.ref.id"></span></button>
                                </div>
                                <div x-show="!log.ref" class="text-sm text-secondary">-</div>
                            </td>
                            <td>
                                <div class="text-sm" x-html="buildNarrative(log)"></div>
                            </td>
                            <td class="text-center">
                                <button
                                    class="btn btn-ghost btn-xs"
                                    title="Lihat detail JSON"
                                    @click="openDetail(log)">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Modal Detail ── --}}
    <template x-if="detailLog">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background:rgba(0,0,0,0.5)"
            @click.self="detailLog = null">
            <div class="card w-full max-w-2xl max-h-[85vh] flex flex-col" style="background:var(--bg-card)">
                <div class="flex items-center justify-between p-4 border-b" style="border-color:var(--border-color)">
                    <div>
                        <h3 class="font-bold" style="color:var(--text-primary)">Detail Perubahan</h3>
                        <p class="text-xs mt-0.5" style="color:var(--text-secondary)"
                            x-text="tableLabel(detailLog.table_name) + ' #' + detailLog.record_id + ' — ' + formatDateTime(detailLog.timestamp)">
                        </p>
                    </div>
                    <button class="btn btn-ghost btn-sm" @click="detailLog = null">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="overflow-y-auto p-4 space-y-4">
                    <template x-if="detailLog.before_value">
                        <div>
                            <div class="text-xs font-semibold uppercase mb-2" style="color:var(--text-secondary)">
                                <i data-lucide="arrow-left-circle" class="w-3.5 h-3.5 inline mr-1"></i> Sebelum
                            </div>
                            <div class="text-sm p-3 rounded-lg" style="background:var(--bg-secondary);color:var(--text-primary);line-height:1.6" x-html="buildNarrative(Object.assign({}, detailLog, { before_value: detailLog.before_value }))"></div>
                        </div>
                    </template>
                    <template x-if="detailLog.after_value">
                        <div>
                            <div class="text-xs font-semibold uppercase mb-2" style="color:var(--text-secondary)">
                                <i data-lucide="arrow-right-circle" class="w-3.5 h-3.5 inline mr-1"></i> Sesudah
                            </div>
                            <div class="text-sm p-3 rounded-lg" style="background:var(--bg-secondary);color:var(--text-primary);line-height:1.6" x-html="buildNarrative(Object.assign({}, detailLog, { after_value: detailLog.after_value }))"></div>
                        </div>
                    </template>
                    <template x-if="!detailLog.before_value && !detailLog.after_value">
                        <p class="text-sm text-center py-4" style="color:var(--text-secondary)">Tidak ada data detail tersimpan.</p>
                    </template>
                </div>
            </div>
        </div>
    </template>

</div>

<script>
    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return String(unsafe)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function humanizeStatus(s) {
        if (!s) return s;
        s = String(s).toLowerCase();
        if (s === 'draft') return 'Draft';
        if (s === 'final') return 'Final';
        if (s === 'confirmed') return 'Terkonfirmasi';
        return s.charAt(0).toUpperCase() + s.slice(1);
    }

    function activityLogPage() {
        return {
            logs: [],
            filteredLogs: [],
            availableTables: [],
            filterAction: '',
            filterTable: '',
            limit: 100,
            loading: false,
            detailLog: null,

            init() {
                this.loadData();
            },

            async loadData() {
                this.loading = true;
                try {
                    const res = await axios.get('/peace_seafood/api/activity-log?limit=' + this.limit);
                    this.logs = res.data || [];
                    this.filteredLogs = this.logs.slice();
                    this.availableTables = Array.from(new Set(this.logs.map(l => l.table_name)));
                } catch (e) {
                    console.error(e);
                }
                this.loading = false;
            },

            applyFilter() {
                this.filteredLogs = this.logs.filter(l => {
                    if (this.filterAction && l.action !== this.filterAction) return false;
                    if (this.filterTable && l.table_name !== this.filterTable) return false;
                    return true;
                });
            },

            tableLabel(name) {
                const map = {
                    nota: 'Nota Penjualan',
                    stok_masuk: 'Stok Masuk',
                    timbangan: 'Timbangan',
                    stok_transfer: 'Stok Transfer',
                    titipan: 'Penitipan',
                    retur: 'Retur'
                };
                return map[name] || name;
            },

            formatDateTime(dt) {
                return Utils.formatDateTime(dt);
            },

            buildNarrative(log) {
                try {
                    const before = log.before_value || null;
                    const after = log.after_value || null;
                    const t = log.table_name;
                    const a = log.action;

                    // NOTE: prefer after data for inserts, compare for updates
                    if (a === 'INSERT') {
                        if (t === 'stok_masuk' && after && after.qty !== undefined) {
                            return `Menambah stok masuk baru sebesar <strong>${formatKg(after.qty, 2)}</strong>.`;
                        }
                        if (t === 'timbangan' && after) {
                            const id = after.id_stok_masuk || log.record_id || '-';
                            return `Mencatat timbangan untuk Stok Masuk #${escapeHtml(id)} — berat riil <strong>${formatKg(after.qty_actual || after.qty_teoritis || 0,2)}</strong>.`;
                        }
                        if (t === 'nota' && after) {
                            return `Membuat Nota baru (ID #${log.record_id}).`;
                        }
                    }

                    if (a === 'UPDATE') {
                        // status changes
                        const beforeStatus = before && (before.status || before.status_before || null);
                        const afterStatus = after && (after.status || after.status_after || null) || (after && after.status) || (before && before.status && after && after.status ? after.status : null);
                        if (before && after && 'status' in before && 'status' in after) {
                            return `Mengubah status ${this.tableLabel(t)} #${log.record_id} dari <strong>${humanizeStatus(before.status)}</strong> menjadi <strong>${humanizeStatus(after.status)}</strong>.`;
                        }

                        // generic update: list changed keys
                        if (before && after) {
                            const changed = [];
                            for (const k of Object.keys(after)) {
                                const bv = before[k];
                                const av = after[k];
                                if (JSON.stringify(bv) !== JSON.stringify(av)) {
                                    changed.push(`${escapeHtml(k)}: "${escapeHtml(String(bv))}" → "${escapeHtml(String(av))}"`);
                                }
                            }
                            if (changed.length) return `Memperbarui ${this.tableLabel(t)} #${log.record_id}: ${changed.join(', ')}.`;
                        }
                    }

                    if (a === 'DELETE') {
                        return `Menghapus record pada ${this.tableLabel(t)} (ID #${log.record_id}).`;
                    }

                    // default fallback: short summary
                    return `${escapeHtml(a)} pada ${this.tableLabel(t)} #${log.record_id}`;
                } catch (e) {
                    return escapeHtml(JSON.stringify(log));
                }
            },

            openDetail(log) {
                // Show human-readable detail — do not expose raw JSON
                this.detailLog = log;
            },

            async openRef(log) {
                try {
                    const token = localStorage.getItem('token');
                    const res = await axios.get('/peace_seafood/api/activity-log/resource', { params: { table: log.table_name, id: log.record_id }, headers: { Authorization: 'Bearer ' + token } });
                    this.resourceDetail = res.data || {};
                    this.resourceRaw = JSON.stringify(this.resourceDetail, null, 2);
                    this.showResourceModal = true;
                } catch (e) {
                    iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal memuat resource', position: 'topRight' });
                }
            },

            prettyJson(obj) {
                try {
                    return JSON.stringify(obj, null, 2);
                } catch (e) {
                    return String(obj);
                }
            }
            ,
            // Resource modal state
            showResourceModal: false,
            resourceDetail: null,
            resourceRaw: null,
            showRaw: false
        }
    }
</script>

<!-- Resource Modal (opened when user clicks REF) -->
<template x-if="showResourceModal">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5)" @click.self="showResourceModal = false">
        <div class="card w-full max-w-2xl max-h-[85vh] overflow-y-auto p-4" style="background:var(--bg-card)">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold" style="color:var(--text-primary)">Detail Referensi</h3>
                <div class="flex items-center gap-2">
                    <button x-show="(JSON.parse(localStorage.getItem('user')||'{}')).role === 'super_admin'" class="btn btn-outline btn-sm" @click="showRaw = !showRaw">Show raw JSON</button>
                    <button class="btn btn-ghost btn-sm" @click="showResourceModal = false"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
            </div>

            <template x-if="resourceDetail">
                <div>
                    <template x-if="!showRaw">
                        <div class="space-y-2 text-sm">
                            <template x-for="(v,k) in resourceDetail" :key="k">
                                <div>
                                    <div class="text-xs text-secondary" x-text="k"></div>
                                    <div class="font-medium" x-text="(typeof v === 'object' ? JSON.stringify(v) : v)"></div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="showRaw">
                        <pre class="text-xs p-3 rounded-lg overflow-auto" style="background:var(--bg-secondary);color:var(--text-primary);line-height:1.5" x-text="resourceRaw"></pre>
                    </template>
                </div>
            </template>
        </div>
    </div>
</template>