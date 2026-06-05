<?php
/**
 * Recovery Popup Component
 * Shown once after subscription is restored (approved payment or manual extension)
 */
?>

<!-- Recovery Popup (shown once per session after access restored) -->
<div x-show="showRecoveryPopup" 
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-[100000] flex items-center justify-center p-4"
     @click.self="closeRecoveryPopup()">
    
    <div class="max-w-md w-full bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-900 rounded-2xl shadow-2xl overflow-hidden transform"
         x-transition:enter="transition ease-out duration-300 delay-100"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100">
        
        <!-- Checkmark Animation Header -->
        <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-8 text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAwIDEwIEwgNDAgMTAgTSAxMCAwIEwgMTAgNDAgTSAwIDIwIEwgNDAgMjAgTSAyMCAwIEwgMjAgNDAgTSAwIDMwIEwgNDAgMzAgTSAzMCAwIEwgMzAgNDAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS1vcGFjaXR5PSIwLjA1IiBzdHJva2Utd2lkdGg9IjEiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjZ3JpZCkiLz48L3N2Zz4=')] opacity-30"></div>
            
            <div class="relative">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center animate-bounce">
                    <i data-lucide="check-circle-2" class="w-12 h-12 text-white"></i>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">✅ Akses Gudang Dipulihkan</h2>
                <p class="text-green-50 text-sm font-medium">Pembayaran telah diverifikasi</p>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-6 space-y-4">
            
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl p-4 text-center">
                <p class="text-sm text-slate-700 dark:text-slate-300 mb-3">
                    Masa aktif gudang Anda telah diperpanjang hingga:
                </p>
                <div class="bg-white dark:bg-slate-800 rounded-lg py-3 px-4 inline-block">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Berlaku sampai</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="recoveryData.expiryDate"></p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1" x-text="'(' + recoveryData.daysAdded + ' hari ke depan)'"></p>
                </div>
            </div>
            
            <div class="bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-200 dark:border-cyan-700 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-cyan-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i data-lucide="info" class="w-4 h-4 text-cyan-600 dark:text-cyan-400"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Apa yang bisa Anda lakukan sekarang:</p>
                        <ul class="text-xs text-slate-600 dark:text-slate-400 space-y-1">
                            <li>✓ Akses penuh ke semua fitur sistem</li>
                            <li>✓ Kelola stok dan transaksi</li>
                            <li>✓ Cetak laporan dan nota</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <button @click="closeRecoveryPopup()" 
                    class="w-full h-12 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-lg transition-all transform hover:scale-[1.02]">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                Mulai Bekerja
            </button>
            
        </div>
        
    </div>
    
</div>

<script>
    // Re-initialize Lucide icons for recovery popup
    document.addEventListener('alpine:initialized', () => {
        setTimeout(() => lucide.createIcons(), 100);
    });
</script>
