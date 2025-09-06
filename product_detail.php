
<?php include("header.php"); ?>
<?php $page= "Product Detail"; ?>
<main class="mx-auto pt-[112px] md:pt-[112px]">
  <?php include("inc/breadcrumb.php"); ?>

    <!-- Product Detail Section -->
  <section class="bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 lg:py-12 grid gap-8 lg:grid-cols-2">

      <!-- LEFT: Gallery -->
      <div>
        <div class="aspect-[4/3] w-full overflow-hidden rounded-xl border border-gray-200">
          <img id="pd-main" src="assets/images/products/j-1018/main.jpg" alt="Jai Combi Max J 1018"
              class="h-full w-full object-cover" />
        </div>

        <!-- Thumbnails -->
        <div class="mt-4 flex gap-3 overflow-x-auto">
          <!-- repeat .thumb for all images -->
          <button class="thumb shrink-0 w-24 h-24 overflow-hidden rounded-lg border border-gray-200 focus:outline-none focus:ring"
                  data-src="assets/images/products/j-1018/main.jpg">
            <img src="assets/images/products/j-1018/main.jpg" class="h-full w-full object-cover" />
          </button>
          <button class="thumb shrink-0 w-24 h-24 overflow-hidden rounded-lg border border-gray-200"
                  data-src="assets/images/products/j-1018/2.jpg">
            <img src="assets/images/products/j-1018/2.jpg" class="h-full w-full object-cover" />
          </button>
          <button class="thumb shrink-0 w-24 h-24 overflow-hidden rounded-lg border border-gray-200"
                  data-src="assets/images/products/j-1018/3.jpg">
            <img src="assets/images/products/j-1018/3.jpg" class="h-full w-full object-cover" />
          </button>
          <button class="thumb shrink-0 w-24 h-24 overflow-hidden rounded-lg border border-gray-200"
                  data-src="assets/images/products/j-1018/4.jpg">
            <img src="assets/images/products/j-1018/4.jpg" class="h-full w-full object-cover" />
          </button>
        </div>
      </div>

      <!-- RIGHT: Info -->
      <div class="lg:pl-6">
        <!-- Title -->
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-900">
          Jai Combi Max J 1018
        </h1>

        <!-- Price + CTA row -->
        <div class="mt-3 flex flex-wrap items-center gap-3">
          <div class="text-2xl font-bold text-gray-900">
            ₹ 1,39,500 <span class="text-sm font-normal text-gray-600">/ Piece</span>
          </div>

          <a href="#get-latest-price"
            class="inline-flex items-center rounded-lg border border-red-600 px-3 py-2 text-red-600 hover:bg-red-50">
            Get Latest Price
          </a>
          <a href="assets/brochures/j-1018.pdf" target="_blank"
            class="inline-flex items-center rounded-lg border px-3 py-2 text-gray-700 hover:bg-gray-50">
            <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 3v12m0 0l3.5-3.5M12 15l-3.5-3.5M4 19h16" />
            </svg>
            Product Brochure
          </a>
        </div>

        <!-- Quick Specs -->
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
          <div>
            <div class="text-sm text-gray-500">Power</div>
            <div class="font-medium text-gray-900">5 HP</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">No Load Speed</div>
            <div class="font-medium text-gray-900">1440 RPM</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Model</div>
            <div class="font-medium text-gray-900">J-1018</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Material</div>
            <div class="font-medium text-gray-900">Cast Iron</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Brand</div>
            <div class="font-medium text-gray-900">JAI</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Circular Saw Table</div>
            <div class="font-medium text-gray-900">610×305 / 24"×12"</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Feeding Speed / Min</div>
            <div class="font-medium text-gray-900">21</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Work Table Size</div>
            <div class="font-medium text-gray-900">61"</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Main Motor Power</div>
            <div class="font-medium text-gray-900">5 HP</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Country of Origin</div>
            <div class="font-medium text-gray-900">Made in India</div>
          </div>
        </div>

        <!-- Description -->
        <div class="mt-6 text-gray-700 leading-relaxed">
          Combi planer combines surfacer & thicknesser m/cs with multi-functionality in a single machine.
          High precision and flexibility make it ideal for professional workshops.
        </div>

        <!-- Action buttons -->
        <div class="mt-8 flex flex-wrap gap-3">
          <a href="#quote"
            class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-3 font-medium text-white hover:bg-red-700">
            Get Best Quote
          </a>
          <a href="#interested"
            class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-3 font-medium text-gray-800 hover:bg-gray-50">
            Yes! I am interested
          </a>
        </div>

        <!-- Small trust strip (optional) -->
        <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-gray-600">
          <div class="flex items-center gap-2">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4M7 12a5 5 0 1010 0 5 5 0 00-10 0z" />
            </svg>
            Verified Supplier
          </div>
          <div class="flex items-center gap-2">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7" />
            </svg>
            Secure Payments
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>
    // simple gallery swap
    document.querySelectorAll('.thumb').forEach(btn => {
      btn.addEventListener('click', () => {
        const src = btn.getAttribute('data-src');
        document.getElementById('pd-main').src = src;
      });
    });
  </script>

</main>

<?php include("footer.php"); ?>
