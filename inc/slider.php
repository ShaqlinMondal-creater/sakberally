<!-- Slider -->
<section class="pt-10 md:pt-32">
  <div class="relative overflow-hidden slider-outer">
    <!-- Track -->
    <div id="sliderTrack" class="slider-track">
      <div class="slide" style="background-image:url('https://images.pexels.com/photos/209235/pexels-photo-209235.jpeg?auto=compress&cs=tinysrgb&w=1600');"></div>
      <div class="slide" style="background-image:url('https://images.pexels.com/photos/1108572/pexels-photo-1108572.jpeg?auto=compress&cs=tinysrgb&w=1600');"></div>
      <div class="slide" style="background-image:url('https://images.pexels.com/photos/1249611/pexels-photo-1249611.jpeg?auto=compress&cs=tinysrgb&w=1600');"></div>
    </div>

    <!-- Arrows -->
    <div class="absolute inset-0 flex items-center justify-between px-3 sm:px-6">
      <button class="bg-white/70 hover:bg-white p-2 rounded-full shadow" onclick="prevSlide()">‹</button>
      <button class="bg-white/70 hover:bg-white p-2 rounded-full shadow" onclick="nextSlide()">›</button>
    </div>

    <!-- Dots -->
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
      <span class="w-2.5 h-2.5 rounded-full bg-white/80" id="dot0"></span>
      <span class="w-2.5 h-2.5 rounded-full bg-white/40" id="dot1"></span>
      <span class="w-2.5 h-2.5 rounded-full bg-white/40" id="dot2"></span>
    </div>
  </div>
</section>

<!-- Slider CSS -->


<script src="inc/slider.js"></script>