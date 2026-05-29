<?php ?>
<div x-data="penjualanPage()" x-init="init()">

    <div class="no-print">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold" style="color: var(--text-primary)">Penjualan</h2>
                <p class="text-sm" style="color: var(--text-secondary)">Kelola nota penjualan</p>
            </div>
            <a href="/peace_seafood/penjualan/create" class="btn btn-primary"
                x-show="['admin','super_admin'].includes(user.role)">
                <i data-lucide="file-plus" class="w-4 h-4"></i>
                Buat Nota
            </a>
        </div>

        <!-- Filter -->
        <div class="card p-4 mb-6">
            <div class="flex flex-wrap gap-3">
                <input type="text" x-model="search" placeholder="Cari no nota / pembeli..."
                    class="form-input flex-1 min-w-48">
                <input type="text" id="filter-dari" placeholder="Tanggal dari..." class="form-input w-auto" readonly>
                <input type="text" id="filter-sampai" placeholder="Tanggal sampai..." class="form-input w-auto"
                    readonly>
                <select x-model="filterStatus" class="form-input w-auto">
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="final">Final</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <button @click="loadData()" class="btn btn-primary">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    Cari
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6" x-show="!loading" x-cloak>
            <div class="stat-card">
                <p class="text-xs" style="color: var(--text-secondary)">Total Nota</p>
                <p class="text-2xl font-bold" x-text="notaList.length"></p>
            </div>
            <div class="stat-card">
                <p class="text-xs" style="color: var(--text-secondary)">Total Penjualan</p>
                <p class="text-lg font-bold" style="color: var(--color-success)"
                    x-text="'Rp ' + notaList.filter(n=>n.status==='final').reduce((s,n)=>s+parseFloat(n.total),0).toLocaleString('id-ID')">
                </p>
            </div>
            <div class="stat-card">
                <p class="text-xs" style="color: var(--text-secondary)">Draft</p>
                <p class="text-2xl font-bold" style="color: var(--color-warning)"
                    x-text="notaList.filter(n=>n.status==='draft').length"></p>
            </div>
            <div class="stat-card">
                <p class="text-xs" style="color: var(--text-secondary)">Final</p>
                <p class="text-2xl font-bold" style="color: var(--color-success)"
                    x-text="notaList.filter(n=>n.status==='final').length"></p>
            </div>
        </div>

        <!-- Table -->
        <div class="card" x-show="!loading" x-cloak>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No Nota</th>
                            <th>Tanggal</th>
                            <th>Pembeli</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="filteredNota.length === 0">
                            <tr>
                                <td colspan="7" class="text-center py-8" style="color:var(--text-secondary)">Tidak ada
                                    data</td>
                            </tr>
                        </template>
                        <template x-for="nota in filteredNota" :key="nota.id">
                            <tr>
                                <td><span class="font-mono text-sm font-medium" x-text="nota.no_nota"></span></td>
                                <td><span class="text-sm" x-text="formatDate(nota.tanggal_nota)"></span></td>
                                <td><span class="text-sm" x-text="nota.nama_pembeli || 'Umum'"></span></td>
                                <td><span class="font-semibold" style="color:var(--color-success)"
                                        x-text="'Rp ' + parseFloat(nota.total).toLocaleString('id-ID')"></span></td>
                                <td>
                                    <span class="badge"
                                        :class="(nota.pembayaran || nota.jenis_pembayaran) === 'cash' ? 'badge-success' : ((nota.pembayaran || nota.jenis_pembayaran) === 'hutang' ? 'badge-gray' : 'badge-warning')"
                                        x-text="(nota.pembayaran || nota.jenis_pembayaran || '-').toUpperCase()"></span>
                                    <div class="mt-1">
                                        <span class="badge"
                                            :class="nota.status_pembayaran === 'lunas' ? 'badge-success' : 'badge-warning'"
                                            x-text="nota.status_pembayaran ? nota.status_pembayaran.toUpperCase() : '-' "></span>
                                        <span class="text-xs ml-2" style="color: var(--text-secondary)"
                                            x-show="nota.status_pembayaran && nota.status_pembayaran !== 'lunas'"
                                            x-text="'Sisa: Rp ' + parseFloat(nota.sisa_tagihan || 0).toLocaleString('id-ID')"></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge"
                                        :class="{'badge-success':nota.status==='final','badge-warning':nota.status==='draft','badge-gray':nota.status==='cancelled'}"
                                        x-text="nota.status?.toUpperCase()"></span>
                                    <template
                                        x-if="nota.status === 'draft' && nota.catatan && nota.catatan.includes('[Draft oleh Checker')">
                                        <span class="badge badge-warning ml-1"
                                            style="background: rgba(245,158,11,0.15); color: var(--color-warning); border: 1px solid rgba(245,158,11,0.3);"
                                            title="Dikirim oleh Checker">Dari Checker</span>
                                    </template>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <button @click="showDetail(nota.id)" class="btn btn-secondary p-1.5"
                                            title="Detail">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button
                                            x-show="nota.status === 'draft' && ['admin','super_admin'].includes(user.role)"
                                            @click="openFinalizeModal(nota)"
                                            class="btn btn-success px-2.5 py-1.5 text-xs flex items-center gap-1 font-semibold"
                                            title="Finalisasi Transaksi">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                            Finalisasi Transaksi
                                        </button>
                                        <button
                                            x-show="nota.status === 'final' && (nota.pembayaran || nota.jenis_pembayaran) === 'hutang' && nota.status_pembayaran !== 'lunas' && ['admin','super_admin'].includes(user.role)"
                                            @click="openBayarDirect(nota)" class="btn btn-success p-1.5"
                                            title="Bayar Cicilan">
                                            <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button
                                            x-show="nota.status !== 'cancelled' && ['admin','super_admin'].includes(user.role)"
                                            @click="cancelNota(nota.id)" class="btn btn-danger p-1.5" title="Cancel">
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
            <div class="modal-box max-w-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg" x-text="'Nota: ' + (detail?.no_nota || '')"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <template x-if="detail">
                    <div>
                        <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                            <div><span style="color:var(--text-secondary)">No Nota:</span> <strong
                                    x-text="detail.no_nota || '-' "></strong></div>
                            <div><span style="color:var(--text-secondary)">Tanggal:</span> <strong
                                    x-text="formatDate(detail.tanggal_nota)"></strong></div>
                            <div><span style="color:var(--text-secondary)">Pembeli:</span> <strong
                                    x-text="detail.nama_pembeli || 'Umum'"></strong></div>
                            <div><span style="color:var(--text-secondary)">Status:</span> <strong
                                    x-text="(detail.status || '-').toUpperCase()"></strong></div>
                            <div>
                                <span style="color:var(--text-secondary)">Pembayaran:</span>
                                <strong x-text="(detail.pembayaran || '-').toUpperCase()"></strong>
                                <span class="badge ml-2"
                                    :class="detail.status_pembayaran === 'lunas' ? 'badge-success' : 'badge-warning'"
                                    x-text="detail.status_pembayaran ? detail.status_pembayaran.toUpperCase() : '-' "></span>
                                <span class="text-xs ml-2" style="color:var(--text-secondary)"
                                    x-show="detail.status_pembayaran && detail.status_pembayaran !== 'lunas'"
                                    x-text="'Sisa: Rp ' + parseFloat(detail.sisa_tagihan || 0).toLocaleString('id-ID')"></span>
                            </div>
                            <div x-show="detail.pembayaran === 'transfer'"><span
                                    style="color:var(--text-secondary)">Bank Tujuan:</span> <strong
                                    x-text="detail.nama_bank ? detail.nama_bank + ' - ' + detail.account_name : '-' "></strong>
                            </div>
                        </div>
                        <table class="table mb-4">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="item in (detail.items || [])" :key="item.id">
                                    <tr>
                                        <td x-text="item.nama_produk"></td>
                                        <td x-text="formatWeight(item.qty, item.satuan)"></td>
                                        <td x-text="formatRupiah(item.harga_jual)"></td>
                                        <td x-text="formatRupiah(item.subtotal)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <div class="text-right space-y-1 text-sm">
                            <div>Subtotal: <strong x-text="formatRupiah(detail.subtotal)"></strong></div>
                            <div x-show="parseFloat(detail.diskon_nominal||0) > 0">Diskon: <strong class="text-red-500"
                                    x-text="'- ' + formatRupiah(detail.diskon_nominal)"></strong></div>
                            <div x-show="parseFloat(detail.pajak||0) > 0">Pajak: <strong
                                    x-text="'+ ' + formatRupiah(detail.pajak)"></strong></div>
                            <div class="text-lg font-bold" style="color:var(--color-primary)">
                                Total: <span x-text="formatRupiah(detail.total)"></span>
                            </div>
                        </div>

                        <!-- Riwayat Pembayaran Cicilan -->
                        <template
                            x-if="detail.pembayaran === 'hutang' && detail.payments && detail.payments.length > 0">
                            <div class="mt-6 pt-4 border-t" style="border-color: var(--border-color)">
                                <h4 class="font-bold text-sm mb-2" style="color: var(--text-primary)">Riwayat Pembayaran
                                    Cicilan</h4>
                                <div class="overflow-x-auto">
                                    <table class="table text-xs">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Nominal</th>
                                                <th>Penerima</th>
                                                <th>Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="p in detail.payments" :key="p.id">
                                                <tr>
                                                    <td class="whitespace-nowrap" x-text="formatDate(p.created_at)">
                                                    </td>
                                                    <td class="font-semibold text-green-600 dark:text-green-400 whitespace-nowrap"
                                                        x-text="formatRupiah(p.nominal_bayar)"></td>
                                                    <td x-text="p.nama_user || '-'"></td>
                                                    <td x-text="p.keterangan || '-'"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>

                        <!-- Print Layout Selection & Action Buttons -->
                        <div class="mt-6 pt-4 border-t flex flex-wrap items-center justify-between gap-4"
                            style="border-color: var(--border-color)">
                            <div class="flex items-center gap-2">
                                <label class="text-xs font-semibold" style="color: var(--text-secondary)">Pilih
                                    Kertas:</label>
                                <select x-model="printLayout" class="form-input text-xs py-1 px-2 w-auto">
                                    <option value="a4">A4 (Nota Kantor)</option>
                                    <option value="a5">A5 (Nota Tradisional)</option>
                                    <option value="thermal">Thermal 80mm</option>
                                    <option value="thermal58">Thermal 58mm</option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <a :href="getWhatsAppShareLink()" target="_blank"
                                    class="btn btn-success btn-sm flex items-center gap-1.5"
                                    style="background:#25D366; color:white;" x-show="detail">
                                    <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                    Kirim via WhatsApp
                                </a>
                                <button @click="printInvoice()"
                                    class="btn btn-primary btn-sm flex items-center gap-1.5">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    Cetak Nota
                                </button>
                                <button @click="showModal = false" class="btn btn-secondary btn-sm">Tutup</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Bayar Modal -->
        <div class="modal-overlay" x-show="showBayarModal" @click.self="showBayarModal = false" x-cloak>
            <div class="modal-box">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg">Input Pembayaran Cicilan</h3>
                    <button @click="showBayarModal = false" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div x-show="selectedNota"
                    class="mb-4 space-y-1.5 p-3 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <p class="text-sm"><span style="color:var(--text-secondary)">No Nota:</span> <strong
                            x-text="selectedNota?.no_nota"></strong></p>
                    <p class="text-sm"><span style="color:var(--text-secondary)">Pembeli:</span> <strong
                            x-text="selectedNota?.nama_pembeli || 'Umum'"></strong></p>
                    <p class="text-sm"><span style="color:var(--text-secondary)">Sisa Tagihan:</span> <strong
                            class="text-red-500"
                            x-text="'Rp ' + parseFloat(selectedNota?.sisa_tagihan||0).toLocaleString('id-ID')"></strong>
                    </p>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label block text-sm font-semibold mb-1">Nominal Bayar <span
                            class="text-red-500">*</span></label>
                    <input type="text" :value="bayarForm.nominal_bayar" @input="formatBayarMoney($event)"
                        class="form-input w-full" inputmode="numeric" placeholder="Masukkan nominal...">
                </div>
                <div class="form-group mb-4">
                    <label class="form-label block text-sm font-semibold mb-1">Tanggal Pembayaran</label>
                    <input type="date" x-model="bayarForm.tanggal_bayar" class="form-input w-full">
                </div>
                <div class="form-group mb-4">
                    <label class="form-label block text-sm font-semibold mb-1">Catatan</label>
                    <input type="text" x-model="bayarForm.catatan" class="form-input w-full" placeholder="Opsional">
                </div>
                <div class="flex gap-3 mt-6">
                    <button @click="submitBayarDirect()" class="btn btn-primary flex-1">Simpan Pembayaran</button>
                    <button @click="showBayarModal = false" class="btn btn-secondary flex-1">Batal</button>
                </div>
            </div>
        </div>

        <!-- Finalize Modal (Modal Finalisasi Transaksi dengan Pembayaran) -->
        <div class="modal-overlay" x-show="showFinalizeModal" @click.self="showFinalizeModal = false" x-cloak>
            <div class="modal-box max-w-lg">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg flex items-center gap-2" style="color: var(--text-primary)">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500"></i>
                        Finalisasi Transaksi Penjualan
                    </h3>
                    <button @click="showFinalizeModal = false" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div x-show="finalizeNotaData"
                    class="mb-4 space-y-1.5 p-4 rounded-xl shadow-inner bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                    <div class="flex justify-between text-sm">
                        <span style="color: var(--text-secondary)">No. Nota:</span>
                        <strong class="font-mono" x-text="finalizeNotaData?.no_nota"></strong>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span style="color: var(--text-secondary)">Pembeli:</span>
                        <strong x-text="finalizeNotaData?.nama_pembeli || 'Umum'"></strong>
                    </div>
                    <div class="flex justify-between items-center pt-2 mt-2 border-t text-base font-bold"
                        style="border-color: var(--border-color)">
                        <span style="color: var(--text-primary)">Total Tagihan:</span>
                        <span style="color: var(--color-success)"
                            x-text="'Rp ' + parseFloat(finalizeNotaData?.total || 0).toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="form-group">
                        <label class="form-label block text-sm font-semibold mb-1">Metode Pembayaran <span
                                class="text-red-500">*</span></label>
                        <select x-model="finalizeForm.jenis_pembayaran" class="form-input w-full"
                            @change="onFinalizePaymentChange()">
                            <option value="cash">CASH (TUNAI)</option>
                            <option value="hutang">HUTANG (TEMPO)</option>
                            <option value="transfer">TRANSFER BANK</option>
                        </select>
                    </div>

                    <!-- Bank Account Dropdown (Visible only when TRANSFER is chosen) -->
                    <div class="form-group" x-show="finalizeForm.jenis_pembayaran === 'transfer'">
                        <label class="form-label block text-sm font-semibold mb-1">Bank Tujuan <span
                                class="text-red-500">*</span></label>
                        <select x-model="finalizeForm.bank_account_id" class="form-input w-full">
                            <option value="">-- Pilih Rekening --</option>
                            <template x-for="b in bankAccounts" :key="b.id">
                                <option :value="b.id" x-text="b.bank_name + ' - ' + b.account_name"></option>
                            </template>
                        </select>
                        <p class="text-xs mt-1.5" style="color: var(--text-secondary)" x-show="selectedBank">
                            No. Rek: <span class="font-semibold" x-text="selectedBank?.account_number"></span> |
                            Pemilik: <span class="font-semibold" x-text="selectedBank?.account_name"></span>
                        </p>
                    </div>

                    <!-- Credit Limit Warning Box (Visible only when HUTANG is chosen) -->
                    <div class="form-group" x-show="finalizeForm.jenis_pembayaran === 'hutang' && creditInfo">
                        <div class="p-3 rounded-lg border text-xs"
                            :class="creditInfo?.is_over ? 'border-red-400 bg-red-50 dark:bg-red-950/20' : 'border-emerald-400 bg-emerald-50 dark:bg-emerald-950/20'">
                            <p class="font-bold" :style="creditInfo?.is_over ? 'color:#dc2626' : 'color:#059669'">
                                <span
                                    x-text="creditInfo?.is_over ? 'Peringatan Kredit Terlampaui!' : 'Sisa Kredit Tersedia' "></span>
                            </p>
                            <p class="mt-1" :style="creditInfo?.is_over ? 'color:#b91c1c' : 'color:#047857'">
                                Limit Kredit: <span class="font-semibold"
                                    x-text="formatRupiah(creditInfo?.kredit_limit || 0)"></span> <br>
                                Outstanding: <span class="font-semibold"
                                    x-text="formatRupiah(creditInfo?.outstanding || 0)"></span> <br>
                                Sisa Tersedia: <span class="font-semibold"
                                    x-text="formatRupiah(creditInfo?.available || 0)"></span>
                            </p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label block text-sm font-semibold mb-1">Catatan Transaksi</label>
                        <input type="text" x-model="finalizeForm.catatan" class="form-input w-full"
                            placeholder="Catatan internal kasir...">
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button @click="submitFinalizeDraft()"
                        class="btn btn-success flex-1 font-semibold flex items-center justify-center gap-1.5"
                        :disabled="submittingFinalize">
                        <template x-if="!submittingFinalize">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                Konfirmasi & Finalisasi
                            </span>
                        </template>
                        <template x-if="submittingFinalize">
                            <span class="flex items-center gap-1.5">
                                <div class="animate-spin w-4 h-4 rounded-full border-2 border-white"
                                    style="border-top-color: transparent"></div>
                                Memproses...
                            </span>
                        </template>
                    </button>
                    <button @click="showFinalizeModal = false"
                        class="btn btn-secondary flex-1 font-semibold">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Printable Invoice Template -->
    <div id="invoice-print-area" :class="'print-layout-' + printLayout" x-show="detail" x-cloak>
        <template x-if="detail">
            <div>
                <!-- A4 & A5 Layouts -->
                <div x-show="printLayout === 'a4' || printLayout === 'a5'">
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #333; padding-bottom: 10px;">
                        <div>
                            <h1
                                style="font-size: 20px; font-weight: bold; margin: 0; text-transform: uppercase; color: #111;">
                                PEACE SEAFOOD</h1>
                            <p style="margin: 2px 0 0 0; font-size: 11px; color: #555;">Supplier Ikan Segar &
                                Berkualitas</p>
                            <p style="margin: 2px 0 0 0; font-size: 11px; color: #555;"
                                x-text="'Gudang: ' + (detail.nama_gudang || '-')"></p>
                        </div>
                        <div style="text-align: right;">
                            <h2 style="font-size: 16px; font-weight: bold; margin: 0; color: #333;">NOTA PENJUALAN</h2>
                            <p style="margin: 3px 0 0 0; font-family: monospace; font-size: 12px;"
                                x-text="detail.no_nota"></p>
                        </div>
                    </div>

                    <div
                        style="display: grid; grid-template-columns: 1fr 1fr; margin-top: 15px; font-size: 12px; gap: 20px;">
                        <div>
                            <table style="width: 100%; border: none; margin: 0;">
                                <tr style="border: none;">
                                    <td style="width: 80px; padding: 2px 0; border: none; font-weight: bold;">Tanggal
                                    </td>
                                    <td style="padding: 2px 0; border: none;"
                                        x-text="': ' + formatDate(detail.tanggal_nota)"></td>
                                </tr>
                                <tr style="border: none;">
                                    <td style="padding: 2px 0; border: none; font-weight: bold;">Kasir</td>
                                    <td style="padding: 2px 0; border: none;" x-text="': ' + (detail.nama_user || '-')">
                                    </td>
                                </tr>
                                <tr style="border: none;">
                                    <td style="padding: 2px 0; border: none; font-weight: bold;">Pembayaran</td>
                                    <td style="padding: 2px 0; border: none;"
                                        x-text="': ' + (detail.pembayaran || '-').toUpperCase() + (detail.status_pembayaran === 'lunas' ? ' (LUNAS)' : ' (BELUM LUNAS)')">
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div style="text-align: right;">
                            <p style="margin: 0; font-weight: bold;">Kepada Yth.</p>
                            <p style="margin: 2px 0 0 0; font-size: 14px; font-weight: bold;"
                                x-text="detail.nama_pembeli || 'Umum'"></p>
                        </div>
                    </div>

                    <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                        <thead>
                            <tr style="border-bottom: 2px solid #000; border-top: 1px solid #ddd;">
                                <th style="padding: 8px 4px; text-align: left; font-weight: bold;">Produk</th>
                                <th style="padding: 8px 4px; text-align: right; font-weight: bold; width: 100px;">Qty
                                </th>
                                <th style="padding: 8px 4px; text-align: right; font-weight: bold; width: 120px;">Harga
                                    Satuan</th>
                                <th style="padding: 8px 4px; text-align: right; font-weight: bold; width: 120px;">
                                    Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in detail.items" :key="item.id">
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 8px 4px;" x-text="item.nama_produk"></td>
                                    <td style="padding: 8px 4px; text-align: right;"
                                        x-text="formatWeight(item.qty, item.satuan)"></td>
                                    <td style="padding: 8px 4px; text-align: right;"
                                        x-text="formatRupiah(item.harga_jual)"></td>
                                    <td style="padding: 8px 4px; text-align: right;"
                                        x-text="formatRupiah(item.subtotal)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <div style="display: flex; justify-content: space-between; margin-top: 15px; font-size: 12px;">
                        <div style="width: 50%;">
                            <p style="margin: 0 0 5px 0; font-style: italic;" x-show="detail.catatan"
                                x-text="'Catatan: ' + detail.catatan"></p>
                            <div x-show="detail.pembayaran === 'transfer' && detail.nama_bank"
                                style="background-color: #f9f9f9; padding: 8px; border-radius: 4px; border: 1px solid #eee; display: inline-block;">
                                <p style="margin: 0; font-weight: bold; font-size: 11px;">Info Transfer Bank:</p>
                                <p style="margin: 2px 0 0 0; font-size: 11px;"
                                    x-text="detail.nama_bank + ' - ' + detail.account_name"></p>
                                <p style="margin: 2px 0 0 0; font-size: 11px;"
                                    x-text="'No. Rek: ' + detail.account_number"></p>
                            </div>

                            <!-- Riwayat Cicilan (A4/A5) -->
                            <div x-show="detail.pembayaran === 'hutang' && detail.payments && detail.payments.length > 0"
                                style="margin-top: 15px;">
                                <p style="margin: 0 0 5px 0; font-weight: bold; font-size: 11px;">Riwayat Cicilan:</p>
                                <table
                                    style="width: 100%; border-collapse: collapse; font-size: 10px; text-align: left; border: 1px solid #ddd;">
                                    <thead>
                                        <tr style="background-color: #f9f9f9; border-bottom: 1px solid #ddd;">
                                            <th style="padding: 4px; border: 1px solid #ddd;">Tanggal</th>
                                            <th style="padding: 4px; border: 1px solid #ddd; text-align: right;">Nominal
                                            </th>
                                            <th style="padding: 4px; border: 1px solid #ddd;">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="p in detail.payments" :key="p.id">
                                            <tr style="border-bottom: 1px solid #eee;">
                                                <td style="padding: 4px; border: 1px solid #ddd;"
                                                    x-text="formatDate(p.created_at)"></td>
                                                <td style="padding: 4px; border: 1px solid #ddd; text-align: right;"
                                                    x-text="formatRupiah(p.nominal_bayar)"></td>
                                                <td style="padding: 4px; border: 1px solid #ddd;"
                                                    x-text="p.keterangan || '-'"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div style="width: 40%;">
                            <table style="width: 100%; border: none; margin: 0;">
                                <tr style="border: none;">
                                    <td style="padding: 4px 0; text-align: right; border: none; color: #555;">Subtotal:
                                    </td>
                                    <td style="padding: 4px 0; text-align: right; border: none; font-weight: bold; width: 120px;"
                                        x-text="formatRupiah(detail.subtotal)"></td>
                                </tr>
                                <tr style="border: none;" x-show="parseFloat(detail.diskon_nominal || 0) > 0">
                                    <td style="padding: 4px 0; text-align: right; border: none; color: #555;">Potongan:
                                    </td>
                                    <td style="padding: 4px 0; text-align: right; border: none; font-weight: bold; color: #d32f2f;"
                                        x-text="'- ' + formatRupiah(detail.diskon_nominal)"></td>
                                </tr>
                                <tr style="border: none;" x-show="parseFloat(detail.pajak || 0) > 0">
                                    <td style="padding: 4px 0; text-align: right; border: none; color: #555;">Pajak:
                                    </td>
                                    <td style="padding: 4px 0; text-align: right; border: none; font-weight: bold;"
                                        x-text="'+ ' + formatRupiah(detail.pajak)"></td>
                                </tr>
                                <tr style="border-top: 1.5px solid #333;">
                                    <td
                                        style="padding: 6px 0; text-align: right; border: none; font-weight: bold; font-size: 14px;">
                                        TOTAL:</td>
                                    <td style="padding: 6px 0; text-align: right; border: none; font-weight: bold; font-size: 14px; color: #111;"
                                        x-text="formatRupiah(detail.total)"></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Signature Area -->
                    <div
                        style="display: flex; justify-content: space-between; margin-top: 50px; font-size: 12px; text-align: center;">
                        <div style="width: 150px;">
                            <p style="margin-bottom: 60px;">Penerima,</p>
                            <div style="border-top: 1px solid #333; padding-top: 5px;">(
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                )</div>
                        </div>
                        <div style="width: 150px;">
                            <p style="margin-bottom: 60px;">Hormat Kami,</p>
                            <div style="border-top: 1px solid #333; padding-top: 5px;"
                                x-text="detail.nama_user || 'Kasir'"></div>
                        </div>
                    </div>
                </div>

                <!-- Thermal 80mm & 58mm Layouts -->
                <div x-show="printLayout === 'thermal' || printLayout === 'thermal58'"
                    style="font-family: 'Courier New', Courier, monospace;">
                    <div
                        style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 6px;">
                        <h1 style="font-size: 14px; font-weight: bold; margin: 0 0 2px 0;">PEACE SEAFOOD</h1>
                        <p style="margin: 0; font-size: 9px;">Gudang: <span x-text="detail.nama_gudang || '-'"></span>
                        </p>
                        <p style="margin: 2px 0 0 0; font-size: 9px;" x-text="detail.no_nota"></p>
                    </div>

                    <div
                        style="font-size: 9px; line-height: 1.3; margin-bottom: 6px; border-bottom: 1px dashed #000; padding-bottom: 6px;">
                        <table style="width: 100%; border: none; margin: 0;">
                            <tr style="border: none;">
                                <td style="padding: 1px 0; border: none;">Tgl</td>
                                <td style="padding: 1px 0; border: none; text-align: right;"
                                    x-text="formatDate(detail.tanggal_nota)"></td>
                            </tr>
                            <tr style="border: none;">
                                <td style="padding: 1px 0; border: none;">Kasir</td>
                                <td style="padding: 1px 0; border: none; text-align: right;"
                                    x-text="detail.nama_user || '-'"></td>
                            </tr>
                            <tr style="border: none;">
                                <td style="padding: 1px 0; border: none;">Cust</td>
                                <td style="padding: 1px 0; border: none; text-align: right; font-weight: bold;"
                                    x-text="detail.nama_pembeli || 'Umum'"></td>
                            </tr>
                            <tr style="border: none;">
                                <td style="padding: 1px 0; border: none;">Bayar</td>
                                <td style="padding: 1px 0; border: none; text-align: right;"
                                    x-text="(detail.pembayaran || '-').toUpperCase()"></td>
                            </tr>
                        </table>
                    </div>

                    <table style="width: 100%; border-collapse: collapse; font-size: 9px; margin-bottom: 6px;">
                        <tbody>
                            <template x-for="item in detail.items" :key="item.id">
                                <tr>
                                    <td colspan="3" style="padding: 3px 0 1px 0; font-weight: bold;"
                                        x-text="item.nama_produk"></td>
                                </tr>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 1px 0 3px 0;" x-text="formatWeight(item.qty, item.satuan)"></td>
                                    <td style="padding: 1px 0 3px 0; text-align: right;"
                                        x-text="formatRupiah(item.harga_jual)"></td>
                                    <td style="padding: 1px 0 3px 0; text-align: right; font-weight: bold;"
                                        x-text="formatRupiah(item.subtotal)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <div style="font-size: 9px; border-top: 1px dashed #000; padding-top: 4px; margin-bottom: 15px;">
                        <table style="width: 100%; border: none; margin: 0;">
                            <tr style="border: none;">
                                <td style="padding: 1px 0; border: none;">Subtotal</td>
                                <td style="padding: 1px 0; border: none; text-align: right;"
                                    x-text="formatRupiah(detail.subtotal)"></td>
                            </tr>
                            <tr style="border: none;" x-show="parseFloat(detail.diskon_nominal || 0) > 0">
                                <td style="padding: 1px 0; border: none; color: #d32f2f;">Diskon</td>
                                <td style="padding: 1px 0; border: none; text-align: right; color: #d32f2f;"
                                    x-text="'-' + formatRupiah(detail.diskon_nominal)"></td>
                            </tr>
                            <tr style="border: none;" x-show="parseFloat(detail.pajak || 0) > 0">
                                <td style="padding: 1px 0; border: none;">Pajak</td>
                                <td style="padding: 1px 0; border: none; text-align: right;"
                                    x-text="'+' + formatRupiah(detail.pajak)"></td>
                            </tr>
                            <tr style="border-top: 1px dashed #000;">
                                <td style="padding: 3px 0; border: none; font-weight: bold; font-size: 11px;">TOTAL</td>
                                <td style="padding: 3px 0; border: none; text-align: right; font-weight: bold; font-size: 11px;"
                                    x-text="formatRupiah(detail.total)"></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Riwayat Cicilan (Thermal 80mm) -->
                    <div x-show="detail.pembayaran === 'hutang' && detail.payments && detail.payments.length > 0"
                        style="font-size: 9px; border-top: 1px dashed #000; padding-top: 4px; margin-bottom: 10px;">
                        <p style="margin: 0 0 4px 0; font-weight: bold; text-align: center;">RIWAYAT CICILAN</p>
                        <table style="width: 100%; border-collapse: collapse; margin: 0;">
                            <tbody>
                                <template x-for="p in detail.payments" :key="p.id">
                                    <tr style="border-bottom: 1px dotted #ccc;">
                                        <td style="padding: 2px 0;" x-text="formatDate(p.created_at)"></td>
                                        <td style="padding: 2px 0; text-align: right;"
                                            x-text="formatRupiah(p.nominal_bayar)"></td>
                                    </tr>
                                    <template x-if="p.keterangan">
                                        <tr>
                                            <td colspan="2"
                                                style="padding: 0 0 4px 0; font-style: italic; color: #555; font-size: 8px;"
                                                x-text="'* ' + p.keterangan"></td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div
                        style="text-align: center; font-size: 8px; margin-top: 10px; border-top: 1px dashed #000; padding-top: 6px;">
                        <p style="margin: 0; font-weight: bold;">Terima Kasih</p>
                        <p style="margin: 2px 0 0 0;">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<?php $scripts = <<<'JS'
