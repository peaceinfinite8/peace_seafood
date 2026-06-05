<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Approval Result') ?> — Peace Seafood</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-500 via-cyan-500 to-slate-900 flex items-center justify-center p-4">
    
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8 text-center">
        
        <?php if (isset($isError) && $isError): ?>
            <!-- Error State -->
            <div class="mb-6">
                <div class="w-20 h-20 mx-auto bg-red-100 rounded-full flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-12 h-12 text-red-600"></i>
                </div>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-3">
                <?= htmlspecialchars($pageTitle) ?>
            </h1>
            
            <p class="text-gray-600 mb-6 leading-relaxed">
                <?= $message ?>
            </p>
            
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-left">
                <p class="text-sm text-red-800">
                    <strong>Kemungkinan penyebab:</strong><br>
                    • Token sudah pernah digunakan<br>
                    • Token sudah kadaluarsa<br>
                    • Link tidak valid
                </p>
            </div>
            
            <p class="text-sm text-gray-500 mb-6">
                Silakan approve manual dari dashboard SaaS Owner atau hubungi administrator.
            </p>
            
        <?php else: ?>
            <!-- Success State -->
            <div class="mb-6">
                <div class="w-20 h-20 mx-auto bg-green-100 rounded-full flex items-center justify-center animate-bounce">
                    <i data-lucide="check-circle" class="w-12 h-12 text-green-600"></i>
                </div>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-3">
                ✅ Pembayaran Disetujui
            </h1>
            
            <p class="text-gray-600 mb-6 leading-relaxed">
                <?= $message ?>
            </p>
            
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-green-800">
                    <strong>Akses telah dipulihkan!</strong><br>
                    Pemilik gudang akan menerima notifikasi email dan dapat login kembali ke sistem.
                </p>
            </div>
            
            <p class="text-sm text-gray-500 mb-6">
                Token Magic Link ini sudah tidak dapat digunakan lagi.
            </p>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-400">
                Peace Seafood WMS — Sistem Manajemen Gudang Ikan<br>
                © <?= date('Y') ?> All Rights Reserved
            </p>
        </div>
        
    </div>
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
