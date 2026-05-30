/* extracted from penitipan_index.scripts.1.js */
// extracted from src/views/penitipan/index.php
function penitipanPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        loading: true,
        list: [],
        search: '',
        filterStatus: '',
        showDetailModal: false,
        detail: null,
        showJual: false,
        selectedTitipan: null,
        submitting: false,
        jualForm: { titipan_id: null, jumlah_terjual: '', harga_jual: '', tanggal: new Date().toISOString().slice(0,10), penjual: 'gudang_penerima' },

        get filtered() {
            const q = this.search.toLowerCase();
            return this.list.filter(t =>
                (!q || (t.nama_supplier||'').toLowerCase().includes(q) ||
                       (t.nama_pembeli||'').toLowerCase().includes(q) ||
                       (t.nama_produk||'').toLowerCase().includes(q)) &&
                (!this.filterStatus || t.status === this.filterStatus)
            );
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
                const res = await axios.get('/peace_seafood/api/penitipan', { headers: { Authorization: 'Bearer '+token } });
                this.list = res.data?.data || [];
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal memuat data', position: 'topRight' }); }
            this.loading = false;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async openDetail(id) {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/penitipan/' + id, { headers: { Authorization: 'Bearer '+token } });
                this.detail = res.data?.data;
                this.showDetailModal = true;
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal memuat detail', position: 'topRight' }); }
        },

        openJual(titipan) {
            this.selectedTitipan = titipan;
            this.jualForm = { titipan_id: titipan.id, jumlah_terjual: '', harga_jual: titipan.harga_titip||'', tanggal: new Date().toISOString().slice(0,10), penjual: 'gudang_penerima' };
            this.showJual = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async submitJual() {
            this.submitting = true;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/penitipan/' + this.jualForm.titipan_id + '/jual', this.jualForm, { headers: { Authorization: 'Bearer '+token } });
                iziToast.success({ title: 'Berhasil', message: 'Penjualan titipan dicatat!', position: 'topRight' });
                this.showJual = false;
                await this.loadData();
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
            this.submitting = false;
        },

        async doSettlement(id) {
            if (!await confirm('Lakukan settlement untuk titipan ini?')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/penitipan/' + id + '/selesai', { titipan_id: id }, { headers: { Authorization: 'Bearer '+token } });
                iziToast.success({ title: 'Berhasil', message: 'Settlement selesai!', position: 'topRight' });
                await this.loadData();
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal', position: 'topRight' }); }
        },

        statusLabel(s) {
            return { masuk:'Masuk', dijual_sebagian:'Dijual Sebagian', dijual_semua:'Dijual Semua', selesai:'Selesai' }[s] || s;
        },

        formatDate(d) { return d ? new Date(d).toLocaleDateString('id-ID') : '-'; }
    };
}
