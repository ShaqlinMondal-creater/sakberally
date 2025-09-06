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
  const BASE_URL = 'https://sakberally.com/apis';
  const FETCH_API_URL = BASE_URL + '/categories/fetch.php';  // Fetch Categories
  const CREATE_API_URL = BASE_URL + '/categories/create.php'; // Create Category

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

  // function renderRows(categories) {
  //   $tbody.innerHTML = '';
  //   if (!categories || !categories.length) {
  //     $tbody.innerHTML = `<tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No categories found</td></tr>`;
  //     return;
  //   }

  //   const frag = document.createDocumentFragment();
  //   categories.forEach(c => {
  //     // Replace '../' with the base URL in category image path
  //     const image = c.category_image_path ? c.category_image_path.replace('../', 'https://sakberally.com/apis/') : '';

  //     const tr = document.createElement('tr');
  //     tr.innerHTML = `
  //       <td class="px-4 py-3">${escapeHtml(c.name || '')}</td>
  //       <td class="px-4 py-3">
  //         ${image ? `<img src="${image}" alt="${c.name}" class="w-16 h-16 object-cover rounded bg-gray-100">` : '—'}
  //       </td>
  //       <td class="px-4 py-3">
  //         <!-- Action Buttons (Delete, Update, Others) -->
  //         <div class="flex gap-2">
  //           <!-- Delete Button -->
  //           <button class="text-red-600 hover:text-red-800" title="Delete">
  //             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  //               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M6 18L18 6"/>
  //             </svg>
  //           </button>
  //           <!-- Update Button -->
  //           <button class="text-blue-600 hover:text-blue-800" title="Update">
  //             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  //               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 3h4v4m0 0L7 17l-4 4m16-6l-3 3m0 0L5 7"/>
  //             </svg>
  //           </button>
  //           <!-- Others Button -->
  //           <button class="text-green-600 hover:text-green-800" title="Other Actions">
  //             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  //               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 0v4m0-4h4m-4 0h-4"/>
  //             </svg>
  //           </button>
  //         </div>
  //       </td>
  //     `;
  //     frag.appendChild(tr);
  //   });
  //   $tbody.appendChild(frag);
  // }

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
    const image = c.category_image_path ? c.category_image_path.replace('../', 'https://sakberally.com/apis/') : '';

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
          <button class="text-red-600 hover:text-red-800" title="Delete">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M6 18L18 6"/>
            </svg>
          </button>
          <!-- Update Button -->
          <button class="text-blue-600 hover:text-blue-800" title="Update" onclick="openUpdatePopup(${c.id}, '${c.name}', ${c.sort_no}, '${c.category_image_path}')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 3h4v4m0 0L7 17l-4 4m16-6l-3 3m0 0L5 7"/>
            </svg>
          </button>
          <!-- Other Button -->
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

// Open SweetAlert popup for updating category
function openUpdatePopup(id, name, sort_no, category_image_path) {
  Swal.fire({
    title: 'Update Category',
    html: `
      <input type="text" id="categoryId" class="swal2-input" value="${id}" readonly />
      <input type="text" id="categoryName" class="swal2-input" value="${name}" />
      <input type="number" id="categorySortId" class="swal2-input" value="${sort_no}" />
      <input type="file" id="categoryImage" class="swal2-input" accept="image/*" />
    `,
    focusConfirm: false,
    preConfirm: () => {
      const categoryName = document.getElementById('categoryName').value;
      const categorySortId = document.getElementById('categorySortId').value;
      const categoryImage = document.getElementById('categoryImage').files[0];
      const categoryId = document.getElementById('categoryId').value;

      if (!categoryName || !categorySortId) {
        Swal.showValidationMessage('Please enter category name and sort id');
      } else {
        return {
          id: categoryId,
          name: categoryName,
          sort_id: categorySortId,
          category_image: categoryImage
        };
      }
    }
  }).then(async (result) => {
    if (result.isConfirmed) {
      const formData = new FormData();
      formData.append('id', result.value.id);
      formData.append('name', result.value.name);
      formData.append('sort_no', result.value.sort_id);
      if (result.value.category_image) {
        formData.append('category_image', result.value.category_image);
      }
      formData.append('token', localStorage.getItem('user_token')); // Pass token

      try {
        const response = await fetch('https://sakberally.com/apis/categories/update.php', {
          method: 'POST',
          body: formData
        });

        const json = await response.json();

        if (json.success) {
          Swal.fire('Success!', json.message, 'success');
          fetchCategories(); // Re-fetch the categories after update
        } else {
          Swal.fire('Error!', json.message, 'error');
        }
      } catch (error) {
        Swal.fire('Error!', 'Failed to update category', 'error');
      }
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

  /* ======= INIT ======= */
  (function init(){
    $search.value = '';
    fetchCategories();
  })();
</script>


</body>
</html>
