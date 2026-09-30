    /* ---------------- Autocomplete (combobox) ---------------- */
    var indexPromise = null;
    function loadIndex() {
        if (!indexPromise) {
            indexPromise = fetch('/assets/data/search-index.json', { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .catch(function () { return { groups: [], themes: [], packages: [] }; });
        }
        return indexPromise;
    }
    function norm(s) { return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9 ]+/g, ' '); }
    function editDistance1(a, b) {
        if (Math.abs(a.length - b.length) > 1) return false;
        var i = 0, j = 0, edits = 0;
        while (i < a.length && j < b.length) {
            if (a[i] === b[j]) { i++; j++; continue; }
            if (++edits > 1) return false;
            if (a.length > b.length) i++; else if (b.length > a.length) j++; else { i++; j++; }
        }
        return edits + (a.length - i) + (b.length - j) <= 1;
    }
    function score(q, text) {
        var t = norm(text), words = t.split(' ');
        if (t.indexOf(q) === 0) return 3;
        for (var i = 0; i < words.length; i++) if (words[i].indexOf(q) === 0) return 2;
        if (t.indexOf(q) > -1) return 1;
        if (q.length >= 4) for (var k = 0; k < words.length; k++) if (words[k].length >= 4 && editDistance1(q, words[k].slice(0, q.length + 1)) ) return 0.5;
        return 0;
    }
    function highlight(label, q) {
        var i = norm(label).indexOf(q);
        var esc = function (s) { return s.replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
        if (i < 0) return esc(label);
        return esc(label.slice(0, i)) + '<mark>' + esc(label.slice(i, i + q.length)) + '</mark>' + esc(label.slice(i + q.length));
    }
    $$('[data-hg-autocomplete]').forEach(function (input) {
        var list = document.getElementById(input.getAttribute('aria-controls'));
        var form = input.form;
        var active = -1, options = [];
        function close() { list.hidden = true; input.setAttribute('aria-expanded', 'false'); input.removeAttribute('aria-activedescendant'); active = -1; }
        function setActive(i) {
            options.forEach(function (o, k) { o.setAttribute('aria-selected', k === i ? 'true' : 'false'); });
            active = i;
            if (i >= 0) { input.setAttribute('aria-activedescendant', options[i].id); options[i].scrollIntoView({ block: 'nearest' }); }
        }
        function render(q) {
            loadIndex().then(function (idx) {
                q = norm(q).trim();
                if (q.length < 2) { close(); return; }
                var rank = function (arr, fields) {
                    return arr.map(function (x) {
                        var s = 0;
                        fields.forEach(function (f) { s = Math.max(s, score(q, x[f] || '') * (f === 'keywords' ? 0.8 : 1)); });
                        return { x: x, s: s };
                    }).filter(function (r) { return r.s > 0; }).sort(function (a, b) { return b.s - a.s; });
                };
                var dests = rank(idx.groups, ['name', 'keywords', 'area']).slice(0, 5);
                var themes = rank(idx.themes, ['name', 'keywords']).slice(0, 2);
                var pkgs = rank(idx.packages, ['t']).slice(0, 6);
                // Search by Package ID ("0001", "package id 12"): exact match on approved IDs only.
                var num = q.replace(/^(package|pkg|tour)\s*(id|no\.?|number)?\s*[:#]?\s*/, '').trim();
                if (/^\d{1,4}$/.test(num)) {
                    var padded = ('0000' + num).slice(-4);
                    pkgs = idx.packages.filter(function (x) { return x.n === padded; }).map(function (x) { return { x: x, s: 1 }; }).concat(pkgs).slice(0, 6);
                }
                var html = '', n = 0;
                var attr = function (v) { return String(v).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
                var add = function (label, meta, url, kind) {
                    html += '<li class="hg-ac__opt" role="option" id="' + list.id + '-o' + n + '" data-url="' + attr(url) + '" data-kind="' + kind + '" data-label="' + attr(label) + '" aria-selected="false"><span>' + highlight(label, q) + '</span><small>' + attr(meta) + '</small></li>';
                    n++;
                };
                if (dests.length) { html += '<li class="hg-ac__group" role="presentation">Destinations</li>'; dests.forEach(function (r) { add(r.x.name, r.x.area, r.x.url, 'dest'); }); }
                if (themes.length) { html += '<li class="hg-ac__group" role="presentation">Holiday types</li>'; themes.forEach(function (r) { add(r.x.name, 'Speciality tours', r.x.url, 'theme'); }); }
                if (pkgs.length) { html += '<li class="hg-ac__group" role="presentation">Packages</li>'; pkgs.forEach(function (r) { add(r.x.t, r.x.d, r.x.u, 'pkg'); }); }
                if (!n) html = '<li class="hg-ac__empty" role="presentation">No match. Press Enter to search tours, or <a href="/customized-holidays">plan a custom trip</a>.</li>';
                list.innerHTML = html;
                options = $$('.hg-ac__opt', list);
                list.hidden = false;
                input.setAttribute('aria-expanded', 'true');
                active = -1;
            });
        }
        input.addEventListener('focus', loadIndex);
        input.addEventListener('input', function () { render(input.value); });
        input.addEventListener('keydown', function (e) {
            if (list.hidden) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); setActive(Math.min(options.length - 1, active + 1)); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(Math.max(0, active - 1)); }
            else if (e.key === 'Escape') { e.preventDefault(); close(); } // keep the typed text (search inputs clear on Escape)
            else if (e.key === 'Enter' && active >= 0) { e.preventDefault(); choose(options[active]); }
        });
        // Search forms (data-hg-ac-fill): a destination fills the field so the
        // traveller can add dates and travellers; a package opens directly with
        // the context. Other inputs navigate to the suggestion.
        function choose(o) {
            var kind = o.getAttribute('data-kind');
            track('search_suggestion_select', { suggestion_type: kind, search_term: o.getAttribute('data-label') });
            if (input.hasAttribute('data-hg-ac-fill') && kind === 'dest') {
                input.value = o.getAttribute('data-label');
                close();
                var next = form && form.querySelector('input[type="date"], select');
                if (next) next.focus();
                return;
            }
            window.location.href = o.getAttribute('data-url') + (kind === 'pkg' ? contextQuery(form) : '');
        }
        list.addEventListener('mousedown', function (e) {
            var o = e.target.closest('.hg-ac__opt');
            if (o) { e.preventDefault(); choose(o); }
        });
        input.addEventListener('blur', function () { setTimeout(close, 150); });
    });

