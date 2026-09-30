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
