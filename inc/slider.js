let idx = 0;
  const track = document.getElementById('sliderTrack');
  const dots = [
    document.getElementById('dot0'),
    document.getElementById('dot1'),
    document.getElementById('dot2')
  ];

  function updateDots() {
    dots.forEach((d, i) => {
      d.className = `w-2.5 h-2.5 rounded-full ${i === idx ? 'bg-white/80' : 'bg-white/40'}`;
    });
  }

  function show(n) {
    const total = track.children.length;
    idx = (n + total) % total;
    track.style.transform = `translateX(-${idx * 100}%)`;
    updateDots();
  }

  function nextSlide() { show(idx + 1); }
  function prevSlide() { show(idx - 1); }

  window.nextSlide = nextSlide;
  window.prevSlide = prevSlide;

  updateDots(); // highlight first dot
  setInterval(nextSlide, 5000); // auto-slide