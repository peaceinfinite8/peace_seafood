/* extracted from stok_history.scripts.1.js */
// extracted from src/views/stok/history.php
function stokHistory() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        loading: true,
        list: [],
        search: '',
        filterDari: '',
        filterSampai: '',
        filterStatus: '',
        showModal: false,
        selected: null,

        get filtered() {
            const q = this.search.toLowerCase();
            return this.list.filter(i =>
                (!q || (i.nama_produk||'').toLowerCase().includes(q) ||
                       (i.nama_supplier||'').toLowerCase().includes(q)) &&
                (!this.filterStatus || i.status === this.filterStatus)
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
                let url = '/peace_seafood/api/stok/history?per_page=200';
                if (this.filterDari)   url += '&dari='    + this.filterDari;
                if (this.filterSampai) url += '&sampai='  + this.filterSampai;
                const res = await axios.get(url, { headers: { Authorization: 'Bearer '+token } });
                this.list = res.data?.data || [];
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal memuat history', position: 'topRight' }); }
            this.loading = false;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        openDetail(item) {
            this.selected = item;
            this.showModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        formatDate(d) { return d ? new Date(d).toLocaleDateString('id-ID') : '-'; }
    };
}
