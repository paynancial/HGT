    /* ---------------- Mega menus ---------------- */
    var megaItems = $$('.hg-nav__item--mega');
    function closeMegas(except) {
        megaItems.forEach(function (li) {
            if (li !== except) {
                li.classList.remove('is-open');
                var b = $('[data-hg-mega]', li);
                if (b) b.setAttribute('aria-expanded', 'false');
            }
        });
    }
    megaItems.forEach(function (li) {
        var btn = $('[data-hg-mega]', li);
        var timer;
        btn.addEventListener('click', function () {
            var open = !li.classList.contains('is-open');
            closeMegas(li);
            li.classList.toggle('is-open', open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        li.addEventListener('mouseenter', function () {
            if (!desktop.matches) return;
            clearTimeout(timer);
            timer = setTimeout(function () { closeMegas(li); li.classList.add('is-open'); btn.setAttribute('aria-expanded', 'true'); }, 120);
        });
        li.addEventListener('mouseleave', function () {
            if (!desktop.matches) return;
            clearTimeout(timer);
            timer = setTimeout(function () { li.classList.remove('is-open'); btn.setAttribute('aria-expanded', 'false'); }, 180);
        });
    });
    document.addEventListener('click', function (e) {
        if (desktop.matches && !e.target.closest('.hg-nav__item--mega')) closeMegas(null);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        var open = $('.hg-nav__item--mega.is-open');
        if (open) { closeMegas(null); $('[data-hg-mega]', open).focus(); }
    });

    /* Region tabs inside Domestic / International (hover or click shows that region; arrow keys move) */
    $$('.hg-mega__rail').forEach(function (rail) {
        var tabs = $$('[data-hg-mega-tab]', rail), hoverTimer;
        function select(tab, focus) {
            tabs.forEach(function (t) {
                var on = t === tab, pane = document.getElementById(t.getAttribute('aria-controls'));
                t.setAttribute('aria-selected', on ? 'true' : 'false');
                if (on) t.removeAttribute('tabindex'); else t.setAttribute('tabindex', '-1');
                if (pane) pane.classList.toggle('is-active', on);
            });
            if (focus) tab.focus();
        }
        tabs.forEach(function (t, i) {
            t.addEventListener('click', function () { select(t); });
            t.addEventListener('mouseenter', function () {
                if (!desktop.matches) return;
                clearTimeout(hoverTimer);
                hoverTimer = setTimeout(function () { select(t); }, 90);
            });
            t.addEventListener('mouseleave', function () { clearTimeout(hoverTimer); });
            t.addEventListener('keydown', function (e) {
                var n = null;
                if (e.key === 'ArrowDown') n = (i + 1) % tabs.length;
                if (e.key === 'ArrowUp') n = (i - 1 + tabs.length) % tabs.length;
                if (e.key === 'Home') n = 0;
                if (e.key === 'End') n = tabs.length - 1;
                if (n !== null) { e.preventDefault(); select(tabs[n], true); }
            });
        });
    });
