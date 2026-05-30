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

$appName = $dbNameSetting ? $dbNameSetting['nilai'] : 'Peace Seafood';
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
    <link rel="stylesheet" href="/peace_seafood/inline-assets/css/pages/login.css">
</head>

<body class="bg-[#020617]">

    <div class="page-bg" x-data="loginPage()" x-init="init()" :class="'theme-' + currentTheme">

        <!-- 🎵 PREMIUM OCEAN WAVE AMBIENCE TOGGLE -->
        <button type="button" @click="toggleSound()" class="sound-toggle-btn"
            :title="isMuted ? 'Aktifkan Suara Ombak' : 'Bisukan Suara Ombak'">
            <!-- Animated Sound Wave Bars when unmuted -->
            <div x-show="!isMuted" class="flex items-end gap-0.5 h-4 w-4 justify-center" x-cloak>
                <div class="w-0.5 bg-current animate-[soundBar_0.8s_ease-in-out_infinite]"></div>
                <div class="w-0.5 bg-current animate-[soundBar_0.8s_ease-in-out_0.2s_infinite]"></div>
                <div class="w-0.5 bg-current animate-[soundBar_0.8s_ease-in-out_0.4s_infinite]"></div>
            </div>
            <!-- Static Muted Icon -->
            <svg x-show="isMuted" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
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
                    <h3 class="text-lg font-bold tracking-wide text-sky-400 loading-text-glow uppercase">Mempersiapkan
                        Masuk</h3>
                    <p class="text-xs text-slate-300 transition-all duration-300 h-6" x-text="loaderStatusText"></p>
                </div>

                <!-- Tiny progressive loading line indicator -->
                <div class="w-48 h-1 bg-slate-800 rounded-full overflow-hidden mt-2">
                    <div class="h-full bg-gradient-to-r from-sky-400 to-teal-400 transition-all duration-300"
                        :style="'width: ' + loaderProgress + '%'"></div>
                </div>
            </div>
        </div>



        <!-- ☀️ SUN SPARKLES / MOONLIGHT STARS SYSTEM -->
        <div class="sparkle-container">
            <div class="sparkle"
                style="--speed: 12s; --drift: 80px; --max-opacity: 0.6; left: 6%; width: 6px; height: 6px; animation-delay: 0s;">
            </div>
            <div class="sparkle"
                style="--speed: 18s; --drift: -50px; --max-opacity: 0.7; left: 16%; width: 9px; height: 9px; animation-delay: 2s;">
            </div>
            <div class="sparkle"
                style="--speed: 14s; --drift: 100px; --max-opacity: 0.5; left: 28%; width: 5px; height: 5px; animation-delay: 4s;">
            </div>
            <div class="sparkle"
                style="--speed: 22s; --drift: -80px; --max-opacity: 0.65; left: 40%; width: 11px; height: 11px; animation-delay: 1s;">
            </div>
            <div class="sparkle"
                style="--speed: 16s; --drift: 60px; --max-opacity: 0.5; left: 52%; width: 6px; height: 6px; animation-delay: 6s;">
            </div>
            <div class="sparkle"
                style="--speed: 20s; --drift: -100px; --max-opacity: 0.8; left: 66%; width: 8px; height: 8px; animation-delay: 3s;">
            </div>
            <div class="sparkle"
                style="--speed: 13s; --drift: 70px; --max-opacity: 0.4; left: 78%; width: 5px; height: 5px; animation-delay: 5s;">
            </div>
            <div class="sparkle"
                style="--speed: 19s; --drift: -40px; --max-opacity: 0.6; left: 88%; width: 10px; height: 10px; animation-delay: 2s;">
            </div>
            <div class="sparkle"
                style="--speed: 15s; --drift: 90px; --max-opacity: 0.55; left: 95%; width: 7px; height: 7px; animation-delay: 7s;">
            </div>
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
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-white shadow-md border border-white/20 mx-auto mb-3 overflow-hidden bg-gradient-to-br from-cyan-400 to-teal-500">
                            <?php if ($appLogoBase64): ?>
                                <img src="<?= $appLogoBase64 ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?= htmlspecialchars($appLogoInitial) ?>
                            <?php endif; ?>
                        </div>
                        <h2 class="text-xl font-bold tracking-tight"
                            :class="isDarkTheme ? 'text-white' : 'text-slate-900'"><?= htmlspecialchars($appName) ?>
                        </h2>
                        <p class="text-xs mt-1 font-semibold transition-all duration-300"
                            :class="isDarkTheme ? 'text-cyan-400' : 'text-cyan-600'"
                            x-text="mode === 'login' ? 'Sistem Manajemen Gudang Ikan' : (mode === 'signup' ? 'Aktivasi Trial Baru' : (mode === 'forgot' ? 'Pemulihan Sandi' : 'Atur Ulang Kata Sandi'))">
                        </p>
                    </div>

                    <!-- Auth Login Form -->
                    <form x-show="mode === 'login'" @submit.prevent="doLogin()" novalidate class="space-y-5"
                        x-transition.fade.duration.400ms>

                        <!-- Email Input -->
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider mb-2"
                                :class="isDarkTheme ? 'text-slate-300' : 'text-slate-700'">
                                Email atau Username
                            </label>
                            <div class="input-wrapper">
                                <input type="text" x-model="form.email" class="input-field"
                                    placeholder="email atau username" autocomplete="username" x-ref="emailInput">
                                <svg class="input-icon-left" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <p class="text-xs text-red-400 mt-1" x-show="errors.email" x-text="errors.email" x-cloak>
                            </p>
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider mb-2"
                                :class="isDarkTheme ? 'text-slate-300' : 'text-slate-700'">
                                Password
                            </label>
                            <div class="input-wrapper">
                                <input :type="showPass ? 'text' : 'password'" x-model="form.password"
                                    class="input-field" placeholder="password" autocomplete="current-password">
                                <svg class="input-icon-left" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <button type="button" @click="showPass = !showPass"
                                    class="input-icon-right hover:opacity-80">
                                    <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" x-cloak>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-red-400 mt-1" x-show="errors.password" x-text="errors.password"
                                x-cloak></p>
                        </div>

                        <!-- Remember & Forgot Password -->
                        <div class="flex items-center justify-between pt-1">
                            <label class="custom-checkbox">
                                <input type="checkbox" x-model="rememberMe">
                                <div class="checkbox-box">
                                    <svg x-show="rememberMe" class="w-3.5 h-3.5 text-white" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-xs select-none"
                                    :class="isDarkTheme ? 'text-slate-300' : 'text-slate-600'">Ingat saya</span>
                            </label>
                            <a href="#" @click.prevent="mode = 'forgot'"
                                class="text-xs font-semibold hover:opacity-80 transition-opacity"
                                :class="isDarkTheme ? 'text-cyan-400' : 'text-cyan-600'">Lupa password?</a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn-login" :disabled="loading">
                            <span x-show="!loading" class="flex items-center gap-2">
                                Masuk ke Dashboard
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span x-show="loading" class="flex items-center gap-2" x-cloak>
                                <span class="spinner"></span>
                                Memverifikasi...
                            </span>
                        </button>

                        <div class="text-center pt-2">
                            <span class="text-xs" :class="isDarkTheme ? 'text-slate-400' : 'text-slate-600'">Tertarik
                                dengan platform ini? </span>
                            <a href="#" @click.prevent="mode = 'signup'" class="text-xs font-bold hover:underline"
                                :class="isDarkTheme ? 'text-cyan-400' : 'text-cyan-600'">Daftar Trial Baru</a>
                        </div>
                    </form>

                    <!-- Sign Up (Trial Activation) Form -->
                    <form x-show="mode === 'signup'" @submit.prevent="doSignup()" novalidate class="space-y-5" x-cloak
                        x-transition.fade.duration.400ms>
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider mb-2"
                                :class="isDarkTheme ? 'text-slate-300' : 'text-slate-700'">
                                Email Pendaftaran
                            </label>
                            <div class="input-wrapper">
                                <input type="email" x-model="form.email" class="input-field"
                                    placeholder="Masukkan email Gmail Anda">
                                <svg class="input-icon-left" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                                </svg>
                            </div>
                            <div class="mt-2.5 p-3 rounded-xl border flex items-start gap-2.5"
                                :class="isDarkTheme ? 'bg-sky-950/20 border-sky-900/40 text-slate-300' : 'bg-sky-50 border-sky-100 text-slate-700'">
                                <svg class="w-5 h-5 text-sky-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-xs leading-relaxed">
                                    Email Anda harus sudah disetujui pre-approval oleh Developer. Kata sandi default
                                    akan dikirimkan otomatis ke inbox Gmail Anda.
                                </span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn-login" :disabled="loading">
                            <span x-show="!loading" class="flex items-center gap-2">
                                Aktivasi Akun Trial
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span x-show="loading" class="flex items-center gap-2" x-cloak>
                                <span class="spinner"></span>
                                Memproses Aktivasi...
                            </span>
                        </button>

                        <div class="text-center pt-2">
                            <a href="#" @click.prevent="mode = 'login'"
                                class="text-xs font-semibold hover:opacity-80 transition-opacity"
                                :class="isDarkTheme ? 'text-cyan-400' : 'text-cyan-600'">Kembali ke Login</a>
                        </div>
                    </form>

                    <!-- Forgot Password Form -->
                    <form x-show="mode === 'forgot'" @submit.prevent="doForgotPassword()" novalidate class="space-y-5"
                        x-cloak x-transition.fade.duration.400ms>
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider mb-2"
                                :class="isDarkTheme ? 'text-slate-300' : 'text-slate-700'">
                                Email Terdaftar
                            </label>
                            <div class="input-wrapper">
                                <input type="email" x-model="form.email" class="input-field"
                                    placeholder="Masukkan email terdaftar">
                                <svg class="input-icon-left" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn-login" :disabled="loading">
                            <span x-show="!loading" class="flex items-center gap-2">
                                Kirim Link Reset Password
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span x-show="loading" class="flex items-center gap-2" x-cloak>
                                <span class="spinner"></span>
                                Mengirim Link...
                            </span>
                        </button>

                        <div class="text-center pt-2">
                            <a href="#" @click.prevent="mode = 'login'"
                                class="text-xs font-semibold hover:opacity-80 transition-opacity"
                                :class="isDarkTheme ? 'text-cyan-400' : 'text-cyan-600'">Kembali ke Login</a>
                        </div>
                    </form>

                    <!-- Reset Password Form -->
                    <form x-show="mode === 'reset'" @submit.prevent="doResetPassword()" novalidate class="space-y-4"
                        x-cloak x-transition.fade.duration.400ms>
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider mb-2"
                                :class="isDarkTheme ? 'text-slate-300' : 'text-slate-700'">
                                Password Baru
                            </label>
                            <div class="input-wrapper">
                                <input :type="showPass ? 'text' : 'password'" x-model="form.password"
                                    class="input-field" placeholder="Password Baru">
                                <svg class="input-icon-left" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider mb-2"
                                :class="isDarkTheme ? 'text-slate-300' : 'text-slate-700'">
                                Konfirmasi Password Baru
                            </label>
                            <div class="input-wrapper">
                                <input :type="showPass ? 'text' : 'password'" x-model="form.password_confirm"
                                    class="input-field" placeholder="Konfirmasi Password Baru">
                                <svg class="input-icon-left" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Smart Strength Indicators -->
                        <div class="space-y-1.5 p-3 rounded-xl border text-left"
                            :class="isDarkTheme ? 'bg-slate-900/60 border-slate-800' : 'bg-slate-50 border-slate-200'">
                            <span
                                class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kekuatan
                                Sandi Baru:</span>
                            <div class="flex items-center gap-2 text-xs"
                                :class="passwordChecks.length ? 'text-emerald-400 font-medium' : 'text-slate-400'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        :d="passwordChecks.length ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'" />
                                </svg>
                                Minimal 8 karakter
                            </div>
                            <div class="flex items-center gap-2 text-xs"
                                :class="passwordChecks.upper ? 'text-emerald-400 font-medium' : 'text-slate-400'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        :d="passwordChecks.upper ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'" />
                                </svg>
                                Huruf besar (A-Z)
                            </div>
                            <div class="flex items-center gap-2 text-xs"
                                :class="passwordChecks.lower ? 'text-emerald-400 font-medium' : 'text-slate-400'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        :d="passwordChecks.lower ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'" />
                                </svg>
                                Huruf kecil (a-z)
                            </div>
                            <div class="flex items-center gap-2 text-xs"
                                :class="passwordChecks.number ? 'text-emerald-400 font-medium' : 'text-slate-400'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        :d="passwordChecks.number ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'" />
                                </svg>
                                Angka (0-9)
                            </div>
                            <div class="flex items-center gap-2 text-xs"
                                :class="passwordChecks.special ? 'text-emerald-400 font-medium' : 'text-slate-400'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        :d="passwordChecks.special ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'" />
                                </svg>
                                Karakter khusus (@, #, $, dll)
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn-login"
                            :disabled="loading || !isPasswordStrong || form.password !== form.password_confirm">
                            <span x-show="!loading" class="flex items-center gap-2">
                                Perbarui Password
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </span>
                            <span x-show="loading" class="flex items-center gap-2" x-cloak>
                                <span class="spinner"></span>
                                Memperbarui...
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- 📝 Minimalist Branding Footer -->
            <div class="text-center mt-6 animate-fade-in-up" style="animation-delay: 0.2s">
                <p class="text-xs select-none transition-colors duration-1000"
                    :class="isDarkTheme ? 'text-white/40' : 'text-slate-500/60'">&copy; <?= date('Y') ?> Peace Seafood —
                    Warehouse Management System</p>
            </div>

        </div>

    </div>

    <script src="/peace_seafood/inline-assets/js/pages/login.js"></script>
</body>

</html>