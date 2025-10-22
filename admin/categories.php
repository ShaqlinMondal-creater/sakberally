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
<div id="addCategoryPopup" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 hidden justify-center items-center">
  <div class="bg-white p-6 rounded-lg w-1/3">
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
              <button class="text-blue-600 hover:text-blue-800" title="Update" onclick="openUpdatePopup(${c.id}, '${c.name}', ${c.sort_no}, '${c.category_image_path}')">
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

    function openUpdatePopup(id, name, sort_no, category_image_path) {
      const currentImgUrl = buildImageUrl(category_image_path);

      Swal.fire({
        title: 'Update Category',
        // Responsive 2-col grid on wide screens, 1-col on small screens
        html: `
          <style>
            .upd-grid{display:grid;gap:12px;grid-template-columns:1fr 1fr}
            @media(max-width:520px){.upd-grid{grid-template-columns:1fr}}
            .upd-field label{display:block;font-size:12px;color:#6b7280;margin-bottom:6px}
            .upd-field input[type="text"],
            .upd-field input[type="number"],
            .upd-field input[type="file"]{
              width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px
            }
            .upd-img-wrap{
              display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap
            }
            .upd-thumb{
              width:72px;height:72px;border-radius:8px;object-fit:cover;background:#f3f4f6;border:1px solid #e5e7eb
            }
            .upd-note{font-size:12px;color:#6b7280}
          </style>

          <div class="upd-grid">
            <div class="upd-field">
              <label>Category ID</label>
              <input type="text" id="upd_category_id" value="${String(id ?? '').replace(/"/g,'&quot;')}" readonly>
            </div>
            <div class="upd-field">
              <label>Sort ID <span class="upd-note">(optional)</span></label>
              <input type="number" id="upd_sort_id" placeholder="e.g. 10" value="${Number(sort_no ?? 0)}">
            </div>

            <div class="upd-field" style="grid-column:1/-1">
              <label>Category Name <span class="upd-note">(optional)</span></label>
              <input type="text" id="upd_name" placeholder="Enter new name"
                    value="${String(name ?? '').replace(/"/g,'&quot;')}">
            </div>

            <div class="upd-field" style="grid-column:1/-1">
              <label>Category Image <span class="upd-note">(optional)</span></label>
              <div class="upd-img-wrap">
                <input type="file" id="upd_image" accept="image/*">
                <img id="upd_preview" class="upd-thumb" src="${currentImgUrl || ''}" alt="Preview">
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
        didOpen: () => {
          // Live preview when user picks a new file
          const $file = document.getElementById('upd_image');
          const $prev = document.getElementById('upd_preview');
          $file.addEventListener('change', () => {
            const f = $file.files && $file.files[0];
            if (f) {
              const reader = new FileReader();
              reader.onload = e => { $prev.src = e.target.result; };
              reader.readAsDataURL(f);
            } else {
              // reset to original
              $prev.src = currentImgUrl || '';
            }
          });
        },
        preConfirm: () => {
          const category_id = document.getElementById('upd_category_id').value.trim();
          const name        = document.getElementById('upd_name').value.trim();
          const sort_id_raw = document.getElementById('upd_sort_id').value;
          const sort_id     = sort_id_raw === '' ? null : Number(sort_id_raw);
          const imageFile   = document.getElementById('upd_image').files[0] || null;

          if (!category_id) {
            Swal.showValidationMessage('Missing category_id.');
            return false;
          }

          // Prepare exactly what we’ll send; we’ll still build FormData after confirm
          return { category_id, name, sort_id, imageFile };
        }
      }).then(async (result) => {
        if (!result.isConfirmed) return;

        try {
          const { category_id, name, sort_id, imageFile } = result.value;
          const token = localStorage.getItem('user_token');

          if (!token) {
            Swal.fire('Error', 'Authentication token not found. Please login again.', 'error');
            return;
          }

          // Build FormData as required by your API
          const fd = new FormData();
          fd.append('token', token);                 // mandatory
          fd.append('category_id', category_id);     // mandatory

          // Append optional fields ONLY if user provided them
          if (name)    fd.append('name', name);      // optional
          if (sort_id !== null && !Number.isNaN(sort_id)) fd.append('sort_no', String(sort_id));
          if (imageFile) fd.append('category_image', imageFile); // optional

          const res = await fetch(UPDATE_API_URL, { method: 'POST', body: fd });
          const json = await res.json().catch(() => ({}));

          if (!res.ok) {
            throw new Error('HTTP ' + res.status);
          }

          if (json && json.success) {
            Swal.fire('Updated', json.message || 'Category updated successfully.', 'success');
            fetchCategories(); // refresh table
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
          offset: state.offset
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
      formData.append('sort_no', 0);  // default sort_no
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
<?php include("footer.php"); ?>

