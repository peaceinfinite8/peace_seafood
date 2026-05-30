/* extracted from settings_index.scripts.1.js */
// extracted from src/views/settings/index.php
function settingsPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        tab: 'umum',
        viewMode: 'tenant', // 'platform' or 'tenant' - UI-only switch
        settings: [],
        selectedGudangId: '',
        users: [],
        gudang: [],
        bankAccounts: [],
        showUserModal: false,
        showBankModal: false,
        showGudangModal: false,
        editUserId: null,
        editBankId: null,
        editGudangId: null,
        saving: false,
        savingBank: false,
        savingGudang: false,
        backingUp: false,
        logoBase64: '',
        userForm: { name: '', email: '', password: '', role: 'admin', id_gudang: '' },
        bankForm: { bank_name: '', account_number: '', account_name: '', is_active: '1' },
        gudangForm: { nama: '', alamat: '', kota: '', telpon: '', id_bos: '', is_active: '1' },
        
        // SaaS Developer States
        preApproving: false,
        savingWa: false,
        devWhatsappNumber: '628123456789',
        preApproveForm: { name: '', email: '', trial_days: '14' },

        showCropModal: false,
        cropImageSrc: '',
        cropZoom: 1.0,
        cropRotate: 0,
        cropPanX: 0,
        cropPanY: 0,
        isDraggingLogo: false,
        dragStartX: 0,
        dragStartY: 0,

        async init() {
            if (this.user.role !== 'super_admin' && this.user.role !== 'saas_owner' && this.user.role !== 'bos') {
                window.location.href = '/peace_seafood/dashboard';
                return;
            }
            await this.loadAll();
            // Default view mode: platform for saas_owner/super_admin, tenant otherwise
            if (['super_admin','saas_owner'].includes(this.user.role)) this.viewMode = 'platform';
            else this.viewMode = 'tenant';
            
            // Prefill WhatsApp Number
            const wa = this.settings.find(x => x.kunci === 'platform_developer_whatsapp');
            if (wa && wa.nilai) {
                this.devWhatsappNumber = wa.nilai;
            }
            // Default selected gudang for platform users: first in list
            if (['super_admin','saas_owner'].includes(this.user.role)) {
                if (this.gudang && this.gudang.length) this.selectedGudangId = this.gudang[0].id;
            } else if (this.user.role === 'bos') {
                this.selectedGudangId = this.user.id_gudang || '';
            }
            // If tenant view and a gudang selected, load its settings
            if (this.viewMode === 'tenant' && this.selectedGudangId) await this.loadSettingsForGudang();
            
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadAll() {
            const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
            const [setRes, usrRes, gudRes, bankRes] = await Promise.all([
                axios.get('/peace_seafood/api/settings', { headers }),
                axios.get('/peace_seafood/api/settings/users', { headers }),
                axios.get('/peace_seafood/api/settings/gudang', { headers }),
                axios.get('/peace_seafood/api/settings/bank-accounts', { headers }),
            ]);
            this.settings = setRes.data?.data || [];
            this.users    = usrRes.data?.data || [];
            this.gudang   = gudRes.data?.data || [];
            this.bankAccounts = bankRes.data?.data || [];
            
            this.syncLogoFromSettings();
        },

        syncLogoFromSettings() {
            const logo = this.settings.find(x => x.kunci === 'company_logo_base64');
            this.logoBase64 = logo ? logo.nilai : '';
        },

        async loadSettingsForPlatform() {
            const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
            const res = await axios.get('/peace_seafood/api/settings', { headers });
            this.settings = res.data?.data || [];
            this.syncLogoFromSettings();
        },

        async loadSettingsForGudang() {
            try {
                const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
                if (!this.selectedGudangId) {
                    // reload default server-provided settings
                    const res = await axios.get('/peace_seafood/api/settings', { headers });
                    this.settings = res.data?.data || [];
                } else {
                    const res = await axios.get('/peace_seafood/api/settings', { headers, params: { id_gudang: this.selectedGudangId } });
                    this.settings = res.data?.data || [];
                }
                this.syncLogoFromSettings();
            } catch (e) {
                console.error(e);
            }
        },

        async switchViewMode(mode) {
            this.viewMode = mode;
            this.tab = mode === 'platform' ? 'saas' : 'umum';
            if (mode === 'tenant') {
                await this.loadSettingsForGudang();
            } else {
                await this.loadSettingsForPlatform();
            }
        },

        formatNumberDot(val) {
            if (val === undefined || val === null || val === '') return '0';
            const num = parseInt(String(val).replace(/\D/g, '')) || 0;
            return num.toLocaleString('id-ID');
        },

        get availableRoles() {
            if (['super_admin', 'saas_owner'].includes(this.user.role)) {
                return [
                    { value: 'super_admin', label: 'Super Admin' },
                    { value: 'saas_owner', label: 'SaaS Owner' },
                    { value: 'bos', label: 'Bos (Executive Owner)' },
                    { value: 'admin', label: 'Admin Gudang' },
                    { value: 'checker', label: 'Checker Lapangan' },
                ];
            }
            return [
                { value: 'admin', label: 'Admin Gudang' },
                { value: 'checker', label: 'Checker Lapangan' },
            ];
        },

        isToggle(kunci) {
            return ['multi_warehouse_aktif', 'backup_otomatis', 'onboarding_wizard_aktif'].includes(kunci);
        },

        isSelect(kunci) {
            return ['komisi_penitipan_tipe', 'harga_locked_untuk', 'export_permission'].includes(kunci);
        },

        isNumber(kunci) {
            return ['stok_minimum_threshold', 'susut_alert_threshold', 'komisi_penitipan_persen',
                    'pajak_default_persen', 'jatuh_tempo_default_hari', 'session_timeout_menit', 'kapasitas_cold_storage_kg'].includes(kunci);
        },

        getSelectOptions(kunci) {
            const map = {
                komisi_penitipan_tipe: [
                    { value: 'potong',         label: 'Potong Langsung — supplier bayar net setelah komisi' },
                    { value: 'bayar_terpisah', label: 'Bayar Terpisah — supplier bayar full, komisi diklaim terpisah' },
                ],
                harga_locked_untuk: [
                    { value: 'bos',   label: 'Bos Only (default — paling aman)' },
                    { value: 'admin', label: 'Bos & Admin' },
                    { value: 'semua', label: 'Semua User (tidak disarankan)' },
                ],
                export_permission: [
                    { value: 'bos',   label: 'Bos Only (default — paling aman)' },
                    { value: 'admin', label: 'Bos & Admin' },
                    { value: 'semua', label: 'Semua User' },
                ],
            };
            return map[kunci] || [];
        },

        async saveSetting(setting) {
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                // If editing a specific gudang as platform user, pass id_gudang param
                const params = {};
                if (this.viewMode === 'tenant' && this.selectedGudangId) params.id_gudang = this.selectedGudangId;
                await axios.put('/peace_seafood/api/settings/' + setting.kunci + (Object.keys(params).length ? '?id_gudang=' + params.id_gudang : ''), { nilai: setting.nilai }, { headers });
                iziToast.success({ title: 'Berhasil', message: 'Setting disimpan', position: 'topRight' });
                // Reload only settings (not all lists) to reflect canonical server values
                if (this.viewMode === 'tenant' && this.selectedGudangId) await this.loadSettingsForGudang();
                else await this.loadSettingsForPlatform();
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal simpan', position: 'topRight' }); }
        },

        openAddBank() { this.editBankId = null; this.bankForm = { bank_name: '', account_number: '', account_name: '', is_active: '1' }; this.showBankModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        openEditBank(b) { this.editBankId = b.id; this.bankForm = { bank_name: b.bank_name, account_number: b.account_number, account_name: b.account_name, is_active: String(b.is_active ?? '1') }; this.showBankModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },

        async saveBank() {
            this.savingBank = true;
            try {
                const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
                this.bankForm.is_active = String(this.bankForm.is_active || '1');
                if (this.editBankId) { await axios.put('/peace_seafood/api/settings/bank-accounts/' + this.editBankId, this.bankForm, { headers }); }
                else { await axios.post('/peace_seafood/api/settings/bank-accounts', this.bankForm, { headers }); }
                iziToast.success({ title: 'Berhasil', message: 'Rekening tersimpan', position: 'topRight' });
                this.showBankModal = false; await this.reloadBanks();
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
            this.savingBank = false;
        },

        async reloadBanks() {
            const token = localStorage.getItem('token');
            const res = await axios.get('/peace_seafood/api/settings/bank-accounts', { headers: { Authorization: 'Bearer ' + token } });
            this.bankAccounts = res.data?.data || [];
        },

        async deleteBank(id) {
            if (!await confirm('Nonaktifkan rekening ini?')) return;
            const token = localStorage.getItem('token');
            await axios.delete('/peace_seafood/api/settings/bank-accounts/' + id, { headers: { Authorization: 'Bearer ' + token } });
            iziToast.success({ title: 'Berhasil', message: 'Rekening dinonaktifkan', position: 'topRight' });
            await this.loadAll();
        },

        openAddUser() { this.editUserId = null; this.userForm = { name: '', email: '', password: '', role: 'admin', id_gudang: '' }; this.showUserModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },
        openEditUser(u) { this.editUserId = u.id; this.userForm = { name: u.name, email: u.email, password: '', role: u.role, id_gudang: u.id_gudang||'' }; this.showUserModal = true; this.$nextTick(() => { if (window.lucide) lucide.createIcons(); }); },

        async saveUser() {
            this.saving = true;
            try {
                const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
                if (this.editUserId) { await axios.put('/peace_seafood/api/settings/users/' + this.editUserId, this.userForm, { headers }); }
                else { await axios.post('/peace_seafood/api/settings/users', this.userForm, { headers }); }
                iziToast.success({ title: 'Berhasil', message: 'User tersimpan', position: 'topRight' });
                this.showUserModal = false; await this.loadAll();
            } catch(e) { iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal', position: 'topRight' }); }
            this.saving = false;
        },

        async deleteUser(id) {
            if (!await confirm('Hapus user ini?')) return;
            const token = localStorage.getItem('token');
            await axios.delete('/peace_seafood/api/settings/users/' + id, { headers: { Authorization: 'Bearer ' + token } });
            iziToast.success({ title: 'Berhasil', message: 'User dihapus', position: 'topRight' }); await this.loadAll();
        },

        /* ── Gudang CRUD Methods ── */
        openAddGudang() {
            this.editGudangId = null;
            this.gudangForm = { nama: '', alamat: '', kota: '', telpon: '', id_bos: '', is_active: '1' };
            this.showGudangModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        openEditGudang(g) {
            this.editGudangId = g.id;
            this.gudangForm = {
                nama: g.nama,
                alamat: g.alamat || '',
                kota: g.kota || '',
                telpon: g.telpon || '',
                id_bos: g.id_bos || '',
                is_active: String(g.is_active ?? '1')
            };
            this.showGudangModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async saveGudang() {
            this.savingGudang = true;
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: 'Bearer ' + token };
                if (this.editGudangId) {
                    await axios.put('/peace_seafood/api/settings/gudang/' + this.editGudangId, this.gudangForm, { headers });
                } else {
                    await axios.post('/peace_seafood/api/settings/gudang', this.gudangForm, { headers });
                }
                iziToast.success({ title: 'Berhasil', message: 'Gudang berhasil disimpan', position: 'topRight' });
                this.showGudangModal = false;
                await this.loadAll();
            } catch(e) {
                iziToast.error({ title: 'Error', message: e.response?.data?.message || 'Gagal menyimpan gudang', position: 'topRight' });
            }
            this.savingGudang = false;
        },

        async deleteGudang(id) {
            if (!await confirm('Apakah Anda yakin ingin menghapus gudang ini? Jika sudah ada transaksi, gudang akan dinonaktifkan secara aman.')) return;
            try {
                const token = localStorage.getItem('token');
                await axios.delete('/peace_seafood/api/settings/gudang/' + id, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Gudang berhasil dihapus/dinonaktifkan', position: 'topRight' });
                await this.loadAll();
            } catch(e) {
                iziToast.error({ title: 'Error', message: 'Gagal memproses penghapusan gudang', position: 'topRight' });
            }
        },

        /* ── Database Backup ── */
        async runBackup() {
            this.backingUp = true;
            try {
                const token = localStorage.getItem('token');
                const response = await axios({
                    url: '/peace_seafood/api/settings/backup',
                    method: 'POST',
                    responseType: 'blob',
                    headers: { Authorization: 'Bearer ' + token }
                });
                
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'peace_seafood_backup_' + new Date().toISOString().split('T')[0] + '.sql');
                document.body.appendChild(link);
                link.click();
                link.remove();
                
                iziToast.success({ title: 'Berhasil', message: 'Backup database berhasil diunduh', position: 'topRight' });
            } catch(e) {
                iziToast.error({ title: 'Error', message: 'Gagal membuat backup database', position: 'topRight' });
            }
            this.backingUp = false;
        },

        /* ── SaaS Developer Methods ── */
        async runPreApprove() {
            this.preApproving = true;
            try {
                const token = localStorage.getItem('token');
                const res = await axios.post('/peace_seafood/api/settings/pre-approve', this.preApproveForm, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                iziToast.success({ title: 'Berhasil', message: res.data?.message || 'Email Bos sukses disetujui!', position: 'topRight' });
                this.preApproveForm = { name: '', email: '', trial_days: '14' };
                await this.loadAll();
            } catch (e) {
                const msg = e.response?.data?.message || 'Gagal pra-persetujuan.';
                iziToast.error({ title: 'Gagal', message: msg, position: 'topRight' });
            } finally {
                this.preApproving = false;
            }
        },

        async saveDevWhatsapp() {
            this.savingWa = true;
            try {
                const token = localStorage.getItem('token');
                await axios.put('/peace_seafood/api/settings/platform_developer_whatsapp', {
                    nilai: this.devWhatsappNumber
                }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                iziToast.success({ title: 'Berhasil', message: 'No. WhatsApp Developer diperbarui!', position: 'topRight' });
                await this.loadAll();
            } catch (e) {
                iziToast.error({ title: 'Gagal', message: 'Gagal memperbarui WhatsApp Developer.', position: 'topRight' });
            } finally {
                this.savingWa = false;
            }
        },

        getBosEmail(idBos) {
            const u = this.users.find(x => x.id === idBos);
            return u ? u.email : '';
        },

        getRemainingDaysText(expiryStr) {
            if (!expiryStr) return '(Trial Belum Dimulai)';
            const diffTime = new Date(expiryStr) - new Date();
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            if (diffDays < 0) return '(Masa Aktif Habis)';
            return `(${diffDays} hari tersisa)`;
        },

        getRemainingDaysNum(expiryStr) {
            if (!expiryStr) return 999; // belum dimulai → tampil normal (tidak expired)
            const diffTime = new Date(expiryStr) - new Date();
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        },

        async updateGudangSubscription(gudangObj, newDate) {
            try {
                const token = localStorage.getItem('token');
                await axios.put('/peace_seafood/api/settings/gudang/' + gudangObj.id, {
                    nama: gudangObj.nama,
                    id_bos: gudangObj.id_bos,
                    alamat: gudangObj.alamat,
                    kota: gudangObj.kota,
                    telpon: gudangObj.telpon,
                    is_active: gudangObj.is_active,
                    subscription_until: newDate,
                    status_langganan: gudangObj.status_langganan
                }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                iziToast.success({ title: 'Berhasil', message: 'Masa aktif sewa diperbarui!', position: 'topRight' });
                gudangObj.subscription_until = newDate;
            } catch (e) {
                iziToast.error({ title: 'Gagal', message: 'Gagal memperbarui masa aktif.', position: 'topRight' });
            }
        },

        async updateGudangStatus(gudangObj, newStatus) {
            try {
                const token = localStorage.getItem('token');
                await axios.put('/peace_seafood/api/settings/gudang/' + gudangObj.id, {
                    nama: gudangObj.nama,
                    id_bos: gudangObj.id_bos,
                    alamat: gudangObj.alamat,
                    kota: gudangObj.kota,
                    telpon: gudangObj.telpon,
                    is_active: gudangObj.is_active,
                    subscription_until: gudangObj.subscription_until,
                    status_langganan: newStatus
                }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                iziToast.success({ title: 'Berhasil', message: 'Status sewa gudang diperbarui!', position: 'topRight' });
                gudangObj.status_langganan = newStatus;
            } catch (e) {
                iziToast.error({ title: 'Gagal', message: 'Gagal memperbarui status sewa.', position: 'topRight' });
            }
        },

        async impersonateUser(idBos) {
            if (!idBos) return;
            try {
                const token = localStorage.getItem('token');
                const res = await axios.post('/peace_seafood/api/auth/impersonate', {
                    user_id: idBos
                }, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                const data = res.data?.data;
                if (data?.token) {
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    iziToast.success({ title: 'Sukses', message: 'Impersonating Bos, mengalihkan...', position: 'topRight' });
                    setTimeout(() => {
                        window.location.href = '/peace_seafood/dashboard';
                    }, 1000);
                }
            } catch (e) {
                const msg = e.response?.data?.message || 'Gagal impersonate.';
                iziToast.error({ title: 'Gagal', message: msg, position: 'topRight' });
            }
        },

        /* ── Premium Logo Cropper Methods ── */
        getCompanyInitial() {
            const s = this.settings.find(x => x.kunci === 'company_logo_initial');
            return s ? s.nilai : 'PS';
        },

        triggerLogoUpload() {
            const input = document.getElementById('logo-file-input');
            if (input) input.click();
        },

        onLogoSelected(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (event) => {
                this.cropImageSrc = event.target.result;
                this.cropZoom = 1.0;
                this.cropRotate = 0;
                this.cropPanX = 0;
                this.cropPanY = 0;
                this.showCropModal = true;
                this.setupDragEvents();
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            };
            reader.readAsDataURL(file);
        },

        setupDragEvents() {
            this.$nextTick(() => {
                const img = document.getElementById('crop-image');
                if (!img) return;
                
                // Mouse events
                img.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    this.isDraggingLogo = true;
                    this.dragStartX = e.clientX - this.cropPanX;
                    this.dragStartY = e.clientY - this.cropPanY;
                });
                
                window.addEventListener('mousemove', (e) => {
                    if (!this.isDraggingLogo) return;
                    this.cropPanX = e.clientX - this.dragStartX;
                    this.cropPanY = e.clientY - this.dragStartY;
                });
                
                window.addEventListener('mouseup', () => {
                    this.isDraggingLogo = false;
                });
                
                // Touch events for mobile
                img.addEventListener('touchstart', (e) => {
                    if (e.touches.length !== 1) return;
                    this.isDraggingLogo = true;
                    this.dragStartX = e.touches[0].clientX - this.cropPanX;
                    this.dragStartY = e.touches[0].clientY - this.cropPanY;
                });
                
                img.addEventListener('touchmove', (e) => {
                    if (!this.isDraggingLogo || e.touches.length !== 1) return;
                    this.cropPanX = e.touches[0].clientX - this.dragStartX;
                    this.cropPanY = e.touches[0].clientY - this.dragStartY;
                });
                
                img.addEventListener('touchend', () => {
                    this.isDraggingLogo = false;
                });
            });
        },

        applyCrop() {
            const img = new Image();
            img.src = this.cropImageSrc;
            img.onload = async () => {
                const canvas = document.createElement('canvas');
                canvas.width = 500;
                canvas.height = 500;
                const ctx = canvas.getContext('2d');
                if (!ctx) return;
                
                ctx.fillStyle = '#0f172a';
                ctx.fillRect(0, 0, 500, 500);
                
                ctx.save();
                ctx.translate(250, 250);
                ctx.rotate((this.cropRotate * Math.PI) / 180);
                const scale = parseFloat(this.cropZoom);
                
                let dw = img.width;
                let dh = img.height;
                const maxDim = Math.max(dw, dh);
                if (maxDim > 0) {
                     dw = (dw / maxDim) * 450 * scale;
                     dh = (dh / maxDim) * 450 * scale;
                }
                
                ctx.drawImage(img, -dw/2 + this.cropPanX * scale, -dh/2 + this.cropPanY * scale, dw, dh);
                ctx.restore();
                
                const croppedBase64 = canvas.toDataURL('image/jpeg', 0.85);
                this.logoBase64 = croppedBase64;
                
                let logoSetting = this.settings.find(x => x.kunci === 'company_logo_base64');
                if (!logoSetting) {
                    logoSetting = { kunci: 'company_logo_base64', nilai: croppedBase64 };
                    this.settings.push(logoSetting);
                } else {
                    logoSetting.nilai = croppedBase64;
                }
                
                await this.saveSetting(logoSetting);
                this.showCropModal = false;
                
                // Dispatch event so other pages or elements (e.g. sidebar) immediately update
                localStorage.setItem('company_logo_base64', croppedBase64);
                window.dispatchEvent(new Event('logo-updated'));
            };
        },
    };
}
