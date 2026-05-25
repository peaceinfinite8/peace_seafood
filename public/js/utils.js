/**
 * Peace Seafood - Utility Functions
 */

const Utils = (() => {
  /**
   * Format number as Indonesian currency (IDR)
   */
  function formatCurrency(amount) {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(amount ?? 0);
  }

  /**
   * Format number with thousand separator
   */
  function formatNumber(num) {
    return new Intl.NumberFormat("id-ID").format(num ?? 0);
  }

  /**
   * Format date to Indonesian locale
   */
  function formatDate(dateStr, options = {}) {
    if (!dateStr) return "-";
    const defaults = { day: "2-digit", month: "short", year: "numeric" };
    return new Date(dateStr).toLocaleDateString("id-ID", {
      ...defaults,
      ...options,
    });
  }

  /**
   * Format datetime
   */
  function formatDateTime(dateStr) {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleString("id-ID");
  }

  /**
   * Format kilogram numbers consistently (default 2 decimals)
   * Returns a string like "4.50 kg"
   */
  function formatKg(value, decimals = 2) {
    const n = parseFloat(value) || 0;
    return (
      new Intl.NumberFormat("id-ID", {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
      }).format(n) + " kg"
    );
  }

  /**
   * Show toast notification
   */
  function showToast(message, type = "info") {
    if (window.iziToast && typeof window.iziToast.showToast === "function") {
      window.iziToast.showToast(type, {
        title: type.charAt(0).toUpperCase() + type.slice(1),
        message: message,
      });
      return;
    }
    const container =
      document.getElementById("toast-container") ||
      (() => {
        const el = document.createElement("div");
        el.id = "toast-container";
        el.className = "toast-container";
        document.body.appendChild(el);
        return el;
      })();

    const colorMap = {
      success: "bg-success",
      error: "bg-danger",
      warning: "bg-warning",
      info: "bg-info",
    };
    const toast = document.createElement("div");
    toast.className = `toast show text-white ${colorMap[type] || "bg-info"}`;
    toast.innerHTML = `
      <div class="toast-body d-flex justify-content-between align-items-center">
        <span>${message}</span>
        <button type="button" class="btn-close btn-close-white ms-2" onclick="this.closest('.toast').remove()"></button>
      </div>
    `;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
  }

  /**
   * Confirm dialog
   */
  async function confirm(message) {
    return await window.confirm(message);
  }

  /**
   * Debounce function
   */
  function debounce(fn, delay = 300) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), delay);
    };
  }

  /**
   * Toggle dark mode
   */
  function toggleDarkMode() {
    const current = document.documentElement.getAttribute("data-theme");
    const next = current === "dark" ? "light" : "dark";
    document.documentElement.setAttribute("data-theme", next);
    localStorage.setItem("theme", next);
  }

  /**
   * Apply saved theme
   */
  function applySavedTheme() {
    const saved = localStorage.getItem("theme") || "light";
    document.documentElement.setAttribute("data-theme", saved);
  }

  // expose formatKg globally for inline templates
  if (typeof window !== "undefined") window.formatKg = formatKg;

  return {
    formatCurrency,
    formatNumber,
    formatDate,
    formatDateTime,
    formatKg,
    showToast,
    confirm,
    debounce,
    toggleDarkMode,
    applySavedTheme,
  };
})();

document.addEventListener("DOMContentLoaded", () => Utils.applySavedTheme());
