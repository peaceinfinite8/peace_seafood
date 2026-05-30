<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Halaman Tidak Ditemukan · Peace Seafood</title>

    <script src=`${window.APP_BASE_URL}/js/tailwindcss.js`></script>
    <link rel="stylesheet" href="${window.APP_BASE_URL}/css/variables.css">
    <link rel="stylesheet" href="${window.APP_BASE_URL}/css/dark-mode.css">
    <link rel="stylesheet" href="${window.APP_BASE_URL}/css/custom.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" crossorigin="anonymous"></script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: var(--bg-gray, #f8fafc);
            color: var(--text-primary, #1e293b);
            font-family: 'Inter', system-ui, sans-serif;
            padding: 1.5rem;
            transition: background 0.3s, color 0.3s;
        }
        .error-card {
            background: var(--bg-light, #ffffff);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 1.25rem;
            box-shadow: 0 8px 40px rgba(0,0,0,0.10);
            padding: 3rem 2.5rem;
            max-width: 480px; width: 100%;
            text-align: center; position: relative; overflow: hidden;
        }
        .error-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, #2563eb, #06b6d4, #2563eb);
            background-size: 200% 100%;
            animation: stripe-slide 3s linear infinite;
        }
        @keyframes stripe-slide {
            0%   { background-position: 0% 0%; }
            100% { background-position: 200% 0%; }
        }
        .error-icon-wrap {
            width: 80px; height: 80px; border-radius: 50%;
            background: rgba(37, 99, 235, 0.08);
            border: 2px solid rgba(37, 99, 235, 0.2);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
            animation: icon-pulse 2.5s ease-in-out infinite;
        }
        @keyframes icon-pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(37,99,235,0.15); }
            50%       { box-shadow: 0 0 0 12px rgba(37,99,235,0); }
        }
        .error-code {
            font-size: 5rem; font-weight: 900; line-height: 1;
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-bottom: 0.5rem; letter-spacing: -2px;
        }
        .error-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem; }
        .error-desc  { font-size: 0.9rem; color: var(--text-secondary, #64748b); line-height: 1.6; margin-bottom: 1.5rem; }
        .btn-group   { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
        .btn-primary-err {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: #2563eb; color: #fff; border: none;
            border-radius: 0.625rem; padding: 0.625rem 1.25rem;
            font-size: 0.875rem; font-weight: 600; text-decoration: none;
            transition: background 0.2s, transform 0.15s;
        }
        .btn-primary-err:hover { background: #1d4ed8; transform: translateY(-1px); }
        .btn-ghost-err {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: transparent; color: var(--text-secondary, #64748b);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 0.625rem; padding: 0.625rem 1.25rem;
            font-size: 0.875rem; font-weight: 600; text-decoration: none;
            transition: background 0.2s, transform 0.15s;
        }
        .btn-ghost-err:hover { background: var(--bg-secondary, #f1f5f9); transform: translateY(-1px); }
        .divider { border: none; border-top: 1px solid var(--border-color, #e2e8f0); margin: 1.75rem 0 1.25rem; }
        .help-text { font-size: 0.78rem; color: var(--text-secondary, #94a3b8); }
        .theme-btn {
            position: fixed; top: 1rem; right: 1rem;
            background: var(--bg-light, #fff); border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 50%; width: 38px; height: 38px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--text-secondary, #64748b);
            transition: background 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .theme-btn:hover { background: var(--bg-secondary, #f1f5f9); }
        [data-theme="dark"] .error-card {
            background: rgba(15, 23, 42, 0.97);
            border-color: rgba(37, 99, 235, 0.15);
            box-shadow: 0 8px 40px rgba(0,0,0,0.4);
        }
    </style>
</head>
<body>
    <button class="theme-btn" onclick="toggleTheme()" title="Ganti tema">
        <i data-lucide="moon" id="icon-moon" style="width:16px;height:16px"></i>
        <i data-lucide="sun"  id="icon-sun"  style="width:16px;height:16px;display:none"></i>
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
            <a href="${window.APP_BASE_URL}/dashboard" class="btn-primary-err">
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

    <script>
        if (window.lucide) lucide.createIcons();
        (function () {
            const saved = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', saved);
            syncIcons(saved);
        })();
        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const next = current === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            syncIcons(next);
        }
        function syncIcons(theme) {
            const moon = document.getElementById('icon-moon');
            const sun  = document.getElementById('icon-sun');
            if (!moon || !sun) return;
            moon.style.display = theme === 'dark' ? 'none' : 'block';
            sun.style.display  = theme === 'dark' ? 'block' : 'none';
        }
    </script>
</body>
</html>
