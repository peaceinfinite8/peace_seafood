<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Halaman Tidak Ditemukan · Peace Seafood</title>

    <script src="/peace_seafood/js/tailwindcss.js"></script>
    <link rel="stylesheet" href="/peace_seafood/css/variables.css">
    <link rel="stylesheet" href="/peace_seafood/css/dark-mode.css">
    <link rel="stylesheet" href="/peace_seafood/css/custom.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="/peace_seafood/inline-assets/css/errors/404.css">
</head>

<body>
    <button class="theme-btn" onclick="toggleTheme()" title="Ganti tema">
        <i data-lucide="moon" id="icon-moon" style="width:16px;height:16px"></i>
        <i data-lucide="sun" id="icon-sun" style="width:16px;height:16px;display:none"></i>
    </button>

    <div class="error-card">
        <div class="error-icon-wrap">
            <i data-lucide="map-pin-off" style="width:36px;height:36px;color:#2563eb"></i>
        </div>
        <div class="error-code">404</div>
        <h1 class="error-title">Halaman Tidak Ditemukan</h1>
        <p class="error-desc">
            Halaman yang Anda cari tidak ada atau sudah dipindahkan.<br>
            Periksa kembali URL yang Anda masukkan.
        </p>
        <div class="btn-group">
            <a href="/peace_seafood/dashboard" class="btn-primary-err">
                <i data-lucide="layout-dashboard" style="width:15px;height:15px"></i>
                Kembali ke Dashboard
            </a>
            <a href="javascript:history.back()" class="btn-ghost-err">
                <i data-lucide="arrow-left" style="width:15px;height:15px"></i>
                Halaman Sebelumnya
            </a>
        </div>
        <hr class="divider">
        <p class="help-text">Kode error: 404 Not Found</p>
    </div>

    <script src="/peace_seafood/inline-assets/js/errors/404.js"></script>
</body>

</html>