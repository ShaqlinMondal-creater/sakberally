<!-- Categories (round icons) -->
<section id="home-categories" class="py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div id="catGrid"
         class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-8 place-items-center">
      <!-- loading state -->
      <div id="catLoading" class="col-span-full text-center text-gray-600">Loading categories…</div>
    </div>
  </div>
</section>

<script>
  // Set this once on your page (or replace with your actual base URL)
  window.BASE_URL = "<?php echo BASE_URL; ?>";

  // Local placeholder for missing category images
  const CATEGORY_PLACEHOLDER = 'assets/images/placeholder-product.png';

  // Resolve category image path with safe fallbacks
  function getCategoryImage(cat) {
    const src = cat?.category_image_path || '';
    if (!src) return CATEGORY_PLACEHOLDER;

    // Already absolute or data URI?
    if (/^(https?:)?\/\//i.test(src) || /^data:/i.test(src)) return src;

    // Relative path → join with BASE_URL
    const BASE = window.BASE_URL + '/';
    const cleaned = src.replace(/^(\.\.\/)+|^\.\/+/, '');
    return BASE.replace(/\/+$/, '') + '/' + cleaned.replace(/^\/+/, '');
  }

  // Optional: truncate long category names
  function truncate(text = '', max = 26) {
    return text.length > max ? text.slice(0, max) + '…' : text;
  }

  async function loadCategories() {
    const grid = document.getElementById('catGrid');
    const loading = document.getElementById('catLoading');

    try {
      const res = await fetch(`${window.BASE_URL}/categories/fetch.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        // ✅ Required body
        body: JSON.stringify({ wise: 'category', limit: 100, offset: 0 })
      });

      const json = await res.json();
      const categories = Array.isArray(json?.data?.categories) ? json.data.categories : [];

      // Clear loading
      loading?.remove();

      if (!categories.length) {
        grid.innerHTML = `<p class="col-span-full text-center text-gray-600">No categories found.</p>`;
        return;
      }

      // ✅ Sort by sort_no ASC (null/undefined go last)
      categories.sort((a, b) => {
        const as = (a?.sort_no ?? Number.POSITIVE_INFINITY);
        const bs = (b?.sort_no ?? Number.POSITIVE_INFINITY);
        return as - bs;
      });

      // Render
      const frag = document.createDocumentFragment();
      categories.forEach(cat => {
        const name = (cat?.name || '').trim();
        const img  = getCategoryImage(cat);

        const a = document.createElement('a');
        a.href = `products?category=${encodeURIComponent(name)}`;
        a.className = 'group flex flex-col items-center text-center select-none';
        a.setAttribute('aria-label', name);

        a.innerHTML = `
          <span class="relative inline-flex items-center justify-center">
            <img src="${img}" alt="${name}"
                 class="w-32 h-32 sm:w-52 sm:h-52 rounded-full object-cover border-[6px] border-gray-300 shadow-sm
                        transition duration-200 group-hover:scale-105"
                 onerror="this.onerror=null; this.src='${CATEGORY_PLACEHOLDER}'">
          </span>
          <span class="mt-4 font-serif text-base sm:text-sm tracking-wide uppercase text-gray-700
                       transition group-hover:text-red-600">
            ${truncate(name, 26)}
          </span>
        `;
        frag.appendChild(a);
      });

      grid.appendChild(frag);
    } catch (e) {
      loading?.remove();
      grid.innerHTML = `<p class="col-span-full text-center text-red-600">Failed to load categories.</p>`;
      console.error('Categories fetch error:', e);
    }
  }

  document.addEventListener('DOMContentLoaded', loadCategories);
</script>

