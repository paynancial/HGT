<?php
/**
 * GLOBAL COMPONENT: page loader ("Preparing Your Journey") + image-loading helpers.
 *
 * Included twice by hg_layout_start() (include/ui/core.php):
 *   $hgLoaderPart = 'head'  → inline critical CSS and a tiny script (runs before anything paints)
 *   $hgLoaderPart = 'body'  → the overlay markup and its progress script (first thing in <body>)
 *
 * Rules (owner, 2026-09-30):
 * - Shown only on a visitor's FIRST page in a visit (sessionStorage, per browser tab session).
 * - JavaScript only: without JS the overlay is never displayed. Pure CSS also removes it after 3 s,
 *   so an error can never leave it on screen (CSS stop at 3 s).
 * - Progress is real: DOM ready + web fonts + above-the-fold images. It closes as soon as they are ready
 *   (short 0.6 s minimum so the brand moment does not flicker); on a slow connection it stops waiting at
 *   2 s and is gone by about 2.6 s on screen (hard stop 2.3 s + a quick 0.2 s fade).
 * - prefers-reduced-motion: no spinning or flying plane; it closes without animation.
 * - The head script also sets html.hg-js (used by the image reveal in components/media-reveal.css) and
 *   window.hgImgFail (branded fallback for images that fail to load).
 * - Automated browsers (navigator.webdriver) skip the loader unless sessionStorage 'hg-force-loader' is set,
 *   so page tests and screenshots stay deterministic.
 */
if (!isset($hgLoaderPart)) $hgLoaderPart = 'body';

