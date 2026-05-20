/**
 * Peace Seafood - Chart Configuration
 * Chart.js configurations for dashboard and reports
 * FIXED: Properly destroy previous charts before reusing canvas
 */

const ChartConfig = (() => {
  const defaultColors = [
    '#2563eb', '#16a34a', '#d97706', '#dc2626',
    '#0891b2', '#7c3aed', '#db2777', '#ea580c',
  ];

  // Store chart instances to prevent canvas reuse errors
  const chartInstances = {
    'chart-penjualan': null,
    'chart-stok': null,
  };

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
   * Destroy chart if it exists to prevent canvas reuse errors
   */
  function destroyChart(canvasId) {
    if (chartInstances[canvasId]) {
      chartInstances[canvasId].destroy();
      chartInstances[canvasId] = null;
    }
  }

  /**
   * Initialize sales chart
   */
  function initSalesChart(canvasId, labels, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    // Destroy previous chart instance before creating new one
    destroyChart(canvasId);

    chartInstances[canvasId] = new Chart(ctx, {
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

    return chartInstances[canvasId];
  }

  /**
   * Initialize stock chart
   */
  function initStockChart(canvasId, labels, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    // Destroy previous chart instance before creating new one
    destroyChart(canvasId);

    chartInstances[canvasId] = new Chart(ctx, {
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

    return chartInstances[canvasId];
  }

  /**
   * Destroy all dashboard charts (cleanup)
   */
  function destroyAll() {
    Object.keys(chartInstances).forEach(key => {
      destroyChart(key);
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

  return {
    initSalesChart,
    initStockChart,
    initDashboardCharts,
    destroyAll,
    destroyChart,
  };
})();
