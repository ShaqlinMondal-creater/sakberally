<?php include("header.php") ?>

<!-- Content -->
<main class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Filters -->
  <section class="bg-white rounded-xl shadow p-4">
    <div class="flex flex-col md:flex-row gap-3 md:items-center">
      <div class="flex gap-2">
        <button id="btnAdd" class="px-4 py-2 rounded-lg bg-brand text-white hover:bg-brand-700" onclick="openAddBrandPopup()">Add Brand</button>
      </div>

      <div class="md:ml-auto grid grid-cols-2 sm:grid-cols-4 gap-2 w-full md:w-auto">
        <input id="searchName" type="text" placeholder="Search by name…" class="col-span-2 sm:col-span-2 px-3 py-2 rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-brand/30">
        <select id="limit" class="px-3 py-2 rounded-lg bg-gray-100">
          <option value="10">10 / page</option>
          <option value="20">20 / page</option>
          <option value="50">50 / page</option>
          <option value="100">100 / page</option>
        </select>
      </div>
    </div>
  </section>

  <!-- Brands Table -->
  <section class="bg-white rounded-xl shadow overflow-hidden">
    <div class="px-4 py-3 border-b flex items-center justify-between text-sm">
      <div id="meta">Showing 0–0 of 0</div>
      <div id="status" class="text-gray-500"></div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left text-gray-600">
            <th class="px-4 py-3 font-medium">ID</th>
            <th class="px-4 py-3 font-medium">Brand</th>
            <th class="px-4 py-3 font-medium">Logo</th>
            <th class="px-4 py-3 font-medium">Catalogue</th>
            <th class="px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody id="tbodyBrands" class="divide-y">
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

<!-- Add Brand Popup (Hidden by default) -->
<div id="addBrandPopup" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 hidden justify-center items-center">
  <div class="bg-white p-6 rounded-lg w-1/3">
    <h3 class="text-xl font-semibold mb-4">Add New Brand</h3>
    <form id="addBrandForm" enctype="multipart/form-data">
      <div class="mb-4">
        <label for="brandName" class="block text-sm font-medium text-gray-700">Brand Name</label>
        <input type="text" id="brandName" name="name" class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/30" required />
      </div>
      <div class="mb-4">
        <label for="brandLogo" class="block text-sm font-medium text-gray-700">Brand Logo</label>
        <input type="file" id="brandLogo" name="brand_logo" class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/30" required />
      </div>
      <div class="mb-4">
        <label for="brandCatalogue" class="block text-sm font-medium text-gray-700">Brand Catalogue</label>
        <input type="file" id="brandCatalogue" name="brand_catalogue" class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/30" />
      </div>
      <div class="flex justify-end gap-3">
        <button type="button" class="px-4 py-2 rounded-lg bg-gray-300 text-white" onclick="closeAddBrandPopup()">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-brand text-white hover:bg-brand-700">Save Brand</button>
      </div>
    </form>
  </div>
</div>

</div>
</div>

