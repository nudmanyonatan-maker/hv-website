/**
 * Shared product hero slide — full product visible, large and readable.
 * Used for carousel slides and single-image posts (1080×1350).
 */

function esc(s) {
  return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

const FONT = `@import url('https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800;900&display=swap');`;

export const FEED_W = 1080;
export const FEED_H = 1350;

/** Product image fills the card but stays contained — whole SKU visible. */
export function renderProductHeroSlide(product, imageDataUrl, { width = FEED_W, height = FEED_H, dark = false } = {}) {
  const bg = dark ? '#0a0a0a' : '#f3f4f6';
  const cardBg = '#ffffff';
  const textColor = dark ? '#fff' : '#111';
  const subColor = dark ? '#9ca3af' : '#6b7280';

  return `<!DOCTYPE html><html><head><meta charset="utf-8"><style>
${FONT}
* { margin:0; padding:0; box-sizing:border-box; }
body {
  width:${width}px; height:${height}px;
  font-family:'Inter',system-ui,sans-serif;
  background:${bg}; overflow:hidden;
  -webkit-font-smoothing:antialiased;
}
.wrap {
  height:100%; display:flex; flex-direction:column;
  padding:28px 32px 32px;
}
.badge {
  text-align:center; flex-shrink:0;
  font-size:22px; font-weight:800; color:#B91C1C;
  text-transform:uppercase; letter-spacing:0.12em;
  margin-bottom:16px;
}
.card {
  flex:1; min-height:0;
  background:${cardBg};
  border-radius:32px;
  border:2px solid ${dark ? '#222' : '#e5e7eb'};
  box-shadow:0 24px 64px rgba(0,0,0,${dark ? '0.45' : '0.08'});
  display:flex; align-items:center; justify-content:center;
  padding:48px 40px;
}
.card img {
  max-width:100%;
  max-height:100%;
  width:auto;
  height:auto;
  object-fit:contain;
  object-position:center center;
}
.meta {
  flex-shrink:0;
  text-align:center;
  margin-top:24px;
  padding:0 12px;
}
.name {
  font-size:46px;
  font-weight:900;
  color:${textColor};
  line-height:1.08;
  letter-spacing:-0.01em;
}
.pack {
  font-size:26px;
  font-weight:600;
  color:${subColor};
  margin-top:10px;
}
.brand {
  margin-top:14px;
  font-size:20px;
  font-weight:700;
  color:#B91C1C;
  letter-spacing:0.08em;
  text-transform:uppercase;
}
</style></head><body>
<div class="wrap">
  <div class="badge">${esc(product.category)}</div>
  <div class="card"><img src="${imageDataUrl}" alt=""/></div>
  <div class="meta">
    <div class="name">${esc(product.name)}</div>
    ${product.pack ? `<div class="pack">${esc(product.pack)}</div>` : ''}
    <div class="brand">Home Value Wholesale</div>
  </div>
</div>
</body></html>`;
}

export function renderCoverSlide(copy, thumbImages = [], { width = FEED_W, height = FEED_H } = {}) {
  const thumbs = thumbImages.slice(0, 3).map((src, i) =>
    `<div class="thumb t${i}"><img src="${src}" alt=""/></div>`
  ).join('');

  return `<!DOCTYPE html><html><head><meta charset="utf-8"><style>
${FONT}
* { margin:0; padding:0; box-sizing:border-box; }
body { width:${width}px; height:${height}px; font-family:'Inter',system-ui,sans-serif; overflow:hidden; }
.wrap { height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center;
  padding:56px 40px; background:linear-gradient(160deg,#B91C1C 0%,#7f1d1d 55%,#111 100%); color:#fff; text-align:center; }
.brand { font-size:28px; font-weight:800; letter-spacing:0.06em; opacity:0.9; margin-bottom:32px; }
.title { font-size:64px; font-weight:900; line-height:1; letter-spacing:-0.02em; }
.sub { font-size:32px; font-weight:600; margin-top:20px; opacity:0.88; }
.mosaic { display:flex; gap:14px; margin:40px 0 28px; justify-content:center; align-items:center; }
.thumb { width:180px; height:180px; background:#fff; border-radius:20px; padding:14px;
  display:flex; align-items:center; justify-content:center; box-shadow:0 16px 32px rgba(0,0,0,0.25); }
.thumb img { max-width:100%; max-height:100%; object-fit:contain; }
.cats { font-size:20px; font-weight:600; opacity:0.75; line-height:1.4; max-width:900px; }
.swipe { margin-top:auto; font-size:26px; font-weight:800; background:rgba(255,255,255,0.15);
  padding:14px 36px; border-radius:999px; }
</style></head><body>
<div class="wrap">
  <div class="brand">Home Value</div>
  <div class="title">${esc(copy.cover.title)}</div>
  <div class="sub">${esc(copy.cover.subtitle)}</div>
  <div class="mosaic">${thumbs}</div>
  <div class="cats">${esc(copy.cover.categories || copy.cover.title)}</div>
  <div class="swipe">${esc(copy.cover.swipe)}</div>
</div>
</body></html>`;
}

export function renderCtaSlide(copy, { width = FEED_W, height = FEED_H } = {}) {
  return `<!DOCTYPE html><html><head><meta charset="utf-8"><style>
${FONT}
* { margin:0; padding:0; box-sizing:border-box; }
body { width:${width}px; height:${height}px; font-family:'Inter',system-ui,sans-serif; overflow:hidden; }
.wrap { height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center;
  padding:56px 48px; background:#111; color:#fff; text-align:center; }
.icon { font-size:72px; margin-bottom:28px; }
.line { font-size:64px; font-weight:900; line-height:1.05; }
.action { font-size:44px; font-weight:800; color:#FFE082; margin-top:24px; }
.url { font-size:30px; font-weight:600; color:#aaa; margin-top:20px; }
.foot { margin-top:auto; font-size:24px; font-weight:700; color:#666; letter-spacing:0.04em; text-transform:uppercase; }
</style></head><body>
<div class="wrap">
  <div class="icon">🏪</div>
  <div class="line">${esc(copy.cta.line)}</div>
  <div class="action">${esc(copy.cta.action)}</div>
  <div class="url">${esc(copy.cta.url)}</div>
  <div class="foot">2,500+ wholesale SKUs</div>
</div>
</body></html>`;
}
