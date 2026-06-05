<?php
/**
 * Tab 1: Platform Identity & Lock Screen Messages
 */
?>

<!-- Platform Identity Section -->
<div class="settings-section">
    <div class="settings-section-title">
        <i data-lucide="building-2" class="w-5 h-5 text-blue-500"></i>
        Identitas Platform
    </div>
    <p class="settings-section-desc">
        Konfigurasi identitas dasar platform yang akan tampil di seluruh sistem
    </p>

    <div class="settings-grid">
        <div class="settings-field">
            <label class="settings-label">Nama Platform</label>
            <input type="text" 
                   x-model="platform.name" 
                   class="settings-input"
                   placeholder="Peace Seafood WMS">
            <p class="settings-hint">Nama yang tampil di email, UI, dan notifikasi</p>
        </div>

        <div class="settings-field">
            <label class="settings-label">Nomor WhatsApp Bisnis</label>
            <input type="text" 
                   x-model="platform.whatsapp" 
                   class="settings-input"
                   placeholder="628123456789">
            <p class="settings-hint">Format: 628xxx (tanpa +, 0, atau spasi)</p>
        </div>
    </div>

    <div class="settings-field">
        <label class="settings-label">Warna Tema Utama</label>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <input type="color" 
                   x-model="platform.color" 
                   style="width: 80px; height: 40px; border-radius: 0.5rem; border: 1px solid var(--border-color); cursor: pointer;">
            <input type="text" 
                   x-model="platform.color" 
                   class="settings-input"
                   style="flex: 1; max-width: 200px;"
                   placeholder="#2563eb">
            <div style="width: 40px; height: 40px; border-radius: 0.5rem; border: 1px solid var(--border-color);" 
                 :style="'background: ' + platform.color"></div>
        </div>
        <p class="settings-hint">Warna utama yang digunakan di seluruh interface</p>
    </div>

    <div class="settings-field">
        <label class="settings-label">Logo Platform</label>
        <div class="settings-image-upload" @click="$refs.logoInput.click()">
            <template x-if="platform.logo">
                <img :src="platform.logo" class="settings-image-preview" alt="Logo Preview">
            </template>
            <template x-if="!platform.logo">
                <div>
                    <i data-lucide="upload" class="w-8 h-8 mx-auto mb-2" style="color: var(--text-secondary)"></i>
                    <p style="color: var(--text-secondary); font-size: 0.875rem;">Klik untuk upload logo</p>
                    <p style="color: var(--text-secondary); font-size: 0.75rem; margin-top: 0.5rem;">Format: JPG, PNG (Max 5MB)</p>
                </div>
            </template>
        </div>
        <input type="file" 
               x-ref="logoInput" 
               accept="image/jpeg,image/png,image/jpg"
               style="display: none;"
               @change="uploadImage($event, 'platform_logo')">
        <p class="settings-hint">Logo akan ditampilkan di sidebar, email, dan halaman login</p>
    </div>
</div>

<!-- Lock Screen Messages Section -->
<div class="settings-section">
    <div class="settings-section-title">
        <i data-lucide="lock" class="w-5 h-5 text-orange-500"></i>
        Pesan Kustom Lock Screen
    </div>
    <p class="settings-section-desc">
        Kustomisasi pesan yang ditampilkan saat gudang tenant terkunci
    </p>

    <div class="settings-field">
        <label class="settings-label">Judul Lock Screen</label>
        <input type="text" 
               x-model="platform.lock_title" 
               class="settings-input"
               placeholder="Masa Aktif Anda Telah Habis">
        <p class="settings-hint">Judul utama yang tampil di lock screen</p>
    </div>

    <div class="settings-field">
        <label class="settings-label">Pesan Utama</label>
        <textarea x-model="platform.lock_message" 
                  class="settings-input settings-textarea"
                  placeholder="Silakan lakukan pembayaran untuk melanjutkan akses ke sistem"></textarea>
        <p class="settings-hint">Pesan yang menjelaskan situasi kepada tenant</p>
    </div>

    <div class="settings-field">
        <label class="settings-label">Instruksi Pembayaran</label>
        <textarea x-model="platform.lock_steps" 
                  class="settings-input settings-textarea"
                  placeholder="1. Transfer ke rekening yang tertera&#10;2. Upload bukti pembayaran&#10;3. Tunggu verifikasi admin"></textarea>
        <p class="settings-hint">Step-by-step instruksi pembayaran (gunakan Enter untuk line break)</p>
    </div>

    <div class="settings-field">
        <label class="settings-label">Pesan Setelah Submit</label>
        <textarea x-model="platform.lock_after" 
                  class="settings-input settings-textarea"
                  placeholder="Terima kasih! Bukti pembayaran Anda sedang diverifikasi..."></textarea>
        <p class="settings-hint">Pesan konfirmasi setelah tenant upload bukti bayar</p>
    </div>
</div>

<!-- Footer Buttons -->
<div class="settings-footer">
    <button type="button" 
            class="settings-btn settings-btn-primary"
            @click="saveTab('platform')"
            :disabled="saving">
        <i data-lucide="save" class="w-4 h-4"></i>
        <span x-text="saving ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
    </button>
</div>

<script>
    function uploadImage(event, settingKey) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate
        if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
            iziToast.error({ title: 'Error', message: 'Format file harus JPG atau PNG' });
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            iziToast.error({ title: 'Error', message: 'Ukuran file maksimal 5MB' });
            return;
        }

        // Show loading
        iziToast.info({ title: 'Upload', message: 'Mengupload gambar...' });

        // Upload via FormData
        const formData = new FormData();
        formData.append('image', file);
        formData.append('setting_key', settingKey);

        fetch('/peace_seafood/api/saas/settings/upload', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                iziToast.success({ title: 'Sukses', message: data.message });
                // Update preview
                if (settingKey === 'platform_logo') {
                    Alpine.store('platformSettings').platform.logo = data.data.base64;
                } else if (settingKey === 'payment_qris_image') {
                    Alpine.store('platformSettings').payment.qris = data.data.base64;
                }
                // Reload settings to get updated data
                location.reload();
            } else {
                iziToast.error({ title: 'Error', message: data.message });
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            iziToast.error({ title: 'Error', message: 'Gagal mengupload gambar' });
        });
    }
</script>

