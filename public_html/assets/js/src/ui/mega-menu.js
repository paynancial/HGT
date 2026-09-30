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

