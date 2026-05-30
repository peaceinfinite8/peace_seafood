/**
 * Peace Seafood - Dashboard Module
 * Handles dashboard data loading and rendering
 */

const Dashboard = (() => {
  /**
   * Load dashboard summary stats
   */
  async function loadStats() {
    try {
      const data = await ApiClient.get('/dashboard/stats');
      renderStats(data);
    } catch (err) {
      Utils.showToast('Gagal memuat statistik dashboard', 'error');
    }
  }

  /**
   * Render stat cards
   */
  function renderStats(data) {
    const fields = ['total_stok', 'total_penjualan', 'total_piutang', 'total_hutang'];
    fields.forEach(field => {
      const el = document.getElementById(`stat-${field}`);
      if (el && data[field] !== undefined) {
        el.textContent = Utils.formatCurrency(data[field]);
      }
    });
  }

  /**
   * Load recent transactions
   */
  async function loadRecentTransactions() {
    try {
      const data = await ApiClient.get('/dashboard/recent-transactions');
      renderRecentTransactions(data.data);
    } catch (err) {
      console.error('Failed to load recent transactions', err);
    }
  }

  /**
   * Render recent transactions table
   */
  function renderRecentTransactions(transactions) {
    const tbody = document.getElementById('recent-transactions-body');
    if (!tbody) return;

    tbody.innerHTML = transactions.map(t => `
      <tr>
        <td>${t.nomor_nota}</td>
        <td>${t.pembeli_nama}</td>
        <td>${Utils.formatCurrency(t.total)}</td>
        <td><span class="badge badge-status bg-${t.status_color}">${t.status_label}</span></td>
        <td>${Utils.formatDate(t.created_at)}</td>
      </tr>
    `).join('');
  }

  /**
   * Initialize dashboard
   */
  function init() {
    loadStats();
    loadRecentTransactions();
    if (window.ChartConfig && typeof ChartConfig.initDashboardCharts === 'function' && window.dashboardStats && Object.keys(window.dashboardStats).length > 0) {
      try {
        ChartConfig.initDashboardCharts(window.dashboardStats);
      } catch (e) {
        console.warn('ChartConfig.initDashboardCharts failed', e);
      }
    }
  }

  return { init, loadStats, loadRecentTransactions };
})();

document.addEventListener('DOMContentLoaded', () => Dashboard.init());
