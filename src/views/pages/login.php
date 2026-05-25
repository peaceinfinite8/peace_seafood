<?php
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
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= htmlspecialchars($appName) ?></title>
    <script src="/peace_seafood/js/tailwindcss.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            overflow-x: hidden;
            overflow-y: auto;
            min-height: 100vh;
            background-color: #020617;
        }

        /* ═══════════════════════════════════════════════════════════════════════════
           COLOR SYSTEM & VARIABLES — DYNAMIC BEACH STATES
           ═══════════════════════════════════════════════════════════════════════════ */
        .page-bg {
            position: relative;
            overflow: hidden;
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
            transition: background 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Smooth Transition Engine for all Dynamic Colors */
        .page-bg,
        .page-bg *,
        .page-bg *::before,
        .page-bg *::after {
            transition: background 1.5s cubic-bezier(0.4, 0, 0.2, 1),
                background-color 1.5s cubic-bezier(0.4, 0, 0.2, 1),
                border-color 1.5s cubic-bezier(0.4, 0, 0.2, 1),
                color 1.0s ease,
                fill 1.5s cubic-bezier(0.4, 0, 0.2, 1),
                box-shadow 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* 🌅 PAGI: 06:00 - 11:00 (Soft sunrise pastel shore) */
        .theme-morning {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 45%, #ffedd5 100%);
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --card-glass: rgba(255, 255, 255, 0.42);
            --card-border: rgba(255, 255, 255, 0.55);
            --primary: #0284c7;
            --primary-glow: rgba(2, 132, 199, 0.25);
            --input-bg: rgba(255, 255, 255, 0.65);
            --input-border: rgba(14, 165, 233, 0.25);
            --input-focus-border: #0284c7;
            --wave-1: rgba(14, 165, 233, 0.12);
            --wave-2: rgba(56, 189, 248, 0.2);
            --wave-3: rgba(186, 230, 253, 0.35);
            --wave-4: rgba(255, 255, 255, 0.7);
            --btn-bg: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            --btn-shadow: rgba(2, 132, 199, 0.25);
            --role-bg: rgba(255, 255, 255, 0.45);
            --role-border: rgba(255, 255, 255, 0.4);
            --divider-line: rgba(15, 23, 42, 0.08);
            --sparkle-color: rgba(253, 224, 71, 0.65);
            --sparkle-glow: rgba(253, 224, 71, 0.35);
            --sand-gradient: linear-gradient(to bottom, #f5ebe0 0%, #e3d5ca 100%);
        }

        /* ☀️ SIANG: 11:00 - 15:00 (Turquoise crystal shore) */
        .theme-noon {
            background: linear-gradient(135deg, #bae6fd 0%, #7dd3fc 35%, #99f6e4 100%);
            --text-primary: #0f172a;
            --text-secondary: #334155;
            --text-muted: #475569;
            --card-glass: rgba(255, 255, 255, 0.35);
            --card-border: rgba(255, 255, 255, 0.45);
            --primary: #0d9488;
            --primary-glow: rgba(13, 148, 136, 0.2);
            --input-bg: rgba(255, 255, 255, 0.55);
            --input-border: rgba(13, 148, 136, 0.25);
            --input-focus-border: #0d9488;
            --wave-1: rgba(20, 184, 166, 0.18);
            --wave-2: rgba(6, 182, 212, 0.25);
            --wave-3: rgba(56, 189, 248, 0.38);
            --wave-4: rgba(255, 255, 255, 0.75);
            --btn-bg: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%);
            --btn-shadow: rgba(13, 148, 136, 0.3);
            --role-bg: rgba(255, 255, 255, 0.4);
            --role-border: rgba(255, 255, 255, 0.3);
            --divider-line: rgba(15, 23, 42, 0.08);
            --sparkle-color: rgba(255, 255, 255, 0.85);
            --sparkle-glow: rgba(6, 182, 212, 0.4);
            --sand-gradient: linear-gradient(to bottom, #f5ebe0 0%, #e3d5ca 100%);
        }


        /* 🌙 MALAM: 18:30 - 06:00 (Deep starry night beach) */
        .theme-night {
            background: linear-gradient(135deg, #020617 0%, #071e3d 60%, #020617 100%);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #475569;
            --card-glass: rgba(5, 16, 35, 0.6);
            --card-border: rgba(6, 182, 212, 0.18);
            --primary: #38bdf8;
            --primary-glow: rgba(56, 189, 248, 0.15);
            --input-bg: rgba(2, 10, 24, 0.45);
            --input-border: rgba(6, 182, 212, 0.22);
            --input-focus-border: #38bdf8;
            --wave-1: rgba(6, 182, 212, 0.07);
            --wave-2: rgba(14, 165, 233, 0.12);
            --wave-3: rgba(2, 8, 19, 0.35);
            --wave-4: rgba(2, 8, 19, 0.6);
            --btn-bg: linear-gradient(135deg, #0ea5e9 0%, #14b8a6 100%);
            --btn-shadow: rgba(6, 182, 212, 0.3);
            --role-bg: rgba(2, 10, 24, 0.35);
            --role-border: rgba(255, 255, 255, 0.04);
            --divider-line: rgba(255, 255, 255, 0.06);
            --sparkle-color: rgba(14, 165, 233, 0.85);
            --sparkle-glow: rgba(20, 184, 166, 0.55);
            --sand-gradient: linear-gradient(to bottom, #050b14 0%, #01040a 100%);
        }

        /* ═══════════════════════════════════════════════════════════════════════════
           PARALLAX WAVES & SANDY BOTTOM
           ═══════════════════════════════════════════════════════════════════════════ */
        .waves-container {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 180px;
            pointer-events: none;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .editorial-waves {
            width: 100%;
            height: 140px;
            margin-bottom: -1px;
            /* Align perfectly with sand layer */
        }

        .parallax-waves>use {
            animation: move-forever 20s cubic-bezier(.55, .5, .45, .5) infinite;
        }

        .parallax-waves>use:nth-child(1) {
            animation-delay: -2s;
            animation-duration: 9s;
        }

        .parallax-waves>use:nth-child(2) {
            animation-delay: -3s;
            animation-duration: 12s;
        }

        .parallax-waves>use:nth-child(3) {
            animation-delay: -4s;
            animation-duration: 15s;
        }

        .parallax-waves>use:nth-child(4) {
            animation-delay: -5s;
            animation-duration: 20s;
        }

        @keyframes move-forever {
            0% {
                transform: translate3d(-90px, 0, 0);
            }

            100% {
                transform: translate3d(85px, 0, 0);
            }
        }

        .sand-layer {
            width: 100%;
            height: 40px;
            background: var(--sand-gradient);
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.02);
        }

        /* ═══════════════════════════════════════════════════════════════════════════
           SPARKLES & STARS PARTICLES SYSTEM
           ═══════════════════════════════════════════════════════════════════════════ */
        .sparkle-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }

        .sparkle {
            position: absolute;
            border-radius: 50%;
            background: var(--sparkle-color);
            box-shadow: 0 0 8px var(--sparkle-glow);
            animation: float-sparkle var(--speed, 12s) ease-in-out infinite;
            opacity: 0;
        }

        @keyframes float-sparkle {
            0% {
                transform: translateY(105vh) translateX(0) scale(0.5);
                opacity: 0;
            }

            30% {
                opacity: var(--max-opacity, 0.6);
            }

            85% {
                opacity: var(--max-opacity, 0.6);
            }

            100% {
                transform: translateY(-5vh) translateX(var(--drift, 50px)) scale(1.2);
                opacity: 0;
            }
        }

        /* ═══════════════════════════════════════════════════════════════════════════
           GLASSMORPHIC LOGIN CARD
           ═══════════════════════════════════════════════════════════════════════════ */
        .login-card {
            background: var(--card-glass);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(24px) saturate(140%);
            -webkit-backdrop-filter: blur(24px) saturate(140%);
            border-radius: 24px;
            padding: 28px 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08),
                0 0 40px rgba(255, 255, 255, 0.01) inset;
            position: relative;
            overflow: hidden;
            animation: card-enter 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
            width: 100%;
            max-width: 440px;
        }

        @media (min-width: 640px) {
            .login-card {
                padding: 38px 34px;
            }
        }

        @keyframes card-enter {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Inputs */
        .input-wrapper {
            position: relative;
            width: 100%;
        }

        .input-field {
            width: 100%;
            height: 48px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            padding: 0 16px 0 46px;
            color: var(--text-primary);
            font-size: 14px;
            outline: none;
        }

        .input-field:focus {
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 4px var(--primary-glow);
        }

        .input-field::placeholder {
            color: var(--text-muted);
            opacity: 0.75;
        }

        .input-icon-left {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--text-muted);
            pointer-events: none;
        }

        .input-icon-right {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            padding: 4px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
        }

        /* Buttons */
        .btn-login {
            width: 100%;
            height: 48px;
            border-radius: 12px;
            background: var(--btn-bg);
            border: none;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 6px 20px var(--btn-shadow);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px var(--btn-shadow);
            opacity: 0.95;
        }

        .btn-login:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .role-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 16px;
        }

        .role-card {
            padding: 14px 8px;
            border-radius: 12px;
            background: var(--role-bg);
            border: 1px solid var(--role-border);
            cursor: pointer;
            text-align: center;
            color: var(--text-primary);
        }

        .role-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        }

        .role-icon-wrapper {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin: 0 auto 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Checkbox custom */
        .custom-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .custom-checkbox input {
            display: none;
        }

        .checkbox-box {
            width: 18px;
            height: 18px;
            border-radius: 5px;
            border: 1.5px solid var(--input-border);
            background: var(--input-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .custom-checkbox input:checked+.checkbox-box {
            background: var(--primary);
            border-color: var(--primary);
        }

        .error-box {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 10px;
            padding: 10px 14px;
            color: #fca5a5;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .spinner {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Entrance Animations */
        .animate-fade-in-up {
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .animate-fade-in-left {
            animation: fadeInLeft 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-15px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* 📱 VERTICAL HEIGHT RESPONSIVE ENGINE — PREVENTS VERTICAL SCROLLING ON SHORT VIEWPORTS (e.g. 1366x768 LAPTOPS) */
        @media (max-height: 740px) {
            .page-bg {
                padding: 16px 12px !important;
            }

            .login-card {
                padding: 20px 24px !important;
                border-radius: 20px !important;
            }

            /* Header adjustments */
            .login-card .text-center.mb-8 {
                margin-bottom: 14px !important;
            }

            .login-card .w-12.h-12 {
                width: 36px !important;
                height: 36px !important;
                border-radius: 8px !important;
                margin-bottom: 6px !important;
                font-size: 13px !important;
            }

            .login-card h2.text-xl {
                font-size: 15px !important;
            }

            .login-card p.text-xs.mt-1 {
                font-size: 10px !important;
                margin-top: 2px !important;
            }

            /* Form fields spacing & margins */
            .login-card form.space-y-5 {
                margin-top: 10px !important;
            }

            .login-card form.space-y-5> :not([hidden])~ :not([hidden]) {
                margin-top: 10px !important;
            }

            .login-card label {
                margin-bottom: 4px !important;
                font-size: 10px !important;
            }

            /* Inputs & Button overrides */
            .input-field {
                height: 38px !important;
                font-size: 13px !important;
                border-radius: 8px !important;
            }

            .input-icon-left {
                width: 16px !important;
                height: 16px !important;
                left: 12px !important;
            }

            .input-field {
                padding-left: 36px !important;
            }

            .btn-login {
                height: 38px !important;
                font-size: 13px !important;
                border-radius: 8px !important;
            }

            /* Remember & Forgot Password */
            .custom-checkbox .checkbox-box {
                width: 16px !important;
                height: 16px !important;
                border-radius: 4px !important;
            }

            .custom-checkbox span.text-xs,
            .login-card a.text-xs {
                font-size: 11px !important;
            }

            /* Divider */
            .login-card .my-6 {
                margin-top: 10px !important;
                margin-bottom: 10px !important;
            }

            .login-card .text-\[10px\] {
                font-size: 9px !important;
            }

            /* Autofill Credentials buttons */
            .role-cards {
                gap: 8px !important;
                margin-top: 8px !important;
            }

            .role-card {
                padding: 6px 4px !important;
                border-radius: 8px !important;
            }

            .role-icon-wrapper {
                width: 26px !important;
                height: 26px !important;
                margin-bottom: 4px !important;
            }

            .role-icon-wrapper svg {
                width: 13px !important;
                height: 13px !important;
            }

            .role-card .text-xs {
                font-size: 10px !important;
            }

            .role-card .text-\[10px\] {
                font-size: 8px !important;
            }

            /* Waves & Sandy Shoreline adjustments to prevent card overlap */
            .waves-container {
                height: 110px !important;
            }

            .editorial-waves {
                height: 80px !important;
            }

            .sand-layer {
                height: 30px !important;
            }

            /* Copyright footer */
            .page-bg .mt-6 {
                margin-top: 8px !important;
            }

            .page-bg .mt-6 p {
                font-size: 10px !important;
            }
        }

        /* ═══════════════════════════════════════════════════════════════════════════
           PREMIUM USER EXPERIENCE CUSTOM STYLES
           ═══════════════════════════════════════════════════════════════════════════ */

        /* Input Focus Glowing Wave Pulse */
        .input-field:focus {
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 4px var(--primary-glow);
            animation: input-pulse 2s infinite ease-in-out;
        }

        @keyframes input-pulse {

            0%,
            100% {
                box-shadow: 0 0 0 4px var(--primary-glow);
            }

            50% {
                box-shadow: 0 0 0 6px var(--primary-glow), 0 0 15px var(--primary-glow);
            }
        }

        /* 🔔 SWEETALERT2 GLASSMORPHIC THEMED OVERRIDES */
        .swal2-popup.swal2-glassmorphic {
            background: var(--card-glass) !important;
            border: 1px solid var(--card-border) !important;
            backdrop-filter: blur(24px) saturate(140%) !important;
            -webkit-backdrop-filter: blur(24px) saturate(140%) !important;
            border-radius: 24px !important;
            color: var(--text-primary) !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15) !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-title {
            color: var(--text-primary) !important;
            font-family: 'Inter', sans-serif !important;
            font-weight: 700 !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-html-container {
            color: var(--text-secondary) !important;
            font-family: 'Inter', sans-serif !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-confirm {
            background: var(--btn-bg) !important;
            box-shadow: 0 4px 15px var(--btn-shadow) !important;
            border-radius: 12px !important;
            padding: 10px 24px !important;
            font-weight: 600 !important;
            font-family: 'Inter', sans-serif !important;
            border: none !important;
            color: white !important;
            cursor: pointer;
            transition: all 0.3s;
        }

        .swal2-popup.swal2-glassmorphic .swal2-confirm:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .swal2-popup.swal2-glassmorphic .swal2-confirm:focus {
            box-shadow: 0 0 0 3px var(--primary-glow) !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-icon.swal2-error {
            border-color: #ef4444 !important;
            color: #ef4444 !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-icon.swal2-error [class^=swal2-x-mark-line] {
            background-color: #ef4444 !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-icon.swal2-success {
            border-color: #10b981 !important;
            color: #10b981 !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-icon.swal2-success [class^=swal2-success-line] {
            background-color: #10b981 !important;
        }

        .swal2-popup.swal2-glassmorphic .swal2-icon.swal2-success .swal2-success-ring {
            border: 4px solid rgba(16, 185, 129, 0.2) !important;
        }

        /* 🧭 FULL PAGE TRANSITION LOADER OVERLAY */
        .full-page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(2, 6, 23, 0.85);
            backdrop-filter: blur(24px) saturate(140%);
            -webkit-backdrop-filter: blur(24px) saturate(140%);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
        }

        /* Premium Nautical Compass Spinner */
        .compass-outer {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 4px dashed rgba(56, 189, 248, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: spin-compass 16s linear infinite;
            box-shadow: 0 0 40px rgba(56, 189, 248, 0.2), inset 0 0 20px rgba(56, 189, 248, 0.1);
            background: rgba(255, 255, 255, 0.02);
        }

        .compass-inner {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            border: 2px solid #38bdf8;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: inset 0 0 20px rgba(56, 189, 248, 0.35);
        }

        .compass-needle {
            width: 10px;
            height: 66px;
            background: linear-gradient(to bottom, #ef4444 50%, #38bdf8 50%);
            clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
            animation: jitter-needle 1.2s ease-in-out infinite;
        }

        @keyframes spin-compass {
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes jitter-needle {

            0%,
            100% {
                transform: rotate(0deg);
            }

            20% {
                transform: rotate(18deg);
            }

            40% {
                transform: rotate(-12deg);
            }

            60% {
                transform: rotate(22deg);
            }

            80% {
                transform: rotate(-18deg);
            }
        }

        .loading-text-glow {
            text-shadow: 0 0 12px rgba(56, 189, 248, 0.6);
            animation: pulse-glow 2s infinite ease-in-out;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                opacity: 0.8;
            }

            50% {
                opacity: 1;
                text-shadow: 0 0 22px rgba(56, 189, 248, 0.95);
            }
        }

        /* 🎵 SOUND TOGGLE CONTROL BUTTON */
        .sound-toggle-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px) saturate(120%);
            -webkit-backdrop-filter: blur(12px) saturate(120%);
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: var(--text-primary);
            cursor: pointer;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sound-toggle-btn:hover {
            transform: scale(1.08);
            background: rgba(255, 255, 255, 0.22);
            border-color: rgba(255, 255, 255, 0.35);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        @keyframes soundBar {

            0%,
            100% {
                height: 4px;
            }

            50% {
                height: 16px;
            }
        }

        /* ☀️/🌙 CELESTIAL SUN & MOON ORB SYSTEM */
        .celestial-container {
            position: absolute;
            top: -95px;
            left: 50%;
            transform: translateX(-50%);
            width: 140px;
            height: 140px;
            pointer-events: none;
            z-index: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .celestial-orb {
            width: 66px;
            height: 66px;
            border-radius: 50%;
            position: relative;
            filter: blur(1px);
            animation: float-celestial 6s ease-in-out infinite;
            transition: all 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes float-celestial {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-10px) rotate(4deg);
            }
        }

        /* Dynamic theme styling for celestial orb */
        .theme-morning .celestial-orb {
            background: radial-gradient(circle, #fef08a 0%, #f97316 70%);
            box-shadow: 0 0 45px rgba(249, 115, 22, 0.65), 0 0 80px rgba(253, 224, 71, 0.45);
        }

        .theme-noon .celestial-orb {
            background: radial-gradient(circle, #ffffff 15%, #fef08a 70%);
            box-shadow: 0 0 55px rgba(253, 224, 71, 0.85), 0 0 95px rgba(255, 255, 255, 0.65);
        }

        .theme-night .celestial-orb {
            background: radial-gradient(circle, #e2e8f0 20%, #94a3b8 70%);
            box-shadow: 0 0 35px rgba(148, 163, 184, 0.4), 0 0 75px rgba(56, 189, 248, 0.35);
        }

        /* Moon Crescent Overlay */
        .theme-night .celestial-orb::after {
            content: '';
            position: absolute;
            top: -4px;
            right: -4px;
            width: 66px;
            height: 66px;
            border-radius: 50%;
            background: rgba(2, 6, 23, 0.92);
            box-shadow: inset -6px 6px 12px rgba(2, 6, 23, 0.95);
            transition: all 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .theme-morning .celestial-orb::after,
        .theme-noon .celestial-orb::after {
            content: '';
            position: absolute;
            width: 0;
            height: 0;
            opacity: 0;
            transition: all 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-[#020617]">

    <div class="page-bg" x-data="loginPage()" x-init="init()" :class="'theme-' + currentTheme">

        <!-- 🎵 PREMIUM OCEAN WAVE AMBIENCE TOGGLE -->
        <button type="button" @click="toggleSound()" class="sound-toggle-btn" :title="isMuted ? 'Aktifkan Suara Ombak' : 'Bisukan Suara Ombak'">
            <!-- Animated Sound Wave Bars when unmuted -->
            <div x-show="!isMuted" class="flex items-end gap-0.5 h-4 w-4 justify-center" x-cloak>
                <div class="w-0.5 bg-current animate-[soundBar_0.8s_ease-in-out_infinite]"></div>
                <div class="w-0.5 bg-current animate-[soundBar_0.8s_ease-in-out_0.2s_infinite]"></div>
                <div class="w-0.5 bg-current animate-[soundBar_0.8s_ease-in-out_0.4s_infinite]"></div>
            </div>
            <!-- Static Muted Icon -->
            <svg x-show="isMuted" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
            </svg>
        </button>

        <!-- 🧭 FULL-SCREEN NAUTICAL PAGE TRANSITION LOADING OVERLAY -->
        <div class="full-page-loader" x-show="showLoader" x-transition.opacity.duration.700ms x-cloak>
            <div class="flex flex-col items-center gap-6">
                <!-- Glowing Nautical Compass -->
                <div class="compass-outer">
                    <div class="compass-inner">
                        <div class="compass-needle"></div>
                    </div>
                </div>

                <!-- Progressive Status Indicator -->
                <div class="text-center space-y-2 mt-4">
                    <h3 class="text-lg font-bold tracking-wide text-sky-400 loading-text-glow uppercase">Mempersiapkan Masuk</h3>
                    <p class="text-xs text-slate-300 transition-all duration-300 h-6" x-text="loaderStatusText"></p>
                </div>

                <!-- Tiny progressive loading line indicator -->
                <div class="w-48 h-1 bg-slate-800 rounded-full overflow-hidden mt-2">
                    <div class="h-full bg-gradient-to-r from-sky-400 to-teal-400 transition-all duration-300" :style="'width: ' + loaderProgress + '%'"></div>
                </div>
            </div>
        </div>



        <!-- ☀️ SUN SPARKLES / MOONLIGHT STARS SYSTEM -->
        <div class="sparkle-container">
            <div class="sparkle" style="--speed: 12s; --drift: 80px; --max-opacity: 0.6; left: 6%; width: 6px; height: 6px; animation-delay: 0s;"></div>
            <div class="sparkle" style="--speed: 18s; --drift: -50px; --max-opacity: 0.7; left: 16%; width: 9px; height: 9px; animation-delay: 2s;"></div>
            <div class="sparkle" style="--speed: 14s; --drift: 100px; --max-opacity: 0.5; left: 28%; width: 5px; height: 5px; animation-delay: 4s;"></div>
            <div class="sparkle" style="--speed: 22s; --drift: -80px; --max-opacity: 0.65; left: 40%; width: 11px; height: 11px; animation-delay: 1s;"></div>
            <div class="sparkle" style="--speed: 16s; --drift: 60px; --max-opacity: 0.5; left: 52%; width: 6px; height: 6px; animation-delay: 6s;"></div>
            <div class="sparkle" style="--speed: 20s; --drift: -100px; --max-opacity: 0.8; left: 66%; width: 8px; height: 8px; animation-delay: 3s;"></div>
            <div class="sparkle" style="--speed: 13s; --drift: 70px; --max-opacity: 0.4; left: 78%; width: 5px; height: 5px; animation-delay: 5s;"></div>
            <div class="sparkle" style="--speed: 19s; --drift: -40px; --max-opacity: 0.6; left: 88%; width: 10px; height: 10px; animation-delay: 2s;"></div>
            <div class="sparkle" style="--speed: 15s; --drift: 90px; --max-opacity: 0.55; left: 95%; width: 7px; height: 7px; animation-delay: 7s;"></div>
        </div>

        <!-- 🌊 Animated Waves Background at Bottom over Sandy Shoreline -->
        <div class="waves-container">
            <svg class="editorial-waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
                <defs>
                    <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s58 18 88 18 58-18 88-18 58 18 88 18v44h-352z" />
                </defs>
                <g class="parallax-waves">
                    <use xlink:href="#gentle-wave" x="48" y="0" fill="var(--wave-1)" />
                    <use xlink:href="#gentle-wave" x="48" y="2" fill="var(--wave-2)" />
                    <use xlink:href="#gentle-wave" x="48" y="4" fill="var(--wave-3)" />
                    <use xlink:href="#gentle-wave" x="48" y="6" fill="var(--wave-4)" />
                </g>
            </svg>
            <div class="sand-layer"></div>
        </div>

        <!-- 📦 Main Centered Layout Container -->
        <div class="w-full flex flex-col items-center justify-center relative z-10 py-6">

            <div class="relative w-full max-w-[440px] flex flex-col items-center px-4">

                <!-- ☀️/🌙 CELESTIAL SUN & MOON ORB -->
                <div class="celestial-container">
                    <div class="celestial-orb"></div>
                </div>

                <div class="login-card">
                    <!-- Card Header -->
                    <div class="text-center mb-8">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-white shadow-md border border-white/20 mx-auto mb-3 overflow-hidden bg-gradient-to-br from-cyan-400 to-teal-500">
                            <?php if ($appLogoBase64): ?>
                                <img src="<?= $appLogoBase64 ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?= htmlspecialchars($appLogoInitial) ?>
                            <?php endif; ?>
                        </div>
                        <h2 class="text-xl font-bold tracking-tight" :class="isDarkTheme ? 'text-white' : 'text-slate-900'"><?= htmlspecialchars($appName) ?></h2>
                        <p class="text-xs mt-1" :class="isDarkTheme ? 'text-slate-400' : 'text-slate-600'">Sistem Manajemen Gudang Ikan</p>
                    </div>

                    <!-- Auth Form -->
                    <form @submit.prevent="doLogin()" novalidate class="space-y-5">

                        <!-- Email Input -->
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider mb-2" :class="isDarkTheme ? 'text-slate-300' : 'text-slate-700'">
                                Email atau Username
                            </label>
                            <div class="input-wrapper">
                                <input type="text" x-model="form.email" class="input-field"
                                    placeholder="email atau username" autocomplete="username" x-ref="emailInput">
                                <svg class="input-icon-left" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <p class="text-xs text-red-400 mt-1" x-show="errors.email" x-text="errors.email" x-cloak></p>
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider mb-2" :class="isDarkTheme ? 'text-slate-300' : 'text-slate-700'">
                                Password
                            </label>
                            <div class="input-wrapper">
                                <input :type="showPass ? 'text' : 'password'" x-model="form.password" class="input-field"
                                    placeholder="password" autocomplete="current-password">
                                <svg class="input-icon-left" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <button type="button" @click="showPass = !showPass" class="input-icon-right hover:opacity-80">
                                    <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-red-400 mt-1" x-show="errors.password" x-text="errors.password" x-cloak></p>
                        </div>

                        <!-- Remember & Forgot Password -->
                        <div class="flex items-center justify-between pt-1">
                            <label class="custom-checkbox">
                                <input type="checkbox" x-model="rememberMe">
                                <div class="checkbox-box">
                                    <svg x-show="rememberMe" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-xs select-none" :class="isDarkTheme ? 'text-slate-300' : 'text-slate-600'">Ingat saya</span>
                            </label>
                            <a href="#" class="text-xs font-semibold hover:opacity-80 transition-opacity" :class="isDarkTheme ? 'text-cyan-400' : 'text-cyan-600'">Lupa password?</a>
                        </div>


                        <!-- Submit Button -->
                        <button type="submit" class="btn-login" :disabled="loading">
                            <span x-show="!loading" class="flex items-center gap-2">
                                Masuk ke Dashboard
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span x-show="loading" class="flex items-center gap-2" x-cloak>
                                <span class="spinner"></span>
                                Memverifikasi...
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- 📝 Minimalist Branding Footer -->
            <div class="text-center mt-6 animate-fade-in-up" style="animation-delay: 0.2s">
                <p class="text-xs select-none transition-colors duration-1000" :class="isDarkTheme ? 'text-white/40' : 'text-slate-500/60'">&copy; <?= date('Y') ?> Peace Seafood — Warehouse Management System</p>
            </div>

        </div>

    </div>

    <script>
        function loginPage() {
            return {
                form: {
                    email: '',
                    password: ''
                },
                errors: {},
                errorMsg: '',
                loading: false,
                showPass: false,
                rememberMe: false,

                // Time & Theme States
                currentTheme: 'noon', // morning | noon | night
                manualTheme: 'auto', // auto | morning | noon | night
                selectedRole: '',
                themeInterval: null,

                // Sound, Page Transition, and Progressive Loader States
                isMuted: true,
                showLoader: false,
                loaderStatusText: 'Mengamankan koneksi...',
                loaderProgress: 0,
                audioCtx: null,
                waveGain: null,
                waveInterval: null,

                init() {
                    const token = localStorage.getItem('token');
                    if (token) {
                        window.location.href = '/peace_seafood/dashboard';
                        return;
                    }

                    // Set Initial Time Theme
                    this.updateTimeTheme();

                    // Start clock interval to auto-adjust every minute if in Auto mode
                    this.themeInterval = setInterval(() => {
                        if (this.manualTheme === 'auto') {
                            this.updateTimeTheme();
                        }
                    }, 60000);

                    this.$nextTick(() => {
                        if (this.$refs.emailInput) this.$refs.emailInput.focus();
                    });
                },

                // Helper to check if theme is night for text rendering
                get isDarkTheme() {
                    return this.currentTheme === 'night';
                },

                // Update Theme based on West Indonesian Time (WIB) hour
                updateTimeTheme() {
                    const hour = new Date().getHours();
                    if (hour >= 6 && hour < 11) {
                        this.currentTheme = 'morning';
                    } else if (hour >= 11 && hour < 18.5) {
                        // Merged 11:00 to 18:30 WIB into noon theme (previously Noon + Sunset)
                        this.currentTheme = 'noon';
                    } else {
                        this.currentTheme = 'night';
                    }
                },

                // Handle manual override
                setManualTheme(mode) {
                    this.manualTheme = mode;
                    if (mode === 'auto') {
                        this.updateTimeTheme();
                    } else {
                        this.currentTheme = mode;
                    }
                },

                fillCredential(email, password, role) {
                    this.form.email = email;
                    this.form.password = password;
                    this.selectedRole = role;
                    this.errorMsg = '';
                    this.errors = {};
                },

                validate() {
                    this.errors = {};
                    if (!this.form.email.trim()) this.errors.email = 'Email atau username wajib diisi';
                    if (!this.form.password) this.errors.password = 'Password wajib diisi';
                    return Object.keys(this.errors).length === 0;
                },

                async doLogin() {
                    if (!this.validate()) return;
                    this.loading = true;
                    this.errorMsg = '';
                    try {
                        const response = await axios.post(
                            '/peace_seafood/api/auth/login', {
                                email: this.form.email,
                                password: this.form.password
                            }, {
                                headers: {
                                    'Content-Type': 'application/json'
                                }
                            }
                        );
                        const data = response.data?.data;
                        if (data?.token) {
                            localStorage.setItem('token', data.token);
                            localStorage.setItem('user', JSON.stringify(data.user));

                            // Trigger Premium Full-Screen Transition Loader
                            this.triggerSuccessTransition('/peace_seafood/dashboard');
                            return;
                        }
                        this.showSweetAlert('Gagal Masuk', response.data?.message || 'Response tidak valid dari server', 'error');
                    } catch (e) {
                        if (localStorage.getItem('token')) {
                            this.triggerSuccessTransition('/peace_seafood/dashboard');
                            return;
                        }
                        const msg = e.response?.data?.message || 'Email atau password salah';
                        this.showSweetAlert('Gagal Masuk', msg, 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                // 🔔 SweetAlert2 Themed Custom Popup
                showSweetAlert(title, text, icon) {
                    Swal.fire({
                        title: title,
                        text: text,
                        icon: icon,
                        customClass: {
                            popup: 'swal2-glassmorphic',
                            confirmButton: 'swal2-confirm'
                        },
                        buttonsStyling: false,
                        background: 'transparent'
                    });
                },

                // 🧭 Progressive Nautical Transition Loader
                triggerSuccessTransition(redirectUrl) {
                    this.showLoader = true;
                    this.loaderProgress = 0;

                    const steps = [{
                            progress: 20,
                            text: 'Menghubungkan ke server gudang...'
                        },
                        {
                            progress: 45,
                            text: 'Memverifikasi otoritas akses...'
                        },
                        {
                            progress: 70,
                            text: 'Mengunduh data inventaris terbaru...'
                        },
                        {
                            progress: 90,
                            text: 'Mempersiapkan dasbor Anda...'
                        },
                        {
                            progress: 100,
                            text: 'Selamat Datang di Peace Seafood!'
                        }
                    ];

                    let currentStep = 0;
                    const runStep = () => {
                        if (currentStep < steps.length) {
                            const step = steps[currentStep];
                            this.loaderProgress = step.progress;
                            this.loaderStatusText = step.text;
                            currentStep++;

                            const delay = currentStep === steps.length ? 700 : 450;
                            setTimeout(runStep, delay);
                        } else {
                            setTimeout(() => {
                                window.location.href = redirectUrl;
                            }, 250);
                        }
                    };

                    setTimeout(runStep, 100);
                },

                // 🎵 Web Audio Ambient Wave Synthesizer
                toggleSound() {
                    this.isMuted = !this.isMuted;
                    if (!this.isMuted) {
                        this.startOceanSound();
                    } else {
                        this.stopOceanSound();
                    }
                },

                startOceanSound() {
                    try {
                        if (this.audioCtx) return;
                        const AudioContext = window.AudioContext || window.webkitAudioContext;
                        this.audioCtx = new AudioContext();

                        const bufferSize = 4 * this.audioCtx.sampleRate;
                        const noiseBuffer = this.audioCtx.createBuffer(1, bufferSize, this.audioCtx.sampleRate);
                        const output = noiseBuffer.getChannelData(0);

                        let b0, b1, b2, b3, b4, b5, b6;
                        b0 = b1 = b2 = b3 = b4 = b5 = b6 = 0.0;
                        for (let i = 0; i < bufferSize; i++) {
                            const white = Math.random() * 2 - 1;
                            b0 = 0.99886 * b0 + white * 0.0555179;
                            b1 = 0.99332 * b1 + white * 0.0750759;
                            b2 = 0.96900 * b2 + white * 0.1538520;
                            b3 = 0.86650 * b3 + white * 0.3104856;
                            b4 = 0.55000 * b4 + white * 0.5329522;
                            b5 = -0.7616 * b5 - white * 0.0168980;
                            output[i] = b0 + b1 + b2 + b3 + b4 + b5 + b6 + white * 0.5362;
                            output[i] *= 0.05; // Soft background volume multiplier
                            b6 = white * 0.115926;
                        }

                        const noiseNode = this.audioCtx.createBufferSource();
                        noiseNode.buffer = noiseBuffer;
                        noiseNode.loop = true;

                        const filter = this.audioCtx.createBiquadFilter();
                        filter.type = 'lowpass';
                        filter.frequency.value = 200;
                        filter.Q.value = 1.0;

                        this.waveGain = this.audioCtx.createGain();
                        this.waveGain.gain.value = 0.08;

                        noiseNode.connect(filter);
                        filter.connect(this.waveGain);
                        this.waveGain.connect(this.audioCtx.destination);

                        noiseNode.start(0);

                        const self = this;
                        const modulateWaves = () => {
                            if (!self.audioCtx) return;
                            const now = self.audioCtx.currentTime;
                            filter.frequency.setValueAtTime(filter.frequency.value, now);
                            filter.frequency.exponentialRampToValueAtTime(300, now + 3);
                            filter.frequency.exponentialRampToValueAtTime(140, now + 6);

                            self.waveGain.gain.setValueAtTime(self.waveGain.gain.value, now);
                            self.waveGain.gain.linearRampToValueAtTime(0.15, now + 3);
                            self.waveGain.gain.linearRampToValueAtTime(0.03, now + 6);
                        };

                        modulateWaves();
                        this.waveInterval = setInterval(modulateWaves, 6000);
                    } catch (err) {
                        console.warn('AudioContext failed to start:', err);
                        this.isMuted = true;
                    }
                },

                stopOceanSound() {
                    if (this.waveInterval) {
                        clearInterval(this.waveInterval);
                        this.waveInterval = null;
                    }
                    if (this.audioCtx) {
                        this.audioCtx.close().catch(() => {});
                        this.audioCtx = null;
                    }
                }
            };
        }
    </script>
</body>

</html>