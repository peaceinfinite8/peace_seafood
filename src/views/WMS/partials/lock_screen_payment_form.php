<?php
/**
 * Lock Screen Payment Form Component
 * Displayed when tenant's subscription is expired
 */
?>

<!-- Payment Form Section (shown after expanding) -->
<div x-show="showPaymentForm" 
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     class="mt-6 max-w-2xl mx-auto">
    
    <!-- Payment Instructions Card -->
    <div class="bg-slate-900/60 border border-slate-700 rounded-2xl p-6 text-left space-y-6">
        
        <!-- Header -->
        <div class="flex items-center gap-3 pb-4 border-b border-slate-700">
            <div class="w-10 h-10 rounded-full bg-cyan-500/10 flex items-center justify-center">
                <i data-lucide="credit-card" class="w-5 h-5 text-cyan-400"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-white" x-text="paymentSettings.lock_screen_title || 'Masa Aktif Anda Telah Habis'"></h3>
                <p class="text-xs text-slate-400" x-text="paymentSettings.lock_screen_message || 'Silakan lakukan pembayaran untuk melanjutkan'"></p>
            </div>
        </div>
        
        <!-- Payment Details Grid -->
        <div class="grid md:grid-cols-2 gap-4">
            
            <!-- Bank Transfer -->
            <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Transfer Bank</p>
                <div class="space-y-2">
                    <div>
                        <p class="text-[10px] text-slate-500">Nama Bank</p>
                        <p class="text-sm font-bold text-white" x-text="paymentSettings.payment_bank_name || '-'"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-500">Atas Nama</p>
                        <p class="text-sm font-bold text-white" x-text="paymentSettings.payment_bank_holder || '-'"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-500">Nomor Rekening</p>
                        <p class="text-base font-mono font-bold text-cyan-400" x-text="paymentSettings.payment_bank_number || '-'"></p>
                    </div>
                </div>
            </div>
            
            <!-- QRIS -->
            <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700 flex flex-col">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">QRIS</p>
                <div class="flex-1 flex items-center justify-center">
                    <template x-if="paymentSettings.payment_qris_image">
                        <img :src="'data:image/png;base64,' + paymentSettings.payment_qris_image" 
                             alt="QRIS Code" 
                             class="w-40 h-40 object-contain rounded-lg border-2 border-slate-700">
                    </template>
                    <template x-if="!paymentSettings.payment_qris_image">
                        <div class="text-center text-slate-500 text-xs">QRIS tidak tersedia</div>
                    </template>
                </div>
            </div>
            
        </div>
        
        <!-- Payment Amount -->
        <div class="bg-gradient-to-r from-cyan-500/10 to-blue-500/10 rounded-xl p-4 border border-cyan-500/20">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400">Total Pembayaran</p>
                    <p class="text-2xl font-bold text-white">
                        <span x-text="'Rp ' + parseInt(paymentSettings.payment_monthly_price || 0).toLocaleString('id-ID')"></span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-full bg-cyan-500/20 flex items-center justify-center">
                    <i data-lucide="banknote" class="w-6 h-6 text-cyan-400"></i>
                </div>
            </div>
        </div>
        
        <!-- Payment Steps -->
        <div class="bg-slate-800/30 rounded-xl p-4 border border-slate-700">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Cara Pembayaran</p>
            <div class="space-y-2 text-xs text-slate-300 leading-relaxed" 
                 x-html="(paymentSettings.lock_screen_payment_steps || 'Ikuti instruksi pembayaran').replace(/\n/g, '<br>')"></div>
        </div>
        
        <!-- Upload Form -->
        <div class="pt-4 border-t border-slate-700">
            <form @submit.prevent="submitPaymentProof" enctype="multipart/form-data">
                
                <label class="block mb-2 text-sm font-semibold text-white">
                    <i data-lucide="upload" class="w-4 h-4 inline-block mr-1"></i>
                    Upload Bukti Pembayaran
                </label>
                
                <div class="mb-4">
                    <input type="file" 
                           name="bukti_transfer" 
                           id="bukti_transfer"
                           accept=".jpg,.jpeg,.png,.pdf"
                           @change="handleFileSelect($event)"
                           class="hidden">
                    
                    <label for="bukti_transfer" 
                           class="block w-full cursor-pointer border-2 border-dashed border-slate-600 hover:border-cyan-500 rounded-xl p-6 text-center transition-all"
                           :class="selectedFile ? 'bg-cyan-500/5 border-cyan-500' : 'bg-slate-800/30'">
                        <template x-if="!selectedFile">
                            <div>
                                <i data-lucide="file-image" class="w-10 h-10 mx-auto mb-2 text-slate-500"></i>
                                <p class="text-sm text-slate-400">Klik untuk pilih file</p>
                                <p class="text-xs text-slate-500 mt-1" x-text="'Format: ' + (paymentSettings.payment_allowed_formats || 'jpg, png, pdf') + ' | Max: ' + (paymentSettings.payment_max_upload_mb || '5') + 'MB'"></p>
                            </div>
                        </template>
                        <template x-if="selectedFile">
                            <div>
                                <i data-lucide="check-circle" class="w-10 h-10 mx-auto mb-2 text-green-500"></i>
                                <p class="text-sm text-white font-semibold" x-text="selectedFile.name"></p>
                                <p class="text-xs text-slate-400 mt-1" x-text="(selectedFile.size / 1024 / 1024).toFixed(2) + ' MB'"></p>
                            </div>
                        </template>
                    </label>
                </div>
                
                <button type="submit" 
                        :disabled="!selectedFile || isSubmitting"
                        class="w-full h-12 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 disabled:from-slate-700 disabled:to-slate-700 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl flex items-center justify-center gap-2 shadow-lg transition-all">
                    <template x-if="!isSubmitting">
                        <span><i data-lucide="send" class="w-4 h-4 inline-block mr-1"></i> Kirim untuk Diverifikasi</span>
                    </template>
                    <template x-if="isSubmitting">
                        <span><i data-lucide="loader-2" class="w-4 h-4 inline-block mr-1 animate-spin"></i> Mengirim...</span>
                    </template>
                </button>
                
            </form>
        </div>
        
    </div>
    
    <!-- Cancel Button -->
    <button @click="showPaymentForm = false" 
            class="mt-4 mx-auto block text-sm text-slate-400 hover:text-white transition-colors">
        <i data-lucide="chevron-up" class="w-4 h-4 inline-block"></i>
        Tutup Form Pembayaran
    </button>
    
</div>

<!-- Success State (after submission) -->
<div x-show="paymentSubmitted" 
     x-cloak
     x-transition
     class="mt-6 max-w-md mx-auto bg-green-900/20 border border-green-500/30 rounded-2xl p-6 text-center">
    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-500/10 flex items-center justify-center">
        <i data-lucide="check-circle-2" class="w-8 h-8 text-green-400"></i>
    </div>
    <h3 class="text-lg font-bold text-white mb-2">Bukti Pembayaran Terkirim!</h3>
    <p class="text-sm text-slate-300 leading-relaxed" 
       x-text="paymentSettings.lock_screen_after_submit || 'Terima kasih! Bukti pembayaran Anda sedang diverifikasi.'"></p>
</div>

<script>
    // Re-initialize Lucide icons after Alpine renders
    document.addEventListener('alpine:initialized', () => {
        setTimeout(() => lucide.createIcons(), 100);
    });
</script>
