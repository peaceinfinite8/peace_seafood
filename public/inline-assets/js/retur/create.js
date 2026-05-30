/* extracted from retur_create.scripts.1.js */
// extracted from src/views/retur/create.php
function returCreate() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        submitting: false,
        produkList: [],
        suppliers: [],
        pembeli: [],
        selectedProduk: null,
        form: {
            tipe: 'stok',
            id_produk: '',
            qty: '',
            id_supplier: '',
            id_pembeli: '',
            pihak: '',
            nominal: '',
            alasan: '',
            keterangan: '',
        },

        async init() {
            await Promise.all([this.loadProduk(), this.loadSuppliers(), this.loadPembeli()]);
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadProduk() {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/master/produk', { headers: { Authorization: 'Bearer '+token } });
                this.produkList = res.data?.data || [];
            } catch(e) {}
        },

        async loadSuppliers() {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/master/supplier', { headers: { Authorization: 'Bearer '+token } });
                this.suppliers = res.data?.data || [];
            } catch(e) {}
        },

        async loadPembeli() {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/master/pembeli', { headers: { Authorization: 'Bearer '+token } });
                this.pembeli = res.data?.data || [];
            } catch(e) {}
        },

        onProdukChange() {
            this.selectedProduk = this.produkList.find(p => p.id == this.form.id_produk) || null;
        },

        async submit() {
            // Validate
            if (!this.form.alasan) {
                iziToast.warning({ title: 'Perhatian', message: 'Alasan retur wajib diisi', position: 'topRight' });
                return;
            }
            if (this.form.tipe === 'stok' && (!this.form.id_produk || !this.form.qty)) {
                iziToast.warning({ title: 'Perhatian', message: 'Produk dan qty wajib diisi', position: 'topRight' });
                return;
            }
            if (this.form.tipe === 'piutang' && !this.form.nominal) {
                iziToast.warning({ title: 'Perhatian', message: 'Nominal wajib diisi', position: 'topRight' });
                return;
            }

            this.submitting = true;
            try {
                const token = localStorage.getItem('token');
                const payload = { ...this.form };
                // Clean unused fields
                if (this.form.tipe === 'stok') {
                    delete payload.id_supplier; delete payload.id_pembeli; delete payload.nominal; delete payload.pihak;
                } else {
                    delete payload.id_produk; delete payload.qty;
                    if (this.form.pihak === 'supplier') delete payload.id_pembeli;
                    else delete payload.id_supplier;
                }
                await axios.post('/peace_seafood/api/retur', payload, { headers: { Authorization: 'Bearer '+token } });
                iziToast.success({ title: 'Berhasil', message: 'Retur berhasil disubmit, menunggu persetujuan Bos', position: 'topRight' });
                setTimeout(() => { window.location.href = '/peace_seafood/retur'; }, 1200);
            } catch(e) {
                const msg = e.response?.data?.message || 'Gagal menyimpan retur';
                iziToast.error({ title: 'Error', message: msg, position: 'topRight' });
            }
            this.submitting = false;
        }
    };
}
