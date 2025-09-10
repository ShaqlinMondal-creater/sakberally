<?php include("header.php") ?>

      <!-- Content -->
      <main class="p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- Table + Sidebar widgets -->
        <section class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <!-- Activity -->
            <div class="bg-white rounded-xl shadow p-4">
                <h3 class="font-semibold mb-3">Queries</h3>
                <ol id="queriesList" class="space-y-3 text-sm">
                <!-- Queries will be injected here -->
                </ol>
            </div>
        </section>

      </main>
    </div>
  </div>

<script>
  const queriesList = document.getElementById("queriesList");

  // Format date: "19th February 2025, 12.05AM"
  function formatDate(dateString) {
    const date = new Date(dateString);

    const day = date.getDate();
    const daySuffix = (d => {
      if (d > 3 && d < 21) return "th";
      switch (d % 10) {
        case 1: return "st";
        case 2: return "nd";
        case 3: return "rd";
        default: return "th";
      }
    })(day);

    const month = date.toLocaleString("en-US", { month: "long" });
    const year = date.getFullYear();

    let hours = date.getHours();
    const minutes = date.getMinutes().toString().padStart(2, "0");
    const ampm = hours >= 12 ? "PM" : "AM";
    hours = hours % 12 || 12;

    return `${day}${daySuffix} ${month} ${year}, ${hours}.${minutes}${ampm}`;
  }

  async function fetchQueries() {
    try {
      const res = await fetch(`<?php echo BASE_URL; ?>/query/fetch.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          limit: 50,
          offset: 0
        })
      });

      const data = await res.json();
      if (!res.ok || data.success !== true) {
        throw new Error(data.message || "Failed to fetch queries");
      }

      queriesList.innerHTML = "";

      data.data.inquiries.forEach(q => {
        const color =
          q.subject === "contact"
            ? "bg-green-500"
            : "bg-yellow-500";

        const li = document.createElement("li");
        li.className = "flex gap-3";
        li.innerHTML = `
          <span class="w-2 h-2 mt-2 rounded-full ${color}"></span>
          <div>
            <span class="font-medium">${escapeHtml(q.name)} - #${q.id} - ${escapeHtml(q.subject)}</span>
            ${escapeHtml(q.messege || "")}
            <span class="text-gray-500">${formatDate(q.date)}</span>
          </div>
        `;
        queriesList.appendChild(li);
      });
    } catch (err) {
      console.error("Error fetching queries:", err);
      queriesList.innerHTML =
        '<li class="text-red-500">Failed to load queries</li>';
    }
  }

  // Escape helper
  function escapeHtml(str) {
    return String(str)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#39;");
  }

  // Initial load
  fetchQueries();
</script>

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
