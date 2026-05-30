/* extracted from pages_login.script.1.js */
// extracted from src/views/pages/login.php
function loginPage() {
            return {
                mode: 'login', // login | signup | forgot | reset
                form: {
                    email: '',
                    password: '',
                    password_confirm: '',
                    resetToken: ''
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

                // Password strength checkers
                get passwordChecks() {
                    const pass = this.form.password || '';
                    return {
                        length: pass.length >= 8,
                        upper: /[A-Z]/.test(pass),
                        lower: /[a-z]/.test(pass),
                        number: /[0-9]/.test(pass),
                        special: /[^a-zA-Z0-9]/.test(pass)
                    };
                },

                get isPasswordStrong() {
                    const checks = this.passwordChecks;
                    return checks.length && checks.upper && checks.lower && checks.number && checks.special;
                },

                init() {
                    const token = localStorage.getItem('token');
                    if (token) {
                        window.location.href = '/peace_seafood/dashboard';
                        return;
                    }

                    // Check URL query parameters for reset token
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('token')) {
                        this.mode = 'reset';
                        this.form.resetToken = urlParams.get('token');
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

                async doSignup() {
                    if (!this.form.email.trim()) {
                        this.errors.email = 'Email wajib diisi';
                        return;
                    }
                    this.loading = true;
                    this.errors = {};
                    try {
                        const response = await axios.post('/peace_seafood/api/auth/signup', {
                            email: this.form.email
                        });
                        this.showSweetAlert('Sukses', response.data?.message || 'Registrasi berhasil, password telah dikirim!', 'success');
                        this.mode = 'login';
                    } catch (e) {
                        const msg = e.response?.data?.message || 'Pendaftaran gagal. Pastikan email Anda sudah disetujui Developer.';
                        this.showSweetAlert('Gagal Pendaftaran', msg, 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async doForgotPassword() {
                    if (!this.form.email.trim()) {
                        this.errors.email = 'Email wajib diisi';
                        return;
                    }
                    this.loading = true;
                    this.errors = {};
                    try {
                        const response = await axios.post('/peace_seafood/api/auth/forgot-password', {
                            email: this.form.email
                        });
                        this.showSweetAlert('Sukses', response.data?.message || 'Instruksi reset sandi telah dikirim ke email Anda.', 'success');
                        this.mode = 'login';
                    } catch (e) {
                        const msg = e.response?.data?.message || 'Gagal mengirim email reset password.';
                        this.showSweetAlert('Gagal Kirim', msg, 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async doResetPassword() {
                    if (!this.isPasswordStrong) {
                        this.showSweetAlert('Gagal', 'Password Anda belum memenuhi kriteria keamanan!', 'error');
                        return;
                    }
                    if (this.form.password !== this.form.password_confirm) {
                        this.showSweetAlert('Gagal', 'Konfirmasi password tidak cocok!', 'error');
                        return;
                    }
                    this.loading = true;
                    try {
                        const response = await axios.post('/peace_seafood/api/auth/reset-password', {
                            token: this.form.resetToken,
                            password: this.form.password
                        });
                        this.showSweetAlert('Sukses', response.data?.message || 'Password berhasil diperbarui!', 'success');
                        this.mode = 'login';
                        window.history.replaceState({}, document.title, window.location.pathname);
                    } catch (e) {
                        const msg = e.response?.data?.message || 'Token tidak valid atau sudah kedaluwarsa.';
                        this.showSweetAlert('Gagal Reset', msg, 'error');
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
