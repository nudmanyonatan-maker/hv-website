/**
 * Reel slide templates — 1080×1920, picture-first (product fills the frame).
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
    <div class="top">
      <div class="brand">Home Value</div>
      <div class="title">${esc(copy.cover.title)}</div>
    </div>
    <div class="mosaic">${thumbs}</div>
    <div class="bottom">
      <div class="swipe">${esc(copy.cover.swipe)}</div>
    </div>
  </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; background:linear-gradient(160deg,#B91C1C 0%,#7f1d1d 50%,#111 100%); color:#fff; }
.top { text-align:center; padding:48px 40px 24px; flex-shrink:0; }
.brand { font-size:28px; font-weight:800; letter-spacing:0.06em; opacity:0.85; margin-bottom:16px; }
.title { font-size:56px; font-weight:900; line-height:1; letter-spacing:-0.02em; }
.mosaic { flex:1; min-height:0; display:flex; gap:20px; padding:16px 28px; align-items:center; justify-content:center; }
.thumb { flex:1; max-width:320px; height:100%; max-height:1200px; background:#fff; border-radius:28px; padding:12px;
  display:flex; align-items:center; justify-content:center; box-shadow:0 24px 48px rgba(0,0,0,0.3); }
.thumb.t1 { transform:scale(1.04); }
.thumb img { width:100%; height:100%; object-fit:contain; }
.bottom { flex-shrink:0; text-align:center; padding:32px 40px 56px; }
.swipe { font-size:28px; font-weight:800; background:rgba(255,255,255,0.15);
  padding:16px 40px; border-radius:999px; display:inline-block; }
  `);
}

export function renderCtaSlide(copy) {
  return baseHtml(`<div class="wrap">
    <div class="line">${esc(copy.cta.line)}</div>
    <div class="action">${esc(copy.cta.action)}</div>
    <div class="url">${esc(copy.cta.url)}</div>
  </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center;
  padding:64px 48px; background:#111; color:#fff; text-align:center; }
.line { font-size:72px; font-weight:900; line-height:1.05; }
.action { font-size:48px; font-weight:800; color:#FFE082; margin-top:28px; }
.url { font-size:32px; font-weight:600; color:#888; margin-top:24px; }
  `, true);
}

/** Shared: image stage eats ~88% of frame, slim text bar below. */
const IMG = 'width:100%; height:100%; object-fit:contain;';
const STAGE = 'flex:1; min-height:0; width:100%; display:flex; align-items:center; justify-content:center;';

const productStyles = {
  clean(product, image) {
    return baseHtml(`<div class="wrap">
      <div class="stage"><img src="${image}" alt=""/></div>
      <div class="bar">
        <div class="name">${esc(product.name)}</div>
      </div>
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; background:#fff; padding:20px 16px 0; }
.stage { ${STAGE} padding:8px; }
.stage img { ${IMG} filter:drop-shadow(0 20px 40px rgba(0,0,0,0.12)); }
.bar { flex-shrink:0; text-align:center; padding:20px 24px 36px; background:#fafafa; border-top:3px solid #B91C1C; }
.name { font-size:40px; font-weight:900; color:#111; line-height:1.1; }
    `);
  },

  dark(product, image) {
    return baseHtml(`<div class="wrap">
      <div class="stage"><img src="${image}" alt=""/></div>
      <div class="bar">
        <div class="name">${esc(product.name)}</div>
      </div>
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; background:#0a0a0a; padding:16px 12px 0; }
.stage { ${STAGE} padding:4px; }
.stage img { ${IMG} filter:drop-shadow(0 24px 48px rgba(255,255,255,0.08)); }
.bar { flex-shrink:0; text-align:center; padding:18px 24px 32px; }
.name { font-size:38px; font-weight:900; color:#fff; line-height:1.1; }
    `, true);
  },

  gradient(product, image) {
    return baseHtml(`<div class="wrap">
      <div class="stage"><img src="${image}" alt=""/></div>
      <div class="bar"><div class="name">${esc(product.name)}</div></div>
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; background:linear-gradient(180deg,#fff5f5,#fff); padding:16px 12px 0; }
.stage { ${STAGE} padding:4px; }
.stage img { ${IMG} filter:drop-shadow(0 24px 48px rgba(185,28,28,0.15)); }
.bar { flex-shrink:0; text-align:center; padding:18px 24px 32px; }
.name { font-size:40px; font-weight:900; color:#111; line-height:1.1; }
    `);
  },

  spotlight(product, image) {
    return baseHtml(`<div class="wrap">
      <div class="stage"><img src="${image}" alt=""/></div>
      <div class="bar"><div class="name">${esc(product.name)}</div></div>
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; background:#111; }
.stage { ${STAGE} padding:12px 8px;
  background:radial-gradient(circle at 50% 45%, rgba(255,255,255,0.12) 0%, transparent 55%); }
.stage img { ${IMG} filter:drop-shadow(0 32px 64px rgba(0,0,0,0.45)); }
.bar { flex-shrink:0; text-align:center; padding:16px 24px 36px; }
.name { font-size:40px; font-weight:900; color:#fff; line-height:1.1; }
    `, true);
  },

  minimal(product, image) {
    return baseHtml(`<div class="wrap">
      <div class="fill"><img src="${image}" alt=""/></div>
      <div class="bar"><div class="name">${esc(product.name)}</div></div>
    </div>`, `
.wrap { position:relative; height:100%; background:#fff; display:flex; flex-direction:column; }
.fill { flex:1; min-height:0; display:flex; align-items:center; justify-content:center; padding:24px 12px 8px; }
.fill img { ${IMG} }
.bar { flex-shrink:0; padding:16px 28px 40px; text-align:center;
  background:linear-gradient(transparent, rgba(255,255,255,0.98) 20%); }
.name { font-size:40px; font-weight:900; color:#111; line-height:1.1; }
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
