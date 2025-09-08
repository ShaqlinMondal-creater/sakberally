<?php include("header.php") ?>

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
          <option value="Wood Working" >Wood Working</option>
          <option value="Pressure Washer Pump" >Pressure Washer Pump</option>
          <option value="Construction Machine" >Construction Machine</option>
          <option value="CUT-100 Air Plasma Cutting Machine" >CUT-100 Air Plasma Cutting Machine</option>
          <option value="Power Tools" >Power Tools</option>
          <option value="Sheet Metal Machine" >Sheet Metal Machine</option>
          <option value="Single Phase Floor Polishing Machine" >Single Phase Floor Polishing Machine</option>
          <option value="Air Compressor" >Air Compressor</option>
          <option value="Woodworking Machine" >Woodworking Machine</option>
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

    <div class="overflow-x-auto h-96">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left text-gray-600">
            <th class="px-4 py-3 font-medium">Product</th>
            <th class="px-4 py-3 font-medium">Category</th>
            <th class="px-4 py-3 font-medium">Price</th>
            <th class="px-4 py-3 font-medium">Features</th>
            <th class="px-4 py-3 font-medium">Updated Image</th>
            <th class="px-4 py-3 font-medium">Actions</th> <!-- New column for actions -->
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
  const BASE_URL = '<?php echo BASE_URL; ?>';
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
      $tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No products found</td></tr>`;
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
        <td class="px-4 py-3">
          <!-- Action Buttons (Delete, Update, Others) -->
          <div class="flex gap-2">
            <!-- Delete Button -->
            <button class="text-red-600 hover:text-red-800" title="Delete">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M6 18L18 6"/>
              </svg>
            </button>
            <!-- Update Button -->
            <button class="text-blue-600 hover:text-blue-800" title="Update">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 3h4v4m0 0L7 17l-4 4m16-6l-3 3m0 0L5 7"/>
              </svg>
            </button>
            <!-- Others Button -->
            <button class="text-green-600 hover:text-green-800" title="Other Actions">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 0v4m0-4h4m-4 0h-4"/>
              </svg>
            </button>
          </div>
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
      $tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-6 text-center text-red-600">Error loading products</td></tr>`;
      state.count = 0;
      updateMeta();
    } finally {
      setLoading(false);
    }
  }

  /* ======= EVENTS ======= */
  function debounce(fn, ms=350){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; }

  $search.addEventListener('input', debounce(e => {
    state.name = e.target.value.trim();
    state.offset = 0;
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
    $search.value = '';
    fetchProducts();
  })();
</script>
</body>
</html>
