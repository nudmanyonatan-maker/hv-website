/**
 * Single-product Reel layouts — one SKU, big and front-and-center.
 */

function esc(s) {
  return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
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

function product(copy, image) {
  return copy.products[0];
}

function ctaBlock(copy) {
  return `<div class="cta">
    <div class="cta-line">${esc(copy.ctaLine)}</div>
    <div class="cta-action">${esc(copy.ctaAction)}</div>
  </div>`;
}

function heroImage(image, cls = 'hero-img') {
  return `<div class="${cls}"><img src="${image}" alt=""/></div>`;
}

function productNameBlock(p) {
  return `<div class="name">${esc(p.name)}</div>`;
}

function categoryBadge(p) {
  return `<div class="badge">${esc(p.category)}</div>`;
}

function hookStrip(copy) {
  return `<div class="strip">${esc(copy.hook)} · ${esc(copy.hookSub)}</div>`;
}

const heroCenter = {
  scene1(copy, images) {
    const p = product(copy, images);
    return baseHtml(`<div class="wrap">
      ${hookStrip(copy)}
      ${heroImage(images[0], 'stage')}
      <div class="footer">
        ${categoryBadge(p)}
        ${productNameBlock(p)}
      </div>
    </div>`, `
.wrap { display:flex; flex-direction:column; height:100%; background:#fafafa; }
.strip { background:#B91C1C; color:#fff; font-size:30px; font-weight:800; text-align:center; padding:22px 20px; flex-shrink:0; }
.stage { flex:1; min-height:0; display:flex; align-items:center; justify-content:center; padding:24px 32px; }
.stage img { max-width:100%; max-height:100%; object-fit:contain; filter:drop-shadow(0 24px 48px rgba(0,0,0,0.12)); }
.footer { flex-shrink:0; padding:28px 40px 48px; text-align:center; background:#fff; border-top:1px solid #eee; }
.badge { display:inline-block; font-size:22px; font-weight:700; color:#B91C1C; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:12px; }
.name { font-size:52px; font-weight:900; color:#111; line-height:1.08; }
    `);
  },
  scene2(copy, images) {
    const p = product(copy, images);
    return baseHtml(`<div class="wrap">
      ${heroImage(images[0], 'stage-big')}
      <div class="overlay">
        ${productNameBlock(p)}
        ${ctaBlock(copy)}
      </div>
    </div>`, `
.wrap { position:relative; height:100%; background:#111; }
.stage-big { height:100%; display:flex; align-items:center; justify-content:center; padding:80px 48px 320px; }
.stage-big img { max-width:100%; max-height:100%; object-fit:contain; filter:drop-shadow(0 32px 64px rgba(0,0,0,0.35)); }
.overlay { position:absolute; left:0; right:0; bottom:0; padding:40px 48px 56px; background:linear-gradient(transparent, rgba(0,0,0,0.92)); text-align:center; }
.name { font-size:48px; font-weight:900; color:#fff; line-height:1.1; margin-bottom:28px; }
.cta { background:#B91C1C; border-radius:24px; padding:32px 40px; }
.cta-line { font-size:40px; font-weight:900; color:#fff; }
.cta-action { font-size:36px; font-weight:800; color:#FFE082; margin-top:8px; }
    `, true);
  },
};

const heroGlow = {
  scene1(copy, images) {
    const p = product(copy, images);
    return baseHtml(`<div class="wrap">
      ${categoryBadge(p)}
      ${heroImage(images[0], 'stage')}
      ${productNameBlock(p)}
      <div class="sub">${esc(copy.hookSub)}</div>
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center;
  padding:48px 40px; background:linear-gradient(165deg,#fff5f5 0%,#fff 45%,#f8fafc 100%); }
.badge { font-size:24px; font-weight:800; color:#B91C1C; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:24px; }
.stage { flex:1; width:100%; min-height:0; display:flex; align-items:center; justify-content:center; padding:16px; }
.stage img { max-width:96%; max-height:96%; object-fit:contain; filter:drop-shadow(0 28px 56px rgba(185,28,28,0.15)); }
.name { font-size:56px; font-weight:900; color:#111; text-align:center; line-height:1.08; margin-top:16px; }
.sub { font-size:32px; font-weight:600; color:#666; text-align:center; margin-top:16px; }
    `);
  },
  scene2(copy, images) {
    return baseHtml(`<div class="wrap">
      ${heroImage(images[0], 'stage')}
      ${ctaBlock(copy)}
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; background:#fff; }
.stage { flex:1; min-height:0; display:flex; align-items:center; justify-content:center; padding:64px 40px 32px; background:radial-gradient(circle at 50% 40%, #fef2f2 0%, #fff 70%); }
.stage img { max-width:98%; max-height:98%; object-fit:contain; }
.cta { flex-shrink:0; margin:0 40px 56px; background:#111; border-radius:28px; padding:48px 40px; text-align:center; }
.cta-line { font-size:44px; font-weight:900; color:#fff; }
.cta-action { font-size:38px; font-weight:800; color:#FFE082; margin-top:12px; }
    `);
  },
};

const heroDark = {
  scene1(copy, images) {
    const p = product(copy, images);
    return baseHtml(`<div class="wrap">
      <div class="top">${esc(copy.hook)}</div>
      <div class="card">${heroImage(images[0], 'stage')}</div>
      <div class="meta">
        ${categoryBadge(p)}
        ${productNameBlock(p)}
      </div>
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; padding:40px 36px 48px; gap:24px; background:#0a0a0a; }
.top { text-align:center; font-size:28px; font-weight:800; color:#888; letter-spacing:0.12em; text-transform:uppercase; }
.card { flex:1; min-height:0; background:#fff; border-radius:32px; display:flex; align-items:center; justify-content:center; padding:40px; box-shadow:0 40px 80px rgba(0,0,0,0.45); }
.stage { width:100%; height:100%; display:flex; align-items:center; justify-content:center; }
.stage img { max-width:100%; max-height:100%; object-fit:contain; }
.meta { text-align:center; }
.badge { font-size:22px; font-weight:700; color:#B91C1C; text-transform:uppercase; margin-bottom:10px; }
.name { font-size:50px; font-weight:900; color:#fff; line-height:1.1; }
    `, true);
  },
  scene2(copy, images) {
    return baseHtml(`<div class="wrap">
      ${heroImage(images[0], 'stage')}
      ${ctaBlock(copy)}
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; background:#0a0a0a; }
.stage { flex:1; min-height:0; display:flex; align-items:center; justify-content:center; padding:48px 32px 24px; }
.stage img { max-width:100%; max-height:100%; object-fit:contain; filter:drop-shadow(0 24px 48px rgba(255,255,255,0.08)); }
.cta { flex-shrink:0; margin:0 36px 52px; background:#B91C1C; border-radius:24px; padding:44px 36px; text-align:center; }
.cta-line { font-size:42px; font-weight:900; color:#fff; }
.cta-action { font-size:36px; font-weight:800; color:#FFE082; margin-top:10px; }
    `, true);
  },
};

const heroBleed = {
  scene1(copy, images) {
    const p = product(copy, images);
    return baseHtml(`<div class="wrap">
      ${heroImage(images[0], 'fill')}
      <div class="bar">
        <div class="badge">${esc(p.category)}</div>
        <div class="name">${esc(p.name)}</div>
      </div>
    </div>`, `
.wrap { position:relative; height:100%; background:#fff; }
.fill { height:100%; display:flex; align-items:center; justify-content:center; padding:120px 24px 280px; }
.fill img { width:100%; height:100%; object-fit:contain; }
.bar { position:absolute; left:0; right:0; bottom:0; padding:36px 40px 52px; background:linear-gradient(transparent, rgba(255,255,255,0.95) 30%); }
.badge { font-size:24px; font-weight:800; color:#B91C1C; text-transform:uppercase; margin-bottom:10px; }
.name { font-size:54px; font-weight:900; color:#111; line-height:1.08; }
    `);
  },
  scene2(copy, images) {
    return baseHtml(`<div class="wrap">
      ${heroImage(images[0], 'fill')}
      <div class="bar">${ctaBlock(copy)}</div>
    </div>`, `
.wrap { position:relative; height:100%; background:#f5f5f5; }
.fill { height:100%; display:flex; align-items:center; justify-content:center; padding:80px 20px 340px; }
.fill img { width:100%; height:100%; object-fit:contain; }
.bar { position:absolute; left:0; right:0; bottom:0; padding:32px 36px 48px; }
.cta { background:#111; border-radius:24px; padding:40px 36px; text-align:center; }
.cta-line { font-size:44px; font-weight:900; color:#fff; }
.cta-action { font-size:38px; font-weight:800; color:#FFE082; margin-top:10px; }
    `);
  },
};

const heroMagazine = {
  scene1(copy, images) {
    const p = product(copy, images);
    return baseHtml(`<div class="wrap">
      <div class="side">
        <div class="brand">Home Value</div>
        <div class="hook">${esc(copy.hook)}</div>
        <div class="sub">${esc(copy.hookSub)}</div>
        <div class="cat">${esc(p.category)}</div>
      </div>
      <div class="main">
        ${heroImage(images[0], 'stage')}
        ${productNameBlock(p)}
      </div>
    </div>`, `
.wrap { display:flex; height:100%; }
.side { width:300px; background:#B91C1C; color:#fff; padding:48px 28px; display:flex; flex-direction:column; justify-content:center; }
.brand { font-size:26px; font-weight:800; opacity:0.9; margin-bottom:40px; }
.hook { font-size:40px; font-weight:900; line-height:1.1; }
.sub { font-size:24px; font-weight:600; margin-top:20px; opacity:0.85; line-height:1.35; }
.cat { font-size:18px; font-weight:700; margin-top:32px; text-transform:uppercase; letter-spacing:0.06em; opacity:0.75; }
.main { flex:1; display:flex; flex-direction:column; background:#fafafa; padding:32px 28px 40px; }
.stage { flex:1; min-height:0; display:flex; align-items:center; justify-content:center; }
.stage img { max-width:100%; max-height:100%; object-fit:contain; }
.name { font-size:42px; font-weight:900; color:#111; text-align:center; line-height:1.1; margin-top:16px; }
    `);
  },
  scene2(copy, images) {
    return baseHtml(`<div class="wrap">
      ${heroImage(images[0], 'stage')}
      ${ctaBlock(copy)}
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; background:#fff; padding:40px 36px 48px; gap:28px; }
.stage { flex:1; min-height:0; display:flex; align-items:center; justify-content:center; background:#f8f8f8; border-radius:28px; padding:32px; }
.stage img { max-width:100%; max-height:100%; object-fit:contain; }
.cta { flex-shrink:0; background:#B91C1C; border-radius:24px; padding:44px 36px; text-align:center; }
.cta-line { font-size:44px; font-weight:900; color:#fff; }
.cta-action { font-size:38px; font-weight:800; color:#FFE082; margin-top:10px; }
    `);
  },
};

const heroSpotlight = {
  scene1(copy, images) {
    const p = product(copy, images);
    return baseHtml(`<div class="wrap">
      <div class="spot">${heroImage(images[0], 'stage')}</div>
      <div class="info">
        ${categoryBadge(p)}
        ${productNameBlock(p)}
      </div>
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; background:#111; }
.spot { flex:1; min-height:0; display:flex; align-items:center; justify-content:center;
  background:radial-gradient(circle at 50% 45%, rgba(255,255,255,0.14) 0%, rgba(255,255,255,0.04) 35%, transparent 65%); padding:48px 36px; }
.stage { width:100%; height:100%; display:flex; align-items:center; justify-content:center; }
.stage img { max-width:100%; max-height:100%; object-fit:contain; filter:drop-shadow(0 32px 64px rgba(0,0,0,0.5)); }
.info { flex-shrink:0; text-align:center; padding:32px 40px 56px; background:linear-gradient(transparent, #111); }
.badge { font-size:22px; font-weight:700; color:#B91C1C; text-transform:uppercase; margin-bottom:12px; }
.name { font-size:52px; font-weight:900; color:#fff; line-height:1.08; }
    `, true);
  },
  scene2(copy, images) {
    return baseHtml(`<div class="wrap">
      <div class="spot">${heroImage(images[0], 'stage')}</div>
      ${ctaBlock(copy)}
    </div>`, `
.wrap { height:100%; display:flex; flex-direction:column; background:#111; padding-bottom:48px; }
.spot { flex:1; min-height:0; display:flex; align-items:center; justify-content:center; padding:56px 32px 24px;
  background:radial-gradient(circle at 50% 42%, rgba(255,255,255,0.12) 0%, transparent 60%); }
.stage { width:100%; height:100%; display:flex; align-items:center; justify-content:center; }
.stage img { max-width:100%; max-height:100%; object-fit:contain; }
.cta { flex-shrink:0; margin:0 36px; background:#fff; border-radius:24px; padding:44px 36px; text-align:center; }
.cta-line { font-size:42px; font-weight:900; color:#111; }
.cta-action { font-size:36px; font-weight:800; color:#B91C1C; margin-top:10px; }
    `, true);
  },
};

export const FORMAT_RENDERERS = {
  'hero-center': heroCenter,
  'hero-glow': heroGlow,
  'hero-dark': heroDark,
  'hero-bleed': heroBleed,
  'hero-magazine': heroMagazine,
  'hero-spotlight': heroSpotlight,
};

export function renderScene(formatId, sceneNum, copy, productImages) {
  const fmt = FORMAT_RENDERERS[formatId];
  if (!fmt) throw new Error(`Unknown format: ${formatId}`);
  const fn = sceneNum === 1 ? fmt.scene1 : fmt.scene2;
  return fn(copy, productImages);
}
