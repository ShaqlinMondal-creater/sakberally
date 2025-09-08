<?php include("header.php"); ?>
    <!-- <main class="max-w-9xl mx-auto px-4 sm:px-6 lg:px-8 py-12"> -->
<?php $page= "Products"; ?>
    <main class="mx-auto pt-[112px] md:pt-[112px]">
        <?php include("inc/breadcrumb.php"); ?>

        <!-- PRODUCTS (dynamic) -->
        <section id="products" class="home_products py-12 md:py-16 bg-white">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

              <!-- Filters (no apply button, fetch on selection/typing) -->
              <div class="mb-6 flex justify-end gap-3">
                  <input id="searchInput" type="text" placeholder="Search product name…" class="w-60 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-red-500">
                  <select id="categorySelect" class="w-40 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-red-500">
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
              </div>
              <hr class="w-full mb-7 border border-gray-300">
              <!-- Grid -->
              <div id="productsGrid" class="grid gap-4 sm:gap-6 md:gap-8 grid-cols-2 lg:grid-cols-4"></div>

              <!-- Status -->
              <div class="mt-6 text-center text-sm text-gray-600" id="countLabel"></div>

              <!-- Infinite scroll sentinel (hidden) -->
              <div id="infiniteSentinel" class="h-6"></div>
          </div>
        </section>

    </main>
<script>
        // ====== CONFIG ======
         const API_URL = `<?php echo BASE_URL; ?>/products/fetch.php`;

  // URL params
  const params = new URLSearchParams(location.search);
  const urlCategory = (params.get('category') || '').trim();

  // ====== STATE ======
  const gridEl = document.getElementById('productsGrid');
  const countLabel = document.getElementById('countLabel');
  const sentinel = document.getElementById('infiniteSentinel');

  const searchInput = document.getElementById('searchInput');
  const categorySelect = document.getElementById('categorySelect');

  let totalCount = 0;           // from API
  let shownCount = 0;           // rendered count
  let offset = 0;               // API offset
  let loading = false;
  let allLoaded = false;

  // Behaviors based on params
  const NO_PARAM_MODE = urlCategory === '';
  const MAX_CAP_NO_PARAM = 500;              // cap when no params
  const FIRST_PAGE_SIZE_NO_PARAM = 24;       // initial
  const NEXT_PAGE_SIZE_NO_PARAM = 500;  //100     // subsequent
  const PAGE_SIZE_CATEGORY = 500;       //100     // when category param present

  // Current filters
  let currentName = '';
  let currentCategory = urlCategory || '';

  // Prefill UI from param
  if (urlCategory) {
    categorySelect.value = urlCategory;
  }

  // ====== HELPERS ======
  function fmtPrice(p) {
    if (p === null || p === undefined) return '';
    const num = Number(p);
    if (Number.isNaN(num)) return String(p);
    return num === 0 ? '—' : `₹ ${num.toLocaleString('en-IN')}`;
  }

  function imageFrom(item) {
    if (item.upload_path) return item.upload_path;
    if (item.upd_link) return item.upd_link;
    return 'assets/images/placeholder-product.png';
  }

  function truncateText(text, maxLength) {
    if (text.length > maxLength) {
      return text.substring(0, maxLength) + '...';
    }
    return text;
  }

  function createCard(item) {
    const card = document.createElement('article');
    card.className = "group border border-red-200 bg-white overflow-hidden rounded cursor-pointer";

    const imgWrap = document.createElement('div');
    imgWrap.className = "h-40 sm:h-48 md:h-60 lg:h-72 flex items-center justify-center p-4 sm:p-6";
    const img = document.createElement('img');
    img.src = imageFrom(item);
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
    title.textContent = truncateText(item.name, 20); // Truncate if more than 20 chars

    const footer = document.createElement('div');
    footer.className = "px-3 py-2 flex items-center justify-center text-sm";
    
    // Replace price and view with "View Specification"
    const viewBtn = document.createElement('button');
    viewBtn.className = "text-red-600 hover:text-red-700 font-semibold";
    viewBtn.textContent = "View Specification";
    viewBtn.addEventListener('click', () => {
      // Navigate to product detail page with product ID
      const productId = item.id;
      window.location.href = `product_detail?id=${productId}`;
    });

    footer.appendChild(viewBtn);

    card.appendChild(imgWrap);
    card.appendChild(title);
    card.appendChild(footer);
    return card;
  }

  function updateCountLabel() {
    const targetTotal = NO_PARAM_MODE ? Math.min(totalCount, MAX_CAP_NO_PARAM) : totalCount;
    countLabel.textContent = targetTotal
      ? `Showing ${shownCount} of ${targetTotal}${NO_PARAM_MODE && totalCount > MAX_CAP_NO_PARAM ? ' (capped at 500)' : ''}`
      : '';
  }

  function getNextLimit() {
    if (NO_PARAM_MODE) {
      // First call uses 24, then 100
      return (offset === 0) ? FIRST_PAGE_SIZE_NO_PARAM : NEXT_PAGE_SIZE_NO_PARAM;
    }
    // Category param mode: always 100
    return PAGE_SIZE_CATEGORY;
  }

  function reachedCap() {
    if (!NO_PARAM_MODE) return false;
    return shownCount >= Math.min(totalCount, MAX_CAP_NO_PARAM);
  }

  // ====== API ======
  async function fetchProducts(append = true) {
    if (loading || allLoaded || reachedCap()) return;
    loading = true;

    const limit = getNextLimit();

    const payload = {
      name: currentName || "",
      category: currentCategory || "",
      limit,
      offset
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
      totalCount = Number(data.count || 0);
      const products = Array.isArray(data.products) ? data.products : [];

      if (!append) gridEl.innerHTML = "";

      products.forEach(p => {
        if (NO_PARAM_MODE && shownCount >= MAX_CAP_NO_PARAM) return; // enforce cap
        gridEl.appendChild(createCard(p));
        shownCount++;
      });

      offset += products.length;

      // all loaded?
      const targetTotal = NO_PARAM_MODE ? Math.min(totalCount, MAX_CAP_NO_PARAM) : totalCount;
      if (shownCount >= targetTotal || products.length === 0) {
        allLoaded = true;
      }

      updateCountLabel();
    } catch (e) {
      console.error('Fetch failed:', e);
      if (!append) {
        gridEl.innerHTML = `<div class="col-span-full text-center text-red-600">
          Failed to load products. Please try again.
        </div>`;
      }
      allLoaded = true;
    } finally {
      loading = false;
    }
  }

  // ====== Infinite Scroll ======
  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) fetchProducts(true);
    });
  }, { rootMargin: '600px 0px' }); // prefetch earlier

  // ====== Events ======
  categorySelect.addEventListener('change', () => {
    currentCategory = categorySelect.value.trim();
    resetState();
    fetchProducts(false);
  });

  searchInput.addEventListener('input', () => {
    if (searchInput.value.length >= 3) {
      currentName = searchInput.value.trim();
      resetState();
      fetchProducts(false);
    }
  });

  function resetState() {
    totalCount = 0;
    shownCount = 0;
    offset = 0;
    allLoaded = false;
  }

  // ====== INIT ======
  // Prefill search if desired (left blank by default)
  // If URL had a category, keep it & use category behavior automatically.
  fetchProducts(false);
  io.observe(sentinel);
</script>
</script>

<?php include("footer.php"); ?>