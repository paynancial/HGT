/* Holiday Guru Travel — CMS behaviour. Progressive enhancement: every action also works as a plain form post. */
(function () {
    'use strict';
    var $ = function (s, r) { return (r || document).querySelector(s); };
    var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

    /* Mobile navigation drawer */
    var openBtn = $('[data-cms-nav-open]');
    function nav(open) {
        document.body.classList.toggle('cms-nav-open', open);
        if (openBtn) openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        var sc = $('.cms-side__scrim'); if (sc) sc.hidden = !open;
        if (open) { var f = $('.cms-side a'); if (f) f.focus(); } else if (openBtn) openBtn.focus();
    }
    if (openBtn) openBtn.addEventListener('click', function () { nav(true); });
    $$('[data-cms-nav-close]').forEach(function (b) { b.addEventListener('click', function () { nav(false); }); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (document.body.classList.contains('cms-nav-open')) nav(false);
            $$('.cms-more[open]').forEach(function (d) { d.open = false; });
        }
    });
    document.addEventListener('click', function (e) { $$('.cms-more[open]').forEach(function (d) { if (!d.contains(e.target)) d.open = false; }); });

    /* Mobile: collapsible filter panels (search stays visible) */
    $$('.cms-filters').forEach(function (f) {
        if (f.children.length < 3) return;
        var b = document.createElement('button');
        b.type = 'button'; b.className = 'cms-btn cms-btn--ghost cms-filters-toggle'; b.setAttribute('aria-expanded', 'false');
        b.textContent = 'More filters';
        b.addEventListener('click', function () { var o = f.classList.toggle('is-open'); b.setAttribute('aria-expanded', o ? 'true' : 'false'); b.textContent = o ? 'Fewer filters' : 'More filters'; });
        f.appendChild(b);
    });

    /* Keep the active editor tab visible in the scrolling tab bar */
    var at = $('.cms-tabs__a.is-active');
    if (at) { var bar = at.closest('.cms-tabs'); bar.scrollLeft = at.offsetLeft - (bar.clientWidth - at.offsetWidth) / 2; }

    /* Confirmations */
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-cms-confirm]');
        if (b && !window.confirm(b.getAttribute('data-cms-confirm'))) e.preventDefault();
    });

    /* Unsaved-change guard on the tab form */
    var form = $('#tab-form'), dirty = false;
    if (form && !form.hasAttribute('data-readonly')) {
        form.addEventListener('input', function () { dirty = true; });
        form.addEventListener('submit', function () { dirty = false; });
        window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
    }

    /* Dependent destination selectors: Country → Region → Destination */
    $$('[data-cms-dest]').forEach(function (box) {
        var c = $('[data-cms-dest-country]', box), r = $('[data-cms-dest-region]', box), d = $('[data-cms-dest-dest]', box);
        if (!c || !r || !d) return;
        function filter(sel, fn) {
            var first = null;
            $$('option', sel).forEach(function (o) { if (!o.value) return; var ok = fn(o); o.hidden = !ok; o.disabled = !ok; if (ok && !first) first = o; });
            var cur = sel.options[sel.selectedIndex];
            if (cur && cur.value && cur.disabled && first) sel.value = first.value;
        }
        function sync(from) {
            if (from === 'c') filter(r, function (o) { return o.dataset.country === c.value; });
            if (from !== 'd') filter(d, function (o) { return o.dataset.country === c.value && o.dataset.region === r.value; });
        }
        c.addEventListener('change', function () { sync('c'); });
        r.addEventListener('change', function () { sync('r'); });
        d.addEventListener('change', function () { var o = d.options[d.selectedIndex]; if (o && o.value) { c.value = o.dataset.country; filter(r, function (x) { return x.dataset.country === c.value; }); r.value = o.dataset.region; } });
        sync('c');
    });

    /* Character counters with ranges, e.g. data-cms-count="120-160" */
    $$('[data-cms-count]').forEach(function (el) {
        var rng = el.getAttribute('data-cms-count').split('-').map(Number);
        var out = document.createElement('small');
        out.className = 'cms-hint'; out.setAttribute('aria-live', 'polite');
        el.parentNode.appendChild(out);
        function upd() {
            var n = el.value.length;
            out.textContent = n + ' characters (aim ' + rng[0] + '–' + rng[1] + ')';
            out.classList.toggle('is-bad', n > 0 && (n < rng[0] || n > rng[1]));
        }
        el.addEventListener('input', upd); upd();
    });

    /* Duration line and SERP mirror */
    var dIn = $('input[name="days"]'), nIn = $('input[name="nights"]'), dOut = $('[data-cms-duration]');
    if (dIn && nIn && dOut) { var du = function () { dOut.textContent = (dIn.value || 0) + ' Days / ' + (nIn.value || 0) + ' Nights'; }; dIn.addEventListener('input', du); nIn.addEventListener('input', du); }
    $$('[data-cms-mirror]').forEach(function (out) {
        var src = $('[name="' + out.getAttribute('data-cms-mirror') + '"]');
        if (src) src.addEventListener('input', function () { out.textContent = src.value || out.textContent; });
    });

    /* Rich-text editor (contenteditable → hidden textarea; the server sanitises) */
    $$('[data-cms-rte]').forEach(function (box) {
        var area = $('.cms-rte__area', box), ta = $('textarea', box), words = $('[data-cms-words]');
        function sync() { ta.value = area.innerHTML; if (words) words.textContent = (area.innerText.trim().match(/\S+/g) || []).length; }
        area.addEventListener('input', sync);
        $$('.cms-rte__bar button', box).forEach(function (b) {
            b.addEventListener('click', function () {
                area.focus();
                var cmd = b.dataset.cmd, val = b.dataset.val;
                if (cmd === 'createLink') { var u = window.prompt('Link address (https://… or /page)'); if (u && /^(https?:\/\/|\/|#|mailto:)/.test(u)) document.execCommand('createLink', false, u); }
                else if (cmd === 'table') document.execCommand('insertHTML', false, '<table><tbody><tr><th>Heading</th><th>Heading</th></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr></tbody></table><p></p>');
                else if (cmd === 'callout') { var t = window.getSelection().toString() || 'Text'; document.execCommand('insertHTML', false, '<aside class="' + val + '">' + t.replace(/[<>&]/g, '') + '</aside><p></p>'); }
                else if (cmd === 'image') { var s = window.prompt('Image address (from the Media Library)'), a = s ? window.prompt('Alt text (describe the image)') : ''; if (s && /^(https?:\/\/|\/)/.test(s)) document.execCommand('insertHTML', false, '<figure><img src="' + s.replace(/"/g, '') + '" alt="' + (a || '').replace(/"/g, '') + '"></figure><p></p>'); }
                else document.execCommand(cmd, false, val || null);
                sync();
            });
        });
        if (form) form.addEventListener('submit', sync);
    });

    /* Reorder: itinerary days (drag + buttons), sortable lists (buttons). Field names are renumbered on submit. */
    function renumberDays(list) {
        $$('[data-cms-day]', list).forEach(function (li, i) {
            var n = $('[data-cms-dayn]', li); if (n) n.textContent = 'Day ' + (i + 1);
            $$('[name^="days["]', li).forEach(function (f) { f.name = f.name.replace(/^days\[\d+\]/, 'days[' + i + ']'); });
            $$('button[name="op"]', li).forEach(function (b) { b.value = b.value.replace(/:\d+$/, ':' + i); });
        });
        var c = $('[data-cms-daycount]'); if (c) c.textContent = $$('[data-cms-day]', list).length + ' days';
    }
    function renumberItems(list) {
        $$(':scope > li', list).forEach(function (li, i) {
            $$('[name]', li).forEach(function (f) { f.name = f.name.replace(/^(items|addons)\[\d+\]/, '$1[' + i + ']'); });
        });
    }
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-cms-move],[data-cms-sort]');
        if (!b) return;
        var li = b.closest('[data-cms-day],[data-cms-sort-item]'), dir = +(b.dataset.cmsMove || b.dataset.cmsSort);
        var sib = dir < 0 ? li.previousElementSibling : li.nextElementSibling;
        if (!sib) return;
        li.parentNode.insertBefore(li, dir < 0 ? sib : sib.nextElementSibling);
        if (li.hasAttribute('data-cms-day')) renumberDays(li.parentNode); else renumberItems(li.parentNode);
        dirty = true; b.focus();
    });
    $$('[data-cms-days]').forEach(function (list) {
        var drag = null;
        $$('[data-cms-grip]', list).forEach(function (g) {
            var li = g.closest('[data-cms-day]');
            g.addEventListener('mousedown', function () { li.setAttribute('draggable', 'true'); });
            li.addEventListener('dragstart', function (e) { drag = li; li.classList.add('is-dragging'); e.dataTransfer.effectAllowed = 'move'; });
            li.addEventListener('dragend', function () { li.classList.remove('is-dragging'); li.setAttribute('draggable', 'false'); $$('.is-over', list).forEach(function (x) { x.classList.remove('is-over'); }); renumberDays(list); dirty = true; });
            li.addEventListener('dragover', function (e) { if (!drag || drag === li) return; e.preventDefault(); li.classList.add('is-over'); });
            li.addEventListener('dragleave', function () { li.classList.remove('is-over'); });
            li.addEventListener('drop', function (e) { e.preventDefault(); li.classList.remove('is-over'); if (drag && drag !== li) list.insertBefore(drag, li); });
        });
    });

    /* Image drop zone */
    $$('[data-cms-drop]').forEach(function (z) {
        var input = $('input[type=file]', z), out = $('[data-cms-drop-files]', z);
        function show() { out.textContent = input.files.length ? input.files.length + ' file(s) ready: ' + Array.prototype.map.call(input.files, function (f) { return f.name; }).join(', ') : ''; }
        input.addEventListener('change', show);
        ['dragenter', 'dragover'].forEach(function (t) { z.addEventListener(t, function (e) { e.preventDefault(); z.classList.add('is-over'); }); });
        ['dragleave', 'drop'].forEach(function (t) { z.addEventListener(t, function (e) { e.preventDefault(); z.classList.remove('is-over'); }); });
        z.addEventListener('drop', function (e) { if (e.dataTransfer.files.length) { input.files = e.dataTransfer.files; show(); } });
    });
    /* Replace image: choosing a file submits the media form with the right action */
    $$('[data-cms-autosubmit]').forEach(function (inp) {
        inp.addEventListener('change', function () {
            if (!inp.files.length) return;
            var f = document.getElementById(inp.getAttribute('form')), h = document.createElement('input');
            h.type = 'hidden'; h.name = 'op'; h.value = inp.getAttribute('data-cms-autosubmit');
            f.appendChild(h); f.submit();
        });
    });
})();
