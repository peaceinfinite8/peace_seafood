<!DOCTYPE html>
<html lang="id" id="errorPage">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Akses Ditolak · Peace Seafood</title>

    <script src=`${window.APP_BASE_URL}/js/tailwindcss.js`></script>
    <link rel="stylesheet" href="${window.APP_BASE_URL}/css/variables.css">
    <link rel="stylesheet" href="${window.APP_BASE_URL}/css/dark-mode.css">
    <link rel="stylesheet" href="${window.APP_BASE_URL}/css/custom.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" crossorigin="anonymous"></script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
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
            max-width: 480px;
            width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Decorative top stripe */
        .error-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ef4444, #f97316, #ef4444);
            background-size: 200% 100%;
            animation: stripe-slide 3s linear infinite;
        }

        @keyframes stripe-slide {
            0%   { background-position: 0% 0%; }
            100% { background-position: 200% 0%; }
        }

        .error-icon-wrap {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.08);
            border: 2px solid rgba(239, 68, 68, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: icon-pulse 2.5s ease-in-out infinite;
        }

        @keyframes icon-pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.15); }
            50%       { box-shadow: 0 0 0 12px rgba(239,68,68,0); }
        }

        .error-code {
            font-size: 5rem;
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(135deg, #ef4444, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            letter-spacing: -2px;
        }

        .error-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary, #1e293b);
            margin-bottom: 0.75rem;
        }

        .error-desc {
            font-size: 0.9rem;
            color: var(--text-secondary, #64748b);
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: rgba(239, 68, 68, 0.08);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 9999px;
            padding: 0.25rem 0.875rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            margin: 0.75rem 0 1.5rem;
        }

        .btn-group {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary-err {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 0.625rem;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
        }
        .btn-primary-err:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-ghost-err {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: transparent;
            color: var(--text-secondary, #64748b);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 0.625rem;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
        }
        .btn-ghost-err:hover {
            background: var(--bg-secondary, #f1f5f9);
            transform: translateY(-1px);
        }

        .divider {
            border: none;
            border-top: 1px solid var(--border-color, #e2e8f0);
            margin: 1.75rem 0 1.25rem;
        }

        .help-text {
            font-size: 0.78rem;
            color: var(--text-secondary, #94a3b8);
        }

        /* Dark mode toggle button */
        .theme-btn {
            position: fixed;
            top: 1rem; right: 1rem;
            background: var(--bg-light, #fff);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 50%;
            width: 38px; height: 38px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: var(--text-secondary, #64748b);
            transition: background 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .theme-btn:hover { background: var(--bg-secondary, #f1f5f9); }

        [data-theme="dark"] .error-card {
            background: rgba(15, 23, 42, 0.97);
            border-color: rgba(239, 68, 68, 0.15);
            box-shadow: 0 8px 40px rgba(0,0,0,0.4);
        }
    </style>
</head>
<body>

    <!-- Dark mode toggle -->
    <button class="theme-btn" onclick="toggleTheme()" title="Ganti tema">
        <i data-lucide="moon" id="icon-moon" style="width:16px;height:16px"></i>
        <i data-lucide="sun"  id="icon-sun"  style="width:16px;height:16px;display:none"></i>
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

        <p class="help-text">
            Butuh akses? Hubungi <strong>Super Admin</strong> untuk mengubah hak akses akun Anda.
        </p>

    </div>

    <script>
        // Init icons
        if (window.lucide) lucide.createIcons();

        // Dark mode sync dengan localStorage
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
            if (theme === 'dark') {
                moon.style.display = 'none';
                sun.style.display  = 'block';
            } else {
                moon.style.display = 'block';
                sun.style.display  = 'none';
            }
        }
    </script>
</body>
</html>
