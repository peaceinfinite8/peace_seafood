/* extracted from laporan_index.scripts.1.js */
// extracted from src/views/laporan/index.php
function laporanPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        loading: false,
        filters: {
            dari: new Date(new Date().setDate(1)).toISOString().split('T')[0],
            sampai: new Date().toISOString().split('T')[0],
        },
        activeTab: 'penjualan',
        stokData: [],
        penjualanData: [],
        keuanganData: {},
        agingData: [],

        async init() {
            if (!['super_admin', 'bos', 'admin'].includes(this.user.role)) {
                window.location.href = '/peace_seafood/dashboard';
                return;
            }
            await this.loadData();
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
                this.initDatePickers();
            });
        },

        initDatePickers() {
            if (!window.flatpickr) return;

            const locale = {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    longhand:  ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],
                },
                months: {
                    shorthand: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                    longhand:  ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
                },
            };

            const opts = {
                locale,
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true,
            };

            flatpickr('#laporan-dari', {
                ...opts,
                defaultDate: this.filters.dari,
                onChange: ([d]) => { this.filters.dari = d ? flatpickr.formatDate(d, 'Y-m-d') : ''; },
            });

            flatpickr('#laporan-sampai', {
                ...opts,
                defaultDate: this.filters.sampai,
                onChange: ([d]) => { this.filters.sampai = d ? flatpickr.formatDate(d, 'Y-m-d') : ''; },
            });
        },

        async loadData() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                const q = `?dari=${this.filters.dari}&sampai=${this.filters.sampai}`;
                const [stokRes, penjRes, agingRes] = await Promise.all([
                    axios.get('/peace_seafood/api/laporan/stok' + q, { headers }),
                    axios.get('/peace_seafood/api/laporan/penjualan' + q, { headers }),
                    axios.get('/peace_seafood/api/laporan/hutang-aging', { headers }),
                ]);
                this.stokData     = stokRes.data?.data || [];
                this.penjualanData = penjRes.data?.data || [];
                this.agingData    = agingRes.data?.data || [];
            } catch(e) { console.error(e); }
            this.loading = false;
        },

        exportCsv() {
            const token = localStorage.getItem('token');
            window.location.href = `/peace_seafood/api/laporan/export-csv?tab=${this.activeTab}&dari=${this.filters.dari}&sampai=${this.filters.sampai}&token=${token}`;
        },
        exportPdf() {
            const token = localStorage.getItem('token');
            window.location.href = `/peace_seafood/api/laporan/export-pdf?tab=${this.activeTab}&dari=${this.filters.dari}&sampai=${this.filters.sampai}&token=${token}`;
        },
    };
}
