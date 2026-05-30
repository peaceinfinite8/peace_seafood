/* extracted from master-data_index.scripts.1.js */
// extracted from src/views/master-data/index.php
function masterDataPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        counts: { supplier: 0, pembeli: 0, jenis: 0, produk: 0 },

        async init() {
            if (!['super_admin','admin'].includes(this.user.role)) {
                window.location.href = '/peace_seafood/dashboard';
                return;
            }
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            await this.loadCounts();
        },

        async loadCounts() {
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const [supRes, pemRes, jenisRes, prodRes] = await Promise.all([
                    axios.get('/peace_seafood/api/master/supplier', { headers }),
                    axios.get('/peace_seafood/api/master/pembeli', { headers }),
                    axios.get('/peace_seafood/api/master/jenis-ikan', { headers }),
                    axios.get('/peace_seafood/api/master/produk', { headers }),
                ]);
                this.counts.supplier = (supRes.data?.data || []).length;
                this.counts.pembeli  = (pemRes.data?.data || []).length;
                this.counts.jenis    = (jenisRes.data?.data || []).length;
                this.counts.produk   = (prodRes.data?.data || []).length;
            } catch(e) { console.error(e); }
        }
    };
}
