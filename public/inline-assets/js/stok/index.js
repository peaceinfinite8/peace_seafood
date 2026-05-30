/* extracted from stok_index.scripts.1.js */
// extracted from src/views/stok/index.php
function stokPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        loading: true,
        items: [],
        jenisIkan: [],
        search: '',
        filterJenis: '',
        filterStock: '',
        pendingCount: 0,

        formatWeight(qty, satuan = 'kg') {
            let q = parseFloat(qty || 0);
            if (!satuan || satuan.toLowerCase() === 'kg') {
                if (q >= 10000) {
                    return (q / 1000).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' ton';
                } else if (q >= 100) {
                    return (q / 100).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' kintal';
                } else {
                    // use global helper for consistent kg formatting
                    return formatKg(q, 2);
                }
            }
            return q.toLocaleString('id-ID') + ' ' + satuan;
        },


        get filteredItems() {
            return this.items.filter(i => {
                const q = this.search.toLowerCase();
                const matchSearch = !q || i.nama?.toLowerCase().includes(q) || i.nama_jenis?.toLowerCase().includes(q);
                const matchJenis  = !this.filterJenis || i.id_jenis_ikan == this.filterJenis;
                const matchStock  = !this.filterStock || 
                    (this.filterStock === 'low' && i.is_low_stock) ||
                    (this.filterStock === 'ok'  && !i.is_low_stock);
                return matchSearch && matchJenis && matchStock;
            });
        },

        async init() {
            await this.loadData();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadData() {
            this.loading = true;
            try {
                const token   = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const [stokRes, pendingRes, jenisRes] = await Promise.all([
                    axios.get('/peace_seafood/api/stok', { headers }),
                    axios.get('/peace_seafood/api/stok/pending-timbang', { headers }),
                    axios.get('/peace_seafood/api/master/jenis-ikan', { headers }),
                ]);
                this.items        = stokRes.value?.data?.data || stokRes.data?.data || [];
                this.pendingCount = pendingRes.value?.data?.data?.length || pendingRes.data?.data?.length || 0;
                this.jenisIkan    = jenisRes.value?.data?.data || jenisRes.data?.data || [];
            } catch(e) {
                if (e.response?.status === 401) { localStorage.clear(); window.location.href = '/peace_seafood/login'; }
                console.error(e);
            } finally {
                this.loading = false;
            }
        }
    };
}
