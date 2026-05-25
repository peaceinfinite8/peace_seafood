<?php

/**
 * Main App Layout
 * Usage: include this file, set $pageTitle, $activeMenu, $content vars
 */
try {
    $dbNameSetting = \App\Utils\Database::fetchOne("SELECT nilai FROM settings WHERE kunci = 'company_name' LIMIT 1");
    $dbLogoSetting = \App\Utils\Database::fetchOne("SELECT nilai FROM settings WHERE kunci = 'company_logo_base64' LIMIT 1");
    $dbInitialSetting = \App\Utils\Database::fetchOne("SELECT nilai FROM settings WHERE kunci = 'company_logo_initial' LIMIT 1");
} catch (\Exception $e) {
    $dbNameSetting = null;
    $dbLogoSetting = null;
    $dbInitialSetting = null;
}

$appName  = $dbNameSetting ? $dbNameSetting['nilai'] : 'Peace Seafood';
$appLogoBase64 = $dbLogoSetting ? $dbLogoSetting['nilai'] : null;
$appLogoInitial = $dbInitialSetting ? $dbInitialSetting['nilai'] : 'PS';
$baseUrl  = '/peace_seafood';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — <?= $appName ?></title>
    <meta name="description" content="Peace Seafood - Sistem Manajemen Gudang Ikan">

    <!-- Tailwind CSS runtime (self-hosted to avoid CDN warning) -->
    <script src="<?= $baseUrl ?>/js/tailwindcss.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" crossorigin="anonymous"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" crossorigin="anonymous"></script>
    <script>
        // Uncaught error handler to show errors in UI
        window.addEventListener('error', (event) => {
            const errBanner = document.createElement('div');
            errBanner.style.position = 'fixed';
            errBanner.style.bottom = '0';
            errBanner.style.left = '0';
            errBanner.style.width = '100%';
            errBanner.style.background = 'rgba(239, 68, 68, 0.95)';
            errBanner.style.color = '#fff';
            errBanner.style.padding = '12px';
            errBanner.style.zIndex = '99999';
            errBanner.style.fontFamily = 'monospace';
            errBanner.style.fontSize = '12px';
            errBanner.innerHTML = `<strong>JS Error:</strong> ${event.message} at ${event.filename}:${event.lineno}:${event.colno}`;
            document.body.appendChild(errBanner);
        });
        window.addEventListener('unhandledrejection', (event) => {
            const errBanner = document.createElement('div');
            errBanner.style.position = 'fixed';
            errBanner.style.bottom = '0';
            errBanner.style.left = '0';
            errBanner.style.width = '100%';
            errBanner.style.background = 'rgba(245, 158, 11, 0.95)';
            errBanner.style.color = '#fff';
            errBanner.style.padding = '12px';
            errBanner.style.zIndex = '99999';
            errBanner.style.fontFamily = 'monospace';
            errBanner.style.fontSize = '12px';
            errBanner.innerHTML = `<strong>Promise Rejection:</strong> ${event.reason}`;
            document.body.appendChild(errBanner);
        });

        // Global Interceptor Proxy for iziToast -> SweetAlert2 Toast
        window.iziToast = {
            showToast(type, options) {
                const title = options.title || '';
                const message = options.message || '';
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'swal2-toast-glassmorphic'
                    },
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
                Toast.fire({
                    icon: type, // 'success', 'error', 'warning', 'info'
                    title: title ? `<strong>${title}</strong>` : '',
                    html: message
                });
            },
            success(options) {
                this.showToast('success', options);
            },
            error(options) {
                this.showToast('error', options);
            },
            warning(options) {
                this.showToast('warning', options);
            },
            info(options) {
                this.showToast('info', options);
            }
        };

        // Async Wrapper for window.confirm
        window.confirm = (message) => {
            return new Promise((resolve) => {
                Swal.fire({
                    title: 'Konfirmasi Tindakan',
                    text: message,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'swal2-glassmorphic',
                        confirmButton: 'swal2-confirm-btn',
                        cancelButton: 'swal2-cancel-btn'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    resolve(result.isConfirmed);
                });
            });
        };
    </script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" crossorigin="anonymous"></script>

    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js" crossorigin="anonymous"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" crossorigin="anonymous"></script>

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" crossorigin="anonymous"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/variables.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/dark-mode.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/custom.css">

    <!-- PWA -->
    <link rel="manifest" href="<?= $baseUrl ?>/manifest.json">

    <script>
        // Tailwind config
        tailwind.config = {
            darkMode: ['class', '[data-theme="dark"]'],
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#2563eb',
                            light: '#dbeafe',
                            dark: '#1e40af'
                        },
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444',
                        info: '#06b6d4',
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --color-primary: #2563eb;
            --color-primary-light: #dbeafe;
            --color-primary-dark: #1e40af;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
            --color-info: #06b6d4;
            --bg-light: #ffffff;
            --bg-gray: #f8fafc;
            --bg-secondary: #f1f5f9;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;

            /* SweetAlert2 variables */
            --swal-bg: rgba(255, 255, 255, 0.97);
            --swal-border: rgba(226, 232, 240, 0.9);
            --swal-text: #1e293b;
            --swal-text-secondary: #64748b;
            --swal-btn-confirm: linear-gradient(135deg, #2563eb, #1d4ed8);
            --swal-btn-cancel: rgba(241, 245, 249, 1);
            --swal-btn-cancel-text: #475569;
            --swal-shadow: 0 25px 60px rgba(0, 0, 0, 0.18), 0 0 0 1px rgba(255, 255, 255, 0.6) inset;
        }

        [data-theme="dark"] {
            --color-primary: #3b82f6;
            --color-primary-light: rgba(59, 130, 246, 0.15);
            --color-primary-dark: #1d4ed8;
            --bg-light: #1e293b;
            --bg-gray: #0f172a;
            --bg-secondary: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --border-color: #475569;

            /* SweetAlert2 variables */
            --swal-bg: rgba(15, 23, 42, 0.97);
            --swal-border: rgba(6, 182, 212, 0.3);
            --swal-text: #f1f5f9;
            --swal-text-secondary: #cbd5e1;
            --swal-btn-confirm: linear-gradient(135deg, #06b6d4, #0891b2);
            --swal-btn-cancel: rgba(30, 41, 59, 0.9);
            --swal-btn-cancel-text: #cbd5e1;
            --swal-shadow: 0 25px 60px rgba(0, 0, 0, 0.5), 0 0 20px rgba(6, 182, 212, 0.08);
        }

        body {
            background: transparent !important;
            color: var(--text-primary);
            transition: background 0.3s, color 0.3s;
        }

        .sidebar {
            background: var(--bg-sidebar-glass, var(--bg-light));
            border-right: 1px solid var(--border-sidebar-glass, var(--border-color));
            backdrop-filter: var(--blur-card-glass, none);
            -webkit-backdrop-filter: var(--blur-card-glass, none);
        }

        .card {
            background: var(--bg-card-glass, var(--bg-light));
            border: 1px solid var(--border-card-glass, var(--border-color));
            border-radius: 0.75rem;
            backdrop-filter: var(--blur-card-glass, none);
            -webkit-backdrop-filter: var(--blur-card-glass, none);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 1rem;
            border-radius: 0.5rem;
            color: var(--text-secondary);
            transition: all 0.2s;
            font-size: 0.875rem;
            text-decoration: none;
        }

        .nav-link:hover {
            background: var(--color-primary-light);
            color: var(--color-primary);
        }

        .nav-link.active {
            background: var(--color-primary-light);
            color: var(--color-primary);
            font-weight: 600;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .badge-info {
            background: rgba(6, 182, 212, 0.1);
            color: #0891b2;
        }

        .badge-gray {
            background: rgba(100, 116, 139, 0.1);
            color: #64748b;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--color-primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--color-primary-dark);
        }

        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--border-color);
        }

        .btn-danger {
            background: var(--color-danger);
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-success {
            background: var(--color-success);
            color: white;
        }

        .btn-warning {
            background: var(--color-warning);
            color: white;
        }

        .form-input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            background: var(--bg-light);
            color: var(--text-primary);
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px var(--color-primary-light);
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.375rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .table th {
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            background: var(--bg-gray);
            border-bottom: 1px solid var(--border-color);
        }

        .table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .table tr:hover td {
            background: var(--bg-gray);
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-box {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 0.75rem;
            padding: 1.5rem;
            width: 100%;
            max-width: 32rem;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.6) inset;
        }

        @media (max-width: 640px) {
            .modal-box {
                margin: 1rem;
                max-height: calc(100vh - 2rem);
                padding: 1.25rem;
            }
        }

        [data-theme="dark"] .modal-box {
            background: rgba(15, 23, 42, 0.97);
            border: 1px solid rgba(6, 182, 212, 0.2);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.04) inset;
        }

        .stat-card {
            background: var(--bg-card-glass, var(--bg-light));
            border-radius: 0.75rem;
            padding: 1.25rem;
            border: 1px solid var(--border-card-glass, var(--border-color));
            backdrop-filter: var(--blur-card-glass, none);
            -webkit-backdrop-filter: var(--blur-card-glass, none);
        }

        [x-cloak] {
            display: none !important;
        }

        /* 🔔 SWEETALERT2 GLASSMORPHIC THEMED OVERRIDES */
        .swal2-popup.swal2-glassmorphic {
            background: var(--swal-bg) !important;
            border: 1px solid var(--swal-border) !important;
            backdrop-filter: blur(24px) saturate(140%) !important;
            -webkit-backdrop-filter: blur(24px) saturate(140%) !important;
            border-radius: 24px !important;
            color: var(--swal-text) !important;
            box-shadow: var(--swal-shadow) !important;
            font-family: 'Inter', sans-serif !important;
            transition: all 0.3s ease !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-title {
            color: var(--swal-text) !important;
            font-weight: 700 !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-html-container {
            color: var(--swal-text-secondary) !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-confirm-btn {
            background: var(--swal-btn-confirm) !important;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2) !important;
            border-radius: 12px !important;
            padding: 10px 24px !important;
            font-weight: 600 !important;
            border: none !important;
            color: white !important;
            cursor: pointer;
            margin: 0.5rem !important;
            transition: all 0.3s;
        }

        .swal2-popup.swal2-glassmorphic .swal2-confirm-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .swal2-popup.swal2-glassmorphic .swal2-cancel-btn {
            background: var(--swal-btn-cancel) !important;
            border-radius: 12px !important;
            padding: 10px 24px !important;
            font-weight: 600 !important;
            border: none !important;
            color: var(--swal-btn-cancel-text) !important;
            cursor: pointer;
            margin: 0.5rem !important;
            transition: all 0.3s;
        }

        .swal2-popup.swal2-glassmorphic .swal2-cancel-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .swal2-popup.swal2-glassmorphic .swal2-icon.swal2-error {
            border-color: var(--color-danger) !important;
            color: var(--color-danger) !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-icon.swal2-error [class^=swal2-x-mark-line] {
            background-color: var(--color-danger) !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-icon.swal2-success {
            border-color: var(--color-success) !important;
            color: var(--color-success) !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-icon.swal2-success [class^=swal2-success-line] {
            background-color: var(--color-success) !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-icon.swal2-success .swal2-success-ring {
            border: 4px solid rgba(16, 185, 129, 0.2) !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-icon.swal2-warning {
            border-color: var(--color-warning) !important;
            color: var(--color-warning) !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-icon.swal2-info {
            border-color: var(--color-info) !important;
            color: var(--color-info) !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-icon.swal2-question {
            border-color: var(--color-primary) !important;
            color: var(--color-primary) !important;
        }

        /* 🔔 SWEETALERT2 TOAST GLASSMORPHIC OVERRIDES */
        .swal2-popup.swal2-toast-glassmorphic {
            background: var(--swal-bg) !important;
            border: 1px solid var(--swal-border) !important;
            backdrop-filter: blur(16px) saturate(140%) !important;
            -webkit-backdrop-filter: blur(16px) saturate(140%) !important;
            border-radius: 16px !important;
            box-shadow: var(--swal-shadow) !important;
            padding: 10px 16px !important;
        }

        .swal2-popup.swal2-toast-glassmorphic .swal2-title {
            color: var(--swal-text) !important;
            font-size: 0.875rem !important;
            font-weight: 600 !important;
        }

        .swal2-popup.swal2-toast-glassmorphic .swal2-html-container {
            color: var(--swal-text-secondary) !important;
            font-size: 0.75rem !important;
        }

        .swal2-popup.swal2-toast-glassmorphic.swal2-clickable-toast {
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 0.5rem !important;
            padding: 16px !important;
            width: 320px !important;
        }

        .swal2-popup.swal2-toast-glassmorphic.swal2-clickable-toast .swal2-actions {
            display: flex !important;
            width: 100% !important;
            justify-content: flex-end !important;
            gap: 0.35rem !important;
            margin-top: 0.5rem !important;
            border: none !important;
            padding: 0 !important;
            background: transparent !important;
        }

        .swal2-popup.swal2-toast-glassmorphic.swal2-clickable-toast .swal2-toast-confirm-btn {
            background: var(--color-primary) !important;
            color: white !important;
            border-radius: 8px !important;
            padding: 4px 10px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            border: none !important;
            cursor: pointer !important;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2) !important;
        }

        .swal2-popup.swal2-toast-glassmorphic.swal2-clickable-toast .swal2-toast-confirm-btn:hover {
            opacity: 0.9 !important;
        }

        .swal2-popup.swal2-toast-glassmorphic.swal2-clickable-toast .swal2-toast-cancel-btn {
            background: rgba(100, 116, 139, 0.1) !important;
            color: var(--text-secondary) !important;
            border-radius: 8px !important;
            padding: 4px 10px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            border: none !important;
            cursor: pointer !important;
        }

        .swal2-popup.swal2-toast-glassmorphic.swal2-clickable-toast .swal2-toast-cancel-btn:hover {
            background: rgba(100, 116, 139, 0.2) !important;
        }

        .custom-scrollbar-thin::-webkit-scrollbar {
            width: 5px !important;
            height: 5px !important;
        }

        .custom-scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(37, 99, 235, 0.2) !important;
            border-radius: 9999px !important;
        }

        /* Glassmorphic Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(37, 99, 235, 0.15);
            border-radius: 9999px;
            transition: background 0.3s;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(37, 99, 235, 0.35);
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        [data-theme="dark"] ::-webkit-scrollbar-thumb {
            background: rgba(6, 182, 212, 0.2);
        }

        [data-theme="dark"] ::-webkit-scrollbar-thumb:hover {
            background: rgba(6, 182, 212, 0.5);
        }

        /* Premium Card Interactive Hover & Scale */
        .card {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.3s !important;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px -10px rgba(37, 99, 235, 0.12);
            border-color: rgba(37, 99, 235, 0.3) !important;
        }

        [data-theme="dark"] .card:hover {
            box-shadow: 0 12px 30px -10px rgba(6, 182, 212, 0.15), 0 0 15px rgba(6, 182, 212, 0.05);
            border-color: rgba(6, 182, 212, 0.4) !important;
        }

        /* Nautical Loading Screen Transitions */
        .nautical-loader-overlay {
            position: fixed;
            inset: 0;
            background: var(--bg-gray);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease;
        }

        .loader-compass {
            position: relative;
            width: 80px;
            height: 80px;
            border: 4px solid var(--color-primary);
            border-radius: 50%;
            animation: loader-spin 3s linear infinite;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loader-compass::before {
            content: '';
            position: absolute;
            width: 4px;
            height: 36px;
            background: linear-gradient(to top, var(--color-primary) 50%, var(--color-danger) 50%);
            border-radius: 2px;
            transform: translateY(-2px);
        }

        .loader-compass::after {
            content: '';
            position: absolute;
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            border: 2px solid var(--color-primary);
        }

        @keyframes loader-spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loader-text {
            margin-top: 1.5rem;
            font-size: 0.875rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            color: var(--text-primary);
            animation: loader-pulse 2s infinite ease-in-out;
        }

        @keyframes loader-pulse {

            0%,
            100% {
                opacity: 0.6;
            }

            50% {
                opacity: 1;
            }
        }

        /* Premium Empty State Styling */
        .empty-state-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.5rem;
            text-align: center;
        }

        .empty-state-icon {
            width: 64px;
            height: 64px;
            color: var(--text-secondary);
            opacity: 0.4;
            margin-bottom: 1rem;
            animation: empty-pulse 3s infinite ease-in-out;
        }

        @keyframes empty-pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.4;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.6;
            }
        }

        /* 🌟 Premium Modern Highlight Glow Animation */
        @keyframes highlight-glow-pulse {

            0%,
            100% {
                box-shadow: 0 0 15px rgba(245, 158, 11, 0.45), inset 0 0 8px rgba(245, 158, 11, 0.1);
                border-color: rgba(245, 158, 11, 0.8) !important;
                background-color: rgba(245, 158, 11, 0.06) !important;
            }

            50% {
                box-shadow: 0 0 25px rgba(245, 158, 11, 0.9), inset 0 0 15px rgba(245, 158, 11, 0.25);
                border-color: rgba(245, 158, 11, 1) !important;
                background-color: rgba(245, 158, 11, 0.14) !important;
            }
        }

        .highlight-glow-pulse {
            animation: highlight-glow-pulse 1.6s infinite ease-in-out !important;
            position: relative;
            z-index: 10;
            transition: all 0.5s ease-out;
        }
    </style>

    <style>
        .theme-toggle-icon-light {
            display: inline-block;
        }

        .theme-toggle-icon-dark {
            display: none;
        }

        [data-theme="dark"] .theme-toggle-icon-light {
            display: none;
        }

        [data-theme="dark"] .theme-toggle-icon-dark {
            display: inline-block;
        }
    </style>
</head>

<body class="min-h-screen" x-data="appLayout()" x-init="init()">

    <!-- Realistic Coastal Animated Background Container -->
    <div class="coastal-bg-container" id="coastal-bg" aria-hidden="true">
        <!-- Sun/Moon Glow Orb -->
        <div class="coastal-celestial-orb"></div>

        <!-- Night Stars (Active in Night Mode) -->
        <div class="coastal-stars-container">
            <div class="micro-star star-1"></div>
            <div class="micro-star star-2"></div>
            <div class="micro-star star-3"></div>
            <div class="micro-star star-4"></div>
            <div class="micro-star star-5"></div>
        </div>

        <!-- Wind Breezes (Angin Sepoi-Sepoi) -->
        <div class="coastal-wind-container">
            <div class="wind-streak streak-1"></div>
            <div class="wind-streak streak-2"></div>
            <div class="wind-streak streak-3"></div>
        </div>

        <!-- Beach Shore & Waves -->
        <div class="coastal-shore">
            <!-- Sand Bed -->
            <div class="beach-sand"></div>

            <!-- Wave Layer 1 (Deepest water) -->
            <svg class="coastal-wave wave-back" viewBox="0 0 1440 160" preserveAspectRatio="none">
                <path d="M0,80 C320,130 640,30 960,100 C1280,170 1360,90 1440,80 L1440,160 L0,160 Z" />
            </svg>

            <!-- Wave Layer 2 (Mid-water) -->
            <svg class="coastal-wave wave-mid" viewBox="0 0 1440 160" preserveAspectRatio="none">
                <path d="M0,60 C360,110 720,20 1080,80 C1240,110 1360,50 1440,60 L1440,160 L0,160 Z" />
            </svg>

            <!-- Wave Layer 3 & Sea Foam (Front edge) -->
            <svg class="coastal-wave wave-front" viewBox="0 0 1440 160" preserveAspectRatio="none">
                <path d="M0,40 C280,90 560,10 840,70 C1120,130 1280,50 1440,40 L1440,160 L0,160 Z" />
            </svg>
        </div>
    </div>

    <!-- Nautical Page Loader Overlay -->
    <div id="nautical-page-loader" class="nautical-loader-overlay">
        <div class="loader-compass"></div>
        <div class="loader-text">MEMUAT SISTEM BAHARI...</div>
    </div>
    <script>
        window.addEventListener('load', () => {
            const loader = document.getElementById('nautical-page-loader');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => loader.remove(), 500);
            }
        });
        setTimeout(() => {
            const loader = document.getElementById('nautical-page-loader');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => loader.remove(), 500);
            }
        }, 2000); // 2 seconds safety timeout
    </script>

    <!-- Sidebar -->
    <aside class="sidebar fixed top-0 left-0 h-full w-64 flex flex-col z-30 transition-transform duration-300"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-64 lg:translate-x-0'">

        <!-- Logo -->
        <div class="flex items-center gap-3 px-6 py-5 border-b" style="border-color: var(--border-color)">
            <?php if ($appLogoBase64): ?>
                <div class="w-9 h-9 rounded-lg overflow-hidden flex items-center justify-center">
                    <img src="<?= $appLogoBase64 ?>" class="w-full h-full object-cover">
                </div>
            <?php else: ?>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: var(--color-primary)">
                    <span class="text-white font-bold text-sm"><?= htmlspecialchars($appLogoInitial) ?></span>
                </div>
            <?php endif; ?>
            <div>
                <p class="font-bold text-sm truncate max-w-[140px]" style="color: var(--text-primary)"><?= htmlspecialchars($appName) ?></p>
                <p class="text-xs" style="color: var(--text-secondary)" x-text="currentUser ? currentUser.nama_gudang || 'All Gudang' : 'All Gudang'"></p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

            <a href="/peace_seafood/dashboard"
                class="nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                Dashboard
            </a>

            <!-- Stok -->
            <div class="pt-2 pb-1 px-3 text-xs font-semibold uppercase tracking-wider" style="color: var(--text-secondary)">Operasional</div>

            <a href="/peace_seafood/stok"
                class="nav-link <?= ($activeMenu ?? '') === 'stok' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin','checker'].includes(currentUser.role)">
                <i data-lucide="package" class="w-4 h-4"></i>
                Stok
            </a>

            <a href="/peace_seafood/penjualan"
                class="nav-link <?= ($activeMenu ?? '') === 'penjualan' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin'].includes(currentUser.role)">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                Penjualan
            </a>

            <a href="/peace_seafood/keuangan"
                class="nav-link <?= ($activeMenu ?? '') === 'keuangan' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin'].includes(currentUser.role)">
                <i data-lucide="wallet" class="w-4 h-4"></i>
                Keuangan
            </a>

            <!-- Master & Laporan -->
            <div class="pt-2 pb-1 px-3 text-xs font-semibold uppercase tracking-wider" style="color: var(--text-secondary)">Data & Laporan</div>

            <a href="/peace_seafood/master-data"
                class="nav-link <?= ($activeMenu ?? '') === 'master-data' ? 'active' : '' ?>">
                <i data-lucide="database" class="w-4 h-4"></i>
                Master Data
            </a>

            <a href="/peace_seafood/migrasi"
                class="nav-link <?= ($activeMenu ?? '') === 'migrasi' ? 'active' : '' ?>"
                x-show="['super_admin','admin'].includes(currentUser.role)">
                <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                Migrasi Data
            </a>

            <a href="/peace_seafood/laporan"
                class="nav-link <?= ($activeMenu ?? '') === 'laporan' ? 'active' : '' ?>">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                Laporan
            </a>

            <!-- Fitur jarang dipakai -->
            <div class="pt-2 pb-1 px-3 text-xs font-semibold uppercase tracking-wider" style="color: var(--text-secondary)">Fitur Tambahan</div>

            <a href="/peace_seafood/penitipan"
                class="nav-link <?= ($activeMenu ?? '') === 'penitipan' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin'].includes(currentUser.role)">
                <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                Penitipan
            </a>

            <a href="/peace_seafood/retur"
                class="nav-link <?= ($activeMenu ?? '') === 'retur' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin'].includes(currentUser.role)">
                <i data-lucide="refresh-ccw" class="w-4 h-4"></i>
                Retur
            </a>

            <a href="/peace_seafood/stok-opname"
                class="nav-link <?= ($activeMenu ?? '') === 'stok-opname' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin','checker'].includes(currentUser.role)">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                Stok Opname
            </a>

            <a href="/peace_seafood/stok-transfer"
                class="nav-link <?= ($activeMenu ?? '') === 'stok-transfer' ? 'active' : '' ?>"
                x-show="['super_admin','bos','admin','checker'].includes(currentUser.role)">
                <i data-lucide="move-horizontal" class="w-4 h-4"></i>
                Stok Transfer
            </a>

            <a href="/peace_seafood/activity-log"
                class="nav-link <?= ($activeMenu ?? '') === 'activity-log' ? 'active' : '' ?>"
                x-show="['super_admin','bos'].includes(currentUser.role)">
                <i data-lucide="history" class="w-4 h-4"></i>
                Activity Log
            </a>

            <!-- Settings (Super Admin Only) -->
            <div class="pt-2 pb-1 px-3 text-xs font-semibold uppercase tracking-wider"
                style="color: var(--text-secondary)"
                x-show="currentUser.role === 'super_admin'">Pengaturan</div>

            <a href="/peace_seafood/settings"
                class="nav-link <?= ($activeMenu ?? '') === 'settings' ? 'active' : '' ?>"
                x-show="currentUser.role === 'super_admin'">
                <i data-lucide="settings" class="w-4 h-4"></i>
                Settings
            </a>
        </nav>

        <!-- User info bottom -->
        <div class="px-4 py-4 border-t" style="border-color: var(--border-color)">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold"
                    style="background: var(--color-primary)">
                    <span x-text="currentUser.name ? currentUser.name.charAt(0).toUpperCase() : 'U'"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate" style="color: var(--text-primary)" x-text="currentUser.name"></p>
                    <p class="text-xs truncate" style="color: var(--text-secondary)" x-text="currentUser.role?.toUpperCase()"></p>
                </div>
                <button @click="logout()" class="p-1 rounded hover:bg-red-50" title="Logout">
                    <i data-lucide="log-out" class="w-4 h-4 text-red-500"></i>
                </button>
            </div>
        </div>
    </aside>

    <!-- Sidebar overlay (mobile) -->
    <div class="fixed inset-0 bg-black/50 z-20 lg:hidden"
        x-show="sidebarOpen"
        @click="sidebarOpen = false"
        x-cloak></div>

    <!-- Main Content -->
    <div class="lg:ml-64 min-h-screen flex flex-col">

        <!-- Top Navbar -->
        <header class="sticky top-0 z-10" style="background: var(--bg-light); border-bottom: 1px solid var(--border-color)">
            <div class="flex items-center gap-4 px-4 py-3">

                <!-- Hamburger (mobile) -->
                <button class="lg:hidden p-2 rounded-lg" style="color: var(--text-secondary)"
                    @click="sidebarOpen = !sidebarOpen">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>

                <!-- Page Title -->
                <h1 class="font-semibold text-lg" style="color: var(--text-primary)">
                    <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?>
                </h1>

                <div class="flex-1"></div>
                <!-- Notifications -->
                <div class="relative" x-data="{ open: false }"
                    @keydown.escape.window="open = false"
                    @close-notif-dropdown.window="open = false">
                    <button @click="open = !open; if(open) loadNotif();"
                        class="relative p-2 rounded-xl transition-all duration-200 hover:bg-gray-100/60 dark:hover:bg-slate-800/40" style="color: var(--text-secondary)">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        <span class="absolute -top-0.5 -right-0.5 w-5 h-5 flex items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-md animate-pulse"
                            x-show="unreadCount > 0" x-text="unreadCount" x-cloak></span>
                    </button>

                    <!-- Dropdown notif - @click.away disabled when dialogs open -->
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                        class="absolute right-0 top-12 w-96 rounded-2xl shadow-2xl z-50 backdrop-blur-xl"
                        style="background: var(--bg-light); border: 1px solid var(--border-color); box-shadow: 0 20px 40px -8px rgba(0,0,0,0.15), 0 8px 16px -4px rgba(0,0,0,0.08);"
                        x-cloak
                        @click.outside="if(!window._swalOpen) open = false">

                        <!-- Header Dropdown -->
                        <div class="px-4 pt-4 pb-3 flex items-center justify-between" style="border-bottom: 1px solid var(--border-color)">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)">
                                    <i data-lucide="bell" class="w-3.5 h-3.5 text-white"></i>
                                </div>
                                <span class="font-bold text-sm" style="color: var(--text-primary)">Notifikasi</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold text-white"
                                    style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 2px 4px rgba(239,68,68,0.4);"
                                    x-show="unreadCount > 0" x-text="unreadCount + ' Baru'"></span>
                            </div>
                            <button @click="markAllRead()" class="text-xs font-semibold px-2.5 py-1 rounded-lg transition-all hover:shadow-sm"
                                style="color: var(--color-primary); background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.15);">
                                Tandai Semua Dibaca
                            </button>
                        </div>

                        <!-- Tab Controls -->
                        <div class="flex text-xs font-semibold" style="border-bottom: 1px solid var(--border-color); background: rgba(0,0,0,0.02);">
                            <button @click="notifTab = 'unread'" class="flex-1 py-2.5 text-center border-b-2 transition-all duration-150"
                                :class="notifTab === 'unread' ? 'border-blue-500 text-blue-600 font-bold bg-blue-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50/50'">
                                <span class="flex items-center justify-center gap-1">
                                    <i data-lucide="inbox" class="w-3 h-3"></i> Belum Dibaca
                                </span>
                            </button>
                            <button @click="notifTab = 'all'" class="flex-1 py-2.5 text-center border-b-2 transition-all duration-150"
                                :class="notifTab === 'all' ? 'border-blue-500 text-blue-600 font-bold bg-blue-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50/50'">
                                <span class="flex items-center justify-center gap-1">
                                    <i data-lucide="list" class="w-3 h-3"></i> Semua
                                </span>
                            </button>
                        </div>

                        <!-- Dropdown List -->
                        <div class="max-h-[400px] overflow-y-auto custom-scrollbar-thin">
                            <template x-if="filteredNotifList().length === 0">
                                <div class="p-8 text-center flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 rounded-full bg-blue-50/50 dark:bg-slate-800/40 flex items-center justify-center mb-3 border border-blue-100/30 dark:border-slate-700/10 relative">
                                        <i data-lucide="bell" class="w-6 h-6 text-primary animate-[pulse_2s_infinite]"></i>
                                        <span class="absolute top-0 right-0 w-2.5 h-2.5 rounded-full bg-blue-400"></span>
                                    </div>
                                    <span class="font-semibold text-xs mb-1" style="color: var(--text-primary)">Kotak Masuk Bersih</span>
                                    <p class="text-[10px] leading-relaxed max-w-[200px]" style="color: var(--text-secondary)">
                                        Semua sunyi di sini. Seluruh pekerjaan Anda telah diselesaikan dengan sempurna!
                                    </p>
                                </div>
                            </template>

                            <template x-for="n in filteredNotifList()" :key="n.id">
                                <div class="relative group"
                                    :class="n._deleting ? 'opacity-30 pointer-events-none transition-opacity duration-500' : ''">
                                    <div class="px-4 py-3.5 border-b transition-all duration-200 cursor-pointer flex gap-3"
                                        :class="n.is_read == 0 ? 'bg-blue-50/50 dark:bg-blue-950/15' : 'hover:bg-gray-50/40 dark:hover:bg-slate-800/10'"
                                        style="border-color: var(--border-color)"
                                        @click="handleNotifClick(n)">

                                        <!-- Unread left indicator bar -->
                                        <div class="absolute left-0 top-0 bottom-0 w-0.5 rounded-r bg-blue-500 transition-all duration-300"
                                            :class="n.is_read == 0 ? 'opacity-100' : 'opacity-0'"></div>

                                        <!-- Left: Icon circle -->
                                        <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center border"
                                            :class="getNotifBgClass(n.tipe)">
                                            <i :data-lucide="getNotifIcon(n.tipe)" class="w-4 h-4" :class="getNotifIconColorClass(n.tipe)"></i>
                                        </div>

                                        <!-- Middle: Content -->
                                        <div class="flex-1 min-w-0 pr-10">
                                            <div class="flex items-center gap-1.5 mb-0.5">
                                                <span class="font-bold text-xs" style="color: var(--text-primary)" x-text="n.judul"></span>
                                                <!-- Unread pulse dot -->
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 flex-shrink-0 animate-pulse" x-show="n.is_read == 0"></span>
                                            </div>
                                            <p class="text-[11px] leading-relaxed mb-1.5" style="color: var(--text-secondary)" x-text="n.pesan"></p>
                                            <div class="flex items-center gap-1 text-[9px]" style="color: var(--text-secondary)">
                                                <i data-lucide="clock" class="w-3 h-3"></i>
                                                <span x-text="formatTime(n.created_at)"></span>
                                                <!-- Navigation hint -->
                                                <span x-show="getNotifLink(n)" class="ml-1 text-blue-400 font-medium">· Klik untuk buka →</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action buttons on hover - positioned over item -->
                                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-200 z-20 flex items-center gap-1">
                                        <!-- Mark Read (if unread) -->
                                        <button x-show="n.is_read == 0"
                                            @click.stop="quickDismiss(n.id)"
                                            class="w-7 h-7 rounded-full shadow-lg border flex items-center justify-center transition-all duration-150 hover:scale-110"
                                            style="background: white; border-color: #d1fae5;"
                                            title="Tandai dibaca">
                                            <i data-lucide="check" class="w-3.5 h-3.5" style="color: #059669"></i>
                                        </button>
                                        <!-- Delete button (always on hover) -->
                                        <button @click.stop="deleteNotif(n.id)"
                                            class="w-7 h-7 rounded-full shadow-lg border flex items-center justify-center transition-all duration-150 hover:scale-110"
                                            style="background: white; border-color: #fecaca;"
                                            title="Hapus notifikasi">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5" style="color: #ef4444"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Footer -->
                        <div class="px-4 py-2.5 text-center" style="border-top: 1px solid var(--border-color); background: rgba(0,0,0,0.02);">
                            <p class="text-[10px]" style="color: var(--text-secondary);">Klik notifikasi untuk navigasi otomatis &amp; tandai dibaca</p>
                        </div>
                    </div>
                </div>

                <!-- Dark mode toggle -->
                <button @click="toggleTheme()" class="p-2 rounded-lg" style="color: var(--text-secondary)" title="Toggle Dark Mode">
                    <i data-lucide="moon" class="w-5 h-5 theme-toggle-icon-light"></i>
                    <i data-lucide="sun" class="w-5 h-5 theme-toggle-icon-dark"></i>
                </button>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-4 md:p-6">
            <?= $content ?? '' ?>
        </main>

        <!-- Footer -->
        <footer class="px-6 py-3 text-center text-xs" style="color: var(--text-secondary); border-top: 1px solid var(--border-color)">
            &copy; <?= date('Y') ?> <?= htmlspecialchars($appName) ?> — Sistem Manajemen Gudang Ikan
        </footer>
    </div>

    <!-- Custom JS -->
    <script src="<?= $baseUrl ?>/js/api-client.js"></script>
    <script src="<?= $baseUrl ?>/js/utils.js"></script>
    <script src="<?= $baseUrl ?>/js/chart-config.js"></script>

    <script>
        function appLayout() {
            return {
                sidebarOpen: false,
                theme: localStorage.getItem('theme') || 'light',
                currentUser: JSON.parse(localStorage.getItem('user') || '{}'),
                notifList: [],
                unreadCount: 0,
                notifTab: 'unread',

                init() {
                    // Apply saved theme
                    document.documentElement.setAttribute('data-theme', this.theme);

                    // Init Lucide icons
                    if (window.lucide) lucide.createIcons();

                    // Check auth
                    const token = localStorage.getItem('token');
                    if (!token) {
                        window.location.href = '/peace_seafood/login';
                        return;
                    }

                    // Setup axios auth header
                    window.API_TOKEN = token;

                    // Load unread count
                    this.loadUnreadCount();

                    // Watchers untuk Alpine -> trigger lucide rendering setelah render DOM
                    this.$watch('notifList', () => {
                        this.$nextTick(() => {
                            if (window.lucide) lucide.createIcons();
                        });
                    });
                    this.$watch('notifTab', () => {
                        this.$nextTick(() => {
                            if (window.lucide) lucide.createIcons();
                        });
                    });

                    // Start background polling
                    this.startPolling();

                    // Apply any deep-linked glowing highlights
                    this.applyHighlightGlow();
                },

                toggleTheme() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    document.documentElement.setAttribute('data-theme', this.theme);
                    if (window.lucide) lucide.createIcons();
                },

                async loadUnreadCount() {
                    try {
                        const res = await apiClient.get('/notifikasi?unread=1');
                        this.unreadCount = res.data.data?.unread_count || 0;
                    } catch (e) {}
                },

                async loadNotif() {
                    try {
                        const res = await apiClient.get('/notifikasi');
                        this.notifList = res.data.data?.notifikasi || [];
                        this.unreadCount = res.data.data?.unread_count || 0;
                    } catch (e) {}
                },

                async loadNotifSilently() {
                    try {
                        const res = await apiClient.get('/notifikasi');
                        this.notifList = res.data.data?.notifikasi || [];
                        this.unreadCount = res.data.data?.unread_count || 0;
                    } catch (e) {}
                },

                async markRead(id) {
                    // Optimistic UI update first so tab count is instant
                    const n = this.notifList.find(x => x.id === id);
                    if (n && n.is_read == 0) {
                        n.is_read = 1;
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }
                    // ALWAYS await so state is persisted to DB before any navigation
                    try {
                        await apiClient.post(`/notifikasi/${id}/read`);
                    } catch (e) {
                        // If API fails, rollback UI
                        if (n) {
                            n.is_read = 0;
                            this.unreadCount++;
                        }
                        console.error('markRead failed:', e);
                    }
                },

                async markAllRead() {
                    try {
                        // Optimistic UI update first
                        const wasUnread = this.notifList.filter(n => n.is_read == 0).length;
                        this.notifList.forEach(n => n.is_read = 1);
                        this.unreadCount = 0;
                        // Fire API
                        apiClient.post('/notifikasi/read-all').catch(() => {});
                    } catch (e) {}
                },

                async quickDismiss(id) {
                    await this.markRead(id);
                },

                async deleteNotif(id) {
                    // Prevent click.away from closing dropdown while swal is open
                    window._swalOpen = true;
                    try {
                        const result = await Swal.fire({
                            title: 'Hapus Notifikasi?',
                            text: 'Notifikasi ini akan dihapus permanen.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: '<i class="lucide-trash-2" style="display:inline-block;width:14px;height:14px;vertical-align:middle;margin-right:5px;"></i> Ya, Hapus',
                            cancelButtonText: 'Batal',
                            customClass: {
                                popup: 'swal2-glassmorphic',
                                confirmButton: 'swal2-confirm-btn',
                                cancelButton: 'swal2-cancel-btn'
                            },
                            buttonsStyling: false,
                            reverseButtons: true
                        });
                        window._swalOpen = false;

                        if (result.isConfirmed) {
                            try {
                                await apiClient.delete(`/notifikasi/${id}`);
                                // Immediately remove from list
                                this.notifList = this.notifList.filter(x => x.id !== id);
                                this.unreadCount = this.notifList.filter(x => x.is_read == 0).length;

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil dihapus',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 2500,
                                    timerProgressBar: true,
                                    customClass: {
                                        popup: 'swal2-toast-glassmorphic'
                                    }
                                });
                            } catch (e) {
                                console.error('Delete failed:', e);
                                const errMsg = e.response?.data?.message || 'Gagal menghapus notifikasi';
                                iziToast.error({
                                    title: 'Gagal',
                                    message: errMsg,
                                    position: 'topRight'
                                });
                            }
                        }
                    } catch (e) {
                        window._swalOpen = false;
                        console.error(e);
                    }
                },

                async handleNotifClick(n) {
                    // AWAIT markRead so status is saved to DB BEFORE page navigates
                    if (n.is_read == 0) {
                        await this.markRead(n.id);
                    }
                    const link = this.getNotifLink(n);
                    if (link) {
                        // Close dropdown first, then navigate
                        window.dispatchEvent(new CustomEvent('close-notif-dropdown'));
                        window.location.href = link;
                    }
                },

                getNotifLink(n) {
                    if (n.tipe === 'timbangan_pending' || n.tipe === 'timbangan_eskalasi' || n.tipe === 'timbangan_selesai') {
                        return `/peace_seafood/stok/timbangan?highlight=timbangan-${n.reference_id}`;
                    }
                    if (n.tipe === 'stok_minimum') {
                        return `/peace_seafood/master-data/produk?highlight=produk-${n.reference_id}`;
                    }
                    if (n.tipe === 'hutang_jatuh_tempo') {
                        return `/peace_seafood/keuangan?highlight=debt-${n.reference_id}`;
                    }
                    return null;
                },

                applyHighlightGlow() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const highlightId = urlParams.get('highlight');
                    if (!highlightId) return;

                    const checkInterval = setInterval(() => {
                        const element = document.getElementById(highlightId) || document.querySelector(`[data-highlight="${highlightId}"]`);
                        if (element) {
                            clearInterval(checkInterval);

                            // Scroll smoothly into view
                            element.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });

                            // Add glowing class
                            element.classList.add('highlight-glow-pulse');

                            // Remove class on interaction
                            const removeHighlight = () => {
                                element.classList.remove('highlight-glow-pulse');
                                element.removeEventListener('click', removeHighlight);
                                element.removeEventListener('focusin', removeHighlight);
                                element.removeEventListener('mousedown', removeHighlight);
                            };

                            element.addEventListener('click', removeHighlight);
                            element.addEventListener('focusin', removeHighlight);
                            element.addEventListener('mousedown', removeHighlight);

                            // Auto remove after 10 seconds
                            setTimeout(removeHighlight, 10000);
                        }
                    }, 100);

                    // Safety timeout to prevent infinite checking
                    setTimeout(() => clearInterval(checkInterval), 6000);
                },

                filteredNotifList() {
                    if (this.notifTab === 'unread') {
                        return this.notifList.filter(x => x.is_read == 0);
                    }
                    return this.notifList;
                },

                getNotifBgClass(tipe) {
                    switch (tipe) {
                        case 'hutang_jatuh_tempo':
                        case 'timbangan_eskalasi':
                            return 'bg-red-50/80 dark:bg-red-950/30 text-red-500 border-red-100 dark:border-red-900/20';
                        case 'stok_minimum':
                            return 'bg-amber-50/80 dark:bg-amber-950/30 text-amber-500 border-amber-100 dark:border-amber-900/20';
                        case 'timbangan_selesai':
                            return 'bg-emerald-50/80 dark:bg-emerald-950/30 text-success border-emerald-100 dark:border-emerald-900/20';
                        case 'timbangan_pending':
                        default:
                            return 'bg-blue-50/80 dark:bg-blue-950/30 text-primary border-blue-100 dark:border-blue-900/20';
                    }
                },

                getNotifIcon(tipe) {
                    switch (tipe) {
                        case 'hutang_jatuh_tempo':
                            return 'credit-card';
                        case 'timbangan_eskalasi':
                            return 'alert-octagon';
                        case 'stok_minimum':
                            return 'package';
                        case 'timbangan_selesai':
                            return 'check-circle';
                        case 'timbangan_pending':
                        default:
                            return 'clock';
                    }
                },

                getNotifIconColorClass(tipe) {
                    switch (tipe) {
                        case 'hutang_jatuh_tempo':
                        case 'timbangan_eskalasi':
                            return 'text-red-500 dark:text-red-400';
                        case 'stok_minimum':
                            return 'text-amber-500 dark:text-amber-400';
                        case 'timbangan_selesai':
                            return 'text-success dark:text-emerald-400';
                        case 'timbangan_pending':
                        default:
                            return 'text-primary dark:text-blue-400';
                    }
                },

                getNotifToastIcon(tipe) {
                    switch (tipe) {
                        case 'hutang_jatuh_tempo':
                        case 'timbangan_eskalasi':
                            return 'error';
                        case 'stok_minimum':
                            return 'warning';
                        case 'timbangan_selesai':
                            return 'success';
                        case 'timbangan_pending':
                        default:
                            return 'info';
                    }
                },

                showClassyToast(n) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: true,
                        confirmButtonText: 'Buka Aksi',
                        showCancelButton: true,
                        cancelButtonText: 'Abaikan',
                        timer: 8000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'swal2-toast-glassmorphic swal2-clickable-toast',
                            confirmButton: 'swal2-toast-confirm-btn',
                            cancelButton: 'swal2-toast-cancel-btn'
                        },
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                            // Force rendering of dynamic icons inside custom sweetalert popups if any
                            if (window.lucide) lucide.createIcons();
                        }
                    });

                    Toast.fire({
                        icon: this.getNotifToastIcon(n.tipe),
                        title: `<strong>${n.judul}</strong>`,
                        html: `<div class="text-[11px] opacity-90 mt-1">${n.pesan}</div>`
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.handleNotifClick(n);
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            this.quickDismiss(n.id);
                        }
                    });
                },

                startPolling() {
                    // Polling senyap setiap 15 detik
                    setInterval(async () => {
                        try {
                            const res = await apiClient.get('/notifikasi?unread=1');
                            const newUnreadCount = res.data.data?.unread_count || 0;
                            const newUnreadList = res.data.data?.notifikasi || [];

                            if (newUnreadCount > this.unreadCount) {
                                // Cari notifikasi unread mana yang baru saja masuk
                                const oldIds = this.notifList.map(x => x.id);
                                const newItems = newUnreadList.filter(x => !oldIds.includes(x.id));

                                newItems.forEach(n => {
                                    this.showClassyToast(n);
                                });
                            }

                            this.unreadCount = newUnreadCount;

                            // Segarkan data notifikasi di memory secara senyap jika dropdown sedang ditutup
                            if (!this.open) {
                                this.loadNotifSilently();
                            }
                        } catch (e) {}
                    }, 15000);
                },

                formatTime(dateStr) {
                    if (!dateStr) return '';
                    try {
                        const t = dateStr.split(/[- :]/);
                        const d = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);
                        const now = new Date();

                        const hours = String(d.getHours()).padStart(2, '0');
                        const mins = String(d.getMinutes()).padStart(2, '0');

                        if (d.toDateString() === now.toDateString()) {
                            return `Hari ini, ${hours}:${mins}`;
                        }

                        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                        return `${d.getDate()} ${months[d.getMonth()]}, ${hours}:${mins}`;
                    } catch (e) {
                        return dateStr;
                    }
                },

                logout() {
                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                    window.location.href = '/peace_seafood/login';
                }
            };
        }
    </script>

    <!-- ═══════════════════════════════════════════════════════════════
         FLOATING CALCULATOR — FAB + POPUP
         Shortcut: Alt+C to toggle
    ═══════════════════════════════════════════════════════════════ -->
    <div x-data="floatingCalc()" x-init="initCalc()" @keydown.window="handleKey($event)">

        <!-- FAB Button -->
        <button
            @click="open = !open"
            title="Kalkulator (Alt+C)"
            class="fixed z-50 flex items-center justify-center rounded-full shadow-lg transition-all duration-300"
            style="
                bottom: 28px; right: 28px;
                width: 52px; height: 52px;
                background: var(--color-primary);
                color: white;
                border: none;
                cursor: pointer;
                box-shadow: 0 4px 20px rgba(37,99,235,0.4);
            "
            :style="open ? 'transform: rotate(45deg); background: var(--color-danger)' : ''">
            <svg x-show="!open" style="width:22px;height:22px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" />
            </svg>
            <svg x-show="!open" x-cloak style="display:none"></svg>
            <!-- X icon when open -->
            <svg x-show="open" x-cloak style="width:22px;height:22px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Calculator Popup -->
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            @click.outside="open = false"
            class="fixed z-50"
            style="bottom: 92px; right: 28px; width: 280px;">
            <div style="
                background: var(--bg-light);
                border: 1px solid var(--border-color);
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 20px 60px rgba(0,0,0,0.18), 0 0 0 1px rgba(255,255,255,0.05) inset;
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
            ">
                <!-- Header -->
                <div style="
                    padding: 12px 16px 8px;
                    display: flex; align-items: center; justify-content: space-between;
                    border-bottom: 1px solid var(--border-color);
                ">
                    <span style="font-size:12px; font-weight:600; color: var(--text-secondary); letter-spacing:0.05em; text-transform:uppercase;">Kalkulator</span>
                    <span style="font-size:10px; color: var(--text-secondary); opacity:0.6">Alt+C</span>
                </div>

                <!-- Display -->
                <div style="padding: 12px 16px 8px; text-align: right;">
                    <div style="font-size:11px; color: var(--text-secondary); min-height:16px; margin-bottom:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="expression || '&nbsp;'"></div>
                    <div style="font-size:28px; font-weight:700; color: var(--text-primary); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; line-height:1.2;" x-text="display"></div>
                </div>

                <!-- Buttons Grid -->
                <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:6px; padding: 8px 12px 14px;">

                    <!-- Row 1: AC, +/-, %, ÷ -->
                    <template x-for="btn in buttons" :key="btn.label">
                        <button
                            @click="pressBtn(btn)"
                            style="
                                height: 52px;
                                border-radius: 12px;
                                border: none;
                                font-size: 15px;
                                font-weight: 600;
                                cursor: pointer;
                                transition: all 0.12s ease;
                            "
                            :style="getBtnStyle(btn)"
                            @mousedown="$el.style.transform='scale(0.92)'"
                            @mouseup="$el.style.transform='scale(1)'"
                            @mouseleave="$el.style.transform='scale(1)'"
                            x-text="btn.label"></button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        function floatingCalc() {
            return {
                open: false,
                display: '0',
                expression: '',
                currentVal: '',
                operator: null,
                prevVal: null,
                justCalc: false,

                buttons: [{
                        label: 'AC',
                        type: 'fn'
                    },
                    {
                        label: '+/-',
                        type: 'fn'
                    },
                    {
                        label: '%',
                        type: 'fn'
                    },
                    {
                        label: '÷',
                        type: 'op'
                    },
                    {
                        label: '7',
                        type: 'num'
                    },
                    {
                        label: '8',
                        type: 'num'
                    },
                    {
                        label: '9',
                        type: 'num'
                    },
                    {
                        label: '×',
                        type: 'op'
                    },
                    {
                        label: '4',
                        type: 'num'
                    },
                    {
                        label: '5',
                        type: 'num'
                    },
                    {
                        label: '6',
                        type: 'num'
                    },
                    {
                        label: '−',
                        type: 'op'
                    },
                    {
                        label: '1',
                        type: 'num'
                    },
                    {
                        label: '2',
                        type: 'num'
                    },
                    {
                        label: '3',
                        type: 'num'
                    },
                    {
                        label: '+',
                        type: 'op'
                    },
                    {
                        label: '0',
                        type: 'zero'
                    },
                    {
                        label: '.',
                        type: 'num'
                    },
                    {
                        label: '⌫',
                        type: 'del'
                    },
                    {
                        label: '=',
                        type: 'eq'
                    },
                ],

                getBtnStyle(btn) {
                    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                    if (btn.type === 'op') {
                        return `background: var(--color-primary); color: white;`;
                    }
                    if (btn.type === 'eq') {
                        return `background: var(--color-success); color: white;`;
                    }
                    if (btn.type === 'del') {
                        return `background: var(--color-danger); color: white;`;
                    }
                    if (btn.type === 'fn') {
                        return isDark ?
                            `background: rgba(100,116,139,0.25); color: var(--text-primary);` :
                            `background: rgba(100,116,139,0.15); color: var(--text-primary);`;
                    }
                    // num / zero
                    return isDark ?
                        `background: rgba(255,255,255,0.07); color: var(--text-primary);` :
                        `background: rgba(0,0,0,0.05); color: var(--text-primary);`;
                },

                initCalc() {
                    // keyboard input support
                    window.addEventListener('keydown', (e) => {
                        if (!this.open) return;
                        const map = {
                            '0': '0',
                            '1': '1',
                            '2': '2',
                            '3': '3',
                            '4': '4',
                            '5': '5',
                            '6': '6',
                            '7': '7',
                            '8': '8',
                            '9': '9',
                            '.': '.',
                            'Enter': '=',
                            '=': '=',
                            '+': '+',
                            '-': '−',
                            '*': '×',
                            '/': '÷',
                            'Backspace': '⌫',
                            'Escape': 'AC'
                        };
                        const label = map[e.key];
                        if (!label) return;
                        e.preventDefault();
                        const btn = this.buttons.find(b => b.label === label);
                        if (btn) this.pressBtn(btn);
                        else if (label === '⌫') this.backspace();
                    });
                },

                handleKey(e) {
                    if (e.altKey && e.key === 'c') {
                        e.preventDefault();
                        this.open = !this.open;
                    }
                },

                pressBtn(btn) {
                    if (btn.type === 'num' || btn.type === 'zero') {
                        this.inputDigit(btn.label);
                    } else if (btn.type === 'op') {
                        this.inputOp(btn.label);
                    } else if (btn.type === 'eq') {
                        this.calculate();
                    } else if (btn.type === 'del') {
                        this.backspace();
                    } else if (btn.type === 'fn') {
                        if (btn.label === 'AC') this.clear();
                        else if (btn.label === '+/-') this.negate();
                        else if (btn.label === '%') this.percent();
                    }
                },

                inputDigit(d) {
                    if (this.justCalc) {
                        this.currentVal = '';
                        this.justCalc = false;
                    }
                    if (d === '.' && this.currentVal.includes('.')) return;
                    if (d === '.' && this.currentVal === '') this.currentVal = '0';
                    if (this.currentVal === '0' && d !== '.') this.currentVal = d;
                    else this.currentVal += d;
                    this.display = this.formatDisplay(this.currentVal);
                },

                inputOp(op) {
                    if (this.prevVal !== null && this.currentVal !== '' && !this.justCalc) {
                        this.doCalc();
                    }
                    if (this.currentVal !== '' || this.prevVal !== null) {
                        this.prevVal = parseFloat(this.currentVal || this.display.replace(/\./g, '').replace(',', '.')) || this.prevVal;
                        this.operator = op;
                        this.expression = this.formatDisplay(String(this.prevVal)) + ' ' + op;
                        this.currentVal = '';
                        this.justCalc = false;
                    }
                },

                calculate() {
                    if (this.prevVal === null || this.currentVal === '') return;
                    const expr = this.expression + ' ' + this.formatDisplay(this.currentVal) + ' =';
                    this.doCalc();
                    this.expression = expr;
                    this.operator = null;
                    this.prevVal = null;
                    this.justCalc = true;
                },

                doCalc() {
                    const a = this.prevVal;
                    const b = parseFloat(this.currentVal);
                    let result;
                    if (this.operator === '+') result = a + b;
                    else if (this.operator === '−') result = a - b;
                    else if (this.operator === '×') result = a * b;
                    else if (this.operator === '÷') result = b !== 0 ? a / b : 0;
                    else return;
                    // Round to avoid floating point noise
                    result = Math.round(result * 1e10) / 1e10;
                    this.currentVal = String(result);
                    this.display = this.formatDisplay(this.currentVal);
                },

                clear() {
                    this.display = '0';
                    this.expression = '';
                    this.currentVal = '';
                    this.operator = null;
                    this.prevVal = null;
                    this.justCalc = false;
                },

                negate() {
                    if (this.currentVal === '' || this.currentVal === '0') return;
                    this.currentVal = String(-parseFloat(this.currentVal));
                    this.display = this.formatDisplay(this.currentVal);
                },

                percent() {
                    if (this.currentVal === '') return;
                    this.currentVal = String(parseFloat(this.currentVal) / 100);
                    this.display = this.formatDisplay(this.currentVal);
                },

                backspace() {
                    if (this.justCalc) {
                        this.clear();
                        return;
                    }
                    this.currentVal = this.currentVal.slice(0, -1);
                    this.display = this.currentVal ? this.formatDisplay(this.currentVal) : '0';
                },

                formatDisplay(val) {
                    const num = parseFloat(val);
                    if (isNaN(num)) return val || '0';
                    // Show decimals if present
                    const parts = String(val).split('.');
                    const intPart = parseInt(parts[0]).toLocaleString('id-ID');
                    if (parts.length > 1) return intPart + ',' + parts[1];
                    return intPart;
                }
            };
        }
    </script>

    <?= $scripts ?? '' ?>

    <script>
        // Subtle Mouse Parallax for Coastal Background (desktop only)
        document.addEventListener('mousemove', (e) => {
            if (window.innerWidth < 768) return;
            const bg = document.getElementById('coastal-bg');
            if (!bg) return;
            const x = (window.innerWidth / 2 - e.clientX) * 0.012;
            const y = (window.innerHeight / 2 - e.clientY) * 0.012;
            bg.style.transform = `translate3d(${x}px, ${y}px, 0)`;
        });

        // Scroll Parallax for Background Elements (desktop only)
        window.addEventListener('scroll', () => {
            if (window.innerWidth < 768) return;
            const scrollY = window.scrollY;

            // Move celestial orb slower
            const orb = document.querySelector('.coastal-celestial-orb');
            if (orb) {
                orb.style.transform = `translate3d(0, ${scrollY * 0.15}px, 0)`;
            }

            // Move stars container
            const stars = document.querySelector('.coastal-stars-container');
            if (stars) {
                stars.style.transform = `translate3d(0, ${scrollY * 0.1}px, 0)`;
            }

            // Move shore slightly
            const shore = document.querySelector('.coastal-shore');
            if (shore) {
                shore.style.transform = `translate3d(0, ${scrollY * 0.08}px, 0)`;
            }
        });

        // Re-init lucide icons after Alpine renders
        document.addEventListener('alpine:initialized', () => {
            if (window.lucide) lucide.createIcons();
        });

        // 🌟 Universal Escape Key Modal Dismissal
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const overlays = document.querySelectorAll('.modal-overlay');
                overlays.forEach(overlay => {
                    // Try to resolve and set Alpine variables directly
                    const alpineEl = overlay.closest('[x-data]');
                    if (alpineEl && window.Alpine) {
                        try {
                            const data = window.Alpine.$data(alpineEl);
                            if (data) {
                                const modalVars = [
                                    'showModal', 'showBankModal', 'showUserModal',
                                    'showPreview', 'showDetailModal', 'showJual',
                                    'showBayarModal', 'showCreateModal'
                                ];
                                modalVars.forEach(v => {
                                    if (v in data) data[v] = false;
                                });
                            }
                        } catch (err) {}
                    }
                    // Dispatch click event on overlay to trigger @click.self triggers
                    overlay.dispatchEvent(new MouseEvent('click', {
                        bubbles: true,
                        view: window
                    }));
                });
            }
        });
    </script>
</body>

</html>