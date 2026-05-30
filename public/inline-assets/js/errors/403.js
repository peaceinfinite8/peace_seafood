/* extracted from errors_403.script.1.js */
// extracted from src/views/errors/403.php
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
