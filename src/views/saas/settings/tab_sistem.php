<?php
/**
 * Tab 4: System Configuration
 */
?>

<!-- Duration Settings Section -->
<div class="settings-section">
    <div class="settings-section-title">
        <i data-lucide="clock" class="w-5 h-5 text-blue-500"></i>
        Durasi & Periode
    </div>
    <p class="settings-section-desc">
        Konfigurasi durasi trial, perpanjangan, dan peringatan grace period
    </p>

    <div class="settings-grid">
        <div class="settings-field">
            <label class="settings-label">Durasi Trial Tenant Baru (Hari)</label>
            <input type="number" 
                   x-model="system.trial_days" 
                   class="settings-input"
                   placeholder="14"
                   min="1"
                   max="365">
            <p class="settings-hint">Durasi gratis setelah onboarding selesai</p>
        </div>

        <div class="settings-field">
            <label class="settings-label">Durasi Perpanjangan per Pembayaran (Hari)</label>
            <input type="number" 
                   x-model="system.renewal_days" 
                   class="settings-input"
                   placeholder="30"
                   min="1"
                   max="365">
            <p class="settings-hint">Berapa hari ditambahkan setelah pembayaran disetujui</p>
        </div>
    </div>

    <div style="border-top: 1px solid var(--border-color); margin: 1.5rem 0; padding-top: 1.5rem;">
        <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1rem;">
            Jadwal Email Reminder
        </h4>

        <div class="settings-grid">
            <div class="settings-field">
                <label class="settings-label">Peringatan Pertama (H-?)</label>
                <input type="number" 
                       x-model="system.warning_1" 
                       class="settings-input"
                       placeholder="7"
                       min="1"
                       max="30">
                <p class="settings-hint">Kirim email pertama H-? hari sebelum expired</p>
            </div>

            <div class="settings-field">
                <label class="settings-label">Peringatan Kedua (H-?)</label>
                <input type="number" 
                       x-model="system.warning_2" 
                       class="settings-input"
                       placeholder="3"
                       min="1"
                       max="30">
                <p class="settings-hint">Kirim email kedua H-? hari sebelum expired</p>
            </div>

            <div class="settings-field">
                <label class="settings-label">Peringatan Ketiga (H-?)</label>
                <input type="number" 
                       x-model="system.warning_3" 
                       class="settings-input"
                       placeholder="1"
                       min="1"
                       max="30">
                <p class="settings-hint">Kirim email ketiga H-? hari sebelum expired</p>
            </div>
        </div>
    </div>

    <div style="border-top: 1px solid var(--border-color); margin: 1.5rem 0; padding-top: 1.5rem;">
        <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1rem;">
            Threshold Banner Peringatan
        </h4>

        <div class="settings-grid">
            <div class="settings-field">
                <label class="settings-label">Banner Kuning Muncul (≤ Hari)</label>
                <input type="number" 
                       x-model="system.yellow_threshold" 
                       class="settings-input"
                       placeholder="7"
                       min="1"
                       max="30">
                <p class="settings-hint">Banner kuning muncul saat sisa hari ≤ nilai ini</p>
            </div>

            <div class="settings-field">
                <label class="settings-label">Banner Merah Muncul (≤ Hari)</label>
                <input type="number" 
                       x-model="system.red_threshold" 
                       class="settings-input"
                       placeholder="3"
                       min="1"
                       max="30">
                <p class="settings-hint">Banner merah muncul saat sisa hari ≤ nilai ini</p>
            </div>
        </div>
    </div>
</div>

<!-- Localization Section -->
<div class="settings-section">
    <div class="settings-section-title">
        <i data-lucide="globe" class="w-5 h-5 text-green-500"></i>
        Lokalisasi
    </div>
    <p class="settings-section-desc">
        Pengaturan zona waktu dan bahasa sistem
    </p>

    <div class="settings-grid">
        <div class="settings-field">
            <label class="settings-label">Zona Waktu</label>
            <select x-model="system.timezone" class="settings-input">
                <option value="Asia/Jakarta">Asia/Jakarta (WIB - UTC+7)</option>
                <option value="Asia/Makassar">Asia/Makassar (WITA - UTC+8)</option>
                <option value="Asia/Jayapura">Asia/Jayapura (WIT - UTC+9)</option>
                <option value="UTC">UTC (UTC+0)</option>
            </select>
            <p class="settings-hint">Zona waktu yang digunakan untuk timestamp sistem</p>
        </div>

        <div class="settings-field">
            <label class="settings-label">Bahasa Sistem</label>
            <select x-model="system.language" class="settings-input">
                <option value="id">Indonesia</option>
                <option value="en">English</option>
            </select>
            <p class="settings-hint">Bahasa interface sistem (fitur multi-bahasa coming soon)</p>
        </div>
    </div>
</div>

<!-- Maintenance Mode Section -->
<div class="settings-section">
    <div class="settings-section-title">
        <i data-lucide="wrench" class="w-5 h-5 text-orange-500"></i>
        Maintenance Mode
    </div>
    <p class="settings-section-desc">
        Aktifkan maintenance mode untuk menutup akses sementara ke semua tenant
    </p>

    <div class="settings-field" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: rgba(245, 158, 11, 0.1); border-radius: 0.5rem; border: 1px solid rgba(245, 158, 11, 0.3);">
        <div>
            <div class="settings-label" style="margin-bottom: 0.25rem; color: #d97706;">
                <i data-lucide="alert-triangle" class="w-4 h-4" style="display: inline-block; vertical-align: middle;"></i>
                Status Maintenance Mode
            </div>
            <p class="settings-hint" style="margin-top: 0; color: #92400e;">
                Saat aktif, semua tenant akan melihat halaman maintenance
            </p>
        </div>
        <label class="settings-toggle">
            <input type="checkbox" 
                   x-model="system.maintenance"
                   @change="toggleSetting('maintenance_mode', system.maintenance)">
            <span class="settings-toggle-slider"></span>
        </label>
    </div>

    <div class="settings-field">
        <label class="settings-label">Pesan Maintenance</label>
        <textarea x-model="system.maintenance_msg" 
                  class="settings-input settings-textarea"
                  placeholder="Sistem sedang dalam pemeliharaan. Kami akan kembali sebentar lagi."></textarea>
        <p class="settings-hint">Pesan yang ditampilkan kepada tenant saat maintenance mode aktif</p>
    </div>

    <div x-show="system.maintenance" 
         style="padding: 1rem; background: rgba(239, 68, 68, 0.1); border-radius: 0.5rem; border: 1px solid rgba(239, 68, 68, 0.3);">
        <div style="display: flex; align-items: start; gap: 0.75rem;">
            <i data-lucide="alert-circle" class="w-5 h-5" style="color: #dc2626; flex-shrink: 0; margin-top: 0.125rem;"></i>
            <div>
                <p style="font-weight: 600; color: #dc2626; font-size: 0.875rem; margin-bottom: 0.25rem;">
                    Maintenance Mode Sedang Aktif
                </p>
                <p style="color: #7f1d1d; font-size: 0.75rem; line-height: 1.5;">
                    Semua tenant tidak dapat mengakses sistem. Hanya SaaS Owner yang masih bisa login untuk mengatur settings.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Footer Buttons -->
<div class="settings-footer">
    <button type="button" 
            class="settings-btn settings-btn-primary"
            @click="saveTab('system')"
            :disabled="saving">
        <i data-lucide="save" class="w-4 h-4"></i>
        <span x-text="saving ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
    </button>
</div>

