/* extracted from master-data_pembeli.scripts.1.js */
// extracted from src/views/master-data/pembeli.php
function pembeliPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        items: [], search: '', showModal: false, editId: null, saving: false,
        form: { nama: '', telepon: '', alamat: '', tipe: 'retail' },
        get filtered() { const q = this.search.toLowerCase(); return this.items.filter(s => !q || s.nama?.toLowerCase().includes(q)); },
        async init() { await this.load(); this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        async load() { const token = localStorage.getItem('token'); const res = await axios.get('/peace_seafood/api/master/pembeli', { headers: { Authorization: 'Bearer ' + token } }); this.items = res.data?.data || []; },
        openAdd() { this.editId = null; this.form = { nama: '', telepon: '', alamat: '', tipe: 'retail' }; this.showModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        openEdit(s) { this.editId = s.id; this.form = { nama: s.nama, telepon: s.telpon||'', alamat: s.alamat||'', tipe: s.tipe||'retail' }; this.showModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        async save() {
            if (!this.form.nama) return; this.saving = true;
            try {
                const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
                if (this.editId) { await axios.put('/peace_seafood/api/master/pembeli/' + this.editId, this.form, { headers }); }
                else { await axios.post('/peace_seafood/api/master/pembeli', this.form, { headers }); }
                iziToast.success({ title: 'Berhasil', message: 'Data tersimpan', position: 'topRight' }); this.showModal = false; await this.load();
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
            this.saving = false;
        },
        async deleteItem(id) {
            if (!await confirm('Hapus pembeli ini?')) return;
            const token = localStorage.getItem('token');
            await axios.delete('/peace_seafood/api/master/pembeli/' + id, { headers: { Authorization: 'Bearer ' + token } });
            iziToast.success({ title: 'Berhasil', message: 'Pembeli dihapus', position: 'topRight' }); await this.load();
        }
    };
}
