<?php ?>
<div x-data="settingsPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold" style="color: var(--text-primary)">Pengaturan Sistem</h2>
            <p class="text-sm" style="color: var(--text-secondary)">Konfigurasi aplikasi, user, dan gudang</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="keu-tab-group mb-6">
        <button @click="tab = 'umum'"
                class="keu-tab"
                :class="tab === 'umum' ? 'keu-tab--active keu-tab--semua' : 'keu-tab--idle'">
            Umum
        </button>
        <button @click="tab = 'users'"
                class="keu-tab"
                :class="tab === 'users' ? 'keu-tab--active keu-tab--semua' : 'keu-tab--idle'">
            User
        </button>
        <button @click="tab = 'gudang'"
                class="keu-tab"
                :class="tab === 'gudang' ? 'keu-tab--active keu-tab--semua' : 'keu-tab--idle'">
            Gudang
        </button>
    </div>

    <!-- Tab Umum -->
    <div x-show="tab === 'umum'" class="card p-6 max-w-2xl">
        <h3 class="font-semibold mb-5" style="color: var(--text-primary)">Pengaturan Aplikasi</h3>

        <!-- Empty state -->
        <template x-if="settings.length === 0">
            <p class="text-sm py-4" style="color: var(--text-secondary)">Memuat pengaturan...</p>
        </template>

        <div class="space-y-5">
            <template x-for="setting in settings" :key="setting.kunci">
                <div class="form-group mb-0">
                    <label class="form-label" x-text="setting.label || setting.kunci"></label>

                    <!-- Toggle (boolean: 0/1) -->
                    <template x-if="isToggle(setting.kunci)">
                        <div class="flex items-center gap-3 mt-1">
                            <button type="button"
                                    @click="setting.nilai = setting.nilai == '1' ? '0' : '1'; saveSetting(setting)"
                                    class="settings-toggle"
                                    :class="setting.nilai == '1' ? 'settings-toggle--on' : 'settings-toggle--off'"
                                    :aria-checked="setting.nilai == '1'"
                                    role="switch">
                                <span class="settings-toggle__thumb"></span>
                            </button>
                            <span class="text-sm font-medium"
                                  :style="setting.nilai == '1' ? 'color:var(--color-success)' : 'color:var(--text-secondary)'"
                                  x-text="setting.nilai == '1' ? 'Aktif' : 'Nonaktif'">
                            </span>
                        </div>
                    </template>

                    <!-- Select dropdown (enum) -->
                    <template x-if="isSelect(setting.kunci)">
                        <div class="flex gap-2 mt-1">
                            <select x-model="setting.nilai" class="form-input flex-1">
                                <template x-for="opt in getSelectOptions(setting.kunci)" :key="opt.value">
                                    <option :value="opt.value" x-text="opt.label"></option>
                                </template>
                            </select>
                            <button @click="saveSetting(setting)" class="btn btn-primary">
                                <i data-lucide="save" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </template>

                    <!-- Number / text input (default) -->
                    <template x-if="!isToggle(setting.kunci) && !isSelect(setting.kunci)">
                        <div class="flex gap-2 mt-1">
                            <input type="number"
                                   x-show="isNumber(setting.kunci)"
                                   x-model="setting.nilai"
                                   class="form-input flex-1"
                                   min="0" step="1">
                            <input type="text"
                                   x-show="!isNumber(setting.kunci)"
                                   x-model="setting.nilai"
                                   class="form-input flex-1">
                            <button @click="saveSetting(setting)" class="btn btn-primary">
                                <i data-lucide="save" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </template>

                    <p class="text-xs mt-1.5" style="color: var(--text-secondary)" x-text="setting.keterangan || ''"></p>
                </div>
            </template>
        </div>
    </div>

    <!-- Tab Users -->
    <div x-show="tab === 'users'" x-cloak>
        <div class="flex justify-between items-center mb-4">
            <p class="text-sm" style="color: var(--text-secondary)">Kelola akun pengguna sistem</p>
            <button @click="openAddUser()" class="btn btn-primary">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Tambah User
            </button>
        </div>
        <div class="card">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Gudang</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <template x-for="u in users" :key="u.id">
                            <tr>
                                <td class="font-medium text-sm" x-text="u.name"></td>
                                <td class="text-sm" x-text="u.email"></td>
                                <td><span class="badge" :class="u.role==='bos'?'badge-danger':u.role==='admin'?'badge-warning':'badge-info'" x-text="u.role?.toUpperCase()"></span></td>
                                <td class="text-sm" x-text="u.nama_gudang || 'Semua'"></td>
                                <td><span class="badge" :class="u.is_active?'badge-success':'badge-gray'" x-text="u.is_active?'AKTIF':'NONAKTIF'"></span></td>
                                <td>
                                    <div class="flex gap-2">
                                        <button @click="openEditUser(u)" class="btn btn-secondary p-1.5"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></button>
                                        <button @click="deleteUser(u.id)" class="btn btn-danger p-1.5" x-show="u.role !== 'bos'"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab Gudang -->
    <div x-show="tab === 'gudang'" x-cloak>
        <div class="flex justify-between items-center mb-4">
            <p class="text-sm" style="color: var(--text-secondary)">Kelola data gudang/cabang</p>
            <button @click="openAddGudang()" class="btn btn-primary">
                <i data-lucide="building" class="w-4 h-4"></i>
                Tambah Gudang
            </button>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <template x-for="g in gudang" :key="g.id">
                <div class="card p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-bold" x-text="g.nama"></h3>
                            <p class="text-sm mt-1" style="color: var(--text-secondary)" x-text="g.alamat || 'Alamat belum diset'"></p>
                            <p class="text-xs mt-2" style="color: var(--text-secondary)" x-text="g.telepon || ''"></p>
                        </div>
                        <button @click="openEditGudang(g)" class="btn btn-secondary p-1.5">
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- User Modal -->
    <div class="modal-overlay" x-show="showUserModal" @click.self="showUserModal = false" x-cloak>
        <div class="modal-box">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg" x-text="editUserId ? 'Edit User' : 'Tambah User'"></h3>
                <button @click="showUserModal = false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="saveUser()">
                <div class="form-group"><label class="form-label">Nama *</label><input type="text" x-model="userForm.name" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Email *</label><input type="email" x-model="userForm.email" class="form-input" required></div>
                <div class="form-group">
                    <label class="form-label" x-text="editUserId ? 'Password (kosongkan jika tidak diubah)' : 'Password *'"></label>
                    <input type="password" x-model="userForm.password" class="form-input" :required="!editUserId">
                </div>
                <div class="form-group">
                    <label class="form-label">Role *</label>
                    <select x-model="userForm.role" class="form-input" required>
                        <option value="bos">Bos</option>
                        <option value="admin">Admin</option>
                        <option value="checker">Checker</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Gudang</label>
                    <select x-model="userForm.id_gudang" class="form-input">
                        <option value="">-- Semua Gudang --</option>
                        <template x-for="g in gudang" :key="g.id"><option :value="g.id" x-text="g.nama"></option></template>
                    </select>
                </div>
                <div class="flex gap-3 mt-4">
                    <button type="submit" class="btn btn-primary" :disabled="saving" x-text="saving ? 'Menyimpan...' : (editUserId ? 'Update' : 'Simpan')"></button>
                    <button type="button" @click="showUserModal = false" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $scripts = <<<'JS'
