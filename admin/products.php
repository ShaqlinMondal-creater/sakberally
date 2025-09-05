<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin · Products</title>

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

  <style>
    .sidebar { transition: transform .25s ease; }
    .btn-disabled { opacity:.45; pointer-events:none; }
  </style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
  <div class="min-h-screen flex">

    <!-- Mobile overlay -->
    <div id="overlay" class="fixed inset-0 bg-black/40 z-30 hidden" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar fixed z-40 inset-y-0 left-0 w-72 bg-white shadow-lg lg:static lg:translate-x-0 -translate-x-full">
      <div class="h-full flex flex-col">
        <div class="flex items-center gap-2 h-16 px-4 border-b">
          <div class="w-9 h-9 grid place-items-center rounded-full bg-brand text-white font-bold">SA</div>
          <div class="leading-tight">
            <p class="font-semibold">S Akberally</p>
            <p class="text-xs text-gray-500">Admin Panel</p>
          </div>
          <button class="lg:hidden ml-auto p-2 rounded hover:bg-gray-100" onclick="closeSidebar()" aria-label="Close sidebar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Nav -->
        <nav class="p-3 space-y-1 overflow-y-auto">
          <a href="index.html" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/>
            </svg>
            Dashboard
          </a>

          <!-- PRODUCTS (ACTIVE) -->
          <a href="products.html" aria-current="page"
             class="flex items-center gap-3 px-3 py-2 rounded-lg bg-gray-100 text-gray-900">
            <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V7a2 2 0 00-2-2h-5l-2-2H6a2 2 0 00-2 2v6m16 0l-2 8H6l-2-8m16 0H4"/>
            </svg>
            Products
          </a>

          <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/>
            </svg>
            Orders
          </a>
          <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            Categories
          </a>
          <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Customers
          </a>
        </nav>

        <div class="mt-auto p-3 border-t text-xs text-gray-500">© <span id="year"></span> S Akberally</div>
      </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 min-w-0">
      <!-- Topbar -->
      <header class="sticky top-0 z-20 bg-white border-b">
        <div class="h-16 px-4 sm:px-6 lg:px-8 flex items-center gap-3">
          <button class="lg:hidden p-2 rounded hover:bg-gray-100" onclick="openSidebar()" aria-label="Open sidebar">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
          <h1 class="font-semibold text-lg hidden sm:block">Products</h1>

          <div class="ml-auto relative max-w-md w-full">
            <input type="text" id="topSearch" placeholder="Search products..."
                   class="w-full pl-10 pr-3 py-2.5 bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/40" />
            <svg class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                 d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>

          <div class="w-9 h-9 rounded-full bg-brand text-white grid place-items-center font-semibold">BK</div>
        </div>
      </header>

      <!-- Content -->
      <main class="p-4 sm:p-6 lg:p-8 space-y-6">

        <!-- Filters -->
        <section class="bg-white rounded-xl shadow p-4">
          <div class="flex flex-col md:flex-row gap-3 md:items-center">
            <div class="flex gap-2">
              <button id="btnAdd" class="px-4 py-2 rounded-lg bg-brand text-white hover:bg-brand-700">Add Product</button>
            </div>

            <div class="md:ml-auto grid grid-cols-2 sm:grid-cols-4 gap-2 w-full md:w-auto">
              <input id="searchName" type="text" placeholder="Search by name…"
                     class="col-span-2 sm:col-span-2 px-3 py-2 rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-brand/30">
              <select id="filterCategory" class="px-3 py-2 rounded-lg bg-gray-100">
                <option value="">All Categories</option>
                <option>Wood Working</option>
                <option>Lathe</option>
                <option>Welding</option>
                <option>Drilling</option>
              </select>
              <select id="limit" class="px-3 py-2 rounded-lg bg-gray-100">
                <option value="10">10 / page</option>
                <option value="20">20 / page</option>
                <option value="50">50 / page</option>
              </select>
            </div>
          </div>
        </section>

        <!-- Products Table -->
        <section class="bg-white rounded-xl shadow overflow-hidden">
          <div class="px-4 py-3 border-b flex items-center justify-between text-sm">
            <div id="meta">Showing 0–0 of 0</div>
            <div id="status" class="text-gray-500"></div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50">
                <tr class="text-left text-gray-600">
                  <th class="px-4 py-3 font-medium">Product</th>
                  <th class="px-4 py-3 font-medium">Category</th>
                  <th class="px-4 py-3 font-medium">Price</th>
                  <th class="px-4 py-3 font-medium">Features</th>
                  <th class="px-4 py-3 font-medium">Updated Image</th>
                </tr>
              </thead>
              <tbody id="tbodyProducts" class="divide-y">
                <!-- rows injected here -->
              </tbody>
            </table>
          </div>

          <!-- Footer / pagination -->
          <div class="p-4 flex flex-col sm:flex-row gap-3 sm:items-center justify-between">
            <div class="text-sm text-gray-600" id="serverMessage"></div>
            <nav class="flex items-center gap-1">
              <button id="btnPrev" class="px-3 py-2 rounded-lg hover:bg-gray-100">Prev</button>
              <button id="btnNext" class="px-3 py-2 rounded-lg hover:bg-gray-100">Next</button>
            </nav>
          </div>
        </section>
      </main>
    </div>
  </div>

  <script>
    /* ======= CONFIG ======= */
    // Replace with your real base url (no trailing slash), e.g. 'https://sakberally.com'
    const BASE_URL = 'http://localhost/sakberally/apis';
    const API_URL  = BASE_URL + '/products/fetch.php';

    /* ======= SIDEBAR ======= */
    function openSidebar() { document.getElementById('sidebar').style.transform = 'translateX(0)'; document.getElementById('overlay').classList.remove('hidden'); }
    function closeSidebar() { document.getElementById('sidebar').style.transform = 'translateX(-100%)'; document.getElementById('overlay').classList.add('hidden'); }
    document.getElementById('year').textContent = new Date().getFullYear();

    /* ======= STATE ======= */
    const state = {
      name: '',          // search
      category: '',      // filter
      limit: 100,
      offset: 0,
      count: 0,
      loading: false
    };

    /* ======= DOM ======= */
    const $tbody   = document.getElementById('tbodyProducts');
    const $meta    = document.getElementById('meta');
    const $status  = document.getElementById('status');
    const $msg     = document.getElementById('serverMessage');
    const $prev    = document.getElementById('btnPrev');
    const $next    = document.getElementById('btnNext');
    const $search  = document.getElementById('searchName');
    const $topSrch = document.getElementById('topSearch');
    const $cat     = document.getElementById('filterCategory');
    const $limit   = document.getElementById('limit');

    /* ======= HELPERS ======= */
    const currency = (n) => {
      if (n === null || n === undefined) return '—';
      const num = Number(n);
      if (!isFinite(num) || num <= 0) return '—';
      return '₹ ' + num.toLocaleString('en-IN');
    };
    const imgOr = (a, b) => a || b || '';

    function setLoading(v) {
      state.loading = v;
      $status.textContent = v ? 'Loading…' : '';
      [ $prev, $next ].forEach(b => v ? b.classList.add('btn-disabled') : b.classList.remove('btn-disabled'));
    }

    function renderRows(products) {
      $tbody.innerHTML = '';
      if (!products || !products.length) {
        $tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No products found</td></tr>`;
        return;
      }

      const frag = document.createDocumentFragment();
      products.forEach(p => {
        const tr = document.createElement('tr');

        const image = imgOr(p.upload_path, p.upd_link);
        const price = currency(p.price);

        tr.innerHTML = `
          <td class="px-4 py-3">
            <div class="flex items-center gap-3">
              <img src="${image}" onerror="this.src='https://via.placeholder.com/56x56?text=%20';" alt=""
                   class="w-14 h-14 object-cover rounded bg-gray-100">
              <div>
                <div class="font-medium">${escapeHtml(p.name || '')}</div>
                <div class="text-gray-500 text-xs">${(p.unit || '').toString()}</div>
              </div>
            </div>
          </td>
          <td class="px-4 py-3">${escapeHtml(p.category_name || '')}</td>
          <td class="px-4 py-3">${price}</td>
          <td class="px-4 py-3">
            ${p.features ? '<span class="text-xs text-gray-500">HTML</span>' : '<span class="text-xs text-gray-400">—</span>'}
          </td>
          <td class="px-4 py-3">
            ${image ? '<a href="'+image+'" target="_blank" class="text-brand hover:underline">Open</a>' : '—'}
          </td>
        `;
        frag.appendChild(tr);
      });
      $tbody.appendChild(frag);
    }

    function updateMeta() {
      const start = state.count ? state.offset + 1 : 0;
      const end = Math.min(state.offset + state.limit, state.count);
      $meta.textContent = `Showing ${start}–${end} of ${state.count}`;
      $prev.classList.toggle('btn-disabled', state.offset <= 0);
      $next.classList.toggle('btn-disabled', state.offset + state.limit >= state.count);
    }

    function escapeHtml(s) {
      return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    /* ======= API ======= */
    async function fetchProducts() {
      setLoading(true);
      try {
        const body = {
          name: state.name || "",
          category: state.category || "",
          limit: state.limit,
          offset: state.offset
        };
        const res = await fetch(API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(body)
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const json = await res.json();

        $msg.textContent = json.message || '';
        if (!json.success) throw new Error(json.message || 'Request failed');

        const data = json.data || { count: 0, products: [] };
        state.count = Number(data.count) || 0;

        renderRows(data.products || []);
        updateMeta();
      } catch (err) {
        console.error(err);
        $msg.textContent = 'Failed to fetch products';
        $tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-6 text-center text-red-600">Error loading products</td></tr>`;
        state.count = 0;
        updateMeta();
      } finally {
        setLoading(false);
      }
    }

    /* ======= EVENTS ======= */
    // Debounce helper
    function debounce(fn, ms=350){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; }

    $search.addEventListener('input', debounce(e => {
      state.name = e.target.value.trim();
      state.offset = 0;
      fetchProducts();
    }));

    $topSrch.addEventListener('input', debounce(e => {
      state.name = e.target.value.trim();
      state.offset = 0;
      $search.value = state.name;
      fetchProducts();
    }));

    $cat.addEventListener('change', e => {
      state.category = e.target.value;
      state.offset = 0;
      fetchProducts();
    });

    $limit.addEventListener('change', e => {
      state.limit = Number(e.target.value) || 10;
      state.offset = 0;
      fetchProducts();
    });

    $prev.addEventListener('click', () => {
      if (state.offset <= 0) return;
      state.offset = Math.max(0, state.offset - state.limit);
      fetchProducts();
    });

    $next.addEventListener('click', () => {
      if (state.offset + state.limit >= state.count) return;
      state.offset += state.limit;
      fetchProducts();
    });

    /* ======= INIT ======= */
    (function init(){
      // copy search from topbar to filter on load
      $search.value = '';
      $topSrch.value = '';
      fetchProducts();
    })();
  </script>
</body>
</html>
