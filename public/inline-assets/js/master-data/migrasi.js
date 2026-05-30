/* extracted from master-data_migrasi.scripts.1.js */
// extracted from src/views/master-data/migrasi.php
function migrationPage() {
    return {
        tab: 'excel', // 'excel' or 'ocr'
        previewMode: false,
        ocrPreviewMode: false,
        dragOver: false,
        ocrDragOver: false,
        loading: false,
        loadingText: 'Memuat berkas...',
        ocrScanning: false,
        ocrProgress: 0,
        ocrStatusTitle: '',
        ocrStatusDesc: '',
        previewTab: 'stok', // 'stok', 'penjualan', 'supplier', 'pembeli'
        
        previewData: {
            supplier: [],
            pembeli: [],
            stok: [],
            penjualan: []
        },

        init() {
            // Check roles
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            if (!['super_admin','admin'].includes(user.role)) {
                window.location.href = '/peace_seafood/dashboard';
                return;
            }
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
            });
        },

        switchTab(target) {
            this.tab = target;
            this.previewMode = false;
            this.ocrPreviewMode = false;
            this.ocrScanning = false;
            this.clearPreviewData();
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
            });
        },

        async downloadTemplate(type) {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.get('/peace_seafood/api/migrasi/template?type=' + type, {
                    headers: { Authorization: 'Bearer ' + token },
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const a = document.createElement('a');
                a.href = url;
                a.download = type === 'stok' ? 'template_migrasi_stok_masuk.csv' : 'template_migrasi_penjualan.csv';
                document.body.appendChild(a);
                a.click();
                setTimeout(() => window.URL.revokeObjectURL(url), 1000);
                a.remove();
            } catch (err) {
                console.error(err);
                iziToast.error({ title: 'Gagal Mengunduh', message: 'Tidak dapat mengunduh template. Pastikan Anda sudah login.', position: 'topRight' });
            }
        },

        clearPreviewData() {
            this.previewData = {
                supplier: [],
                pembeli: [],
                stok: [],
                penjualan: []
            };
        },

        formatQty(qty) {
            if (qty === undefined || qty === null) return '0';
            return parseFloat(qty).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },

        formatMoney(amount) {
            if (amount === undefined || amount === null) return 'Rp 0';
            return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
        },

        cancelPreview() {
            this.previewMode = false;
            this.clearPreviewData();
        },

        cancelOcrPreview() {
            this.ocrPreviewMode = false;
            this.clearPreviewData();
        },

        // EXCEL EVENT HANDLERS
        handleDrop(e) {
            this.dragOver = false;
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.uploadFile(files[0]);
            }
        },

        handleFileSelect(e) {
            const files = e.target.files;
            if (files.length > 0) {
                this.uploadFile(files[0]);
            }
        },

        async uploadFile(file) {
            this.loading = true;
            this.loadingText = 'Mengunggah & menganalisis berkas migrasi...';
            
            const formData = new FormData();
            formData.append('file', file);
            
            try {
                const token = localStorage.getItem('token');
                const res = await axios.post('/peace_seafood/api/migrasi/excel/preview', formData, {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'multipart/form-data'
                    }
                });
                
                this.previewData = res.data.data;
                this.previewMode = true;
                
                // Set active preview tab based on what's available
                if (this.previewData.stok.length > 0) {
                    this.previewTab = 'stok';
                } else if (this.previewData.penjualan.length > 0) {
                    this.previewTab = 'penjualan';
                } else if (this.previewData.supplier.length > 0) {
                    this.previewTab = 'supplier';
                } else {
                    this.previewTab = 'pembeli';
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berkas Berhasil Dibaca!',
                    text: 'Silakan periksa rangkuman preview data di bawah sebelum menyimpan.',
                    timer: 2500,
                    showConfirmButton: false
                });
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Membaca Berkas',
                    text: err.response?.data?.message || 'Pastikan file sesuai template.',
                });
            } finally {
                this.loading = false;
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            }
        },

        async confirmImport() {
            const totalRecords = this.previewData.stok.length + this.previewData.penjualan.length;
            
            const confirm = await Swal.fire({
                title: 'Apakah Anda Yakin?',
                html: `Anda akan mengimpor <strong>${totalRecords} baris transaksi historis</strong> serta menyinkronkan data supplier/pembeli ke dalam database. Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Proses Migrasi!',
                cancelButtonText: 'Batal'
            });

            if (!confirm.isConfirmed) return;

            this.loading = true;
            this.loadingText = 'Menyimpan data transaksi historis ke database...';

            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/migrasi/excel/import', {
                    data: this.previewData
                }, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Migrasi Berhasil!',
                    text: 'Semua entitas dan riwayat keuangan/stok lama telah terintegrasi.',
                    confirmButtonText: 'Mantap!'
                });

                this.previewMode = false;
                this.clearPreviewData();
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Impor Data',
                    text: err.response?.data?.message || 'Terjadi kesalahan sistem saat menyimpan.'
                });
            } finally {
                this.loading = false;
            }
        },

        // OCR EVENT HANDLERS
        handleOcrDrop(e) {
            this.ocrDragOver = false;
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.scanOcrImage(files[0]);
            }
        },

        handleOcrFileSelect(e) {
            const files = e.target.files;
            if (files.length > 0) {
                this.scanOcrImage(files[0]);
            }
        },

        async scanOcrImage(file) {
            this.ocrScanning = true;
            this.ocrProgress = 15;
            this.ocrStatusTitle = 'Mengunggah Gambar Catatan...';
            this.ocrStatusDesc = 'Sonar AI sedang mentransfer berkas citra.';

            // Simulation animation steps
            const timer1 = setTimeout(() => {
                this.ocrProgress = 45;
                this.ocrStatusTitle = 'Membaca Tulisan Tangan...';
                this.ocrStatusDesc = 'AI sedang memindai pola teks & garis baris.';
            }, 800);

            const timer2 = setTimeout(() => {
                this.ocrProgress = 80;
                this.ocrStatusTitle = 'Sinkronisasi Entitas Kamus...';
                this.ocrStatusDesc = 'Mencari kecocokan produk & nama supplier.';
            }, 1800);

            const formData = new FormData();
            formData.append('file', file);

            try {
                const token = localStorage.getItem('token');
                const res = await axios.post('/peace_seafood/api/migrasi/ocr/preview', formData, {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'multipart/form-data'
                    }
                });

                // Complete progress
                this.ocrProgress = 100;
                this.ocrStatusTitle = 'Analisis OCR Selesai!';
                this.ocrStatusDesc = 'Tabel grid berhasil disiapkan.';

                setTimeout(() => {
                    this.previewData = res.data.data;
                    this.ocrScanning = false;
                    this.ocrPreviewMode = true;
                    this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                }, 400);

            } catch (err) {
                clearTimeout(timer1);
                clearTimeout(timer2);
                this.ocrScanning = false;
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal OCR Gambar',
                    text: err.response?.data?.message || 'Pastikan foto memiliki pencahayaan cukup dan teks terbaca.',
                });
            }
        },

        async confirmOcrImport() {
            const confirm = await Swal.fire({
                title: 'Simpan Hasil OCR?',
                text: 'Semua perubahan yang Anda buat di grid tabel akan disimpan secara permanen ke database.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Ya, Simpan Transaksi!',
                cancelButtonText: 'Batal'
            });

            if (!confirm.isConfirmed) return;

            this.ocrScanning = true;
            this.ocrStatusTitle = 'Menyinkronkan Database...';
            this.ocrStatusDesc = 'Memproses penambahan stok & rekapan penjualan.';
            this.ocrProgress = 50;

            try {
                const token = localStorage.getItem('token');
                await axios.post('/peace_seafood/api/migrasi/ocr/import', {
                    data: this.previewData
                }, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Disimpan!',
                    text: 'Riwayat transaksi buku logbook Anda telah terdaftar dalam sistem.',
                    confirmButtonText: 'Selesai'
                });

                this.ocrPreviewMode = false;
                this.clearPreviewData();
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Simpan Gagal',
                    text: err.response?.data?.message || 'Terjadi gangguan internal saat memproses.'
                });
            } finally {
                this.ocrScanning = false;
            }
        }
    };
}
