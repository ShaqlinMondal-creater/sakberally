<!-- Categories (round icons) -->
<section id="home-categories" class="py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div id="catGrid"
         class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-8 place-items-center">
      <!-- loading state -->
      <div id="catLoading" class="col-span-full text-center text-gray-600">Loading categories…</div>
    </div>
  </div>
</section>
<script>
  // Set this once on your page (or replace with your actual base URL)
  window.BASE_URL = "<?php echo BASE_URL; ?>"; 

  async function loadCategories() {
    const grid = document.getElementById('catGrid');
    const loading = document.getElementById('catLoading');
    try {
      const res = await fetch(`${window.BASE_URL}/categories/fetch.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        // Pass limit/offset if you want. You can remove body entirely too.
        body: JSON.stringify({ limit: 50, offset: 0 })
      });

      const json = await res.json();
      const categories = json?.data?.categories || [];

      // Clear loading
      loading?.remove();

      if (!categories.length) {
        grid.innerHTML = `<p class="col-span-full text-center text-gray-600">No categories found.</p>`;
        return;
      }

      categories.forEach(cat => {
        const name = (cat.name || '').trim();
        const img  = cat.category_image_path
        // (cat.category_image_path && cat.category_image_path.trim())
        //               ? cat.category_image_path.trim()
        //               : 'assets/images/category_placeholder.jpg'; // add this file

        const a = document.createElement('a');
        a.href = `products?category=${encodeURIComponent(name)}`;
        a.className = 'group flex flex-col items-center text-center select-none';

        a.innerHTML = `
          <span class="relative inline-flex items-center justify-center">
            <img src="${img}" alt="${name}"
                 class="w-32 h-32 sm:w-36 sm:h-36 rounded-full object-cover border-[6px] border-gray-300 shadow-sm
                        transition duration-200 group-hover:scale-105"
                 onerror="this.onerror=null;this.src='assets/images/category_placeholder.jpg'">
          </span>
          <span class="mt-4 font-serif text-base sm:text-sm tracking-wide uppercase text-gray-700
                       transition group-hover:text-red-600">
            ${name}
          </span>
        `;
        grid.appendChild(a);
      });
    } catch (e) {
      loading?.remove();
      grid.innerHTML = `<p class="col-span-full text-center text-red-600">Failed to load categories.</p>`;
      console.error('Categories fetch error:', e);
    }
  }

  document.addEventListener('DOMContentLoaded', loadCategories);
</script>
