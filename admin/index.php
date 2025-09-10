<?php include("header.php") ?>

<!-- Content -->
<main class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Charts -->
  <section class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl shadow p-4 lg:col-span-2">
      <!-- Products -->
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500">Products</p>
        </div>
        <p id="statProducts" class="mt-2 text-3xl font-semibold">--</p>
        <p class="text-xs text-gray-500 mt-1">total products</p>
      </div>

      <!-- Categories -->
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500">Categories</p>
        </div>
        <p id="statCategories" class="mt-2 text-3xl font-semibold">--</p>
        <p class="text-xs text-gray-500 mt-1">total categories</p>
      </div>

      <!-- Brands -->
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500">Brands</p>
        </div>
        <p id="statBrands" class="mt-2 text-3xl font-semibold">--</p>
        <p class="text-xs text-gray-500 mt-1">total brands</p>
      </div>

      <!-- Inquiries -->
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500">Inquiries</p>
        </div>
        <p id="statInquiries" class="mt-2 text-3xl font-semibold">--</p>
        <p class="text-xs text-gray-500 mt-1">total inquiries</p>
      </div>
    </div>

    <!-- Counts Overview -->
    <div class="bg-white rounded-xl shadow p-4">
      <h3 class="font-semibold">Counts Overview</h3>
      <div class="mt-3">
        <canvas id="statusChart" height="110"></canvas>
      </div>
      <div id="statusLegend" class="mt-4 grid grid-cols-2 gap-2 text-sm"></div>
    </div>
  </section>

</main>
</div>
</div>

<!-- Chart.js (guard-load if not already present) -->
<script>
  (function ensureChart() {
    if (typeof Chart === "undefined") {
      const s = document.createElement("script");
      s.src = "https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js";
      document.head.appendChild(s);
    }
  })();
</script>

<script>
  const SGBASE_URL = "<?php echo BASE_URL; ?>"; // Set if not templated

  // Helpers
  const fmt = n => (n ?? 0).toLocaleString("en-IN");
  function getPalette(n) {
    const base = ['#22c55e','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#10b981','#64748b','#e11d48','#14b8a6'];
    if (n <= base.length) return base.slice(0, n);
    const out = [...base];
    while (out.length < n) out.push(base[out.length % base.length]);
    return out;
  }

  let statusChartInstance = null;

  function renderCountsChart(counts) {
    const ctx = document.getElementById('statusChart');
    if (!ctx || typeof Chart === "undefined") return;

    const labels = ['Users','Brands','Categories','Products','Uploads','Inquiries','Sheets'];
    const values = [
      counts.users, counts.brands, counts.categories, counts.products,
      counts.uploads, counts.inquiries, counts.sheets
    ];
    const colors = getPalette(values.length);

    if (statusChartInstance) statusChartInstance.destroy();
    statusChartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{ data: values, backgroundColor: colors }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } }
      }
    });

    // Build legend
    const legendEl = document.getElementById('statusLegend');
    if (legendEl) {
      legendEl.innerHTML = labels.map((label, i) => `
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-sm" style="background:${colors[i]}"></span>
          ${label} <span class="text-gray-500">(${fmt(values[i])})</span>
        </div>
      `).join('');
    }
  }

  async function loadDashboardCounts() {
    try {
      const res = await fetch(`${SGBASE_URL.replace(/\/+$/, '')}/helper/dashboard_counts.php`, {
        method: 'POST'
      });
      const json = await res.json();
      if (!res.ok || json.success !== true) {
        throw new Error(json.message || 'Failed to load counts');
      }
      const c = json.data || {};

      // Update stat cards
      const byId = id => document.getElementById(id);
      const setText = (id, val) => { const el = byId(id); if (el) el.textContent = fmt(val); };
      setText('statProducts', c.products);
      setText('statCategories', c.categories);
      setText('statBrands', c.brands);
      setText('statInquiries', c.inquiries);

      // Chart
      const whenChartReady = () => new Promise(r => {
        if (typeof Chart !== "undefined") return r();
        const iv = setInterval(() => { if (typeof Chart !== "undefined") { clearInterval(iv); r(); } }, 50);
      });
      await whenChartReady();
      renderCountsChart(c);

    } catch (e) {
      console.error('Dashboard counts error:', e);
    }
  }

  // Init
  window.addEventListener('load', loadDashboardCounts);
</script>

<?php include("footer.php"); ?>
