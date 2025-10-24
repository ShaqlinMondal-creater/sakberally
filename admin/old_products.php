<?php include("header.php") ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

<!-- Add Product Modal -->
<div id="modalAdd" class="fixed inset-0 z-[100] hidden">
  <!-- overlay -->
  <div class="absolute inset-0 bg-black/40" data-close-modal></div>

  <!-- dialog -->
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl">
      <!-- header -->
      <div class="px-5 py-4 border-b flex items-center justify-between">
        <h3 class="text-lg font-semibold">Add Product</h3>
        <button class="p-2 rounded hover:bg-gray-100" data-close-modal aria-label="Close">
          ✕
        </button>
      </div>

      <!-- body -->
      <form id="formAddProduct" class="px-5 py-4 space-y-4">
        <!-- token (hidden or fill automatically from localStorage) -->
        <input type="hidden" name="token" id="ap_token" value="">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="text-sm text-gray-600">Name</label>
            <input name="name" class="w-full mt-1 px-3 py-2 border rounded-lg" placeholder='Haneri JadeSpin 48"'
                   required>
          </div>

          <div>
            <label class="text-sm text-gray-600">Price</label>
            <input name="price" type="number" step="0.01" class="w-full mt-1 px-3 py-2 border rounded-lg"
                   placeholder="2299.00" required>
          </div>

          <div>
            <label class="text-sm text-gray-600">Unit</label>
            <input name="unit" class="w-full mt-1 px-3 py-2 border rounded-lg" placeholder="pcs" required>
          </div>

          <div>
            <label class="text-sm text-gray-600">Category</label>
            <!-- We'll populate this from the page's filterCategory options -->
            <select name="category_id" id="ap_category" class="w-full mt-1 px-3 py-2 border rounded-lg" required>
              <option value="">Select category…</option>
            </select>
          </div>

          <!-- Brand (SELECT fed from API) -->
          <div>
            <label class="text-sm text-gray-600">Brand</label>
            <select name="brand_id" id="ap_brand" class="w-full mt-1 px-3 py-2 border rounded-lg">
              <option value="">Select brand…</option>
            </select>
          </div>

          <div>
            <label class="text-sm text-gray-600">Short Description</label>
            <input name="short_description" class="w-full mt-1 px-3 py-2 border rounded-lg"
                   placeholder='48" energy-saving fan'>
          </div>
        </div>

        <div>
          <label class="text-sm text-gray-600">Description (plain text; server formats to HTML)</label>
          <textarea name="description" rows="2" class="w-full mt-1 px-3 py-2 border rounded-lg"
                    placeholder="Premium energy-efficient ceiling fan with aerodynamically designed blades."></textarea>
        </div>

        <!-- Uploads (multiple images) -->
        <div>
          <label class="text-sm text-gray-600">Uploads (Images)</label>
          <input id="ap_uploads" name="uploads" type="file" accept="image/*" multiple
                class="w-full mt-1 px-3 py-2 border rounded-lg">
          <p class="text-xs text-gray-500 mt-1">You can select multiple images. They’ll be uploaded after the product is created.</p>
        </div>

        <!-- FEATURES -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="text-sm font-medium">Features</label>
            <button type="button" id="btnAddFeature"
                    class="px-3 py-1.5 rounded-lg border hover:bg-gray-50 text-sm">
              + Add feature
            </button>
          </div>

          <div id="featureList" class="space-y-2">
            <!-- Rows injected here -->
          </div>

          <!-- one hidden template row -->
          <template id="featureRowTpl">
            <div class="grid grid-cols-[1fr_1fr_auto] gap-2 items-center">
              <input class="ap-feature-key px-3 py-2 border rounded-lg" placeholder="e.g., color">
              <input class="ap-feature-val px-3 py-2 border rounded-lg" placeholder="e.g., Matte Black">
              <button type="button" class="ap-feature-del px-3 py-2 border rounded-lg text-red-600 hover:bg-red-50">
                Remove
              </button>
            </div>
          </template>
        </div>

        <div id="ap_msg" class="text-sm text-red-600"></div>

        <!-- footer -->
        <div class="pt-3 border-t flex items-center justify-end gap-2">
          <button type="button" class="px-4 py-2 rounded-lg border hover:bg-gray-50" data-close-modal>Cancel</button>
          <button id="btnSubmitAdd" type="submit"
                  class="px-4 py-2 rounded-lg bg-brand text-white hover:bg-brand-700">
            Save Product
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  /* ======= CONFIG ======= */
  const API_URL  = '<?php echo BASE_URL; ?>/products/fetch.php';
  const FALLBACK_IMG = 'assets/images/placeholder-product.png';
  const CATS_API_URL   = '<?php echo BASE_URL; ?>/categories/fetch.php';
  const BRANDS_API_URL = '<?php echo BASE_URL; ?>/brands/fetch.php';
  const DELETE_API_URL = '<?php echo BASE_URL; ?>/products/delete.php';
  const UPDATE_API_URL = '<?php echo BASE_URL; ?>/products/update.php';
  const CREATE_API_URL = '<?php echo BASE_URL; ?>/products/create.php';
  const UPLOAD_IMG_API_URL = '<?php echo BASE_URL; ?>/products/upload_images.php';
  const DELETE_UPLOAD_API_URL  ='<?php echo BASE_URL; ?>/products/delete_images.php';
  /* ======= SIDEBAR ======= */
  function openSidebar() { document.getElementById('sidebar')?.style && (document.getElementById('sidebar').style.transform = 'translateX(0)'); document.getElementById('overlay')?.classList?.remove('hidden'); }
  function closeSidebar() { document.getElementById('sidebar')?.style && (document.getElementById('sidebar').style.transform = 'translateX(-100%)'); document.getElementById('overlay')?.classList?.add('hidden'); }
  const yEl = document.getElementById('year'); if (yEl) yEl.textContent = new Date().getFullYear();

  /* ======= STATE ======= */
  const state = {
    name: '',          // search
    category: '',      // filter
    limit: 500,        // you can change via dropdown
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

  // Safely get the primary image: prefer uploads[0].upload_path; fallback to other fields; then placeholder
  function getPrimaryImage(p) {
    const firstUpload = Array.isArray(p.uploads) && p.uploads[0]?.upload_path
      ? p.uploads[0].upload_path
      : null;

    const src = firstUpload || p.upload_path || p.file_path || p.upd_link || '';
    if (!src) return FALLBACK_IMG;  // ✅ local placeholder instead of via.placeholder.com

    // absolute or data URI?
    if (/^(https?:)?\/\//i.test(src) || /^data:/i.test(src)) return src;

    // relative path → join with BASE_URL
    const BASE = '<?php echo BASE_URL; ?>/';
    const cleaned = src.replace(/^(\.\.\/)+|^\.\/+/, '');
    return BASE.replace(/\/+$/, '') + '/' + cleaned.replace(/^\/+/, '');
  }

  function setLoading(v) {
    state.loading = v;
    $status.textContent = v ? 'Loading…' : '';
    [$prev, $next].forEach(b => b && (v ? b.classList.add('btn-disabled') : b.classList.remove('btn-disabled')));
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
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
      const imgUrl = getPrimaryImage(p);
      const price = currency(p.price);

      tr.innerHTML = `
        <td class="px-4 py-3">
          <div class="flex items-center gap-3">
            <img src="${imgUrl || FALLBACK_IMG}" alt="" class="w-14 h-14 object-cover rounded bg-gray-100"
                onerror="this.onerror=null; this.src='assets/images/placeholder-product.png'">
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
          ${imgUrl ? '<a href="'+imgUrl+'" target="_blank" class="text-brand hover:underline">Open</a>' : '—'}
        </td>
        <td class="px-4 py-3">
          <div class="flex gap-2">
            <!-- Delete Button -->
            <button class="text-red-600 hover:text-red-800 btn-delete" data-id="${p.id}" title="Delete">
              Delete
            </button>
            <!-- Update Button -->
            <button class="text-blue-600 hover:text-blue-800 btn-update" data-id="${p.id}" data-category-id="${p.category_id ?? ''}"
              data-brand-id="${p.brand_id ?? ''}" data-price="${p.price ?? ''}" data-name="${escapeHtml(p.name || '')}" 
              data-uploads="${encodeURIComponent(JSON.stringify(p.uploads || []))}" title="Update"
            >
              Update
            </button>
            <!-- Others Button -->
            <button class="text-green-600 hover:text-green-800 btn-more" data-id="${p.id}" title="Other Actions">
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

  // Optional: action buttons (wire up to your APIs later)
  document.addEventListener('click', (e) => {
    const del = e.target.closest('.btn-delete');
    const upd = e.target.closest('.btn-update');
    const more = e.target.closest('.btn-more');
    if (del) {
      const id = del.dataset.id;
      console.log('Delete product', id); // call your delete API here
      if (!id) return;

      // optional: lock the button to prevent double taps
      del.disabled = true;
      deleteProduct(id).finally(() => { del.disabled = false; });
      return;
    }
    if (upd) {
      const id = upd.dataset.id;
      console.log('Update product', id); // open update modal / navigate
      if (!id) return;
      updateProduct(id);
      return;
    }
    if (more) {
      const id = more.dataset.id;
      console.log('More actions for', id);
    }
  });

  /* ======= INIT ======= */
  (function init(){
    if ($limit) $limit.value = String(state.limit);
    if ($search) $search.value = '';
    fetchProducts();
  })();
</script>

<!-- Delete -->
<script>
  
  function getAuthToken() {
    return localStorage.getItem('user_token') || '';
  }

  function findRowByProductId(id) {
    return $tbody.querySelector(`.btn-delete[data-id="${id}"]`)?.closest('tr') || null;
  }
  let deletingIds = new Set(); // guard against double clicks

  async function deleteProduct(productId) {
    const token = getAuthToken();
    if (!token) {
      $msg.textContent = 'Unauthorized: missing token.';
      return;
    }

    // Confirm
    const ok = window.confirm('Delete this product permanently? This will also remove associated images.');
    if (!ok) return;

    // Re-entrancy guard
    if (deletingIds.has(productId)) return;
    deletingIds.add(productId);

    // Optimistic UI: fade out row (optional)
    const row = findRowByProductId(productId);
    if (row) row.style.opacity = '0.5';

    // Show status
    $msg.textContent = 'Deleting…';

    try {
      const res = await fetch(DELETE_API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          token: token,
          product_id: Number(productId)
        })
      });

      const json = await res.json().catch(() => ({}));
      if (!res.ok || json.success === false) {
        throw new Error(json.message || `HTTP ${res.status}`);
      }

      // If row existed, remove it optimistically
      if (row && row.parentElement) {
        row.parentElement.removeChild(row);
      }

      // If the page might now be empty, adjust pagination:
      // If there are no more rows in DOM and we’re not on the first page, go back one page before refetch.
      const rowsLeft = $tbody.querySelectorAll('tr').length;
      if (rowsLeft === 0 && state.offset > 0) {
        state.offset = Math.max(0, state.offset - state.limit);
      }

      // Refresh list from server to keep counts correct
      $msg.textContent = json.message || 'Product deleted';
      await fetchProducts();
    } catch (err) {
      console.error(err);
      // Revert optimistic change if deletion failed
      if (row) row.style.opacity = '';
      $msg.textContent = err.message || 'Failed to delete product.';
    } finally {
      deletingIds.delete(productId);
    }
  }
</script>
<!-- Update -->
 <script>
  
  async function fetchCategoriesSimple() {
    try {
      const res = await fetch(CATS_API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ wise: 'simple', limit: 100 })
      });
      const json = await res.json();
      const list = json?.data?.categories ?? json?.categories ?? (Array.isArray(json) ? json : []);
      // Normalize to {id, name}
      return list.map(c => ({
        id:  c.id ?? c.category_id ?? c.value,
        name: c.name ?? c.category_name ?? c.label ?? String(c.id ?? c.category_id ?? '')
      })).filter(x => x.id);
    } catch (e) { console.error(e); return []; }
  }

  async function fetchBrandsList() {
    try {
      const res = await fetch(BRANDS_API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ limit: 100 })
      });
      const json = await res.json();
      const list = json?.data?.brands ?? json?.brands ?? (Array.isArray(json) ? json : []);
      // Normalize to {id, name}
      return list.map(b => ({
        id:  b.id ?? b.brand_id ?? b.value,
        name: b.name ?? b.brand_name ?? b.label ?? String(b.id ?? b.brand_id ?? '')
      })).filter(x => x.id);
    } catch (e) { console.error(e); return []; }
  }

  function buildOptions(list, selectedId) {
    const sid = selectedId == null ? null : String(selectedId);
    return ['<option value="">— Select —</option>']
      .concat(list.map(({id, name}) => {
        const sel = (sid && String(id) === sid) ? ' selected' : '';
        return `<option value="${String(id)}"${sel}>${String(name)}</option>`;
      })).join('');
  }

  function joinWithBase(urlOrPath) {
    if (!urlOrPath) return '';
    if (/^(https?:)?\/\//i.test(urlOrPath) || /^data:/i.test(urlOrPath)) return urlOrPath;
    const BASE = '<?php echo BASE_URL; ?>/';
    const cleaned = String(urlOrPath).replace(/^(\.\.\/)+|^\.\/+/, '');
    return BASE.replace(/\/+$/, '') + '/' + cleaned.replace(/^\/+/, '');
  }
  function normalizeUploads(raw) {
    if (!Array.isArray(raw)) return [];
    return raw.map(u => ({
      id: u.id ?? u.upload_id ?? u.image_id ?? null,
      path: joinWithBase(u.upload_path ?? u.path ?? u.file_path ?? u.url ?? ''),
    })).filter(u => u.path);
  }
  function renderUploadsGrid(uploads) {
    if (!uploads.length) {
      return `<div class="text-xs text-gray-500">No images yet.</div>`;
    }
    return `
      <div class="grid grid-cols-3 gap-8" style="gap:10px">
        ${uploads.map(u => `
          <div class="relative group border rounded-lg overflow-hidden">
            <img src="${u.path}" alt="" style="width:100%;height:96px;object-fit:cover;">
            <button
              type="button"
              class="sw-del-upload"
              data-upload-id="${u.id ?? ''}"
              style="
                position:absolute;top:6px;right:6px;
                background:#ef4444;color:#fff;border:none;border-radius:9999px;
                width:28px;height:28px;display:flex;align-items:center;justify-content:center;
                opacity:0.9"
              title="Delete image"
            >✕</button>
          </div>
        `).join('')}
      </div>
    `;
  }

  // async function updateProduct(productId) {
  //   const token = localStorage.getItem('user_token') || '';
  //   if (!token) { $msg.textContent = 'Unauthorized: missing token.'; return; }

  //   // Read current values from the row/button
  //   const row = findRowByProductId(productId);
  //   const btn = row?.querySelector(`.btn-update[data-id="${productId}"]`);
  //   const currentName  = btn?.dataset.name || row?.querySelector('td:nth-child(1) .font-medium')?.textContent?.trim() || '';
  //   const currentPrice = btn?.dataset.price || row?.querySelector('td:nth-child(3)')?.textContent?.replace(/[₹,]/g,'').trim() || '';
  //   const currentCatId = btn?.dataset.categoryId || '';
  //   const currentBrandId = btn?.dataset.brandId || '';
  //   const currentCatName = row?.querySelector('td:nth-child(2)')?.textContent?.trim() || '';

  //   // Load lists in parallel
  //   const [cats, brands] = await Promise.all([fetchCategoriesSimple(), fetchBrandsList()]);

  //   // If we don't have category_id but have name, try to match by name
  //   let selectedCatId = currentCatId;
  //   if (!selectedCatId && currentCatName) {
  //     const match = cats.find(c => (c.name || '').toLowerCase() === currentCatName.toLowerCase());
  //     if (match) selectedCatId = String(match.id);
  //   }

  //   // Build selects
  //   const catOptions   = buildOptions(cats, selectedCatId || null);
  //   const brandOptions = buildOptions(brands, currentBrandId || null);

  //   // Clean layout: 2-column grid in Swal
  //   const html = `
  //     <style>
  //       .swal-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
  //       .swal-form-grid .full { grid-column: 1 / -1; }
  //       .swal-label { font-size:12px; color:#6b7280; display:block; margin-bottom:4px; }
  //       .swal-input, .swal-select { width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; }
  //     </style>
  //     <div class="swal-form-grid">
  //       <div class="full">
  //         <label class="swal-label">Name</label>
  //         <input id="sw-name" class="swal-input" placeholder="Product Name" value="${currentName.replace(/"/g,'&quot;')}">
  //       </div>
  //       <div>
  //         <label class="swal-label">Price</label>
  //         <input id="sw-price" class="swal-input" type="number" step="0.01" placeholder="Price" value="${currentPrice}">
  //       </div>
  //       <div>
  //         <label class="swal-label">Category</label>
  //         <select id="sw-category" class="swal-select">${catOptions}</select>
  //       </div>
  //       <div>
  //         <label class="swal-label">Brand</label>
  //         <select id="sw-brand" class="swal-select">${brandOptions}</select>
  //       </div>
  //     </div>
  //     <p style="font-size:12px;color:#9ca3af;margin-top:8px;">Leave fields empty to keep existing values.</p>
  //   `;

  //   const { value: formValues } = await Swal.fire({
  //     title: 'Update Product',
  //     html,
  //     focusConfirm: false,
  //     showCancelButton: true,
  //     confirmButtonText: 'Update',
  //     preConfirm: () => {
  //       const name = document.getElementById('sw-name').value.trim();
  //       const priceStr = document.getElementById('sw-price').value;
  //       const category_id = document.getElementById('sw-category').value;
  //       const brand_id = document.getElementById('sw-brand').value;

  //       const out = { };
  //       if (name) out.name = name;
  //       if (priceStr) {
  //         const num = Number(priceStr);
  //         if (isNaN(num) || num < 0) {
  //           Swal.showValidationMessage('Price must be a valid non-negative number.');
  //           return false;
  //         }
  //         out.price = num;
  //       }
  //       if (category_id) out.category_id = Number(category_id);
  //       if (brand_id) out.brand_id = Number(brand_id);
  //       return out;
  //     }
  //   });

  //   if (!formValues) return; // user cancelled

  //   const payload = {
  //     token,
  //     product_id: Number(productId),
  //     ...formValues
  //   };

  //   try {
  //     const res = await fetch(UPDATE_API_URL, {
  //       method: 'POST',
  //       headers: { 'Content-Type': 'application/json' },
  //       body: JSON.stringify(payload)
  //     });
  //     const json = await res.json().catch(() => ({}));
  //     if (!res.ok || json.success === false) throw new Error(json.message || `HTTP ${res.status}`);

  //     Swal.fire('Updated!', json.message || 'Product updated successfully.', 'success');
  //     fetchProducts();
  //   } catch (err) {
  //     console.error(err);
  //     Swal.fire('Error', err.message || 'Failed to update product.', 'error');
  //   }
  // }

  async function updateProduct(productId) {
    const token = localStorage.getItem('user_token') || '';
    if (!token) { $msg.textContent = 'Unauthorized: missing token.'; return; }

    // Get current values
    const row = findRowByProductId(productId);
    const btn = row?.querySelector(`.btn-update[data-id="${productId}"]`);
    const currentName  = btn?.dataset.name || row?.querySelector('td:nth-child(1) .font-medium')?.textContent?.trim() || '';
    const currentPrice = btn?.dataset.price || row?.querySelector('td:nth-child(3)')?.textContent?.replace(/[₹,]/g,'').trim() || '';
    const currentCatId = btn?.dataset.categoryId || '';
    const currentBrandId = btn?.dataset.brandId || '';
    const currentCatName = row?.querySelector('td:nth-child(2)')?.textContent?.trim() || '';

    // Decode uploads from data-attr
    let uploads = [];
    try {
      const raw = btn?.dataset.uploads ? JSON.parse(decodeURIComponent(btn.dataset.uploads)) : [];
      uploads = normalizeUploads(raw);
    } catch(_) {}

    // Load lists
    const [cats, brands] = await Promise.all([fetchCategoriesSimple(), fetchBrandsList()]);

    // Category id fallback by name
    let selectedCatId = currentCatId;
    if (!selectedCatId && currentCatName) {
      const match = cats.find(c => (c.name || '').toLowerCase() === currentCatName.toLowerCase());
      if (match) selectedCatId = String(match.id);
    }

    // Build selects
    const catOptions   = buildOptions(cats, selectedCatId || null);
    const brandOptions = buildOptions(brands, currentBrandId || null);

    // HTML
    const html = `
      <style>
        .swal-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .swal-form-grid .full { grid-column: 1 / -1; }
        .swal-label { font-size:12px; color:#6b7280; display:block; margin-bottom:4px; }
        .swal-input, .swal-select { width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; }
        .sw-actions-row { display:flex; gap:8px; align-items:center; margin-top:8px; }
        .sw-upload-btn { padding:8px 12px; border-radius:8px; background:#2563eb; color:#fff; border:none; }
        .sw-upload-msg { font-size:12px; color:#6b7280; margin-top:4px; }
      </style>

      <div class="swal-form-grid">
        <div class="full">
          <label class="swal-label">Name</label>
          <input id="sw-name" class="swal-input" placeholder="Product Name" value="${currentName.replace(/"/g,'&quot;')}">
        </div>
        <div>
          <label class="swal-label">Price</label>
          <input id="sw-price" class="swal-input" type="number" step="0.01" placeholder="Price" value="${currentPrice}">
        </div>
        <div>
          <label class="swal-label">Category</label>
          <select id="sw-category" class="swal-select">${catOptions}</select>
        </div>
        <div>
          <label class="swal-label">Brand</label>
          <select id="sw-brand" class="swal-select">${brandOptions}</select>
        </div>

        <div class="full">
          <label class="swal-label">Images</label>
          <div id="sw-uploads">${renderUploadsGrid(uploads)}</div>

          <div class="sw-actions-row">
            <input id="sw-files" type="file" accept="image/*" multiple class="swal-input" style="padding:8px">
            <button type="button" id="sw-upload-btn" class="sw-upload-btn">Upload</button>
          </div>
          <div id="sw-upload-msg" class="sw-upload-msg">You can upload multiple images. Click ✕ to delete an image.</div>
        </div>
      </div>

      <p style="font-size:12px;color:#9ca3af;margin-top:8px;">Leave text fields empty to keep existing values.</p>
    `;

    const { value: formValues } = await Swal.fire({
      title: 'Update Product',
      html,
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: 'Update',
      didOpen: () => {
        // Upload handler
        const uploadBtn = document.getElementById('sw-upload-btn');
        const fileInput = document.getElementById('sw-files');
        const msgEl     = document.getElementById('sw-upload-msg');
        const gridEl    = document.getElementById('sw-uploads');

        uploadBtn?.addEventListener('click', async () => {
          const files = fileInput?.files ?? [];
          if (!files.length) { msgEl.textContent = 'Select 1 or more images first.'; return; }
          msgEl.textContent = 'Uploading…';
          uploadBtn.disabled = true;

          try {
            // Use existing upload API
            const fd = new FormData();
            fd.append('token', token);
            fd.append('product_id', String(productId));
            [...files].forEach(f => fd.append('uploads[]', f, f.name));

            const res = await fetch(UPLOAD_IMG_API_URL, { method: 'POST', body: fd });
            const json = await res.json().catch(() => ({}));
            if (!res.ok || json.success === false) throw new Error(json.message || `HTTP ${res.status}`);

            // Prefer server-returned uploads; else, show local previews
            let newUploads = [];
            if (json?.data?.uploads) {
              newUploads = normalizeUploads(json.data.uploads);
            } else {
              newUploads = [...files].map(f => ({ id: null, path: URL.createObjectURL(f) }));
            }
            uploads = [...newUploads, ...uploads]; // prepend new
            gridEl.innerHTML = renderUploadsGrid(uploads);
            msgEl.textContent = json.message || 'Images uploaded.';
            fileInput.value = '';
          } catch (err) {
            console.error(err);
            msgEl.textContent = err.message || 'Upload failed.';
          } finally {
            uploadBtn.disabled = false;
          }
        });

        // Delete handler (event delegation)
        gridEl?.addEventListener('click', async (ev) => {
          const btnDel = ev.target.closest('.sw-del-upload');
          if (!btnDel) return;
          const uploadId = btnDel.dataset.uploadId;
          if (!uploadId) { // If no id, it’s a local preview (not saved yet)
            btnDel.closest('.relative')?.remove();
            return;
          }

          btnDel.disabled = true;
          msgEl.textContent = 'Deleting image…';
          try {
            const res = await fetch(DELETE_UPLOAD_API_URL, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ token, upload_id: Number(uploadId) })
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok || json.success === false) throw new Error(json.message || `HTTP ${res.status}`);

            // remove from local array + DOM
            uploads = uploads.filter(u => String(u.id) !== String(uploadId));
            btnDel.closest('.relative')?.remove();
            msgEl.textContent = json.message || 'Image deleted.';
          } catch (err) {
            console.error(err);
            msgEl.textContent = err.message || 'Failed to delete image.';
            btnDel.disabled = false;
          }
        });
      },
      preConfirm: () => {
        const name = document.getElementById('sw-name').value.trim();
        const priceStr = document.getElementById('sw-price').value;
        const category_id = document.getElementById('sw-category').value;
        const brand_id = document.getElementById('sw-brand').value;

        const out = {};
        if (name) out.name = name;
        if (priceStr) {
          const num = Number(priceStr);
          if (isNaN(num) || num < 0) {
            Swal.showValidationMessage('Price must be a valid non-negative number.');
            return false;
          }
          out.price = num;
        }
        if (category_id) out.category_id = Number(category_id);
        if (brand_id) out.brand_id = Number(brand_id);
        return out;
      }
    });

    if (!formValues) return; // cancelled

    const payload = { token, product_id: Number(productId), ...formValues };

    try {
      const res = await fetch(UPDATE_API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json().catch(() => ({}));
      if (!res.ok || json.success === false) throw new Error(json.message || `HTTP ${res.status}`);

      Swal.fire('Updated!', json.message || 'Product updated successfully.', 'success');
      fetchProducts();
    } catch (err) {
      console.error(err);
      Swal.fire('Error', err.message || 'Failed to update product.', 'error');
    }
  }

 </script>
<script>
  /* ======= API URLs ======= */

  /* ======= MODAL ELTS ======= */
  const $modalAdd    = document.getElementById('modalAdd');
  const $formAdd     = document.getElementById('formAddProduct');
  const $apToken     = document.getElementById('ap_token');
  const $apCategory  = document.getElementById('ap_category');
  const $apBrand     = document.getElementById('ap_brand');
  const $apMsg       = document.getElementById('ap_msg');
  const $btnAdd      = document.getElementById('btnAdd');
  const $featureList = document.getElementById('featureList');
  const $featureTpl  = document.getElementById('featureRowTpl');

  /* ======= Helpers ======= */
  function openAddModal() {
    resetAddForm();
    // Load dropdown data in parallel
    Promise.all([loadCategories(), loadBrands()]).finally(() => {
      $modalAdd.classList.remove('hidden');
    });
  }
  function closeAddModal() { $modalAdd.classList.add('hidden'); }

  // feature rows
  function addFeatureRow(k = '', v = '') {
    const node = $featureTpl.content.firstElementChild.cloneNode(true);
    node.querySelector('.ap-feature-key').value = k;
    node.querySelector('.ap-feature-val').value = v;
    $featureList.appendChild(node);
  }
  function readFeaturesIntoObject() {
    const out = {};
    const keys = $featureList.querySelectorAll('.ap-feature-key');
    const vals = $featureList.querySelectorAll('.ap-feature-val');
    keys.forEach((kEl, i) => {
      const k = (kEl.value || '').trim();
      const v = (vals[i]?.value || '').trim();
      if (k) out[k] = v;
    });
    return out;
  }

  // reset on open
  function resetAddForm() {
    $formAdd.reset();
    $apMsg.textContent = '';

    // (1) Token from localStorage user_token
    const token = localStorage.getItem('user_token') || '';
    $apToken.value = token;

    // seed features
    $featureList.innerHTML = '';
    addFeatureRow('color', 'Matte Black');
  }

  /* ======= Load dropdowns from APIs ======= */

  // (2) Categories -> POST { wise: "simple" }
  async function loadCategories() {
    if (!$apCategory) return;
    $apCategory.innerHTML = '<option value="">Loading…</option>';
    try {
      const res = await fetch(CATS_API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ wise: 'simple', "limit": 100 })
      });
      const json = await res.json();

      // Try common shapes:
      // A) { success, data: { categories: [{id,name}, ...] } }
      // B) { success, categories: [...] }
      // C) plain array [...]
      const list =
        json?.data?.categories ??
        json?.categories ??
        (Array.isArray(json) ? json : []);

      $apCategory.innerHTML = '<option value="">Select category…</option>';

      list.forEach(c => {
        // Accept several forms: {id,name} or {category_id, category_name}
        const id   = c.id ?? c.category_id ?? c.value ?? '';
        const name = c.name ?? c.category_name ?? c.label ?? String(id);
        if (!id) return;
        const opt = document.createElement('option');
        opt.value = id;            // numeric/string ID
        opt.textContent = name;    // display name
        $apCategory.appendChild(opt);
      });

      if ($apCategory.options.length === 1) {
        $apCategory.innerHTML = '<option value="">No categories found</option>';
      }
    } catch (e) {
      console.error(e);
      $apCategory.innerHTML = '<option value="">Failed to load</option>';
    }
  }

  // (3) Brands -> POST { limit: 100 }
  async function loadBrands() {
    if (!$apBrand) return;
    $apBrand.innerHTML = '<option value="">Loading…</option>';
    try {
      const res = await fetch(BRANDS_API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ limit: 100 })
      });
      const json = await res.json();

      // Try common shapes:
      // A) { success, data: { brands: [{id,name}, ...] } }
      // B) { success, brands: [...] }
      // C) plain array [...]
      const list =
        json?.data?.brands ??
        json?.brands ??
        (Array.isArray(json) ? json : []);

      $apBrand.innerHTML = '<option value="">Select brand…</option>';

      list.forEach(b => {
        const id   = b.id ?? b.brand_id ?? b.value ?? '';
        const name = b.name ?? b.brand_name ?? b.label ?? String(id);
        if (!id) return;
        const opt = document.createElement('option');
        opt.value = id;
        opt.textContent = name;
        $apBrand.appendChild(opt);
      });

      if ($apBrand.options.length === 1) {
        $apBrand.innerHTML = '<option value="">No brands found</option>';
      }
    } catch (e) {
      console.error(e);
      $apBrand.innerHTML = '<option value="">Failed to load</option>';
    }
  }

  /* ======= Wire modal open/close ======= */
  document.getElementById('btnAdd')?.addEventListener('click', openAddModal);
  $modalAdd?.addEventListener('click', (e) => {
    if (e.target.matches('[data-close-modal]')) closeAddModal();
  });

  // feature add/remove
  document.getElementById('btnAddFeature')?.addEventListener('click', () => addFeatureRow());
  $featureList?.addEventListener('click', (e) => {
    if (e.target.classList.contains('ap-feature-del')) {
      e.target.closest('.grid')?.remove();
    }
  });

