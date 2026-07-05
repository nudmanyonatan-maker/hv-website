/**
 * Instagram carousel slide templates — 1080×1350 (4:5).
 * Cover + rotating product layouts + CTA.
 */

function esc(s) {
  return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

const FONT = `@import url('https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800;900&display=swap');`;

const W = 1080;
const H = 1350;

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
    <div class="cats">${esc(copy.cover.categories)}</div>
    <div class="swipe">${esc(copy.cover.swipe)}</div>
  </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center;
  padding:64px 48px; background:linear-gradient(160deg,#B91C1C 0%,#7f1d1d 55%,#111 100%); color:#fff; text-align:center; }
.brand { font-size:32px; font-weight:800; letter-spacing:0.06em; opacity:0.9; margin-bottom:48px; }
.title { font-size:88px; font-weight:900; line-height:0.95; letter-spacing:-0.02em; }
.sub { font-size:36px; font-weight:600; margin-top:24px; opacity:0.88; line-height:1.3; }
.mosaic { display:flex; gap:16px; margin:56px 0 32px; justify-content:center; align-items:center; }
.thumb { width:200px; height:200px; background:#fff; border-radius:24px; padding:16px;
  display:flex; align-items:center; justify-content:center; box-shadow:0 20px 40px rgba(0,0,0,0.25); }
.thumb.t1 { transform:scale(1.08); }
.thumb img { max-width:100%; max-height:100%; object-fit:contain; }
.cats { font-size:22px; font-weight:600; opacity:0.75; line-height:1.5; max-width:900px; }
.swipe { margin-top:auto; font-size:28px; font-weight:800; background:rgba(255,255,255,0.15);
  padding:16px 40px; border-radius:999px; backdrop-filter:blur(8px); }
  `);
}

export function renderCtaSlide(copy) {
  return baseHtml(`<div class="wrap">
    <div class="icon">🏪</div>
    <div class="line">${esc(copy.cta.line)}</div>
    <div class="action">${esc(copy.cta.action)}</div>
    <div class="url">${esc(copy.cta.url)}</div>
    <div class="foot">2,500+ wholesale SKUs</div>
  </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center;
  padding:64px 56px; background:#111; color:#fff; text-align:center; }
.icon { font-size:80px; margin-bottom:32px; }
.line { font-size:72px; font-weight:900; line-height:1.05; }
.action { font-size:48px; font-weight:800; color:#FFE082; margin-top:28px; }
.url { font-size:34px; font-weight:600; color:#aaa; margin-top:24px; }
.foot { margin-top:auto; font-size:26px; font-weight:700; color:#666; letter-spacing:0.04em; text-transform:uppercase; }
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
.wrap { height:100%; display:flex; flex-direction:column; padding:48px 40px 56px; background:#fafafa; }
.badge { text-align:center; font-size:24px; font-weight:800; color:#B91C1C; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:24px; }
.stage { flex:1; min-height:0; display:flex; align-items:center; justify-content:center; background:#fff; border-radius:32px; padding:32px; box-shadow:0 16px 48px rgba(0,0,0,0.06); }
.stage img { max-width:100%; max-height:100%; object-fit:contain; filter:drop-shadow(0 12px 24px rgba(0,0,0,0.08)); }
.name { font-size:52px; font-weight:900; color:#111; text-align:center; line-height:1.08; margin-top:32px; }
.pack { font-size:28px; font-weight:600; color:#888; text-align:center; margin-top:12px; }
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
.wrap { height:100%; display:flex; flex-direction:column; padding:40px 36px 52px; gap:28px; background:#0a0a0a; }
.card { flex:1; min-height:0; background:#fff; border-radius:28px; display:flex; align-items:center; justify-content:center; padding:36px; box-shadow:0 32px 64px rgba(0,0,0,0.4); }
.card img { max-width:100%; max-height:100%; object-fit:contain; }
.meta { text-align:center; }
.badge { font-size:22px; font-weight:700; color:#B91C1C; text-transform:uppercase; letter-spacing:0.08em; }
.name { font-size:48px; font-weight:900; color:#fff; line-height:1.1; margin-top:12px; }
.pack { font-size:26px; font-weight:600; color:#666; margin-top:10px; }
    `, true);
  },

  gradient(product, image) {
    return baseHtml(`<div class="wrap">
      <div class="badge">${esc(product.category)}</div>
      <div class="stage"><img src="${image}" alt=""/></div>
      <div class="name">${esc(product.name)}</div>
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center;
  padding:48px 40px; background:linear-gradient(165deg,#fff1f2 0%,#fff 40%,#f8fafc 100%); text-align:center; }
.badge { font-size:24px; font-weight:800; color:#B91C1C; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:20px; }
.stage { flex:1; width:100%; min-height:0; display:flex; align-items:center; justify-content:center; padding:16px; }
.stage img { max-width:98%; max-height:98%; object-fit:contain; filter:drop-shadow(0 24px 48px rgba(185,28,28,0.12)); }
.name { font-size:54px; font-weight:900; color:#111; line-height:1.08; margin-top:24px; }
    `);
  },

  editorial(product, image) {
    return baseHtml(`<div class="wrap">
      <div class="side">
        <div class="num">HV</div>
        <div class="cat">${esc(product.category)}</div>
        <div class="pack">${esc(product.pack)}</div>
      </div>
      <div class="main">
        <div class="stage"><img src="${image}" alt=""/></div>
        <div class="name">${esc(product.name)}</div>
      </div>
    </div>`, `
.wrap { display:flex; height:100%; }
.side { width:260px; background:#B91C1C; color:#fff; padding:48px 28px; display:flex; flex-direction:column; justify-content:center; }
.num { font-size:36px; font-weight:900; opacity:0.9; margin-bottom:40px; }
.cat { font-size:28px; font-weight:800; line-height:1.2; text-transform:uppercase; letter-spacing:0.04em; }
.pack { font-size:22px; font-weight:600; margin-top:24px; opacity:0.85; line-height:1.35; }
.main { flex:1; display:flex; flex-direction:column; background:#fff; padding:36px 32px 48px; }
.stage { flex:1; min-height:0; display:flex; align-items:center; justify-content:center; }
.stage img { max-width:100%; max-height:100%; object-fit:contain; }
.name { font-size:44px; font-weight:900; color:#111; text-align:center; line-height:1.1; margin-top:20px; }
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
.glow { flex:1; min-height:0; display:flex; align-items:center; justify-content:center; padding:48px 36px;
  background:radial-gradient(circle at 50% 42%, rgba(255,255,255,0.14) 0%, rgba(255,255,255,0.03) 40%, transparent 70%); }
.glow img { max-width:100%; max-height:100%; object-fit:contain; filter:drop-shadow(0 32px 64px rgba(0,0,0,0.5)); }
.info { text-align:center; padding:36px 40px 52px; }
.badge { font-size:22px; font-weight:700; color:#B91C1C; text-transform:uppercase; letter-spacing:0.08em; }
.name { font-size:50px; font-weight:900; color:#fff; line-height:1.08; margin-top:14px; }
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
.fill { height:100%; display:flex; align-items:center; justify-content:center; padding:80px 32px 260px; }
.fill img { width:100%; height:100%; object-fit:contain; }
.bar { position:absolute; left:0; right:0; bottom:0; padding:40px 44px 52px;
  background:linear-gradient(transparent, rgba(255,255,255,0.97) 35%); }
.badge { font-size:24px; font-weight:800; color:#B91C1C; text-transform:uppercase; margin-bottom:10px; }
.name { font-size:52px; font-weight:900; color:#111; line-height:1.08; }
    `);
  },
};

const STYLE_ORDER = ['clean', 'dark', 'gradient', 'editorial', 'spotlight', 'minimal'];

export function renderProductSlide(product, image, styleId) {
  const fn = productStyles[styleId] || productStyles.clean;
  return fn(product, image);
}

export function styleForIndex(index) {
  return STYLE_ORDER[index % STYLE_ORDER.length];
}

export const SLIDE_STYLES = STYLE_ORDER;
