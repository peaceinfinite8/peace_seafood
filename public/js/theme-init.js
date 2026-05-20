/**
 * Theme Initialization Script
 * Runs BEFORE Alpine.js to prevent theme-related errors
 * Place this script in <head> with inline execution
 */

(function () {
    // Get saved theme or default to light
    const savedTheme = localStorage.getItem('theme') || 'light';

    // Apply theme IMMEDIATELY to prevent flash
    document.documentElement.setAttribute('data-theme', savedTheme);

    // Ensure CSS Custom Properties are available
    document.documentElement.style.setProperty('--current-theme', savedTheme);
})();
