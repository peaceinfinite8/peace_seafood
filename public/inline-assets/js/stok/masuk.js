/* extracted from stok_masuk.scripts.1.js */
// extracted from src/views/stok/masuk.php
function stokMasukPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        form: { id_supplier: '', id_produk: '', qty: '', qty_ton: '', qty_kuintal: '', qty_kg: '', harga_beli: '', catatan: '' },
        errors: {},
        saving: false,
        suppliers: [],
        produk: [],
        supplierQuery: '',

        async init() {
            if (!['super_admin', 'admin'].includes(this.user.role)) {
                window.location.href = '/peace_seafood/dashboard';
                return;
            }
            await this.loadMasterData();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadMasterData() {
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const [supRes, prodRes] = await Promise.all([
                    axios.get('/peace_seafood/api/master/supplier', { headers }),
                    axios.get('/peace_seafood/api/master/produk', { headers }),
                ]);
                this.suppliers = supRes.data?.data || [];
                this.produk    = prodRes.data?.data || [];
            } catch(e) { console.error(e); }
        },

        filteredSuppliers() {
            if (!this.supplierQuery) return this.suppliers;
            const q = this.supplierQuery.toLowerCase();
            return this.suppliers.filter(s => s.nama.toLowerCase().includes(q));
        },

        isExactSupplierMatch() {
            if (!this.supplierQuery) return false;
            const q = this.supplierQuery.trim().toLowerCase();
            return this.suppliers.some(s => s.nama.toLowerCase() === q);
        },

        selectSupplier(s) {
            this.form.id_supplier = s.id;
            this.supplierQuery = s.nama;
            this.errors.id_supplier = '';
        },

        selectNewSupplier(query) {
            this.form.id_supplier = query.trim();
            this.supplierQuery = query.trim();
            this.errors.id_supplier = '';
        },

        onSupplierInput() {
            const trimmed = this.supplierQuery.trim();
            if (!trimmed) {
                this.form.id_supplier = '';
                return;
            }
            const exact = this.suppliers.find(s => s.nama.toLowerCase() === trimmed.toLowerCase());
            if (exact) {
                this.form.id_supplier = exact.id;
            } else {
                this.form.id_supplier = trimmed;
            }
        },

        setProdukInfo() {
            const p = this.produk.find(x => x.id == this.form.id_produk);
            if (p) this.form.harga_beli = p.harga_beli || '';
        },

        updateQtyFromSplit() {
            const ton = parseFloat(this.form.qty_ton) || 0;
            const kuintal = parseFloat(this.form.qty_kuintal) || 0;
            const kg = parseFloat(this.form.qty_kg) || 0;
            this.form.qty = (ton * 1000) + (kuintal * 100) + kg;
            this.calcTotal();
        },

        formatQty(qty) {
            let q = parseFloat(qty) || 0;
            if (q >= 10000) {
                return (q / 1000).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' ton';
            } else if (q >= 100) {
                return (q / 100).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' kintal';
            } else {
                return q.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' kg';
            }
        },

        calcTotal() {
            // Re-evaluates totals reactively
        },

        validate() {
            this.errors = {};
            if (!this.form.id_supplier) this.errors.id_supplier = 'Supplier wajib dipilih atau diisi';
            if (!this.form.id_produk)   this.errors.id_produk   = 'Produk wajib dipilih';
            if (!this.form.qty || parseFloat(this.form.qty) <= 0) this.errors.qty = 'Qty harus lebih dari 0';
            if (!this.form.harga_beli || parseFloat(this.form.harga_beli) <= 0) this.errors.harga_beli = 'Harga beli harus diisi';
            return Object.keys(this.errors).length === 0;
        },

        async submit() {
            if (!this.validate()) return;
            this.saving = true;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/stok/masuk', this.form, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Stok masuk tersimpan! Menunggu timbangan.', position: 'topRight' });
                setTimeout(() => window.location.href = '/peace_seafood/stok', 1000);
            } catch(e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal menyimpan', position: 'topRight' });
            } finally {
                this.saving = false;
            }
        }
    };
}
