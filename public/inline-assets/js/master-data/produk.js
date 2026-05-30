/* extracted from master-data_produk.scripts.1.js */
// extracted from src/views/master-data/produk.php
function produkPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        items: [], jenisIkan: [], search: '', showModal: false, editId: null, saving: false,
        form: { nama: '', id_jenis_ikan: '', harga_beli: '', harga_jual: '', stok_minimum: '', satuan: 'kg' },
        get filtered() { const q = this.search.toLowerCase(); return this.items.filter(p => !q || p.nama?.toLowerCase().includes(q) || p.nama_jenis?.toLowerCase().includes(q)); },
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
        formatRupiahInput(value) {
            const numeric = String(value ?? '').replace(/\D/g, '');
            if (!numeric) return '';
            return new Intl.NumberFormat('id-ID').format(Number(numeric));
        },
        handleRupiahInput(field, event) {
            const formatted = this.formatRupiahInput(event.target.value);
            this.form[field] = formatted;
            event.target.value = formatted;
        },
        normalizeRupiah(value) {
            const numeric = String(value ?? '').replace(/\D/g, '');
            return numeric ? Number(numeric) : 0;
        },
        async init() {
            const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
            const [prodRes, jenisRes] = await Promise.all([
                axios.get('/peace_seafood/api/master/produk', { headers }),
                axios.get('/peace_seafood/api/master/jenis-ikan', { headers }),
            ]);
            this.items = prodRes.data?.data || []; this.jenisIkan = jenisRes.data?.data || [];
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },
        openAdd() { this.editId = null; this.form = { nama: '', id_jenis_ikan: '', harga_beli: '', harga_jual: '', stok_minimum: '', satuan: 'kg' }; this.showModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        openEdit(p) { this.editId = p.id; this.form = { nama: p.nama, id_jenis_ikan: p.id_jenis_ikan, harga_beli: this.formatRupiahInput(p.harga_beli), harga_jual: this.formatRupiahInput(p.harga_jual), stok_minimum: p.stok_minimum ?? '', satuan: 'kg' }; this.showModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        async save() {
            if (!this.form.nama || !this.form.id_jenis_ikan) return; this.saving = true;
            try {
                const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
                const payload = {
                    ...this.form,
                    harga_beli: this.normalizeRupiah(this.form.harga_beli),
                    harga_jual: this.normalizeRupiah(this.form.harga_jual),
                    satuan: 'kg',
                };
                if (this.editId) { await axios.put('/peace_seafood/api/master/produk/' + this.editId, payload, { headers }); }
                else { await axios.post('/peace_seafood/api/master/produk', payload, { headers }); }
                iziToast.success({ title: 'Berhasil', message: 'Data tersimpan', position: 'topRight' }); this.showModal = false;
                const res = await axios.get('/peace_seafood/api/master/produk', { headers });
                this.items = res.data?.data || [];
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
            this.saving = false;
        }
    };
}
