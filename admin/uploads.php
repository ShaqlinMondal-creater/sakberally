<?php include("header.php") ?>

      <!-- Content -->
        <!-- Filters (optional) -->
        <div class="p-4 sm:p-6 lg:p-8">
          <form id="filterForm" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
              <select id="purpose" class="border rounded-lg p-2">
              <option value="">All purposes</option>
              <option value="brands">brands</option>
              <option value="category">category</option>
              <option value="products">products</option>
              <option value="others">others</option>
              <option value="all">all</option>
              </select>
              <select id="extension" class="border rounded-lg p-2">
              <option value="">All types</option>
              <option value="pdf">pdf</option>
              <option value="jpg">jpg</option>
              <option value="jpeg">jpeg</option>
              <option value="png">png</option>
              <option value="webp">webp</option>
              </select>
              <input id="nameSearch" class="border rounded-lg p-2" placeholder="Search by file name…" />
              <button class="bg-gray-900 text-white rounded-lg px-4 py-2">Apply</button>
          </form>
        </div>

        <!-- Content -->
        <main class="p-4 sm:p-6 lg:p-8 space-y-6">

        <!-- Stat cards -->
        <section id="uploadsGrid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4"></section>

        <!-- Infinite scroll sentinel -->
        <div id="sentinel" class="h-12"></div>
        </main>
    </div>
  </div>



