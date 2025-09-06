<?php include("header.php"); ?>
    <!-- <main class="max-w-9xl mx-auto px-4 sm:px-6 lg:px-8 py-12"> -->
<?php $page= "Products"; ?>
    <main class="mx-auto pt-[112px] md:pt-[112px]">
        <?php include("inc/breadcrumb.php"); ?>

        <!-- PRODUCTS (dynamic) -->
        <section id="products" class="home_products py-12 md:py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Filters (kept; will prefill from URL param if present) -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-3">
                <input id="searchInput" type="text" placeholder="Search product name…" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-red-500">
                <select id="categorySelect" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-red-500">
                    <option value="">All Categories</option>
                    <option value="Wood Working">Wood Working</option>
                    <!-- add more categories as needed -->
                </select>
                <button id="applyBtn" class="w-full md:w-auto bg-red-600 text-white rounded px-4 py-2 hover:bg-red-700">
                    Apply
                </button>
                </div>

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
        const BASE_URL = <?php echo BASE_URL; ?>; // <-- replace this
        const API_URL = `${BASE_URL}/products/fetch.php`;

        // URL params
        const params = new URLSearchParams(location.search);
        const urlCategory = (params.get('category') || '').trim();

        // ====== STATE ======
        const gridEl = document.getElementById('productsGrid');
        const countLabel = document.getElementById('countLabel');
        const sentinel = document.getElementById('infiniteSentinel');

        const searchInput = document.getElementById('searchInput');
        const categorySelect = document.getElementById('categorySelect');
        const applyBtn = document.getElementById('applyBtn');

        let totalCount = 0;           // from API
        let shownCount = 0;           // rendered count
        let offset = 0;               // API offset
        let loading = false;
        let allLoaded = false;

        // Behaviors based on params
        const NO_PARAM_MODE = urlCategory === '';
        const MAX_CAP_NO_PARAM = 500;              // cap when no params
        const FIRST_PAGE_SIZE_NO_PARAM = 24;       // initial
        const NEXT_PAGE_SIZE_NO_PARAM = 100;       // subsequent
        const PAGE_SIZE_CATEGORY = 100;            // when category param present

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

        function createCard(item) {
            const card = document.createElement('article');
            card.className = "group border border-red-200 bg-white overflow-hidden rounded";

            const imgWrap = document.createElement('div');
            imgWrap.className = "h-40 sm:h-48 md:h-60 lg:h-72 flex items-center justify-center p-4 sm:p-6";
            const img = document.createElement('img');
            img.src = imageFrom(item);
            img.alt = item.name || '';
            img.className = "max-h-full w-auto object-contain transition-transform duration-300 group-hover:scale-[1.03]";
            img.onerror = () => { img.src = 'assets/images/placeholder-product.png'; };
            imgWrap.appendChild(img);

            const title = document.createElement('div');
            title.className = "h-20 title bg-red-600 text-white text-center uppercase tracking-wide font-serif font-semibold text-xs sm:text-sm md:text-base leading-tight flex items-center justify-center px-2";
            title.textContent = item.name || '';

            const footer = document.createElement('div');
            footer.className = "px-3 py-2 flex items-center justify-between text-sm";
            const price = document.createElement('div');
            price.className = "font-medium text-gray-800";
            price.textContent = fmtPrice(item.price);

            const viewBtn = document.createElement('button');
            viewBtn.className = "text-red-600 hover:text-red-700 font-semibold";
            viewBtn.textContent = "View";
            viewBtn.addEventListener('click', () => {
            const nameParam = encodeURIComponent(item.name || '');
            window.location.href = `product_detail.html?name=${nameParam}`;
            });

            footer.appendChild(price);
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
        applyBtn.addEventListener('click', () => {
            currentName = searchInput.value.trim();
            currentCategory = categorySelect.value.trim();

            // Reset state
            totalCount = 0;
            shownCount = 0;
            offset = 0;
            allLoaded = false;

            // If user manually filters, keep page-size rules:
            // - If category becomes empty -> no-param behavior (24 then 100, cap 500).
            // - If category non-empty -> category behavior (100).
            fetchProducts(false);
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') applyBtn.click();
        });

        // ====== INIT ======
        // Prefill search if desired (left blank by default)
        // If URL had a category, keep it & use category behavior automatically.
        fetchProducts(false);
        io.observe(sentinel);
    </script>

<?php include("footer.php"); ?>