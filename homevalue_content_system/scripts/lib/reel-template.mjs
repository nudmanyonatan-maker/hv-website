/**
 * Reel frames — multiple products on screen at once.
 */

const BASE = `
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    width: 1080px; height: 1920px;
    font-family: 'Inter', system-ui, sans-serif;
    background: #FFFFFF;
    overflow: hidden;
    -webkit-font-smoothing: antialiased;
  }
  .wrap {
    width: 100%; height: 100%;
    display: flex; flex-direction: column;
    padding: 36px 40px 44px;
  }
  .hook-banner {
    background: #B91C1C;
    border-radius: 22px;
    padding: 32px 36px;
    text-align: center;
    flex-shrink: 0;
  }
  .hook-1 { font-size: 52px; font-weight: 900; color: #FFF; line-height: 1.08; letter-spacing: -0.03em; }
  .hook-2 { font-size: 40px; font-weight: 800; color: #FFE082; margin-top: 10px; }
  .hook-3 { font-size: 28px; font-weight: 600; color: rgba(255,255,255,0.9); margin-top: 6px; }
  .product-grid {
    flex: 1;
    min-height: 0;
    display: grid;
    gap: 16px;
    margin: 20px 0;
  }
  .grid-3 { grid-template-columns: 1fr 1fr 1fr; }
  .grid-2 { grid-template-columns: 1fr 1fr; }
  .prod-card {
    background: #FAFAFA;
    border: 2px solid #EEEEEE;
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-height: 0;
  }
  .prod-img {
    flex: 1;
    min-height: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
  }
  .prod-img img { max-width: 100%; max-height: 100%; object-fit: contain; }
  .prod-info {
    background: #FFF;
    padding: 14px 12px;
    border-top: 2px solid #EEE;
    text-align: center;
  }
  .prod-name { font-size: 22px; font-weight: 800; color: #111; line-height: 1.15; }
  .prod-pack { font-size: 20px; font-weight: 700; color: #B91C1C; margin-top: 4px; }
  .headline {
    font-size: 38px; font-weight: 900; color: #111;
    text-align: center; flex-shrink: 0;
  }
  .callout-box {
    background: #111; border-radius: 22px;
    padding: 32px 36px; text-align: center; flex-shrink: 0;
  }
  .callout-title { font-size: 40px; font-weight: 900; color: #FFF; line-height: 1.15; }
  .callout-sub { font-size: 28px; font-weight: 600; color: #FFE082; margin-top: 10px; }
  .steps { display: flex; flex-direction: column; gap: 14px; flex: 1; min-height: 0; }
  .step {
    display: flex; align-items: center; gap: 18px;
    background: #FAFAFA; border-radius: 18px;
    padding: 20px 22px; border: 2px solid #EEE;
  }
  .step-num {
    flex-shrink: 0; width: 58px; height: 58px;
    background: #B91C1C; color: #fff; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; font-weight: 900;
  }
  .step-text { font-size: 32px; font-weight: 700; color: #111; line-height: 1.25; }
  .cta-btn {
    background: #B91C1C; color: #FFF; text-align: center;
    font-size: 38px; font-weight: 900;
    padding: 32px 20px; border-radius: 18px; flex-shrink: 0; margin-top: 12px;
  }
  .mini-grid .prod-card { }
  .mini-grid .prod-img { min-height: 200px; }
  .mini-grid .prod-name { font-size: 18px; }
  .mini-grid .prod-pack { font-size: 16px; }
`;

function productGridHtml(products, images, mini = false) {
  const count = products.length;
  const gridClass = count === 2 ? 'grid-2' : 'grid-3';
  const cards = products.map((p, i) => `
    <div class="prod-card">
      <div class="prod-img"><img src="${images[i]}" alt="" /></div>
      <div class="prod-info">
        <div class="prod-name">${esc(p.name)}</div>
        <div class="prod-pack">${esc(p.pack)}</div>
      </div>
    </div>`).join('');
  return `<div class="product-grid ${gridClass}${mini ? ' mini-grid' : ''}">${cards}</div>`;
}

export function reelHtmlScene1({ copy, productImages }) {
  return `<!DOCTYPE html><html><head>
<meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@600;700;800;900&display=swap" rel="stylesheet">
<style>${BASE}</style></head><body>
<div class="wrap">
  <div class="hook-banner">
    <div class="hook-1">${esc(copy.hookLine1)}</div>
    <div class="hook-2">${esc(copy.hookLine2)}</div>
    <div class="hook-3">${esc(copy.hookLine3)}</div>
  </div>
  ${productGridHtml(copy.products, productImages)}
  <div class="headline">${esc(copy.headline)}</div>
</div></body></html>`;
}

export function reelHtmlScene2({ copy, productImages }) {
  return `<!DOCTYPE html><html><head>
<meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@600;700;800;900&display=swap" rel="stylesheet">
<style>${BASE}
  .product-grid { flex: none; height: 680px; }
</style></head><body>
<div class="wrap">
  ${productGridHtml(copy.products, productImages)}
  <div class="callout-box">
    <div class="callout-title">${esc(copy.callout)}</div>
    <div class="callout-sub">${esc(copy.calloutSub)}</div>
  </div>
</div></body></html>`;
}

export function reelHtmlScene3({ copy, productImages }) {
  const stepsHtml = copy.steps.map((s) =>
    `<div class="step"><div class="step-num">${esc(s.num)}</div><div class="step-text">${esc(s.text)}</div></div>`
  ).join('');
  return `<!DOCTYPE html><html><head>
<meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@600;700;800;900&display=swap" rel="stylesheet">
<style>${BASE}
  .product-grid { flex: none; height: 340px; margin: 12px 0; }
</style></head><body>
<div class="wrap">
  <div class="hook-banner" style="padding:24px 28px">
    <div class="hook-1" style="font-size:40px">How to order</div>
  </div>
  ${productGridHtml(copy.products, productImages, true)}
  <div class="steps">${stepsHtml}</div>
  <div class="cta-btn">${esc(copy.ctaButton)}</div>
</div></body></html>`;
}

function esc(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

export const sceneRenderers = { 1: reelHtmlScene1, 2: reelHtmlScene2, 3: reelHtmlScene3 };
