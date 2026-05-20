/**
 * Peace Seafood - Chart Configuration
 * Chart.js configurations for dashboard and reports
 */

const ChartConfig = (() => {
  const defaultColors = [
    '#2563eb', '#16a34a', '#d97706', '#dc2626',
    '#0891b2', '#7c3aed', '#db2777', '#ea580c',
  ];

  /**
   * Default chart options
   */
  const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom' },
      tooltip: {
        callbacks: {
          label: (ctx) => ` ${Utils.formatCurrency(ctx.parsed.y ?? ctx.parsed)}`,
        },
      },
    },
  };

  /**
   * Initialize sales chart
   */
  function initSalesChart(canvasId, labels, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    return new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Penjualan',
          data,
          borderColor: defaultColors[0],
          backgroundColor: `${defaultColors[0]}20`,
          fill: true,
          tension: 0.4,
        }],
      },
      options: defaultOptions,
    });
  }

  /**
   * Initialize stock chart
   */
  function initStockChart(canvasId, labels, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    return new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Stok (kg)',
          data,
          backgroundColor: defaultColors,
        }],
      },
      options: defaultOptions,
    });
  }

  /**
   * Initialize dashboard charts
   */
  async function initDashboardCharts() {
    try {
      const data = await ApiClient.get('/dashboard/chart-data');
      initSalesChart('chart-penjualan', data.labels, data.penjualan);
      initStockChart('chart-stok', data.stok_labels, data.stok_data);
    } catch (err) {
      console.error('Failed to load chart data', err);
    }
  }

  return { initSalesChart, initStockChart, initDashboardCharts };
})();
