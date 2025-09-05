<!-- BRANDS: 4-at-a-time slider -->
<section id="brands" class="bg-white">
  <div class="h-1 bg-red-600"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- <h2 class="sr-only"></h2> -->
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
            Our Brands
          </span>
        </h2>
      </div>
    </div>

    <div class="relative">
      <!-- arrows -->
      <button id="brandPrev"
        class="absolute left-0 top-1/2 -translate-y-1/2 z-10 grid place-items-center w-9 h-9 rounded-full bg-white shadow hover:bg-gray-50"
        aria-label="Previous">‹</button>
      <button id="brandNext"
        class="absolute right-0 top-1/2 -translate-y-1/2 z-10 grid place-items-center w-9 h-9 rounded-full bg-white shadow hover:bg-gray-50"
        aria-label="Next">›</button>

      <!-- viewport -->
      <div id="brandViewport" class="overflow-hidden px-12"> <!-- padding leaves space for arrows -->
        <!-- track (JS fills) -->
        <div id="brandTrack" class="flex items-center gap-8 transition-transform duration-500 ease-out"></div>
      </div>
    </div>
  </div>
</section>

<!-- Styles specific to logos -->
<style>
  .brand-card {
    flex: 0 0 auto;           /* width set via JS based on visible count */
    display: grid;
    place-items: center;
  }
  .brand-logo {
    height: 60px;
    width: auto;
    object-fit: contain;
    filter: saturate(0) opacity(.9);
    transition: filter .15s, transform .15s;
  }
  .brand-logo:hover { filter: none; transform: translateY(-1px); }
  @media (max-width: 767.98px){
    .brand-logo { height: 48px; }
  }
</style>

<script>
  (function(){
    // Set your base URL once
    const BASE_URL = 'http://localhost/sakberally/apis';       // e.g. 'https://yourdomain.com'
    const API = `${BASE_URL}/brands/fetch.php`;

    const viewport = document.getElementById('brandViewport');
    const track = document.getElementById('brandTrack');
    const prevBtn = document.getElementById('brandPrev');
    const nextBtn = document.getElementById('brandNext');

    let items = [];        // DOM nodes for slides
    let start = 0;         // index of first visible item
    let visible = 4;       // how many to show at once (desktop default)
    let gapPx = 0;         // computed from CSS gap

    function resolvePath(p){
      if (!p) return null;
      if (/^https?:\/\//i.test(p)) return p;
      return `${BASE_URL}/${p.replace(/^(\.\.\/)+/, '')}`;
    }

    // Build one slide
    function makeItem(b){
      const card = document.createElement('div');
      card.className = 'brand-card';
      const link = document.createElement(b.brand_catalouge_path ? 'a' : 'span');
      if (b.brand_catalouge_path){
        link.href = resolvePath(b.brand_catalouge_path);
        link.target = '_blank';
        link.rel = 'noopener';
        link.title = b.name ? `${b.name} – Catalogue` : 'Catalogue';
      }
      const img = document.createElement('img');
      img.className = 'brand-logo';
      img.alt = b.name || 'Brand';
      img.src = resolvePath(b.brand_logo_path);
      link.appendChild(img);
      card.appendChild(link);
      return card;
    }

    // Calculate how many to show (2 on small screens, 4 otherwise)
    function computeVisible(){
      return window.innerWidth < 768 ? 2 : 4; // you asked for 4 showing; this keeps it usable on mobile
    }

    // Read the gap between items (from computed style)
    function computeGapPx(){
      const style = getComputedStyle(track);
      return parseFloat(style.columnGap || style.gap || '0') || 0;
    }

    // Apply widths and move to current "page"
    function layout(){
      visible = computeVisible();
      gapPx = computeGapPx();

      // Each item width in pixels so that exactly `visible` items fit in the viewport including gaps
      const vpWidth = viewport.clientWidth;
      const totalGap = gapPx * (visible - 1);
      const itemWidth = (vpWidth - totalGap) / visible;

      items.forEach(el => { el.style.width = `${itemWidth}px`; });

      // Clamp start so we never show empty space at the end
      const maxStart = Math.max(0, items.length - visible);
      if (start > maxStart) start = maxStart;

      // Translate track
      const offset = start * (itemWidth + gapPx);
      track.style.transform = `translateX(-${offset}px)`;

      // Button states
      prevBtn.classList.toggle('opacity-40', start === 0);
      prevBtn.classList.toggle('pointer-events-none', start === 0);
      nextBtn.classList.toggle('opacity-40', start === maxStart);
      nextBtn.classList.toggle('pointer-events-none', start === maxStart);
    }

    function nextPage(){
      const maxStart = Math.max(0, items.length - visible);
      if (start < maxStart){ start = Math.min(start + visible, maxStart); layout(); }
    }
    function prevPage(){
      if (start > 0){ start = Math.max(start - visible, 0); layout(); }
    }

    // Touch swipe (simple)
    let touchX = null, baseStart = 0;
    viewport.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; baseStart = start; }, {passive:true});
    viewport.addEventListener('touchend', e => {
      if (touchX == null) return;
      const dx = e.changedTouches[0].clientX - touchX;
      const threshold = 40; // px
      if (dx < -threshold) nextPage();
      else if (dx > threshold) prevPage();
      touchX = null;
    }, {passive:true});

    prevBtn.addEventListener('click', prevPage);
    nextBtn.addEventListener('click', nextPage);
    window.addEventListener('resize', () => layout());

    async function init(){
      try{
        const res = await fetch(API, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ limit: 50, offset: 0 })
        });
        const json = await res.json();
        const brands = json?.data?.brands || [];
        if (!brands.length){ document.getElementById('brands').classList.add('hidden'); return; }

        // add slides
        const frag = document.createDocumentFragment();
        brands.forEach(b => {
          const el = makeItem(b);
          items.push(el);
          frag.appendChild(el);
        });
        track.appendChild(frag);

        layout();
      }catch(err){
        console.error('Failed to load brands:', err);
      }
    }

    document.addEventListener('DOMContentLoaded', init);
  })();
</script>
