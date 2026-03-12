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

    // Desktop: Hover to activate
    items.forEach(li => {
      li.addEventListener('mouseenter', function() {
        if (!isMobile()) {
          activateItem(rail, li);
        }
      });
    });

    rail.addEventListener('click', function (e) {
      const li = e.target.closest('.g-item');
      if (!li) return;

      if (isMobile()) {
        // Reorder deck: clicked card becomes first/top
        e.preventDefault();
        rail.insertBefore(li, rail.firstElementChild);
        rail.querySelectorAll('.g-item').forEach(n => n.classList.remove('active'));
        rail.firstElementChild.classList.add('active');
        return;
      }

      // Desktop: click follows link if already active, otherwise activates (redundant with hover but safe)
      if (!li.classList.contains('active')) {
        e.preventDefault();
        activateItem(rail, li);
      }
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
