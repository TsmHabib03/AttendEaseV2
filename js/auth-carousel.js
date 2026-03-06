/**
 * AUTH CAROUSEL — Auto-rotating hero image carousel
 * Used on login, forgot-password, and reset-password pages.
 */
(function () {
  'use strict';

  const track = document.getElementById('carouselTrack');
  const dotsContainer = document.getElementById('carouselIndicators');
  if (!track || !dotsContainer) return;

  const slides = track.querySelectorAll('.carousel-slide');
  const dots = dotsContainer.querySelectorAll('.carousel-dot');
  const total = slides.length;
  if (total === 0) return;

  let current = 0;
  let interval = null;
  const DELAY = 5000; // 5 seconds per slide

  function goTo(index) {
    current = ((index % total) + total) % total;
    track.style.transform = 'translateX(-' + (current * 100) + '%)';
    dots.forEach(function (d, i) {
      d.classList.toggle('active', i === current);
    });
  }

  function next() {
    goTo(current + 1);
  }

  function startAutoplay() {
    stopAutoplay();
    interval = setInterval(next, DELAY);
  }

  function stopAutoplay() {
    if (interval) clearInterval(interval);
  }

  // Dot click handlers
  dots.forEach(function (dot) {
    dot.addEventListener('click', function () {
      var idx = parseInt(this.getAttribute('data-index'), 10);
      if (!isNaN(idx)) {
        goTo(idx);
        startAutoplay(); // Reset timer on manual navigation
      }
    });
  });

  // Pause on hover over hero area (desktop)
  var hero = document.querySelector('.auth-hero');
  if (hero) {
    hero.addEventListener('mouseenter', stopAutoplay);
    hero.addEventListener('mouseleave', startAutoplay);
  }

  // Pause when page not visible
  document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
      stopAutoplay();
    } else {
      startAutoplay();
    }
  });

  // Init
  goTo(0);
  startAutoplay();
})();
