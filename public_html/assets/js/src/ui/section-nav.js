    /* ---------------- Section nav highlight ---------------- */
    var secLinks = $$('.hg-secnav a');
    if (secLinks.length && 'IntersectionObserver' in window) {
        var map = {};
        secLinks.forEach(function (a) { map[a.getAttribute('href').slice(1)] = a; });
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting && map[en.target.id]) {
                    secLinks.forEach(function (a) { a.removeAttribute('aria-current'); });
                    map[en.target.id].setAttribute('aria-current', 'true');
                }
            });
        }, { rootMargin: '-30% 0px -60% 0px' });
        Object.keys(map).forEach(function (id) { var el = document.getElementById(id); if (el) io.observe(el); });
    }

    /* Listing filters submit on change */
    $$('form[data-hg-autosubmit] select').forEach(function (s) {
        s.addEventListener('change', function () { s.form.submit(); });
    });