<script>
function settingsPage() {
    return {
        user: JSON.parse(localStorage.getItem('user') || '{}'),
        tab: 'umum',
        settings: [],
        users: [],
        gudang: [],
        showUserModal: false,
        editUserId: null,
        saving: false,
        userForm: { name: '', email: '', password: '', role: 'admin', id_gudang: '' },

        async init() {
            if (this.user.role !== 'bos') { window.location.href = '/peace_seafood/dashboard'; return; }
            await this.loadAll();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async loadAll() {
            const token = localStorage.getItem('token'); const headers = { Authorization: 'Bearer ' + token };
            const [setRes, usrRes, gudRes] = await Promise.all([
                axios.get('/peace_seafood/api/settings', { headers }),
                axios.get('/peace_seafood/api/settings/users', { headers }),
                axios.get('/peace_seafood/api/settings/gudang', { headers }),
            ]);
            this.settings = setRes.data?.data || [];
            this.users    = usrRes.data?.data || [];
            this.gudang   = gudRes.data?.data || [];
        },

        /* ── Field type helpers ── */
        isToggle(kunci) {
            return ['multi_warehouse_aktif', 'backup_otomatis', 'onboarding_wizard_aktif'].includes(kunci);
        },

        isSelect(kunci) {
            return ['komisi_penitipan_tipe', 'harga_locked_untuk', 'export_permission'].includes(kunci);
        },

        isNumber(kunci) {
            return ['stok_minimum_threshold', 'susut_alert_threshold', 'komisi_penitipan_persen',
                    'pajak_default_persen', 'jatuh_tempo_default_hari', 'session_timeout_menit'].includes(kunci);
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
                await axios.put('/peace_seafood/api/settings/' + setting.kunci, { nilai: setting.nilai }, { headers: { Authorization: 'Bearer ' + token } });
                iziToast.success({ title: 'Berhasil', message: 'Setting disimpan', position: 'topRight' });
            } catch(e) { iziToast.error({ title: 'Error', message: 'Gagal simpan', position: 'topRight' }); }
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
            if (!confirm('Hapus user ini?')) return;
            const token = localStorage.getItem('token');
            await axios.delete('/peace_seafood/api/settings/users/' + id, { headers: { Authorization: 'Bearer ' + token } });
            iziToast.success({ title: 'Berhasil', message: 'User dihapus', position: 'topRight' }); await this.loadAll();
        },
        openAddGudang() { iziToast.info({ title: 'Info', message: 'Fitur tambah gudang akan segera tersedia', position: 'topRight' }); },
        openEditGudang(g) { iziToast.info({ title: 'Info', message: 'Fitur edit gudang akan segera tersedia', position: 'topRight' }); },
    };
}
</script>
JS;
?>
