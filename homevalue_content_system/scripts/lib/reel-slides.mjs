/**
 * Reel slide templates — 1080×1920, carousel-style (cover → products → CTA).
 */

function esc(s) {
  return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

const FONT = `@import url('https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800;900&display=swap');`;
const W = 1080;
const H = 1920;

function baseHtml(body, css, dark = false) {
  return `<!DOCTYPE html><html><head><meta charset="utf-8"><style>
${FONT}
* { margin:0; padding:0; box-sizing:border-box; }
body { width:${W}px; height:${H}px; font-family:'Inter',system-ui,sans-serif;
  background:${dark ? '#0a0a0a' : '#fff'}; overflow:hidden; -webkit-font-smoothing:antialiased; }
${css}
</style></head><body>${body}</body></html>`;
}

export function renderCoverSlide(copy, thumbImages = []) {
  const thumbs = thumbImages.slice(0, 3).map((src, i) =>
    `<div class="thumb t${i}"><img src="${src}" alt=""/></div>`
  ).join('');

  return baseHtml(`<div class="wrap">
    <div class="brand">Home Value</div>
    <div class="title">${esc(copy.cover.title)}</div>
    <div class="sub">${esc(copy.cover.subtitle)}</div>
    <div class="mosaic">${thumbs}</div>
    <div class="count">${copy.products.length} wholesale picks</div>
    <div class="swipe">${esc(copy.cover.swipe)}</div>
  </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center;
  padding:72px 48px; background:linear-gradient(160deg,#B91C1C 0%,#7f1d1d 55%,#111 100%); color:#fff; text-align:center; }
.brand { font-size:34px; font-weight:800; letter-spacing:0.06em; opacity:0.9; margin-bottom:52px; }
.title { font-size:72px; font-weight:900; line-height:0.98; letter-spacing:-0.02em; max-width:960px; }
.sub { font-size:38px; font-weight:600; margin-top:28px; opacity:0.88; line-height:1.3; }
.mosaic { display:flex; gap:18px; margin:64px 0 36px; justify-content:center; align-items:center; }
.thumb { width:220px; height:220px; background:#fff; border-radius:26px; padding:18px;
  display:flex; align-items:center; justify-content:center; box-shadow:0 24px 48px rgba(0,0,0,0.25); }
.thumb.t1 { transform:scale(1.08); }
.thumb img { max-width:100%; max-height:100%; object-fit:contain; }
.count { font-size:26px; font-weight:700; opacity:0.8; }
.swipe { margin-top:auto; font-size:30px; font-weight:800; background:rgba(255,255,255,0.15);
  padding:18px 44px; border-radius:999px; backdrop-filter:blur(8px); }
  `);
}

export function renderCtaSlide(copy) {
  return baseHtml(`<div class="wrap">
    <div class="icon">🏪</div>
    <div class="line">${esc(copy.cta.line)}</div>
    <div class="action">${esc(copy.cta.action)}</div>
    <div class="url">${esc(copy.cta.url)}</div>
    <div class="foot">${esc(copy.category)} · 2,500+ SKUs</div>
  </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center;
  padding:72px 56px; background:#111; color:#fff; text-align:center; }
.icon { font-size:88px; margin-bottom:36px; }
.line { font-size:76px; font-weight:900; line-height:1.05; }
.action { font-size:52px; font-weight:800; color:#FFE082; margin-top:32px; }
.url { font-size:36px; font-weight:600; color:#aaa; margin-top:28px; }
.foot { margin-top:auto; font-size:28px; font-weight:700; color:#666; letter-spacing:0.04em; text-transform:uppercase; }
  `, true);
}

const productStyles = {
  clean(product, image) {
    return baseHtml(`<div class="wrap">
      <div class="badge">${esc(product.category)}</div>
      <div class="stage"><img src="${image}" alt=""/></div>
      <div class="name">${esc(product.name)}</div>
      <div class="pack">${esc(product.pack)}</div>
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; padding:56px 44px 64px; background:#fafafa; }
.badge { text-align:center; font-size:26px; font-weight:800; color:#B91C1C; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:28px; }
.stage { flex:1; min-height:0; display:flex; align-items:center; justify-content:center; background:#fff; border-radius:36px; padding:40px; box-shadow:0 20px 56px rgba(0,0,0,0.07); }
.stage img { max-width:100%; max-height:100%; object-fit:contain; filter:drop-shadow(0 16px 32px rgba(0,0,0,0.1)); }
.name { font-size:58px; font-weight:900; color:#111; text-align:center; line-height:1.08; margin-top:36px; }
.pack { font-size:30px; font-weight:600; color:#888; text-align:center; margin-top:14px; }
    `);
  },

  dark(product, image) {
    return baseHtml(`<div class="wrap">
      <div class="card"><img src="${image}" alt=""/></div>
      <div class="meta">
        <div class="badge">${esc(product.category)}</div>
        <div class="name">${esc(product.name)}</div>
        <div class="pack">${esc(product.pack)}</div>
      </div>
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; padding:48px 40px 60px; gap:32px; background:#0a0a0a; }
.card { flex:1; min-height:0; background:#fff; border-radius:32px; display:flex; align-items:center; justify-content:center; padding:44px; box-shadow:0 36px 72px rgba(0,0,0,0.45); }
.card img { max-width:100%; max-height:100%; object-fit:contain; }
.meta { text-align:center; }
.badge { font-size:24px; font-weight:700; color:#B91C1C; text-transform:uppercase; letter-spacing:0.08em; }
.name { font-size:54px; font-weight:900; color:#fff; line-height:1.1; margin-top:14px; }
.pack { font-size:28px; font-weight:600; color:#666; margin-top:12px; }
    `, true);
  },

  gradient(product, image) {
    return baseHtml(`<div class="wrap">
      <div class="badge">${esc(product.category)}</div>
      <div class="stage"><img src="${image}" alt=""/></div>
      <div class="name">${esc(product.name)}</div>
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center;
  padding:56px 44px; background:linear-gradient(165deg,#fff1f2 0%,#fff 40%,#f8fafc 100%); text-align:center; }
.badge { font-size:26px; font-weight:800; color:#B91C1C; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:24px; }
.stage { flex:1; width:100%; min-height:0; display:flex; align-items:center; justify-content:center; padding:20px; }
.stage img { max-width:98%; max-height:98%; object-fit:contain; filter:drop-shadow(0 28px 56px rgba(185,28,28,0.14)); }
.name { font-size:60px; font-weight:900; color:#111; line-height:1.08; margin-top:28px; }
    `);
  },

  spotlight(product, image) {
    return baseHtml(`<div class="wrap">
      <div class="glow"><img src="${image}" alt=""/></div>
      <div class="info">
        <div class="badge">${esc(product.category)}</div>
        <div class="name">${esc(product.name)}</div>
      </div>
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; background:#111; }
.glow { flex:1; min-height:0; display:flex; align-items:center; justify-content:center; padding:56px 40px;
  background:radial-gradient(circle at 50% 42%, rgba(255,255,255,0.14) 0%, rgba(255,255,255,0.03) 40%, transparent 70%); }
.glow img { max-width:100%; max-height:100%; object-fit:contain; filter:drop-shadow(0 36px 72px rgba(0,0,0,0.5)); }
.info { text-align:center; padding:40px 44px 64px; }
.badge { font-size:24px; font-weight:700; color:#B91C1C; text-transform:uppercase; letter-spacing:0.08em; }
.name { font-size:56px; font-weight:900; color:#fff; line-height:1.08; margin-top:16px; }
    `, true);
  },

  minimal(product, image) {
    return baseHtml(`<div class="wrap">
      <div class="fill"><img src="${image}" alt=""/></div>
      <div class="bar">
        <div class="badge">${esc(product.category)}</div>
        <div class="name">${esc(product.name)}</div>
      </div>
    </div>`, `
.wrap { position:relative; height:100%; background:#fff; }
.fill { height:100%; display:flex; align-items:center; justify-content:center; padding:100px 36px 320px; }
.fill img { width:100%; height:100%; object-fit:contain; }
.bar { position:absolute; left:0; right:0; bottom:0; padding:48px 48px 72px;
  background:linear-gradient(transparent, rgba(255,255,255,0.97) 35%); }
.badge { font-size:26px; font-weight:800; color:#B91C1C; text-transform:uppercase; margin-bottom:12px; }
.name { font-size:58px; font-weight:900; color:#111; line-height:1.08; }
    `);
  },
};

const STYLE_ORDER = ['clean', 'dark', 'gradient', 'spotlight', 'minimal'];

export function renderProductSlide(product, image, styleId) {
  const fn = productStyles[styleId] || productStyles.clean;
  return fn(product, image);
}

export function styleForIndex(index) {
  return STYLE_ORDER[index % STYLE_ORDER.length];
}
