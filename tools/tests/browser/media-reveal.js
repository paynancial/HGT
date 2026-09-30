// Image loading + reveal test (staging). Needs photos in the staging copy: run tools/build_images.php there first
// (the repository has no photo library). Env: HG_BASE_URL, PLAYWRIGHT_MODULE.
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || '/opt/node22/lib/node_modules/playwright');
const B = process.env.HG_BASE_URL || 'http://localhost:8098';
const PKG = '/amarnath-ji-yatra-by-helicopter-2n-3d', LIST = '/tours/amarnath';
let fail = 0; const rec = (ok, m) => { if (!ok) fail++; console.log((ok ? 'PASS  ' : 'FAIL  ') + m); };
(async () => {
  const b = await chromium.launch();
  const ctx = async (o = {}) => { const c = await b.newContext({ viewport: o.vp || { width: 1440, height: 900 }, reducedMotion: o.reduced ? 'reduce' : 'no-preference', javaScriptEnabled: o.js !== false, deviceScaleFactor: o.dpr || 1, isMobile: !!o.touch, hasTouch: !!o.touch }); return c; };
  // 1. Skeleton + spinner while loading, then reveal (image held back 1.2 s).
  let c = await ctx(); let p = await c.newPage();
  await p.route(/assets\/img\/.*\.(webp|jpg)$/, async r => { await new Promise(x => setTimeout(x, 1200)); r.continue(); });
  p.goto(B + PKG).catch(() => {}); await p.waitForSelector('.hg-pkghead__media img'); await p.waitForTimeout(300);
  const before = await p.evaluate(() => { const f = document.querySelector('.hg-pkghead__media'), i = f.querySelector('img'); const cs = getComputedStyle(f); return { bg: cs.backgroundImage.includes('svg'), op: getComputedStyle(i).opacity, h: f.getBoundingClientRect().height }; });
  rec(before.bg && before.op === '0' && before.h > 100, `hero: skeleton + spinner while loading, image hidden, space reserved (${Math.round(before.h)} px)`);
  await p.waitForFunction(() => document.querySelector('.hg-pkghead__media img').classList.contains('is-loaded'), null, { timeout: 8000 });
  await p.waitForTimeout(800);
  const after = await p.evaluate(() => { const f = document.querySelector('.hg-pkghead__media'), i = f.querySelector('img'); return { bg: getComputedStyle(f).backgroundImage, op: getComputedStyle(i).opacity, filter: getComputedStyle(i).filter, h: f.getBoundingClientRect().height, src: i.currentSrc }; });
  rec(after.op === '1' && (after.filter === 'none' || after.filter === 'blur(0px)') && after.bg === 'none', 'hero: revealed sharp, skeleton removed');
  rec(Math.abs(after.h - before.h) < 1, 'hero: no layout shift when the image arrives');
  rec(/-1600\.webp$|-960\.webp$/.test(after.src), `hero: responsive WebP chosen (${after.src.split('/').pop()})`);
  await c.close();
  // 2. Listing cards: lazy below the fold, reveal, hover zoom, mobile picks the small variant.
  c = await ctx(); p = await c.newPage(); await p.goto(B + LIST, { waitUntil: 'networkidle' });
  const lazy = await p.$$eval('.hg-rcard img', a => a.map(i => i.getAttribute('loading')));
  rec(lazy.length > 0 && lazy.every(x => x === 'lazy'), `result cards: ${lazy.length} images lazy-loaded`);
  const card = p.locator('.hg-rcard').first(); await card.scrollIntoViewIfNeeded(); await p.waitForTimeout(900);
  await card.hover(); await p.waitForTimeout(500);
  const tf = await card.locator('img').evaluate(i => getComputedStyle(i).transform);
  rec(/matrix\(1\.04/.test(tf), `result card: image zooms on hover (${tf})`);
  const hero = await p.$$eval('.hg-dhero__bg img', a => a.map(i => i.getAttribute('fetchpriority')));
  rec(!hero.length || hero[0] === 'high', 'destination hero image loads with priority');
  await c.close();
  c = await ctx({ vp: { width: 390, height: 844 }, dpr: 1 }); p = await c.newPage(); await p.goto(B + PKG, { waitUntil: 'networkidle' });
  const msrc = await p.$eval('.hg-pkghead__media img', i => i.currentSrc);
  rec(/-480\.webp$/.test(msrc), `mobile: small WebP variant downloaded (${msrc.split('/').pop()})`);
  await c.close();
  // 3. Broken image → branded fallback, no broken icon, title/CTA intact.
  c = await ctx(); p = await c.newPage();
  await p.route(/assets\/img\//, r => r.abort());
  await p.goto(B + PKG, { waitUntil: 'networkidle' }); await p.waitForTimeout(600);
  const fb = await p.$eval('.hg-pkghead__media img', i => ({ src: i.currentSrc, ok: i.naturalWidth > 0, op: getComputedStyle(i).opacity }));
  rec(/image-fallback\.svg$/.test(fb.src) && fb.ok && fb.op === '1', 'broken image: branded fallback shown (no broken icon)');
  rec(await p.locator('h1').isVisible() && await p.locator('a:has-text("Enquire Now"):visible').count() > 0, 'broken image: title and Enquire Now still work');
  await c.close();
  // 4. JavaScript disabled: images simply load and show.
  c = await ctx({ js: false }); p = await c.newPage(); await p.goto(B + PKG, { waitUntil: 'networkidle' });
  const nj = await p.$eval('.hg-pkghead__media img', i => ({ op: getComputedStyle(i).opacity, w: i.naturalWidth }));
  rec(nj.op === '1' && nj.w > 0, 'JavaScript disabled: image visible normally');
  await c.close();
  // 5. Reduced motion: no animation, no spinner.
  c = await ctx({ reduced: true }); p = await c.newPage(); await p.goto(B + PKG, { waitUntil: 'networkidle' });
  const rm = await p.$eval('.hg-pkghead__media img', i => getComputedStyle(i).animationName);
  rec(rm === 'none', 'reduced motion: no reveal animation');
  await c.close();
  // 6. Cached load: second visit reveals immediately (images from cache).
  c = await ctx(); p = await c.newPage(); await p.goto(B + PKG, { waitUntil: 'networkidle' });
  const t0 = Date.now(); await p.reload({ waitUntil: 'load' });
  await p.waitForFunction(() => document.querySelector('.hg-pkghead__media img').classList.contains('is-loaded'));
  rec(Date.now() - t0 < 1500, `cached reload: image revealed after ${Date.now() - t0} ms`);
  // CLS on a card-heavy page
  await p.goto(B + LIST, { waitUntil: 'networkidle' });
  const cls = await p.evaluate(() => new Promise(res => { let v = 0; new PerformanceObserver(l => { for (const e of l.getEntries()) if (!e.hadRecentInput) v += e.value; }).observe({ type: 'layout-shift', buffered: true }); setTimeout(() => res(v), 500); }));
  rec(cls < 0.05, `CLS on destination page: ${cls.toFixed(3)}`);
  await c.close();
  // 7. Homepage destination cards: CTA visible on mobile; hidden until hover on desktop.
  c = await ctx({ vp: { width: 390, height: 844 }, touch: true }); p = await c.newPage(); await p.goto(B + '/', { waitUntil: 'networkidle' });
  const mcta = await p.locator('.hg-dcard__cta').first().evaluate(e => getComputedStyle(e).display !== 'none' && getComputedStyle(e).opacity === '1');
  rec(mcta, 'mobile: destination card CTA visible without hover'); await c.close();
  c = await ctx(); p = await c.newPage(); await p.goto(B + '/', { waitUntil: 'networkidle' });
  const d = p.locator('.hg-dcard').first(); await d.scrollIntoViewIfNeeded();
  const o0 = await d.locator('.hg-dcard__cta').evaluate(e => getComputedStyle(e).opacity);
  await d.hover(); await p.waitForTimeout(450);
  const o1 = await d.locator('.hg-dcard__cta').evaluate(e => getComputedStyle(e).opacity);
  rec(o0 === '0' && o1 === '1', 'desktop: CTA fades in on hover');
  await c.close();
  await b.close(); console.log(fail ? `\n${fail} FAILED` : '\nall passed'); process.exit(fail ? 1 : 0);
})();
