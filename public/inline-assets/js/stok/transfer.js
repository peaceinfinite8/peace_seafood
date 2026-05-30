/* extracted from stok_transfer.scripts.1.js */
// extracted from src/views/stok/transfer.php
function stokTransferPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        warehouses: [],
        products: [],
        transfers: [],
        search: '',
        filterStatus: '',
        selectedSourceId: '',
        selectedTransfer: null,
        showCreateModal: false,
        showDetailModal: false,
        saving: false,
        form: { gudang_asal_id: '', gudang_tujuan_id: '', id_produk: '', qty: '1' },

        get filtered() {
            const q = this.search.toLowerCase();
            return this.transfers.filter(row => {
                const matchStatus = !this.filterStatus || row.status === this.filterStatus;
                const matchSearch = !q || [row.nama_gudang_asal, row.nama_gudang_tujuan, row.nama_produk, row.nama_jenis, row.status].some(v => String(v || '').toLowerCase().includes(q));
                return matchStatus && matchSearch;
            });
        },

        canSelectSource() {
            return ['super_admin', 'bos'].includes(this.user.role);
        },

        async init() {
            await this.loadWarehouses();
            this.resolveSource();
            await this.loadProducts();
            await this.loadList();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadWarehouses() {
            const token = localStorage.getItem('token');
            const res = await axios.get('/peace_seafood/api/settings/gudang', { headers: { Authorization: 'Bearer ' + token } });
            this.warehouses = res.data?.data || [];
        },

        resolveSource() {
            const userSource = this.user.id_gudang || '';
            const firstGudang = this.warehouses[0]?.id || '';
            this.selectedSourceId = this.canSelectSource() ? (userSource || firstGudang || '') : userSource;
            this.form.gudang_asal_id = this.selectedSourceId;
        },

        async loadProducts() {
            if (!this.selectedSourceId) {
                this.products = [];
                return;
            }
            const token = localStorage.getItem('token');
            const res = await axios.get('/peace_seafood/api/master/produk?id_gudang=' + this.selectedSourceId, { headers: { Authorization: 'Bearer ' + token } });
            this.products = res.data?.data || [];
            this.form.id_produk = '';
        },

        async onSourceChange() {
            this.selectedSourceId = this.form.gudang_asal_id;
            await this.loadProducts();
        },

        async loadList() {
            const token = localStorage.getItem('token');
            const headers = { Authorization: 'Bearer ' + token };
            const url = this.canSelectSource() && this.selectedSourceId ? '/peace_seafood/api/stok-transfer?id_gudang=' + this.selectedSourceId : '/peace_seafood/api/stok-transfer';
            const res = await axios.get(url, { headers });
            this.transfers = res.data?.data || [];
        },

        openCreate() {
            this.form = { gudang_asal_id: this.selectedSourceId, gudang_tujuan_id: '', id_produk: '', qty: '1' };
            this.showCreateModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        formatNumber(value) {
            const num = parseFloat(value || 0);
            return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },

        async save() {
            if (!this.form.gudang_tujuan_id || !this.form.id_produk || !this.form.qty) {
                iziToast.warning({ title: 'Perhatian', message: 'Gudang tujuan, produk, dan qty wajib diisi', position: 'topRight' });
                return;
            }
            this.saving = true;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/stok-transfer', this.form, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Transfer stok dibuat', position: 'topRight' });
                this.showCreateModal = false;
                await this.loadList();
            } catch (e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal menyimpan transfer', position: 'topRight' });
            }
            this.saving = false;
        },

        showDetail(item) {
            this.selectedTransfer = item;
            this.showDetailModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async updateStatus(id, status) {
            if (!await confirm('Ubah status transfer menjadi ' + status.toUpperCase() + '?')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.put('/peace_seafood/api/stok-transfer/' + id + '/status', { status }, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Status transfer diperbarui', position: 'topRight' });
                await this.loadList();
            } catch (e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal mengubah status', position: 'topRight' });
            }
        }
    };
}
