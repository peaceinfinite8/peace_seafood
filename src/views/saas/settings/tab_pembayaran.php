<?php
/**
 * Tab 3: Payment Configuration
 */
?>

<!-- Bank Account Section -->
<div class="settings-section">
    <div class="settings-section-title">
        <i data-lucide="building" class="w-5 h-5 text-green-500"></i>
        Rekening Bank
    </div>
    <p class="settings-section-desc">
        Informasi rekening bank untuk menerima pembayaran dari tenant
    </p>

    <div class="settings-grid">
        <div class="settings-field">
            <label class="settings-label">Nama Bank</label>
            <input type="text" 
                   x-model="payment.bank_name" 
                   class="settings-input"
                   placeholder="Bank Mandiri">
            <p class="settings-hint">Contoh: Bank Mandiri, BCA, BNI</p>
        </div>

        <div class="settings-field">
            <label class="settings-label">Nama Pemilik Rekening</label>
            <input type="text" 
                   x-model="payment.bank_holder" 
                   class="settings-input"
                   placeholder="PT Peace Seafood Indonesia">
            <p class="settings-hint">Nama lengkap sesuai rekening bank</p>
        </div>
    </div>

    <div class="settings-field">
        <label class="settings-label">Nomor Rekening</label>
        <input type="text" 
               x-model="payment.bank_number" 
               class="settings-input"
               style="max-width: 300px;"
               placeholder="1234567890">
        <p class="settings-hint">Nomor rekening tanpa spasi atau tanda hubung</p>
    </div>
</div>

<!-- QRIS Section -->
<div class="settings-section">
    <div class="settings-section-title">
        <i data-lucide="qr-code" class="w-5 h-5 text-purple-500"></i>
        QRIS
    </div>
    <p class="settings-section-desc">
        Upload foto QR Code untuk pembayaran via QRIS
    </p>

    <div class="settings-field">
        <label class="settings-label">Foto QRIS</label>
        <div class="settings-image-upload" @click="$refs.qrisInput.click()">
            <template x-if="payment.qris">
                <img :src="payment.qris" class="settings-image-preview" alt="QRIS Preview">
            </template>
            <template x-if="!payment.qris">
                <div>
                    <i data-lucide="upload" class="w-8 h-8 mx-auto mb-2" style="color: var(--text-secondary)"></i>
                    <p style="color: var(--text-secondary); font-size: 0.875rem;">Klik untuk upload QRIS</p>
                    <p style="color: var(--text-secondary); font-size: 0.75rem; margin-top: 0.5rem;">Format: JPG, PNG (Max 5MB)</p>
                </div>
            </template>
        </div>
        <input type="file" 
               x-ref="qrisInput" 
               accept="image/jpeg,image/png,image/jpg"
               style="display: none;"
               @change="uploadImage($event, 'payment_qris_image')">
        <p class="settings-hint">Upload screenshot atau foto QR Code dari aplikasi banking</p>
    </div>
</div>

<!-- Pricing & Upload Policy Section -->
<div class="settings-section">
    <div class="settings-section-title">
        <i data-lucide="dollar-sign" class="w-5 h-5 text-blue-500"></i>
        Harga & Kebijakan Upload
    </div>
    <p class="settings-section-desc">
        Konfigurasi harga sewa dan aturan upload bukti pembayaran
    </p>

    <div class="settings-grid">
        <div class="settings-field">
            <label class="settings-label">Nominal Sewa per Periode</label>
            <div style="position: relative;">
                <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-weight: 600;">Rp</span>
                <input type="number" 
                       x-model="payment.price" 
                       class="settings-input"
                       style="padding-left: 2.5rem;"
                       placeholder="500000"
                       min="0"
                       step="1000">
            </div>
            <p class="settings-hint">Harga sewa per bulan/periode dalam rupiah</p>
        </div>

        <div class="settings-field">
            <label class="settings-label">Maksimal Ukuran File Upload (MB)</label>
            <input type="number" 
                   x-model="payment.max_upload" 
                   class="settings-input"
                   placeholder="5"
                   min="1"
                   max="10">
            <p class="settings-hint">Ukuran maksimal file bukti bayar (1-10 MB)</p>
        </div>
    </div>

    <div class="settings-field">
        <label class="settings-label">Format File yang Diizinkan</label>
        <div style="display: flex; gap: 1.5rem; margin-top: 0.5rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" 
                       :checked="payment.formats.includes('jpg')"
                       @change="toggleFormat('jpg', $event.target.checked)"
                       style="width: 18px; height: 18px; cursor: pointer;">
                <span style="color: var(--text-primary); font-size: 0.875rem;">JPG</span>
            </label>
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" 
                       :checked="payment.formats.includes('png')"
                       @change="toggleFormat('png', $event.target.checked)"
                       style="width: 18px; height: 18px; cursor: pointer;">
                <span style="color: var(--text-primary); font-size: 0.875rem;">PNG</span>
            </label>
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" 
                       :checked="payment.formats.includes('pdf')"
                       @change="toggleFormat('pdf', $event.target.checked)"
                       style="width: 18px; height: 18px; cursor: pointer;">
                <span style="color: var(--text-primary); font-size: 0.875rem;">PDF</span>
            </label>
        </div>
        <p class="settings-hint">Format file yang dapat diupload oleh tenant</p>
    </div>

    <div class="settings-field">
        <label class="settings-label">Durasi Kadaluarsa Magic Link (Jam)</label>
        <input type="number" 
               x-model="payment.magic_expiry" 
               class="settings-input"
               style="max-width: 200px;"
               placeholder="24"
               min="1"
               max="72">
        <p class="settings-hint">Berapa lama link approval pembayaran valid sebelum kadaluarsa</p>
    </div>
</div>

<!-- Footer Buttons -->
<div class="settings-footer">
    <button type="button" 
            class="settings-btn settings-btn-primary"
            @click="saveTab('payment')"
            :disabled="saving">
        <i data-lucide="save" class="w-4 h-4"></i>
        <span x-text="saving ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
    </button>
</div>

<script>
    function toggleFormat(format, checked) {
        let formats = this.payment.formats.split(',').filter(f => f.trim());
        
        if (checked) {
            if (!formats.includes(format)) {
                formats.push(format);
            }
        } else {
            formats = formats.filter(f => f !== format);
        }
        
        this.payment.formats = formats.join(',');
    }

    // Add to Alpine data
    document.addEventListener('alpine:init', () => {
        Alpine.data('platformSettings', () => ({
            ...platformSettings(),
            toggleFormat
        }));
    });
</script>

