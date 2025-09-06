
<?php include("header.php"); ?>
<?php $page= "Product Detail"; ?>
<main class="mx-auto pt-[112px] md:pt-[112px]">
  <?php //include("inc/breadcrumb.php"); ?>


<!-- Product Detail Section -->
<section class="bg-white">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 lg:py-12 grid gap-8 lg:grid-cols-2">

    <!-- LEFT: Gallery -->
    <div>
      <div class="aspect-[4/3] w-full overflow-hidden rounded-xl border border-gray-200">
        <img id="pd-main" src="" alt="Product Image" class="h-full w-full object-cover" />
      </div>

      <!-- Thumbnails -->
      <div class="mt-4 flex gap-3 overflow-x-auto" id="thumbs-container">
        <!-- Thumbnails will be populated dynamically -->
      </div>
    </div>

    <!-- RIGHT: Info -->
    <div class="lg:pl-6">
      <!-- Title -->
      <h1 id="product-name" class="text-2xl md:text-3xl font-semibold text-gray-900">Product Name</h1>

      <!-- Price + CTA row -->
      <div class="mt-3 flex flex-wrap items-center gap-3">
        <div id="product-price" class="text-2xl font-bold text-gray-900">
          ₹0 <span class="text-sm font-normal text-gray-600">/ Piece</span>
        </div>

        <a href="#get-latest-price" class="inline-flex items-center rounded-lg border border-red-600 px-3 py-2 text-red-600 hover:bg-red-50">
          Get Latest Price
        </a>
        <a href="#" id="product-brochure" target="_blank" class="inline-flex items-center rounded-lg border px-3 py-2 text-gray-700 hover:bg-gray-50">
          <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l3.5-3.5M12 15l-3.5-3.5M4 19h16" />
          </svg>
          Product Brochure
        </a>
      </div>

      <!-- Quick Specs -->
      <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4" id="product-specs">
        <!-- Specs will be populated dynamically -->
      </div>

      <!-- Description -->
      <div id="product-description" class="mt-6 text-gray-700 leading-relaxed">
        <!-- Description will be populated dynamically -->
      </div>

      <!-- Action buttons -->
      <div class="mt-8 flex flex-wrap gap-3">
        <a href="#quote" class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-3 font-medium text-white hover:bg-red-700">
          Get Best Quote
        </a>
        <a href="#interested" class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-3 font-medium text-gray-800 hover:bg-gray-50">
          Yes! I am interested
        </a>
      </div>

      <!-- Small trust strip (optional) -->
      <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-gray-600">
        <div class="flex items-center gap-2">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 12a5 5 0 1010 0 5 5 0 00-10 0z" />
          </svg>
          Verified Supplier
        </div>
        <div class="flex items-center gap-2">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7" />
          </svg>
          Secure Payments
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  // Fetch product details from API
  const params = new URLSearchParams(window.location.search);
  const productId = params.get('id');
  const apiUrl = '<?php echo BASE_URL; ?>/products/fetch.php';
  
  fetch(apiUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ id: productId }),
  })
  .then(response => response.json())
  .then(data => {
    if (data.success && data.data.products.length > 0) {
      const product = data.data.products[0];

      // Set product info
      document.getElementById('product-name').textContent = product.name;
      document.getElementById('product-price').textContent = `₹${product.price} ${product.unit}`;
      document.getElementById('product-description').innerHTML = product.description;

      // Set product image and gallery
      const mainImage = document.getElementById('pd-main');
      mainImage.src = product.upd_link;
      
      const thumbsContainer = document.getElementById('thumbs-container');
      thumbsContainer.innerHTML = `<button class="thumb shrink-0 w-24 h-24 overflow-hidden rounded-lg border border-gray-200" data-src="${product.upd_link}">
        <img src="${product.upd_link}" class="h-full w-full object-contain" />
      </button>`;

      // Set brochure link
      const brochureLink = document.getElementById('product-brochure');
      brochureLink.href = `assets/brochures/${product.id}.pdf`;  // Adjust with actual path if required

      // Set specs dynamically
      const specsContainer = document.getElementById('product-specs');
      const featuresTable = new DOMParser().parseFromString(product.features, 'text/html');
      const rows = featuresTable.querySelectorAll('tr');
      rows.forEach(row => {
        const specName = row.querySelector('td:first-child').textContent;
        const specValue = row.querySelector('td:last-child').textContent;
        specsContainer.innerHTML += `<div>
          <div class="text-sm text-gray-500">${specName}</div>
          <div class="font-medium text-gray-900">${specValue}</div>
        </div>`;
      });
    } else {
      alert('Product not found.');
    }
  })
  .catch(error => {
    console.error('Error fetching product details:', error);
    alert('Failed to load product details.');
  });

  // Simple gallery image swap
  document.querySelectorAll('.thumb').forEach(btn => {
    btn.addEventListener('click', () => {
      const src = btn.getAttribute('data-src');
      document.getElementById('pd-main').src = src;
    });
  });
</script>


</main>

<?php include("footer.php"); ?>
