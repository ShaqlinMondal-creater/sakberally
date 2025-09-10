<?php include("header.php") ?>

      <!-- Content -->
      <main class="p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- Table + Sidebar widgets -->
        <section class="grid grid-cols-1 xl:grid-cols-3 gap-4">
          <!-- Activity -->
            <div class="bg-white rounded-xl shadow p-4">
              <h3 class="font-semibold mb-3">Queries</h3>
              <ol class="space-y-3 text-sm">
                <li class="flex gap-3">
                  <span class="w-2 h-2 mt-2 rounded-full bg-green-500"></span>
                  <div><span class="font-medium">User Name - #quiry id - subject </span> messege <span class="text-gray-500">· 2h ago</span></div>
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
  </script>
</body>
</html>
