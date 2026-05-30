/* extracted from layouts_app.script.1.js */
// extracted from src/views/layouts/app.php
// Uncaught error handler to show errors in UI
window.addEventListener('error', (event) => {
    const errBanner = document.createElement('div');
    errBanner.style.position = 'fixed';
    errBanner.style.bottom = '0';
    errBanner.style.left = '0';
    errBanner.style.width = '100%';
    errBanner.style.background = 'rgba(239, 68, 68, 0.95)';
    errBanner.style.color = '#fff';
    errBanner.style.padding = '12px';
    errBanner.style.zIndex = '99999';
    errBanner.style.fontFamily = 'monospace';
    errBanner.style.fontSize = '12px';
    errBanner.innerHTML = `<strong>JS Error:</strong> ${event.message} at ${event.filename}:${event.lineno}:${event.colno}`;
    document.body.appendChild(errBanner);
});
window.addEventListener('unhandledrejection', (event) => {
    const errBanner = document.createElement('div');
    errBanner.style.position = 'fixed';
    errBanner.style.bottom = '0';
    errBanner.style.left = '0';
    errBanner.style.width = '100%';
    errBanner.style.background = 'rgba(245, 158, 11, 0.95)';
    errBanner.style.color = '#fff';
    errBanner.style.padding = '12px';
    errBanner.style.zIndex = '99999';
    errBanner.style.fontFamily = 'monospace';
    errBanner.style.fontSize = '12px';
    errBanner.innerHTML = `<strong>Promise Rejection:</strong> ${event.reason}`;
    document.body.appendChild(errBanner);
});

// Global Interceptor Proxy for iziToast -> SweetAlert2 Toast
window.iziToast = {
    showToast(type, options) {
        const title = options.title || '';
        const message = options.message || '';
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            customClass: {
                popup: 'swal2-toast-glassmorphic'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        Toast.fire({
            icon: type, // 'success', 'error', 'warning', 'info'
            title: title ? `<strong>${title}</strong>` : '',
            html: message
        });
    },
    success(options) {
        this.showToast('success', options);
    },
    error(options) {
        this.showToast('error', options);
    },
    warning(options) {
        this.showToast('warning', options);
    },
    info(options) {
        this.showToast('info', options);
    }
};

// Async Wrapper for window.confirm
window.confirm = (message) => {
    return new Promise((resolve) => {
        Swal.fire({
            title: 'Konfirmasi Tindakan',
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'swal2-glassmorphic',
                confirmButton: 'swal2-confirm-btn',
                cancelButton: 'swal2-cancel-btn'
            },
            buttonsStyling: false
        }).then((result) => {
            resolve(result.isConfirmed);
        });
    });
};

/* extracted from layouts_app.script.2.js */
// extracted from src/views/layouts/app.php
// Tailwind config
tailwind.config = {
    darkMode: ['class', '[data-theme="dark"]'],
    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: '#2563eb',
                    light: '#dbeafe',
                    dark: '#1e40af'
                },
                success: '#10b981',
                warning: '#f59e0b',
                danger: '#ef4444',
                info: '#06b6d4',
            }
        }
    }
}

/* extracted from layouts_app.script.3.js */
// extracted from src/views/layouts/app.php
window.addEventListener('load', () => {
    const loader = document.getElementById('nautical-page-loader');
    if (loader) {
        loader.style.opacity = '0';
        setTimeout(() => loader.remove(), 500);
    }
});
setTimeout(() => {
    const loader = document.getElementById('nautical-page-loader');
    if (loader) {
        loader.style.opacity = '0';
        setTimeout(() => loader.remove(), 500);
    }
}, 2000); // 2 seconds safety timeout

