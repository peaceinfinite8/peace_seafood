/**
 * Peace Seafood - Chart Configuration
 * Chart.js configurations for dashboard and reports
 */

const ChartConfig = (() => {
  // SafeChart wrapper prevents Chart.js from being called with a null/invalid target
  function SafeChart(target, config) {
    try {
      let ctx = target;
      if (!ctx) {
        console.warn('SafeChart: target is null, skipping chart', config && config.type);
        return { destroy: () => {} };
      }

      if (typeof ctx === 'string') {
        ctx = document.getElementById(ctx);
        if (!ctx) {
          console.warn('SafeChart: canvas id not found', target);
          return { destroy: () => {} };
        }
      }

      // If a CanvasRenderingContext2D is passed, extract its canvas element for better Chart.js compatibility
      if (typeof CanvasRenderingContext2D !== 'undefined' && ctx instanceof CanvasRenderingContext2D) {
        ctx = ctx.canvas;
      }

      // Always pass the canvas element to new Chart() for stability
      if (ctx instanceof HTMLCanvasElement || (ctx && typeof ctx.getContext === 'function')) {
        // Safely destroy any existing chart bound to this canvas to prevent "Canvas is already in use" and resizing crashes
        try {
          const existing = Chart.getChart(ctx);
          if (existing) {
            existing.destroy();
          }
        } catch (e) {
          console.warn('SafeChart: error destroying existing chart', e);
        }

        return new Chart(ctx, config);
      }

      console.warn('SafeChart: invalid target type', target);
      return { destroy: () => {} };
    } catch (err) {
      console.warn('SafeChart error', err);
      return { destroy: () => {} };
    }
  }

  const defaultColors = [
    "#2563eb",
    "#16a34a",
    "#d97706",
    "#dc2626",
    "#0891b2",
    "#7c3aed",
    "#db2777",
    "#ea580c",
  ];

  /**
   * Default chart options
   */
  const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: "bottom" },
      tooltip: {
        callbacks: {
          label: (ctx) =>
            ` ${Utils.formatCurrency(ctx.parsed.y ?? ctx.parsed)}`,
        },
      },
    },
  };

  // Safely destroy an existing Chart.js instance attached to a canvas id
  function destroyChart(canvasId) {
    try {
      const existing = Chart.getChart(canvasId);
      if (existing) existing.destroy();
    } catch (e) {
      // swallow any Chart.js lookup errors
    }
  }

  /**
   * Initialize sales chart
   */
  function initSalesChart(canvasId, labels, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    destroyChart(canvasId);

    return SafeChart(ctx, {
      type: "line",
      data: {
        labels,
        datasets: [
          {
            label: "Penjualan",
            data,
            borderColor: defaultColors[0],
            backgroundColor: `${defaultColors[0]}20`,
            fill: true,
            tension: 0.4,
          },
        ],
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
    destroyChart(canvasId);

    return SafeChart(ctx, {
      type: "bar",
      data: {
        labels,
        datasets: [
          {
            label: "Stok (kg)",
            data,
            backgroundColor: defaultColors,
          },
        ],
      },
      options: defaultOptions,
    });
  }

  /**
   * Initialize dashboard charts
   */
  function initDashboardCharts(data) {
    const salesLabels = data?.sales_chart_labels || [];
    const salesData = data?.sales_chart || [];
    const stokLabels = data?.stok_chart?.labels || [];
    const stokValues = data?.stok_chart?.values || [];

    // Match IDs used in dashboard markup
    try {
      initSalesChart("salesTrendChart", salesLabels, salesData);
      initStockChart("stockCategoryChart", stokLabels, stokValues);
    } catch (e) {
      console.warn('initDashboardCharts warning', e);
    }
  }

  window.ChartConfig = {
    initSalesChart,
    initStockChart,
    destroyChart,
    initDashboardCharts,
  };

  // Expose SafeChart globally so other inline scripts can use it
  try { window.SafeChart = SafeChart; } catch (e) {}

  return { initSalesChart, initStockChart, destroyChart, initDashboardCharts };
})();
