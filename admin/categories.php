<?php include("header.php") ?>

<!-- Content -->
<main class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Filters -->
  <section class="bg-white rounded-xl shadow p-4">
    <div class="flex flex-col md:flex-row gap-3 md:items-center">
      <div class="flex gap-2">
        <button id="btnAdd" class="px-4 py-2 rounded-lg bg-brand text-white hover:bg-brand-700" onclick="openAddCategoryPopup()">Add Category</button>
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

  <!-- Categories Table -->
  <section class="bg-white rounded-xl shadow overflow-hidden">
    <div class="px-4 py-3 border-b flex items-center justify-between text-sm">
      <div id="meta">Showing 0–0 of 0</div>
      <div id="status" class="text-gray-500"></div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left text-gray-600">
            <th class="px-4 py-3 font-medium">Category</th>
            <th class="px-4 py-3 font-medium">Image</th>
            <th class="px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody id="tbodyCategories" class="divide-y">
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

<!-- Add Category Popup (Hidden by default) -->
<div id="addCategoryPopup" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 hidden flex justify-center items-center">
  <div class="bg-white p-6 rounded-lg w-full max-w-lg sm:max-w-xl mx-4">
    <h3 class="text-xl font-semibold mb-4">Add New Category</h3>
    <form id="addCategoryForm" enctype="multipart/form-data">
      <div class="mb-4">
        <label for="categoryName" class="block text-sm font-medium text-gray-700">Category Name</label>
        <input type="text" id="categoryName" name="name" class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/30" required />
      </div>
      <div class="mb-4">
        <label for="categoryImage" class="block text-sm font-medium text-gray-700">Category Image</label>
        <input type="file" id="categoryImage" name="category_image" class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/30" />
      </div>
      <!-- Sort No -->
      <div class="mb-4">
        <label for="categorySortNo" class="block text-sm font-medium text-gray-700">Sort No</label>
        <input type="number" id="categorySortNo" name="sort_no"
              class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/30"
              value="0" />
      </div>

      <!-- Parent Category -->
      <div class="mb-4">
        <label for="parentCategory" class="block text-sm font-medium text-gray-700">Parent Category (optional)</label>
        <select id="parentCategory" name="parent_id"
                class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/30">
          <option value="">— None —</option>
          <!-- options will be injected via JS -->
        </select>
      </div>

      <div class="flex justify-end gap-3">
        <button type="button" class="px-4 py-2 rounded-lg bg-gray-300 text-white" onclick="closeAddCategoryPopup()">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-brand text-white hover:bg-brand-700">Save Category</button>
      </div>
    </form>
  </div>
</div>

</div>
</div>

