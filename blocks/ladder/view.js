/**
 * Ladder block – accordion behaviour
 * • Only one <details> open at a time
 * • Smooth open/close animation
 */
(function () {
    const speed = 300; // js transition speed in ms

    document.querySelectorAll('.blog-cats__list').forEach(function (list) {
        const items = Array.from(list.querySelectorAll('details.bc-acc'));

        items.forEach(function (detail) {
            const summary = detail.querySelector('summary');
            const content = detail.querySelector('.bc-acc__panel');

            summary.addEventListener('click', function (e) {
                e.preventDefault();

                // If it's open, close it
                if (detail.open) {
                    slideUp(detail, content);
                } else {
                    // Open it
                    slideDown(detail, content);
                    // And close others in the same group
                    items.forEach(function (other) {
                        if (other !== detail && other.open) {
                            slideUp(other, other.querySelector('.bc-acc__panel'));
                        }
                    });
                }
            });
        });
    });

    function slideUp(detail, content) {
        // Current total height
        detail.style.height = detail.offsetHeight + 'px';
        // Force browser reflow to register the starting height
        detail.offsetHeight;

        // Animate to just the summary height
        detail.style.height = detail.querySelector('summary').offsetHeight + 'px';

        // Once transition completes, cleanup and natively close it
        setTimeout(() => {
            detail.removeAttribute('open');
            detail.style.height = '';
        }, speed);
    }

    function slideDown(detail, content) {
        // Open natively first to allow DOM inside to render & be measured
        detail.setAttribute('open', '');

        // Measure heights
        const summaryHeight = detail.querySelector('summary').offsetHeight;
        const contentHeight = content.offsetHeight;
        const totalHeight = summaryHeight + contentHeight;

        // Set explicit starting height to summary height
        detail.style.height = summaryHeight + 'px';
        // Force browser reflow to register the starting height
        detail.offsetHeight;

        // Fire the animation
        detail.style.height = totalHeight + 'px';

        // Once transition completes, clear inline height to return to auto
        setTimeout(() => {
            detail.style.height = '';
        }, speed);
    }
})();
