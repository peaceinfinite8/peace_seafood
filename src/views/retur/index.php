<?php ?>
<div x-data="returPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Retur</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Kelola retur stok & retur piutang</p>
        </div>
        <a href="/peace_seafood/retur/create" class="btn btn-primary"
            x-show="['bos','admin','checker'].includes(user.role)">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Buat Retur
        </a>
    </div>

    <!-- Filter -->
    <div class="card p-4 mb-6">
        <div class="flex flex-wrap gap-3">
            <select x-model="filterTipe" class="form-input w-auto">
                <option value="">Semua Tipe</option>
                <option value="stok">Retur Stok</option>
                <option value="piutang">Retur Piutang</option>
            </select>
            <select x-model="filterStatus" class="form-input w-auto">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="posted">Posted</option>
            </select>
            <button @click="loadData()" class="btn btn-primary">
                <i data-lucide="search" class="w-4 h-4"></i> Filter
            </button>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6" x-show="!loading" x-cloak>
        <div class="stat-card">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Total Retur</p>
            <p class="text-2xl font-bold" x-text="list.length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Pending</p>
            <p class="text-2xl font-bold text-yellow-500" x-text="list.filter(r => r.status === 'pending').length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Approved</p>
            <p class="text-2xl font-bold text-green-500"
                x-text="list.filter(r => ['approved','posted'].includes(r.status)).length"></p>
        </div>
        <div class="stat-card">
            <p class="text-xs mb-1" style="color: var(--text-secondary)">Rejected</p>
            <p class="text-2xl font-bold text-red-500" x-text="list.filter(r => r.status === 'rejected').length"></p>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="card p-12 text-center" style="color: var(--text-secondary)">
        <p class="text-sm">Memuat data...</p>
    </div>

    <!-- Table -->
    <div class="card" x-show="!loading" x-cloak>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tipe</th>
                        <th>Produk / Pihak</th>
                        <th>Qty / Nominal</th>
                        <th>Alasan</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="8" class="text-center py-10" style="color:var(--text-secondary)">
                                Tidak ada data retur
                            </td>
                        </tr>
                    </template>
                    <template x-for="r in filtered" :key="r.id">
                        <tr>
                            <td>
                                <span class="badge" :class="r.tipe === 'stok' ? 'badge-info' : 'badge-warning'"
                                    x-text="r.tipe === 'stok' ? 'Stok' : 'Piutang'"></span>
                            </td>
                            <td>
                                <span class="text-sm font-medium"
                                    x-text="r.nama_produk || r.nama_supplier || r.nama_pembeli || '-'"></span>
                            </td>
                            <td>
                                <span class="text-sm font-semibold" x-text="r.tipe === 'stok'
                                        ? (parseFloat(r.qty||0).toFixed(1) + ' kg')
                                        : ('Rp ' + parseFloat(r.nominal||0).toLocaleString('id-ID'))">
                                </span>
                            </td>
                            <td>
                                <span class="text-sm" x-text="alasanLabel(r.alasan)"></span>
                            </td>
                            <td><span class="text-sm" x-text="r.nama_user || '-'"></span></td>
                            <td><span class="text-sm" x-text="formatDate(r.created_at)"></span></td>
                            <td>
                                <span class="badge" :class="{
                                        'badge-warning': r.status === 'pending',
                                        'badge-success': r.status === 'approved' || r.status === 'posted',
                                        'badge-danger':  r.status === 'rejected',
                                        'badge-gray':    r.status === 'posted',
                                      }" x-text="r.status?.toUpperCase()"></span>
                            </td>
                            <td>
                                <div class="flex gap-1.5">
                                    <button @click="openDetail(r.id)" class="btn btn-secondary p-1.5" title="Detail">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button x-show="r.status === 'pending' && user.role === 'bos'"
                                        @click="approve(r.id)" class="btn btn-success p-1.5" title="Approve">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button x-show="r.status === 'pending' && user.role === 'bos'" @click="reject(r.id)"
                                        class="btn btn-danger p-1.5" title="Reject">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal-overlay" x-show="showModal" @click.self="showModal = false" x-cloak>
        <div class="modal-box max-w-lg">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg">Detail Retur</h3>
                <button @click="showModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <template x-if="detail">
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Tipe</p>
                            <span class="badge" :class="detail.tipe==='stok'?'badge-info':'badge-warning'"
                                x-text="detail.tipe==='stok'?'Retur Stok':'Retur Piutang'"></span>
                        </div>
                        <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Status</p>
                            <span class="badge"
                                :class="{'badge-warning':detail.status==='pending','badge-success':['approved','posted'].includes(detail.status),'badge-danger':detail.status==='rejected'}"
                                x-text="detail.status?.toUpperCase()"></span>
                        </div>
                        <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Produk / Pihak</p>
                            <p class="font-semibold"
                                x-text="detail.nama_produk || detail.nama_supplier || detail.nama_pembeli || '-'"></p>
                        </div>
                        <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" x-text="detail.tipe==='stok' ? 'Qty' : 'Nominal'"
                                style="color:var(--text-secondary)"></p>
                            <p class="font-semibold"
                                x-text="detail.tipe==='stok' ? (parseFloat(detail.qty||0).toFixed(1)+' kg') : ('Rp '+parseFloat(detail.nominal||0).toLocaleString('id-ID'))">
                            </p>
                        </div>
                        <div class="p-3 rounded-lg col-span-2" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Alasan</p>
                            <p class="font-semibold" x-text="alasanLabel(detail.alasan)"></p>
                        </div>
                        <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Dibuat Oleh</p>
                            <p class="font-semibold" x-text="detail.nama_user || '-'"></p>
                        </div>
                        <div class="p-3 rounded-lg" style="background:var(--bg-gray)">
                            <p class="text-xs mb-1" style="color:var(--text-secondary)">Tanggal</p>
                            <p class="font-semibold" x-text="formatDate(detail.created_at)"></p>
                        </div>
                    </div>
                    <div x-show="detail.approved_by_nama" class="p-3 rounded-lg text-sm"
                        style="background:var(--bg-gray)">
                        <p class="text-xs mb-1" style="color:var(--text-secondary)">Diproses Oleh</p>
                        <p class="font-semibold"
                            x-text="detail.approved_by_nama + ' — ' + formatDate(detail.approved_at)"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

<?php $scripts = '<script src="/peace_seafood/inline-assets/js/retur/index.js"></script>'; ?>