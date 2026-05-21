<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Peace Seafood</title>
    <meta name="description" content="Login ke sistem manajemen gudang Peace Seafood">

    <!-- CRITICAL: Initialize theme BEFORE Alpine.js to prevent errors -->
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            document.documentElement.style.setProperty('--current-theme', savedTheme);
        })();
    </script>

    <!-- Tailwind CSS - Suppressing warning for development -->
    <script data-tailwind-config="true" src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/izitoast.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/izitoast.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27%3E%3Crect width=%27100%27 height=%27100%27 fill=%27%23ffffff%27 rx=%2720%27/%3E%3Cellipse cx=%2750%27 cy=%2750%27 rx=%2728%27 ry=%2720%27 fill=%27%232563eb%27/%3E%3Ccircle cx=%2765%27 cy=%2750%27 r=%2715%27 fill=%27%232563eb%27/%3E%3Cpath d=%27M 22 50 Q 15 40 8 35 Q 10 50 8 65 Q 15 60 22 50 Z%27 fill=%27%230891b2%27 opacity=%270.9%27/%3E%3Ccircle cx=%2770%27 cy=%2748%27 r=%273%27 fill=%27%23ffffff%27/%3E%3Cpath d=%27M 45 28 Q 48 18 50 12 Q 52 18 55 28 Z%27 fill=%27%230891b2%27 opacity=%270.8%27/%3E%3C/svg%3E">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: var(--font-family-base, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
            color: var(--text-primary);
        }

        :root {
            --color-primary: #2563eb;
            --color-primary-dark: #1e40af;
            --color-info: #0ea5e9;
            --color-danger: #ef4444;
            --color-danger-soft: rgba(239, 68, 68, 0.1);
            --color-primary-soft: rgba(37, 99, 235, 0.1);
            --bg-page: linear-gradient(135deg, #0f172a 0%, #1d4ed8 48%, #0ea5e9 100%);
            --bg-card: #ffffff;
            --bg-surface: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --login-wave: rgba(255, 255, 255, 0.05);
        }

        [data-theme="dark"] {
            --bg-page: linear-gradient(135deg, #020617 0%, #1e3a8a 48%, #075985 100%);
            --bg-card: #1e293b;
            --bg-surface: #0f172a;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border-color: #334155;
            --login-wave: rgba(255, 255, 255, 0.03);
        }

        .text-red-500,
        .text-red-600 {
            color: var(--color-danger) !important;
        }

        .login-bg {
            min-height: 100vh;
            background: var(--bg-page);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-card {
            background: var(--bg-card);
            border-radius: 1.25rem;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px rgba(2, 6, 23, 0.22);
            border: 1px solid var(--border-color);
        }

        .form-input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1.5px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            color: var(--text-primary);
            background: var(--bg-surface);
        }

        .form-input:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            background: var(--bg-card);
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: var(--color-primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-login:hover:not(:disabled) {
            background: var(--color-primary-dark);
        }

        .btn-login:active:not(:disabled) {
            transform: scale(0.99);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .wave {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body>
    <div class="login-bg" x-data="loginPage()" x-init="init()">

        <!-- Background decoration -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none" style="position: fixed">
            <div class="wave"
                style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: var(--login-wave); border-radius: 50%">
            </div>
            <div class="wave"
                style="position: absolute; bottom: -150px; left: -150px; width: 500px; height: 500px; background: var(--login-wave); border-radius: 50%; animation-delay: 3s">
            </div>
        </div>

        <div class="login-card" style="position: relative; z-index: 10">

            <!-- Logo & Header -->
            <div class="text-center mb-8">
                <div
                    style="width: 64px; height: 64px; background: linear-gradient(135deg, var(--color-primary), var(--color-info)); border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem">
                    <span style="color: white; font-size: 1.5rem; font-weight: 800">PS</span>
                </div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin: 0 0 0.25rem">Peace
                    Seafood</h1>
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0">Sistem Manajemen Gudang Ikan</p>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="doLogin()" novalidate>

                <!-- Email/Username -->
                <div class="mb-4">
                    <label
                        style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.375rem">
                        Email atau Username
                    </label>
                    <div style="position: relative">
                        <i data-lucide="user"
                            style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-secondary)"></i>
                        <input type="text" x-model="form.email" id="email-input" class="form-input"
                            style="padding-left: 2.5rem" placeholder="email atau username" required
                            autocomplete="username">
                    </div>
                    <p class="text-red-500 text-xs mt-1" x-show="errors.email" x-text="errors.email" x-cloak></p>
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label
                        style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.375rem">
                        Password
                    </label>
                    <div style="position: relative">
                        <i data-lucide="lock"
                            style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-secondary)"></i>
                        <input :type="showPass ? 'text' : 'password'" x-model="form.password" id="password-input"
                            class="form-input" style="padding-left: 2.5rem; padding-right: 2.5rem"
                            placeholder="password" required autocomplete="current-password">
                        <button type="button" @click="showPass = !showPass"
                            style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary); background: none; border: none; cursor: pointer; padding: 0">
                            <i :data-lucide="showPass ? 'eye-off' : 'eye'" style="width: 16px; height: 16px"></i>
                        </button>
                    </div>
                    <p class="text-red-500 text-xs mt-1" x-show="errors.password" x-text="errors.password" x-cloak></p>
                </div>

                <!-- Error message -->
                <div x-show="errorMsg" class="mb-4 p-3 rounded-lg"
                    style="background: var(--color-danger-soft); border: 1px solid rgba(239,68,68,0.2)" x-cloak>
                    <p class="text-sm text-red-600" x-text="errorMsg"></p>
                </div>

                <!-- Submit -->
                <button type="submit" id="login-btn" class="btn-login" :disabled="loading">
                    <template x-if="!loading">
                        <span style="display: flex; align-items: center; gap: 0.5rem">
                            <i data-lucide="log-in" style="width: 16px; height: 16px"></i>
                            Masuk
                        </span>
                    </template>
                    <template x-if="loading">
                        <span>Memproses...</span>
                    </template>
                </button>
            </form>

            <!-- Demo credentials hint -->
            <div class="mt-6 p-3 rounded-lg text-center"
                style="background: var(--color-primary-soft); border: 1px solid rgba(37,99,235,0.18); cursor:pointer"
                @click="form.email='bos@example.com'; form.password='bos123'">
                <p class="text-xs" style="color: var(--color-primary-dark); font-weight: 600">Demo Credentials <span
                        style="font-weight:400">(klik untuk isi otomatis)</span></p>
                <p class="text-xs" style="color: var(--color-primary); margin-top: 0.25rem">bos@example.com / bos123</p>
                <p class="text-xs" style="color: var(--text-secondary); margin-top: 0.15rem">admin@example.com /
                    admin123 &nbsp;|&nbsp; checker@example.com / checker123</p>
            </div>

            <p class="text-center text-xs mt-4" style="color: var(--text-secondary)">&copy; <?= date('Y') ?> Peace
                Seafood</p>
        </div>
    </div>

    <script>
        function loginPage() {
            return {
                form: { email: '', password: '' },
                errors: {},
                errorMsg: '',
                loading: false,
                showPass: false,

                init() {
                    // Redirect if already logged in
                    const token = localStorage.getItem('token');
                    if (token) {
                        window.location.href = '/peace_seafood/dashboard';
                        return;
                    }
                    if (window.lucide) lucide.createIcons();
                },

                validate() {
                    this.errors = {};
                    if (!this.form.email.trim()) this.errors.email = 'Email/username wajib diisi';
                    if (!this.form.password) this.errors.password = 'Password wajib diisi';
                    return Object.keys(this.errors).length === 0;
                },

                async doLogin() {
                    if (!this.validate()) return;

                    this.loading = true;
                    this.errorMsg = '';

                    try {
                        const response = await axios.post(
                            '/peace_seafood/api/auth/login',
                            { email: this.form.email, password: this.form.password },
                            { headers: { 'Content-Type': 'application/json' } }
                        );

                        const data = response.data?.data;
                        if (data?.token) {
                            localStorage.setItem('token', data.token);
                            localStorage.setItem('user', JSON.stringify(data.user));

                            // Redirect langsung — tidak pakai setTimeout supaya tidak bisa diinterupsi
                            window.location.href = '/peace_seafood/dashboard';
                            return;
                        }

                        this.errorMsg = response.data?.message || 'Response tidak valid dari server';

                    } catch (e) {
                        // Fallback: jika token sudah tersimpan (login sukses tapi ada error lain),
                        // tetap redirect ke dashboard
                        if (localStorage.getItem('token')) {
                            window.location.href = '/peace_seafood/dashboard';
                            return;
                        }
                        const msg = e.response?.data?.message || 'Login gagal, periksa email & password';
                        this.errorMsg = msg;
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>
</body>

</html>