<?php include("header.php"); ?>
    <!-- <main class="max-w-9xl mx-auto px-4 sm:px-6 lg:px-8 py-12"> -->
<?php $page= "sub_categories"; ?>
    <main class="mx-auto pt-[112px] md:pt-[112px]">
        <?php include("inc/breadcrumb.php"); ?>

        <!-- sub_categories (dynamic) -->
        <section id="sub_categories" class="home_sub_categories py-12 md:py-16 bg-white">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

              <hr class="w-full mb-7 border border-gray-300">
              <!-- Grid -->
              <div id="sub_categoriesGrid" class="grid gap-4 sm:gap-6 md:gap-8 grid-cols-2 lg:grid-cols-4"></div>

              <!-- Status -->
              <div class="mt-6 text-center text-sm text-gray-600" id="countLabel"></div>

              <!-- Infinite scroll sentinel (hidden) -->
              <div id="infiniteSentinel" class="h-6"></div>
          </div>
        </section>

    </main>


<script>
    document.addEventListener("DOMContentLoaded", () => {
    const BASE_URL = "<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>";
    const PLACEHOLDER_IMG = "/assets/images/placeholder-product.png";
    const gridEl  = document.getElementById("sub_categoriesGrid");
    const countEl = document.getElementById("countLabel");

    const params = new URLSearchParams(window.location.search);
    const categoryId = Number(params.get("category_id")) || 3;

    const escapeHtml = (s) => (s ?? "").replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    const toPlural   = (n, w) => `${n} ${w}${n===1?'':'s'}`;

    const showSkeletons = (count = 8) => {
        gridEl.innerHTML = "";
        for (let i = 0; i < count; i++) {
        gridEl.innerHTML += `
            <div class="animate-pulse bg-gray-100 rounded-xl overflow-hidden">
            <div class="aspect-[4/3] bg-gray-200"></div>
            <div class="p-3">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
            </div>
            </div>`;
        }
    };

    const renderChildren = (parent) => {
        const children = Array.isArray(parent.children) ? parent.children : [];
        gridEl.innerHTML = "";

        children
        .sort((a, b) => (a.sort_no ?? 0) - (b.sort_no ?? 0))
        .forEach(child => {
            const id   = child.id;
            const name = escapeHtml(child.name);
            const img  = child.category_image_path || PLACEHOLDER_IMG;

            gridEl.innerHTML += `
            <a href="products.php?category=${encodeURIComponent(name)}" 
                class="group block rounded-2xl overflow-hidden border border-gray-200 hover:border-gray-300 hover:shadow transition">
                <div class="relative w-full aspect-[4/3] bg-gray-50">
                <img src="${img}" alt="${name}" class="w-full h-full object-cover object-center transition group-hover:scale-[1.03]">
                </div>
                <div class="p-3">
                <h3 class="text-base md:text-lg font-medium text-gray-800 truncate">${name}</h3>
                <p class="text-xs text-gray-500">Tap to view products</p>
                </div>
            </a>`;
        });

        // countEl.textContent = `${toPlural(children.length, "sub-category")} under “${parent.name}”`;
    };

    const showError = (msg) => {
        gridEl.innerHTML = `
        <div class="col-span-2 lg:col-span-4 text-center text-red-600 bg-red-50 border border-red-200 rounded-xl p-6">
            ${escapeHtml(msg || "Failed to load sub-categories.")}
        </div>`;
        countEl.textContent = "";
    };

    (async function init() {
        showSkeletons();
        try {
        const url = `${BASE_URL}/categories/get_children.php?category_id=${encodeURIComponent(categoryId)}`;
        const res = await fetch(url);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        if (!json?.success) throw new Error(json?.message || "Unknown API error");

        const parent = json?.data?.parents?.[0];
        if (!parent) {
            showError("No parent category found");
            return;
        }

        renderChildren(parent);

        } catch (e) {
        showError(e.message || e);
        }
    })();
    });
</script>


<?php include("footer.php"); ?>