    /* ---------------- Mobile drawer ---------------- */
    var nav = $('[data-hg-nav]');
    var menuBtn = $('[data-hg-menu-toggle]');
    var scrim = $('.hg-nav__scrim');
    function setDrawer(open) {
        if (!nav) return;
        nav.classList.toggle('is-open', open);
        document.documentElement.classList.toggle('hg-sheet-open', open);
        menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        scrim.hidden = !open;
        document.documentElement.style.overflow = open ? 'hidden' : '';
        if (open) { var f = $('button, a', nav); if (f) f.focus(); } else { menuBtn.focus(); }
    }
    if (menuBtn) menuBtn.addEventListener('click', function () { setDrawer(true); });
    $$('[data-hg-menu-close]').forEach(function (el) { el.addEventListener('click', function () { setDrawer(false); }); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && nav && nav.classList.contains('is-open')) setDrawer(false);
    });
    var onBp = function () { if (desktop.matches && nav && nav.classList.contains('is-open')) setDrawer(false); };
    if (desktop.addEventListener) desktop.addEventListener('change', onBp);