</script>
<script>


  const $apUploads = document.getElementById('ap_uploads');

  // Helper: actually upload the selected files
  async function uploadProductImages(productId, files, token) {
    if (!files || !files.length) return { success: true, message: 'No images to upload' };

    const fd = new FormData();
    fd.append('token', token);
    fd.append('product_id', String(productId));
    // append files as uploads[]
    [...files].forEach(file => fd.append('uploads[]', file, file.name));

    const res = await fetch(UPLOAD_IMG_API_URL, {
      method: 'POST',
      body: fd
      // no Content-Type header → browser sets multipart boundary
    });

    let json = {};
    try { json = await res.json(); } catch (_) {}
    if (!res.ok || json.success === false) {
      throw new Error(json.message || `Image upload failed (HTTP ${res.status})`);
    }
    return json;
  }

  let creatingProduct = false; // <-- put this near your upload script top
  // Replace your success block with this enhanced version
  $formAdd?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (creatingProduct) return;   // guard
    creatingProduct = true;
    $apMsg.textContent = '';

    const fd = new FormData($formAdd);
    const token = (fd.get('token') || '').toString().trim();

    const payload = {
      token,
      name: (fd.get('name') || '').toString().trim(),
      price: Number(fd.get('price') || 0),
      unit: (fd.get('unit') || '').toString().trim(),
      category_id: fd.get('category_id') ? Number(fd.get('category_id')) : null,
      brand_id: fd.get('brand_id') ? Number(fd.get('brand_id')) : null,
      features: readFeaturesIntoObject(),
      description: (fd.get('description') || '').toString(),
      short_description: (fd.get('short_description') || '').toString()
    };

    // quick checks
    if (!payload.token) { $apMsg.textContent = 'Missing token.'; return; }
    if (!payload.name) { $apMsg.textContent = 'Name is required.'; return; }
    if (!payload.price || payload.price <= 0) { $apMsg.textContent = 'Price must be > 0.'; return; }
    if (!payload.unit) { $apMsg.textContent = 'Unit is required.'; return; }
    if (!payload.category_id) { $apMsg.textContent = 'Category is required.'; return; }

    const $btn = document.getElementById('btnSubmitAdd');
    const prev = $btn.innerHTML;
    $btn.disabled = true; $btn.innerHTML = 'Saving…';

    try {
      // 1) Create product
      const res = await fetch(CREATE_API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json().catch(() => ({}));
      if (!res.ok || !json.success) throw new Error(json.message || `HTTP ${res.status}`);

      const productId = json?.data?.product?.id;
      if (!productId) throw new Error('Product created but no ID returned.');

      // 2) If there are images selected, upload them
      const files = $apUploads?.files ?? [];
      if (files.length) {
        document.getElementById('serverMessage').textContent = 'Uploading images…';
        await uploadProductImages(productId, files, token);
      }

      // 3) All done → close, reset list
      closeAddModal();
      document.getElementById('serverMessage').textContent = json.message || 'Product created';
      state.offset = 0;
      fetchProducts();

      // Optional: clear file input for next time
      if ($apUploads) $apUploads.value = '';
    } catch (err) {
      console.error(err);
      $apMsg.textContent = err.message || 'Failed to create product.';
    } finally {
      $btn.disabled = false; 
      $btn.innerHTML = prev;
      creatingProduct = false;     // release guard
    }
  });
</script>
