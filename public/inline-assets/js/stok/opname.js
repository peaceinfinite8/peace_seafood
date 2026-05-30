/* extracted from stok_opname.scripts.1.js */
// extracted from src/views/stok/opname.php
function stokOpnamePage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        warehouses: [],
        products: [],
        sessions: [],
        search: '',
        filterStatus: '',
        selectedGudangId: '',
        selectedGudangName: '',
        showCreateModal: false,
        showDetailModal: false,
        saving: false,
        detail: null,
        form: { id_gudang: '', items: [{ id_produk: '', qty_fisik: '' }] },

        get filtered() {
            const q = this.search.toLowerCase();
            return this.sessions.filter(row => {
                const matchStatus = !this.filterStatus || row.status === this.filterStatus;
                const matchSearch = !q || [row.nama_gudang, row.nama_user, row.status, row.tanggal_opname].some(v => String(v || '').toLowerCase().includes(q));
                return matchStatus && matchSearch;
            });
        },

        canSelectGudang() {
            return ['super_admin', 'bos'].includes(this.user.role);
        },

        async init() {
            await this.loadWarehouses();
            this.resolveGudang();
            await this.loadProducts();
            await this.loadList();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadWarehouses() {
            const token = localStorage.getItem('token');
            const res = await axios.get('/peace_seafood/api/settings/gudang', { headers: { Authorization: 'Bearer ' + token } });
            this.warehouses = res.data?.data || [];
        },

        resolveGudang() {
            const userGudang = this.user.id_gudang || '';
            const firstGudang = this.warehouses[0]?.id || '';
            this.selectedGudangId = this.canSelectGudang() ? (userGudang || firstGudang || '') : userGudang;
            this.selectedGudangName = this.warehouses.find(g => String(g.id) === String(this.selectedGudangId))?.nama || this.user.nama_gudang || 'All Gudang';
            if (!this.selectedGudangId && this.warehouses.length > 0) {
                this.selectedGudangId = this.warehouses[0].id;
            }
        },

        async loadProducts() {
            if (!this.selectedGudangId) {
                this.products = [];
                return;
            }
            const token = localStorage.getItem('token');
            const res = await axios.get('/peace_seafood/api/master/produk?id_gudang=' + this.selectedGudangId, { headers: { Authorization: 'Bearer ' + token } });
            this.products = res.data?.data || [];
            this.form.id_gudang = this.selectedGudangId;
        },

        async onGudangChange() {
            this.selectedGudangId = this.form.id_gudang;
            this.selectedGudangName = this.warehouses.find(g => String(g.id) === String(this.selectedGudangId))?.nama || '-';
            await this.loadProducts();
            this.form.items = [{ id_produk: '', qty_fisik: '' }];
        },

        async loadList() {
            const token = localStorage.getItem('token');
            const headers = { Authorization: 'Bearer ' + token };
            const url = this.canSelectGudang() && this.selectedGudangId ? '/peace_seafood/api/stok-opname?id_gudang=' + this.selectedGudangId : '/peace_seafood/api/stok-opname';
            const res = await axios.get(url, { headers });
            this.sessions = res.data?.data || [];
        },

        openCreate() {
            this.form = { id_gudang: this.selectedGudangId, items: [{ id_produk: '', qty_fisik: '' }] };
            this.showCreateModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        addItem() {
            this.form.items.push({ id_produk: '', qty_fisik: '' });
        },

        removeItem(index) {
            this.form.items.splice(index, 1);
            if (this.form.items.length === 0) this.addItem();
        },

        formatDate(value) {
            if (!value) return '-';
            return new Date(value).toLocaleDateString('id-ID');
        },

        formatNumber(value) {
            const num = parseFloat(value || 0);
            return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },

        async save() {
            const items = this.form.items.filter(item => item.id_produk && item.qty_fisik !== '');
            if (!this.form.id_gudang || items.length === 0) {
                iziToast.warning({ title: 'Perhatian', message: 'Gudang dan item opname wajib diisi', position: 'topRight' });
                return;
            }

            this.saving = true;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/stok-opname', {
                    id_gudang: this.form.id_gudang,
                    items: items,
                }, { headers: { Authorization: 'Bearer ' + token } });

                iziToast.success({ title: 'Berhasil', message: 'Sesi opname dibuat', position: 'topRight' });
                this.showCreateModal = false;
                await this.loadList();
            } catch (e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal menyimpan opname', position: 'topRight' });
            }
            this.saving = false;
        },

        async showDetail(id) {
            const token = localStorage.getItem('token');
            const res = await axios.get('/peace_seafood/api/stok-opname/' + id, { headers: { Authorization: 'Bearer ' + token } });
            this.detail = res.data?.data || null;
            this.showDetailModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async finalize(id) {
            if (!await confirm('Finalisasi opname ini? Stok produk akan disesuaikan.')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/stok-opname/' + id + '/finalize', {}, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Opname difinalisasi', position: 'topRight' });
                await this.loadList();
            } catch (e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal finalize opname', position: 'topRight' });
            }
        }
    };
}