/* extracted from layouts_app.script.4.js */
// extracted from src/views/layouts/app.php
function appLayout() {
    return {
        sidebarOpen: false,
        theme: localStorage.getItem('theme') || 'light',
        currentUser: (() => {
            let u = {};
            try {
                u = JSON.parse(localStorage.getItem('user') || '{}') || {};
            } catch (e) {
                u = {};
            }
            if (u && u.role) u.role = u.role.toLowerCase();
            return u;
        })(),
        notifList: [],
        unreadCount: 0,
        notifTab: 'unread',

        // SaaS States
        showFirstLoginModal: false,
        firstLoginPass: '',
        firstLoginPassConfirm: '',
        forceLoginLoading: false,

        showOnboardingWizard: false,
        onboardingStep: 1,
        onboardingCompleted: true, // Default true to avoid visual flash
        // activeMenu injected server-side in views; use empty string in static build
        activeMenu: '',
        onboardingForm: {
            nama_gudang: '',
            alamat: '',
            kota: '',
            logo_base64: '',
            ikan_pilihan: [],
            mapper: {
                tanggal: 'Tanggal',
                jenis_ikan: 'Jenis Ikan',
                berat: 'Qty (kg)',
                harga: 'Harga',
                pembeli: 'Pembeli'
            }
        },
        excelUploaded: false,
        excelFileName: '',
        excelRawText: '',
        excelColumns: ['Tanggal', 'Jenis Ikan', 'Qty (kg)', 'Harga', 'Pembeli', 'Supplier', 'Total'],
        availableFishes: ['Cakalang', 'DEHO', 'Baby Tuna', 'Bandeng', 'Salem', 'Layang', 'Kembung Banjar', 'Cucut', 'Tenggiri'],

        saasLocked: false,
        saasLockReason: '',
        developerWhatsapp: '628123456789',

        get forcePasswordChecks() {
            const pass = this.firstLoginPass || '';
            return {
                length: pass.length >= 8,
                upper: /[A-Z]/.test(pass),
                lower: /[a-z]/.test(pass),
                number: /[0-9]/.test(pass),
                special: /[^a-zA-Z0-9]/.test(pass)
            };
        },

        get isForcePasswordStrong() {
            const c = this.forcePasswordChecks;
            return c.length && c.upper && c.lower && c.number && c.special;
        },

        init() {
            // Apply saved theme
            document.documentElement.setAttribute('data-theme', this.theme);

            // Init Lucide icons
            if (window.lucide) lucide.createIcons();

            // Check auth
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = '/peace_seafood/login';
                return;
            }

            // Setup axios auth header
            window.API_TOKEN = token;

            // Listen to global SaaS billing and bypass lock events
            window.addEventListener('saas-payment-required', (e) => {
                this.saasLocked = true;
                this.saasLockReason = e.detail || 'Masa aktif uji coba gratis atau sewa bulanan Anda telah berakhir.';
            });

            window.addEventListener('saas-password-change-required', () => {
                this.showFirstLoginModal = true;
            });

            window.addEventListener('open-onboarding-wizard', () => {
                this.showOnboardingWizard = true;
                this.onboardingStep = 1;
            });

            // Fetch settings to update Developer WhatsApp support and onboarding status
            this.fetchDeveloperWhatsapp();

            if (this.currentUser.is_first_login === 1) {
                this.showFirstLoginModal = true;
            } else if (['bos', 'admin', 'checker'].includes(this.currentUser.role)) {
                this.checkOnboardingStatus();
            }

            // Load unread count
            this.loadUnreadCount();

            // Watchers untuk Alpine -> trigger lucide rendering setelah render DOM
            this.$watch('notifList', () => {
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });
            });
            this.$watch('notifTab', () => {
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });
            });

            // Start background polling
            this.startPolling();

            // Apply any deep-linked glowing highlights
            this.applyHighlightGlow();
        },

        toggleTheme() {
            this.theme = this.theme === 'light' ? 'dark' : 'light';
            localStorage.setItem('theme', this.theme);
            document.documentElement.setAttribute('data-theme', this.theme);
            if (window.PSTheme && typeof window.PSTheme.set === 'function') window.PSTheme.set(this.theme);
            if (window.lucide) lucide.createIcons();
        },

        async loadUnreadCount() {
            try {
                const res = await apiClient.get('/notifikasi?unread=1');
                this.unreadCount = res.data.data?.unread_count || 0;
            } catch (e) { }
        },

        async loadNotif() {
            try {
                const res = await apiClient.get('/notifikasi');
                this.notifList = res.data.data?.notifikasi || [];
                this.unreadCount = res.data.data?.unread_count || 0;
            } catch (e) { }
        },

        async loadNotifSilently() {
            try {
                const res = await apiClient.get('/notifikasi');
                this.notifList = res.data.data?.notifikasi || [];
                this.unreadCount = res.data.data?.unread_count || 0;
            } catch (e) { }
        },

        async markRead(id) {
            // Optimistic UI update first so tab count is instant
            const n = this.notifList.find(x => x.id === id);
            if (n && n.is_read == 0) {
                n.is_read = 1;
                this.unreadCount = Math.max(0, this.unreadCount - 1);
            }
            // ALWAYS await so state is persisted to DB before any navigation
            try {
                await apiClient.post(`/notifikasi/${id}/read`);
            } catch (e) {
                // If API fails, rollback UI
                if (n) {
                    n.is_read = 0;
                    this.unreadCount++;
                }
                console.error('markRead failed:', e);
            }
        },

        async markAllRead() {
            try {
                // Optimistic UI update first
                const wasUnread = this.notifList.filter(n => n.is_read == 0).length;
                this.notifList.forEach(n => n.is_read = 1);
                this.unreadCount = 0;
                // Fire API
                apiClient.post('/notifikasi/read-all').catch(() => { });
            } catch (e) { }
        },

        async quickDismiss(id) {
            await this.markRead(id);
        },

        async deleteNotif(id) {
            // Prevent click.away from closing dropdown while swal is open
            window._swalOpen = true;
            try {
                const result = await Swal.fire({
                    title: 'Hapus Notifikasi?',
                    text: 'Notifikasi ini akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="lucide-trash-2" style="display:inline-block;width:14px;height:14px;vertical-align:middle;margin-right:5px;"></i> Ya, Hapus',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'swal2-glassmorphic',
                        confirmButton: 'swal2-confirm-btn',
                        cancelButton: 'swal2-cancel-btn'
                    },
                    buttonsStyling: false,
                    reverseButtons: true
                });
                window._swalOpen = false;

                if (result.isConfirmed) {
                    try {
                        await apiClient.delete(`/notifikasi/${id}`);
                        // Immediately remove from list
                        this.notifList = this.notifList.filter(x => x.id !== id);
                        this.unreadCount = this.notifList.filter(x => x.is_read == 0).length;

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil dihapus',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'swal2-toast-glassmorphic'
                            }
                        });
                    } catch (e) {
                        console.error('Delete failed:', e);
                        const errMsg = e.response?.data?.message || 'Gagal menghapus notifikasi';
                        iziToast.error({
                            title: 'Gagal',
                            message: errMsg,
                            position: 'topRight'
                        });
                    }
                }
            } catch (e) {
                window._swalOpen = false;
                console.error(e);
            }
        },

        async handleNotifClick(n) {
            // AWAIT markRead so status is saved to DB BEFORE page navigates
            if (n.is_read == 0) {
                await this.markRead(n.id);
            }
            const link = this.getNotifLink(n);
            if (link) {
                // Close dropdown first, then navigate
                window.dispatchEvent(new CustomEvent('close-notif-dropdown'));
                window.location.href = link;
            }
        },

        getNotifLink(n) {
            if (n.tipe === 'timbangan_pending' || n.tipe === 'timbangan_eskalasi' || n.tipe === 'timbangan_selesai') {
                return `/peace_seafood/stok/timbangan?highlight=timbangan-${n.reference_id}`;
            }
            if (n.tipe === 'stok_minimum') {
                return `/peace_seafood/master-data/produk?highlight=produk-${n.reference_id}`;
            }
            if (n.tipe === 'hutang_jatuh_tempo') {
                return `/peace_seafood/keuangan?highlight=debt-${n.reference_id}`;
            }
            if (n.tipe === 'draft_penjualan') {
                return `/peace_seafood/penjualan?highlight=nota-${n.reference_id}&filter=draft`;
            }
            return null;
        },

        applyHighlightGlow() {
            const urlParams = new URLSearchParams(window.location.search);
            const highlightId = urlParams.get('highlight');
            if (!highlightId) return;

            const checkInterval = setInterval(() => {
                const element = document.getElementById(highlightId) || document.querySelector(`[data-highlight="${highlightId}"]`);
                if (element) {
                    clearInterval(checkInterval);

                    // Scroll smoothly into view
                    element.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    // Add glowing class
                    element.classList.add('highlight-glow-pulse');

                    // Remove class on interaction
                    const removeHighlight = () => {
                        element.classList.remove('highlight-glow-pulse');
                        element.removeEventListener('click', removeHighlight);
                        element.removeEventListener('focusin', removeHighlight);
                        element.removeEventListener('mousedown', removeHighlight);
                    };

                    element.addEventListener('click', removeHighlight);
                    element.addEventListener('focusin', removeHighlight);
                    element.addEventListener('mousedown', removeHighlight);

                    // Auto remove after 10 seconds
                    setTimeout(removeHighlight, 10000);
                }
            }, 100);

            // Safety timeout to prevent infinite checking
            setTimeout(() => clearInterval(checkInterval), 6000);
        },

        filteredNotifList() {
            if (this.notifTab === 'unread') {
                return this.notifList.filter(x => x.is_read == 0);
            }
            return this.notifList;
        },

        getNotifBgClass(tipe) {
            switch (tipe) {
                case 'hutang_jatuh_tempo':
                case 'timbangan_eskalasi':
                    return 'bg-red-50/80 dark:bg-red-950/30 text-red-500 border-red-100 dark:border-red-900/20';
                case 'stok_minimum':
                    return 'bg-amber-50/80 dark:bg-amber-950/30 text-amber-500 border-amber-100 dark:border-amber-900/20';
                case 'timbangan_selesai':
                    return 'bg-emerald-50/80 dark:bg-emerald-950/30 text-success border-emerald-100 dark:border-emerald-900/20';
                case 'timbangan_pending':
                default:
                    return 'bg-blue-50/80 dark:bg-blue-950/30 text-primary border-blue-100 dark:border-blue-900/20';
            }
        },

        getNotifIcon(tipe) {
            switch (tipe) {
                case 'hutang_jatuh_tempo':
                    return 'credit-card';
                case 'timbangan_eskalasi':
                    return 'alert-octagon';
                case 'stok_minimum':
                    return 'package';
                case 'timbangan_selesai':
                    return 'check-circle';
                case 'timbangan_pending':
                default:
                    return 'clock';
            }
        },

        getNotifIconColorClass(tipe) {
            switch (tipe) {
                case 'hutang_jatuh_tempo':
                case 'timbangan_eskalasi':
                    return 'text-red-500 dark:text-red-400';
                case 'stok_minimum':
                    return 'text-amber-500 dark:text-amber-400';
                case 'timbangan_selesai':
                    return 'text-success dark:text-emerald-400';
                case 'timbangan_pending':
                default:
                    return 'text-primary dark:text-blue-400';
            }
        },

        getNotifToastIcon(tipe) {
            switch (tipe) {
                case 'hutang_jatuh_tempo':
                case 'timbangan_eskalasi':
                    return 'error';
                case 'stok_minimum':
                    return 'warning';
                case 'timbangan_selesai':
                    return 'success';
                case 'timbangan_pending':
                default:
                    return 'info';
            }
        },

        showClassyToast(n) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: true,
                confirmButtonText: 'Buka Aksi',
                showCancelButton: true,
                cancelButtonText: 'Abaikan',
                timer: 8000,
                timerProgressBar: true,
                customClass: {
                    popup: 'swal2-toast-glassmorphic swal2-clickable-toast',
                    confirmButton: 'swal2-toast-confirm-btn',
                    cancelButton: 'swal2-toast-cancel-btn'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                    // Force rendering of dynamic icons inside custom sweetalert popups if any
                    if (window.lucide) lucide.createIcons();
                }
            });

            Toast.fire({
                icon: this.getNotifToastIcon(n.tipe),
                title: `<strong>${n.judul}</strong>`,
                html: `<div class="text-[11px] opacity-90 mt-1">${n.pesan}</div>`
            }).then((result) => {
                if (result.isConfirmed) {
                    this.handleNotifClick(n);
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    this.quickDismiss(n.id);
                }
            });
        },

        startPolling() {
            // Polling senyap setiap 15 detik
            setInterval(async () => {
                try {
                    const res = await apiClient.get('/notifikasi?unread=1');
                    const newUnreadCount = res.data.data?.unread_count || 0;
                    const newUnreadList = res.data.data?.notifikasi || [];

                    if (newUnreadCount > this.unreadCount) {
                        // Cari notifikasi unread mana yang baru saja masuk
                        const oldIds = this.notifList.map(x => x.id);
                        const newItems = newUnreadList.filter(x => !oldIds.includes(x.id));

                        newItems.forEach(n => {
                            this.showClassyToast(n);
                        });
                    }

                    this.unreadCount = newUnreadCount;

                    // Segarkan data notifikasi di memory secara senyap jika dropdown sedang ditutup
                    if (!this.open) {
                        this.loadNotifSilently();
                    }
                } catch (e) { }
            }, 15000);
        },

        formatTime(dateStr) {
            if (!dateStr) return '';
            try {
                const t = dateStr.split(/[- :]/);
                const d = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);
                const now = new Date();

                const hours = String(d.getHours()).padStart(2, '0');
                const mins = String(d.getMinutes()).padStart(2, '0');

                if (d.toDateString() === now.toDateString()) {
                    return `Hari ini, ${hours}:${mins}`;
                }

                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                return `${d.getDate()} ${months[d.getMonth()]}, ${hours}:${mins}`;
            } catch (e) {
                return dateStr;
            }
        },

        async checkOnboardingStatus() {
            // Wizard onboarding HANYA untuk bos, admin, checker — saas_owner tidak perlu setup gudang
            if (this.currentUser.role === 'saas_owner') {
                this.onboardingCompleted = true;
                return;
            }
            try {
                const res = await apiClient.get('/settings');
                const settings = res.data.data || [];
                const onboarding = settings.find(s => s.kunci === 'onboarding_completed');
                if (!onboarding || onboarding.nilai !== '1') {
                    this.onboardingCompleted = false;
                    if (this.currentUser.role === 'bos') {
                        this.showOnboardingWizard = true;
                    }
                } else {
                    this.onboardingCompleted = true;
                }
            } catch (e) {
                console.error('Failed to check onboarding status:', e);
                this.onboardingCompleted = true; // Fallback so we don't lock on API error
            }
        },

        async fetchDeveloperWhatsapp() {
            try {
                const res = await apiClient.get('/settings');
                const settings = res.data.data || [];
                const wa = settings.find(s => s.kunci === 'platform_developer_whatsapp');
                if (wa && wa.nilai) {
                    this.developerWhatsapp = wa.nilai;
                }
            } catch (e) {
                // Fallback is safe
            }
        },

        async forceChangePassword() {
            if (!this.isForcePasswordStrong || this.firstLoginPass !== this.firstLoginPassConfirm) return;
            this.forceLoginLoading = true;
            try {
                await apiClient.post('/auth/change-password', {
                    password: this.firstLoginPass
                });
                this.showFirstLoginModal = false;

                // Update currentUser
                this.currentUser.is_first_login = 0;
                localStorage.setItem('user', JSON.stringify(this.currentUser));

                Swal.fire({
                    icon: 'success',
                    title: 'Password Diperbarui!',
                    text: 'Sandi aman baru Anda berhasil dipasang.',
                    customClass: {
                        popup: 'swal2-glassmorphic',
                        confirmButton: 'swal2-confirm-btn'
                    },
                    buttonsStyling: false
                }).then(() => {
                    if (this.currentUser.role === 'bos') {
                        this.checkOnboardingStatus();
                    }
                });
            } catch (e) {
                const msg = e.response?.data?.message || 'Gagal mengubah password.';
                Swal.fire({
                    icon: 'error',
                    title: 'Ubah Sandi Gagal',
                    text: msg,
                    customClass: {
                        popup: 'swal2-glassmorphic',
                        confirmButton: 'swal2-confirm-btn'
                    },
                    buttonsStyling: false
                });
            } finally {
                this.forceLoginLoading = false;
            }
        },

        handleLogoUpload(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (event) => {
                this.onboardingForm.logo_base64 = event.target.result;
            };
            reader.readAsDataURL(file);
        },

        handleExcelUpload(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.excelFileName = file.name;
            this.excelUploaded = true;
            if (file.name.endsWith('.csv')) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const text = event.target.result || '';
                    this.excelRawText = text;
                    const firstLine = text.split('\n')[0] || '';
                    if (firstLine.includes(',')) {
                        this.excelColumns = firstLine.split(',').map(s => s.trim().replace(/^["']|["']$/g, '')).filter(Boolean);
                    } else if (firstLine.includes(';')) {
                        this.excelColumns = firstLine.split(';').map(s => s.trim().replace(/^["']|["']$/g, '')).filter(Boolean);
                    }
                };
                reader.readAsText(file);
            }
        },

        handleOnboardingNext() {
            if (this.onboardingStep < 3) {
                this.onboardingStep++;
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });
            } else {
                this.submitOnboarding();
            }
        },

        async submitOnboarding() {
            Swal.fire({
                title: 'Memproses Setup...',
                html: 'Sedang menyiapkan basis data dan mengimpor rekapan historis Anda.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                let transactions = [];
                if (this.excelUploaded && this.excelRawText) {
                    const text = this.excelRawText;
                    const lines = text.split(/\r?\n/).filter(line => line.trim() !== '');
                    if (lines.length > 1) {
                        const delimiter = text.includes(';') ? ';' : ',';
                        const headers = lines[0].split(delimiter).map(s => s.trim().replace(/^["']|["']$/g, ''));

                        const colMap = this.onboardingForm.mapper;
                        const idxTanggal = headers.indexOf(colMap.tanggal);
                        const idxJenisIkan = headers.indexOf(colMap.jenis_ikan);
                        const idxBerat = headers.indexOf(colMap.berat);
                        const idxHarga = headers.indexOf(colMap.harga);
                        const idxPembeli = headers.indexOf(colMap.pembeli);

                        for (let i = 1; i < lines.length; i++) {
                            const line = lines[i];
                            let cols = [];
                            let inQuotes = false;
                            let currentVal = '';
                            for (let c = 0; c < line.length; c++) {
                                const char = line[c];
                                if (char === '"') {
                                    inQuotes = !inQuotes;
                                } else if (char === delimiter && !inQuotes) {
                                    cols.push(currentVal.trim().replace(/^["']|["']$/g, ''));
                                    currentVal = '';
                                } else {
                                    currentVal += char;
                                }
                            }
                            cols.push(currentVal.trim().replace(/^["']|["']$/g, ''));

                            if (cols.length <= Math.max(idxTanggal, idxJenisIkan, idxBerat, idxHarga)) continue;

                            const rawTanggal = cols[idxTanggal] || '';
                            const rawJenisIkan = cols[idxJenisIkan] || '';
                            const rawBerat = parseFloat(cols[idxBerat]) || 0;
                            const rawHarga = parseFloat(cols[idxHarga]) || 0;
                            const rawPembeli = idxPembeli !== -1 ? (cols[idxPembeli] || 'Umum') : 'Umum';

                            if (!rawTanggal || !rawJenisIkan || rawBerat <= 0 || rawHarga <= 0) continue;

                            // Normalize Date format to YYYY-MM-DD
                            let formattedDate = rawTanggal;
                            if (rawTanggal.includes('/') || rawTanggal.includes('-')) {
                                const sep = rawTanggal.includes('/') ? '/' : '-';
                                const parts = rawTanggal.split(sep);
                                if (parts.length === 3) {
                                    if (parts[0].length === 4) {
                                        formattedDate = `${parts[0]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')}`;
                                    } else if (parts[2].length === 4) {
                                        formattedDate = `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
                                    }
                                }
                            }

                            transactions.push({
                                tanggal: formattedDate,
                                pembeli: rawPembeli,
                                no_nota: 'NOTA-OB-' + Math.floor(100000 + Math.random() * 900000),
                                produk: rawJenisIkan,
                                qty: rawBerat,
                                harga_jual: rawHarga,
                                subtotal: rawBerat * rawHarga,
                                diskon: 0,
                                total: rawBerat * rawHarga,
                                pembayaran: 'cash',
                                catatan: 'Migrasi onboarding data rekapan lama'
                            });
                        }

                        // Sort chronologically by date
                        transactions.sort((a, b) => new Date(a.tanggal) - new Date(b.tanggal));
                    }
                }

                // 1. Post to import historical data if present
                if (transactions.length > 0) {
                    await apiClient.post('/migrasi/excel/import', {
                        data: {
                            pembeli: [],
                            supplier: [],
                            stok: [],
                            penjualan: transactions
                        }
                    });
                }

                // 2. Finalize onboarding via complete
                const res = await apiClient.post('/onboarding/complete', {
                    nama_gudang: this.onboardingForm.nama_gudang,
                    alamat: this.onboardingForm.alamat,
                    kota: this.onboardingForm.kota,
                    ikan_pilihan: this.onboardingForm.ikan_pilihan
                });

                // 3. Save logo if uploaded
                if (this.onboardingForm.logo_base64) {
                    await apiClient.put('/settings/company_logo_base64', {
                        nilai: this.onboardingForm.logo_base64
                    });
                }
                await apiClient.put('/settings/company_name', {
                    nilai: this.onboardingForm.nama_gudang
                });

                this.showOnboardingWizard = false;

                Swal.fire({
                    icon: 'success',
                    title: 'Setup Berhasil!',
                    text: res.data.message || 'Masa uji coba gratis Anda resmi dimulai sekarang!',
                    customClass: {
                        popup: 'swal2-glassmorphic',
                        confirmButton: 'swal2-confirm-btn'
                    },
                    buttonsStyling: false
                }).then(() => {
                    window.location.reload();
                });
            } catch (e) {
                const msg = e.response?.data?.message || 'Gagal menyelesaikan onboarding.';
                Swal.fire({
                    icon: 'error',
                    title: 'Setup Gagal',
                    text: msg,
                    customClass: {
                        popup: 'swal2-glassmorphic',
                        confirmButton: 'swal2-confirm-btn'
                    },
                    buttonsStyling: false
                });
            }
        },

        logout() {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/peace_seafood/login';
        }
    };
}

/* extracted from layouts_app.script.5.js */
// extracted from src/views/layouts/app.php
function floatingCalc() {
    return {
        open: false,
        display: '0',
        expression: '',
        currentVal: '',
        operator: null,
        prevVal: null,
        justCalc: false,

        buttons: [{
            label: 'AC',
            type: 'fn'
        },
        {
            label: '+/-',
            type: 'fn'
        },
        {
            label: '%',
            type: 'fn'
        },
        {
            label: '÷',
            type: 'op'
        },
        {
            label: '7',
            type: 'num'
        },
        {
            label: '8',
            type: 'num'
        },
        {
            label: '9',
            type: 'num'
        },
        {
            label: '×',
            type: 'op'
        },
        {
            label: '4',
            type: 'num'
        },
        {
            label: '5',
            type: 'num'
        },
        {
            label: '6',
            type: 'num'
        },
        {
            label: '−',
            type: 'op'
        },
        {
            label: '1',
            type: 'num'
        },
        {
            label: '2',
            type: 'num'
        },
        {
            label: '3',
            type: 'num'
        },
        {
            label: '+',
            type: 'op'
        },
        {
            label: '0',
            type: 'zero'
        },
        {
            label: '.',
            type: 'num'
        },
        {
            label: '⌫',
            type: 'del'
        },
        {
            label: '=',
            type: 'eq'
        },
        ],

        getBtnStyle(btn) {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            if (btn.type === 'op') {
                return `background: var(--color-primary); color: white;`;
            }
            if (btn.type === 'eq') {
                return `background: var(--color-success); color: white;`;
            }
            if (btn.type === 'del') {
                return `background: var(--color-danger); color: white;`;
            }
            if (btn.type === 'fn') {
                return isDark ?
                    `background: rgba(100,116,139,0.25); color: var(--text-primary);` :
                    `background: rgba(100,116,139,0.15); color: var(--text-primary);`;
            }
            // num / zero
            return isDark ?
                `background: rgba(255,255,255,0.07); color: var(--text-primary);` :
                `background: rgba(0,0,0,0.05); color: var(--text-primary);`;
        },

        initCalc() {
            // keyboard input support
            window.addEventListener('keydown', (e) => {
                if (!this.open) return;
                const map = {
                    '0': '0',
                    '1': '1',
                    '2': '2',
                    '3': '3',
                    '4': '4',
                    '5': '5',
                    '6': '6',
                    '7': '7',
                    '8': '8',
                    '9': '9',
                    '.': '.',
                    'Enter': '=',
                    '=': '=',
                    '+': '+',
                    '-': '−',
                    '*': '×',
                    '/': '÷',
                    'Backspace': '⌫',
                    'Escape': 'AC'
                };
                const label = map[e.key];
                if (!label) return;
                e.preventDefault();
                const btn = this.buttons.find(b => b.label === label);
                if (btn) this.pressBtn(btn);
                else if (label === '⌫') this.backspace();
            });
        },

        handleKey(e) {
            if (e.altKey && e.key === 'c') {
                e.preventDefault();
                this.open = !this.open;
            }
        },

        pressBtn(btn) {
            if (btn.type === 'num' || btn.type === 'zero') {
                this.inputDigit(btn.label);
            } else if (btn.type === 'op') {
                this.inputOp(btn.label);
            } else if (btn.type === 'eq') {
                this.calculate();
            } else if (btn.type === 'del') {
                this.backspace();
            } else if (btn.type === 'fn') {
                if (btn.label === 'AC') this.clear();
                else if (btn.label === '+/-') this.negate();
                else if (btn.label === '%') this.percent();
            }
        },

        inputDigit(d) {
            if (this.justCalc) {
                this.currentVal = '';
                this.justCalc = false;
            }
            if (d === '.' && this.currentVal.includes('.')) return;
            if (d === '.' && this.currentVal === '') this.currentVal = '0';
            if (this.currentVal === '0' && d !== '.') this.currentVal = d;
            else this.currentVal += d;
            this.display = this.formatDisplay(this.currentVal);
        },

        inputOp(op) {
            if (this.prevVal !== null && this.currentVal !== '' && !this.justCalc) {
                this.doCalc();
            }
            if (this.currentVal !== '' || this.prevVal !== null) {
                this.prevVal = parseFloat(this.currentVal || this.display.replace(/\./g, '').replace(',', '.')) || this.prevVal;
                this.operator = op;
                this.expression = this.formatDisplay(String(this.prevVal)) + ' ' + op;
                this.currentVal = '';
                this.justCalc = false;
            }
        },

        calculate() {
            if (this.prevVal === null || this.currentVal === '') return;
            const expr = this.expression + ' ' + this.formatDisplay(this.currentVal) + ' =';
            this.doCalc();
            this.expression = expr;
            this.operator = null;
            this.prevVal = null;
            this.justCalc = true;
        },

        doCalc() {
            const a = this.prevVal;
            const b = parseFloat(this.currentVal);
            let result;
            if (this.operator === '+') result = a + b;
            else if (this.operator === '−') result = a - b;
            else if (this.operator === '×') result = a * b;
            else if (this.operator === '÷') result = b !== 0 ? a / b : 0;
            else return;
            // Round to avoid floating point noise
            result = Math.round(result * 1e10) / 1e10;
            this.currentVal = String(result);
            this.display = this.formatDisplay(this.currentVal);
        },

        clear() {
            this.display = '0';
            this.expression = '';
            this.currentVal = '';
            this.operator = null;
            this.prevVal = null;
            this.justCalc = false;
        },

        negate() {
            if (this.currentVal === '' || this.currentVal === '0') return;
            this.currentVal = String(-parseFloat(this.currentVal));
            this.display = this.formatDisplay(this.currentVal);
        },

        percent() {
            if (this.currentVal === '') return;
            this.currentVal = String(parseFloat(this.currentVal) / 100);
            this.display = this.formatDisplay(this.currentVal);
        },

        backspace() {
            if (this.justCalc) {
                this.clear();
                return;
            }
            this.currentVal = this.currentVal.slice(0, -1);
            this.display = this.currentVal ? this.formatDisplay(this.currentVal) : '0';
        },

        formatDisplay(val) {
            const num = parseFloat(val);
            if (isNaN(num)) return val || '0';
            // Show decimals if present
            const parts = String(val).split('.');
            const intPart = parseInt(parts[0]).toLocaleString('id-ID');
            if (parts.length > 1) return intPart + ',' + parts[1];
            return intPart;
        }
    };
}

/* extracted from layouts_app.script.6.js */
// extracted from src/views/layouts/app.php
// Subtle Mouse Parallax for Coastal Background (desktop only)
document.addEventListener('mousemove', (e) => {
    if (window.innerWidth < 768) return;
    const bg = document.getElementById('coastal-bg');
    if (!bg) return;
    const x = (window.innerWidth / 2 - e.clientX) * 0.012;
    const y = (window.innerHeight / 2 - e.clientY) * 0.012;
    bg.style.transform = `translate3d(${x}px, ${y}px, 0)`;
});

// Scroll Parallax for Background Elements (desktop only)
window.addEventListener('scroll', () => {
    if (window.innerWidth < 768) return;
    const scrollY = window.scrollY;

    // Move celestial orb slower
    const orb = document.querySelector('.coastal-celestial-orb');
    if (orb) {
        orb.style.transform = `translate3d(0, ${scrollY * 0.15}px, 0)`;
    }

    // Move stars container
    const stars = document.querySelector('.coastal-stars-container');
    if (stars) {
        stars.style.transform = `translate3d(0, ${scrollY * 0.1}px, 0)`;
    }

    // Move shore slightly
    const shore = document.querySelector('.coastal-shore');
    if (shore) {
        shore.style.transform = `translate3d(0, ${scrollY * 0.08}px, 0)`;
    }
});

// Re-init lucide icons after Alpine renders
document.addEventListener('alpine:initialized', () => {
    if (window.lucide) lucide.createIcons();
});

// 🌟 Universal Escape Key Modal Dismissal
window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const overlays = document.querySelectorAll('.modal-overlay');
        overlays.forEach(overlay => {
            // Try to resolve and set Alpine variables directly
            const alpineEl = overlay.closest('[x-data]');
            if (alpineEl && window.Alpine) {
                try {
                    const data = window.Alpine.$data(alpineEl);
                    if (data) {
                        const modalVars = [
                            'showModal', 'showBankModal', 'showUserModal',
                            'showPreview', 'showDetailModal', 'showJual',
                            'showBayarModal', 'showCreateModal'
                        ];
                        modalVars.forEach(v => {
                            if (v in data) data[v] = false;
                        });
                    }
                } catch (err) { }
            }
            // Dispatch click event on overlay to trigger @click.self triggers
            overlay.dispatchEvent(new MouseEvent('click', {
                bubbles: true,
                view: window
            }));
        });
    }
});
