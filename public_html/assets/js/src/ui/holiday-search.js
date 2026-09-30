    /* ---------------- Mobile search editor (header form as a sheet) ---------------- */
    var hSearch = $('#hg-header-search');
    var searchOpener = null;
    function setSearch(open, opener) {
        if (!hSearch) return;
        hSearch.classList.toggle('is-open', open);
        document.documentElement.classList.toggle('hg-sheet-open', open && !desktop.matches);
        $$('[data-hg-search-toggle], [data-hg-search-open]').forEach(function (b) { b.setAttribute('aria-expanded', open ? 'true' : 'false'); });
        document.documentElement.style.overflow = open && !desktop.matches ? 'hidden' : '';
        if (open) {
            searchOpener = opener || null;
            var f = $('input', hSearch); if (f) f.focus();
            track('search_edit_open');
        } else if (searchOpener) { searchOpener.focus(); searchOpener = null; }
    }
    $$('[data-hg-search-toggle], [data-hg-search-open]').forEach(function (b) {
        b.addEventListener('click', function () { setSearch(!hSearch.classList.contains('is-open'), b); });
    });
    $$('[data-hg-search-close]').forEach(function (b) { b.addEventListener('click', function () { setSearch(false); }); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && hSearch && hSearch.classList.contains('is-open') && !$('.hg-ac:not([hidden])', hSearch)) setSearch(false);
    });
    if (desktop.addEventListener) desktop.addEventListener('change', function () { if (desktop.matches && hSearch && hSearch.classList.contains('is-open')) setSearch(false); });
    if (hSearch) hSearch.addEventListener('submit', function () {
        var d = hSearch.elements.destination;
        // Don't send values nobody chose: empty fields, and default travellers
        // on pages without a traveller context (keeps URLs and context honest).
        $$('input, select', hSearch).forEach(function (el) {
            if (!el.name) return;
            var untouchedDefault = el.hasAttribute('data-hg-optional') && el.tagName === 'SELECT' && el.options[el.selectedIndex] && el.options[el.selectedIndex].defaultSelected;
            if (el.value === '' || untouchedDefault) el.disabled = true;
        });
        setTimeout(function () { $$('[disabled]', hSearch).forEach(function (el) { if (el.name) el.disabled = false; }); }, 0);
        track('search_submit', { search_term: d ? d.value : '' });
    });

    // Rotating search hint: Search "Gulmarg" → Search "Kashmir" … (static if reduced motion)
    $$('[data-hg-placeholder-cycle]').forEach(function (input) {
        var words = input.getAttribute('data-hg-placeholder-cycle').split('|'), i = 0;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || words.length < 2) return;
        setInterval(function () {
            if (document.activeElement === input || input.value) return;
            i = (i + 1) % words.length;
            input.setAttribute('placeholder', 'Search "' + words[i] + '"');
        }, 2600);
    });

    /* Search context (date, travellers, departure) carried to the next page */
    function contextQuery(form) {
        var src = form || hSearch, q = new URLSearchParams();
        var page = new URLSearchParams(window.location.search);
        ['date', 'adults', 'children', 'departure'].forEach(function (k) {
            var el = src && src.elements ? src.elements[k] : null;
            var v = el ? el.value : page.get(k);
            if (v) q.set(k, v);
        });
        var s = q.toString();
        return s ? '?' + s : '';
    }

