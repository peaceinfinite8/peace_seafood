/* extracted from checker_draft-penjualan.scripts.1.js */
// extracted from src/views/checker/draft-penjualan.php
function checkerDraftPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        step: 1,
        loadingProduk: true,
        sending: false,
        produkList: [],
        filteredProduk: [],
        search: '',
        selectedItems: [],
        namaPembeli: '',
        catatan: '',
        sentNoNota: '',

        get totalEstimasi() {
            return this.selectedItems.reduce((s, i) => s + (parseFloat(i.subtotal) || 0), 0);
        },
        get isValid() {
            return this.selectedItems.length > 0 &&
                   this.selectedItems.every(i => parseFloat(i.qty) > 0);
        },

        async init() {
            if (this.user.role !== 'checker') {
                window.location.href = '/peace_seafood/dashboard';
                return;
            }
            await this.loadProduk();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadProduk() {
            this.loadingProduk = true;
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/master/produk', {
                    headers: { Authorization: 'Bearer ' + token }
                });
                this.produkList = (res.data?.data || []).filter(p => p.is_active && parseFloat(p.stok_qty || 0) > 0);
                this.filteredProduk = [...this.produkList];
            } catch (e) {
                iziToast.error({ title: 'Error', message: 'Gagal memuat daftar produk', position: 'topRight' });
            }
            this.loadingProduk = false;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        filterProduk() {
            const q = this.search.toLowerCase().trim();
            this.filteredProduk = q
                ? this.produkList.filter(p => p.nama.toLowerCase().includes(q) || (p.nama_jenis || '').toLowerCase().includes(q))
                : [...this.produkList];
        },

        selectProduk(p) {
            const existing = this.selectedItems.find(i => i.id_produk == p.id);
            if (existing) {
                // Toggle off
                this.selectedItems = this.selectedItems.filter(i => i.id_produk != p.id);
            } else {
                this.selectedItems.push({
                    id_produk:     p.id,
                    nama:          p.nama,
                    stok_qty:      p.stok_qty,
                    stok_minimum:  p.stok_minimum,
                    harga_jual:    parseFloat(p.harga_jual || 0),
                    harga_jual_fmt: parseFloat(p.harga_jual || 0).toLocaleString('id-ID'),
                    qty:           '',
                    subtotal:      0,
                });
            }
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        removeItem(idx) {
            this.selectedItems.splice(idx, 1);
            if (this.selectedItems.length === 0) this.step = 1;
        },

        calcItem(idx) {
            const item = this.selectedItems[idx];
            const qty  = parseFloat(item.qty) || 0;
            const hrg  = parseFloat(item.harga_jual) || 0;
            item.subtotal = Math.round(qty * hrg);
        },

        onHargaInput(idx, event) {
            const raw = event.target.value.replace(/\D/g, '');
            const num = parseInt(raw) || 0;
            this.selectedItems[idx].harga_jual     = num;
            this.selectedItems[idx].harga_jual_fmt = num.toLocaleString('id-ID');
            event.target.value = this.selectedItems[idx].harga_jual_fmt;
            this.calcItem(idx);
        },

        async kirimDraft() {
            if (!this.isValid) return;
            this.sending = true;
            try {
                const token = localStorage.getItem('token');
                const payload = {
                    id_pembeli:       this.namaPembeli || '',
                    jenis_pembayaran: 'cash',
                    catatan:          this.catatan || '',
                    items: this.selectedItems.map(i => ({
                        id_produk:  i.id_produk,
                        qty:        parseFloat(i.qty),
                        harga_jual: parseFloat(i.harga_jual),
                    })),
                };
                const res = await axios.post('/peace_seafood/api/penjualan/draft', payload, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                this.sentNoNota = res.data?.data?.no_nota || '—';
                this.step = 4;
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            } catch (e) {
                iziToast.error({
                    title: 'Gagal Mengirim',
                    message: e.response?.data?.message || 'Terjadi kesalahan. Coba lagi.',
                    position: 'topRight'
                });
            }
            this.sending = false;
        },

        resetForm() {
            this.step = 1;
            this.selectedItems = [];
            this.namaPembeli = '';
            this.catatan = '';
            this.sentNoNota = '';
            this.search = '';
            this.filteredProduk = [...this.produkList];
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },
    };
}