<script>
  // Debounce helper
  function debounce(fn, ms=350) {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), ms);
    };
  }

  /* ======= CONFIG ======= */
  const BASE_URL = 'https://sakberally.com/apis';
  const API_URL  = BASE_URL + '/brands/fetch.php';
  const CREATE_API_URL = BASE_URL + '/brands/create.php'; // API for creating brand

  /* ======= STATE ======= */
  const state = {
    name: '',          // search
    limit: 100,        // limit set to 100
    offset: 0,
    count: 0,
    loading: false
  };

  /* ======= DOM ======= */
  const $tbody   = document.getElementById('tbodyBrands');
  const $meta    = document.getElementById('meta');
  const $status  = document.getElementById('status');
  const $msg     = document.getElementById('serverMessage');
  const $prev    = document.getElementById('btnPrev');
  const $next    = document.getElementById('btnNext');
  const $search  = document.getElementById('searchName');
  const $limit   = document.getElementById('limit');
  
  const $addBrandPopup = document.getElementById('addBrandPopup');
  const $addBrandForm  = document.getElementById('addBrandForm');
  const $brandName     = document.getElementById('brandName');
  const $brandLogo     = document.getElementById('brandLogo');
  const $brandCatalogue = document.getElementById('brandCatalogue');

  /* ======= HELPERS ======= */
  const imgOr = (a, b) => a || b || '';
  const escapeHtml = s => String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));

  function setLoading(v) {
    state.loading = v;
    $status.textContent = v ? 'Loading…' : '';
    [ $prev, $next ].forEach(b => v ? b.classList.add('btn-disabled') : b.classList.remove('btn-disabled'));
  }

  function renderRows(brands) {
    $tbody.innerHTML = '';
    if (!brands || !brands.length) {
      $tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No brands found</td></tr>`;
      return;
    }

    const frag = document.createDocumentFragment();
    brands.forEach(b => {
      const tr = document.createElement('tr');

      // Replace '../' with the base URL in brand paths
        const logo = b.brand_logo_path ? b.brand_logo_path.replace('../', 'https://sakberally.com/apis/') : '';
        const catalog = b.brand_catalouge_path ? b.brand_catalouge_path.replace('../', 'https://sakberally.com/apis/') : '#';


      tr.innerHTML = `
        <td class="px-4 py-3">${b.id}</td>
        <td class="px-4 py-3">${escapeHtml(b.name || '')}</td>
        <td class="px-4 py-3">
          <img src="${logo}" alt="${b.name}" class="w-16 h-16 object-contain rounded bg-gray-100">
        </td>
        <td class="px-4 py-3">
          ${catalog ? `<a href="${catalog}" target="_blank" class="text-brand hover:underline">View</a>` : '—'}
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

  /* ======= API ======= */
  async function fetchBrands() {
    setLoading(true);
    try {
      const body = {
        name: state.name || "",
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

      const data = json.data || { count: 0, brands: [] };
      state.count = Number(data.count) || 0;

      // alert(localStorage.getItem('user_token'));
      renderRows(data.brands || []);
      updateMeta();
    } catch (err) {
      console.error(err);
      $msg.textContent = 'Failed to fetch brands';
      $tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-6 text-center text-red-600">Error loading brands</td></tr>`;
      state.count = 0;
      updateMeta();
    } finally {
      setLoading(false);
    }
  }

  // Open the add brand popup
  function openAddBrandPopup() {
    $addBrandPopup.classList.remove('hidden');
  }

  // Close the add brand popup
  function closeAddBrandPopup() {
    $addBrandPopup.classList.add('hidden');
  }

  // Handle brand form submission
  $addBrandForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const atoken = localStorage.getItem('user_token');
    console.log(atoken);
    const formData = new FormData();
    formData.append('token', atoken); // Include the token in the form data
    formData.append('name', $brandName.value);
    formData.append('brand_logo', $brandLogo.files[0]);
    formData.append('brand_catalogue', $brandCatalogue.files[0]);    

    try {
      const res = await fetch(CREATE_API_URL, {
        method: 'POST',
        body: formData
      });

      const json = await res.json();

      if (json.success) {
        alert('Brand created successfully!');
        closeAddBrandPopup();
        fetchBrands(); // Re-fetch the brands list to update the table
      } else {
        alert('Error: ' + json.message);
      }
    } catch (err) {
      console.error(err);
      alert('Failed to create brand');
    }
  });

  /* ======= EVENTS ======= */
  $search.addEventListener('input', debounce(e => {
    state.name = e.target.value.trim();
    state.offset = 0;
    fetchBrands();
  }));

  $limit.addEventListener('change', e => {
    state.limit = Number(e.target.value) || 100;
    state.offset = 0;
    fetchBrands();
  });

  $prev.addEventListener('click', () => {
    if (state.offset <= 0) return;
    state.offset = Math.max(0, state.offset - state.limit);
    fetchBrands();
  });

  $next.addEventListener('click', () => {
    if (state.offset + state.limit >= state.count) return;
    state.offset += state.limit;
    fetchBrands();
  });

  /* ======= INIT ======= */
  (function init(){
    $search.value = '';
    fetchBrands();
  })();
</script>

</body>
</html>

