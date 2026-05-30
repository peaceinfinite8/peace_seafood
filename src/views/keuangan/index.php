<?php ?>
<div x-data="keuanganPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Keuangan</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Hutang, piutang, dan biaya operasional</p>
        </div>
        <button @click="showCreateModal = true; $nextTick(() => { if(window.lucide) lucide.createIcons(); })"
            class="btn btn-primary" x-show="user.role === 'super_admin'">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Input Manual
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="stat-card">
            <p class="text-xs font-semibold uppercase" style="color: var(--text-secondary)">Total Hutang</p>
            <p class="text-2xl font-bold text-red-500" x-text="'Rp ' + summary.total_hutang.toLocaleString('id-ID')">
            </p>
            <p class="text-xs mt-1" style="color: var(--text-secondary)">ke supplier</p>
        </div>
        <div class="stat-card">
            <p class="text-xs font-semibold uppercase" style="color: var(--text-secondary)">Total Piutang</p>
            <p class="text-2xl font-bold text-green-500" x-text="'Rp ' + summary.total_piutang.toLocaleString('id-ID')">
            </p>
            <p class="text-xs mt-1" style="color: var(--text-secondary)">dari pembeli</p>
        </div>
        <div class="stat-card col-span-2 lg:col-span-1">
            <p class="text-xs font-semibold uppercase" style="color: var(--text-secondary)">Jatuh Tempo</p>
            <p class="text-2xl font-bold" :class="summary.overdue_count > 0 ? 'text-red-500' : 'text-green-500'"
                x-text="summary.overdue_count + ' tagihan'"></p>
            <p class="text-xs mt-1" style="color: var(--color-danger)" x-show="summary.overdue_count > 0">⚠ Perlu
                perhatian!</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="keu-tab-group mb-4">
        <button @click="activeTab = 'semua'" class="keu-tab"
            :class="activeTab === 'semua' ? 'keu-tab--active keu-tab--semua' : 'keu-tab--idle'">
            Semua
        </button>
        <button @click="activeTab = 'hutang'" class="keu-tab"
            :class="activeTab === 'hutang' ? 'keu-tab--active keu-tab--hutang' : 'keu-tab--idle'">
            Hutang
        </button>
        <button @click="activeTab = 'piutang'" class="keu-tab"
            :class="activeTab === 'piutang' ? 'keu-tab--active keu-tab--piutang' : 'keu-tab--idle'">
            Piutang
        </button>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Pihak</th>
                        <th>Nominal</th>
                        <th>Sisa</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filteredList.length === 0">
                        <tr>
                            <td colspan="7" class="text-center py-8" style="color:var(--text-secondary)">Tidak ada data
                            </td>
                        </tr>
                    </template>
                    <template x-for="hp in filteredList" :key="hp.id">
                        <tr :id="'debt-' + hp.id" :data-highlight="'debt-' + hp.id">
                            <td>
                                <span class="badge" :class="hp.jenis === 'hutang' ? 'badge-danger' : 'badge-success'"
                                    x-text="hp.jenis?.toUpperCase()"></span>
                            </td>
                            <td>
                                <span class="text-sm font-medium"
                                    x-text="hp.nama_supplier || hp.nama_pembeli || '-'"></span>
                            </td>
                            <td>
                                <span class="text-sm"
                                    x-text="'Rp ' + parseFloat(hp.nominal||0).toLocaleString('id-ID')"></span>
                            </td>
                            <td>
                                <span class="font-semibold text-sm"
                                    :style="hp.status==='open'||hp.status==='sebagian' ? 'color:var(--color-danger)' : 'color:var(--color-success)'"
                                    x-text="'Rp ' + parseFloat(hp.sisa_hutang||0).toLocaleString('id-ID')"></span>
                            </td>
                            <td>
                                <span class="text-sm"
                                    :class="hp.hari_jatuh_tempo < 0 ? 'text-red-500 font-bold' : hp.hari_jatuh_tempo < 7 ? 'text-yellow-500' : ''"
                                    x-text="hp.jatuh_tempo ? new Date(hp.jatuh_tempo).toLocaleDateString('id-ID') : '-'"></span>
                                <span class="badge badge-danger ml-1" x-show="hp.hari_jatuh_tempo < 0">OVERDUE</span>
                            </td>
                            <td>
                                <span class="badge"
                                    :class="{'badge-success':hp.status==='lunas','badge-warning':hp.status==='sebagian','badge-danger':hp.status==='open'}"
                                    x-text="hp.status?.toUpperCase()"></span>
                            </td>
                            <td>
                                <button x-show="hp.status !== 'lunas' && user.role === 'super_admin'"
                                    @click="openBayar(hp)" class="btn btn-success p-1.5" title="Input Pembayaran">
                                    <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bayar Modal -->
    <div class="modal-overlay" x-show="showBayarModal" @click.self="showBayarModal = false" x-cloak>
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Input Pembayaran</h3>
            <div x-show="selectedHp">
                <p class="text-sm mb-1"><span style="color:var(--text-secondary)">Pihak:</span> <strong
                        x-text="selectedHp?.nama_supplier || selectedHp?.nama_pembeli"></strong></p>
                <p class="text-sm mb-4"><span style="color:var(--text-secondary)">Sisa:</span> <strong
                        class="text-red-500"
                        x-text="'Rp ' + parseFloat(selectedHp?.sisa_hutang||0).toLocaleString('id-ID')"></strong></p>
            </div>
            <div class="form-group">
                <label class="form-label">Nominal Bayar <span class="text-red-500">*</span></label>
                <input type="text" :value="bayarForm.nominal_bayar" @input="formatBayarMoney($event)" class="form-input"
                    inputmode="numeric" placeholder="Masukkan nominal...">
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Pembayaran</label>
                <input type="date" x-model="bayarForm.tanggal_bayar" class="form-input"
                    :value="new Date().toISOString().split('T')[0]">
            </div>
            <div class="form-group">
                <label class="form-label">Catatan</label>
                <input type="text" x-model="bayarForm.catatan" class="form-input" placeholder="Opsional">
            </div>
            <div class="flex gap-3 mt-4">
                <button @click="submitBayar()" class="btn btn-primary">Simpan Pembayaran</button>
                <button @click="showBayarModal = false" class="btn btn-secondary">Batal</button>
            </div>
        </div>
    </div>
</div>

<?php $scripts = '<script src="/peace_seafood/inline-assets/js/keuangan/index.js"></script>'; ?>