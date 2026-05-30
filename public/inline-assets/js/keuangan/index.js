/* extracted from keuangan_index.scripts.1.js */
// extracted from src/views/keuangan/index.php
function keuanganPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        loading: true,
        list: [],
        summary: { total_hutang: 0, total_piutang: 0, overdue_count: 0 },
        activeTab: 'semua',
        showBayarModal: false,
        showCreateModal: false,
        selectedHp: null,
        bayarForm: { id_hutang_piutang: '', nominal_bayar: '', tanggal_bayar: new Date().toISOString().split('T')[0], catatan: '' },

        get filteredList() {
            return this.list.filter(h => this.activeTab === 'semua' || h.jenis === this.activeTab);
        },

        async init() {
            if (!['super_admin', 'bos', 'admin'].includes(this.user.role)) {
                window.location.href = '/peace_seafood/dashboard';
                return;
            }
            await this.loadData();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadData() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const [listRes, agingRes] = await Promise.all([
                    axios.get('/peace_seafood/api/keuangan/hutang-piutang', { headers }),
                    axios.get('/peace_seafood/api/laporan/keuangan', { headers }),
                ]);
                this.list = listRes.data?.data || [];
                const keuData = agingRes.data?.data || {};
                this.summary = {
                    total_hutang:  parseFloat(keuData.total_hutang  || 0),
                    total_piutang: parseFloat(keuData.total_piutang || 0),
                    overdue_count: parseInt(keuData.overdue_count   || 0),
                };
            } catch(e) { console.error(e); }
            this.loading = false;
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

        openBayar(hp) {
            this.selectedHp = hp;
            this.bayarForm = { id_hutang_piutang: hp.id, nominal_bayar: '', tanggal_bayar: new Date().toISOString().split('T')[0], catatan: '' };
            this.showBayarModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async submitBayar() {
            if (!this.bayarForm.nominal_bayar) return;
            const parsedNominal = this.parseMoney(this.bayarForm.nominal_bayar);
            if (parsedNominal <= 0) {
                iziToast.warning({ title: 'Peringatan', message: 'Nominal bayar tidak valid', position: 'topRight' });
                return;
            }
            const sisaHutang = parseFloat(this.selectedHp?.sisa_hutang || 0);
            if (parsedNominal > sisaHutang) {
                iziToast.warning({ title: 'Peringatan', message: 'Nominal bayar tidak boleh melebihi sisa utang (' + this.formatMoney(sisaHutang) + ')', position: 'topRight' });
                return;
            }
            try {
                const token = localStorage.getItem('token');
                const payload = {
                    ...this.bayarForm,
                    nominal_bayar: parsedNominal
                };
                await axios.post('/peace_seafood/api/keuangan/bayar', payload, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Pembayaran tersimpan!', position: 'topRight' });
                this.showBayarModal = false;
                await this.loadData();
            } catch(e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' });
            }
        }
    };
}
