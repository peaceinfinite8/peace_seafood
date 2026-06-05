<?php
/**
 * Platform Settings Manager - Main Page
 * 4 tabs: Platform, Email & Notifications, Payment, System
 */
?>

<style>
    /* Settings Page Styles */
    .settings-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .settings-tabs {
        display: flex;
        gap: 0.5rem;
        border-bottom: 2px solid var(--border-color);
        margin-bottom: 2rem;
        overflow-x: auto;
    }

    .settings-tab {
        padding: 0.75rem 1.5rem;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-secondary);
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .settings-tab:hover {
        color: var(--color-primary);
    }

    .settings-tab.active {
        color: var(--color-primary);
        border-bottom-color: var(--color-primary);
    }

    .settings-tab-content {
        display: none;
    }

    .settings-tab-content.active {
        display: block;
    }

    .settings-section {
        background: var(--bg-light);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .settings-section-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .settings-section-desc {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
    }

    .settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .settings-field {
        margin-bottom: 1.5rem;
    }

    .settings-field:last-child {
        margin-bottom: 0;
    }

    .settings-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .settings-hint {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-top: 0.25rem;
    }

    .settings-input {
        width: 100%;
        padding: 0.625rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        background: var(--bg-light);
        color: var(--text-primary);
        font-size: 0.875rem;
        transition: border-color 0.2s;
    }

    .settings-input:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px var(--color-primary-light);
    }

    .settings-textarea {
        min-height: 100px;
        resize: vertical;
    }

    .settings-footer {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
        margin-top: 2rem;
    }

    .settings-btn {
        padding: 0.625rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: none;
    }

    .settings-btn-primary {
        background: var(--color-primary);
        color: white;
    }

    .settings-btn-primary:hover {
        background: var(--color-primary-dark);
    }

    .settings-btn-secondary {
        background: var(--bg-secondary);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
    }

    .settings-btn-secondary:hover {
        background: var(--border-color);
    }

    .settings-toggle {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 24px;
    }

    .settings-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .settings-toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: 0.3s;
        border-radius: 24px;
    }

    .settings-toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
    }

    .settings-toggle input:checked + .settings-toggle-slider {
        background-color: var(--color-primary);
    }

    .settings-toggle input:checked + .settings-toggle-slider:before {
        transform: translateX(24px);
    }

    .settings-image-upload {
        border: 2px dashed var(--border-color);
        border-radius: 0.75rem;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .settings-image-upload:hover {
        border-color: var(--color-primary);
        background: var(--color-primary-light);
    }

    .settings-image-preview {
        max-width: 200px;
        max-height: 200px;
        margin: 1rem auto;
        border-radius: 0.5rem;
        border: 1px solid var(--border-color);
    }

    @media (max-width: 768px) {
        .settings-grid {
            grid-template-columns: 1fr;
        }

        .settings-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
        }
    }
</style>

<div x-data="platformSettings()" x-init="init()" class="settings-container">
    <!-- Loading State -->
    <div x-show="loading" class="flex items-center justify-center py-20">
        <div class="animate-spin w-8 h-8 rounded-full border-4 border-blue-200" style="border-top-color: var(--color-primary)"></div>
    </div>

    <!-- Settings Content -->
    <div x-show="!loading" x-cloak>
        <!-- Tabs -->
        <div class="settings-tabs">
            <div class="settings-tab" :class="activeTab === 'platform' ? 'active' : ''" @click="activeTab = 'platform'">
                <i data-lucide="building-2" class="w-4 h-4"></i>
                Platform
            </div>
            <div class="settings-tab" :class="activeTab === 'email' ? 'active' : ''" @click="activeTab = 'email'">
                <i data-lucide="mail" class="w-4 h-4"></i>
                Email & Notifikasi
            </div>
            <div class="settings-tab" :class="activeTab === 'payment' ? 'active' : ''" @click="activeTab = 'payment'">
                <i data-lucide="credit-card" class="w-4 h-4"></i>
                Pembayaran
            </div>
            <div class="settings-tab" :class="activeTab === 'system' ? 'active' : ''" @click="activeTab = 'system'">
                <i data-lucide="settings" class="w-4 h-4"></i>
                Sistem
            </div>
        </div>

        <!-- Tab: Platform -->
        <div class="settings-tab-content" :class="activeTab === 'platform' ? 'active' : ''">
            <?php include __DIR__ . '/tab_platform.php'; ?>
        </div>

        <!-- Tab: Email & Notifications -->
        <div class="settings-tab-content" :class="activeTab === 'email' ? 'active' : ''">
            <?php include __DIR__ . '/tab_email.php'; ?>
        </div>

        <!-- Tab: Payment -->
        <div class="settings-tab-content" :class="activeTab === 'payment' ? 'active' : ''">
            <?php include __DIR__ . '/tab_pembayaran.php'; ?>
        </div>

        <!-- Tab: System -->
        <div class="settings-tab-content" :class="activeTab === 'system' ? 'active' : ''">
            <?php include __DIR__ . '/tab_sistem.php'; ?>
        </div>
    </div>
</div>

