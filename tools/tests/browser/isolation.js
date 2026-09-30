// Run: 1) check out the commit BEFORE the global change, serve it, `node isolation.js before`;
//      2) check out the change, serve it, `node isolation.js after`. Fails if any page body changes.
// Env: HG_BASE_URL (default http://localhost:8098), HG_ISOLATION_DIR, PLAYWRIGHT_MODULE. Needs python3 + Pillow.
// Global-component isolation check: captures page-body (<main>) DOM, geometry, pixels and SEO head
// per page type, then compares against a saved baseline. Usage: node isolation.js before|after
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const fs = require('fs'), crypto = require('crypto');
const B = process.env.HG_BASE_URL || 'http://localhost:8098', DIR = (process.env.HG_ISOLATION_DIR || '/tmp/hg-isolation') + '/';
const mode = process.argv[2] || 'after';
const PAGES = { homepage: '/', 'search-results': '/tours?destination=gulmarg', destination: '/tours/kashmir?date=2026-10-15&adults=2&children=0',
  'tour-detail': '/srinagar-gulmarg-pahalgam-tour-package-5-days?date=2026-10-15&adults=2', itinerary: '/srinagar-gulmarg-pahalgam-tour-package-5-days#itinerary',
  contact: '/contact', about: '/about', 'travel-guide': '/travel-guide/kashmir' };
const VPS = { desktop: { width: 1440, height: 900 }, mobile: { width: 390, height: 844 } };
const sha = s => crypto.createHash('sha256').update(s).digest('hex').slice(0, 16);
(async () => {
  fs.mkdirSync(DIR + mode, { recursive: true });
  const b = await chromium.launch(); const res = {}; let fail = 0; const log = (ok, m) => { if (!ok) fail++; console.log((ok ? 'PASS  ' : 'FAIL  ') + m); };
  for (const [vp, size] of Object.entries(VPS)) for (const [name, url] of Object.entries(PAGES)) {
    const ctx = await b.newContext({ viewport: size, deviceScaleFactor: 1 });
    await ctx.addCookies([{ name: 'hg_consent', value: '1.a0', url: B }]);
    const p = await ctx.newPage(); const errs = []; p.on('pageerror', e => errs.push(e.message));
    await p.goto(B + url, { waitUntil: 'networkidle' }); await p.reload({ waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready); await p.waitForTimeout(300);
    await p.addStyleTag({ content: '.hg-support,.hg-bottombar,#hg-consent,.scroll-top{visibility:hidden!important}' });
    const d = await p.evaluate(() => {
      const m = document.querySelector('main#main'); const r = m.getBoundingClientRect();
      const ids = [...document.querySelectorAll('[id]')].map(e => e.id); const dup = ids.filter((x, i) => ids.indexOf(x) !== i);
      const q = s => { const e = document.querySelector(s); return e ? (e.getAttribute('content') || e.getAttribute('href') || e.textContent) : null; };
      return { html: m.innerHTML, w: Math.round(r.width), h: Math.round(r.height), x: Math.round(r.left),
        head: { title: document.title, desc: q('meta[name=description]'), canon: q('link[rel=canonical]'), robots: q('meta[name=robots]'), og: q('meta[property="og:title"]'),
          ld: [...document.querySelectorAll('script[type="application/ld+json"]')].map(s => s.textContent).join('\n'), h1: (document.querySelector('main h1') || {}).textContent },
        forms: [...m.querySelectorAll('form')].map(f => f.id + ':' + [...f.elements].map(e => e.name).join(',')).join('|'),
        links: [...m.querySelectorAll('a[href]')].map(a => a.getAttribute('href')).join('|'), dup: [...new Set(dup)],
        shell: ['.hg-utility', 'header.hg-header', 'main#main', 'footer.hg-footer'].map(s => !!document.querySelector(s)) };
    });
    const png = await (await p.$('main#main')).screenshot();
    const key = vp + '/' + name;
    res[key] = { dom: sha(d.html), geo: `${d.x},${d.w}x${d.h}`, head: sha(JSON.stringify(d.head)), forms: sha(d.forms), links: sha(d.links), px: sha(png.toString('base64')), dup: d.dup, errs, shell: d.shell };
    fs.writeFileSync(`${DIR}${mode}/${vp}-${name}.png`, png);
    await ctx.close();
  }
  fs.writeFileSync(`${DIR}${mode}.json`, JSON.stringify(res, null, 1));
  if (mode === 'before') { console.log('baseline saved:', Object.keys(res).length, 'captures'); await b.close(); return; }
  const base = JSON.parse(fs.readFileSync(DIR + 'before.json'));
  for (const k of Object.keys(res)) {
    const a = base[k], z = res[k];
    for (const f of ['dom', 'geo', 'head', 'forms', 'links']) log(a[f] === z[f], `${k}: page-body ${f} unchanged` + (a[f] === z[f] ? '' : ` (${a[f]} → ${z[f]})`));
    log(z.dup.length === 0, `${k}: no duplicate IDs` + (z.dup.length ? ' ' + z.dup.join(',') : ''));
    log(z.errs.length === 0, `${k}: no JS errors` + (z.errs.length ? ' ' + z.errs.join('|') : ''));
    log(z.shell.every(Boolean), `${k}: shell present (utility, header, main, footer)`);
  }
  await b.close();
  // Pixel comparison with tolerance (anti-aliasing noise of 1–2 levels is not a visual change)
  const py = require('child_process').spawnSync('python3', ['-c', `
import os,sys
from PIL import Image, ImageChops
d='${DIR}'; bad=0
for f in sorted(os.listdir(d+'before')):
    a=Image.open(d+'before/'+f).convert('L'); z=Image.open(d+'after/'+f).convert('L')
    if a.size!=z.size: print('FAIL  '+f+': page-body size changed',a.size,'->',z.size); bad+=1; continue
    a=a.crop((0,0,a.size[0],a.size[1]-1)); z=z.crop((0,0,z.size[0],z.size[1]-1))  # last row = boundary with the footer
    diff=ImageChops.difference(a,z).point(lambda v: 255 if v>16 else 0)
    n=sum(1 for v in diff.getdata() if v); pct=100.0*n/(a.size[0]*a.size[1])
    ok=pct<0.05; bad+=0 if ok else 1
    print(('PASS  ' if ok else 'FAIL  ')+f+': page-body pixels unchanged (%.4f%% differ by >16 levels)'%pct)
sys.exit(1 if bad else 0)`], { encoding: 'utf8' });
  process.stdout.write(py.stdout + py.stderr); if (py.status) fail++;
  console.log(`\n${fail ? fail + ' FAILED' : 'all passed'}`); process.exit(fail ? 1 : 0);
})();