<script>
function ensureLucide(cb) {
    if (window.lucide) { try { cb(); } catch(e) {} return; }
    const start = Date.now();
    const iv = setInterval(() => {
        if (window.lucide) { clearInterval(iv); try { cb(); } catch(e) {} }
        if (Date.now() - start > 5000) clearInterval(iv);
    }, 50);
}

function penjualanPage() {
    return {
        user: (() => {
            const u = JSON.parse(localStorage.getItem('user') || '{}');
            if (u && u.role) u.role = u.role.toLowerCase();
            return u;
        })(),
        loading: true,
        notaList: [],
        search: '',
        filterDari: '',
        filterSampai: '',
        filterStatus: '',
        showModal: false,
        detail: null,
        printLayout: 'a4',
        showBayarModal: false,
        selectedNota: null,
        bayarForm: { id_hutang_piutang: '', nominal_bayar: '', tanggal_bayar: new Date().toISOString().split('T')[0], catatan: '' },
        showFinalizeModal: false,
        finalizeNotaData: null,
        finalizeForm: { jenis_pembayaran: 'cash', bank_account_id: '', catatan: '' },
        submittingFinalize: false,
        creditInfo: null,
        bankAccounts: [],

        get filteredNota() {
            const q = this.search.toLowerCase();
            return this.notaList.filter(n =>
                (!q || n.no_nota?.toLowerCase().includes(q) || n.nama_pembeli?.toLowerCase().includes(q)) &&
                (!this.filterStatus || n.status === this.filterStatus)
            );
        },

        get selectedBank() {
            return this.bankAccounts.find(b => String(b.id) === String(this.finalizeForm.bank_account_id)) || null;
        },

        async init() {
            if (!['super_admin', 'bos', 'admin'].includes(this.user.role)) {
                window.location.href = '/peace_seafood/dashboard';
                return;
            }
            await this.loadData();
            await this.loadBankAccounts();

            // Check for deep-linked invoice detail & print
            const urlParams = new URLSearchParams(window.location.search);
            const highlightId = urlParams.get('highlight');
            if (highlightId && highlightId.startsWith('nota-')) {
                const id = highlightId.replace('nota-', '');
                await this.showDetail(id);
                if (urlParams.get('print') === 'true') {
                    this.printLayout = 'thermal';
                    this.$nextTick(() => {
                        this.printInvoice();
                    });
                }
            }

            this.$nextTick(() => {
                ensureLucide(() => window.lucide.createIcons());
                this.initDatePickers();
            });
        },

        async loadBankAccounts() {
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const res = await axios.get('/peace_seafood/api/settings/bank-accounts', { headers });
                this.bankAccounts = res.data?.data || [];
            } catch(e) { console.error(e); }
        },

        openFinalizeModal(nota) {
            this.finalizeNotaData = nota;
            this.finalizeForm = {
                jenis_pembayaran: (nota.pembayaran || nota.jenis_pembayaran) === 'hutang' ? 'hutang' : 'cash',
                bank_account_id: nota.bank_account_id || '',
                catatan: nota.catatan || ''
            };
            this.creditInfo = null;
            this.showFinalizeModal = true;
            this.onFinalizePaymentChange();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async onFinalizePaymentChange() {
            if (this.finalizeForm.jenis_pembayaran === 'hutang') {
                await this.loadCreditStatus();
            } else {
                this.creditInfo = null;
            }
        },

        async loadCreditStatus() {
            this.creditInfo = null;
            if (!this.finalizeNotaData || !this.finalizeNotaData.id_pembeli || isNaN(Number(this.finalizeNotaData.id_pembeli)) || this.finalizeForm.jenis_pembayaran !== 'hutang') return;
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const res = await axios.get('/peace_seafood/api/master/pembeli/' + this.finalizeNotaData.id_pembeli + '/credit-status', { headers });
                this.creditInfo = res.data?.data || null;
            } catch (e) {
                console.error(e);
            }
        },

        async submitFinalizeDraft() {
            if (this.finalizeForm.jenis_pembayaran === 'transfer' && !this.finalizeForm.bank_account_id) {
                iziToast.warning({ title: 'Peringatan', message: 'Rekening bank wajib dipilih', position: 'topRight' });
                return;
            }
            if (this.finalizeForm.jenis_pembayaran === 'hutang' && this.creditInfo && this.creditInfo.is_over) {
                iziToast.warning({ title: 'Peringatan', message: 'Limit kredit terlampaui!', position: 'topRight' });
                return;
            }
            this.submittingFinalize = true;
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const payload = {
                    jenis_pembayaran: this.finalizeForm.jenis_pembayaran,
                    bank_account_id: this.finalizeForm.bank_account_id ? parseInt(this.finalizeForm.bank_account_id) : null,
                    catatan: this.finalizeForm.catatan
                };
                
                // 1. Update draft payment details
                await axios.put('/peace_seafood/api/penjualan/' + this.finalizeNotaData.id, payload, { headers });
                
                // 2. Finalize draft sale
                await axios.post('/peace_seafood/api/penjualan/' + this.finalizeNotaData.id + '/finalize', {}, { headers });
                
                iziToast.success({ title: 'Berhasil', message: 'Nota berhasil difinalisasi!', position: 'topRight' });
                this.showFinalizeModal = false;
                await this.loadData();
            } catch(e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal finalisasi nota', position: 'topRight' });
            } finally {
                this.submittingFinalize = false;
            }
        },

        initDatePickers() {
            if (!window.flatpickr) return;

            const locale = {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    longhand:  ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],
                },
                months: {
                    shorthand: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                    longhand:  ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
                },
            };

            const opts = {
                locale,
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true,
            };

            const fpDari = flatpickr('#filter-dari', {
                ...opts,
                onChange: ([d]) => { this.filterDari = d ? flatpickr.formatDate(d, 'Y-m-d') : ''; },
            });

            const fpSampai = flatpickr('#filter-sampai', {
                ...opts,
                onChange: ([d]) => { this.filterSampai = d ? flatpickr.formatDate(d, 'Y-m-d') : ''; },
            });
        },

        async loadData() {
            this.loading = true;
            try {
                const token   = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                let url = '/peace_seafood/api/penjualan?per_page=100';
                if (this.filterDari)   url += '&dari=' + this.filterDari;
                if (this.filterSampai) url += '&sampai=' + this.filterSampai;
                const res = await axios.get(url, { headers });
                this.notaList = res.data?.data || [];
                this.$nextTick(() => { ensureLucide(() => window.lucide.createIcons()); });
            } catch(e) { console.error(e); }
            this.loading = false;
        },

        async showDetail(id) {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/penjualan/' + id, { headers: { Authorization: 'Bearer ' + token } });
                this.detail = res.data?.data;
                this.showModal = true;
                this.$nextTick(() => { ensureLucide(() => window.lucide.createIcons()); });
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal load detail', position: 'topRight' }); }
        },

        async finalizeNota(id) {
            if (!await confirm('Finalize nota ini? Stok akan dikurangi.')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/penjualan/' + id + '/finalize', {}, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Nota difinalize!', position: 'topRight' });
                await this.loadData();
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
        },

        async cancelNota(id) {
            if (!await confirm('Batalkan nota ini?')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/penjualan/' + id + '/cancel', {}, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Nota dibatalkan', position: 'topRight' });
                await this.loadData();
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal', position: 'topRight' }); }
        },

        parseMoney(value) {
            if (value === null || value === undefined || value === '') return 0;
            if (typeof value === 'number') return value;
            const cleaned = String(value).replace(/[^0-9,-]/g, '').replace(/\./g, '').replace(/,/g, '.');
            return parseFloat(cleaned) || 0;
        },
        formatMoney(value) {
            if (value === '') return '';
            const num = Math.round(this.parseMoney(value));
            return num.toLocaleString('id-ID');
        },
        formatBayarMoney(event) {
            const raw = event.target.value;
            this.bayarForm.nominal_bayar = this.formatMoney(raw);
            event.target.value = this.bayarForm.nominal_bayar;
        },
        openBayarDirect(nota) {
            this.selectedNota = nota;
            this.bayarForm = { 
                id_hutang_piutang: nota.id_hutang_piutang, 
                nominal_bayar: '', 
                tanggal_bayar: new Date().toISOString().split('T')[0], 
                catatan: '' 
            };
            this.showBayarModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },
        async submitBayarDirect() {
            if (!this.bayarForm.nominal_bayar) {
                iziToast.warning({ title: 'Peringatan', message: 'Nominal bayar wajib diisi', position: 'topRight' });
                return;
            }
            const parsedNominal = this.parseMoney(this.bayarForm.nominal_bayar);
            if (parsedNominal <= 0) {
                iziToast.warning({ title: 'Peringatan', message: 'Nominal bayar tidak valid', position: 'topRight' });
                return;
            }
            const sisaTagihan = parseFloat(this.selectedNota?.sisa_tagihan || 0);
            if (parsedNominal > sisaTagihan) {
                iziToast.warning({ title: 'Peringatan', message: 'Nominal bayar tidak boleh melebihi sisa tagihan (' + this.formatMoney(sisaTagihan) + ')', position: 'topRight' });
                return;
            }
            try {
                const token = localStorage.getItem('token');
                const payload = {
                    ...this.bayarForm,
                    nominal_bayar: parsedNominal
                };
                await axios.post('/peace_seafood/api/keuangan/bayar', payload, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Pembayaran cicilan berhasil tersimpan!', position: 'topRight' });
                this.showBayarModal = false;
                await this.loadData();
            } catch(e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal menyimpan pembayaran', position: 'topRight' });
            }
        },

        formatDate(d) { return d ? new Date(d).toLocaleDateString('id-ID') : '-'; },
        formatWeight(qty, satuan = 'kg') {
            let q = parseFloat(qty || 0);
            if (!satuan || satuan.toLowerCase() === 'kg') {
                if (q >= 10000) {
                    return (q / 1000).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' ton';
                } else if (q >= 100) {
                    return (q / 100).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' kintal';
                } else {
                    return formatKg(q, 2);
                }
            }
            return q.toLocaleString('id-ID') + ' ' + satuan;
        },
        formatRupiah(n) {
            let val = parseFloat(n || 0);
            return 'Rp ' + val.toLocaleString('id-ID');
        },
        getWhatsAppShareLink() {
            if (!this.detail) return '#';
            const phone = this.detail.telepon_pembeli ? this.detail.telepon_pembeli.replace(/[^0-9]/g, '') : '';
            let formattedPhone = phone;
            if (formattedPhone.startsWith('0')) {
                formattedPhone = '62' + formattedPhone.slice(1);
            }
            
            const companyName = this.detail.nama_gudang || 'Peace Seafood';
            let msg = `Halo *${this.detail.nama_pembeli || 'Umum'}*,\n\n`;
            msg += `Berikut adalah nota pembelian Anda dari *${companyName}*:\n\n`;
            msg += `📄 *No Nota:* ${this.detail.no_nota}\n`;
            msg += `📅 *Tanggal:* ${this.formatDate(this.detail.tanggal_nota)}\n\n`;
            
            msg += `*Rincian Belanja:*\n`;
            if (this.detail.items && this.detail.items.length > 0) {
                this.detail.items.forEach(item => {
                    msg += `- *${item.nama_produk}*: ${this.formatWeight(item.qty, item.satuan)} x ${this.formatRupiah(item.harga_jual)} = ${this.formatRupiah(item.subtotal)}\n`;
                });
            }
            msg += `\n`;
            
            msg += `💵 *Subtotal:* ${this.formatRupiah(this.detail.subtotal)}\n`;
            if (parseFloat(this.detail.diskon_nominal || 0) > 0) {
                msg += `✂️ *Diskon:* -${this.formatRupiah(this.detail.diskon_nominal)}\n`;
            }
            if (parseFloat(this.detail.pajak || 0) > 0) {
                msg += `➕ *Pajak:* +${this.formatRupiah(this.detail.pajak)}\n`;
            }
            msg += `💰 *TOTAL TAGIHAN:* *${this.formatRupiah(this.detail.total)}*\n\n`;
            msg += `💳 *Metode Bayar:* ${(this.detail.pembayaran || '').toUpperCase()} (${(this.detail.status_pembayaran || '').toUpperCase()})\n\n`;
            msg += `Terima kasih atas kepercayaan Anda kepada kami! 🐟✨`;
            
            return 'https://wa.me/' + formattedPhone + '?text=' + encodeURIComponent(msg);
        },
        printInvoice() {
            this.$nextTick(() => {
                window.print();
            });
        }
    };
}
</script>
JS;
?>