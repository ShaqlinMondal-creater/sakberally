<?php include("header.php") ?>

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
<?php include("footer.php"); ?>