<script>
  // Debounce function to limit the rate of function execution
  function debounce(fn, ms = 350) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), ms);
    };
  }

  /* ======= CONFIG ======= */
  const EBASE_URL = '<?php echo BASE_URL; ?>';
  const FETCH_API_URL = EBASE_URL + '/categories/fetch.php';  // Fetch Categories
  const CREATE_API_URL = EBASE_URL + '/categories/create.php'; // Create Category
  const UPDATE_API_URL = EBASE_URL + '/categories/update.php';
  const DELETE_API_URL = EBASE_URL + '/categories/delete.php';

  /* ======= STATE ======= */
  const state = {
    name: '',          // search
    limit: 100,        // limit set to 100
    offset: 0,
    count: 0,
    loading: false
  };

  /* ======= DOM ======= */
  const $tbody   = document.getElementById('tbodyCategories');
  const $meta    = document.getElementById('meta');
  const $status  = document.getElementById('status');
  const $msg     = document.getElementById('serverMessage');
  const $prev    = document.getElementById('btnPrev');
  const $next    = document.getElementById('btnNext');
  const $search  = document.getElementById('searchName');
  const $limit   = document.getElementById('limit');
  
  const $addCategoryPopup = document.getElementById('addCategoryPopup');
  const $addCategoryForm  = document.getElementById('addCategoryForm');
  const $categoryName     = document.getElementById('categoryName');
  const $categoryImage    = document.getElementById('categoryImage');

  /* ======= HELPERS ======= */
  const imgOr = (a, b) => a || b || '';
  const escapeHtml = s => String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));

  function setLoading(v) {
    state.loading = v;
    $status.textContent = v ? 'Loading…' : '';
    [ $prev, $next ].forEach(b => v ? b.classList.add('btn-disabled') : b.classList.remove('btn-disabled'));
  }

  // Render categories rows with action buttons
  function renderRows(categories) {
    $tbody.innerHTML = '';
    if (!categories || !categories.length) {
      $tbody.innerHTML = `<tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No categories found</td></tr>`;
      return;
    }

    const frag = document.createDocumentFragment();
    categories.forEach(c => {
      // Replace '../' with the base URL in category image path
      const image = c.category_image_path ? c.category_image_path.replace('../', '<?php echo BASE_URL; ?>/') : '';

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="px-4 py-3">${escapeHtml(c.name || '')}</td>
        <td class="px-4 py-3">
          ${image ? `<img src="${image}" alt="${c.name}" class="w-16 h-16 object-cover rounded bg-gray-100">` : '—'}
        </td>
        <td class="px-4 py-3">
          <!-- Action Buttons (Delete, Update, Others) -->
          <div class="flex gap-2">
            <!-- Delete Button -->
            <button class="text-red-600 hover:text-red-800 btn-delete" title="Delete"
              data-id="${c.id}" data-name="${escapeHtml(c.name || '')}"
            >
              Delete
            </button>
            <!-- Update Button -->
            <button class="text-blue-600 hover:text-blue-800" title="Update"
              onclick="openUpdatePopup(${c.id}, '${escapeHtml(c.name || '').replace(/'/g, "\\'")}', ${c.sort_no ?? 0}, '${(c.category_image_path || '').replace(/'/g, "\\'")}', ${c.parent_id ?? 'null'})">
              Update
            </button>

            <!-- Other Button -->
            <button class="text-green-600 hover:text-green-800" title="Other Actions">
              Other
            </button>
          </div>
        </td>
      `;
      frag.appendChild(tr);
    });
    $tbody.appendChild(frag);
  }

  // Build a safe absolute image URL from path returned by API
  function buildImageUrl(category_image_path) {
    if (!category_image_path) return '';
    // handle "../" style paths coming from backend
    const cleaned = category_image_path.replace(/^(\.\.\/)+/, '');
    return EBASE_URL.replace(/\/+$/, '') + '/' + cleaned.replace(/^\/+/, '');
  }

  function openUpdatePopup(id, name, sort_no, category_image_path, parent_id = null) {
    const currentImgUrl = buildImageUrl(category_image_path);

    Swal.fire({
      title: 'Update Category',
      html: `
        <style>
          .upd-grid{display:grid;gap:12px;grid-template-columns:1fr 1fr}
          @media(max-width:520px){.upd-grid{grid-template-columns:1fr}}
          .upd-field label{display:block;font-size:12px;color:#6b7280;margin-bottom:6px}
          .upd-field input[type="text"],
          .upd-field input[type="number"],
          .upd-field input[type="file"],
          .upd-field select{
            width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px
          }
          .upd-img-wrap{display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap}
          .upd-thumb{width:72px;height:72px;border-radius:8px;object-fit:cover;background:#f3f4f6;border:1px solid #e5e7eb}
          .upd-note{font-size:12px;color:#6b7280}
        </style>

        <div class="upd-grid">
          <div class="upd-field">
            <label>Category ID</label>
            <input type="text" id="upd_category_id" value="${String(id ?? '').replace(/"/g,'&quot;')}" readonly>
          </div>

          <div class="upd-field">
            <label>Sort No <span class="upd-note">(optional)</span></label>
            <input type="number" id="upd_sort_id" placeholder="e.g. 10" value="${Number(sort_no ?? 0)}">
          </div>

          <!-- Category Name (now a select of all categories) -->
          <div class="upd-field">
            <label>Category Name</label>
            <select id="upd_name_sel">
              <option value="">Loading…</option>
            </select>
          </div>

          <!-- NEW: Parent Category select -->
          <div class="upd-field">
            <label>Parent Category <span class="upd-note">(optional)</span></label>
            <select id="upd_parent_sel">
              <option value="0">— None —</option>
            </select>
          </div>

          <div class="upd-field" style="grid-column:1/-1">
            <label>Category Image <span class="upd-note">(optional)</span></label>
            <div class="upd-img-wrap">
              <input type="file" id="upd_image" accept="image/*">
              <img id="upd_preview" class="upd-thumb" src="${category_image_path || ''}" alt="Preview">
            </div>
            <div class="upd-note" style="margin-top:6px">
              If you don’t choose a new image, the current one (if any) remains unchanged.
            </div>
          </div>
        </div>
      `,
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: 'Update',
      cancelButtonText: 'Cancel',
      didOpen: async () => {
        // 1) Setup live image preview
        const $file = document.getElementById('upd_image');
        const $prev = document.getElementById('upd_preview');
        $file.addEventListener('change', () => {
          const f = $file.files && $file.files[0];
          if (f) {
            const reader = new FileReader();
            reader.onload = e => { $prev.src = e.target.result; };
            reader.readAsDataURL(f);
          } else {
            $prev.src = currentImgUrl || '';
          }
        });

        // 2) Populate both selects from API
        try {
          const allCats = await fetchAllCategoriesList();

          // (a) Fill NAME select (list of all categories)
          const nameSel = document.getElementById('upd_name_sel');
          nameSel.innerHTML = '';
          allCats.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = String(cat.name || '');
            opt.textContent = cat.name || `#${cat.id}`;
            nameSel.appendChild(opt);
          });

          // Preselect current name if present in list; otherwise add it
          if (name && !Array.from(nameSel.options).some(o => o.value === name)) {
            const opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            nameSel.prepend(opt);
          }
          if (name) nameSel.value = name;

          // (b) Fill PARENT select
          const parentSel = document.getElementById('upd_parent_sel');
          // keep the "— None —"
          allCats.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = String(cat.id);
            opt.textContent = cat.name || `#${cat.id}`;
            parentSel.appendChild(opt);
          });

          // If we got parent_id from the row (optional), preselect it
          if (parent_id) parentSel.value = String(parent_id);
        } catch (e) {
          console.error('Failed to fill update selects:', e);
          // leave the fallback options
        }
      },
      preConfirm: () => {
        const category_id = document.getElementById('upd_category_id').value.trim();
        const sort_id_raw = document.getElementById('upd_sort_id').value;
        const sort_id = sort_id_raw === '' ? null : Number(sort_id_raw);

        const nameSel = document.getElementById('upd_name_sel');
        const nameSelected = (nameSel && nameSel.value) ? nameSel.value.trim() : '';

        const parentSel = document.getElementById('upd_parent_sel');
        const parentSelected = (parentSel && parentSel.value) ? parentSel.value : '';

        const imageFile = document.getElementById('upd_image').files[0] || null;

        if (!category_id) {
          Swal.showValidationMessage('Missing category_id.');
          return false;
        }
        if (!nameSelected) {
          Swal.showValidationMessage('Please select a Category Name.');
          return false;
        }
        return {
          category_id,
          name: nameSelected,
          sort_id,
          parent_id: parentSelected || null,
          imageFile
        };
      }
    }).then(async (result) => {
      if (!result.isConfirmed) return;

      try {
        const { category_id, name, sort_id, parent_id, imageFile } = result.value;
        const token = localStorage.getItem('user_token');
        if (!token) {
          Swal.fire('Error', 'Authentication token not found. Please login again.', 'error');
          return;
        }

        const fd = new FormData();
        fd.append('token', token);
        fd.append('category_id', category_id);
        if (name) fd.append('name', name);
        if (sort_id !== null && !Number.isNaN(sort_id)) fd.append('sort_no', String(sort_id));
        if (parent_id) fd.append('parent_id', parent_id);
        if (imageFile) fd.append('category_image', imageFile);

        const res = await fetch(UPDATE_API_URL, { method: 'POST', body: fd });
        const json = await res.json().catch(() => ({}));

        if (!res.ok) throw new Error('HTTP ' + res.status);
        if (json && json.success) {
          Swal.fire('Updated', json.message || 'Category updated successfully.', 'success');
          fetchCategories();
        } else {
          throw new Error(json && json.message ? json.message : 'Update failed.');
        }
      } catch (err) {
        console.error(err);
        Swal.fire('Error', err.message || 'Failed to update category.', 'error');
      }
    });
  }

  function updateMeta() {
    const start = state.count ? state.offset + 1 : 0;
    const end = Math.min(state.offset + state.limit, state.count);
    $meta.textContent = `Showing ${start}–${end} of ${state.count}`;
    $prev.classList.toggle('btn-disabled', state.offset <= 0);
    $next.classList.toggle('btn-disabled', state.offset + state.limit >= state.count);
  }

  async function confirmAndDelete(category_id, name) {
    const token = localStorage.getItem('user_token');
    if (!token) {
      Swal.fire('Error', 'Authentication token not found. Please login again.', 'error');
      return;
    }

    Swal.fire({
      title: `Delete “${name}”?`,
      text: 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Delete',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#dc2626',
      showLoaderOnConfirm: true,
      allowOutsideClick: () => !Swal.isLoading(),
      preConfirm: async () => {
        try {
          const res = await fetch(DELETE_API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              token: token,            // mandatory
              category_id: Number(category_id) // mandatory
            })
          });

          const json = await res.json().catch(() => ({}));
          if (!res.ok) throw new Error('HTTP ' + res.status);
          if (!json.success) throw new Error(json.message || 'Delete failed.');
          return json;
        } catch (err) {
          Swal.showValidationMessage(err.message || 'Failed to delete.');
          return false;
        }
      }
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire('Deleted', (result.value && result.value.message) || 'Category deleted.', 'success');
        // Refresh the list
        fetchCategories();
      }
    });
  }


  /* ======= API ======= */
  async function fetchCategories() {
    setLoading(true);
    try {
      const body = {
        name: state.name || "",
        limit: state.limit,
        offset: state.offset,
        wise: "simple"
      };
      const res = await fetch(FETCH_API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const json = await res.json();

      $msg.textContent = json.message || '';
      if (!json.success) throw new Error(json.message || 'Request failed');

      const data = json.data || { count: 0, categories: [] };
      state.count = Number(data.count) || 0;

      renderRows(data.categories || []);
      updateMeta();
    } catch (err) {
      console.error(err);
      $msg.textContent = 'Failed to fetch categories';
      $tbody.innerHTML = `<tr><td colspan="3" class="px-4 py-6 text-center text-red-600">Error loading categories</td></tr>`;
      state.count = 0;
      updateMeta();
    } finally {
      setLoading(false);
    }
  }

  // Open the add category popup
  function openAddCategoryPopup() {
    // reset fields
    $addCategoryForm.reset();
    $categorySortNo.value = 0;         // default
    $parentCategory.innerHTML = '<option value="">— None —</option>';

    // load parent categories (async but fast)
    loadParentCategoriesForSelect();

    // show popup
    $addCategoryPopup.classList.remove('hidden');
  }

  // Close the add category popup
  function closeAddCategoryPopup() {
    $addCategoryPopup.classList.add('hidden');
  }

  // Handle category form submission
  $addCategoryForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const token = localStorage.getItem('user_token');
    const formData = new FormData();
    formData.append('name', $categoryName.value);
    formData.append('category_image', $categoryImage.files[0]);
    // formData.append('sort_no', 0);  // default sort_no
    // sort_no from the field
    const sortNoVal = $categorySortNo.value;
    if (sortNoVal !== '' && !Number.isNaN(Number(sortNoVal))) {
      formData.append('sort_no', String(Number(sortNoVal)));
    }

    // parent category (only if selected)
    const parentVal = $parentCategory.value;
    if (parentVal) {
      // change 'parent_id' to whatever your backend expects
      formData.append('parent_id', parentVal);
    }
    formData.append('token', token); // Include the token in the form data

    try {
      const res = await fetch(CREATE_API_URL, {
        method: 'POST',
        body: formData
      });

      const json = await res.json();

      if (json.success) {
        alert('Category created successfully!');
        closeAddCategoryPopup();
        fetchCategories(); // Re-fetch the categories list to update the table
      } else {
        alert('Error: ' + json.message);
      }
    } catch (err) {
      console.error(err);
      alert('Failed to create category');
    }
  });

  /* ======= EVENTS ======= */
  $search.addEventListener('input', debounce(e => {
    state.name = e.target.value.trim();
    state.offset = 0;
    fetchCategories();
  }));

  $limit.addEventListener('change', e => {
    state.limit = Number(e.target.value) || 100;
    state.offset = 0;
    fetchCategories();
  });

  $prev.addEventListener('click', () => {
    if (state.offset <= 0) return;
    state.offset = Math.max(0, state.offset - state.limit);
    fetchCategories();
  });

  $next.addEventListener('click', () => {
    if (state.offset + state.limit >= state.count) return;
    state.offset += state.limit;
    fetchCategories();
  });

  // 👇 ADD THIS RIGHT HERE
  document.addEventListener('click', (e) => {
    const delBtn = e.target.closest('.btn-delete');
    if (!delBtn) return;

    const categoryId = delBtn.dataset.id;
    const categoryName = delBtn.dataset.name || 'this category';
    confirmAndDelete(categoryId, categoryName);
  });
  
  (function init(){
    $search.value = '';
    fetchCategories();
  })();
</script>
  
<script>
  const $categorySortNo  = document.getElementById('categorySortNo');
  const $parentCategory  = document.getElementById('parentCategory');

  async function loadParentCategoriesForSelect() {
    try {
      const res = await fetch(FETCH_API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: "",
          limit: 1000,   // big enough to cover all
          offset: 0
        })
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const json = await res.json();
      if (!json.success) throw new Error(json.message || 'Failed to load categories');

      const list = (json.data && json.data.categories) ? json.data.categories : [];
      // reset options (keep the "— None —")
      $parentCategory.innerHTML = '<option value="">— None —</option>';
      const frag = document.createDocumentFragment();

      list.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.id;                  // assumes backend uses id
        opt.textContent = c.name || `#${c.id}`;
        frag.appendChild(opt);
      });

      $parentCategory.appendChild(frag);
    } catch (err) {
      console.error('Parent category load failed:', err);
      // Still keep the "None" option
    }
  } 
</script>
<script>
  async function fetchAllCategoriesList() {
    const res = await fetch(FETCH_API_URL, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ name: "", limit: 1000, offset: 0 })
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Failed to load categories');
    return (json.data && json.data.categories) ? json.data.categories : [];
  }
</script>

<?php include("footer.php"); ?>

