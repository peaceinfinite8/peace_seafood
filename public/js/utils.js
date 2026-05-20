/**
 * Peace Seafood - Utility Functions
 */

const Utils = (() => {
  /**
   * Format number as Indonesian currency (IDR)
   */
  function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(amount ?? 0);
  }

  /**
   * Format number with thousand separator
   */
  function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num ?? 0);
  }

  /**
   * Format date to Indonesian locale
   */
  function formatDate(dateStr, options = {}) {
    if (!dateStr) return '-';
    const defaults = { day: '2-digit', month: 'short', year: 'numeric' };
    return new Date(dateStr).toLocaleDateString('id-ID', { ...defaults, ...options });
  }

  /**
   * Format datetime
   */
  function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleString('id-ID');
  }

  /**
   * Show toast notification
   */
  function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container')
      || (() => {
        const el = document.createElement('div');
        el.id = 'toast-container';
        el.className = 'toast-container';
        document.body.appendChild(el);
        return el;
      })();

    const colorMap = { success: 'bg-success', error: 'bg-danger', warning: 'bg-warning', info: 'bg-info' };
    const toast = document.createElement('div');
    toast.className = `toast show text-white ${colorMap[type] || 'bg-info'}`;
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
  function confirm(message) {
    return window.confirm(message);
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
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
  }

  /**
   * Apply saved theme
   */
  function applySavedTheme() {
    const saved = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
  }

  /**
   * Show nota detail in a simple modal. Falls back to penjualan page if fetch fails.
   */
  async function showNotaDetail(id) {
    if (!id) return;
    // prefer existing penjualan page instance
    if (window.penjualanPageInstance && typeof window.penjualanPageInstance.showDetail === 'function') {
      return window.penjualanPageInstance.showDetail(id);
    }
    try {
      const token = localStorage.getItem('token');
      const res = await axios.get('/peace_seafood/api/penjualan/' + id, { headers: { Authorization: 'Bearer ' + token } });
      const detail = res.data?.data;
      if (!detail) throw new Error('No data');

      // build modal
      const overlay = document.createElement('div');
      overlay.className = 'modal-overlay';
      overlay.style = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999';
      const box = document.createElement('div');
      box.className = 'modal-box';
      box.style = 'background:var(--bg-light);color:var(--text-primary);max-width:800px;padding:1rem;border-radius:0.5rem;max-height:80vh;overflow:auto';
      box.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem">
          <h3 style="margin:0">Nota: ${detail.no_nota || ''}</h3>
          <button aria-label="close" style="background:transparent;border:0;font-size:1.2rem;cursor:pointer">×</button>
        </div>
        <div style="display:flex;gap:1rem;margin-bottom:0.75rem">
          <div style="flex:1">
            <div><small style="color:var(--text-secondary)">Pembeli</small><div>${detail.nama_pembeli || 'Umum'}</div></div>
            <div><small style="color:var(--text-secondary)">Tanggal</small><div>${new Date(detail.tanggal_nota || '').toLocaleDateString('id-ID') || '-'}</div></div>
          </div>
          <div style="text-align:right">
            <div><small style="color:var(--text-secondary)">Total</small><div style="color:var(--color-primary)">${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(detail.total || 0)}</div></div>
          </div>
        </div>
        <div style="overflow:auto">
          <table style="width:100%;border-collapse:collapse">
            <thead><tr><th style="text-align:left;padding:6px;border-bottom:1px solid var(--border-color)">Produk</th><th style="text-align:right;padding:6px;border-bottom:1px solid var(--border-color)">Qty</th><th style="text-align:right;padding:6px;border-bottom:1px solid var(--border-color)">Harga</th></tr></thead>
            <tbody>
              ${(detail.items || []).map(it => `<tr><td style="padding:6px;border-bottom:1px solid var(--border-color)">${it.nama_produk}</td><td style="padding:6px;border-bottom:1px solid var(--border-color);text-align:right">${parseFloat(it.qty)} ${it.satuan || 'kg'}</td><td style="padding:6px;border-bottom:1px solid var(--border-color);text-align:right">${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(it.harga_jual || 0)}</td></tr>`).join('')}
            </tbody>
          </table>
        </div>
      `;

      overlay.appendChild(box);
      document.body.appendChild(overlay);

      // close handlers
      overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.remove(); });
      box.querySelector('button[aria-label="close"]').addEventListener('click', () => overlay.remove());
      if (window.lucide) window.lucide.createIcons();
    } catch (e) {
      // fallback: navigate to penjualan list
      window.location.href = '/peace_seafood/penjualan';
    }
  }

  return { formatCurrency, formatNumber, formatDate, formatDateTime, showToast, confirm, debounce, toggleDarkMode, applySavedTheme, showNotaDetail };
})();

document.addEventListener('DOMContentLoaded', () => Utils.applySavedTheme());

// expose globally
window.showNotaDetail = Utils.showNotaDetail;
