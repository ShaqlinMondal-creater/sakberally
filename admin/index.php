<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: { DEFAULT: '#e21e26', 600: '#cf1b22', 700: '#b5171d' }
          }
        }
      }
    }
  </script>

  <!-- Chart.js (for the example charts) -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <style>
    /* smooth sidebar slide on mobile */
    .sidebar {
      transition: transform .25s ease;
    }
  </style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">

  <!-- Layout -->
  <div class="min-h-screen flex" x-data="dashboard()" x-init="init()">

    <!-- Mobile overlay -->
    <div id="overlay"
         class="fixed inset-0 bg-black/40 z-30 hidden"
         onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar"
           class="sidebar fixed z-40 inset-y-0 left-0 w-72 bg-white shadow-lg lg:static lg:translate-x-0 -translate-x-full">
      <div class="h-full flex flex-col">
        <!-- Brand -->
        <div class="flex items-center gap-2 h-16 px-4 border-b">
          <div class="w-9 h-9 grid place-items-center rounded-full bg-brand text-white font-bold">SA</div>
          <div class="leading-tight">
            <p class="font-semibold">S Akberally</p>
            <p class="text-xs text-gray-500">Admin Panel</p>
          </div>
          <button class="lg:hidden ml-auto p-2 rounded hover:bg-gray-100"
                  onclick="closeSidebar()" aria-label="Close sidebar">
            <!-- x icon -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Nav -->
        <nav class="p-3 space-y-1 overflow-y-auto">
          <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-gray-100 text-gray-900">
            <svg class="w-5 h-5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
            </svg>
            Dashboard
          </a>
          <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 7h18M3 12h18M3 17h18" />
            </svg>
            Orders
          </a>
          <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V7a2 2 0 00-2-2h-5l-2-2H6a2 2 0 00-2 2v6m16 0l-2 8H6l-2-8m16 0H4" />
            </svg>
            Products
          </a>
          <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            Categories
          </a>
          <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Customers
          </a>
          <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Reports
          </a>
          <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-2a6 6 0 10-12 0v2a2 2 0 002 2z" />
            </svg>
            Settings
          </a>
        </nav>

        <!-- Footer -->
        <div class="mt-auto p-3 border-t text-xs text-gray-500">
          © <span id="year"></span> S Akberally
        </div>
      </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 min-w-0 lg:ml-0">

      <!-- Topbar -->
      <header class="sticky top-0 z-20 bg-white border-b">
        <div class="h-16 px-4 sm:px-6 lg:px-8 flex items-center gap-3">
          <button class="lg:hidden p-2 rounded hover:bg-gray-100" onclick="openSidebar()" aria-label="Open sidebar">
            <!-- burger -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <h1 class="font-semibold text-lg hidden sm:block">Dashboard</h1>

          <!-- Search -->
          <div class="ml-auto relative max-w-md w-full">
            <input type="text" placeholder="Search..."
                   class="w-full pl-10 pr-3 py-2.5 bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/40" />
            <svg class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <!-- Actions -->
          <button class="p-2 rounded hover:bg-gray-100" aria-label="Notifications">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
          </button>
          <div class="w-9 h-9 rounded-full bg-brand text-white grid place-items-center font-semibold">BK</div>
        </div>
      </header>

      <!-- Content -->
      <main class="p-4 sm:p-6 lg:p-8 space-y-6">

        <!-- Stat cards -->
        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
          <div class="bg-white rounded-xl shadow p-4">
            <div class="flex items-center justify-between">
              <p class="text-sm text-gray-500">Total Orders</p>
              <span class="text-green-600 text-xs bg-green-50 px-2 py-0.5 rounded-full">+8%</span>
            </div>
            <p class="mt-2 text-3xl font-semibold">1,284</p>
            <p class="text-xs text-gray-500 mt-1">vs last 30 days</p>
          </div>

          <div class="bg-white rounded-xl shadow p-4">
            <div class="flex items-center justify-between">
              <p class="text-sm text-gray-500">Revenue</p>
              <span class="text-green-600 text-xs bg-green-50 px-2 py-0.5 rounded-full">+12%</span>
            </div>
            <p class="mt-2 text-3xl font-semibold">₹ 18.6L</p>
            <p class="text-xs text-gray-500 mt-1">incl. taxes</p>
          </div>

          <div class="bg-white rounded-xl shadow p-4">
            <div class="flex items-center justify-between">
              <p class="text-sm text-gray-500">New Customers</p>
              <span class="text-red-600 text-xs bg-red-50 px-2 py-0.5 rounded-full">-2%</span>
            </div>
            <p class="mt-2 text-3xl font-semibold">246</p>
            <p class="text-xs text-gray-500 mt-1">this month</p>
          </div>

          <div class="bg-white rounded-xl shadow p-4">
            <div class="flex items-center justify-between">
              <p class="text-sm text-gray-500">Pending Orders</p>
              <span class="text-yellow-700 text-xs bg-yellow-50 px-2 py-0.5 rounded-full">42</span>
            </div>
            <p class="mt-2 text-3xl font-semibold">138</p>
            <p class="text-xs text-gray-500 mt-1">awaiting fulfillment</p>
          </div>
        </section>

        <!-- Charts -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <div class="bg-white rounded-xl shadow p-4 lg:col-span-2">
            <div class="flex items-center justify-between">
              <h3 class="font-semibold">Sales Overview</h3>
              <select class="text-sm bg-gray-100 rounded px-2 py-1">
                <option>Last 6 months</option>
                <option>Last 12 months</option>
                <option>This year</option>
              </select>
            </div>
            <div class="mt-3">
              <canvas id="salesChart" height="110"></canvas>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow p-4">
            <h3 class="font-semibold">Orders by Status</h3>
            <div class="mt-3">
              <canvas id="statusChart" height="110"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-sm bg-green-500"></span> Delivered
              </div>
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-sm bg-yellow-500"></span> Processing
              </div>
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-sm bg-red-500"></span> Canceled
              </div>
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-sm bg-blue-500"></span> New
              </div>
            </div>
          </div>
        </section>

        <!-- Table + Sidebar widgets -->
        <section class="grid grid-cols-1 xl:grid-cols-3 gap-4">
          <!-- Recent Orders -->
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
                  <!-- row -->
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

          <!-- Right widgets -->
          <div class="space-y-4">
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow p-4">
              <h3 class="font-semibold mb-3">Quick Actions</h3>
              <div class="grid grid-cols-2 gap-3">
                <a href="#" class="p-3 rounded-lg bg-brand text-white text-center font-medium hover:bg-brand-700">Add Product</a>
                <a href="#" class="p-3 rounded-lg bg-gray-100 text-center hover:bg-gray-200">Create Order</a>
                <a href="#" class="p-3 rounded-lg bg-gray-100 text-center hover:bg-gray-200">New Customer</a>
                <a href="#" class="p-3 rounded-lg bg-gray-100 text-center hover:bg-gray-200">Generate Report</a>
              </div>
            </div>

            <!-- Activity -->
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

  <script>
    // Sidebar controls
    function openSidebar() {
      document.getElementById('sidebar').style.transform = 'translateX(0)';
      document.getElementById('overlay').classList.remove('hidden');
    }
    function closeSidebar() {
      document.getElementById('sidebar').style.transform = 'translateX(-100%)';
      document.getElementById('overlay').classList.add('hidden');
    }
    // Init year
    document.getElementById('year').textContent = new Date().getFullYear();

    // Charts
    function initCharts() {
      const salesCtx = document.getElementById('salesChart');
      if (salesCtx) {
        new Chart(salesCtx, {
          type: 'line',
          data: {
            labels: ['Mar','Apr','May','Jun','Jul','Aug'],
            datasets: [{
              label: 'Revenue',
              data: [12,15,11,18,20,23],
              borderWidth: 2,
              tension: .35
            },{
              label: 'Orders',
              data: [220,260,240,280,300,320],
              borderWidth: 2,
              tension: .35
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true } },
            scales: {
              y: { grid: { color: 'rgba(0,0,0,0.05)' } },
              x: { grid: { display: false } }
            }
          }
        });
      }

      const statusCtx = document.getElementById('statusChart');
      if (statusCtx) {
        new Chart(statusCtx, {
          type: 'doughnut',
          data: {
            labels: ['Delivered','Processing','Canceled','New'],
            datasets: [{ data: [58, 24, 6, 12] }]
          },
          options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
      }
    }
    // Run when Chart is loaded
    window.addEventListener('load', initCharts);
  </script>
</body>
</html>
