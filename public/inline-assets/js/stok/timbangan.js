/* extracted from stok_timbangan.scripts.1.js */
// extracted from src/views/stok/timbangan.php
function timbanganPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        pendingList: [],
        selectedId: null,
        selectedItem: null,
        form: { qty_actual: '', alasan_susut: '' },
        saving: false,

        get susut() {
            if (!this.selectedItem || !this.form.qty_actual) return 0;
            return parseFloat(this.selectedItem.qty) - parseFloat(this.form.qty_actual);
        },
        get susutPersen() {
            if (!this.selectedItem || !this.form.qty_actual || !parseFloat(this.selectedItem.qty)) return 0;
            return (this.susut / parseFloat(this.selectedItem.qty)) * 100;
        },

        async init() {
            if (!['super_admin', 'admin', 'checker'].includes(this.user.role)) {
                window.location.href = '/peace_seafood/dashboard';
                return;
            }
            await this.loadPending();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadPending() {
            const token = localStorage.getItem('token');
            const res = await axios.get('/peace_seafood/api/stok/pending-timbang', { headers: { Authorization: 'Bearer ' + token } });
            this.pendingList = res.data?.data || [];
        },

        selectItem(item) {
            this.selectedId = item.id;
            this.selectedItem = item;
            this.form = { qty_actual: item.qty, alasan_susut: '' };
        },

        calcSusut() {},

        async submitTimbangan() {
            if (!this.form.qty_actual) { iziToast.warning({ title: 'Peringatan', message: 'Qty actual wajib diisi', position: 'topRight' }); return; }
            this.saving = true;
            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/stok/timbang', {
                    id_stok_masuk: this.selectedId, ...this.form,
                }, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Timbangan dikonfirmasi! Stok diupdate.', position: 'topRight' });
                this.selectedId = null; this.selectedItem = null; this.form = { qty_actual: '', alasan_susut: '' };
                await this.loadPending();
            } catch(e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' });
            } finally { this.saving = false; }
        }
    };
}
