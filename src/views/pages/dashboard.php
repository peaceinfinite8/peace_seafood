<?php

/**
 * Dashboard Page
 */
?>

<link rel="stylesheet" href="/peace_seafood/inline-assets/css/pages/dashboard.css">

<div x-data="dashboardPage()" x-init="init()" class="dashboard-shell">
    <div class="mb-2">
        <h2 class="text-xl font-bold" style="color: var(--text-primary)">
            Selamat datang, <span x-text="user.name || 'User'"></span> | <span class="uppercase font-semibold text-sm"
                style="color: var(--color-primary)" x-text="(user.role || '').toUpperCase()"></span>
        </h2>
        <p class="text-sm mt-1" style="color: var(--text-secondary)">
            <span x-text="today"></span>
            <span x-show="user.nama_gudang"> - <span class="font-medium" x-text="user.nama_gudang"></span></span>
        </p>
    </div>

    <div x-show="loading" class="flex items-center justify-center py-20">
        <div class="animate-spin w-8 h-8 rounded-full border-4 border-blue-200"
            style="border-top-color: var(--color-primary)"></div>
    </div>

    <div x-show="!loading" x-cloak>
        <!-- =========================================================================
             👑 1. SAAS OWNER / PLATFORM DEVELOPER DASHBOARD
             ========================================================================= -->
        <div x-show="user.role === 'saas_owner'" class="space-y-6" x-cloak>

            <!-- Four SaaS Summary cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-left">
                <div
                    class="kpi-card p-4 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Gudang
                            Tenant</span>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-blue-500/10 text-blue-500">
                            <i data-lucide="layout-grid" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="value text-2xl font-bold" x-text="stats.total_gudang || 0"></div>
                    <p class="text-[10px] text-slate-400 mt-1">Gudang aktif & suspended</p>
                </div>

                <div
                    class="kpi-card p-4 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tenant Trial/Sewa
                            Aktif</span>
                        <div
                            class="w-9 h-9 rounded-lg flex items-center justify-center bg-emerald-500/10 text-emerald-500">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="value text-2xl font-bold text-emerald-500" x-text="stats.active_gudang || 0"></div>
                    <p class="text-[10px] text-slate-400 mt-1">Gudang sewa masih berlaku</p>
                </div>

                <div
                    class="kpi-card p-4 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tenant Expired /
                            Suspend</span>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-red-500/10 text-red-500">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="value text-2xl font-bold text-red-500" x-text="stats.expired_gudang || 0"></div>
                    <p class="text-[10px] text-slate-400 mt-1">Gudang sewa habis / suspend</p>
                </div>

                <div
                    class="kpi-card p-4 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Belum Onboarding</span>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-amber-500/10 text-amber-500">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="value text-2xl font-bold text-amber-500" x-text="stats.pending_onboarding || 0"></div>
                    <p class="text-[10px] text-slate-400 mt-1">Gudang terdaftar, trial belum aktif</p>
                </div>
            </div>

            <!-- Volume Transaksi Platform row -->
            <div
                class="kpi-card p-4 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl flex items-center gap-4">
                <div
                    class="w-10 h-10 rounded-lg flex items-center justify-center bg-cyan-500/10 text-cyan-500 flex-shrink-0">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Volume Transaksi Seluruh
                        Platform</p>
                    <p class="text-xl font-bold text-cyan-500 mt-0.5" x-text="formatRupiah(stats.total_sales_all || 0)">
                    </p>
                    <p class="text-[10px] text-slate-400 mt-0.5"><span x-text="stats.total_sales_count || 0"></span>
                        nota penjualan lunas dari semua gudang</p>
                </div>
            </div>

            <!-- Two column developer control center -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Invite and Pre-Approval Panel -->
                <div
                    class="lg:col-span-2 card p-5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl space-y-4 text-left">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                        <i data-lucide="user-plus" class="w-4 h-4 text-blue-500"></i>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">Pra-Persetujuan (Pre-Approve) Akun
                            Bos Baru</h4>
                    </div>

                    <form @submit.prevent="runPreApprove()" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-2xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Nama
                                Lengkap Calon Bos *</label>
                            <input type="text" x-model="preApproveForm.name" class="form-input text-xs"
                                placeholder="Contoh: Bos Ronald" required>
                        </div>
                        <div>
                            <label
                                class="block text-2xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Email
                                Gmail Calon Bos *</label>
                            <input type="email" x-model="preApproveForm.email" class="form-input text-xs"
                                placeholder="Contoh: ronald@gmail.com" required>
                        </div>
                        <div
                            class="sm:col-span-2 flex flex-col sm:flex-row items-stretch sm:items-end justify-between gap-4">
                            <div class="flex-1">
                                <label
                                    class="block text-2xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Durasi
                                    Masa Trial Uji Coba *</label>
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

                <!-- Global developer settings & support contact number -->
                <div
                    class="card p-5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl space-y-4 text-left">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                        <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">WhatsApp Bantuan Platform</h4>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-2xs text-slate-500 dark:text-slate-400 leading-normal">Nomor WhatsApp
                            Developer (Gunakan format 628xxx tanpa tanda + atau 0 di depan)</label>
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

            <!-- Tenant Management Listing and bypass impersonate -->
            <div class="card overflow-hidden shadow-sm rounded-xl">
                <div
                    class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 text-left">
                    <h4 class="text-sm font-bold flex items-center gap-1.5" style="color: var(--text-primary)">
                        <i data-lucide="layout-grid" class="w-4.5 h-4.5 text-blue-500"></i>
                        Daftar Gudang Tenant WMS & Masa Sewa
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
                                        x-text="g.nama_gudang"></td>
                                    <td class="text-sm" x-text="g.kota || '-'"></td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold" style="color: var(--text-primary)"
                                                x-text="g.nama_bos || 'Belum di-assign'"></span>
                                            <span class="text-3xs text-slate-400" x-text="getBosEmail(g.id_bos)"></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <input type="date"
                                                :value="g.subscription_until ? g.subscription_until.substring(0,10) : ''"
                                                @change="updateGudangSubscription(g, $event.target.value)"
                                                class="form-input text-xs py-1 px-2 w-36">
                                            <span class="text-3xs text-slate-400 font-semibold"
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
                                                class="btn btn-secondary py-1 px-2.5 text-3xs flex items-center gap-1 font-bold bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400 border border-blue-200/50 dark:border-blue-900/20"
                                                :disabled="!g.id_bos">
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

            <!-- Platform Real-Time Activity Log -->
            <div class="card">
                <div class="flex items-center justify-between p-5 border-b muted-border"
                    style="border-color: var(--border-color)">
                    <h3 class="font-semibold flex items-center gap-2 text-slate-800 dark:text-white">
                        <i data-lucide="eye" class="w-4 h-4 text-blue-500"></i>
                        Log Aktivitas Terkini Platform (Real-Time)
                    </h3>
                </div>
                <div class="overflow-x-auto p-5">
                    <table class="table text-sm text-left">
                        <thead>
                            <tr style="border-bottom: 1.5px solid var(--border-color);">
                                <th style="padding: 10px 8px; color: var(--text-secondary);">Waktu</th>
                                <th style="padding: 10px 8px; color: var(--text-secondary);">User</th>
                                <th style="padding: 10px 8px; color: var(--text-secondary);">Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="!stats.latest_logs || stats.latest_logs.length === 0">
                                <tr>
                                    <td colspan="3" class="text-center py-4" style="color: var(--text-secondary)">Belum
                                        ada aktivitas tercatat</td>
                                </tr>
                            </template>
                            <template x-for="log in stats.latest_logs || []"
                                :key="log.id || log.record_id || log.timestamp">
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td class="whitespace-nowrap font-mono text-xs"
                                        style="color: var(--text-secondary); padding: 8px;"
                                        x-text="formatDateTime(log.timestamp)"></td>
                                    <td style="padding: 8px;"><span class="font-semibold"
                                            style="color: var(--text-primary)" x-text="log.nama_user"></span></td>
                                    <td style="color: var(--text-secondary); padding: 8px;">
                                        <span class="badge mr-2"
                                            :class="log.action === 'INSERT' ? 'badge-success' : (log.action === 'UPDATE' ? 'badge-warning' : 'badge-gray')"
                                            x-text="log.action"></span>
                                        <span x-text="formatActivity(log)"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- =========================================================================
             🐟 2. STANDARD TENANT WMS DASHBOARD
             ========================================================================= -->
        <div x-show="user.role !== 'saas_owner'" class="space-y-6" x-cloak>

            <!-- ========== ONBOARDING BELUM SELESAI ========== -->
            <div x-show="user.role === 'bos' && onboardingCompleted === false" class="space-y-4" x-cloak>
                <div
                    class="rounded-2xl border-2 border-dashed border-blue-200 dark:border-blue-900/50 bg-blue-50/60 dark:bg-blue-950/20 p-8 text-center">
                    <div
                        class="w-16 h-16 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="sparkles" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-lg font-bold" style="color: var(--text-primary)">Selamat Datang di Sistem Manajemen
                        Gudang!</h3>
                    <p class="text-sm mt-2 max-w-md mx-auto" style="color: var(--text-secondary)">
                        Gudang Anda belum dikonfigurasi. Selesaikan setup singkat untuk mengaktifkan masa trial gratis
                        dan mulai menggunakan sistem.
                    </p>
                    <div class="flex items-center justify-center gap-3 mt-6">
                        <button type="button" @click="$dispatch('open-onboarding-wizard')"
                            class="px-6 py-3 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-semibold text-sm flex items-center gap-2 transition-all shadow-lg shadow-blue-500/20">
                            <i data-lucide="rocket" class="w-4 h-4"></i>
                            Mulai Konfigurasi Gudang
                        </button>
                    </div>
                </div>

                <!-- Info cards mini -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="rounded-xl border p-4 text-center"
                        style="border-color: var(--border-color); background: var(--bg-card)">
                        <i data-lucide="building-2" class="w-5 h-5 mx-auto mb-2 text-blue-400"></i>
                        <p class="text-xs font-semibold" style="color: var(--text-primary)">Profil Gudang</p>
                        <p class="text-[10px] mt-0.5" style="color: var(--text-secondary)">Nama, lokasi & logo</p>
                    </div>
                    <div class="rounded-xl border p-4 text-center"
                        style="border-color: var(--border-color); background: var(--bg-card)">
                        <i data-lucide="fish" class="w-5 h-5 mx-auto mb-2 text-emerald-400"></i>
                        <p class="text-xs font-semibold" style="color: var(--text-primary)">Jenis Produk</p>
                        <p class="text-[10px] mt-0.5" style="color: var(--text-secondary)">Pilih ikan yang dikelola</p>
                    </div>
                    <div class="rounded-xl border p-4 text-center"
                        style="border-color: var(--border-color); background: var(--bg-card)">
                        <i data-lucide="table-2" class="w-5 h-5 mx-auto mb-2 text-purple-400"></i>
                        <p class="text-xs font-semibold" style="color: var(--text-primary)">Import Data Excel</p>
                        <p class="text-[10px] mt-0.5" style="color: var(--text-secondary)">Mapping kolom penjualan</p>
                    </div>
                </div>
            </div>

            <!-- ========== ONBOARDING BELUM SELESAI (ADMIN / CHECKER) ========== -->
            <div x-show="['admin', 'checker'].includes(user.role) && onboardingCompleted === false" class="space-y-4"
                x-cloak>
                <div
                    class="rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 p-8 text-center">
                    <div
                        class="w-16 h-16 rounded-2xl bg-slate-500/10 text-slate-500 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="clock" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-lg font-bold" style="color: var(--text-primary)">Setup Gudang Belum Selesai</h3>
                    <p class="text-sm mt-2 max-w-md mx-auto" style="color: var(--text-secondary)">
                        Pemilik gudang (Bos) belum menyelesaikan konfigurasi awal untuk gudang ini. Silakan hubungi
                        pemilik gudang Anda untuk menyelesaikan setup agar sistem dapat digunakan.
                    </p>
                </div>
            </div>

            <!-- ========== KPI DASHBOARD (hanya tampil jika onboarding selesai) ========== -->
            <template x-if="onboardingCompleted === true || !['bos', 'admin', 'checker'].includes(user.role)">
                <div>
                    <section class="dashboard-section card">
                        <div class="dashboard-section-header">
                            <p class="dashboard-section-title">Ringkasan Utama</p>
                            <p class="dashboard-section-subtitle">Delapan KPI inti untuk memantau operasi harian.</p>
                        </div>

                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="kpi-card" x-show="['bos','super_admin'].includes(user.role)">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <h4>Keuangan Masuk</h4>
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                                        style="background: rgba(16,185,129,0.1)">
                                        <i data-lucide="arrow-down-left" class="w-4 h-4"
                                            style="color: var(--color-success)"></i>
                                    </div>
                                </div>
                                <div class="value" style="color: var(--color-success)"
                                    x-text="formatRupiah(stats.keuangan_masuk)"></div>
                                <p class="text-xs mt-1" style="color: var(--text-secondary)">Total cash + terima piutang
                                </p>
                            </div>

                            <div class="kpi-card" x-show="['bos','super_admin'].includes(user.role)">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <h4>Keuangan Keluar</h4>
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                                        style="background: rgba(239,68,68,0.1)">
                                        <i data-lucide="arrow-up-right" class="w-4 h-4"
                                            style="color: var(--color-danger)"></i>
                                    </div>
                                </div>
                                <div class="value" style="color: var(--color-danger)"
                                    x-text="formatRupiah(stats.keuangan_keluar)"></div>
                                <p class="text-xs mt-1" style="color: var(--text-secondary)">Beli stok + operasional +
                                    bayar hutang</p>
                            </div>

                            <div class="kpi-card"
                                :style="stats.laba_rugi >= 0 ? 'border-color: rgba(16,185,129,0.35)' : 'border-color: rgba(239,68,68,0.35)'">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <h4>Laba / Rugi Bersih</h4>
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                                        :style="stats.laba_rugi >= 0 ? 'background: rgba(16,185,129,0.1)' : 'background: rgba(239,68,68,0.1)'">
                                        <i data-lucide="activity" class="w-4 h-4"
                                            :style="stats.laba_rugi >= 0 ? 'color: var(--color-success)' : 'color: var(--color-danger)'"></i>
                                    </div>
                                </div>
                                <div class="value"
                                    :style="stats.laba_rugi >= 0 ? 'color: var(--color-success)' : 'color: var(--color-danger)'"
                                    x-text="formatRupiah(stats.laba_rugi)"></div>
                                <p class="text-xs mt-1" style="color: var(--text-secondary)">Selisih bersih inflow -
                                    outflow</p>
                            </div>

                            <div class="kpi-card">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <h4>Total Stok</h4>
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                                        style="background: rgba(37,99,235,0.1)">
                                        <i data-lucide="package" class="w-4 h-4"
                                            style="color: var(--color-primary)"></i>
                                    </div>
                                </div>
                                <div class="value" x-text="formatKg(stats.total_stok_qty)"></div>
                                <p class="text-xs mt-1" style="color: var(--text-secondary)"><span
                                        x-text="stats.total_produk"></span> jenis produk</p>
                            </div>

                            <div class="kpi-card">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <h4>Kapasitas Cold Storage</h4>
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                                        style="background: rgba(6,182,212,0.1)">
                                        <i data-lucide="database" class="w-4 h-4" style="color: var(--color-info)"></i>
                                    </div>
                                </div>
                                <div class="value"
                                    x-text="formatPercent((stats.total_stok_qty / (stats.cold_storage_capacity || 10000)) * 100)">
                                </div>
                                <div class="w-full rounded-full h-2 mt-2" style="background: rgba(148,163,184,0.2)">
                                    <div class="h-2 rounded-full transition-all duration-500"
                                        :class="((stats.total_stok_qty / (stats.cold_storage_capacity || 10000)) * 100) > 80 ? 'bg-red-500' : 'bg-cyan-500'"
                                        :style="'width: ' + Math.min(100, ((stats.total_stok_qty / (stats.cold_storage_capacity || 10000)) * 100)) + '%' ">
                                    </div>
                                </div>
                                <p class="text-xs mt-1.5" style="color: var(--text-secondary)"><span
                                        x-text="formatKg(stats.total_stok_qty)"></span> / <span
                                        x-text="formatKg(stats.cold_storage_capacity || 10000)"></span></p>
                            </div>

                            <div class="kpi-card" x-show="user.role !== 'checker'">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <h4>Penjualan Hari Ini</h4>
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                                        style="background: rgba(16,185,129,0.1)">
                                        <i data-lucide="receipt" class="w-4 h-4"
                                            style="color: var(--color-success)"></i>
                                    </div>
                                </div>
                                <div class="value" x-text="formatRupiah(stats.penjualan_hari_ini)"></div>
                                <p class="text-xs mt-1" style="color: var(--text-secondary)"><span
                                        x-text="stats.nota_hari_ini"></span> nota</p>
                            </div>

                            <div class="kpi-card" x-show="['bos','admin','super_admin'].includes(user.role)">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <h4>Total Piutang</h4>
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                                        style="background: rgba(245,158,11,0.1)">
                                        <i data-lucide="trending-up" class="w-4 h-4"
                                            style="color: var(--color-warning)"></i>
                                    </div>
                                </div>
                                <div class="value" x-text="formatRupiah(stats.total_piutang)"></div>
                                <p class="text-xs mt-1" style="color: var(--color-warning)"
                                    x-show="stats.overdue_count > 0"><span x-text="stats.overdue_count"></span> jatuh
                                    tempo!</p>
                            </div>

                            <div class="kpi-card">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <h4>Stok Menipis</h4>
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                                        style="background: rgba(239,68,68,0.1)">
                                        <i data-lucide="alert-triangle" class="w-4 h-4"
                                            style="color: var(--color-danger)"></i>
                                    </div>
                                </div>
                                <div class="value" style="color: var(--color-danger)" x-text="stats.low_stock_count">
                                </div>
                                <p class="text-xs mt-1" style="color: var(--text-secondary)">produk di bawah minimum</p>
                            </div>

                            <!-- Widget Draft Pending dari Checker — hanya untuk admin & super_admin -->
                            <?php if (in_array(getWebUserRole(), ['admin', 'super_admin'], true)): ?>
                                <a href="/peace_seafood/penjualan?filter=draft" class="kpi-card block no-underline"
                                    x-show="['admin','super_admin'].includes(user.role)" x-cloak
                                    style="text-decoration:none;cursor:pointer;position:relative;overflow:hidden"
                                    :style="stats.draft_pending_count > 0 ? 'border-color:rgba(245,158,11,0.5);background:rgba(245,158,11,0.04)' : ''">
                                    <!-- Animated pulse ring saat ada draft -->
                                    <span x-show="stats.draft_pending_count > 0"
                                        class="absolute top-2 right-2 flex h-3 w-3">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                                            style="background:var(--color-warning)"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3"
                                            style="background:var(--color-warning)"></span>
                                    </span>
                                    <div class="flex items-center justify-between gap-3 mb-3">
                                        <h4>Draft dari Checker</h4>
                                        <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                                            :style="stats.draft_pending_count > 0 ? 'background:rgba(245,158,11,0.15)' : 'background:rgba(100,116,139,0.1)'">
                                            <i data-lucide="send" class="w-4 h-4"
                                                :style="stats.draft_pending_count > 0 ? 'color:var(--color-warning)' : 'color:var(--text-secondary)'"></i>
                                        </div>
                                    </div>
                                    <div class="value"
                                        :style="stats.draft_pending_count > 0 ? 'color:var(--color-warning)' : 'color:var(--text-secondary)'"
                                        x-text="stats.draft_pending_count"></div>
                                    <p class="text-xs mt-1" style="color:var(--text-secondary)">
                                        <span x-show="stats.draft_pending_count > 0"
                                            style="color:var(--color-warning);font-weight:600">
                                            Perlu difinalisasi →
                                        </span>
                                        <span x-show="stats.draft_pending_count === 0">nota menunggu proses</span>
                                    </p>
                                </a>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="dashboard-section card">
                        <div class="dashboard-section-header">
                            <p class="dashboard-section-title">Keuangan</p>
                            <p class="dashboard-section-subtitle">Komposisi arus kas dan ringkasan laba rugi.</p>
                        </div>

                        <div class="flex flex-col lg:flex-row gap-6">
                            <div class="dashboard-chart-card p-4 lg:w-3/5 w-full">
                                <div class="dashboard-section-header">
                                    <p class="dashboard-section-title">Laba/Rugi Bersih</p>
                                    <p class="dashboard-section-subtitle">Waterfall sederhana untuk membaca perubahan
                                        kas bersih.</p>
                                </div>
                                <div class="dashboard-chart-wrap section-chart-260" style="height: 260px;">
                                    <canvas id="financeWaterfallChart" class="dashboard-canvas"></canvas>
                                </div>
                            </div>

                            <div class="dashboard-chart-card p-4 lg:w-2/5 w-full">
                                <div class="dashboard-section-header">
                                    <p class="dashboard-section-title">Komposisi Kas</p>
                                    <p class="dashboard-section-subtitle">Masuk versus keluar pada periode berjalan.</p>
                                </div>
                                <div class="dashboard-chart-wrap section-chart-260" style="height: 260px;">
                                    <canvas id="cashCompositionChart" class="dashboard-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="dashboard-section card">
                        <div class="dashboard-section-header">
                            <p class="dashboard-section-title">Penjualan</p>
                            <p class="dashboard-section-subtitle">Tren 7 hari terakhir dan produk terlaris berdasarkan
                                volume.</p>
                        </div>

                        <div class="flex flex-col lg:flex-row gap-6">
                            <div class="dashboard-chart-card p-4 lg:w-3/5 w-full">
                                <div class="dashboard-section-header">
                                    <p class="dashboard-section-title">Tren Penjualan 7 Hari</p>
                                    <p class="dashboard-section-subtitle">Area chart dengan gradient fill.</p>
                                </div>
                                <div class="dashboard-chart-wrap section-chart-260" style="height: 260px;">
                                    <canvas id="salesTrendChart" class="dashboard-canvas"></canvas>
                                </div>
                            </div>

                            <div class="dashboard-chart-card p-4 lg:w-2/5 w-full">
                                <div class="dashboard-section-header">
                                    <p class="dashboard-section-title">Top 5 Produk Terlaris</p>
                                    <p class="dashboard-section-subtitle">Volume kilogram dari produk yang paling sering
                                        terjual.</p>
                                </div>
                                <div class="dashboard-chart-wrap section-chart-260" style="height: 260px;">
                                    <canvas id="topProductsChart" class="dashboard-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="dashboard-section card">
                        <div class="dashboard-section-header">
                            <p class="dashboard-section-title">Stok</p>
                            <p class="dashboard-section-subtitle">Kapasitas, komposisi kategori, dan riwayat stok masuk
                                7 hari terakhir.</p>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="dashboard-chart-card p-4">
                                <div class="dashboard-section-header">
                                    <p class="dashboard-section-title">Kapasitas Cold Storage</p>
                                    <p class="dashboard-section-subtitle">Gauge setengah lingkaran untuk memantau
                                        pemakaian ruang.</p>
                                </div>
                                <div class="dashboard-chart-wrap section-chart-200" style="height: 200px;">
                                    <canvas id="coldStorageGaugeChart" class="dashboard-canvas"></canvas>
                                </div>
                            </div>

                            <div class="dashboard-chart-card p-4">
                                <div class="dashboard-section-header">
                                    <p class="dashboard-section-title">Stok per Kategori</p>
                                    <p class="dashboard-section-subtitle">Komposisi kategori utama stok ikan dan
                                        seafood.</p>
                                </div>
                                <div class="dashboard-chart-wrap section-chart-200" style="height: 200px;">
                                    <canvas id="stockCategoryChart" class="dashboard-canvas"></canvas>
                                </div>
                            </div>

                            <div class="dashboard-chart-card p-4">
                                <div class="dashboard-section-header">
                                    <p class="dashboard-section-title">Riwayat Stok Masuk 7 Hari</p>
                                    <p class="dashboard-section-subtitle">Tren inbound stok dalam satuan kilogram.</p>
                                </div>
                                <div class="dashboard-chart-wrap section-chart-200" style="height: 200px;">
                                    <canvas id="incomingStockChart" class="dashboard-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-col gap-6">
                        <div class="card" x-show="['bos','admin','super_admin'].includes(user.role)">
                            <div class="flex items-center justify-between p-5 border-b muted-border"
                                style="border-color: var(--border-color)">
                                <h3 class="font-semibold" style="color: var(--text-primary)">Nota Terbaru</h3>
                                <a href="/peace_seafood/penjualan" class="text-xs"
                                    style="color: var(--color-primary)">Lihat Semua</a>
                            </div>
                            <div class="p-5">
                                <template x-if="recentNota.length === 0">
                                    <p class="text-sm text-center py-4" style="color: var(--text-secondary)">Belum ada
                                        nota</p>
                                </template>
                                <template x-for="nota in recentNota" :key="nota.id">
                                    <div class="flex items-center justify-between py-2 border-b muted-border"
                                        style="border-color: var(--border-color)">
                                        <div>
                                            <p class="text-sm font-medium" style="color: var(--text-primary)"
                                                x-text="nota.no_nota"></p>
                                            <p class="text-xs" style="color: var(--text-secondary)"
                                                x-text="nota.nama_pembeli || 'Umum'"></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-semibold" style="color: var(--color-success)"
                                                x-text="formatRupiah(nota.total)"></p>
                                            <span class="badge"
                                                :class="nota.status === 'final' ? 'badge-success' : nota.status === 'draft' ? 'badge-warning' : 'badge-gray'"
                                                x-text="(nota.status || '-').toUpperCase()"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="card" x-show="['bos','super_admin','admin'].includes(user.role)">
                                <div class="flex items-center justify-between p-5 border-b muted-border"
                                    style="border-color: var(--border-color)">
                                    <h3 class="font-semibold" style="color: var(--text-primary)">Produk Terlaris</h3>
                                    <span class="text-xs" style="color: var(--text-secondary)">Top 5 (Qty)</span>
                                </div>
                                <div class="p-5">
                                    <template x-if="!stats.top_products || stats.top_products.length === 0">
                                        <p class="text-sm text-center py-4" style="color: var(--text-secondary)">Belum
                                            ada data penjualan</p>
                                    </template>
                                    <template x-for="(prod, index) in stats.top_products || []" :key="index">
                                        <div class="mb-4">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-sm font-medium" style="color: var(--text-primary)">
                                                    <span class="font-bold mr-1" style="color: var(--color-primary)"
                                                        x-text="(index + 1) + '.'"></span>
                                                    <span x-text="prod.nama"></span>
                                                </span>
                                                <span class="text-xs font-semibold" style="color: var(--text-secondary)"
                                                    x-text="formatKg(prod.total_qty)"></span>
                                            </div>
                                            <div class="w-full rounded-full h-1.5"
                                                style="background: rgba(148,163,184,0.2)">
                                                <div class="h-1.5 rounded-full" style="background: var(--color-primary)"
                                                    :style="'width: ' + Math.min(100, Math.max(10, (parseFloat(prod.total_qty) / (parseFloat(stats.top_products[0]?.total_qty) || 1)) * 100)) + '%' ">
                                                </div>
                                            </div>
                                            <div class="text-right mt-0.5">
                                                <span class="text-2xs font-semibold"
                                                    style="color: var(--color-success); font-size: 0.7rem;"
                                                    x-text="formatRupiah(prod.total_nominal)"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="card">
                                <div class="flex items-center justify-between p-5 border-b muted-border"
                                    style="border-color: var(--border-color)">
                                    <h3 class="font-semibold" style="color: var(--text-primary)">Stok Menipis</h3>
                                    <a href="/peace_seafood/stok" class="text-xs"
                                        style="color: var(--color-primary)">Lihat Stok</a>
                                </div>
                                <div class="p-5">
                                    <template x-if="lowStockItems.length === 0">
                                        <p class="text-sm text-center py-4" style="color: var(--color-success)">Semua
                                            stok aman</p>
                                    </template>
                                    <template x-for="item in lowStockItems" :key="item.id">
                                        <div class="flex items-center justify-between py-2 border-b muted-border"
                                            style="border-color: var(--border-color)">
                                            <div>
                                                <p class="text-sm font-medium" style="color: var(--text-primary)"
                                                    x-text="item.nama"></p>
                                                <p class="text-xs" style="color: var(--text-secondary)"
                                                    x-text="item.nama_jenis"></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-bold" style="color: var(--color-danger)"
                                                    x-text="item.stok_qty + ' ' + (item.satuan || 'kg')"></p>
                                                <p class="text-xs" style="color: var(--text-secondary)">min: <span
                                                        x-text="item.stok_minimum"></span></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="card" x-show="['bos','super_admin','admin'].includes(user.role)">
                                <div class="flex items-center justify-between p-5 border-b muted-border"
                                    style="border-color: var(--border-color)">
                                    <h3 class="font-semibold flex items-center gap-2"
                                        style="color: var(--text-primary)">
                                        <i data-lucide="eye" class="w-4 h-4" style="color: var(--color-primary)"></i>
                                        Log Aktivitas Terkini
                                    </h3>
                                    <span class="badge badge-gray">Real-time</span>
                                </div>
                                <div class="overflow-x-auto p-5">
                                    <table class="table text-sm">
                                        <thead>
                                            <tr style="border-bottom: 1.5px solid var(--border-color);">
                                                <th
                                                    style="padding: 10px 8px; text-align: left; color: var(--text-secondary);">
                                                    Waktu</th>
                                                <th
                                                    style="padding: 10px 8px; text-align: left; color: var(--text-secondary);">
                                                    User</th>
                                                <th
                                                    style="padding: 10px 8px; text-align: left; color: var(--text-secondary);">
                                                    Aktivitas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-if="!stats.latest_logs || stats.latest_logs.length === 0">
                                                <tr>
                                                    <td colspan="3" class="text-center py-4"
                                                        style="color: var(--text-secondary)">Belum ada aktivitas
                                                        tercatat</td>
                                                </tr>
                                            </template>
                                            <template x-for="log in stats.latest_logs || []"
                                                :key="log.id || log.record_id || log.timestamp">
                                                <tr style="border-bottom: 1px solid var(--border-color);">
                                                    <td class="whitespace-nowrap font-mono text-xs"
                                                        style="color: var(--text-secondary); padding: 8px;"
                                                        x-text="formatDateTime(log.timestamp)"></td>
                                                    <td style="padding: 8px;"><span class="font-semibold"
                                                            style="color: var(--text-primary)"
                                                            x-text="log.nama_user"></span></td>
                                                    <td style="color: var(--text-secondary); padding: 8px;">
                                                        <span class="badge mr-2"
                                                            :class="log.action === 'INSERT' ? 'badge-success' : (log.action === 'UPDATE' ? 'badge-warning' : 'badge-gray')"
                                                            x-text="log.action"></span>
                                                        <span x-text="formatActivity(log)"></span>
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
            </template>
        </div>
    </div>
</div>

<?php $scripts = '<script src="/peace_seafood/inline-assets/js/pages/dashboard.js"></script>'; ?>