<?php include("header.php") ?>

      <!-- Content -->
      <main class="p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- Table + Sidebar widgets -->
        <section class="grid grid-cols-1 xl:grid-cols-1 gap-4">
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

<!-- <script>
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
</script> -->

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

  // Truncate message to 50 chars (without breaking HTML)
  function truncate(str, n = 50) {
    const s = String(str || "");
    if (s.length <= n) return s;
    return s.slice(0, n).trimEnd() + "...";
  }

  // Basic HTML escaper to prevent injection
  function escapeHtml(str) {
    return String(str)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#39;");
  }

  // Turn "../uploads/..." into absolute path like `${BASE_URL}/uploads/...`
  function resolveUploadPath(path) {
    if (!path) return null;
    const base = BASE_URL.replace(/\/+$/,""); // trim trailing slash
    if (path.startsWith("../")) return base + "/" + path.replace(/^\.{2}\//, "");
    if (path.startsWith("./"))  return base + "/" + path.replace(/^\.\//, "");
    return path; // already absolute
  }

  // Build attachment HTML (image preview if image; else link)
  function attachmentHtml(uploadPath) {
    if (!uploadPath) return "";
    const url = resolveUploadPath(uploadPath);
    const isImage = /\.(png|jpe?g|gif|webp|bmp|svg)$/i.test(url);
    const isPdf   = /\.pdf$/i.test(url);

    if (isImage) {
      return `
        <div style="margin-top:10px">
          <div style="font-weight:600;margin-bottom:4px">Attachment:</div>
          <img src="${escapeHtml(url)}" alt="attachment" style="max-width:100%;height:auto;border-radius:8px;"/>
          <div style="margin-top:6px"><a href="${escapeHtml(url)}" target="_blank" rel="noopener">Open image in new tab</a></div>
        </div>`;
    }
    if (isPdf) {
      return `
        <div style="margin-top:10px">
          <div style="font-weight:600;margin-bottom:4px">Attachment:</div>
          <a href="${escapeHtml(url)}" target="_blank" rel="noopener">Open PDF</a>
        </div>`;
    }
    return `
      <div style="margin-top:10px">
        <div style="font-weight:600;margin-bottom:4px">Attachment:</div>
        <a href="${escapeHtml(url)}" target="_blank" rel="noopener">Download file</a>
      </div>`;
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

      (data.data?.inquiries || []).forEach(q => {
        const color = q.subject === "contact" ? "bg-green-500" : "bg-yellow-500";
        const shortMsg = truncate(q.messege, 50);
        const dt = formatDate(q.date);

        const li = document.createElement("li");
        li.className = "flex gap-3";
        li.innerHTML = `
          <span class="w-2 h-2 mt-2 rounded-full ${color}"></span>
          <div class="flex-1">
            <div class="space-x-1">
              <span class="font-medium">${escapeHtml(q.name)} - #${q.id} - ${escapeHtml(q.subject)}</span>
              <span>${escapeHtml(shortMsg)}</span>
              <span class="text-gray-500">${escapeHtml(dt)}</span>
              <a href="#" class="text-blue-600 hover:underline ml-1" data-open-message="${q.id}">Open message</a>
            </div>
          </div>
        `;

        // Store full data for the click handler
        li.dataset.payload = JSON.stringify({
          id: q.id,
          name: q.name,
          subject: q.subject,
          messege: q.messege,
          mobile: q.mobile,
          email: q.email,
          date: q.date,
          upload_path: q.upload_path
        });

        queriesList.appendChild(li);
      });
    } catch (err) {
      console.error("Error fetching queries:", err);
      queriesList.innerHTML =
        '<li class="text-red-500">Failed to load queries</li>';
    }
  }

  // Delegated click for "Open message"
  queriesList.addEventListener("click", async (e) => {
    const link = e.target.closest("[data-open-message]");
    if (!link) return;
    e.preventDefault();

    const li = link.closest("li");
    if (!li || !li.dataset.payload) return;

    const q = JSON.parse(li.dataset.payload);

    const bodyHtml = `
      <div style="text-align:left;line-height:1.5">
        <div><strong>Name:</strong> ${escapeHtml(q.name || "")}</div>
        <div><strong>Subject:</strong> ${escapeHtml(q.subject || "")}</div>
        <div><strong>Inquiry ID:</strong> #${escapeHtml(q.id)}</div>
        <div><strong>Date:</strong> ${escapeHtml(formatDate(q.date || ""))}</div>
        <div><strong>Mobile:</strong> ${escapeHtml(q.mobile || "-")}</div>
        <div><strong>Email:</strong> ${escapeHtml(q.email || "-")}</div>
        <div style="margin-top:8px"><strong>Message:</strong><br>${escapeHtml(q.messege || "")}</div>
        ${attachmentHtml(q.upload_path)}
      </div>
    `;

    await Swal.fire({
      icon: 'info',
      title: `Message from ${escapeHtml(q.name || "User")}`,
      html: bodyHtml,
      width: 700,
      confirmButtonText: 'Close'
    });
  });

  // Initial load
  fetchQueries();
</script>

<?php include("footer.php"); ?>
