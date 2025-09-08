<?php include("header.php"); ?>
<?php $page = "Our Brands"; ?>

<main class="mx-auto pt-[112px] md:pt-[112px]">
    <?php include("inc/breadcrumb.php"); ?>

    <!-- Brands Section -->
    <section id="brands" class="py-10 md:py-14 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Title -->
            <h3 class="text-center text-red-600 font-semibold text-xl sm:text-2xl md:text-3xl">
                Click on the Brand Logo to view detail PDF Catalogue
            </h3>

            <!-- Grid -->
            <div id="brandsGrid" class="mt-8 md:mt-10 grid gap-x-10 gap-y-10
                            grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 place-items-center">
                <!-- Brand items will be injected here -->
            </div>
        </div>
    </section>
</main>
<script>
  // API URL for fetching brands
  const API_URL = '<?php echo BASE_URL; ?>/brands/fetch.php';

  // Function to fetch the brands from the API
  async function fetchBrands() {
    try {
      const response = await fetch(API_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          limit: 100, // set the limit to 100 as per your request
          offset: 0,
        })
      });

      if (!response.ok) {
        throw new Error('Failed to fetch brands');
      }

      const data = await response.json();

      if (!data.success) {
        throw new Error(data.message || 'Request failed');
      }

      const brands = data.data.brands || [];

      renderBrands(brands);
    } catch (error) {
      console.error(error);
      alert('Error loading brands');
    }
  }

  // Function to render the brands dynamically
  function renderBrands(brands) {
    const brandsGrid = document.getElementById('brandsGrid');
    brandsGrid.innerHTML = ''; // Clear existing brands

    if (brands.length === 0) {
      brandsGrid.innerHTML = `<p class="text-center text-gray-500">No brands found.</p>`;
      return;
    }

    // Loop through the brands and add them to the grid
    brands.forEach(brand => {
      const brandName = brand.name;
      const logoPath = brand.brand_logo_path ? `<?php echo BASE_URL; ?>/${brand.brand_logo_path.replace('../', '')}` : '';
      const pdfPath = brand.brand_catalouge_path ? `<?php echo BASE_URL; ?>/${brand.brand_catalouge_path.replace('../', '')}` : null;

      const brandHTML = `
        <div class="group block p-2 sm:p-3 md:p-4 rounded transition-transform duration-200
                    hover:scale-[1.04] focus:outline-none focus-visible:ring focus-visible:ring-red-500/40">
          ${pdfPath ? 
            `<a href="${pdfPath}" target="_blank" rel="noopener" title="View PDF: ${brandName}">
              <img src="${logoPath}" alt="${brandName} logo" class="h-14 sm:h-16 md:h-20 lg:h-24 w-auto object-contain">
            </a>` 
            : 
            `<div class="block p-2 sm:p-3 md:p-4 rounded cursor-default" title="${brandName}">
              <img src="${logoPath}" alt="${brandName} logo" class="h-14 sm:h-16 md:h-20 lg:h-24 w-auto object-contain opacity-100">
            </div>`
          }
        </div>
      `;

      brandsGrid.innerHTML += brandHTML;
    });
  }

  // Fetch the brands on page load
  fetchBrands();
</script>
<?php include("footer.php"); ?>


