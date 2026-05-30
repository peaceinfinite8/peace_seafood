/* extracted from activity-log_index.scripts.1.js */
// extracted from src/views/activity-log/index.php
function activityLogPage() {

    /* ── Helpers (module-scoped, tidak global) ── */
    function esc(v) {
        if (v === null || v === undefined) return '';
        return String(v)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function fmtKg(v) {
        const n = parseFloat(v);
        if (isNaN(n)) return v;
        return n.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' kg';
    }

    function fmtRp(v) {
        const n = parseInt(v, 10);
        if (isNaN(n)) return v;
        return 'Rp ' + n.toLocaleString('id-ID');
    }

    function statusLabel(s) {
        const map = {
            draft: 'Draft', final: 'Final', cancel: 'Dibatalkan',
            pending: 'Menunggu', confirmed: 'Terkonfirmasi', rejected: 'Ditolak',
            open: 'Belum Lunas', sebagian: 'Sebagian', lunas: 'Lunas',
            masuk: 'Masuk', dijual_sebagian: 'Dijual Sebagian',
            dijual_semua: 'Dijual Semua', selesai: 'Selesai',
            approved: 'Disetujui', posted: 'Diposting',
        };
        return map[String(s).toLowerCase()] || (String(s).charAt(0).toUpperCase() + String(s).slice(1));
    }

    function tableLabel(name) {
        const map = {
            nota: 'Nota Penjualan', nota_detail: 'Item Nota',
            stok_masuk: 'Stok Masuk', timbangan: 'Timbangan',
            stok_transfer: 'Transfer Stok', stok_opname: 'Stok Opname',
            titipan: 'Penitipan', titipan_penjualan: 'Penjualan Titipan',
            retur: 'Retur', hutang_piutang: 'Hutang/Piutang',
            hutang_piutang_history: 'Riwayat Bayar',
            biaya_operasional: 'Biaya Operasional',
            produk: 'Produk', supplier: 'Supplier',
            pembeli: 'Pembeli', jenis_ikan: 'Jenis Ikan',
            gudang: 'Gudang', users: 'Pengguna',
            settings: 'Pengaturan', notifikasi: 'Notifikasi',
        };
        return map[name] || name;
    }

    /* ── Narrative builder ── */
    function buildNarrative(log) {
        try {
            const b   = log.before_value || null;
            const a   = log.after_value  || null;
            const t   = (log.table_name  || '').toLowerCase();
            const act = (log.action      || '').toUpperCase();
            const id  = log.record_id;
            const tl  = tableLabel(t);

            /* ── INSERT ── */
            if (act === 'INSERT') {
                const d = a || b || {};

                if (t === 'nota') {
                    const no    = d.no_nota    ? `<strong>${esc(d.no_nota)}</strong>` : `#${id}`;
                    const total = d.total      ? ` senilai <strong>${fmtRp(d.total)}</strong>` : '';
                    const bayar = d.pembayaran ? ` (${esc(d.pembayaran)})` : '';
                    return `Membuat Nota Penjualan ${no}${total}${bayar}.`;
                }

                if (t === 'stok_masuk') {
                    const qty  = d.qty        ? ` sebesar <strong>${fmtKg(d.qty)}</strong>` : '';
                    const hrg  = d.harga_beli ? ` @ ${fmtRp(d.harga_beli)}/kg` : '';
                    return `Menginput Stok Masuk #${id}${qty}${hrg}.`;
                }

                if (t === 'timbangan') {
                    const teori = d.qty_teoritis ? fmtKg(d.qty_teoritis) : '-';
                    const aktual = d.qty_actual  ? fmtKg(d.qty_actual)   : '-';
                    const pct   = d.persen_susut ? ` (susut ${parseFloat(d.persen_susut).toFixed(1)}%)` : '';
                    return `Mencatat timbangan: teoritis <strong>${teori}</strong>, aktual <strong>${aktual}</strong>${pct}.`;
                }

                if (t === 'stok_opname') {
                    const tgl = d.tanggal_opname ? ` tanggal <strong>${esc(d.tanggal_opname)}</strong>` : '';
                    return `Memulai sesi Stok Opname #${id}${tgl}.`;
                }

                if (t === 'stok_transfer') {
                    const qty = d.qty ? ` sebesar <strong>${fmtKg(d.qty)}</strong>` : '';
                    return `Mengajukan Transfer Stok #${id}${qty}.`;
                }

                if (t === 'titipan') {
                    const no  = d.no_titipan ? `<strong>${esc(d.no_titipan)}</strong>` : `#${id}`;
                    const qty = d.qty_total  ? ` (${fmtKg(d.qty_total)})` : '';
                    return `Mencatat Penitipan ${no}${qty}.`;
                }

                if (t === 'hutang_piutang') {
                    const jenis = d.jenis === 'hutang' ? 'Hutang ke Supplier' : 'Piutang dari Pembeli';
                    const nom   = d.nominal ? ` senilai <strong>${fmtRp(d.nominal)}</strong>` : '';
                    return `Mencatat ${jenis}${nom}.`;
                }

                if (t === 'hutang_piutang_history') {
                    const nom = d.nominal_bayar ? `<strong>${fmtRp(d.nominal_bayar)}</strong>` : '-';
                    return `Mencatat pembayaran sebesar ${nom}.`;
                }

                if (t === 'biaya_operasional') {
                    const kat = d.kategori ? `<strong>${esc(d.kategori)}</strong>` : 'operasional';
                    const nom = d.nominal  ? ` senilai <strong>${fmtRp(d.nominal)}</strong>` : '';
                    return `Mencatat biaya ${kat}${nom}.`;
                }

                if (t === 'retur') {
                    const tipe = d.tipe === 'stok' ? 'Retur Stok' : 'Retur Piutang';
                    const nom  = d.nominal ? ` senilai <strong>${fmtRp(d.nominal)}</strong>` : '';
                    const qty  = d.qty     ? ` (${fmtKg(d.qty)})` : '';
                    return `Mengajukan ${tipe}${qty}${nom}.`;
                }

                if (t === 'supplier') {
                    return `Menambahkan Supplier baru: <strong>${esc(d.nama || '#' + id)}</strong>.`;
                }

                if (t === 'pembeli') {
                    return `Menambahkan Pembeli baru: <strong>${esc(d.nama || '#' + id)}</strong>.`;
                }

                if (t === 'produk') {
                    const hrg = d.harga_jual ? ` — harga jual ${fmtRp(d.harga_jual)}` : '';
                    return `Menambahkan Produk baru: <strong>${esc(d.nama || '#' + id)}</strong>${hrg}.`;
                }

                if (t === 'jenis_ikan') {
                    return `Menambahkan Jenis Ikan baru: <strong>${esc(d.nama || '#' + id)}</strong>.`;
                }

                if (t === 'users') {
                    return `Menambahkan pengguna baru: <strong>${esc(d.name || '#' + id)}</strong> (${esc(d.role || '-')}).`;
                }

                return `Menambahkan data baru pada <strong>${tl}</strong> #${id}.`;
            }

            /* ── UPDATE ── */
            if (act === 'UPDATE') {
                // Status change — paling umum
                if (b && a && 'status' in b && 'status' in a && b.status !== a.status) {
                    let label = tl;
                    if (t === 'nota' && (b.no_nota || a.no_nota)) {
                        label = `Nota <strong>${esc(b.no_nota || a.no_nota)}</strong>`;
                    }
                    return `Mengubah status ${label} dari <strong>${statusLabel(b.status)}</strong> menjadi <strong>${statusLabel(a.status)}</strong>.`;
                }

                // Harga berubah
                if (b && a && ('harga_jual' in b || 'harga_beli' in b)) {
                    const parts = [];
                    if ('harga_jual' in b && b.harga_jual !== a.harga_jual) {
                        parts.push(`harga jual dari <strong>${fmtRp(b.harga_jual)}</strong> menjadi <strong>${fmtRp(a.harga_jual)}</strong>`);
                    }
                    if ('harga_beli' in b && b.harga_beli !== a.harga_beli) {
                        parts.push(`harga beli dari <strong>${fmtRp(b.harga_beli)}</strong> menjadi <strong>${fmtRp(a.harga_beli)}</strong>`);
                    }
                    if (parts.length) return `Mengubah ${parts.join(' dan ')} pada <strong>${tl}</strong> #${id}.`;
                }

                // Stok qty berubah
                if (b && a && 'stok_qty' in b && b.stok_qty !== a.stok_qty) {
                    return `Memperbarui stok <strong>${esc(a.nama || b.nama || tl)}</strong>: ${fmtKg(b.stok_qty)} → <strong>${fmtKg(a.stok_qty)}</strong>.`;
                }

                // Pembayaran hutang/piutang
                if (b && a && 'nominal_bayar' in b && b.nominal_bayar !== a.nominal_bayar) {
                    return `Memperbarui pembayaran ${tl} #${id}: ${fmtRp(b.nominal_bayar)} → <strong>${fmtRp(a.nominal_bayar)}</strong>.`;
                }

                // Generic: list field yang berubah
                if (b && a) {
                    const changed = [];
                    const skipKeys = ['updated_at', 'created_at', 'id'];
                    for (const k of Object.keys(a)) {
                        if (skipKeys.includes(k)) continue;
                        if (JSON.stringify(b[k]) !== JSON.stringify(a[k])) {
                            changed.push(`<em>${esc(k)}</em>: "${esc(String(b[k] ?? ''))}" → "<strong>${esc(String(a[k] ?? ''))}</strong>"`);
                        }
                    }
                    if (changed.length) {
                        return `Memperbarui ${tl} #${id}: ${changed.slice(0, 4).join('; ')}${changed.length > 4 ? ` (+${changed.length - 4} lainnya)` : ''}.`;
                    }
                }

                // Hanya after tersedia
                if (a && 'status' in a) {
                    return `Memperbarui status ${tl} #${id} menjadi <strong>${statusLabel(a.status)}</strong>.`;
                }

                return `Memperbarui data <strong>${tl}</strong> #${id}.`;
            }

            /* ── DELETE ── */
            if (act === 'DELETE') {
                const d = b || {};
                const nama = d.nama || d.name || d.no_nota || d.no_titipan || null;
                if (nama) return `Menghapus ${tl} <strong>${esc(nama)}</strong> (ID #${id}).`;
                return `Menghapus data <strong>${tl}</strong> #${id}.`;
            }

            /* ── MIGRATION ── */
            if (act === 'MIGRATION') {
                const d = a || b || {};
                return `Menjalankan migrasi database${d.migration ? ': <strong>' + esc(d.migration) + '</strong>' : ''}.`;
            }

            return `${esc(act)} pada <strong>${tl}</strong> #${id}.`;

        } catch (e) {
            return `<span style="color:var(--text-secondary)">—</span>`;
        }
    }

    /* ── Alpine component ── */
    return {
        logs: [],
        filteredLogs: [],
        availableTables: [],
        filterAction: '',
        filterTable: '',
        limit: 100,
        loading: false,
        detailLog: null,

        init() {
            this.loadData();
        },

        async loadData() {
            this.loading = true;
            this.detailLog = null;
            try {
                const token = localStorage.getItem('token');
                const res = await axios.get('/peace_seafood/api/activity-log?limit=' + this.limit, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                this.logs = res.data?.data || res.data || [];
                this.availableTables = [...new Set(this.logs.map(l => l.table_name).filter(Boolean))].sort();
                this.applyFilter();
            } catch (e) {
                console.error('Activity log load error:', e);
            }
            this.loading = false;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        applyFilter() {
            this.filteredLogs = this.logs.filter(l => {
                if (this.filterAction && l.action !== this.filterAction) return false;
                if (this.filterTable && l.table_name !== this.filterTable) return false;
                return true;
            });
        },

        tableLabel(name) { return tableLabel(name); },

        buildNarrative(log) { return buildNarrative(log); },

        formatDateTime(dt) {
            if (!dt) return '-';
            try {
                return new Date(dt).toLocaleString('id-ID', {
                    day: '2-digit', month: 'short', year: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                });
            } catch (e) { return dt; }
        },

        openDetail(log) {
            this.detailLog = log;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        prettyJson(obj) {
            try { return JSON.stringify(obj, null, 2); }
            catch (e) { return String(obj); }
        },
    };
}
