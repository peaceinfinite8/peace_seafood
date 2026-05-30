/* extracted from penjualan_index.scripts.1.js */
// extracted from src/views/penjualan/index.php
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
