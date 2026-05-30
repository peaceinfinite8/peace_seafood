/* extracted from master-data_jenis-ikan.scripts.1.js */
// extracted from src/views/master-data/jenis-ikan.php
function jenisIkanPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        loading: true,
        list: [],
        filtered: [],
        search: '',
        showModal: false,
        editMode: false,
        submitting: false,
        editId: null,
        form: { nama: '', deskripsi: '' },

        async init() {
            await this.loadData();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadData() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/master/jenis-ikan', { headers: { Authorization: 'Bearer '+token } });
                this.list = res.data?.data || [];
                this.filterList();
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal memuat data', position: 'topRight' }); }
            this.loading = false;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        filterList() {
            const q = this.search.toLowerCase();
            this.filtered = q ? this.list.filter(j => (j.nama||'').toLowerCase().includes(q)) : [...this.list];
        },

        openAdd() {
            this.editMode = false;
            this.editId = null;
            this.form = { nama: '', deskripsi: '', allowed_sizes: '', allowed_grades: '', allowed_origins: '' };
            this.showModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        openEdit(jenis) {
            this.editMode = true;
            this.editId = jenis.id;
            this.form = { 
                nama: jenis.nama, 
                deskripsi: jenis.deskripsi || '', 
                allowed_sizes: jenis.allowed_sizes || '', 
                allowed_grades: jenis.allowed_grades || '', 
                allowed_origins: jenis.allowed_origins || '' 
            };
            this.showModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async save() {
            if (!this.form.nama.trim()) {
                iziToast.warning({ title: 'Perhatian', message: 'Nama jenis ikan wajib diisi', position: 'topRight' });
                return;
            }
            this.submitting = true;
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer '+token };
                if (this.editMode) {
                    await axios.put('/peace_seafood/api/master/jenis-ikan/' + this.editId, this.form, { headers });
                    iziToast.success({ title: 'Berhasil', message: 'Jenis ikan diperbarui!', position: 'topRight' });
                } else {
                    await axios.post('/peace_seafood/api/master/jenis-ikan', this.form, { headers });
                    iziToast.success({ title: 'Berhasil', message: 'Jenis ikan ditambahkan!', position: 'topRight' });
                }
                this.showModal = false;
                await this.loadData();
            } catch(e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal menyimpan', position: 'topRight' });
            }
            this.submitting = false;
        },

        async deleteJenis(id) {
            if (!await confirm('Hapus jenis ikan ini? Data yang terkait mungkin terpengaruh.')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.delete('/peace_seafood/api/master/jenis-ikan/' + id, { headers: { Authorization: 'Bearer '+token } });
                iziToast.success({ title: 'Berhasil', message: 'Jenis ikan dihapus', position: 'topRight' });
                await this.loadData();
            } catch(e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal menghapus', position: 'topRight' });
            }
        }
    };
}
