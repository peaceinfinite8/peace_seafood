<!DOCTYPE html>
<html lang="id" id="errorPage">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Akses Ditolak · Peace Seafood</title>

    <script src="/peace_seafood/js/tailwindcss.js"></script>
    <link rel="stylesheet" href="/peace_seafood/css/variables.css">
    <link rel="stylesheet" href="/peace_seafood/css/dark-mode.css">
    <link rel="stylesheet" href="/peace_seafood/css/custom.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="/peace_seafood/inline-assets/css/errors/403.css">
</head>

<body>

    <!-- Dark mode toggle -->
    <button class="theme-btn" onclick="toggleTheme()" title="Ganti tema">
        <i data-lucide="moon" id="icon-moon" style="width:16px;height:16px"></i>
        <i data-lucide="sun" id="icon-sun" style="width:16px;height:16px;display:none"></i>
    </button>

    <div class="error-card">

        <!-- Icon -->
        <div class="error-icon-wrap">
            <i data-lucide="shield-x" style="width:36px;height:36px;color:#ef4444"></i>
        </div>

        <!-- Code -->
        <div class="error-code">403</div>

        <!-- Title -->
        <h1 class="error-title">Akses Ditolak</h1>

        <!-- Description -->
        <p class="error-desc">
            Halaman <strong><?= htmlspecialchars($uriSafe ?? '') ?></strong>
            tidak dapat diakses dengan role Anda saat ini.
        </p>

        <!-- Role badge -->
        <?php if (!empty($roleSafe)): ?>
            <div>
                <span class="role-badge">
                    <i data-lucide="user-x" style="width:12px;height:12px"></i>
                    <?= $roleSafe ?>
                </span>
            </div>
        <?php endif; ?>

        <!-- Buttons -->
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

        <p class="help-text">
            Butuh akses? Hubungi <strong>Super Admin</strong> untuk mengubah hak akses akun Anda.
        </p>

    </div>

    <script src="/peace_seafood/inline-assets/js/errors/403.js"></script>
</body>

</html>