<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>S Akberally & Co. – Home</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Active nav style (desktop & mobile): red bg, white text, 5px black underline */
    .nav-active {
      background-color: rgb(220 38 38); /* red-600 */
      color: white !important;
      box-shadow: inset 0 -5px 0 0 #000; /* 5px black underline */
    }
    .nav-link {
      transition: background-color .2s, color .2s;
    }

    /* simple slider */
    .slider-track{display:flex;transition:transform .7s ease}
    .slide{min-width:100%;height:520px;background-size:cover;background-position:center}
  </style>
</head>
<body class="bg-white">

  <!-- Header -->
  <header class="fixed top-0 inset-x-0 z-50">
    <!-- Topbar -->
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

    <!-- Main Nav -->
    <nav class="backdrop-blur-md bg-white/80 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-16 flex items-center justify-between">
          <!-- Logo left -->
          <a href="index.html" class="flex items-center gap-2">
            <img src="your-logo.png" alt="S Akberally & Co." class="h-10 w-auto"/>
            <span class="sr-only">S Akberally & Co.</span>
          </a>

          <!-- Links right (desktop) -->
          <div class="hidden md:flex items-center gap-2 lg:gap-4">
            <a data-link="index.html" class="px-4 py-2 rounded nav-link" href="index.php">HOME</a>
            <a data-link="about.html" class="px-4 py-2 rounded nav-link" href="about.php">ABOUT US</a>

            <!-- Products dropdown (desktop hover) -->
            <div class="relative group">
              <button class="px-4 py-2 rounded nav-link flex items-center gap-1 text-gray-700 hover:text-red-600">
                PRODUCTS
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </button>

              <!-- Keep open while hovering parent OR dropdown -->
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

          <!-- Mobile menu button -->
          <button id="menuBtn" class="md:hidden p-2 rounded hover:bg-gray-100">
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
        </div>
      </div>

      <!-- Mobile panel -->
      <div id="mobilePanel" class="hidden md:hidden border-t border-gray-200 bg-white">
        <div class="px-4 py-3 space-y-1">
          <a data-link="index.html" class="block px-3 py-3 rounded nav-link" href="index.html">HOME</a>
          <a data-link="about.html" class="block px-3 py-3 rounded nav-link" href="about.html">ABOUT US</a>

          <!-- Products accordion (mobile) -->
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

  <!-- Slider -->
  <section class="pt-[112px] md:pt-[112px]">
    <div class="relative overflow-hidden">
      <div id="sliderTrack" class="slider-track">
        <div class="slide" style="background-image:url('https://images.pexels.com/photos/209235/pexels-photo-209235.jpeg?auto=compress&cs=tinysrgb&w=1600');"></div>
        <div class="slide" style="background-image:url('https://images.pexels.com/photos/1108572/pexels-photo-1108572.jpeg?auto=compress&cs=tinysrgb&w=1600');"></div>
        <div class="slide" style="background-image:url('https://images.pexels.com/photos/1249611/pexels-photo-1249611.jpeg?auto=compress&cs=tinysrgb&w=1600');"></div>
      </div>

      <!-- arrows -->
      <div class="absolute inset-0 flex items-center justify-between px-3 sm:px-6">
        <button class="bg-white/70 hover:bg-white p-2 rounded-full shadow" onclick="prevSlide()">‹</button>
        <button class="bg-white/70 hover:bg-white p-2 rounded-full shadow" onclick="nextSlide()">›</button>
      </div>

      <!-- dots -->
      <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-white/80" id="dot0"></span>
        <span class="w-2.5 h-2.5 rounded-full bg-white/40" id="dot1"></span>
        <span class="w-2.5 h-2.5 rounded-full bg-white/40" id="dot2"></span>
      </div>
    </div>
  </section>

  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h2 class="text-2xl font-bold mb-4">Welcome to S Akberally & Co.</h2>
    <p class="text-gray-700">Small demo content for the Home page.</p>
  </main>

  <script>
    /* set active nav by current filename */
    const current = location.pathname.split('/').pop() || 'index.html';
    document.querySelectorAll('[data-link]').forEach(a=>{
      if (a.dataset.link === current) a.classList.add('nav-active');
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

    // slider
    let idx = 0;
    const track = document.getElementById('sliderTrack');
    const dots = [document.getElementById('dot0'), document.getElementById('dot1'), document.getElementById('dot2')];
    function updateDots(){ dots.forEach((d,i)=> d.className = `w-2.5 h-2.5 rounded-full ${i===idx?'bg-white/80':'bg-white/40'}`); }
    function show(n){ const total = track.children.length; idx=(n+total)%total; track.style.transform=`translateX(-${idx*100}%)`; updateDots(); }
    function nextSlide(){ show(idx+1); }
    function prevSlide(){ show(idx-1); }
    window.nextSlide=nextSlide; window.prevSlide=prevSlide;
    setInterval(nextSlide,5000);
  </script>
</body>
</html>
