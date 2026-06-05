<?php
/**
 * Tab 2: Email & Notification Configuration
 */
?>

<!-- SMTP Configuration Section -->
<div class="settings-section">
    <div class="settings-section-title">
        <i data-lucide="mail" class="w-5 h-5 text-blue-500"></i>
        Konfigurasi SMTP
    </div>
    <p class="settings-section-desc">
        Pengaturan email pengirim untuk notifikasi dan reminder ke tenant
    </p>

    <div class="settings-grid">
        <div class="settings-field">
            <label class="settings-label">Email Pengirim (Gmail)</label>
            <input type="email" 
                   x-model="email.user" 
                   class="settings-input"
                   placeholder="your-email@gmail.com">
            <p class="settings-hint">Email Gmail yang akan digunakan sebagai pengirim</p>
        </div>

        <div class="settings-field">
            <label class="settings-label">App Password Gmail</label>
            <div style="display: flex; gap: 0.5rem;">
                <input :type="showPassword ? 'text' : 'password'" 
                       x-model="email.pass" 
                       class="settings-input"
                       style="flex: 1;"
                       placeholder="••••••••">
                <button type="button" 
                        @click="showPassword = !showPassword"
                        class="settings-btn settings-btn-secondary"
                        style="padding: 0.625rem;">
                    <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                </button>
            </div>
            <p class="settings-hint">
                Bukan password biasa! Generate App Password di <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: var(--color-primary); text-decoration: underline;">Google Account</a>
            </p>
        </div>
    </div>

    <div class="settings-grid">
        <div class="settings-field">
            <label class="settings-label">Nama Pengirim</label>
            <input type="text" 
                   x-model="email.from_name" 
                   class="settings-input"
                   placeholder="Peace Seafood WMS">
            <p class="settings-hint">Nama yang tampil sebagai pengirim di inbox</p>
        </div>

        <div class="settings-field">
            <label class="settings-label">
                <span>Test Email</span>
            </label>
            <button type="button" 
                    class="settings-btn settings-btn-secondary"
                    style="width: 100%;"
                    @click="testEmailConfig()"
                    :disabled="testingEmail">
                <i data-lucide="send" class="w-4 h-4"></i>
                <span x-text="testingEmail ? 'Mengirim...' : 'Kirim Test Email ke Saya'"></span>
            </button>
            <p class="settings-hint">Kirim test email untuk verifikasi konfigurasi SMTP</p>
        </div>
    </div>

    <div class="settings-field">
        <label class="settings-label">Footer Email</label>
        <textarea x-model="email.footer" 
                  class="settings-input settings-textarea"
                  placeholder="Peace Seafood WMS - Sistem Manajemen Gudang Ikan&#10;Jl. Contoh No. 123, Jakarta"></textarea>
        <p class="settings-hint">Footer yang tampil di bagian bawah setiap email (alamat bisnis, disclaimer)</p>
    </div>
</div>

<!-- Notification Toggles Section -->
<div class="settings-section">
    <div class="settings-section-title">
        <i data-lucide="bell" class="w-5 h-5 text-orange-500"></i>
        Toggle Notifikasi
    </div>
    <p class="settings-section-desc">
        Aktifkan atau nonaktifkan jenis notifikasi tertentu
    </p>

    <div class="settings-field" style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <div class="settings-label" style="margin-bottom: 0.25rem;">Email Reminder ke Tenant</div>
            <p class="settings-hint" style="margin-top: 0;">Email otomatis H-7, H-3, H-1 sebelum expired</p>
        </div>
        <label class="settings-toggle">
            <input type="checkbox" 
                   x-model="email.reminder"
                   @change="toggleSetting('notification_email_reminder', email.reminder)">
            <span class="settings-toggle-slider"></span>
        </label>
    </div>

    <div class="settings-field" style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <div class="settings-label" style="margin-bottom: 0.25rem;">Email Digest Harian ke SaaS Owner</div>
            <p class="settings-hint" style="margin-top: 0;">Ringkasan harian aktivitas platform</p>
        </div>
        <label class="settings-toggle">
            <input type="checkbox" 
                   x-model="email.digest"
                   @change="toggleSetting('notification_digest', email.digest)">
            <span class="settings-toggle-slider"></span>
        </label>
    </div>

    <div class="settings-field" style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <div class="settings-label" style="margin-bottom: 0.25rem;">Notifikasi In-App Bell Icon</div>
            <p class="settings-hint" style="margin-top: 0;">Notifikasi real-time di navbar dengan polling</p>
        </div>
        <label class="settings-toggle">
            <input type="checkbox" 
                   x-model="email.inapp"
                   @change="toggleSetting('notification_inapp', email.inapp)">
            <span class="settings-toggle-slider"></span>
        </label>
    </div>

    <div class="settings-field">
        <label class="settings-label">Jam Kirim Digest Harian</label>
        <input type="time" 
               x-model="email.digest_time" 
               class="settings-input"
               style="max-width: 200px;">
        <p class="settings-hint">Waktu pengiriman email digest ke SaaS Owner (format 24 jam)</p>
    </div>
</div>

<!-- Footer Buttons -->
<div class="settings-footer">
    <button type="button" 
            class="settings-btn settings-btn-primary"
            @click="saveTab('email')"
            :disabled="saving">
        <i data-lucide="save" class="w-4 h-4"></i>
        <span x-text="saving ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
    </button>
</div>

<script>
    function testEmailConfig() {
        if (this.testingEmail) return;
        
        this.testingEmail = true;
        
        apiClient.post('/api/saas/settings/test-email', {
            email: this.email.user || localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')).email : ''
        })
        .then(response => {
            if (response.data.success) {
                iziToast.success({ 
                    title: 'Sukses', 
                    message: response.data.message,
                    timeout: 5000
                });
            } else {
                iziToast.error({ title: 'Error', message: response.data.message });
            }
        })
        .catch(error => {
            console.error('Test email error:', error);
            iziToast.error({ title: 'Error', message: 'Gagal mengirim test email' });
        })
        .finally(() => {
            this.testingEmail = false;
        });
    }

    // Add to Alpine data
    document.addEventListener('alpine:init', () => {
        Alpine.data('platformSettings', () => ({
            ...platformSettings(),
            showPassword: false,
            testingEmail: false,
            testEmailConfig
        }));
    });
</script>

