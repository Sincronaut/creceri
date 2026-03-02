// Mobile: open/close main menu (guard if header elements exist)
  const header = document.querySelector('.site-header');
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.getElementById('primary-nav');

  if (header && toggle && nav) {
    function setExpanded(open){
      header.dataset.navOpen = String(open);
      toggle.setAttribute('aria-expanded', String(open));
    }

    toggle.addEventListener('click', () => {
      const open = header.dataset.navOpen !== 'true';
      setExpanded(open);
      if(open){
        // move focus to first item for accessibility
        const firstLink = nav.querySelector('a,button');
        firstLink && firstLink.focus({preventScroll:true});
      }
    });

    // Dropdown: click to toggle on mobile, hover handled via CSS on desktop
    const dropdown = document.querySelector('.dropdown');
    const ddButton = dropdown ? dropdown.querySelector('.nav-button') : null;
    if (dropdown && ddButton) {
      ddButton.addEventListener('click', () => {
        const isMobile = window.matchMedia('(max-width: 768px)').matches;
        if(!isMobile) return; // desktop hover handles it
        const open = dropdown.dataset.open !== 'true';
        dropdown.dataset.open = String(open);
        ddButton.setAttribute('aria-expanded', String(open));
      });

      // Close mobile menu if viewport grows beyond breakpoint
      window.addEventListener('resize', () => {
        if (window.matchMedia('(min-width: 769px)').matches) {
          setExpanded(false);
          dropdown.dataset.open = 'false';
          ddButton.setAttribute('aria-expanded', 'false');
        }
      });

      // Close dropdown when clicking outside (mobile)
      document.addEventListener('click', (e) => {
        const isMobile = window.matchMedia('(max-width: 768px)').matches;
        if(!isMobile) return;
        if(!dropdown.contains(e.target) && !ddButton.contains(e.target)){
          dropdown.dataset.open = 'false';
          ddButton.setAttribute('aria-expanded', 'false');
        }
      });
    }
  }

  // Guides rail: click-to-expand accordion behavior
  (function(){
    const rail = document.querySelector('.guides .g-rail');
    if(!rail) return;
    const items = Array.from(rail.querySelectorAll('.g-item'));
    if(items.length === 0) return;

    // Ensure one active by default
    if(!items.some(i => i.classList.contains('active'))){
      items[0].classList.add('active');
      const link = items[0].querySelector('.g-link');
      link && link.setAttribute('aria-expanded','true');
    }

    const setActive = (el) => {
      items.forEach(it => {
        const link = it.querySelector('.g-link');
        if(it === el){
          it.classList.add('active');
          link && link.setAttribute('aria-expanded','true');
        } else {
          it.classList.remove('active');
          link && link.setAttribute('aria-expanded','false');
        }
      });
    };

    rail.addEventListener('click', (e) => {
      const item = e.target.closest('.g-item');
      if(!item || !rail.contains(item)) return;
      e.preventDefault();

      const isDeckMobile = window.matchMedia('(max-width: 760px)').matches;
      if(isDeckMobile){
        if(rail.classList.contains('deck-animating')) return;
        const first = rail.querySelector('.g-item');
        if(!first) return;
        rail.classList.add('deck-animating');
        rail.appendChild(first);
        setTimeout(() => rail.classList.remove('deck-animating'), 500);
        return;
      }

      setActive(item);
    });

    rail.addEventListener('keydown', (e) => {
      if(e.key !== 'Enter' && e.key !== ' ' ) return;
      const item = e.target.closest('.g-item');
      if(!item || !rail.contains(item)) return;
      e.preventDefault();
      const isDeckMobile = window.matchMedia('(max-width: 760px)').matches;
      if(isDeckMobile){
        if(rail.classList.contains('deck-animating')) return;
        const first = rail.querySelector('.g-item');
        if(!first) return;
        rail.classList.add('deck-animating');
        rail.appendChild(first);
        setTimeout(() => rail.classList.remove('deck-animating'), 500);
        return;
      }
      setActive(item);
    });
  })();

  // Footer year: keep copyright year current automatically
  (function(){
    const setYear = () => {
      const yearEls = document.querySelectorAll('.footer-year');
      if(!yearEls.length) return;
      const year = new Date().getFullYear();
      yearEls.forEach(el => { el.textContent = year; });
    };
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', setYear, { once:true });
    } else {
      setYear();
    }
  })();

  // Subscribe form (AJAX submit + inline thank-you)
  (function(){
    const handleSubmit = (form) => {
      const btn = form.querySelector('button[type="submit"]');
      const notice = form.querySelector('.signup-box__notice');
      const successMsg = form.dataset.success || 'Thank you for subscribing to our News Letter';

      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if(!btn) return;

        const fd = new FormData(form);
        // rudimentary client-side email check
        const email = (fd.get('email') || '').toString().trim();
        if(!email){
          notice && (notice.textContent = 'Please enter your email.');
          return;
        }

        const actionAttr = (form.getAttribute('data-endpoint') || form.getAttribute('action') || '').trim();
        let endpoint = actionAttr && !actionAttr.startsWith('[object') ? actionAttr : (window.ajaxurl || '/wp-admin/admin-ajax.php');
        if (!endpoint) {
          notice && (notice.textContent = 'Missing endpoint.');
          return;
        }

        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');
        notice && (notice.textContent = '');

        try{
          const res = await fetch(endpoint, { method:'POST', body: fd, credentials:'same-origin' });
          let data;
          try { data = await res.json(); }
          catch(parseErr){
            const txt = await res.text();
            notice && (notice.textContent = txt || 'Something went wrong. Please try again.');
            return;
          }
          if(res.ok && data?.success){
            notice && (notice.textContent = successMsg);
            form.reset();
          } else {
            const msg = data?.data?.message || data?.message || `Error ${res.status || ''}. Please try again.`;
            notice && (notice.textContent = msg);
          }
        }catch(err){
          notice && (notice.textContent = 'Something went wrong. Please try again.');
        }finally{
          btn.disabled = false;
          btn.removeAttribute('aria-busy');
        }
      });
    };

    const init = () => {
      document.querySelectorAll('form[data-subscribe-form="true"]').forEach(handleSubmit);
    };

    if(document.readyState === 'loading'){
      document.addEventListener('DOMContentLoaded', init, {once:true});
    } else { init(); }
  })();
