<!-- RANDOM PRODUCTS SECTION -->
<section id="random-products" class="py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Title -->
    <div class="relative flex justify-center mb-8 md:mb-12">
      <img src="assets/images/35RW72DCDJ-20180613-150311.png" alt=""
           aria-hidden="true"
           class="hidden md:block w-16 lg:w-20 absolute -top-14 pointer-events-none select-none">

      <!-- Brush Title -->
      <div class="w-full flex justify-center">
        <h2 class="inline-block bg-[url('assets/images/title_bg2.png')] bg-center bg-contain bg-no-repeat">
          <span class="block text-white font-serif font-semibold leading-none
                       px-5 sm:px-6 md:px-8 py-2.5 sm:py-3.5 md:py-4
                       text-2xl sm:text-3xl md:text-4xl rounded-tr-2xl">
            S Akberally Products
          </span>
        </h2>
      </div>
    </div>

    <!-- Grid for random products -->
    <div id="randomProductsGrid" class="grid gap-4 sm:gap-6 md:gap-8 grid-cols-2 lg:grid-cols-4"></div>

    <!-- Load more -->
    <div class="mt-6 text-center">
      <a href="products?category=wood working"
         class="text-sm md:text-base tracking-wide text-gray-700 hover:text-red-600">
        LOAD MORE ...
      </a>
    </div>

  </div>
</section>

<!-- <script>
  // ====== CONFIG ======
  const API_URL = "<?php echo BASE_URL; ?>/products/fetch.php";
  
  // Grid element
  const randomProductsGrid = document.getElementById('randomProductsGrid');

  // Fetch only 8 products randomly
  async function fetchRandomProducts() {
    const payload = {
      name: "",  // no filter
      category: "", // no filter
      limit: 8,  // fetch only 8 products
      offset: 0  // offset not required for random products
    };

    try {
      const res = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      if (!res.ok) throw new Error(`HTTP ${res.status}`);

      const json = await res.json();
      const data = json?.data || {};
      const products = Array.isArray(data.products) ? data.products : [];

      if (products.length > 0) {
        products.forEach(p => {
          randomProductsGrid.appendChild(createRandomProductCard(p));
        });
      }
    } catch (e) {
      console.error('Failed to fetch random products:', e);
      randomProductsGrid.innerHTML = `
        <div class="col-span-full text-center text-red-600">
          Failed to load random products. Please try again.
        </div>`;
    }
  }

  // Create product card
  function createRandomProductCard(item) {
    const card = document.createElement('article');
    card.className = "group border border-gray-200 bg-white overflow-hidden rounded cursor-pointer";

    const imgWrap = document.createElement('div');
    imgWrap.className = "h-40 sm:h-48 md:h-60 lg:h-72 flex items-center justify-center p-4 sm:p-6";
    const img = document.createElement('img');
    img.src = item.upload_path || item.upd_link || 'assets/images/placeholder-product.png';
    img.alt = item.name || '';
    img.className = "max-h-full w-auto object-contain transition-transform duration-300 group-hover:scale-[1.03]";
    img.onerror = () => { img.src = 'assets/images/placeholder-product.png'; };

    imgWrap.addEventListener('click', () => {
      // Navigate to product detail page with product ID
      const productId = item.id;
      window.location.href = `product_detail?id=${productId}`;
    });

    imgWrap.appendChild(img);

    const title = document.createElement('div');
    title.className = "h-20 title bg-red-600 text-white text-center uppercase tracking-wide font-serif font-semibold text-xs sm:text-sm md:text-base leading-tight flex items-center justify-center px-2";
    title.textContent = item.name.length > 20 ? item.name.substring(0, 20) + '...' : item.name; // Truncate if more than 20 chars

    card.appendChild(imgWrap);
    card.appendChild(title);
    return card;
  }

  // Fetch random products when page loads
  fetchRandomProducts();
</script> -->

