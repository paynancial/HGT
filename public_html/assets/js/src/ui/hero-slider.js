
    /* ---------------- Homepage hero photo slider ---------------- */
    // Crossfade every 6 s. Pauses on hover, keyboard focus, hidden tab and the pause button (WCAG 2.2.2);
    // no autoplay with prefers-reduced-motion. Without JS the first photo simply stays.
    (function () {
        var root = $('[data-hg-slider]');
        if (!root) return;
        var slides = $$('[data-hg-slide]', root), ctrl = $('[data-hg-slider-controls]');
        if (slides.length < 2 || !ctrl) return;
        var dots = $$('[data-hg-slide-to]', ctrl), pauseBtn = $('[data-hg-slide-pause]', ctrl);
        var hero = root.parentNode, cur = 0, timer = null, userPaused = false, hover = false, focus = false;
        var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        ctrl.hidden = false;
        function show(n) {
            cur = (n + slides.length) % slides.length;
            slides.forEach(function (s, i) {
                var on = i === cur;
                s.classList.toggle('is-active', on);
                if (on) s.removeAttribute('aria-hidden'); else s.setAttribute('aria-hidden', 'true');
                $$('a', s).forEach(function (a) { if (on) a.removeAttribute('tabindex'); else a.setAttribute('tabindex', '-1'); });
            });
            dots.forEach(function (d, i) { if (i === cur) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current'); });
        }
        function running() { return !userPaused && !reduced && !hover && !focus && !document.hidden; }
        function schedule() {
            clearTimeout(timer);
            if (running()) timer = setTimeout(function () { show(cur + 1); schedule(); }, 6000);
        }
        $('[data-hg-slide-prev]', ctrl).addEventListener('click', function () { show(cur - 1); schedule(); });
        $('[data-hg-slide-next]', ctrl).addEventListener('click', function () { show(cur + 1); schedule(); });
        dots.forEach(function (d) { d.addEventListener('click', function () { show(+d.getAttribute('data-hg-slide-to')); schedule(); }); });
        if (reduced) { userPaused = true; pauseBtn.setAttribute('aria-pressed', 'true'); pauseBtn.setAttribute('aria-label', 'Play slideshow'); }
        pauseBtn.addEventListener('click', function () {
            userPaused = !userPaused; reduced = false;
            pauseBtn.setAttribute('aria-pressed', userPaused ? 'true' : 'false');
            pauseBtn.setAttribute('aria-label', userPaused ? 'Play slideshow' : 'Pause slideshow');
            schedule();
        });
        hero.addEventListener('mouseenter', function () { hover = true; schedule(); });
        hero.addEventListener('mouseleave', function () { hover = false; schedule(); });
        hero.addEventListener('focusin', function () { focus = true; schedule(); });
        hero.addEventListener('focusout', function (e) { if (!hero.contains(e.relatedTarget)) { focus = false; schedule(); } });
        document.addEventListener('visibilitychange', schedule);
        ctrl.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') { show(cur - 1); e.preventDefault(); }
            if (e.key === 'ArrowRight') { show(cur + 1); e.preventDefault(); }
        });
        var x0 = null;
        root.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
        root.addEventListener('touchend', function (e) {
            if (x0 === null) return;
            var dx = e.changedTouches[0].clientX - x0; x0 = null;
            if (Math.abs(dx) > 40) { show(cur + (dx < 0 ? 1 : -1)); schedule(); }
        }, { passive: true });
        schedule();
    })();
