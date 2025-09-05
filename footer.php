
  <footer class="relative text-white" style="background-image:url('assets/images/footer_bg.jpg'); background-size:cover; background-position:center;">
      <!-- overlay for readability -->
      <div class="absolute inset-0 bg-black/40"></div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
          <!-- top row: logo | address | contact -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
          <!-- Left: Company logo -->
          <div class="flex items-start">
              <img src="assets/images/footer_png.png" alt="S Akberally &amp; Co." class="h-32 w-auto">
          </div>

          <!-- Middle: Address -->
          <div>
              <h3 class="text-2xl font-semibold mb-3">Address</h3>
              <div class="flex items-start gap-3">
              <svg class="w-6 h-6 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3zm0 0c-4 0-7 3-7 7 3 1 7 1 7 1s4 0 7-1c0-4-3-7-7-7z"></path>
              </svg>
              <p class="text-gray-100/90 leading-6">
                  137, B.R.B Basu Road (Canning Street) Kolkata 7000001, West Bengal, India.
              </p>
              </div>
          </div>

          <!-- Right: Contact Us -->
          <div>
              <h3 class="text-2xl font-semibold mb-3">Contact Us</h3>
              <div class="space-y-3">
              <div class="flex items-center gap-3">
                  <svg class="w-6 h-6 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h2.6a1 1 0 01.95.69l1.2 3.6a1 1 0 01-.45 1.17l-1.9 1.1a12.05 12.05 0 006.08 6.08l1.1-1.9a1 1 0 011.17-.45l3.6 1.2a1 1 0 01.69.95V19a2 2 0 01-2 2h-1C9.94 21 3 14.06 3 6V5z"></path>
                  </svg>
                  <div>
                  <div class="text-gray-200">Phone:</div>
                  <a href="tel:+919831724830" class="text-white hover:text-red-400 font-medium">9831724830</a>
                  </div>
              </div>
              <div class="flex items-center gap-3">
                  <svg class="w-6 h-6 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l8.25 5.5a2 2 0 002.5 0L22 7M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                  <div>
                  <div class="text-gray-200">Email:</div>
                  <a href="mailto:sakberally@gmail.com" class="text-white hover:text-red-400 font-medium">sakberally@gmail.com</a>
                  </div>
              </div>
              </div>
          </div>
          </div>

          <!-- divider -->
          <div class="mt-8 border-t border-white/20"></div>

          <!-- footer nav -->
          <nav class="relative mt-6">
          <ul class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-gray-200">
              <li><a href="index.php" class="hover:text-red-400">Home</a></li>
              <li><a href="about.php" class="hover:text-red-400">About Us</a></li>
              <li><a href="products.php" class="hover:text-red-400">Products</a></li>
              <li><a href="brands.php" class="hover:text-red-400">Brands</a></li>
              <li><a href="inquiry_form.php" class="hover:text-red-400">Inquiry Form</a></li>
              <li><a href="contact.php" class="hover:text-red-400">Contact Us</a></li>
          </ul>
          </nav>

          <!-- divider -->
          <div class="mt-6 border-t border-white/20"></div>

          <!-- bottom bar: copyright + Designed & Developed by image -->
          <div class="mt-4 flex flex-col md:flex-row items-center justify-between gap-3 text-gray-200">
          <div class="text-center md:text-left">
              <!-- use &copy; to avoid odd encoding like Â© -->
              © 2018 S Akberally &amp; Co. Ltd. All rights reserved.
          </div>
          <div class="flex items-center gap-2">
              <span>Designed &amp; Developed by&nbsp;–</span>
              <a href="#" aria-label="Designed by">
              <img src="assets/images/aslog.png" alt="Designer Logo" class="h-6 w-auto">
              </a>
          </div>
          </div>
      </div>
  </footer>

  <!-- Sticky Back-to-Top button -->
  <button id="backToTop" class="fixed bottom-5 right-5 w-12 h-12 rounded-full bg-yellow-400 text-gray-900 shadow-lg flex items-center justify-center transition-opacity duration-200 opacity-0 pointer-events-none" aria-label="Back to top">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
      </svg>
  </button>

<script>
    // Show/Hide back-to-top & smooth scroll
    (function() {
        const btn = document.getElementById('backToTop');
        const onScroll = () => {
        if (window.scrollY > 200) {
            btn.classList.remove('opacity-0', 'pointer-events-none');
        } else {
            btn.classList.add('opacity-0', 'pointer-events-none');
        }
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        onScroll(); // initialize
    })();
</script>

<script>
    /* set active nav by current filename */
    document.addEventListener('DOMContentLoaded', () => {
        // get current filename (e.g., "about.html", "" -> "index.html")
        let current = location.pathname.split('/').pop();
        if (!current || current === '/') current = 'index.php';

        document.querySelectorAll('.nav-link[data-link]').forEach(a => {
            if (a.getAttribute('data-link') === current) {
                a.classList.add('is-active');
                a.setAttribute('aria-current', 'page'); // accessibility & CSS hook
            }
        });
    });

    // mobile menu
    const menuBtn = document.getElementById('menuBtn');
    const mobilePanel = document.getElementById('mobilePanel');
    menuBtn.addEventListener('click', () => mobilePanel.classList.toggle('hidden'));

    // mobile products accordion
    const mobileProductsBtn = document.getElementById('mobileProductsBtn');
    const mobileProductsList = document.getElementById('mobileProductsList');
    const mobileChevron = document.getElementById('mobileChevron');
    mobileProductsBtn.addEventListener('click', () => {
        mobileProductsList.classList.toggle('hidden');
        mobileChevron.classList.toggle('rotate-180');
    });
</script>

<!-- Toggle script (lightweight) -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('searchToggle');
        const panel = document.getElementById('searchPanel');

        function closePanel(e) {
        if (!panel.classList.contains('hidden')) {
            if (!panel.contains(e.target) && !btn.contains(e.target)) {
            panel.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
            }
        }
        }

        btn.addEventListener('click', () => {
        panel.classList.toggle('hidden');
        btn.setAttribute('aria-expanded', panel.classList.contains('hidden') ? 'false' : 'true');
        if (!panel.classList.contains('hidden')) {
            panel.querySelector('input')?.focus();
        }
        });

        document.addEventListener('click', closePanel);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') panel.classList.add('hidden'); });
    });
</script>

</body>
</html>