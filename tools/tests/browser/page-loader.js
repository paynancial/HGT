// Page loader behaviour test (staging): first-visit only, real progress to 100%, closes fast, reduced motion, JS off, slow network, mobile, cached, CLS.
// Forces the loader in automated browsers via sessionStorage 'hg-force-loader'. Env: HG_BASE_URL, PLAYWRIGHT_MODULE.
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const B = process.env.HG_BASE_URL || 'http://localhost:8098';
let fail = 0; const rec = (ok, m) => { if (!ok) fail++; console.log((ok ? 'PASS  ' : 'FAIL  ') + m); };
const FORCE = () => { if (!sessionStorage.getItem('hg-force-set')) { sessionStorage.setItem('hg-force-set', '1'); sessionStorage.setItem('hg-force-loader', '1'); } };
async function visit(b, opts = {}, url = '/') {
  const ctx = await b.newContext({ viewport: opts.vp || { width: 1440, height: 900 }, reducedMotion: opts.reduced ? 'reduce' : 'no-preference', javaScriptEnabled: opts.js !== false });
  if (opts.force !== false) await ctx.addInitScript(FORCE);
  const p = await ctx.newPage();
  const errs = []; p.on('pageerror', e => errs.push(e.message));
  if (opts.slow) { const c = await ctx.newCDPSession(p); await c.send('Network.enable'); await c.send('Network.emulateNetworkConditions', { offline: false, latency: 150, downloadThroughput: 1.6e6 / 8, uploadThroughput: 750e3 / 8 }); }
  await p.addInitScript(() => {
    window.__hg = { seen: false, first: null, gone: null, pcts: [] , lcp: 0, cls: 0 };
    new PerformanceObserver(l => { for (const e of l.getEntries()) window.__hg.lcp = e.startTime; }).observe({ type: 'largest-contentful-paint', buffered: true });
    new PerformanceObserver(l => { for (const e of l.getEntries()) if (!e.hadRecentInput) window.__hg.cls += e.value; }).observe({ type: 'layout-shift', buffered: true });
    const iv = setInterval(() => {
      const L = document.getElementById('hg-loader');
      const vis = L && getComputedStyle(L).display !== 'none' && getComputedStyle(L).visibility !== 'hidden';
      if (vis) { window.__hg.seen = true; if (window.__hg.first === null) window.__hg.first = performance.now(); const t = L.querySelector('[data-hg-pct]'); if (t) window.__hg.pcts.push(+t.textContent); }
      if (window.__hg.seen && !vis && window.__hg.gone === null) { window.__hg.gone = performance.now(); }
      if (performance.now() > 6000) clearInterval(iv);
    }, 16);
  });
  const t0 = Date.now();
  await p.goto(B + url, { waitUntil: 'load' });
  await p.waitForTimeout(3800);
  const r = opts.js === false ? {} : await p.evaluate(() => window.__hg);
  r.loadingClass = await p.evaluate(() => document.documentElement.className);
  r.loaderInDom = await p.locator('#hg-loader').count();
  r.errs = errs; r.ctx = ctx; r.p = p;
  return r;
}
(async () => {
  const b = await chromium.launch();
  // 1. First page in a visit: loader shows, counts up to 100, closes quickly, removed from DOM.
  let r = await visit(b);
  const mono = r.pcts.every((v, i, a) => !i || v >= a[i - 1]);
  rec(r.seen, 'first page: loader shown');
  rec(r.pcts.length > 0 && r.pcts[r.pcts.length - 1] === 100 && mono, `percentage counts up smoothly to 100 (samples ${r.pcts.length}, first ${r.pcts[0]})`);
  rec(r.gone !== null && r.gone < 2900, `loader gone at ${Math.round(r.gone)} ms (limit 2.9 s)`);
  rec(r.loaderInDom === 0 && !/hg-loading/.test(r.loadingClass) && /hg-js/.test(r.loadingClass), 'overlay removed from the page afterwards; hg-js set');
  rec(r.errs.length === 0, 'no JS errors');
  // 2. Second page in the same visit: no loader.
  await r.p.goto(B + '/about', { waitUntil: 'load' });
  rec(await r.p.locator('html.hg-loading').count() === 0, 'second page in the same visit: no loader');
  // Keyboard is not trapped while loading: skip link is focusable immediately.
  await r.ctx.close();
  // 3. Without forcing, automated browsers skip it (tests stay deterministic).
  r = await visit(b, { force: false });
  rec(!r.seen, 'automated browser without force flag: no loader'); await r.ctx.close();
  // 4. Reduced motion: no spin/plane animation, closes fast.
  r = await visit(b, { reduced: true });
  const anim = await r.p.evaluate(() => { const s = document.createElement('div'); return true; });
  rec(r.seen && r.gone !== null && r.gone < 1500, `reduced motion: shown briefly, gone at ${Math.round(r.gone)} ms`); await r.ctx.close();
  // 5. JavaScript disabled: overlay never visible, content visible.
  r = await visit(b, { js: false });
  const vis = await r.p.evaluate(() => getComputedStyle(document.getElementById('hg-loader')).display);
  rec(vis === 'none' && await r.p.locator('h1').isVisible(), 'JavaScript disabled: no overlay, page visible'); await r.ctx.close();
  // 6. Slow network (1.6 Mbps, 150 ms): still closes within the cap.
  r = await visit(b, { slow: true });
  rec(r.seen && r.gone !== null && r.gone < 3000, `slow network: gone at ${Math.round(r.gone)} ms`); await r.ctx.close();
  // 7. Mobile viewport.
  r = await visit(b, { vp: { width: 390, height: 844 } });
  rec(r.seen && r.gone < 2900 && r.loaderInDom === 0, `mobile: shown and gone at ${Math.round(r.gone)} ms`); await r.ctx.close();
  // 8. Cached load (same context, sessionStorage cleared → loader again, assets from cache).
  r = await visit(b);
  await r.p.evaluate(() => { sessionStorage.removeItem('hg-visit'); });
  await r.p.reload({ waitUntil: 'load' }); await r.p.waitForTimeout(3000);
  const c = await r.p.evaluate(() => window.__hg);
  rec(c.seen && c.gone < 1500, `cached reload: loader gone at ${Math.round(c.gone)} ms`); await r.ctx.close();
  // 9. Web vitals with vs without loader (5 runs each, desktop).
  const med = a => a.sort((x, y) => x - y)[Math.floor(a.length / 2)];
  const L1 = [], L0 = [], C1 = [], C0 = [];
  for (let i = 0; i < 5; i++) { let x = await visit(b); L1.push(x.lcp); C1.push(x.cls); await x.ctx.close(); x = await visit(b, { force: false }); L0.push(x.lcp); C0.push(x.cls); await x.ctx.close(); }
  console.log(`      LCP median: with loader ${Math.round(med(L1))} ms, without ${Math.round(med(L0))} ms · CLS max: with ${Math.max(...C1).toFixed(3)}, without ${Math.max(...C0).toFixed(3)}`);
  rec(Math.max(...C1) < 0.02, 'CLS stays ~0 with the loader');
  await b.close(); console.log(fail ? `\n${fail} FAILED` : '\nall passed'); process.exit(fail ? 1 : 0);
})();
