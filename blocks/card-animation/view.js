/* Guides block – Front-end interaction */
(function () {
  function isMobile() { return window.matchMedia('(max-width: 760px)').matches; }

  function activateItem(rail, li) {
    if (!rail || !li) return;
    rail.querySelectorAll('.g-item').forEach(n => n.classList.remove('active'));
    li.classList.add('active');
  }

  function initRail(rail) {
    const items = rail.querySelectorAll('.g-item');
    if (!items.length) return;

    // Ensure one active item on load
    let current = rail.querySelector('.g-item.active') || items[0];
    activateItem(rail, current);

    rail.addEventListener('click', function (e) {
      const li = e.target.closest('.g-item');
      const a  = e.target.closest('a.g-link');
      if (!li) return;

      if (isMobile()) {
        // Reorder deck: clicked card becomes first/top
        e.preventDefault();
        rail.insertBefore(li, rail.firstElementChild);
        // keep overlay visible on top card
        rail.querySelectorAll('.g-item').forEach(n => n.classList.remove('active'));
        rail.firstElementChild.classList.add('active');
        return;
      }

      // Desktop: first click activates; second click follows link
      if (!li.classList.contains('active')) {
        e.preventDefault();
        activateItem(rail, li);
      }
      // else allow default navigation
    }, false);
  }

  function initAll() {
    document.querySelectorAll('.guides .g-rail').forEach(initRail);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
})();