<script>
  const ENDPOINT = `<?php echo BASE_URL; ?>/helper/fetch_upload.php`;

  // State
  let limit = 200;           // default per your API
  let offset = 0;            // pagination cursor
  let loading = false;
  let done = false;          // when server says no more
  let currentFilters = {};   // purpose, extension, name

  const grid = document.getElementById('uploadsGrid');
  const sentinel = document.getElementById('sentinel');

  // ---- Utils ----
  const normalizePath = (path) => {
    // API returns "../uploads/…", resolve to absolute using BASE_URL
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    // remove any leading ../
    const cleaned = path.replace(/^(\.\.\/)+/, '');
    return `<?php echo BASE_URL; ?>/${cleaned}`;
  };

  const isImageExt = (ext) => ['jpg','jpeg','png','webp','gif'].includes((ext||'').toLowerCase());
  const isPdfExt = (ext) => (ext||'').toLowerCase() === 'pdf';

  // ---- Lazy loader (for images & pdf thumbnails) ----
  const mediaObserver = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el = entry.target;
      const src = el.getAttribute('data-src');
      if (src) {
        el.src = src;   // for <img>
        el.removeAttribute('data-src');
      }
      // For <iframe> (PDF preview), use data-src as well
      const dataSrc = el.getAttribute('data-iframe-src');
      if (dataSrc) {
        el.src = dataSrc;
        el.removeAttribute('data-iframe-src');
      }
      obs.unobserve(el);
    });
  }, { rootMargin: '200px 0px' });

  // ---- Card template ----
  function createCard(item) {
    const { file_original_name, extension, file_path, purpose } = item;
    const absPath = normalizePath(file_path);
    const card = document.createElement('div');
    card.className = 'bg-white rounded-xl shadow p-4';

    // Header (name + badge)
    const head = document.createElement('div');
    head.className = 'flex items-center justify-between';
    head.innerHTML = `
      <p class="text-sm text-gray-700 truncate" title="${file_original_name}">${file_original_name}</p>
      <span class="text-green-700 text-xs bg-green-50 px-2 py-0.5 rounded-full uppercase">${extension}</span>
    `;
    card.appendChild(head);

    // Preview area
    const previewWrap = document.createElement('div');
    previewWrap.className = 'mt-3';

    if (isImageExt(extension)) {
      const img = document.createElement('img');
      img.className = 'w-full h-40 object-cover rounded-lg border';
      img.alt = file_original_name;
      img.loading = 'lazy'; // browser-level lazy
      img.setAttribute('data-src', absPath); // IO lazy
      mediaObserver.observe(img);
      previewWrap.appendChild(img);

      // Link row
      const linkRow = document.createElement('div');
      linkRow.className = 'mt-2 flex items-center justify-between';
      linkRow.innerHTML = `
        <a href="${absPath}" target="_blank" class="text-sm text-blue-600 hover:underline">Open image</a>
        <span class="text-xs text-gray-500">${(item.size || 0).toLocaleString()} bytes</span>
      `;
      previewWrap.appendChild(linkRow);

    } else if (isPdfExt(extension)) {
      // PDF “thumbnail”: light-weight inline frame only when visible
      const thumb = document.createElement('div');
      thumb.className = 'w-full h-40 border rounded-lg overflow-hidden bg-gray-50 flex items-center justify-center';
      // Use an <iframe> but set src lazily to avoid heavy loads
      const iframe = document.createElement('iframe');
      iframe.className = 'w-full h-full';
      iframe.setAttribute('title', file_original_name);
      iframe.setAttribute('data-iframe-src', `${absPath}#toolbar=0&navpanes=0&scrollbar=0&page=1&zoom=page-width`);
      mediaObserver.observe(iframe);
      thumb.appendChild(iframe);
      previewWrap.appendChild(thumb);

      const linkRow = document.createElement('div');
      linkRow.className = 'mt-2 flex items-center justify-between';
      linkRow.innerHTML = `
        <a href="${absPath}" target="_blank" class="text-sm text-blue-600 hover:underline">Open PDF</a>
        <span class="text-xs text-gray-500">${(item.size || 0).toLocaleString()} bytes</span>
      `;
      previewWrap.appendChild(linkRow);

    } else {
      // Fallback for unknown types
      const fallback = document.createElement('div');
      fallback.className = 'w-full h-40 border rounded-lg bg-gray-50 flex items-center justify-center text-sm text-gray-500';
      fallback.textContent = 'No preview';
      previewWrap.appendChild(fallback);

      const linkRow = document.createElement('div');
      linkRow.className = 'mt-2';
      linkRow.innerHTML = `<a href="${absPath}" target="_blank" class="text-sm text-blue-600 hover:underline">Download</a>`;
      previewWrap.appendChild(linkRow);
    }

    card.appendChild(previewWrap);

    // Purpose
    const purposeP = document.createElement('p');
    purposeP.className = 'text-xs text-gray-500 mt-2';
    purposeP.textContent = purpose || '—';
    card.appendChild(purposeP);

    return card;
  }

  // ---- Skeletons ----
  function addSkeletons(n = 8) {
    for (let i = 0; i < n; i++) {
      const sk = document.createElement('div');
      sk.className = 'bg-white rounded-xl shadow p-4 animate-pulse';
      sk.innerHTML = `
        <div class="flex items-center justify-between">
          <div class="h-3 w-1/2 bg-gray-200 rounded"></div>
          <div class="h-4 w-12 bg-gray-100 rounded-full"></div>
        </div>
        <div class="mt-3 h-40 w-full bg-gray-100 rounded-lg"></div>
        <div class="mt-2 h-3 w-1/3 bg-gray-200 rounded"></div>
      `;
      grid.appendChild(sk);
    }
  }

  function removeSkeletons() {
    grid.querySelectorAll('.animate-pulse').forEach(el => el.remove());
  }

  // ---- Fetch helper ----
  async function fetchUploads() {
    if (loading || done) return;
    loading = true;
    addSkeletons(8);

    const body = {
      limit,
      offset,
      // only include filters if present
      ...(currentFilters.purpose ? { purpose: currentFilters.purpose } : {}),
      ...(currentFilters.extension ? { extension: currentFilters.extension } : {}),
      ...(currentFilters.name ? { name: currentFilters.name } : {}),
    };

    try {
      const res = await fetch(ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
      });
      const json = await res.json();

      removeSkeletons();

      if (!json.success) {
        console.error(json);
        loading = false;
        return;
      }

      const uploads = json?.data?.uploads || [];
      const count = uploads.length;

      uploads.forEach(item => grid.appendChild(createCard(item)));

      if (count < limit) {
        done = true;   // no more pages
      } else {
        offset += limit;
      }
    } catch (e) {
      console.error(e);
      removeSkeletons();
    } finally {
      loading = false;
    }
  }

  // ---- Infinite scroll ----
  const pageObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) fetchUploads();
    });
  }, { rootMargin: '300px 0px' });
  pageObserver.observe(sentinel);

  // ---- Filters ----
  document.getElementById('filterForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const purpose = document.getElementById('purpose').value.trim();
    const extension = document.getElementById('extension').value.trim();
    const name = document.getElementById('nameSearch').value.trim();

    // reset state
    currentFilters = { purpose, extension, name };
    offset = 0;
    done = false;
    grid.innerHTML = '';
    fetchUploads();
  });

  // Initial load
  fetchUploads();
</script>
<?php include("footer.php"); ?>
