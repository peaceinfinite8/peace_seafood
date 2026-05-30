/* extracted from penjualan_create.scripts.1.js */
// extracted from src/views/penjualan/create.php
function createNotaPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        form: {
            id_gudang: '',
            id_pembeli: '',
            jenis_pembayaran: 'cash',
            bank_account_id: '',
            catatan: '',
            diskon_mode: 'nominal',
            diskon_value: '0',
            pajak: '0',
            items: [{ id_produk: '', qty: '', qty_ton: '', qty_kuintal: '', qty_kg: '', satuan: 'kg', harga_jual: '', subtotal: 0, is_split: false }],
        },
        produk: [],
        pembeli: [],
        gudangList: [],
        bankAccounts: [],
        creditInfo: null,
        showPreview: false,
        saving: false,

        // Cash calculator merged state
        uangDiterima: '',
        kembalian: null,
        nominals: [
            { label: '50rb',  val: 50000    },
            { label: '100rb', val: 100000   },
            { label: '200rb', val: 200000   },
            { label: '500rb', val: 500000   },
            { label: '1jt',   val: 1000000  },
            { label: '2jt',   val: 2000000  },
            { label: '5jt',   val: 5000000  },
            { label: '10jt',  val: 10000000 },
            { label: '50jt',  val: 50000000 },
        ],

        get subtotal() { return this.form.items.reduce((s, i) => s + this.parseMoney(i.subtotal || 0), 0); },
        get total() { return Math.max(0, this.subtotal - this.discountTotal() + this.parseMoney(this.form.pajak)); },
        get selectedBank() { return this.bankAccounts.find(b => String(b.id) === String(this.form.bank_account_id)) || null; },
        get selectedPembeli() { return this.pembeli.find(p => String(p.id) === String(this.form.id_pembeli)) || null; },

        async init() {
            if (!['super_admin', 'admin'].includes(this.user.role)) {
                window.location.href = '/peace_seafood/dashboard';
                return;
            }
            if (!['bos', 'super_admin'].includes(this.user.role) && this.user.id_gudang) {
                this.form.id_gudang = String(this.user.id_gudang);
            }
            await this.loadMasterData();

            // Watchers
            this.$watch('total', v => {
                this.uangDiterima = '';
                this.kembalian = null;
            });
            this.$watch('form.diskon_mode', v => {
                this.form.diskon_value = '0';
                this.recalcTotals();
            });

            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadMasterData() {
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const query = (['bos', 'super_admin'].includes(this.user.role) && this.form.id_gudang) ? ('?id_gudang=' + this.form.id_gudang) : '';
                const requests = [
                    axios.get('/peace_seafood/api/master/produk' + query, { headers }),
                    axios.get('/peace_seafood/api/master/pembeli', { headers }),
                    axios.get('/peace_seafood/api/settings/bank-accounts' + query, { headers }),
                ];
                if (['bos', 'super_admin'].includes(this.user.role)) {
                    requests.unshift(axios.get('/peace_seafood/api/settings/gudang', { headers }));
                }

                const responses = await Promise.all(requests);
                let offset = 0;
                if (['bos', 'super_admin'].includes(this.user.role)) {
                    this.gudangList = responses[0].data?.data || [];
                    offset = 1;
                }
                const prodRes = responses[offset + 0];
                const pemRes  = responses[offset + 1];
                const bankRes = responses[offset + 2];

                this.produk  = prodRes.data?.data || [];
                this.pembeli = pemRes.data?.data || [];
                this.bankAccounts = bankRes.data?.data || [];

                if (['bos', 'super_admin'].includes(this.user.role) && !this.form.id_gudang && this.gudangList.length) {
                    this.form.id_gudang = String(this.gudangList[0].id);
                    await this.reloadProducts();
                    await this.reloadBankAccounts();
                }
            } catch(e) { console.error(e); }
        },

        async reloadProducts() {
            const token = localStorage.getItem('token');
            const headers = { Authorization: 'Bearer ' + token };
            const query = (['bos', 'super_admin'].includes(this.user.role) && this.form.id_gudang) ? ('?id_gudang=' + this.form.id_gudang) : '';
            const res = await axios.get('/peace_seafood/api/master/produk' + query, { headers });
            this.produk = res.data?.data || [];
            this.form.items = this.form.items.map(i => ({ ...i, id_produk: '', qty: '', qty_ton: '', qty_kuintal: '', qty_kg: '', satuan: 'kg', harga_jual: '', subtotal: 0, is_split: false }));
        },

        async reloadBankAccounts() {
            const token = localStorage.getItem('token');
            const headers = { Authorization: 'Bearer ' + token };
            const query = (['bos', 'super_admin'].includes(this.user.role) && this.form.id_gudang) ? ('?id_gudang=' + this.form.id_gudang) : '';
            const res = await axios.get('/peace_seafood/api/settings/bank-accounts' + query, { headers });
            this.bankAccounts = res.data?.data || [];
        },

        onGudangChange() { this.reloadProducts(); this.reloadBankAccounts(); this.creditInfo = null; },
        onPembeliChange() { this.loadCreditStatus(); },
        onPaymentChange() {
            if (this.form.jenis_pembayaran === 'transfer') {
                this.reloadBankAccounts();
            } else {
                this.form.bank_account_id = '';
            }
            this.loadCreditStatus();
        },
        onBankChange() {},

        addItem() { this.form.items.push({ id_produk: '', qty: '', qty_ton: '', qty_kuintal: '', qty_kg: '', satuan: 'kg', harga_jual: '', subtotal: 0, is_split: false }); },
        removeItem(idx) { this.form.items.splice(idx, 1); this.recalcTotals(); },
        setHargaDefault(idx) {
            const p = this.produk.find(x => x.id == this.form.items[idx].id_produk);
            if (p) {
                this.form.items[idx].harga_jual = this.formatMoney(p.harga_jual || 0);
                this.form.items[idx].satuan = p.satuan || 'kg';
            } else {
                this.form.items[idx].harga_jual = '';
                this.form.items[idx].satuan = 'kg';
            }
            this.form.items[idx].qty = '';
            this.form.items[idx].qty_ton = '';
            this.form.items[idx].qty_kuintal = '';
            this.form.items[idx].qty_kg = '';
            this.form.items[idx].is_split = false;
            this.calcItem(idx);
        },
        calcItem(idx) {
            const item = this.form.items[idx];
            item.subtotal = Math.round((parseFloat(item.qty) || 0) * this.parseMoney(item.harga_jual || 0));
            this.recalcTotals();
        },
        updateItemQty(idx) {
            const item = this.form.items[idx];
            const ton = parseFloat(item.qty_ton) || 0;
            const kuintal = parseFloat(item.qty_kuintal) || 0;
            const kg = parseFloat(item.qty_kg) || 0;
            item.qty = (ton * 1000) + (kuintal * 100) + kg;
            this.calcItem(idx);
        },
        updateTotalQty(idx) {
            const item = this.form.items[idx];
            this.updateSplitQtyFromTotal(item);
            this.calcItem(idx);
        },
        updateSplitQtyFromTotal(item) {
            const totalKg = parseFloat(item.qty) || 0;
            if (totalKg <= 0) {
                item.qty_ton = '';
                item.qty_kuintal = '';
                item.qty_kg = '';
                return;
            }
            let remaining = totalKg;
            const ton = Math.floor(remaining / 1000);
            remaining %= 1000;
            const kuintal = Math.floor(remaining / 100);
            remaining %= 100;
            const kg = Math.round(remaining * 100) / 100;
            item.qty_ton = ton > 0 ? ton : '';
            item.qty_kuintal = kuintal > 0 ? kuintal : '';
            item.qty_kg = kg > 0 ? kg : '';
        },

        parseMoney(value) {
            if (value === null || value === undefined || value === '') return 0;
            if (typeof value === 'number') return value;
            const cleaned = String(value).replace(/[^0-9,-]/g, '').replace(/\./g, '').replace(/,/g, '.');
            return parseFloat(cleaned) || 0;
        },
        formatMoney(value) {
            const num = Math.round(this.parseMoney(value));
            return num.toLocaleString('id-ID');
        },
        formatFormMoney(field, event) {
            const raw = event.target.value;
            if (field === 'diskon_value' && this.form.diskon_mode === 'percent') {
                let cleaned = raw.replace(/[^0-9]/g, '');
                let num = parseInt(cleaned) || 0;
                if (num > 100) num = 100;
                this.form[field] = cleaned === '' ? '0' : String(num);
                event.target.value = this.form[field];
            } else {
                this.form[field] = this.formatMoney(raw);
                event.target.value = this.form[field];
            }
        },
        formatItemMoney(idx, field, event) {
            const raw = event.target.value;
            this.form.items[idx][field] = this.formatMoney(raw);
            event.target.value = this.form.items[idx][field];
            this.calcItem(idx);
        },

        discountTotal() {
            const val = this.parseMoney(this.form.diskon_value);
            if (this.form.diskon_mode === 'per_unit') {
                const totalQty = this.form.items.reduce((s, i) => s + (parseFloat(i.qty) || 0), 0);
                return Math.round(val * totalQty);
            }
            if (this.form.diskon_mode === 'percent') {
                return Math.round(this.subtotal * (val / 100));
            }
            return val;
        },
        get discountPlaceholder() {
            return this.form.diskon_mode === 'percent' ? 'Contoh: 10 untuk 10%' : 'Contoh: 50.000';
        },
        get discountHint() {
            if (this.form.diskon_mode === 'per_unit') return 'Potongan per satuan dikalikan total qty semua item.';
            if (this.form.diskon_mode === 'percent') return 'Masukkan angka persen, mis. 10 untuk potongan 10%.';
            return 'Masukkan nominal potongan total dalam rupiah';
        },
        recalcTotals() {
            this.form.items = this.form.items.map(i => ({ ...i, subtotal: Math.round((parseFloat(i.qty) || 0) * this.parseMoney(i.harga_jual || 0)) }));
        },
        formatRupiah(n) {
            return 'Rp ' + this.parseMoney(n).toLocaleString('id-ID');
        },
        formatQty(qty, satuan) {
            let q = parseFloat(qty) || 0;
            if (!satuan || satuan.toLowerCase() === 'kg') {
                if (q >= 10000) {
                    return (q / 1000).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' ton';
                } else if (q >= 100) {
                    return (q / 100).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' kintal';
                } else {
                    return q.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' kg';
                }
            }
            return q.toLocaleString('id-ID') + ' ' + satuan;
        },
        getQtyBreakdown(qty) {
            const totalKg = parseFloat(qty) || 0;
            if (totalKg <= 0) return '';
            let remaining = totalKg;
            const ton = Math.floor(remaining / 1000);
            remaining %= 1000;
            const kuintal = Math.floor(remaining / 100);
            remaining %= 100;
            const kg = Math.round(remaining * 100) / 100;

            const parts = [];
            if (ton > 0) parts.push(ton + ' Ton');
            if (kuintal > 0) parts.push(kuintal + ' kintal');
            if (kg > 0) parts.push(kg + ' kg');
            return '= ' + parts.join(', ');
        },


        // Merged cash calculator methods
        onUangInput(event) {
            const raw = event.target.value.replace(/\D/g, '');
            const num = parseInt(raw) || 0;
            this.uangDiterima = num > 0 ? num.toLocaleString('id-ID') : '';
            event.target.value = this.uangDiterima;
            this.hitungKembalian(num);
        },
        setNominal(val) {
            const existing = parseInt((this.uangDiterima || '0').replace(/\D/g, '')) || 0;
            const newVal = existing + val;
            this.uangDiterima = newVal.toLocaleString('id-ID');
            this.hitungKembalian(newVal);
        },
        hitungKembalian(uang) {
            if (uang <= 0 || this.total <= 0) {
                this.kembalian = null;
                return;
            }
            this.kembalian = uang - this.total;
        },
        getProductName(id) {
            const p = this.produk.find(x => String(x.id) === String(id));
            return p ? p.nama : '-';
        },
        paymentLabel() {
            return this.form.jenis_pembayaran === 'transfer' ? 'TRANSFER BANK' : this.form.jenis_pembayaran.toUpperCase();
        },

        async loadCreditStatus() {
            this.creditInfo = null;
            if (!this.form.id_pembeli || isNaN(Number(this.form.id_pembeli)) || this.form.jenis_pembayaran !== 'hutang') return;
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const res = await axios.get('/peace_seafood/api/master/pembeli/' + this.form.id_pembeli + '/credit-status', { headers });
                this.creditInfo = res.data?.data || null;
            } catch (e) {
                console.error(e);
            }
        },

        openPreview() {
            if (!this.validate()) return;
            this.showPreview = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        validate() {
            if (['bos', 'super_admin'].includes(this.user.role) && !this.form.id_gudang) {
                iziToast.warning({ title: 'Peringatan', message: 'Gudang wajib dipilih', position: 'topRight' });
                return false;
            }
            if (this.form.items.some(i => !i.id_produk || !i.qty || parseFloat(i.qty) <= 0)) {
                iziToast.warning({ title: 'Peringatan', message: 'Lengkapi semua item produk', position: 'topRight' });
                return false;
            }
            if (this.form.jenis_pembayaran === 'transfer' && !this.form.bank_account_id) {
                iziToast.warning({ title: 'Peringatan', message: 'Bank tujuan wajib diisi', position: 'topRight' });
                return false;
            }
            return true;
        },

        async submitNota(mode) {
            if (!this.validate()) return;
            this.saving = true;
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const payload = {
                    ...this.form,
                    diskon: this.form.diskon_value,
                    diskon_value: this.form.diskon_value,
                    pajak: this.form.pajak,
                    items: this.form.items.map(i => ({
                        ...i,
                        qty: parseFloat(i.qty) || 0,
                        harga_jual: this.parseMoney(i.harga_jual),
                    })),
                };
                const res = await axios.post('/peace_seafood/api/penjualan', payload, { headers });
                const id = res.data?.data?.id;
                if (mode === 'final' && id) {
                    await axios.post('/peace_seafood/api/penjualan/' + id + '/finalize', {}, { headers });
                }

                // Get detail nota for WhatsApp share & immediate print
                const detailRes = await axios.get('/peace_seafood/api/penjualan/' + id, { headers });
                const detail = detailRes.data?.data;

                // Construct WhatsApp Link
                const phone = detail.telepon_pembeli ? detail.telepon_pembeli.replace(/[^0-9]/g, '') : '';
                let formattedPhone = phone;
                if (formattedPhone.startsWith('0')) {
                    formattedPhone = '62' + formattedPhone.slice(1);
                }
                const companyName = detail.nama_gudang || 'Peace Seafood';
                let msg = `Halo *${detail.nama_pembeli || 'Umum'}*,\n\n`;
                msg += `Berikut adalah nota pembelian Anda dari *${companyName}*:\n\n`;
                msg += `📄 *No Nota:* ${detail.no_nota}\n`;
                msg += `📅 *Tanggal:* ${new Date(detail.tanggal_nota).toLocaleDateString('id-ID')}\n\n`;
                msg += `*Rincian Belanja:*\n`;
                if (detail.items && detail.items.length > 0) {
                    detail.items.forEach(item => {
                        let q = parseFloat(item.qty || 0);
                        let wStr = this.formatQty(q, item.satuan);
                        msg += `- *${item.nama_produk}*: ${wStr} x ${this.formatRupiah(item.harga_jual)} = ${this.formatRupiah(item.subtotal)}\n`;
                    });
                }
                msg += `\n`;
                msg += `💵 *Subtotal:* ${this.formatRupiah(detail.subtotal)}\n`;
                if (parseFloat(detail.diskon_nominal || 0) > 0) {
                    msg += `✂️ *Diskon:* -${this.formatRupiah(detail.diskon_nominal)}\n`;
                }
                if (parseFloat(detail.pajak || 0) > 0) {
                    msg += `➕ *Pajak:* +${this.formatRupiah(detail.pajak)}\n`;
                }
                msg += `💰 *TOTAL TAGIHAN:* *${this.formatRupiah(detail.total)}*\n\n`;
                msg += `💳 *Metode Bayar:* ${(detail.pembayaran || '').toUpperCase()}\n\n`;
                msg += `Terima kasih atas kepercayaan Anda kepada kami! 🐟✨`;
                const waLink = 'https://wa.me/' + formattedPhone + '?text=' + encodeURIComponent(msg);

                // Show rich checkout success pop-up
                Swal.fire({
                    icon: 'success',
                    title: 'Transaksi Berhasil!',
                    text: 'Nota penjualan telah sukses disimpan.',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: '<i class="lucide-message-circle" style="display:inline-block;width:14px;height:14px;vertical-align:middle;margin-right:5px;"></i> Kirim WA',
                    denyButtonText: '<i class="lucide-printer" style="display:inline-block;width:14px;height:14px;vertical-align:middle;margin-right:5px;"></i> Cetak Struk',
                    cancelButtonText: 'Tutup & Kembali',
                    customClass: {
                        popup: 'swal2-glassmorphic',
                        confirmButton: 'swal2-confirm-btn',
                        denyButton: 'swal2-confirm-btn bg-blue-600 ml-2',
                        cancelButton: 'swal2-cancel-btn'
                    },
                    buttonsStyling: false,
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(waLink, '_blank');
                        window.location.href = '/peace_seafood/penjualan';
                    } else if (result.isDenied) {
                        // Redirect to sales page with auto print query parameter
                        window.location.href = '/peace_seafood/penjualan?highlight=nota-' + id + '&print=true';
                    } else {
                        window.location.href = '/peace_seafood/penjualan';
                    }
                });
            } catch(e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal simpan nota', position: 'topRight' });
            } finally { this.saving = false; }
        }
    };
}