<script>
    function platformSettings() {
        return {
            loading: true,
            saving: false,
            activeTab: 'platform',
            settings: {},
            
            // Form data
            platform: {},
            email: {},
            payment: {},
            system: {},

            async init() {
                await this.loadSettings();
                this.loading = false;
            },

            async loadSettings() {
                try {
                    const response = await apiClient.get('/api/saas/settings');
                    if (response.data.success) {
                        this.settings = response.data.data;
                        this.populateFormData();
                    }
                } catch (error) {
                    console.error('Failed to load settings:', error);
                    iziToast.error({ title: 'Error', message: 'Gagal memuat pengaturan' });
                }
            },

            populateFormData() {
                // Populate platform tab
                this.platform.name = this.getSettingValue('platform_name');
                this.platform.logo = this.getSettingValue('platform_logo');
                this.platform.color = this.getSettingValue('platform_color');
                this.platform.whatsapp = this.getSettingValue('platform_whatsapp');
                this.platform.lock_title = this.getSettingValue('lock_screen_title');
                this.platform.lock_message = this.getSettingValue('lock_screen_message');
                this.platform.lock_steps = this.getSettingValue('lock_screen_payment_steps');
                this.platform.lock_after = this.getSettingValue('lock_screen_after_submit');

                // Populate email tab
                this.email.user = this.getSettingValue('mail_user');
                this.email.pass = this.getSettingValue('mail_pass');
                this.email.from_name = this.getSettingValue('mail_from_name');
                this.email.footer = this.getSettingValue('mail_footer');
                this.email.reminder = this.getSettingValue('notification_email_reminder') === '1';
                this.email.digest = this.getSettingValue('notification_digest') === '1';
                this.email.inapp = this.getSettingValue('notification_inapp') === '1';
                this.email.digest_time = this.getSettingValue('digest_send_time');

                // Populate payment tab
                this.payment.bank_name = this.getSettingValue('payment_bank_name');
                this.payment.bank_holder = this.getSettingValue('payment_bank_holder');
                this.payment.bank_number = this.getSettingValue('payment_bank_number');
                this.payment.qris = this.getSettingValue('payment_qris_image');
                this.payment.price = this.getSettingValue('payment_monthly_price');
                this.payment.max_upload = this.getSettingValue('payment_max_upload_mb');
                this.payment.formats = this.getSettingValue('payment_allowed_formats');
                this.payment.magic_expiry = this.getSettingValue('payment_magic_link_expiry_hours');

                // Populate system tab
                this.system.trial_days = this.getSettingValue('trial_duration_days');
                this.system.renewal_days = this.getSettingValue('renewal_duration_days');
                this.system.warning_1 = this.getSettingValue('grace_warning_day_1');
                this.system.warning_2 = this.getSettingValue('grace_warning_day_2');
                this.system.warning_3 = this.getSettingValue('grace_warning_day_3');
                this.system.yellow_threshold = this.getSettingValue('grace_yellow_days');
                this.system.red_threshold = this.getSettingValue('grace_red_days');
                this.system.timezone = this.getSettingValue('timezone');
                this.system.language = this.getSettingValue('language');
                this.system.maintenance = this.getSettingValue('maintenance_mode') === '1';
                this.system.maintenance_msg = this.getSettingValue('maintenance_message');
            },

            getSettingValue(key) {
                for (let category in this.settings) {
                    const setting = this.settings[category].find(s => s.kunci === key);
                    if (setting) {
                        return setting.nilai_display || setting.nilai || '';
                    }
                }
                return '';
            },

            async saveTab(tabName) {
                this.saving = true;
                let data = {};

                // Map tab data to settings keys
                switch(tabName) {
                    case 'platform':
                        data = {
                            'platform_name': this.platform.name,
                            'platform_color': this.platform.color,
                            'platform_whatsapp': this.platform.whatsapp,
                            'lock_screen_title': this.platform.lock_title,
                            'lock_screen_message': this.platform.lock_message,
                            'lock_screen_payment_steps': this.platform.lock_steps,
                            'lock_screen_after_submit': this.platform.lock_after
                        };
                        break;
                    case 'email':
                        data = {
                            'mail_user': this.email.user,
                            'mail_from_name': this.email.from_name,
                            'mail_footer': this.email.footer,
                            'digest_send_time': this.email.digest_time
                        };
                        // Add password only if changed
                        if (this.email.pass && this.email.pass !== '••••••••') {
                            data['mail_pass'] = this.email.pass;
                        }
                        break;
                    case 'payment':
                        data = {
                            'payment_bank_name': this.payment.bank_name,
                            'payment_bank_holder': this.payment.bank_holder,
                            'payment_bank_number': this.payment.bank_number,
                            'payment_monthly_price': this.payment.price,
                            'payment_max_upload_mb': this.payment.max_upload,
                            'payment_allowed_formats': this.payment.formats,
                            'payment_magic_link_expiry_hours': this.payment.magic_expiry
                        };
                        break;
                    case 'system':
                        data = {
                            'trial_duration_days': this.system.trial_days,
                            'renewal_duration_days': this.system.renewal_days,
                            'grace_warning_day_1': this.system.warning_1,
                            'grace_warning_day_2': this.system.warning_2,
                            'grace_warning_day_3': this.system.warning_3,
                            'grace_yellow_days': this.system.yellow_threshold,
                            'grace_red_days': this.system.red_threshold,
                            'timezone': this.system.timezone,
                            'language': this.system.language,
                            'maintenance_message': this.system.maintenance_msg
                        };
                        break;
                }

                try {
                    const response = await apiClient.post('/api/saas/settings/save', data);
                    if (response.data.success) {
                        iziToast.success({ title: 'Sukses', message: '✅ Pengaturan berhasil disimpan' });
                        await this.loadSettings();
                    }
                } catch (error) {
                    console.error('Save error:', error);
                    iziToast.error({ title: 'Error', message: 'Gagal menyimpan pengaturan' });
                } finally {
                    this.saving = false;
                }
            },

            async toggleSetting(key, value) {
                try {
                    await apiClient.post('/api/saas/settings/save', {
                        key: key,
                        value: value ? '1' : '0'
                    });
                    iziToast.success({ title: 'Sukses', message: 'Pengaturan diperbarui' });
                } catch (error) {
                    console.error('Toggle error:', error);
                    iziToast.error({ title: 'Error', message: 'Gagal mengubah pengaturan' });
                }
            }
        };
    }
</script>

