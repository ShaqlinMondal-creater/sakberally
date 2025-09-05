<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>S Akberally & Co. – About</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .nav-active {
      background-color: rgb(220 38 38);
      color: white !important;
      box-shadow: inset 0 -5px 0 0 #000;
    }
    .nav-link { transition: background-color .2s, color .2s; }
  </style>
</head>
<body class="bg-white">

  <!-- Reuse the exact same header from index.html -->
  <!-- TIP: put the header in a partial include if using a template engine -->
  <!-- START HEADER COPY -->
  <header class="fixed top-0 inset-x-0 z-50">
    <div class="backdrop-blur-md bg-white/70 border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between py-2 text-sm">
          <div class="text-gray-600 mb-1 sm:mb-0">Total Quality, Total Trust</div>
          <div class="flex items-center gap-5">
            <a href="mailto:sakberally@gmail.com" class="flex items-center gap-2 text-gray-700 hover:text-red-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l8.25 5.5a2 2 0 002.5 0L22 7M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              sakberally@gmail.com
            </a>
            <a href="tel:+919831724830" class="flex items-center gap-2 text-gray-700 hover:text-red-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h2.6a1 1 0 01.95.69l1.2 3.6a1 1 0 01-.45 1.17l-1.9 1.1a12.05 12.05 0 006.08 6.08l1.1-1.9a1 1 0 011.17-.45l3.6 1.2a1 1 0 01.69.95V19a2 2 0 01-2 2h-1C9.94 21 3 14.06 3 6V5z"/></svg>
              9831724830
            </a>
          </div>
        </div>
      </div>
    </div>

    <nav class="backdrop-blur-md bg-white/80 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-16 flex items-center justify-between">
          <a href="index.html" class="flex items-center gap-2">
            <img src="your-logo.png" alt="S Akberally & Co." class="h-10 w-auto"/>
            <span class="sr-only">S Akberally & Co.</span>
          </a>

          <div class="hidden md:flex items-center gap-2 lg:gap-4">
            <a data-link="index.html" class="px-4 py-2 rounded nav-link" href="index.html">HOME</a>
            <a data-link="about.html" class="px-4 py-2 rounded nav-link" href="about.html">ABOUT US</a>

            <div class="relative group">
              <button class="px-4 py-2 rounded nav-link flex items-center gap-1 text-gray-700 hover:text-red-600">
                PRODUCTS
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </button>
              <div class="absolute left-0 mt-2 w-64 bg-white border border-gray-200 rounded-md shadow-lg
                          opacity-0 invisible group-hover:opacity-100 group-hover:visible
                          transition duration-200 ease-out z-20 py-2">
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Power Tools</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Workshop Machinery</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Wood Working</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Steel Metal</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Lifting Tackles</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Garage Machinery</a>
              </div>
            </div>

            <a class="px-4 py-2 rounded nav-link" href="#">BRANDS</a>
            <a class="px-4 py-2 rounded nav-link" href="#">INQUIRY FORM</a>
            <a class="px-4 py-2 rounded nav-link" href="#">CONTACT US</a>
          </div>

          <button id="menuBtn" class="md:hidden p-2 rounded hover:bg-gray-100">
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
        </div>
      </div>

      <div id="mobilePanel" class="hidden md:hidden border-t border-gray-200 bg-white">
        <div class="px-4 py-3 space-y-1">
          <a data-link="index.html" class="block px-3 py-3 rounded nav-link" href="index.html">HOME</a>
          <a data-link="about.html" class="block px-3 py-3 rounded nav-link" href="about.html">ABOUT US</a>

          <button id="mobileProductsBtn" class="w-full flex items-center justify-between px-3 py-3 rounded nav-link">
            <span>PRODUCTS</span>
            <svg id="mobileChevron" class="w-4 h-4 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div id="mobileProductsList" class="hidden ml-3 border-l border-gray-200 pl-3 space-y-1 pb-2">
            <a href="#" class="block py-2 text-sm">Power Tools</a>
            <a href="#" class="block py-2 text-sm">Workshop Machinery</a>
            <a href="#" class="block py-2 text-sm">Wood Working</a>
            <a href="#" class="block py-2 text-sm">Steel Metal</a>
            <a href="#" class="block py-2 text-sm">Lifting Tackles</a>
            <a href="#" class="block py-2 text-sm">Garage Machinery</a>
          </div>

          <a class="block px-3 py-3 rounded nav-link" href="#">BRANDS</a>
          <a class="block px-3 py-3 rounded nav-link" href="#">INQUIRY FORM</a>
          <a class="block px-3 py-3 rounded nav-link" href="#">CONTACT US</a>
        </div>
      </div>
    </nav>
  </header>
  <!-- END HEADER COPY -->

  <section class="pt-[112px]">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <h1 class="text-3xl font-bold mb-4">About Us</h1>
      <p class="text-gray-700 mb-4">
        Small demo content so you can verify the active nav style on this page.
      </p>
    </div>
  </section>

  <script>
    const current = location.pathname.split('/').pop() || 'about.html';
    document.querySelectorAll('[data-link]').forEach(a=>{
      if (a.dataset.link === current) a.classList.add('nav-active');
    });

    const menuBtn = document.getElementById('menuBtn');
    const mobilePanel = document.getElementById('mobilePanel');
    menuBtn.addEventListener('click', () => mobilePanel.classList.toggle('hidden'));

    const mobileProductsBtn = document.getElementById('mobileProductsBtn');
    const mobileProductsList = document.getElementById('mobileProductsList');
    const mobileChevron = document.getElementById('mobileChevron');
    mobileProductsBtn.addEventListener('click', () => {
      mobileProductsList.classList.toggle('hidden');
      mobileChevron.classList.toggle('rotate-180');
    });
  </script>
</body>
</html>
