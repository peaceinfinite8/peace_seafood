<?php

/**
 * Dashboard Page
 */
?>

<style>
    .dashboard-shell {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .dashboard-section {
        padding: 20px;
    }

    .dashboard-section-header {
        margin-bottom: 16px;
    }

    .dashboard-section-title {
        font-size: 16px;
        font-weight: 500;
        color: var(--text-primary);
        margin: 0;
    }

    .dashboard-section-subtitle {
        font-size: 12px;
        color: var(--text-secondary);
        margin: 4px 0 0;
    }

    .dashboard-chart-card {
        background: var(--bg-light);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
    }

    .dashboard-chart-wrap {
        position: relative;
        width: 100%;
    }

    .dashboard-canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .kpi-card {
        background: var(--bg-light);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        padding: 1rem;
    }

    .kpi-card h4 {
        color: var(--text-secondary);
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .kpi-card .value {
        color: var(--text-primary);
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .muted-border {
        border-color: var(--border-color);
    }

    @media (max-width: 768px) {
        .dashboard-section {
            padding: 16px;
        }

        .section-chart-260 {
            height: 200px !important;
        }

        .section-chart-200 {
            height: 180px !important;
        }
    }
</style>

<div x-data="dashboardPage()" x-init="init()" class="dashboard-shell">
    <div class="mb-2">
        <h2 class="text-xl font-bold" style="color: var(--text-primary)">
            Selamat datang, <span x-text="user.name || 'User'"></span> | <span class="uppercase font-semibold text-sm" style="color: var(--color-primary)" x-text="(user.role || '').toUpperCase()"></span>
        </h2>
        <p class="text-sm mt-1" style="color: var(--text-secondary)">
            <span x-text="today"></span>
            <span x-show="user.nama_gudang"> - <span class="font-medium" x-text="user.nama_gudang"></span></span>
        </p>
    </div>

    <div x-show="loading" class="flex items-center justify-center py-20">
        <div class="animate-spin w-8 h-8 rounded-full border-4 border-blue-200" style="border-top-color: var(--color-primary)"></div>
    </div>

    <div x-show="!loading" x-cloak>
        <!-- =========================================================================
             👑 1. SAAS OWNER / PLATFORM DEVELOPER DASHBOARD
             ========================================================================= -->
        <div x-show="user.role === 'saas_owner'" class="space-y-6" x-cloak>
            
            <!-- Four SaaS Summary cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-left">
                <div class="kpi-card p-4 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Gudang Tenant</span>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-blue-500/10 text-blue-500">
                            <i data-lucide="layout-grid" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="value text-2xl font-bold" x-text="stats.total_gudang || 0"></div>
                    <p class="text-[10px] text-slate-400 mt-1">Gudang aktif & suspended</p>
                </div>

                <div class="kpi-card p-4 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tenant Trial/Sewa Aktif</span>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-emerald-500/10 text-emerald-500">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="value text-2xl font-bold text-emerald-500" x-text="stats.active_gudang || 0"></div>
                    <p class="text-[10px] text-slate-400 mt-1">Gudang sewa masih berlaku</p>
                </div>

                <div class="kpi-card p-4 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tenant Expired / Suspend</span>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-red-500/10 text-red-500">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="value text-2xl font-bold text-red-500" x-text="stats.expired_gudang || 0"></div>
                    <p class="text-[10px] text-slate-400 mt-1">Gudang sewa habis / suspend</p>
                </div>

                <div class="kpi-card p-4 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl">
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
            <div class="kpi-card p-4 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-cyan-500/10 text-cyan-500 flex-shrink-0">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Volume Transaksi Seluruh Platform</p>
                    <p class="text-xl font-bold text-cyan-500 mt-0.5" x-text="formatRupiah(stats.total_sales_all || 0)"></p>
                    <p class="text-[10px] text-slate-400 mt-0.5"><span x-text="stats.total_sales_count || 0"></span> nota penjualan lunas dari semua gudang</p>
                </div>
            </div>

            <!-- Two column developer control center -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Invite and Pre-Approval Panel -->
                <div class="lg:col-span-2 card p-5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl space-y-4 text-left">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                        <i data-lucide="user-plus" class="w-4 h-4 text-blue-500"></i>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">Pra-Persetujuan (Pre-Approve) Akun Bos Baru</h4>
                    </div>
                    
                    <form @submit.prevent="runPreApprove()" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-2xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Nama Lengkap Calon Bos *</label>
                            <input type="text" x-model="preApproveForm.name" class="form-input text-xs" placeholder="Contoh: Bos Ronald" required>
                        </div>
                        <div>
                            <label class="block text-2xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Email Gmail Calon Bos *</label>
                            <input type="email" x-model="preApproveForm.email" class="form-input text-xs" placeholder="Contoh: ronald@gmail.com" required>
                        </div>
                        <div class="sm:col-span-2 flex flex-col sm:flex-row items-stretch sm:items-end justify-between gap-4">
                            <div class="flex-1">
                                <label class="block text-2xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Durasi Masa Trial Uji Coba *</label>
                                <select x-model="preApproveForm.trial_days" class="form-input text-xs">
                                    <option value="7">7 Hari (1 Uji Coba)</option>
                                    <option value="14">14 Hari (2 Uji Coba - Default)</option>
                                    <option value="30">30 Hari (1 Bulan)</option>
                                    <option value="60">60 Hari (2 Bulan)</option>
                                    <option value="90">90 Hari (3 Bulan)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary py-2 px-5 text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-blue-500/10 h-[38px]" :disabled="preApproving">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span x-text="preApproving ? 'Memproses...' : 'Setujui Email'"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Global developer settings & support contact number -->
                <div class="card p-5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl space-y-4 text-left">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                        <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">WhatsApp Bantuan Platform</h4>
                    </div>
                    
                    <div class="space-y-3">
                        <label class="block text-2xs text-slate-500 dark:text-slate-400 leading-normal">Nomor WhatsApp Developer (Gunakan format 628xxx tanpa tanda + atau 0 di depan)</label>
                        <div class="flex gap-2">
                            <input type="text" x-model="devWhatsappNumber" class="form-input text-xs flex-1" placeholder="Contoh: 628123456789">
                            <button type="button" @click="saveDevWhatsapp()" class="btn btn-primary p-2 flex items-center justify-center" :disabled="savingWa">
                                <i data-lucide="save" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tenant Management Listing and bypass impersonate -->
            <div class="card overflow-hidden shadow-sm rounded-xl">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 text-left">
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
                                    <td class="font-bold text-sm" style="color: var(--text-primary)" x-text="g.nama_gudang"></td>
                                    <td class="text-sm" x-text="g.kota || '-'"></td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold" style="color: var(--text-primary)" x-text="g.nama_bos || 'Belum di-assign'"></span>
                                            <span class="text-3xs text-slate-400" x-text="getBosEmail(g.id_bos)"></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <input type="date" 
                                                   :value="g.subscription_until ? g.subscription_until.substring(0,10) : ''"
                                                   @change="updateGudangSubscription(g, $event.target.value)"
                                                   class="form-input text-xs py-1 px-2 w-36">
                                            <span class="text-3xs text-slate-400 font-semibold" x-text="getRemainingDaysText(g.subscription_until)"></span>
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
                                            <button type="button" 
                                                    @click="impersonateUser(g.id_bos)"
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
                <div class="flex items-center justify-between p-5 border-b muted-border" style="border-color: var(--border-color)">
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
                                    <td colspan="3" class="text-center py-4" style="color: var(--text-secondary)">Belum ada aktivitas tercatat</td>
                                </tr>
                            </template>
                            <template x-for="log in stats.latest_logs || []" :key="log.id || log.record_id || log.timestamp">
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td class="whitespace-nowrap font-mono text-xs" style="color: var(--text-secondary); padding: 8px;" x-text="formatDateTime(log.timestamp)"></td>
                                    <td style="padding: 8px;"><span class="font-semibold" style="color: var(--text-primary)" x-text="log.nama_user"></span></td>
                                    <td style="color: var(--text-secondary); padding: 8px;">
                                        <span class="badge mr-2" :class="log.action === 'INSERT' ? 'badge-success' : (log.action === 'UPDATE' ? 'badge-warning' : 'badge-gray')" x-text="log.action"></span>
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
                <div class="rounded-2xl border-2 border-dashed border-blue-200 dark:border-blue-900/50 bg-blue-50/60 dark:bg-blue-950/20 p-8 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="sparkles" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-lg font-bold" style="color: var(--text-primary)">Selamat Datang di Sistem Manajemen Gudang!</h3>
                    <p class="text-sm mt-2 max-w-md mx-auto" style="color: var(--text-secondary)">
                        Gudang Anda belum dikonfigurasi. Selesaikan setup singkat untuk mengaktifkan masa trial gratis dan mulai menggunakan sistem.
                    </p>
                    <div class="flex items-center justify-center gap-3 mt-6">
                        <button type="button"
                                @click="$dispatch('open-onboarding-wizard')"
                                class="px-6 py-3 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-semibold text-sm flex items-center gap-2 transition-all shadow-lg shadow-blue-500/20">
                            <i data-lucide="rocket" class="w-4 h-4"></i>
                            Mulai Konfigurasi Gudang
                        </button>
                    </div>
                </div>

                <!-- Info cards mini -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="rounded-xl border p-4 text-center" style="border-color: var(--border-color); background: var(--bg-card)">
                        <i data-lucide="building-2" class="w-5 h-5 mx-auto mb-2 text-blue-400"></i>
                        <p class="text-xs font-semibold" style="color: var(--text-primary)">Profil Gudang</p>
                        <p class="text-[10px] mt-0.5" style="color: var(--text-secondary)">Nama, lokasi & logo</p>
                    </div>
                    <div class="rounded-xl border p-4 text-center" style="border-color: var(--border-color); background: var(--bg-card)">
                        <i data-lucide="fish" class="w-5 h-5 mx-auto mb-2 text-emerald-400"></i>
                        <p class="text-xs font-semibold" style="color: var(--text-primary)">Jenis Produk</p>
                        <p class="text-[10px] mt-0.5" style="color: var(--text-secondary)">Pilih ikan yang dikelola</p>
                    </div>
                    <div class="rounded-xl border p-4 text-center" style="border-color: var(--border-color); background: var(--bg-card)">
                        <i data-lucide="table-2" class="w-5 h-5 mx-auto mb-2 text-purple-400"></i>
                        <p class="text-xs font-semibold" style="color: var(--text-primary)">Import Data Excel</p>
                        <p class="text-[10px] mt-0.5" style="color: var(--text-secondary)">Mapping kolom penjualan</p>
                    </div>
                </div>
            </div>

            <!-- ========== ONBOARDING BELUM SELESAI (ADMIN / CHECKER) ========== -->
            <div x-show="['admin', 'checker'].includes(user.role) && onboardingCompleted === false" class="space-y-4" x-cloak>
                <div class="rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 p-8 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-500/10 text-slate-500 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="clock" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-lg font-bold" style="color: var(--text-primary)">Setup Gudang Belum Selesai</h3>
                    <p class="text-sm mt-2 max-w-md mx-auto" style="color: var(--text-secondary)">
                        Pemilik gudang (Bos) belum menyelesaikan konfigurasi awal untuk gudang ini. Silakan hubungi pemilik gudang Anda untuk menyelesaikan setup agar sistem dapat digunakan.
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
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(16,185,129,0.1)">
                                <i data-lucide="arrow-down-left" class="w-4 h-4" style="color: var(--color-success)"></i>
                            </div>
                        </div>
                        <div class="value" style="color: var(--color-success)" x-text="formatRupiah(stats.keuangan_masuk)"></div>
                        <p class="text-xs mt-1" style="color: var(--text-secondary)">Total cash + terima piutang</p>
                    </div>

                    <div class="kpi-card" x-show="['bos','super_admin'].includes(user.role)">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <h4>Keuangan Keluar</h4>
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(239,68,68,0.1)">
                                <i data-lucide="arrow-up-right" class="w-4 h-4" style="color: var(--color-danger)"></i>
                            </div>
                        </div>
                        <div class="value" style="color: var(--color-danger)" x-text="formatRupiah(stats.keuangan_keluar)"></div>
                        <p class="text-xs mt-1" style="color: var(--text-secondary)">Beli stok + operasional + bayar hutang</p>
                    </div>

                    <div class="kpi-card" :style="stats.laba_rugi >= 0 ? 'border-color: rgba(16,185,129,0.35)' : 'border-color: rgba(239,68,68,0.35)'">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <h4>Laba / Rugi Bersih</h4>
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" :style="stats.laba_rugi >= 0 ? 'background: rgba(16,185,129,0.1)' : 'background: rgba(239,68,68,0.1)'">
                                <i data-lucide="activity" class="w-4 h-4" :style="stats.laba_rugi >= 0 ? 'color: var(--color-success)' : 'color: var(--color-danger)'"></i>
                            </div>
                        </div>
                        <div class="value" :style="stats.laba_rugi >= 0 ? 'color: var(--color-success)' : 'color: var(--color-danger)'" x-text="formatRupiah(stats.laba_rugi)"></div>
                        <p class="text-xs mt-1" style="color: var(--text-secondary)">Selisih bersih inflow - outflow</p>
                    </div>

                    <div class="kpi-card">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <h4>Total Stok</h4>
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(37,99,235,0.1)">
                                <i data-lucide="package" class="w-4 h-4" style="color: var(--color-primary)"></i>
                            </div>
                        </div>
                        <div class="value" x-text="formatKg(stats.total_stok_qty)"></div>
                        <p class="text-xs mt-1" style="color: var(--text-secondary)"><span x-text="stats.total_produk"></span> jenis produk</p>
                    </div>

                    <div class="kpi-card">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <h4>Kapasitas Cold Storage</h4>
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(6,182,212,0.1)">
                                <i data-lucide="database" class="w-4 h-4" style="color: var(--color-info)"></i>
                            </div>
                        </div>
                        <div class="value" x-text="formatPercent((stats.total_stok_qty / (stats.cold_storage_capacity || 10000)) * 100)"></div>
                        <div class="w-full rounded-full h-2 mt-2" style="background: rgba(148,163,184,0.2)">
                            <div class="h-2 rounded-full transition-all duration-500" :class="((stats.total_stok_qty / (stats.cold_storage_capacity || 10000)) * 100) > 80 ? 'bg-red-500' : 'bg-cyan-500'" :style="'width: ' + Math.min(100, ((stats.total_stok_qty / (stats.cold_storage_capacity || 10000)) * 100)) + '%' "></div>
                        </div>
                        <p class="text-xs mt-1.5" style="color: var(--text-secondary)"><span x-text="formatKg(stats.total_stok_qty)"></span> / <span x-text="formatKg(stats.cold_storage_capacity || 10000)"></span></p>
                    </div>

                    <div class="kpi-card" x-show="user.role !== 'checker'">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <h4>Penjualan Hari Ini</h4>
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(16,185,129,0.1)">
                                <i data-lucide="receipt" class="w-4 h-4" style="color: var(--color-success)"></i>
                            </div>
                        </div>
                        <div class="value" x-text="formatRupiah(stats.penjualan_hari_ini)"></div>
                        <p class="text-xs mt-1" style="color: var(--text-secondary)"><span x-text="stats.nota_hari_ini"></span> nota</p>
                    </div>

                    <div class="kpi-card" x-show="['bos','admin','super_admin'].includes(user.role)">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <h4>Total Piutang</h4>
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(245,158,11,0.1)">
                                <i data-lucide="trending-up" class="w-4 h-4" style="color: var(--color-warning)"></i>
                            </div>
                        </div>
                        <div class="value" x-text="formatRupiah(stats.total_piutang)"></div>
                        <p class="text-xs mt-1" style="color: var(--color-warning)" x-show="stats.overdue_count > 0"><span x-text="stats.overdue_count"></span> jatuh tempo!</p>
                    </div>

                    <div class="kpi-card">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <h4>Stok Menipis</h4>
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(239,68,68,0.1)">
                                <i data-lucide="alert-triangle" class="w-4 h-4" style="color: var(--color-danger)"></i>
                            </div>
                        </div>
                        <div class="value" style="color: var(--color-danger)" x-text="stats.low_stock_count"></div>
                        <p class="text-xs mt-1" style="color: var(--text-secondary)">produk di bawah minimum</p>
                    </div>

                    <!-- Widget Draft Pending dari Checker — hanya untuk admin & super_admin -->
                    <?php if (in_array(getWebUserRole(), ['admin', 'super_admin'], true)): ?>
                    <a href="/peace_seafood/penjualan?filter=draft"
                       class="kpi-card block no-underline"
                       x-show="['admin','super_admin'].includes(user.role)"
                       x-cloak
                       style="text-decoration:none;cursor:pointer;position:relative;overflow:hidden"
                       :style="stats.draft_pending_count > 0 ? 'border-color:rgba(245,158,11,0.5);background:rgba(245,158,11,0.04)' : ''">
                        <!-- Animated pulse ring saat ada draft -->
                        <span x-show="stats.draft_pending_count > 0"
                              class="absolute top-2 right-2 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
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
                            <span x-show="stats.draft_pending_count > 0" style="color:var(--color-warning);font-weight:600">
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
                            <p class="dashboard-section-subtitle">Waterfall sederhana untuk membaca perubahan kas bersih.</p>
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
                    <p class="dashboard-section-subtitle">Tren 7 hari terakhir dan produk terlaris berdasarkan volume.</p>
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
                            <p class="dashboard-section-subtitle">Volume kilogram dari produk yang paling sering terjual.</p>
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
                    <p class="dashboard-section-subtitle">Kapasitas, komposisi kategori, dan riwayat stok masuk 7 hari terakhir.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="dashboard-chart-card p-4">
                        <div class="dashboard-section-header">
                            <p class="dashboard-section-title">Kapasitas Cold Storage</p>
                            <p class="dashboard-section-subtitle">Gauge setengah lingkaran untuk memantau pemakaian ruang.</p>
                        </div>
                        <div class="dashboard-chart-wrap section-chart-200" style="height: 200px;">
                            <canvas id="coldStorageGaugeChart" class="dashboard-canvas"></canvas>
                        </div>
                    </div>

                    <div class="dashboard-chart-card p-4">
                        <div class="dashboard-section-header">
                            <p class="dashboard-section-title">Stok per Kategori</p>
                            <p class="dashboard-section-subtitle">Komposisi kategori utama stok ikan dan seafood.</p>
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
                    <div class="flex items-center justify-between p-5 border-b muted-border" style="border-color: var(--border-color)">
                        <h3 class="font-semibold" style="color: var(--text-primary)">Nota Terbaru</h3>
                        <a href="/peace_seafood/penjualan" class="text-xs" style="color: var(--color-primary)">Lihat Semua</a>
                    </div>
                    <div class="p-5">
                        <template x-if="recentNota.length === 0">
                            <p class="text-sm text-center py-4" style="color: var(--text-secondary)">Belum ada nota</p>
                        </template>
                        <template x-for="nota in recentNota" :key="nota.id">
                            <div class="flex items-center justify-between py-2 border-b muted-border" style="border-color: var(--border-color)">
                                <div>
                                    <p class="text-sm font-medium" style="color: var(--text-primary)" x-text="nota.no_nota"></p>
                                    <p class="text-xs" style="color: var(--text-secondary)" x-text="nota.nama_pembeli || 'Umum'"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold" style="color: var(--color-success)" x-text="formatRupiah(nota.total)"></p>
                                    <span class="badge" :class="nota.status === 'final' ? 'badge-success' : nota.status === 'draft' ? 'badge-warning' : 'badge-gray'" x-text="(nota.status || '-').toUpperCase()"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="card" x-show="['bos','super_admin','admin'].includes(user.role)">
                        <div class="flex items-center justify-between p-5 border-b muted-border" style="border-color: var(--border-color)">
                            <h3 class="font-semibold" style="color: var(--text-primary)">Produk Terlaris</h3>
                            <span class="text-xs" style="color: var(--text-secondary)">Top 5 (Qty)</span>
                        </div>
                        <div class="p-5">
                            <template x-if="!stats.top_products || stats.top_products.length === 0">
                                <p class="text-sm text-center py-4" style="color: var(--text-secondary)">Belum ada data penjualan</p>
                            </template>
                            <template x-for="(prod, index) in stats.top_products || []" :key="index">
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium" style="color: var(--text-primary)">
                                            <span class="font-bold mr-1" style="color: var(--color-primary)" x-text="(index + 1) + '.'"></span>
                                            <span x-text="prod.nama"></span>
                                        </span>
                                        <span class="text-xs font-semibold" style="color: var(--text-secondary)" x-text="formatKg(prod.total_qty)"></span>
                                    </div>
                                    <div class="w-full rounded-full h-1.5" style="background: rgba(148,163,184,0.2)">
                                        <div class="h-1.5 rounded-full" style="background: var(--color-primary)" :style="'width: ' + Math.min(100, Math.max(10, (parseFloat(prod.total_qty) / (parseFloat(stats.top_products[0]?.total_qty) || 1)) * 100)) + '%' "></div>
                                    </div>
                                    <div class="text-right mt-0.5">
                                        <span class="text-2xs font-semibold" style="color: var(--color-success); font-size: 0.7rem;" x-text="formatRupiah(prod.total_nominal)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="card">
                        <div class="flex items-center justify-between p-5 border-b muted-border" style="border-color: var(--border-color)">
                            <h3 class="font-semibold" style="color: var(--text-primary)">Stok Menipis</h3>
                            <a href="/peace_seafood/stok" class="text-xs" style="color: var(--color-primary)">Lihat Stok</a>
                        </div>
                        <div class="p-5">
                            <template x-if="lowStockItems.length === 0">
                                <p class="text-sm text-center py-4" style="color: var(--color-success)">Semua stok aman</p>
                            </template>
                            <template x-for="item in lowStockItems" :key="item.id">
                                <div class="flex items-center justify-between py-2 border-b muted-border" style="border-color: var(--border-color)">
                                    <div>
                                        <p class="text-sm font-medium" style="color: var(--text-primary)" x-text="item.nama"></p>
                                        <p class="text-xs" style="color: var(--text-secondary)" x-text="item.nama_jenis"></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold" style="color: var(--color-danger)" x-text="item.stok_qty + ' ' + (item.satuan || 'kg')"></p>
                                        <p class="text-xs" style="color: var(--text-secondary)">min: <span x-text="item.stok_minimum"></span></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="card" x-show="['bos','super_admin','admin'].includes(user.role)">
                        <div class="flex items-center justify-between p-5 border-b muted-border" style="border-color: var(--border-color)">
                            <h3 class="font-semibold flex items-center gap-2" style="color: var(--text-primary)">
                                <i data-lucide="eye" class="w-4 h-4" style="color: var(--color-primary)"></i>
                                Log Aktivitas Terkini
                            </h3>
                            <span class="badge badge-gray">Real-time</span>
                        </div>
                        <div class="overflow-x-auto p-5">
                            <table class="table text-sm">
                                <thead>
                                    <tr style="border-bottom: 1.5px solid var(--border-color);">
                                        <th style="padding: 10px 8px; text-align: left; color: var(--text-secondary);">Waktu</th>
                                        <th style="padding: 10px 8px; text-align: left; color: var(--text-secondary);">User</th>
                                        <th style="padding: 10px 8px; text-align: left; color: var(--text-secondary);">Aktivitas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-if="!stats.latest_logs || stats.latest_logs.length === 0">
                                        <tr>
                                            <td colspan="3" class="text-center py-4" style="color: var(--text-secondary)">Belum ada aktivitas tercatat</td>
                                        </tr>
                                    </template>
                                    <template x-for="log in stats.latest_logs || []" :key="log.id || log.record_id || log.timestamp">
                                        <tr style="border-bottom: 1px solid var(--border-color);">
                                            <td class="whitespace-nowrap font-mono text-xs" style="color: var(--text-secondary); padding: 8px;" x-text="formatDateTime(log.timestamp)"></td>
                                            <td style="padding: 8px;"><span class="font-semibold" style="color: var(--text-primary)" x-text="log.nama_user"></span></td>
                                            <td style="color: var(--text-secondary); padding: 8px;">
                                                <span class="badge mr-2" :class="log.action === 'INSERT' ? 'badge-success' : (log.action === 'UPDATE' ? 'badge-warning' : 'badge-gray')" x-text="log.action"></span>
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

<?php $scripts = <<<'JS'
<script>
window.dashboardStats = window.dashboardStats || {
    total_produk: 0,
    total_stok_qty: 0,
    total_stok_value: 0,
    penjualan_hari_ini: 0,
    nota_hari_ini: 0,
    total_piutang: 0,
    total_hutang: 0,
    overdue_count: 0,
    low_stock_count: 0,
    pending_timbang: 0,
    draft_pending_count: 0,
    sales_chart: [],
    stok_chart: { labels: [], values: [] },
    top_products: [],
    latest_logs: [],
    keuangan_masuk: 0,
    keuangan_keluar: 0,
    laba_rugi: 0,
};
window.stats = window.dashboardStats;

function dashboardPage() {
    const activeCharts = {};
    return {
        user: (() => {
            let u = {};
            try {
                u = JSON.parse(localStorage.getItem('user') || '{}') || {};
            } catch (e) {
                u = {};
            }
            if (u && u.role) u.role = u.role.toLowerCase();
            return u;
        })(),
        today: new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
        loading: true,
        stats: {
            total_produk: 0,
            total_stok_qty: 0,
            total_stok_value: 0,
            penjualan_hari_ini: 0,
            nota_hari_ini: 0,
            total_piutang: 0,
            total_hutang: 0,
            overdue_count: 0,
            low_stock_count: 0,
            pending_timbang: 0,
            draft_pending_count: 0,
            sales_chart: [],
            stok_chart: { labels: [], values: [] },
            top_products: [],
            latest_logs: [],
            keuangan_masuk: 0,
            keuangan_keluar: 0,
            laba_rugi: 0,
            // SaaS Metrics
            total_gudang: 0,
            active_gudang: 0,
            expired_gudang: 0,
            pending_onboarding: 0,
            total_sales_all: 0,
            total_sales_count: 0,
        },
        onboardingCompleted: null, // null=loading, true=done, false=belum
        recentNota: [],
        lowStockItems: [],
        observer: null,

        // SaaS States & Forms
        gudang: [],
        preApproveForm: {
            name: '',
            email: '',
            trial_days: '14'
        },
        devWhatsappNumber: '628123456789',
        preApproving: false,
        savingWa: false,

        async init() {
            if (!localStorage.getItem('token')) {
                window.location.href = '/peace_seafood/login';
                return;
            }

            await this.loadDashboard();
            this.loading = false;

            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
                this.setupThemeObserver();
                this.renderChartsDeferred();
            });
        },

        async loadDashboard() {
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };

                if (this.user.role === 'saas_owner') {
                    const res = await axios.get('/peace_seafood/api/dashboard', { headers });
                    if (res.data?.success) {
                        const data = res.data.data || {};
                        this.stats = { ...this.stats, ...data };
                        this.gudang = data.tenants || [];
                        this.devWhatsappNumber = data.developer_whatsapp || '628123456789';
                        window.dashboardStats = this.stats;
                        window.stats = this.stats;
                    }
                    return;
                }

                // Standard WMS load for other roles
                const [dashRes, notaRes, stokRes] = await Promise.allSettled([
                    axios.get('/peace_seafood/api/dashboard', { headers }),
                    axios.get('/peace_seafood/api/penjualan?per_page=5', { headers }),
                    axios.get('/peace_seafood/api/stok', { headers }),
                ]);

                if (dashRes.status === 'fulfilled') {
                    const data = dashRes.value.data?.data || {};
                    this.stats = { ...this.stats, ...data };
                    window.dashboardStats = this.stats;
                    window.stats = this.stats;
                }

                if (notaRes.status === 'fulfilled') {
                    this.recentNota = (notaRes.value.data?.data || []).slice(0, 5);
                }

                if (stokRes.status === 'fulfilled') {
                    const items = stokRes.value.data?.data || [];
                    this.lowStockItems = items.filter((item) => item.is_low_stock == 1).slice(0, 5);
                }
                // Cek status onboarding untuk role bos
                if (this.user.role === 'bos' || this.user.role === 'admin' || this.user.role === 'checker') {
                    try {
                        const settingsRes = await axios.get('/peace_seafood/api/settings', { headers });
                        const settings = settingsRes.data.data || [];
                        const ob = settings.find(s => s.kunci === 'onboarding_completed');
                        this.onboardingCompleted = (ob && ob.nilai === '1');
                    } catch (e) {
                        this.onboardingCompleted = true; // Gagal fetch = asumsikan done, jangan block
                    }
                } else {
                    this.onboardingCompleted = true; // super_admin selalu bisa lihat
                }

            } catch (error) {
                if (error.response?.status === 401) {
                    localStorage.clear();
                    window.location.href = '/peace_seafood/login';
                }
            }
        },

        // SaaS Owner Methods
        getBosEmail(idBos) {
            const g = this.gudang.find(x => x.id_bos == idBos);
            return g ? g.email_bos || g.nama_bos || '-' : '-';
        },

        getRemainingDaysText(expiryStr) {
            if (!expiryStr) return 'Belum onboarding / Masa trial tertunda';
            const expiry = new Date(expiryStr.substring(0, 10) + 'T00:00:00');
            const today = new Date();
            today.setHours(0,0,0,0);
            const diffTime = expiry - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            if (diffDays < 0) {
                return `Kedaluwarsa ${Math.abs(diffDays)} hari lalu`;
            } else if (diffDays === 0) {
                return 'Hari ini hari terakhir!';
            } else {
                return `${diffDays} hari tersisa`;
            }
        },

        async updateGudangSubscription(g, newDate) {
            try {
                const token = localStorage.getItem('token');
                await axios.put(`/peace_seafood/api/settings/gudang/${g.id}`, {
                    subscription_until: newDate
                }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                g.subscription_until = newDate;
                iziToast.success({
                    title: 'Sukses',
                    message: 'Masa aktif sewa tenant berhasil diperbarui!',
                    position: 'topRight'
                });
            } catch (e) {
                console.error(e);
                iziToast.error({
                    title: 'Gagal',
                    message: e.response?.data?.message || 'Gagal mengubah masa aktif sewa.',
                    position: 'topRight'
                });
            }
        },

        async updateGudangStatus(g, newStatus) {
            try {
                const token = localStorage.getItem('token');
                await axios.put(`/peace_seafood/api/settings/gudang/${g.id}`, {
                    status_langganan: newStatus
                }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                g.status_langganan = newStatus;
                iziToast.success({
                    title: 'Sukses',
                    message: 'Status langganan tenant berhasil diubah!',
                    position: 'topRight'
                });
            } catch (e) {
                console.error(e);
                iziToast.error({
                    title: 'Gagal',
                    message: e.response?.data?.message || 'Gagal mengubah status langganan.',
                    position: 'topRight'
                });
            }
        },

        async impersonateUser(idBos) {
            if (!idBos) return;
            try {
                Swal.fire({
                    title: 'Masuk Sebagai Tenant...',
                    html: 'Sedang mengalihkan sesi Anda ke akun Bos Tenant.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                const token = localStorage.getItem('token');
                const res = await axios.post('/peace_seafood/api/auth/impersonate', { user_id: idBos }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                if (res.data?.success) {
                    // Save new session
                    localStorage.setItem('token', res.data.data.token);
                    localStorage.setItem('user', JSON.stringify(res.data.data.user));
                    
                    // Redirect to reload WMS dashboard
                    window.location.reload();
                }
            } catch (e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Impersonate Gagal',
                    text: e.response?.data?.message || 'Terjadi kesalahan sistem'
                });
            }
        },

        async runPreApprove() {
            this.preApproving = true;
            try {
                const token = localStorage.getItem('token');
                const res = await axios.post('/peace_seafood/api/settings/pre-approve', this.preApproveForm, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                
                if (res.data?.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Disetujui!',
                        text: `Akun Bos "${this.preApproveForm.name}" telah disetujui. Silakan infokan ke ybs untuk mendaftar (Sign Up) menggunakan email "${this.preApproveForm.email}".`,
                        customClass: { popup: 'swal2-glassmorphic', confirmButton: 'swal2-confirm-btn' },
                        buttonsStyling: false
                    });
                    
                    // Reset form
                    this.preApproveForm.name = '';
                    this.preApproveForm.email = '';
                    this.preApproveForm.trial_days = '14';
                    
                    // Reload dashboard data to show the new tenant in list
                    await this.loadDashboard();
                }
            } catch (e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Persetujuan Gagal',
                    text: e.response?.data?.message || 'Terjadi kesalahan sistem saat memproses persetujuan.',
                    customClass: { popup: 'swal2-glassmorphic', confirmButton: 'swal2-confirm-btn' },
                    buttonsStyling: false
                });
            } finally {
                this.preApproving = false;
            }
        },

        async saveDevWhatsapp() {
            this.savingWa = true;
            try {
                const token = localStorage.getItem('token');
                await axios.put('/peace_seafood/api/settings/platform_developer_whatsapp', {
                    nilai: this.devWhatsappNumber
                }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                
                iziToast.success({
                    title: 'Sukses',
                    message: 'Nomor WhatsApp bantuan platform berhasil disimpan!',
                    position: 'topRight'
                });
            } catch (e) {
                console.error(e);
                iziToast.error({
                    title: 'Gagal',
                    message: e.response?.data?.message || 'Gagal menyimpan nomor WhatsApp bantuan.',
                    position: 'topRight'
                });
            } finally {
                this.savingWa = false;
            }
        },

        renderChartsDeferred() {
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    this.destroyCharts();
                    this.renderCharts();
                });
            });
        },

        getThemeColors() {
            const isDark = document.documentElement.classList.contains('dark') || document.documentElement.getAttribute('data-theme') === 'dark';
            return {
                isDark,
                textColor: isDark ? '#e5e7eb' : '#374151',
                secondaryTextColor: isDark ? '#cbd5e1' : '#6b7280',
                gridColor: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)',
                borderColor: isDark ? 'rgba(255,255,255,0.14)' : 'rgba(0,0,0,0.08)',
            };
        },

        themeOptions() {
            const theme = this.getThemeColors();
            return {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 500 },
                plugins: {
                    legend: {
                        labels: {
                            color: theme.textColor,
                            usePointStyle: true,
                            boxWidth: 10,
                            boxHeight: 10,
                        }
                    },
                    tooltip: {
                        backgroundColor: theme.isDark ? 'rgba(15,23,42,0.96)' : 'rgba(255,255,255,0.98)',
                        titleColor: theme.textColor,
                        bodyColor: theme.textColor,
                        borderColor: theme.borderColor,
                        borderWidth: 1,
                    }
                }
            };
        },

        formatMoney(value) {
            return (parseFloat(value) || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
        },

        formatRupiah(value) {
            return 'Rp ' + this.formatMoney(value);
        },

        formatKg(value) {
            return (Math.round(parseFloat(value) || 0)).toLocaleString('id-ID') + ' kg';
        },

        formatPercent(value) {
            return (parseFloat(value) || 0).toFixed(1) + '%';
        },

        formatDateTime(value) {
            if (!value) return '-';
            const cleanValue = value.includes(' ') ? value.replace(' ', 'T') : value;
            const date = new Date(cleanValue);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) + ' ' + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
        },

        formatActivity(log) {
            const action = (log.action || '').toUpperCase();
            const table = (log.table_name || '').toLowerCase();
            const id = log.record_id;

            if (action === 'INSERT') {
                if (table === 'nota') return `membuat Nota Penjualan #${id}`;
                if (table === 'stok_masuk') return `menginput Stok Masuk #${id}`;
                if (table === 'timbangan') return `mencatat Timbangan hasil susut #${id}`;
                if (table === 'stok_opname') return `memulai sesi Stok Opname #${id}`;
                if (table === 'stok_transfer') return `mengajukan Transfer Stok #${id}`;
                if (table === 'biaya_operasional') return `mencatat Biaya Operasional #${id}`;
                return `menambahkan data baru pada tabel ${table} #${id}`;
            }

            if (action === 'UPDATE') {
                if (table === 'nota') return `mengubah/memproses Nota Penjualan #${id}`;
                if (table === 'stok_masuk') return `mengonfirmasi Timbangan masuk #${id}`;
                if (table === 'stok_opname') return `menyelesaikan Stok Opname & sinkronisasi #${id}`;
                if (table === 'stok_transfer') return `memperbarui status Transfer Stok #${id}`;
                return `memperbarui data ${table} #${id}`;
            }

            if (action === 'DELETE') {
                return `menghapus data ${table} #${id}`;
            }

            return `melakukan aksi ${action} pada ${table} #${id}`;
        },

        destroyCharts() {
            Object.values(activeCharts).forEach((chart) => chart && chart.destroy && chart.destroy());
            for (let key in activeCharts) {
                delete activeCharts[key];
            }
        },

        renderCharts() {
            if (!window.Chart) return;

            const theme = this.getThemeColors();

            // compute finance values from stats
            const income = Number(this.stats.keuangan_masuk || 0);
            const expense = Number(this.stats.keuangan_keluar || 0);
            const profit = Number(this.stats.laba_rugi ?? (income - expense));

            const waterfallCanvas = document.getElementById('financeWaterfallChart');
            if (waterfallCanvas && typeof waterfallCanvas.getContext === 'function') {
                // Ensure mathematical correctness [min, max] where min <= max for floating bar chart in Chart.js
                const waterfallData = [
                    [0, income],
                    [income - expense, income],
                    profit >= 0 ? [0, profit] : [profit, 0]
                ];
                activeCharts.financeWaterfall = SafeChart(waterfallCanvas, {
                    type: 'bar',
                    data: {
                        labels: ['Pemasukan', 'Pengeluaran', 'Hasil'],
                        datasets: [{
                            label: 'Nilai',
                            data: waterfallData,
                            backgroundColor: ['rgba(16,185,129,0.85)', 'rgba(239,68,68,0.85)', profit >= 0 ? 'rgba(16,185,129,0.65)' : 'rgba(239,68,68,0.65)'],
                            borderColor: ['#10b981', '#ef4444', profit >= 0 ? '#10b981' : '#ef4444'],
                            borderWidth: 1,
                            borderRadius: 8,
                            barPercentage: 0.7,
                            categoryPercentage: 0.72,
                        }]
                    },
                    options: {
                        ...this.themeOptions(),
                        scales: {
                            x: { 
                                ticks: { color: theme.textColor }, 
                                grid: { display: false },
                                border: { display: false }
                            },
                            y: {
                                ticks: {
                                    color: theme.secondaryTextColor,
                                    callback: (value) => 'Rp ' + (Number(value) / 1000000).toFixed(0) + 'jt',
                                },
                                grid: { color: theme.gridColor },
                                border: { display: false }
                            }
                        },
                        plugins: {
                            ...this.themeOptions().plugins,
                            legend: { display: false },
                            tooltip: {
                                ...this.themeOptions().plugins.tooltip,
                                callbacks: {
                                    label(context) {
                                        const value = context.raw;
                                        const endValue = Array.isArray(value) ? value[1] : value;
                                        return 'Rp ' + Math.abs(endValue).toLocaleString('id-ID');
                                    }
                                }
                            },
                        }
                    }
                });
            }

            const cashCanvas = document.getElementById('cashCompositionChart');
            if (cashCanvas && typeof cashCanvas.getContext === 'function') {
                activeCharts.cashComposition = SafeChart(cashCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Uang Masuk', 'Uang Keluar'],
                        datasets: [{
                            data: [income, expense],
                            backgroundColor: ['rgba(16,185,129,0.9)', 'rgba(239,68,68,0.9)'],
                            borderColor: theme.isDark ? '#0f172a' : '#ffffff',
                            borderWidth: 2,
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        ...this.themeOptions(),
                        cutout: '65%',
                        plugins: {
                            ...this.themeOptions().plugins,
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: theme.textColor,
                                    usePointStyle: true,
                                    boxWidth: 10,
                                }
                            },
                        }
                    }
                });
            }

            const salesCanvas = document.getElementById('salesTrendChart');
            if (salesCanvas && typeof salesCanvas.getContext === 'function') {
                const ctx = salesCanvas.getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 260);
                gradient.addColorStop(0, 'rgba(37,99,235,0.35)');
                gradient.addColorStop(1, 'rgba(37,99,235,0.02)');

                const labels = (this.stats.sales_chart_labels && this.stats.sales_chart_labels.length) ? this.stats.sales_chart_labels : ['H-6','H-5','H-4','H-3','H-2','H-1','Hari ini'];
                const dataPoints = (this.stats.sales_chart && this.stats.sales_chart.length) ? this.stats.sales_chart : [0,0,0,0,0,0,0];

                activeCharts.salesTrend = SafeChart(salesCanvas, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Penjualan',
                            data: dataPoints,
                            fill: true,
                            tension: 0.4,
                            borderColor: '#2563eb',
                            backgroundColor: gradient,
                            pointBackgroundColor: '#2563eb',
                            pointBorderColor: '#2563eb',
                            pointRadius: 3,
                            pointHoverRadius: 5,
                        }]
                    },
                    options: {
                        ...this.themeOptions(),
                        plugins: {
                            ...this.themeOptions().plugins,
                            legend: { display: false },
                            tooltip: {
                                ...this.themeOptions().plugins.tooltip,
                                callbacks: {
                                    label(context) {
                                        return 'Rp ' + Number(context.parsed.y || 0).toLocaleString('id-ID');
                                    }
                                }
                            },
                        },
                        scales: {
                            x: { 
                                ticks: { color: theme.textColor }, 
                                grid: { display: false },
                                border: { display: false }
                            },
                            y: {
                                ticks: {
                                    color: theme.secondaryTextColor,
                                    callback: (value) => 'Rp ' + (Number(value) / 1000000).toFixed(0) + 'jt',
                                },
                                grid: { color: theme.gridColor },
                                border: { display: false }
                            }
                        }
                    }
                });
            }

            const topProductsCanvas = document.getElementById('topProductsChart');
            if (topProductsCanvas && typeof topProductsCanvas.getContext === 'function') {
                const topLabels = (this.stats.top_products || []).map(p => p.nama || 'N/A');
                const topData = (this.stats.top_products || []).map(p => Number(p.total_qty || p.total || 0));
                const colors = ['rgba(37,99,235,0.9)', 'rgba(6,182,212,0.9)', 'rgba(16,185,129,0.9)', 'rgba(245,158,11,0.9)', 'rgba(139,92,246,0.9)'];

                activeCharts.topProducts = SafeChart(topProductsCanvas, {
                    type: 'bar',
                    data: {
                        labels: topLabels.length ? topLabels : ['-','-','-','-','-'],
                        datasets: [{
                            label: 'Qty (kg)',
                            data: topData.length ? topData : [0,0,0,0,0],
                            backgroundColor: colors.slice(0, Math.max(5, topLabels.length)),
                            borderRadius: 8,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        ...this.themeOptions(),
                        indexAxis: 'y',
                        plugins: {
                            ...this.themeOptions().plugins,
                            legend: { display: false },
                        },
                        scales: {
                            x: { 
                                ticks: { color: theme.secondaryTextColor }, 
                                grid: { color: theme.gridColor },
                                border: { display: false }
                            },
                            y: { 
                                ticks: { color: theme.textColor }, 
                                grid: { display: false },
                                border: { display: false }
                            }
                        }
                    }
                });
            }

            const gaugeCanvas = document.getElementById('coldStorageGaugeChart');
            if (gaugeCanvas && typeof gaugeCanvas.getContext === 'function') {
                const used = Number(this.stats.total_stok_qty || 0);
                const capacity = Number(this.stats.cold_storage_capacity || 10000);
                const remaining = Math.max(0, capacity - used);
                const percent = capacity > 0 ? ((used / capacity) * 100) : 0;

                const gaugePlugin = {
                    id: 'centerTextGauge',
                    afterDraw: (chart) => {
                        const { ctx, chartArea } = chart;
                        if (!chartArea) return;
                        const valueText = percent.toFixed(1) + '%';
                        ctx.save();
                        ctx.font = '700 24px Inter, sans-serif';
                        ctx.fillStyle = theme.textColor;
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(valueText, (chartArea.left + chartArea.right) / 2, chartArea.bottom - 16);
                        ctx.font = '500 11px Inter, sans-serif';
                        ctx.fillStyle = theme.secondaryTextColor;
                        ctx.fillText('terpakai', (chartArea.left + chartArea.right) / 2, chartArea.bottom + 2);
                        ctx.restore();
                    }
                };

                activeCharts.coldStorageGauge = SafeChart(gaugeCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Terpakai', 'Tersisa'],
                        datasets: [{
                            data: [used, remaining],
                            backgroundColor: ['rgba(6,182,212,0.95)', 'rgba(148,163,184,0.35)'],
                            borderColor: theme.isDark ? '#0f172a' : '#ffffff',
                            borderWidth: 2,
                            hoverOffset: 2,
                        }]
                    },
                    options: {
                        ...this.themeOptions(),
                        cutout: '72%',
                        rotation: -90,
                        circumference: 180,
                        plugins: {
                            ...this.themeOptions().plugins,
                            legend: { display: false },
                            tooltip: {
                                ...this.themeOptions().plugins.tooltip,
                                callbacks: {
                                    label(context) {
                                        return context.label + ': ' + Number(context.parsed).toLocaleString('id-ID') + ' kg';
                                    }
                                }
                            },
                        }
                    },
                    plugins: [gaugePlugin]
                });
            }

            const stockCategoryCanvas = document.getElementById('stockCategoryChart');
            if (stockCategoryCanvas && typeof stockCategoryCanvas.getContext === 'function') {
                const labels = (this.stats.stok_chart && this.stats.stok_chart.labels) ? this.stats.stok_chart.labels : ['-'];
                const values = (this.stats.stok_chart && this.stats.stok_chart.values) ? this.stats.stok_chart.values : [0];

                activeCharts.stockCategory = SafeChart(stockCategoryCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: ['rgba(37,99,235,0.9)', 'rgba(16,185,129,0.9)', 'rgba(245,158,11,0.9)', 'rgba(6,182,212,0.9)'],
                            borderColor: theme.isDark ? '#0f172a' : '#ffffff',
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        ...this.themeOptions(),
                        cutout: '60%',
                        plugins: {
                            ...this.themeOptions().plugins,
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: theme.textColor,
                                    usePointStyle: true,
                                }
                            }
                        }
                    }
                });
            }

            const incomingStockCanvas = document.getElementById('incomingStockChart');
            if (incomingStockCanvas && typeof incomingStockCanvas.getContext === 'function') {
                const labels = (this.stats.sales_chart_labels && this.stats.sales_chart_labels.length) ? this.stats.sales_chart_labels : ['H-6','H-5','H-4','H-3','H-2','H-1','Hari ini'];
                const dataPoints = (this.stats.incoming_stock && this.stats.incoming_stock.length) ? this.stats.incoming_stock : [0,0,0,0,0,0,0];

                activeCharts.incomingStock = SafeChart(incomingStockCanvas, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Stok Masuk (kg)',
                            data: dataPoints,
                            fill: true,
                            tension: 0.35,
                            borderColor: '#14b8a6',
                            backgroundColor: (ctx) => {
                                const chart = ctx.chart;
                                const area = chart.chartArea;
                                if (!area) return 'rgba(20,184,166,0.15)';
                                const gradient = chart.ctx.createLinearGradient(0, area.top, 0, area.bottom);
                                gradient.addColorStop(0, 'rgba(20,184,166,0.35)');
                                gradient.addColorStop(1, 'rgba(20,184,166,0.04)');
                                return gradient;
                            },
                            pointBackgroundColor: '#14b8a6',
                            pointBorderColor: '#14b8a6',
                            pointRadius: 3,
                        }]
                    },
                    options: {
                        ...this.themeOptions(),
                        plugins: {
                            ...this.themeOptions().plugins,
                            legend: { display: false },
                        },
                        scales: {
                            x: { 
                                ticks: { color: theme.textColor }, 
                                grid: { display: false },
                                border: { display: false }
                            },
                            y: { 
                                ticks: { color: theme.secondaryTextColor }, 
                                grid: { color: theme.gridColor },
                                border: { display: false }
                            }
                        }
                    }
                });
            }
        },

        updateChartTheme() {
            const theme = this.getThemeColors();
            Object.values(activeCharts).forEach((chart) => {
                if (!chart) return;

                if (chart.options?.scales?.x?.ticks) chart.options.scales.x.ticks.color = theme.textColor;
                if (chart.options?.scales?.x?.grid) chart.options.scales.x.grid.color = theme.gridColor;
                if (chart.options?.scales?.y?.ticks) chart.options.scales.y.ticks.color = theme.secondaryTextColor;
                if (chart.options?.scales?.y?.grid) chart.options.scales.y.grid.color = theme.gridColor;

                if (chart.options?.plugins?.legend?.labels) {
                    chart.options.plugins.legend.labels.color = theme.textColor;
                }

                if (chart.options?.plugins?.tooltip) {
                    chart.options.plugins.tooltip.backgroundColor = theme.isDark ? 'rgba(15,23,42,0.96)' : 'rgba(255,255,255,0.98)';
                    chart.options.plugins.tooltip.titleColor = theme.textColor;
                    chart.options.plugins.tooltip.bodyColor = theme.textColor;
                    chart.options.plugins.tooltip.borderColor = theme.borderColor;
                }

                chart.update('none');
            });
        },

        setupThemeObserver() {
            if (this.observer) return;

            this.observer = new MutationObserver(() => {
                this.updateChartTheme();
            });

            this.observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class', 'data-theme'],
            });
        },
    };
}
</script>
JS;
?>