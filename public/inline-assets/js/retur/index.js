/* extracted from retur_index.scripts.1.js */
// extracted from src/views/retur/index.php
function returPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        loading: true,
        list: [],
        filterTipe: '',
        filterStatus: '',
        showModal: false,
        detail: null,

        get filtered() {
            return this.list.filter(r =>
                (!this.filterTipe || r.tipe === this.filterTipe) &&
                (!this.filterStatus || r.status === this.filterStatus)
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
                let url = '/peace_seafood/api/retur?per_page=100';
                if (this.filterTipe)   url += '&tipe='   + this.filterTipe;
                if (this.filterStatus) url += '&status=' + this.filterStatus;
                const res = await axios.get(url, { headers: { Authorization: 'Bearer '+token } });
                this.list = res.data?.data || [];
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal memuat data', position: 'topRight' }); }
            this.loading = false;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async openDetail(id) {
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/retur/' + id, { headers: { Authorization: 'Bearer '+token } });
                this.detail = res.data?.data;
                this.showModal = true;
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal memuat detail', position: 'topRight' }); }
        },

        async approve(id) {
            const notes = prompt('Catatan persetujuan (opsional):') ?? '';
            if (notes === null) return;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/retur/' + id + '/approve', { notes }, { headers: { Authorization: 'Bearer '+token } });
                iziToast.success({ title: 'Berhasil', message: 'Retur disetujui!', position: 'topRight' });
                await this.loadData();
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
        },

        async reject(id) {
            const alasan = prompt('Alasan penolakan:');
            if (!alasan) { iziToast.warning({ title: 'Perhatian', message: 'Alasan wajib diisi', position: 'topRight' }); return; }
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/retur/' + id + '/reject', { alasan_reject: alasan }, { headers: { Authorization: 'Bearer '+token } });
                iziToast.success({ title: 'Berhasil', message: 'Retur ditolak', position: 'topRight' });
                await this.loadData();
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal', position: 'topRight' }); }
        },

        alasanLabel(a) {
            const map = {
                barang_rusak: 'Barang Rusak', kadaluarsa: 'Kadaluarsa',
                tidak_sesuai: 'Tidak Sesuai Pesanan', lainnya: 'Lainnya',
                potongan_kualitas: 'Potongan Kualitas', gratis_marketing: 'Gratis/Marketing',
                recall_produk: 'Recall Produk',
            };
            return map[a] || a || '-';
        },

        formatDate(d) { return d ? new Date(d).toLocaleDateString('id-ID') : '-'; }
    };
}
