/* extracted from penitipan_create.scripts.1.js */
// extracted from src/views/penitipan/create.php
function penitipanCreate() {
    return {
        submitting: false,
        suppliers: [],
        produkList: [],
        form: {
            pembeli_id: '',
            produk_id: '',
            jumlah: '',
            harga_titip: '',
            komisi_persen: 0,
            tanggal_masuk: new Date().toISOString().slice(0, 10),
            catatan: '',
        },

        async init() {
            await Promise.all([this.loadSuppliers(), this.loadProduk()]);
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadSuppliers() {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/master/supplier', { headers: { Authorization: 'Bearer '+token } });
                this.suppliers = res.data?.data || [];
            } catch(e) { console.error(e); }
        },

        async loadProduk() {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/master/produk', { headers: { Authorization: 'Bearer '+token } });
                this.produkList = res.data?.data || [];
            } catch(e) { console.error(e); }
        },

        async submit() {
            if (!this.form.pembeli_id || !this.form.produk_id || !this.form.jumlah || !this.form.harga_titip) {
                iziToast.warning({ title: 'Perhatian', message: 'Lengkapi semua field wajib', position: 'topRight' });
                return;
            }
            this.submitting = true;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/penitipan', this.form, { headers: { Authorization: 'Bearer '+token } });
                iziToast.success({ title: 'Berhasil', message: 'Titipan berhasil disimpan!', position: 'topRight' });
                setTimeout(() => { window.location.href = '/peace_seafood/penitipan'; }, 1000);
            } catch(e) {
                const msg = e.response?.data?.message || 'Gagal menyimpan';
                iziToast.error({ title: 'Error', message: msg, position: 'topRight' });
            }
            this.submitting = false;
        }
    };
}
