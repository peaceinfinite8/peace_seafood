resources/ui/

Purpose: Scaffolded UI resources for Peace Seafood project.
Contains:

- css/theme.css : CSS variables and base styles for light/dark theme
- js/theme.js : Theme toggle and persistence (localStorage)
- components/dashboard.html : Example dashboard markup
- components/settings.html : Example settings markup (tenant vs platform)

Usage:

- Copy `public/css/ui-theme.css` and `public/js/ui-theme.js` into your layout head/footer.
- Or include `resources/ui/css/theme.css` directly during development.

Notes:

- Uses system font stack to avoid conflicting fonts.
- Theme defaults to user preference via `prefers-color-scheme`.
- Ensure Lucide icons script is available if using SVG icons.
