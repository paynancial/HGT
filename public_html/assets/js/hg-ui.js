/* Holiday Guru Travel — Phase 1 UI behaviour. No dependencies. */
(function () {
    'use strict';

    var desktop = window.matchMedia('(min-width: 1024px)');
    var $ = function (sel, root) { return (root || document).querySelector(sel); };
    var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };

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

    /* ---------------- Analytics (consent-gated, see hg-site.js) ---------------- */
    function track(name, params) { if (window.hgTrack) window.hgTrack(name, params); }
    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-hg-track]');
        if (t && !t.closest('.hg-support')) track(t.getAttribute('data-hg-track'), { link_url: t.getAttribute('href') || '' });
    });

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

    /* ---------------- Login dialog ---------------- */
    var login = document.getElementById('hg-login');
    $$('[data-hg-login-open]').forEach(function (b) {
        b.addEventListener('click', function () {
            if (!login) return;
            if (nav && nav.classList.contains('is-open')) setDrawer(false);
            if (typeof login.showModal === 'function') login.showModal(); else login.setAttribute('open', '');
        });
    });
    if (login) login.addEventListener('click', function (e) { if (e.target === login) login.close(); });

    /* ---------------- Enquiry + newsletter forms ---------------- */
    var loadedAt = Date.now();
    // Campaign source (utm_*) is kept for this visit and sent with enquiries.
    var UTM = ['utm_source', 'utm_medium', 'utm_campaign'];
    (function () {
        var q = new URLSearchParams(window.location.search);
        UTM.forEach(function (k) { var v = q.get(k); if (v) { try { sessionStorage.setItem('hg_' + k, v.slice(0, 100)); } catch (e) {} } });
    })();
    function addUtm(form) {
        UTM.forEach(function (k) {
            var v = null;
            try { v = sessionStorage.getItem('hg_' + k); } catch (e) {}
            if (!v || form.querySelector('input[name="' + k + '"]')) return;
            var h = document.createElement('input'); h.type = 'hidden'; h.name = k; h.value = v; form.appendChild(h);
        });
    }
    function guardFields(form) {
        addUtm(form);
        if (form.querySelector('input[name="website"]')) return;
        var hp = document.createElement('input');
        hp.type = 'text'; hp.name = 'website'; hp.tabIndex = -1; hp.autocomplete = 'off';
        hp.setAttribute('aria-hidden', 'true');
        hp.style.cssText = 'position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden';
        form.appendChild(hp);
    }
    function status(form, msg, ok) {
        var s = form.querySelector('.hg-form__status');
        if (!s) { alert(msg); return; }
        s.textContent = msg;
        s.className = 'hg-form__status ' + (ok ? 'is-ok' : 'is-error');
    }
    function send(form, url) {
        var data = new FormData(form);
        data.append('_ts', String(loadedAt));
        data.append('page', window.location.href);
        var btn = form.querySelector('[type="submit"]');
        if (btn) btn.disabled = true;
        return fetch(url, { method: 'POST', body: new URLSearchParams(data), credentials: 'same-origin' })
            .then(function (r) { return r.text(); })
            .then(function (t) { return t.trim() === '1'; })
            .catch(function () { return false; })
            .then(function (ok) { if (btn) btn.disabled = false; return ok; });
    }
    $$('form[data-hg-enquiry]').forEach(function (form) {
        guardFields(form);
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var bad = null;
            $$('input[required]', form).forEach(function (el) {
                var valid = el.value.trim() !== '' && el.checkValidity();
                el.setAttribute('aria-invalid', valid ? 'false' : 'true');
                if (!valid && !bad) bad = el;
            });
            if (bad) { status(form, 'Please check the highlighted fields.', false); bad.focus(); return; }
            status(form, 'Sending…', true);
            send(form, '/mail.php').then(function (ok) {
                if (ok) {
                    status(form, 'Thank you. A travel expert will contact you shortly on phone or WhatsApp.', true);
                    form.reset();
                    var pk = form.querySelector('[name="package_url"]');
                    track('enquiry_submit', { form_id: form.id, package_url: pk ? pk.value : '' });
                } else {
                    status(form, 'Sorry, we could not send this. Please call or WhatsApp +91 80066 92040.', false);
                }
            });
        });
    });
    var news = document.getElementById('newsletter');
    if (news && !window.jQuery) {
        guardFields(news);
        news.addEventListener('submit', function (e) {
            e.preventDefault();
            send(news, '/mail1.php').then(function (ok) {
                alert(ok ? 'Thank you for subscribing.' : 'Sorry, that did not work. Please try again later.');
                if (ok) news.reset();
            });
        });
    }

    /* Prefill enquiry forms from the search context (date, travellers, departure) */
    var params = new URLSearchParams(window.location.search);
    $$('form[data-hg-enquiry]').forEach(function (form) {
        var map = { travel_date: 'date', adults: 'adults', children: 'children', departure_city: 'departure' };
        Object.keys(map).forEach(function (k) {
            var v = params.get(map[k]);
            var el = form.querySelector('[name="' + k + '"]');
            if (!v || !el || el.value && el.type === 'hidden') return;
            if (el.tagName === 'SELECT' && !$('option[value="' + v.replace(/"/g, '') + '"]', el)) return;
            el.value = v;
        });
        var d = params.get('date'), m = form.querySelector('[name="travel_month"]');
        if (d && m && /^\d{4}-\d{2}/.test(d) && $('option[value="' + d.slice(0, 7) + '"]', m)) m.value = d.slice(0, 7);
        form.addEventListener('focusin', function once() { track('enquiry_start', { form_id: form.id }); form.removeEventListener('focusin', once); });
    });

    /* Package page view (slug only; package number once the registry exists) */
    var pv = $('[data-hg-package-view]');
    if (pv) track('package_view', { package_slug: pv.getAttribute('data-hg-package-view'), package_number: pv.getAttribute('data-package-number') || '' });

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

    /* ================================================================ */
    /* Search results: filters, sort, paging without full reloads       */
    /* ================================================================ */
    var liveRegion = null;
    function announce(msg) {
        if (!liveRegion) {
            liveRegion = document.createElement('div');
            liveRegion.className = 'hg-sr'; liveRegion.setAttribute('role', 'status'); liveRegion.setAttribute('aria-live', 'polite');
            document.body.appendChild(liveRegion);
        }
        liveRegion.textContent = ''; setTimeout(function () { liveRegion.textContent = msg; }, 60);
    }

    // Filter bottom sheet (mobile)
    var sheetOpener = null, scrimEl = null;
    function sheet() { return $('[data-hg-sheet]'); }
    function setSheet(open, opener) {
        var s = sheet(); if (!s) return;
        s.classList.toggle('is-open', open);
        $$('[data-hg-sheet-open]').forEach(function (b) { b.setAttribute('aria-expanded', open ? 'true' : 'false'); });
        if (open) {
            if (!scrimEl) { scrimEl = document.createElement('div'); scrimEl.className = 'hg-sheet-scrim'; scrimEl.addEventListener('click', function () { setSheet(false); }); }
            document.body.appendChild(scrimEl);
            s.setAttribute('role', 'dialog'); s.setAttribute('aria-modal', 'true');
            document.documentElement.style.overflow = 'hidden';
            sheetOpener = opener || sheetOpener;
            var f = $('.hg-filterform__head button', s); if (f) f.focus();
            track('filter_panel_open');
        } else {
            if (scrimEl && scrimEl.parentNode) scrimEl.parentNode.removeChild(scrimEl);
            s.removeAttribute('role'); s.removeAttribute('aria-modal');
            document.documentElement.style.overflow = '';
            var o = $('[data-hg-sheet-open]'); if (o && sheetOpener) o.focus();
            sheetOpener = null;
        }
    }
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-hg-sheet-open]');
        if (b) { setSheet(true, b); return; }
        if (e.target.closest('[data-hg-sheet-close]')) setSheet(false);
    });
    document.addEventListener('keydown', function (e) {
        var s = sheet();
        if (!s || !s.classList.contains('is-open')) return;
        if (e.key === 'Escape') { setSheet(false); return; }
        if (e.key === 'Tab') { // keep focus inside the open sheet
            var f = $$('button:not([disabled]), a[href], input:not([disabled]), select', s).filter(function (el) { return el.offsetParent !== null; });
            if (!f.length) return;
            if (e.shiftKey && document.activeElement === f[0]) { e.preventDefault(); f[f.length - 1].focus(); }
            else if (!e.shiftKey && document.activeElement === f[f.length - 1]) { e.preventDefault(); f[0].focus(); }
        }
    });
    if (desktop.addEventListener) desktop.addEventListener('change', function () { if (desktop.matches) setSheet(false); });

    // Wishlist (saved packages stay on this device only)
    var SAVE_KEY = 'hg_saved_packages';
    function savedList() { try { return JSON.parse(localStorage.getItem(SAVE_KEY)) || []; } catch (e) { return []; } }
    function paintSaved() {
        var saved = savedList();
        $$('[data-hg-save]').forEach(function (b) {
            var on = saved.indexOf(b.getAttribute('data-hg-save')) > -1;
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
            var lbl = b.querySelector('[data-hg-save-label]');
            if (lbl) lbl.textContent = on ? 'Saved' : 'Save';
        });
    }
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-hg-save]');
        if (!b) return;
        var id = b.getAttribute('data-hg-save'), saved = savedList(), i = saved.indexOf(id);
        if (i > -1) saved.splice(i, 1); else saved.push(id);
        try { localStorage.setItem(SAVE_KEY, JSON.stringify(saved.slice(-50))); } catch (err) { announce('Saving is not available in this browser.'); return; }
        paintSaved();
        announce(i > -1 ? 'Removed from saved packages' : 'Saved on this device');
        track(i > -1 ? 'wishlist_remove' : 'wishlist_add', { item_id: id });
    });
    paintSaved();

    // Share (native share sheet, or copy link)
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-hg-share]');
        if (!b) return;
        var mode = b.getAttribute('data-hg-share');
        var url = b.getAttribute('data-url') || window.location.href;
        var title = b.getAttribute('data-title') || document.title;
        track('share', { method: mode, content_type: 'package', item_id: b.getAttribute('data-id') || '' });
        if (mode === 'native' && navigator.share) { e.preventDefault(); navigator.share({ title: title, url: url }).catch(function () {}); return; }
        if (mode === 'copy' || mode === 'native') {
            e.preventDefault();
            var done = function () { var t = b.querySelector('[data-hg-share-label]'); if (t) { var o = t.textContent; t.textContent = 'Link copied'; setTimeout(function () { t.textContent = o; }, 2000); } announce('Link copied'); };
            if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(url).then(done, function () { window.prompt('Copy this link', url); });
            else window.prompt('Copy this link', url);
        }
    });

    // AJAX filtering with real URLs (history + back/forward + refresh safe)
    var resultsRoot = $('[data-hg-results-root]');
    if (resultsRoot && window.fetch && window.DOMParser && history.pushState) {
        var fform = $('[data-hg-filterform]');
        if (fform) fform.classList.add('is-live');
        var pending = null;
        var formUrl = function (form) {
            var q = new URLSearchParams();
            new FormData(form).forEach(function (v, k) { if (v !== '') q.append(k, v); });
            var s = q.toString();
            return form.getAttribute('action') + (s ? '?' + s : '');
        };
        var load = function (url, push, reason) {
            if (pending) pending.abort();
            pending = window.AbortController ? new AbortController() : null;
            var wasOpen = sheet() && sheet().classList.contains('is-open');
            var focusId = document.activeElement && document.activeElement.id;
            resultsRoot.classList.add('is-loading');
            resultsRoot.setAttribute('aria-busy', 'true');
            fetch(url, { credentials: 'same-origin', signal: pending ? pending.signal : undefined, headers: { 'X-Requested-With': 'fetch' } })
                .then(function (r) { if (!r.ok) throw new Error(r.status); return r.text(); })
                .then(function (html) {
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    var fresh = doc.querySelector('[data-hg-results-root]');
                    if (!fresh) throw new Error('no results');
                    resultsRoot.innerHTML = fresh.innerHTML;
                    var mb = doc.querySelector('[data-hg-mbar]'), cur = $('[data-hg-mbar]');
                    if (mb && cur) cur.innerHTML = mb.innerHTML;
                    document.title = doc.title;
                    if (push) history.pushState({ hgResults: true }, '', url);
                    var ff = $('[data-hg-filterform]');
                    if (ff) ff.classList.add('is-live');
                    if (wasOpen) { var s = sheet(); s.style.transition = 'none'; s.classList.add('is-open'); s.setAttribute('role', 'dialog'); s.setAttribute('aria-modal', 'true'); s.offsetHeight; s.style.transition = ''; }
                    if (focusId && document.getElementById(focusId)) document.getElementById(focusId).focus();
                    paintSaved();
                    var c = $('.hg-results__count', resultsRoot);
                    if (c) announce(c.textContent.replace(/\s+/g, ' ').trim());
                    if (reason === 'page') resultsRoot.scrollIntoView({ block: 'start' });
                    track(reason === 'sort' ? 'sort_change' : (reason === 'page' ? 'results_page' : 'filter_apply'), { page_location: url });
                })
                .catch(function (err) { if (err && err.name === 'AbortError') return; window.location.href = url; })
                .then(function () { resultsRoot.classList.remove('is-loading'); resultsRoot.removeAttribute('aria-busy'); });
        };
        resultsRoot.addEventListener('change', function (e) {
            var f = e.target.form;
            if (!f) return;
            if (f.hasAttribute('data-hg-filterform')) {
                // keep the sort form in sync
                var sort = $('[data-hg-sortform] select', resultsRoot), hs = f.querySelector('input[name="sort"]');
                if (sort && hs) hs.value = sort.value;
                load(formUrl(f), true, 'filter');
            } else if (f.hasAttribute('data-hg-sortform')) {
                load(formUrl(f), true, 'sort');
            }
        });
        resultsRoot.addEventListener('submit', function (e) {
            if (!e.target.matches('[data-hg-filterform], [data-hg-sortform]')) return;
            e.preventDefault();
            if (e.target.hasAttribute('data-hg-filterform') && sheet() && sheet().classList.contains('is-open')) { setSheet(false); return; }
            load(formUrl(e.target), true, 'filter');
        });
        resultsRoot.addEventListener('click', function (e) {
            var a = e.target.closest('.hg-activechips a, .hg-pager a, .hg-filterform__actions a');
            if (!a || e.metaKey || e.ctrlKey || e.shiftKey) return;
            e.preventDefault();
            load(a.getAttribute('href'), true, a.closest('.hg-pager') ? 'page' : 'filter');
        });
        history.replaceState({ hgResults: true }, '', window.location.href);
        window.addEventListener('popstate', function (e) { if (e.state && e.state.hgResults) load(window.location.href, false, 'history'); });
    } else {
        $$('[data-hg-sortform] select').forEach(function (s) { s.addEventListener('change', function () { s.form.submit(); }); });
    }

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
})();
