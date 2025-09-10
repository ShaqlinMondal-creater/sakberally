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

  <!-- Table + Sidebar widgets (unchanged) -->
  <section class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    <!-- Recent Orders (your static table kept as-is) -->
    <div class="xl:col-span-2 bg-white rounded-xl shadow">
      <div class="p-4 border-b flex items-center justify-between">
        <h3 class="font-semibold">Recent Orders</h3>
        <a href="#" class="text-sm text-brand hover:underline">View all</a>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr class="text-left text-gray-600">
              <th class="px-4 py-3 font-medium">Order #</th>
              <th class="px-4 py-3 font-medium">Customer</th>
              <th class="px-4 py-3 font-medium">Date</th>
              <th class="px-4 py-3 font-medium">Total</th>
              <th class="px-4 py-3 font-medium">Status</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody>
            <!-- sample rows -->
            <tr class="border-t">
              <td class="px-4 py-3 font-medium">#10241</td>
              <td class="px-4 py-3">Rahul Sharma</td>
              <td class="px-4 py-3">01 Sep 2025</td>
              <td class="px-4 py-3">₹ 18,240</td>
              <td class="px-4 py-3">
                <span class="text-xs px-2 py-1 rounded-full bg-green-50 text-green-700">Delivered</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="px-2 py-1 text-sm rounded hover:bg-gray-100">Details</button>
              </td>
            </tr>
            <tr class="border-t">
              <td class="px-4 py-3 font-medium">#10240</td>
              <td class="px-4 py-3">Fatema Kanchwala</td>
              <td class="px-4 py-3">31 Aug 2025</td>
              <td class="px-4 py-3">₹ 9,870</td>
              <td class="px-4 py-3">
                <span class="text-xs px-2 py-1 rounded-full bg-yellow-50 text-yellow-700">Processing</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="px-2 py-1 text-sm rounded hover:bg-gray-100">Details</button>
              </td>
            </tr>
            <tr class="border-t">
              <td class="px-4 py-3 font-medium">#10239</td>
              <td class="px-4 py-3">Mohammad Ali</td>
              <td class="px-4 py-3">31 Aug 2025</td>
              <td class="px-4 py-3">₹ 12,120</td>
              <td class="px-4 py-3">
                <span class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-700">New</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="px-2 py-1 text-sm rounded hover:bg-gray-100">Details</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Right widgets (unchanged) -->
    <div class="space-y-4">
      <div class="bg-white rounded-xl shadow p-4">
        <h3 class="font-semibold mb-3">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-3">
          <a href="#" class="p-3 rounded-lg bg-brand text-white text-center font-medium hover:bg-brand-700">Add Product</a>
          <a href="#" class="p-3 rounded-lg bg-gray-100 text-center hover:bg-gray-200">Create Order</a>
          <a href="#" class="p-3 rounded-lg bg-gray-100 text-center hover:bg-gray-200">New Customer</a>
          <a href="#" class="p-3 rounded-lg bg-gray-100 text-center hover:bg-gray-200">Generate Report</a>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow p-4">
        <h3 class="font-semibold mb-3">Activity</h3>
        <ol class="space-y-3 text-sm">
          <li class="flex gap-3">
            <span class="w-2 h-2 mt-2 rounded-full bg-green-500"></span>
            <div><span class="font-medium">Order #10241</span> delivered to Rahul Sharma <span class="text-gray-500">· 2h ago</span></div>
          </li>
          <li class="flex gap-3">
            <span class="w-2 h-2 mt-2 rounded-full bg-yellow-500"></span>
            <div><span class="font-medium">Stock alert:</span> Lathe Machine VL-200 low stock <span class="text-gray-500">· 6h ago</span></div>
          </li>
          <li class="flex gap-3">
            <span class="w-2 h-2 mt-2 rounded-full bg-blue-500"></span>
            <div><span class="font-medium">New user:</span> zohra@client.com <span class="text-gray-500">· 1d ago</span></div>
          </li>
        </ol>
      </div>
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