<script>
  (() => {
    // ====== CONFIG (scoped) ======
    const RANDOM_API_URL = "<?php echo BASE_URL; ?>/products/fetch.php";
    const randomProductsGrid = document.getElementById('randomProductsGrid');

    // ====== Helpers ======
    function getPrimaryImage(item) {
      // Prefer uploads[0].upload_path per your API
      const firstUpload = Array.isArray(item.uploads) && item.uploads[0]?.upload_path
        ? item.uploads[0].upload_path
        : null;

      const src = firstUpload || item.upload_path || item.file_path || item.upd_link || '';
      if (!src) return 'assets/images/placeholder-product.png';

      // Already absolute or data URI?
      if (/^(https?:)?\/\//i.test(src) || /^data:/i.test(src)) return src;

      // Otherwise join with BASE_URL
      const BASE = '<?php echo BASE_URL; ?>/';
      const cleaned = src.replace(/^(\.\.\/)+|^\.\/+/, '');
      return BASE.replace(/\/+$/, '') + '/' + cleaned.replace(/^\/+/, '');
    }

    function truncate(text = '', max = 20) {
      return text.length > max ? text.slice(0, max) + '...' : text;
    }

    function createRandomProductCard(item) {
      const card = document.createElement('article');
      card.className = "group border border-gray-200 bg-white overflow-hidden rounded cursor-pointer";

      const imgWrap = document.createElement('div');
      imgWrap.className = "h-40 sm:h-48 md:h-60 lg:h-72 flex items-center justify-center p-4 sm:p-6";

      const img = document.createElement('img');
      img.src = getPrimaryImage(item);
      img.alt = item.name || '';
      img.className = "max-h-full w-auto object-contain transition-transform duration-300 group-hover:scale-[1.03]";
      img.onerror = () => { img.src = 'assets/images/placeholder-product.png'; };

      imgWrap.addEventListener('click', () => {
        const productId = item.id;
        // Use .php if that’s your actual file name:
        // window.location.href = `product_detail.php?id=${productId}`;
        window.location.href = `product_detail?id=${productId}`;
      });

      imgWrap.appendChild(img);

      const title = document.createElement('div');
      title.className = "h-20 title bg-red-600 text-white text-center uppercase tracking-wide font-serif font-semibold text-xs sm:text-sm md:text-base leading-tight flex items-center justify-center px-2";
      title.textContent = truncate(item.name, 20);

      card.appendChild(imgWrap);
      card.appendChild(title);
      return card;
    }

    // ====== Fetch: true-random window of 8 ======
    async function fetchRandomProducts() {
      try {
        // 1) Fetch once to get total count
        const headRes = await fetch(RANDOM_API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ name: "", category: "", limit: 1, offset: 0 })
        });
        if (!headRes.ok) throw new Error(`HTTP ${headRes.status}`);
        const headJson = await headRes.json();
        const total = Number(headJson?.data?.count || 0);

        // If nothing returned, show message and stop
        if (!total) {
          randomProductsGrid.innerHTML = `
            <div class="col-span-full text-center text-gray-600">No products available.</div>
          `;
          return;
        }

        // 2) Compute random offset (ensure non-negative)
        const maxStart = Math.max(0, total - 8);
        const randomOffset = Math.floor(Math.random() * (maxStart + 1));

        // 3) Fetch 8 products starting at random offset
        const res = await fetch(RANDOM_API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ name: "", category: "", limit: 8, offset: randomOffset })
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();

        const products = Array.isArray(json?.data?.products) ? json.data.products : [];

        // 4) Render
        randomProductsGrid.innerHTML = ''; // clear before render
        products.forEach(p => randomProductsGrid.appendChild(createRandomProductCard(p)));

        // Fallback: if backend returned fewer than 8 (e.g., near end), optionally top-up from start
        if (products.length < 8 && total > 8) {
          const moreRes = await fetch(RANDOM_API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: "", category: "", limit: 8 - products.length, offset: 0 })
          });
          if (moreRes.ok) {
            const moreJson = await moreRes.json();
            const more = Array.isArray(moreJson?.data?.products) ? moreJson.data.products : [];
            more.forEach(p => randomProductsGrid.appendChild(createRandomProductCard(p)));
          }
        }
      } catch (e) {
        console.error('Failed to fetch random products:', e);
        randomProductsGrid.innerHTML = `
          <div class="col-span-full text-center text-red-600">
            Failed to load random products. Please try again.
          </div>`;
      }
    }

    // Init
    fetchRandomProducts();
  })();
</script>

