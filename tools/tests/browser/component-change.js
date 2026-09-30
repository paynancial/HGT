// Component-change isolation test (staging only).
// For each global component (header, footer, utility bar, navigation, holiday search) it makes a harmless
// change IN THE STAGING COPY: (1) a marker element inside the component's markup, (2) a colour rule on the
// component in the served stylesheet. It then checks that the change is visible inside that component and
// that the page body (<main>) is unchanged on every page type: DOM, text, geometry and computed styles.
// Every file is restored afterwards (also on failure).
//
// Env: HG_DOCROOT (staging docroot, default /srv/hgt/new/public_html — never the live site),
//      HG_BASE_URL (default http://localhost:8098), PLAYWRIGHT_MODULE.
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const fs = require('fs'), crypto = require('crypto');
const DOC = process.env.HG_DOCROOT || '/srv/hgt/new/public_html';
const B = process.env.HG_BASE_URL || 'http://localhost:8098';
if (/holidaygurutravel\.in/.test(B)) { console.error('Refusing to run against the live site.'); process.exit(2); }
const PAGES = { homepage: '/', 'search-results': '/tours?destination=gulmarg', destination: '/tours/kashmir', 'tour-detail': '/srinagar-gulmarg-pahalgam-tour-package-5-days',
  contact: '/contact', about: '/about', 'travel-guide': '/travel-guide/kashmir', policy: '/cancellation-policy' };
const MARK = '<span class="hg-sr" data-hg-test-marker>isolation test</span>';
const SCENARIOS = [
  { name: 'utility bar', file: 'include/global/utility-bar.php', anchor: '<div class="hg-utility">', root: '.hg-utility', css: '.hg-utility{background-color:rgb(1, 2, 3)!important}' },
  { name: 'header', file: 'include/global/header.php', anchor: '<header class="hg-header" data-hg-header>', root: 'header.hg-header', css: 'header.hg-header{background-color:rgb(1, 2, 3)!important}' },
  { name: 'navigation', file: 'include/global/navigation.php', anchor: 'data-hg-nav>', root: '#hg-nav', css: '#hg-nav{background-color:rgb(1, 2, 3)!important}' },
  { name: 'holiday search', file: 'include/global/holiday-search.php', anchor: 'aria-label="Search holiday packages">', root: '#hg-header-search', css: '#hg-header-search{background-color:rgb(1, 2, 3)!important}' },
  { name: 'footer', file: 'include/global/footer.php', anchor: '<footer class="hg-footer">', root: 'footer.hg-footer', css: 'footer.hg-footer{background-color:rgb(1, 2, 3)!important}' },
];
const CSSFILE = DOC + '/assets/css/hg-ui.css';
// PHP's opcode cache re-checks changed files only every few seconds (opcache.revalidate_freq), so wait after each edit.
const settle = () => new Promise(r => setTimeout(r, 3000));
const sha = s => crypto.createHash('sha256').update(s).digest('hex').slice(0, 16);

async function capture(b) {
  const out = {};
  for (const [name, url] of Object.entries(PAGES)) {
    const p = await b.newPage({ viewport: { width: 1440, height: 900 } });
    // Warm up (load twice) so web fonts are cached and in use: font-display: optional otherwise makes text metrics vary.
    await p.goto(B + url, { waitUntil: 'networkidle' }); await p.reload({ waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready); await p.waitForTimeout(300);
    out[name] = await p.evaluate(() => {
      const m = document.querySelector('main#main'), r = m.getBoundingClientRect();
      const styles = [...m.querySelectorAll('*')].map(e => { const c = getComputedStyle(e); return [c.display, c.color, c.backgroundColor, c.fontSize, c.fontWeight, c.width, c.height, c.margin, c.padding].join('|'); }).join('\n');
      return { html: m.innerHTML, text: m.innerText, geo: Math.round(r.width) + 'x' + Math.round(r.height), styles };
    });
    for (const k of Object.keys(out[name])) out[name][k] = sha(out[name][k]);
    out[name].marker = await p.evaluate(() => { const mk = document.querySelector('[data-hg-test-marker]'); return mk ? !mk.closest('main') : null; });
    out[name].rootBg = {};
    for (const s of SCENARIOS) out[name].rootBg[s.root] = await p.evaluate(sel => { const e = document.querySelector(sel); return e ? getComputedStyle(e).backgroundColor : null; }, s.root);
    await p.close();
  }
  return out;
}

(async () => {
  const b = await chromium.launch(); let fail = 0;
  const rec = (ok, msg) => { if (!ok) fail++; console.log((ok ? 'PASS  ' : 'FAIL  ') + msg); };
  const base = await capture(b);
  const base2 = await capture(b);
  rec(JSON.stringify(base) === JSON.stringify(base2), 'self-check: two baseline captures are identical (measurement is stable)');
  if (JSON.stringify(base) !== JSON.stringify(base2)) { await b.close(); process.exit(1); }
  const cssOrig = fs.readFileSync(CSSFILE, 'utf8');
  for (const s of SCENARIOS) {
    const path = DOC + '/' + s.file, orig = fs.readFileSync(path, 'utf8');
    try {
      if (!orig.includes(s.anchor)) { rec(false, `${s.name}: anchor not found in ${s.file}`); continue; }
      fs.writeFileSync(path, orig.replace(s.anchor, s.anchor + MARK));
      fs.writeFileSync(CSSFILE, cssOrig + '\n' + s.css + '\n');
      await settle();
      const after = await capture(b);
      let bodyOk = true, markOk = true, cssOk = true;
      for (const page of Object.keys(PAGES)) {
        for (const k of ['html', 'text', 'geo', 'styles']) if (base[page][k] !== after[page][k]) { bodyOk = false; console.log(`      ${page}: page-body ${k} changed`); }
        if (after[page].marker !== true) markOk = false;
        if (after[page].rootBg[s.root] !== 'rgb(1, 2, 3)') cssOk = false;
      }
      rec(markOk, `${s.name}: markup change appears in the component (outside <main>) on all ${Object.keys(PAGES).length} page types`);
      rec(cssOk, `${s.name}: style change applies to the component on all page types`);
      rec(bodyOk, `${s.name}: page bodies unchanged (DOM, text, geometry, computed styles) on all page types`);
    } finally {
      fs.writeFileSync(path, orig); fs.writeFileSync(CSSFILE, cssOrig);
      await settle();
    }
  }
  const restored = await capture(b);
  rec(JSON.stringify(restored) === JSON.stringify(base), 'staging files restored (pages identical to the start)');
  await b.close();
  console.log(`\n${fail ? fail + ' FAILED' : 'all passed'}`); process.exit(fail ? 1 : 0);
})();
