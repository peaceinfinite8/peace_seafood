/* extracted from errors_404.script.1.js */
// extracted from src/views/errors/404.php
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
