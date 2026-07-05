/**
 * Visual format templates — 6 layouts, 2 scenes each. Picture-first, minimal text.
 */

function esc(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

const FONT = `@import url('https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800;900&display=swap');`;

function baseHtml(body, extraCss = '', dark = false) {
  return `<!DOCTYPE html><html><head><meta charset="utf-8"><style>
${FONT}
* { margin:0; padding:0; box-sizing:border-box; }
body { width:1080px; height:1920px; font-family:'Inter',system-ui,sans-serif;
  background:${dark ? '#0a0a0a' : '#fff'}; overflow:hidden; -webkit-font-smoothing:antialiased; }
${extraCss}
</style></head><body>${body}</body></html>`;
}

function productCards(products, images, opts = {}) {
  const { showCat = false } = opts;
  return products.map((p, i) => `
    <div class="card">
      <div class="img"><img src="${images[i]}" alt=""/></div>
      ${showCat ? `<div class="cat">${esc(p.category)}</div>` : ''}
      <div class="name">${esc(p.name)}</div>
    </div>`).join('');
}

function ctaBlock(copy) {
  return `<div class="cta">
    <div class="cta-line">${esc(copy.ctaLine)}</div>
    <div class="cta-action">${esc(copy.ctaAction)}</div>
    <div class="cta-url">${esc(copy.ctaUrl)}</div>
  </div>`;
}

function hookBlock(copy, variant = 'red') {
  if (variant === 'minimal') {
    return `<div class="hook-min"><span class="pill">${esc(copy.hook)}</span><span class="sub">${esc(copy.hookSub)}</span></div>`;
  }
  return `<div class="hook"><div class="h1">${esc(copy.hook)}</div><div class="h2">${esc(copy.hookSub)}</div></div>`;
}

const cleanRow = {
  scene1(copy, images) {
    return baseHtml(`<div class="wrap">${hookBlock(copy)}<div class="grid3">${productCards(copy.products, images)}</div></div>`, `
.wrap { display:flex; flex-direction:column; height:100%; padding:32px; gap:24px; }
.hook { background:#B91C1C; border-radius:20px; padding:28px; text-align:center; }
.h1 { font-size:48px; font-weight:900; color:#fff; }
.h2 { font-size:32px; font-weight:700; color:#FFE082; margin-top:8px; }
.grid3 { flex:1; display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; min-height:0; }
.card { display:flex; flex-direction:column; background:#fafafa; border-radius:18px; border:1px solid #eee; overflow:hidden; }
.img { flex:1; min-height:0; display:flex; align-items:center; justify-content:center; padding:16px; }
.img img { max-width:100%; max-height:100%; object-fit:contain; }
.name { font-size:20px; font-weight:800; text-align:center; padding:14px 8px; color:#111; line-height:1.2; }
    `);
  },
  scene2(copy, images) {
    return baseHtml(`<div class="wrap"><div class="grid3 big">${productCards(copy.products, images)}</div>${ctaBlock(copy)}</div>`, `
.wrap { display:flex; flex-direction:column; height:100%; padding:32px; gap:20px; }
.grid3 { flex:1; display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; min-height:0; }
.grid3.big .img { min-height:520px; }
.card { display:flex; flex-direction:column; background:#fafafa; border-radius:18px; overflow:hidden; }
.img { flex:1; display:flex; align-items:center; justify-content:center; padding:12px; }
.img img { max-width:100%; max-height:100%; object-fit:contain; }
.name { font-size:18px; font-weight:800; text-align:center; padding:12px 6px; color:#111; }
.cta { background:#111; border-radius:20px; padding:36px; text-align:center; }
.cta-line { font-size:40px; font-weight:900; color:#fff; }
.cta-action { font-size:36px; font-weight:800; color:#FFE082; margin-top:8px; }
.cta-url { font-size:28px; font-weight:600; color:#aaa; margin-top:8px; }
    `);
  },
};

const heroDuo = {
  scene1(copy, images) {
    const [hero, ...rest] = copy.products;
    return baseHtml(`<div class="wrap">${hookBlock(copy, 'minimal')}
      <div class="hero"><img src="${images[0]}" alt=""/></div>
      <div class="hero-name">${esc(hero.name)}</div>
      <div class="duo">${rest.map((p, i) => `<div class="d-card"><img src="${images[i+1]}" alt=""/><div class="dn">${esc(p.name)}</div></div>`).join('')}</div>
    </div>`, `
.wrap { padding:28px; display:flex; flex-direction:column; height:100%; gap:16px; }
.hook-min { text-align:center; }
.pill { background:#111; color:#fff; font-size:26px; font-weight:800; padding:12px 28px; border-radius:999px; }
.sub { display:block; font-size:30px; font-weight:700; color:#444; margin-top:12px; }
.hero { flex:1; min-height:0; background:#f5f5f5; border-radius:24px; display:flex; align-items:center; justify-content:center; padding:24px; }
.hero img { max-width:95%; max-height:95%; object-fit:contain; }
.hero-name { font-size:44px; font-weight:900; text-align:center; color:#111; }
.duo { display:grid; grid-template-columns:1fr 1fr; gap:14px; height:340px; }
.d-card { background:#fafafa; border-radius:16px; display:flex; flex-direction:column; align-items:center; padding:12px; }
.d-card img { max-height:240px; object-fit:contain; }
.dn { font-size:18px; font-weight:800; text-align:center; margin-top:8px; color:#111; }
    `);
  },
  scene2: cleanRow.scene2,
};

const darkLuxe = {
  scene1(copy, images) {
    return baseHtml(`<div class="wrap">${hookBlock(copy)}<div class="grid3">${productCards(copy.products, images, { showCat: true })}</div></div>`, `
.wrap { display:flex; flex-direction:column; height:100%; padding:32px; gap:20px; }
.hook { background:#B91C1C; border-radius:20px; padding:24px; text-align:center; }
.h1 { font-size:44px; font-weight:900; color:#fff; }
.h2 { font-size:28px; font-weight:700; color:#FFE082; margin-top:6px; }
.grid3 { flex:1; display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; min-height:0; }
.card { background:#fff; border-radius:16px; display:flex; flex-direction:column; overflow:hidden; }
.img { flex:1; min-height:0; display:flex; align-items:center; justify-content:center; padding:14px; }
.img img { max-width:100%; max-height:100%; object-fit:contain; }
.cat { font-size:14px; font-weight:700; color:#B91C1C; text-align:center; text-transform:uppercase; letter-spacing:0.06em; padding-top:8px; }
.name { font-size:18px; font-weight:800; text-align:center; padding:8px 6px 14px; color:#111; line-height:1.15; }
    `, true);
  },
  scene2(copy, images) {
    return baseHtml(`<div class="wrap">${ctaBlock(copy)}<div class="grid3">${productCards(copy.products, images)}</div></div>`, `
.wrap { display:flex; flex-direction:column; height:100%; padding:32px; gap:20px; }
.cta { background:#B91C1C; border-radius:20px; padding:32px; text-align:center; flex-shrink:0; }
.cta-line { font-size:38px; font-weight:900; color:#fff; }
.cta-action { font-size:34px; font-weight:800; color:#FFE082; margin-top:6px; }
.cta-url { font-size:26px; color:rgba(255,255,255,0.8); margin-top:6px; }
.grid3 { flex:1; display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }
.card { background:#fff; border-radius:16px; overflow:hidden; display:flex; flex-direction:column; }
.img { flex:1; min-height:400px; display:flex; align-items:center; justify-content:center; padding:12px; }
.img img { max-width:100%; max-height:100%; object-fit:contain; }
.name { font-size:17px; font-weight:800; text-align:center; padding:10px; color:#111; }
    `, true);
  },
};

const fullBleed = {
  scene1(copy, images) {
    return baseHtml(`<div class="wrap"><div class="strip">${esc(copy.hook)} · ${esc(copy.hookSub)}</div>
      <div class="grid3">${copy.products.map((p, i) => `<div class="bleed"><img src="${images[i]}" alt=""/></div>`).join('')}</div></div>`, `
.wrap { display:flex; flex-direction:column; height:100%; }
.strip { background:#B91C1C; color:#fff; font-size:32px; font-weight:800; text-align:center; padding:22px 16px; }
.grid3 { flex:1; display:grid; grid-template-columns:1fr 1fr 1fr; gap:4px; min-height:0; background:#111; }
.bleed { background:#fff; display:flex; align-items:center; justify-content:center; padding:8px; }
.bleed img { width:100%; height:100%; object-fit:contain; }
    `);
  },
  scene2(copy, images) {
    return baseHtml(`<div class="wrap"><div class="grid3">${copy.products.map((p, i) =>
      `<div class="bleed"><img src="${images[i]}" alt=""/><div class="lbl">${esc(p.name)}</div></div>`).join('')}</div>${ctaBlock(copy)}</div>`, `
.wrap { display:flex; flex-direction:column; height:100%; }
.grid3 { flex:1; display:grid; grid-template-columns:1fr 1fr 1fr; gap:4px; min-height:0; }
.bleed { background:#f8f8f8; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:10px; }
.bleed img { max-width:100%; max-height:85%; object-fit:contain; }
.lbl { font-size:16px; font-weight:800; text-align:center; color:#111; margin-top:8px; line-height:1.15; padding:0 4px; }
.cta { background:#111; padding:40px; text-align:center; }
.cta-line { font-size:42px; font-weight:900; color:#fff; }
.cta-action { font-size:36px; font-weight:800; color:#FFE082; margin-top:8px; }
.cta-url { font-size:28px; color:#888; margin-top:6px; }
    `);
  },
};

const stackCards = {
  scene1(copy, images) {
    const rows = copy.products.map((p, i) => `
      <div class="row"><div class="thumb"><img src="${images[i]}" alt=""/></div>
        <div class="meta"><div class="cat">${esc(p.category)}</div><div class="name">${esc(p.name)}</div></div></div>`).join('');
    return baseHtml(`<div class="wrap">${hookBlock(copy, 'minimal')}${rows}</div>`, `
.wrap { padding:28px 32px; display:flex; flex-direction:column; gap:14px; height:100%; }
.hook-min { text-align:center; margin-bottom:8px; }
.pill { background:#B91C1C; color:#fff; font-size:24px; font-weight:800; padding:10px 24px; border-radius:999px; }
.sub { display:block; font-size:28px; font-weight:700; color:#333; margin-top:10px; }
.row { flex:1; display:flex; gap:20px; background:#fafafa; border-radius:20px; padding:16px; border:1px solid #eee; align-items:center; min-height:0; }
.thumb { width:280px; height:100%; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:#fff; border-radius:14px; }
.thumb img { max-width:92%; max-height:92%; object-fit:contain; }
.cat { font-size:22px; font-weight:700; color:#B91C1C; text-transform:uppercase; }
.name { font-size:36px; font-weight:900; color:#111; line-height:1.15; margin-top:8px; }
    `);
  },
  scene2(copy) {
    return baseHtml(`<div class="wrap">${ctaBlock(copy)}</div>`, `
.wrap { display:flex; align-items:center; justify-content:center; height:100%; padding:48px; background:linear-gradient(180deg,#fafafa,#fff); }
.cta { width:100%; background:#B91C1C; border-radius:28px; padding:80px 48px; text-align:center; }
.cta-line { font-size:56px; font-weight:900; color:#fff; line-height:1.1; }
.cta-action { font-size:48px; font-weight:800; color:#FFE082; margin-top:20px; }
.cta-url { font-size:36px; font-weight:600; color:rgba(255,255,255,0.85); margin-top:16px; }
    `);
  },
};

const magazineSplit = {
  scene1(copy, images) {
    return baseHtml(`<div class="wrap"><div class="left"><div class="brand">Home Value</div>
      <div class="h1">${esc(copy.hook)}</div><div class="h2">${esc(copy.hookSub)}</div>
      <div class="tags">${esc(copy.categoryTags)}</div></div>
      <div class="right">${copy.products.map((p, i) =>
        `<div class="item"><img src="${images[i]}" alt=""/><div class="n">${esc(p.name)}</div></div>`).join('')}</div></div>`, `
.wrap { display:flex; height:100%; }
.left { width:380px; background:#111; color:#fff; padding:40px 32px; display:flex; flex-direction:column; justify-content:center; }
.brand { font-size:28px; font-weight:800; color:#B91C1C; margin-bottom:32px; }
.h1 { font-size:44px; font-weight:900; line-height:1.1; }
.h2 { font-size:28px; font-weight:600; color:#ccc; margin-top:16px; line-height:1.3; }
.tags { font-size:18px; font-weight:600; color:#888; margin-top:24px; line-height:1.4; }
.right { flex:1; display:flex; flex-direction:column; gap:8px; padding:8px; background:#f0f0f0; }
.item { flex:1; background:#fff; border-radius:12px; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:12px; min-height:0; }
.item img { max-width:90%; max-height:75%; object-fit:contain; }
.n { font-size:16px; font-weight:800; text-align:center; margin-top:6px; color:#111; }
    `);
  },
  scene2: fullBleed.scene2,
};

export const FORMAT_RENDERERS = {
  'clean-row': cleanRow,
  'hero-duo': heroDuo,
  'dark-luxe': darkLuxe,
  'full-bleed': fullBleed,
  'stack-cards': stackCards,
  'magazine-split': magazineSplit,
};

export function renderScene(formatId, sceneNum, copy, productImages) {
  const fmt = FORMAT_RENDERERS[formatId];
  if (!fmt) throw new Error(`Unknown format: ${formatId}`);
  const fn = sceneNum === 1 ? fmt.scene1 : fmt.scene2;
  return fn(copy, productImages);
}
