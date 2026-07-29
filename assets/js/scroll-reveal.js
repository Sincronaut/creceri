/**
 * Scroll Reveal — IntersectionObserver engine
 * Watches all elements with class starting with "reveal"
 * and adds "is-visible" when they enter the viewport.
 *
 * - Fires once per element (no re-hide on scroll out)
 * - Respects prefers-reduced-motion
 * - Tiny footprint, zero dependencies
 */
(function () {
  'use strict';

  // Bail if user prefers reduced motion
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  // Config
  const THRESHOLD = 0.12;   // 12% of element visible triggers animation
  const ROOT_MARGIN = '0px 0px -40px 0px'; // start slightly before element hits bottom edge

  function init() {
    // Select all elements with any "reveal" class
    const targets = document.querySelectorAll('[class*="reveal"]');

    if (!targets.length) return;

    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: THRESHOLD,
      rootMargin: ROOT_MARGIN
    });

    targets.forEach(function (el) {
      observer.observe(el);
    });
  }

  // Run after DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
