// Accessibility (axe-core, WCAG 2.2 A/AA), horizontal overflow and JS errors on every CMS screen at 3 widths.
//   node cms/tests/browser/cms-a11y.js     (after cms-e2e.js, so the staging test package exists)
// Env: HG_CMS_URL, PLAYWRIGHT_MODULE, AXE_PATH (path to axe.min.js).
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const fs = require('fs'), path = require('path');
const B = process.env.HG_CMS_URL || 'http://127.0.0.1:8099';
const AXE = fs.readFileSync(process.env.AXE_PATH || require.resolve('axe-core/axe.min.js'), 'utf8');
const cred = fs.readFileSync(path.resolve(__dirname, '../../storage/.staging-credentials'), 'utf8').split('\n').filter(l => l.includes('super.admin')).pop().trim().split(/\s+/).pop();
(async () => {
  const b = await chromium.launch(); let fail = 0, n = 0;
  const ctx = await b.newContext(); const p = await ctx.newPage();
  const errs = []; p.on('pageerror', e => errs.push(e.message)); p.on('console', m => { if (m.type() === 'error') errs.push(m.text()); });
  await p.goto(B + '/login'); await p.fill('#f-email', 'super.admin@staging.invalid'); await p.fill('#f-password', cred);
  await Promise.all([p.waitForNavigation(), p.click('button[type=submit]')]);
  const pk = await p.evaluate(async () => { const r = await fetch('/api/packages?q=Staging%20Test'); const j = await r.json(); return j.packages.length ? j.packages[0].ref.slice(3) : '1'; });
  const eq = await p.evaluate(async () => { const r = await fetch('/enquiries'); const h = await r.text(); const m = h.match(/\/enquiries\/(\d+)/); return m ? m[1] : null; });
  const pages = ['/', '/packages', '/packages?q=OF-0001', '/packages/new', ...['basic', 'itinerary', 'media', 'pricing', 'inclusions', 'addons', 'offers', 'seo', 'advanced', 'history'].map(t => `/packages/${pk}?tab=${t}`),
    `/packages/${pk}/preview`, `/packages/${pk}/versions/2`, '/offers', '/offers/OF-0001', '/offers/new', '/enquiries', '/enquiries/new', ...(eq ? [`/enquiries/${eq}`] : []),
    '/media', '/curation', '/pricing', '/versions', '/destinations', '/seo', '/users', '/activity', '/recycle-bin'];
  for (const w of [1440, 768, 390]) {
    await p.setViewportSize({ width: w, height: 900 });
    for (const u of pages) {
      errs.length = 0; n++;
      await p.goto(B + u, { waitUntil: 'networkidle' });
      await p.addScriptTag({ content: AXE });
      const v = await p.evaluate(async () => (await axe.run(document, { runOnly: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'] })).violations.map(x => `${x.id}(${x.nodes.length}): ${x.nodes[0].target.join(' ')}`));
      const ov = await p.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
      const ok = !v.length && ov <= 0 && !errs.length;
      if (!ok) { fail++; console.log(`FAIL ${w}px ${u} ${JSON.stringify({ axe: v, overflow: ov, errs })}`); }
    }
  }
  // Login page (signed out)
  await ctx.clearCookies(); await p.goto(B + '/login'); await p.addScriptTag({ content: AXE });
  const lv = await p.evaluate(async () => (await axe.run(document, { runOnly: ['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa'] })).violations.map(x => x.id));
  n++; if (lv.length) { fail++; console.log('FAIL login ' + lv); }
  await b.close();
  console.log(`\n${n - fail}/${n} screen checks passed`); process.exit(fail ? 1 : 0);
})();