if ($hgLoaderPart === 'head') { ?>
    <style id="hg-loader-css">
        .hg-loader{display:none}
        html.hg-loading .hg-loader{display:flex;position:fixed;inset:0;z-index:2147483000;align-items:center;justify-content:center;flex-direction:column;gap:14px;
            background:radial-gradient(120% 90% at 50% 38%,#1A2A66 0%,#0A163D 58%,#060E28 100%);color:#fff;text-align:center;padding:24px;
            font-family:"Plus Jakarta Sans","Segoe UI",system-ui,-apple-system,sans-serif;animation:hg-loader-kill 0s linear 3s forwards}
        @keyframes hg-loader-kill{to{opacity:0;visibility:hidden;pointer-events:none}}
        .hg-loader__stage{position:relative;width:168px;height:168px;display:grid;place-items:center;transition:transform .28s ease,opacity .28s ease}
        .hg-loader__ring{position:absolute;inset:0;border-radius:50%;border:3px solid rgba(255,255,255,.12);border-top-color:#FE7F16;border-right-color:#FFC20A;animation:hg-spin 1.1s linear infinite}
        .hg-loader__badge{position:relative;width:140px;height:140px;border-radius:50%;background:#fff;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.35)}
        .hg-loader__badge img{display:block;width:100%;height:100%}
        .hg-loader__badge .hg-loader__plane{position:absolute;left:31%;top:14.5%;width:11.5%;height:auto;opacity:0;animation:hg-fly 1.9s ease-in-out infinite}
        @keyframes hg-spin{to{transform:rotate(360deg)}}
        @keyframes hg-fly{0%{transform:translateX(0);opacity:0}15%{opacity:1}75%{opacity:1}100%{transform:translateX(290%);opacity:0}}
        .hg-loader__title{margin:6px 0 0;font-size:22px;font-weight:700;letter-spacing:.01em;transition:opacity .25s ease}
        .hg-loader__tag{margin:0;font-family:"Inter","Segoe UI",system-ui,sans-serif;font-size:14px;letter-spacing:.06em;color:#FFC20A;transition:opacity .25s ease}
        .hg-loader__pct{margin:4px 0 0;font:600 15px/1 "Inter","Segoe UI",system-ui,sans-serif;font-variant-numeric:tabular-nums;color:#C9D3EA;transition:opacity .15s ease}
        .hg-loader__bar{width:180px;height:3px;border-radius:3px;background:rgba(255,255,255,.14);overflow:hidden}
        .hg-loader__bar span{display:block;height:100%;background:linear-gradient(90deg,#FE7F16,#FFC20A);transform-origin:0 50%;transform:scaleX(0)}
        html.hg-loading .hg-loader.is-done{opacity:0;transition:opacity .32s ease .12s}
        html.hg-loading .hg-loader.is-done.is-fast{transition:opacity .18s ease}
        .hg-loader.is-done .hg-loader__pct,.hg-loader.is-done .hg-loader__bar{opacity:0}
        .hg-loader.is-done .hg-loader__stage,.hg-loader.is-done .hg-loader__title,.hg-loader.is-done .hg-loader__tag{transform:scale(.96);opacity:.0;transition:transform .28s ease,opacity .28s ease}
        @media (max-width:767px){.hg-loader__stage{width:128px;height:128px}.hg-loader__badge{width:108px;height:108px}.hg-loader__title{font-size:19px}.hg-loader__tag{font-size:12.5px}.hg-loader__bar{width:150px}}
        @media (prefers-reduced-motion:reduce){.hg-loader__ring,.hg-loader__badge .hg-loader__plane{animation:none}.hg-loader__badge .hg-loader__plane{opacity:1;transform:translateX(145%)}
            html.hg-loading .hg-loader.is-done,.hg-loader.is-done *{transition:none!important}}
    </style>
    <script>
    (function (d, w) {
        var h = d.documentElement;
        h.className += ' hg-js';
        // Branded fallback for any image that fails (no broken-image icons).
        w.hgImgFail = function (img) {
            if (img.getAttribute('data-hg-fb')) { img.style.visibility = 'hidden'; img.className += ' is-loaded'; return; }
            img.setAttribute('data-hg-fb', '1');
            var p = img.parentNode;
            if (p && p.tagName === 'PICTURE') { var s = p.querySelectorAll('source'); for (var i = 0; i < s.length; i++) p.removeChild(s[i]); }
            img.removeAttribute('srcset');
            img.src = '/assets/brand/image-fallback.svg';
        };
        try {
            var ss = w.sessionStorage;
            if (!ss.getItem('hg-visit')) {
                ss.setItem('hg-visit', '1');
                var bot = w.navigator.webdriver && !ss.getItem('hg-force-loader');
                if (!bot) h.className += ' hg-loading';
            }
        } catch (e) { /* storage blocked: no loader */ }
    })(document, window);
    </script>
<?php } else { ?>
<div class="hg-loader" id="hg-loader" aria-hidden="true">
    <div class="hg-loader__stage">
        <span class="hg-loader__ring"></span>
        <span class="hg-loader__badge">
            <img alt="" width="140" height="140" sizes="140px" data-srcset="/assets/brand/holiday-guru-travel-badge-160.webp 160w, /assets/brand/holiday-guru-travel-badge-320.webp 320w" data-src="/assets/brand/holiday-guru-travel-badge-320.webp">
            <img class="hg-loader__plane" alt="" width="16" height="16" data-src="/assets/brand/holiday-guru-travel-plane.webp">
        </span>
    </div>
    <p class="hg-loader__title">Preparing Your Journey</p>
    <p class="hg-loader__tag">Explore &bull; Experience &bull; Create Memories</p>
    <p class="hg-loader__pct"><span data-hg-pct>0</span>%</p>
    <div class="hg-loader__bar"><span data-hg-bar></span></div>
</div>
<script>
(function (d, w) {
    var h = d.documentElement;
    if ((' ' + h.className + ' ').indexOf(' hg-loading ') < 0) return;
    var L = d.getElementById('hg-loader'), pct = L.querySelector('[data-hg-pct]'), bar = L.querySelector('[data-hg-bar]');
    var imgs = L.querySelectorAll('img[data-src]');
    for (var i = 0; i < imgs.length; i++) { if (imgs[i].getAttribute('data-srcset')) imgs[i].srcset = imgs[i].getAttribute('data-srcset'); imgs[i].src = imgs[i].getAttribute('data-src'); }
    var reduced = w.matchMedia && w.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var t0 = Date.now(), MIN = reduced ? 0 : 600, MAX = 2000, capped = false;
    var dom = false, fonts = false, crit = null, shown = 0, finishing = false, closed = false;
    function critImages() {
        // Above-the-fold content images (priority images and any image in the first viewport).
        var out = [], all = d.querySelectorAll('main img'), vh = w.innerHeight;
        for (var i = 0; i < all.length; i++) {
            var r = all[i].getBoundingClientRect();
            if (all[i].getAttribute('fetchpriority') === 'high' || (r.top < vh && r.bottom > 0 && r.width > 0)) out.push(all[i]);
        }
        return out;
    }
    function imgShare() {
        if (!crit) return 0;
        if (!crit.length) return 1;
        var n = 0;
        for (var i = 0; i < crit.length; i++) if (crit[i].complete) n++;
        return n / crit.length;
    }
    function target() { return finishing ? 100 : 8 + (dom ? 35 : 0) + (fonts ? 20 : 0) + 35 * imgShare(); }
    function close() {
        if (closed) return; closed = true;
        L.className += capped ? ' is-done is-fast' : ' is-done';
        setTimeout(function () {
            h.className = h.className.replace(/\s*\bhg-loading\b/, '');
            if (L.parentNode) L.parentNode.removeChild(L);
        }, reduced ? 0 : (capped ? 200 : 450));
    }
    function tick() {
        if (!finishing && dom && fonts && imgShare() === 1) finishing = true;
        if (Date.now() - t0 > MAX) finishing = capped = true;
        var t = target();
        shown = reduced ? t : shown + Math.max(capped ? 4 : 0.6, (t - shown) * (capped ? 0.45 : 0.2));
        if (shown > t) shown = t;
        pct.textContent = Math.round(shown);
        bar.style.transform = 'scaleX(' + (shown / 100) + ')';
        if (finishing && shown >= 99.5 && Date.now() - t0 >= MIN) { pct.textContent = '100'; return close(); }
        w.requestAnimationFrame(tick);
    }
    d.addEventListener('DOMContentLoaded', function () { dom = true; crit = critImages(); });
    if (d.fonts && d.fonts.ready) d.fonts.ready.then(function () { fonts = true; }); else fonts = true;
    w.addEventListener('load', function () { dom = true; fonts = true; crit = crit || []; finishing = true; });
    setTimeout(function () { finishing = capped = true; }, MAX);
    setTimeout(function () { capped = true; close(); }, MAX + 300); // hard stop even if animation frames are throttled (background tab)
    w.requestAnimationFrame(tick);
})(document, window);
</script>
<?php }
