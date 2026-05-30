<?php ?>
<div x-data="createNotaPage()" x-init="init()">

    <div class="flex items-center gap-4 mb-6">
        <a href="/peace_seafood/penjualan" class="btn btn-secondary p-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Buat Nota Penjualan</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Input transaksi penjualan baru</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Form -->
        <div class="lg:col-span-2 space-y-4">

            <!-- Pembeli -->
            <div class="card p-5">
                <h3 class="font-semibold mb-4" style="color: var(--text-primary)">Informasi Pembeli</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group col-span-2" x-show="['bos', 'super_admin'].includes(user.role)">
                        <label class="form-label">Gudang *</label>
                        <select x-model="form.id_gudang" class="form-input" @change="onGudangChange()">
                            <option value="">-- Pilih Gudang --</option>
                            <template x-for="g in gudangList" :key="g.id">
                                <option :value="g.id" x-text="g.nama"></option>
                            </template>
                        </select>
                    </div>
                    <div class="form-group col-span-2" x-data="{ searchQuery: '' }" x-init="
                        $watch('form.id_pembeli', val => {
                            const p = pembeli.find(x => String(x.id) === String(val));
                            if (p) searchQuery = p.nama;
                            else if (!val) searchQuery = '';
                            else searchQuery = val;
                        });
                        $watch('pembeli', val => {
                            const p = val.find(x => String(x.id) === String(form.id_pembeli));
                            if (p) searchQuery = p.nama;
                        });
                    ">
                        <label class="form-label">Pembeli</label>
                        <input type="text" x-model="searchQuery" list="pembeli-list" class="form-input"
                            placeholder="Cari atau ketik nama pembeli harian..." @input="
                                   const match = pembeli.find(p => p.nama === searchQuery);
                                   form.id_pembeli = match ? String(match.id) : searchQuery;
                                   onPembeliChange();
                               " @change="
                                   const match = pembeli.find(p => p.nama === searchQuery);
                                   form.id_pembeli = match ? String(match.id) : searchQuery;
                                   onPembeliChange();
                               ">
                        <datalist id="pembeli-list">
                            <template x-for="p in pembeli" :key="p.id">
                                <option :value="p.nama"></option>
                            </template>
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jenis Pembayaran <span class="text-red-500">*</span></label>
                        <select x-model="form.jenis_pembayaran" class="form-input" @change="onPaymentChange()">
                            <option value="cash">CASH</option>
                            <option value="hutang">HUTANG</option>
                            <option value="transfer">TRANSFER BANK</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Catatan</label>
                        <input type="text" x-model="form.catatan" class="form-input" placeholder="Catatan opsional">
                    </div>
                    <div class="form-group col-span-2" x-show="form.jenis_pembayaran === 'transfer'">
                        <label class="form-label">Bank Tujuan <span class="text-red-500">*</span></label>
                        <select x-model="form.bank_account_id" class="form-input" @change="onBankChange()">
                            <option value="">-- Pilih Rekening --</option>
                            <template x-for="b in bankAccounts" :key="b.id">
                                <option :value="b.id" x-text="b.bank_name + ' - ' + b.account_name"></option>
                            </template>
                        </select>
                        <p class="text-xs mt-1" style="color: var(--text-secondary)" x-show="selectedBank">
                            No. Rekening: <span class="font-semibold" x-text="selectedBank?.account_number"></span>
                            <span class="mx-1">|</span>
                            Nama Pemilik: <span class="font-semibold" x-text="selectedBank?.account_name"></span>
                        </p>
                    </div>
                    <div class="form-group col-span-2" x-show="form.jenis_pembayaran === 'hutang' && creditInfo">
                        <div class="p-3 rounded-lg border"
                            :class="creditInfo?.is_over ? 'border-red-400 bg-red-50' : 'border-emerald-400 bg-emerald-50'">
                            <p class="text-sm font-semibold"
                                :style="creditInfo?.is_over ? 'color:#dc2626' : 'color:#059669'">
                                <span x-text="creditInfo?.is_over ? 'Warning Kredit' : 'Sisa Kredit Tersedia'"></span>
                            </p>
                            <p class="text-xs mt-1" :style="creditInfo?.is_over ? 'color:#b91c1c' : 'color:#047857'">
                                Limit: <span x-text="formatRupiah(creditInfo?.kredit_limit || 0)"></span> |
                                Outstanding: <span x-text="formatRupiah(creditInfo?.outstanding || 0)"></span> |
                                Sisa: <span x-text="formatRupiah(creditInfo?.available || 0)"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold" style="color: var(--text-primary)">Item Penjualan</h3>
                    <button @click="addItem()" class="btn btn-primary btn-sm"
                        style="padding: 0.375rem 0.75rem; font-size: 0.8rem">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        Tambah Item
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, idx) in form.items" :key="idx">
                        <div class="p-4 rounded-lg border" style="border-color: var(--border-color)">
                            <div class="grid grid-cols-12 gap-3 items-end">
                                <div class="col-span-12 md:col-span-3">
                                    <label class="form-label text-xs">Produk</label>
                                    <select x-model="item.id_produk" class="form-input text-sm"
                                        @change="setHargaDefault(idx)">
                                        <option value="">-- Pilih --</option>
                                        <template x-for="p in produk" :key="p.id">
                                            <option :value="p.id"
                                                x-text="p.nama + ' (' + formatQty(p.stok_qty || 0, p.satuan) + ', Rp ' + parseFloat(p.harga_jual || 0).toLocaleString('id-ID') + ')'">
                                            </option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-span-12 md:col-span-4">
                                    <div class="flex justify-between items-center mb-1">
                                        <label class="form-label text-xs mb-0">Qty (<span
                                                x-text="item.satuan || 'kg'"></span>)</label>
                                        <template x-if="!item.satuan || item.satuan.toLowerCase() === 'kg'">
                                            <button type="button" @click="item.is_split = !item.is_split"
                                                class="text-[10px] text-blue-500 hover:text-blue-600 font-semibold focus:outline-none">
                                                <span x-text="item.is_split ? 'Input Tunggal' : 'Pecah Satuan'"></span>
                                            </button>
                                        </template>
                                    </div>
                                    <template x-if="item.satuan && item.satuan.toLowerCase() !== 'kg'">
                                        <input type="number" x-model="item.qty" class="form-input text-sm" min="0.01"
                                            step="0.01" @input="calcItem(idx)" placeholder="0">
                                    </template>
                                    <template x-if="!item.satuan || item.satuan.toLowerCase() === 'kg'">
                                        <div>
                                            <div x-show="item.is_split" class="flex gap-1 items-center">
                                                <input type="number" x-model="item.qty_ton"
                                                    class="form-input text-sm p-1 text-center" placeholder="Ton" min="0"
                                                    step="1" @input="updateItemQty(idx)" style="min-width:0; flex:1">
                                                <input type="number" x-model="item.qty_kuintal"
                                                    class="form-input text-sm p-1 text-center" placeholder="Kintal"
                                                    min="0" step="1" @input="updateItemQty(idx)"
                                                    style="min-width:0; flex:1">
                                                <input type="number" x-model="item.qty_kg"
                                                    class="form-input text-sm p-1 text-center" placeholder="Kg" min="0"
                                                    step="0.01" @input="updateItemQty(idx)" style="min-width:0; flex:1">
                                            </div>
                                            <div x-show="!item.is_split">
                                                <input type="number" x-model="item.qty" class="form-input text-sm"
                                                    min="0.01" step="0.01" @input="updateTotalQty(idx)"
                                                    placeholder="Total kg (contoh: 1540)">
                                            </div>
                                            <div class="text-[10px] mt-1 text-right font-semibold"
                                                style="color: var(--color-primary)" x-show="parseFloat(item.qty) > 0"
                                                x-text="getQtyBreakdown(item.qty)"></div>
                                        </div>
                                    </template>
                                </div>
                                <div class="col-span-6 md:col-span-2">
                                    <label class="form-label text-xs">Harga Jual / <span
                                            x-text="item.satuan || 'kg'"></span></label>
                                    <input type="text" :value="item.harga_jual" class="form-input text-sm"
                                        inputmode="numeric" @input="formatItemMoney(idx, 'harga_jual', $event)">
                                </div>
                                <div class="col-span-4 md:col-span-2">
                                    <label class="form-label text-xs">Subtotal</label>
                                    <p class="text-sm font-bold" style="color: var(--color-primary); padding: 0.5rem 0"
                                        x-text="'Rp ' + parseFloat(item.subtotal||0).toLocaleString('id-ID')"></p>
                                </div>
                                <div class="col-span-2 md:col-span-1 pb-1">
                                    <button @click="removeItem(idx)"
                                        class="btn btn-danger p-1.5 w-full flex justify-center"
                                        x-show="form.items.length > 1">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Right: Summary & Submit -->
        <div class="space-y-4">
            <div class="card p-5">
                <h3 class="font-semibold mb-4" style="color: var(--text-primary)">Ringkasan</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span style="color: var(--text-secondary)">Subtotal</span>
                        <span class="font-medium" x-text="formatRupiah(subtotal)"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label text-xs">Potongan Harga</label>
                        <div class="grid grid-cols-3 gap-2 mb-2">
                            <button type="button" class="btn btn-secondary btn-sm"
                                :class="form.diskon_mode === 'nominal' ? 'btn-primary' : ''"
                                @click="form.diskon_mode = 'nominal'; recalcTotals()">Nominal</button>
                            <button type="button" class="btn btn-secondary btn-sm"
                                :class="form.diskon_mode === 'per_unit' ? 'btn-primary' : ''"
                                @click="form.diskon_mode = 'per_unit'; recalcTotals()">Per Satuan</button>
                            <button type="button" class="btn btn-secondary btn-sm"
                                :class="form.diskon_mode === 'percent' ? 'btn-primary' : ''"
                                @click="form.diskon_mode = 'percent'; recalcTotals()">Persen (%)</button>
                        </div>
                        <input type="text" class="form-input text-sm" inputmode="numeric"
                            :placeholder="discountPlaceholder" :value="form.diskon_value"
                            @input="formatFormMoney('diskon_value', $event); recalcTotals()">
                        <p class="text-xs mt-1" style="color: var(--text-secondary)" x-text="discountHint"></p>
                    </div>

                    <div class="form-group">
                        <label class="form-label text-xs">Pajak (Rp)</label>
                        <input type="text" class="form-input text-sm" inputmode="numeric" :value="form.pajak"
                            @input="formatFormMoney('pajak', $event); recalcTotals()" placeholder="0">
                    </div>

                    <div class="border-t pt-3" style="border-color: var(--border-color)">
                        <div class="flex justify-between items-center">
                            <span class="font-bold">TOTAL</span>
                            <span class="text-xl font-bold" style="color: var(--color-primary)"
                                x-text="formatRupiah(total)"></span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 space-y-2">
                    <button @click="submitNota('draft')" class="btn btn-secondary w-full" :disabled="saving">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Simpan Draft
                    </button>
                    <button @click="openPreview()" class="btn btn-primary w-full" :disabled="saving">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Finalize & Simpan
                    </button>
                </div>

                <!-- ═══════════════════════════════════════════════
                     KALKULATOR KEMBALIAN — Auto-muncul saat CASH
                ═══════════════════════════════════════════════ -->
                <div x-show="form.jenis_pembayaran === 'cash'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2" class="mt-4 rounded-xl overflow-hidden"
                    style="border: 1px solid var(--border-color);">
                    <!-- Header -->
                    <div class="flex items-center gap-2 px-4 py-3"
                        style="background: rgba(37,99,235,0.06); border-bottom: 1px solid var(--border-color);">
                        <svg style="width:15px;height:15px;color:var(--color-primary);flex-shrink:0" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span
                            style="font-size:12px; font-weight:700; color:var(--color-primary); letter-spacing:0.04em; text-transform:uppercase;">Kembalian
                            Cash</span>
                    </div>

                    <div class="p-4 space-y-3">
                        <!-- Total tagihan (read-only) -->
                        <div class="flex justify-between items-center text-sm">
                            <span style="color: var(--text-secondary)">Total Tagihan</span>
                            <span class="font-bold" style="color: var(--text-primary)"
                                x-text="formatRupiah(total)"></span>
                        </div>

                        <!-- Input uang diterima -->
                        <div>
                            <label
                                style="font-size:11px; font-weight:600; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.05em; display:block; margin-bottom:5px;">
                                Uang Diterima
                            </label>
                            <input type="text" inputmode="numeric" class="form-input text-sm font-semibold"
                                placeholder="0" :value="uangDiterima" @input="onUangInput($event)"
                                style="text-align:right; font-size:15px;">
                        </div>

                        <!-- Nominal cepat -->
                        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:5px;">
                            <template x-for="nom in nominals" :key="nom.val">
                                <button type="button" @click="setNominal(nom.val)" class="btn btn-secondary"
                                    style="font-size:11px; padding:5px 4px; border-radius:8px; font-weight:600; text-align:center;"
                                    x-text="nom.label"></button>
                            </template>
                        </div>

                        <!-- Hasil kembalian -->
                        <div x-show="kembalian !== null" x-cloak class="rounded-xl p-3 text-center" :style="kembalian >= 0
                                ? 'background: rgba(16,185,129,0.08); border: 1.5px solid rgba(16,185,129,0.3);'
                                : 'background: rgba(239,68,68,0.08); border: 1.5px solid rgba(239,68,68,0.3);'">
                            <p style="font-size:11px; font-weight:600; margin-bottom:3px;"
                                :style="kembalian >= 0 ? 'color: var(--color-success)' : 'color: var(--color-danger)'">
                                <span x-text="kembalian >= 0 ? 'KEMBALIAN' : 'UANG KURANG'"></span>
                            </p>
                            <p style="font-size:22px; font-weight:800; line-height:1.1;"
                                :style="kembalian >= 0 ? 'color: var(--color-success)' : 'color: var(--color-danger)'"
                                x-text="formatRupiah(Math.abs(kembalian))">
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal-overlay" x-show="showPreview" @click.self="showPreview = false" x-cloak>
        <div class="modal-box max-w-3xl">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg">Preview Nota</h3>
                <button @click="showPreview = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-xs" style="color: var(--text-secondary)">Pembeli</span>
                        <div class="font-semibold"
                            x-text="selectedPembeli ? selectedPembeli.nama : (form.id_pembeli || 'Umum')"></div>
                    </div>
                    <div><span class="text-xs" style="color: var(--text-secondary)">Pembayaran</span>
                        <div class="font-semibold" x-text="paymentLabel()"></div>
                    </div>
                    <div x-show="form.jenis_pembayaran === 'transfer'"><span class="text-xs"
                            style="color: var(--text-secondary)">Bank Tujuan</span>
                        <div class="font-semibold"
                            x-text="selectedBank ? selectedBank.bank_name + ' - ' + selectedBank.account_name : '-' ">
                        </div>
                    </div>
                    <div x-show="form.jenis_pembayaran === 'transfer'"><span class="text-xs"
                            style="color: var(--text-secondary)">No Rekening</span>
                        <div class="font-semibold" x-text="selectedBank?.account_number || '-' "></div>
                    </div>
                </div>

                <div class="border rounded-lg overflow-hidden" style="border-color: var(--border-color)">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, idx) in form.items" :key="idx">
                                <tr>
                                    <td x-text="getProductName(item.id_produk)"></td>
                                    <td x-text="formatQty(item.qty, item.satuan)"></td>
                                    <td x-text="formatRupiah(parseMoney(item.harga_jual))"></td>
                                    <td x-text="formatRupiah(item.subtotal)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="text-right" style="color: var(--text-secondary)">Subtotal</div>
                    <div class="font-semibold text-right" x-text="formatRupiah(subtotal)"></div>
                    <div class="text-right" style="color: var(--text-secondary)">Potongan Harga</div>
                    <div class="font-semibold text-right" x-text="'- ' + formatRupiah(discountTotal())"></div>
                    <div class="text-right" style="color: var(--text-secondary)">Pajak</div>
                    <div class="font-semibold text-right" x-text="'+ ' + formatRupiah(parseMoney(form.pajak))"></div>
                    <div class="text-right text-lg font-bold">TOTAL</div>
                    <div class="font-bold text-right text-lg" style="color: var(--color-primary)"
                        x-text="formatRupiah(total)"></div>
                </div>

                <div class="p-3 rounded-lg"
                    x-show="form.jenis_pembayaran === 'hutang' && creditInfo && creditInfo.is_over"
                    style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25)">
                    <p class="text-sm font-semibold" style="color: var(--color-danger)">Limit kredit terlampaui.
                        Finalize akan ditolak oleh server.</p>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button class="btn btn-secondary" @click="showPreview = false">Kembali</button>
                <button class="btn btn-primary"
                    :disabled="saving || (form.jenis_pembayaran === 'hutang' && creditInfo && creditInfo.is_over)"
                    @click="submitNota('final')">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Simpan Final
                </button>
            </div>
        </div>
    </div>
</div>

<?php $scripts = '<script src="/peace_seafood/inline-assets/js/penjualan/create.js"></script>'; ?>