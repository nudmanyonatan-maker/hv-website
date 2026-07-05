/**
 * Reel frames v4 — HOOK FIRST, bigger product, bigger type.
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
    padding: 40px 48px 48px;
  }
  /* ── HOOK BANNER — first thing you see ── */
  .hook-banner {
    background: #B91C1C;
    border-radius: 24px;
    padding: 36px 40px;
    text-align: center;
    flex-shrink: 0;
  }
  .hook-1 {
    font-size: 56px;
    font-weight: 900;
    color: #FFFFFF;
    line-height: 1.08;
    letter-spacing: -0.03em;
  }
  .hook-2 {
    font-size: 44px;
    font-weight: 800;
    color: #FFE082;
    margin-top: 12px;
    line-height: 1.1;
  }
  .hook-3 {
    font-size: 32px;
    font-weight: 600;
    color: rgba(255,255,255,0.9);
    margin-top: 8px;
  }
  /* ── Product — as big as possible ── */
  .product-card {
    flex: 1;
    min-height: 0;
    margin: 28px 0;
    background: #FAFAFA;
    border-radius: 28px;
    border: 2px solid #EEEEEE;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }
  .product-card img {
    width: 96%;
    height: 96%;
    object-fit: contain;
  }
  .product-card.sm {
    flex: none;
    height: 560px;
    margin: 20px 0;
  }
  .product-card.xs {
    flex: none;
    height: 440px;
    margin: 16px 0;
  }
  /* ── Product label ── */
  .name {
    font-size: 64px;
    font-weight: 900;
    color: #111;
    line-height: 1.05;
    letter-spacing: -0.03em;
    text-align: center;
  }
  .pack {
    font-size: 40px;
    font-weight: 700;
    color: #B91C1C;
    text-align: center;
    margin-top: 10px;
  }
  .category {
    font-size: 30px;
    font-weight: 600;
    color: #888;
    text-align: center;
    margin-top: 8px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  /* ── Callout ── */
  .callout-box {
    background: #111;
    border-radius: 24px;
    padding: 36px 40px;
    text-align: center;
    flex-shrink: 0;
  }
  .callout-title {
    font-size: 44px;
    font-weight: 900;
    color: #FFF;
    line-height: 1.15;
  }
  .callout-sub {
    font-size: 32px;
    font-weight: 600;
    color: #FFE082;
    margin-top: 12px;
    line-height: 1.25;
  }
  /* ── Steps ── */
  .steps { display: flex; flex-direction: column; gap: 16px; flex: 1; }
  .step {
    display: flex; align-items: center; gap: 20px;
    background: #FAFAFA; border-radius: 20px;
    padding: 22px 24px; border: 2px solid #EEE;
  }
  .step-num {
    flex-shrink: 0; width: 64px; height: 64px;
    background: #B91C1C; color: #fff; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 34px; font-weight: 900;
  }
  .step-text {
    font-size: 36px; font-weight: 700; color: #111; line-height: 1.25;
  }
  .cta-btn {
    background: #B91C1C; color: #FFF; text-align: center;
    font-size: 40px; font-weight: 900;
    padding: 36px 24px; border-radius: 20px;
    flex-shrink: 0; margin-top: 16px;
  }
  .brand-tag {
    text-align: center;
    font-size: 28px;
    font-weight: 700;
    color: #999;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    flex-shrink: 0;
    margin-bottom: 8px;
  }
`;

/** Scene 1: HOOK + huge product — stops the scroll */
export function reelHtmlScene1({ copy, imageDataUrl }) {
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
  <div class="product-card">
    <img src="${imageDataUrl}" alt="" />
  </div>
  <div class="name">${esc(copy.productName)}</div>
  <div class="pack">${esc(copy.packLine)}</div>
</div></body></html>`;
}

/** Scene 2: Product + who this is for */
export function reelHtmlScene2({ copy, imageDataUrl }) {
  return `<!DOCTYPE html><html><head>
<meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@600;700;800;900&display=swap" rel="stylesheet">
<style>${BASE}</style></head><body>
<div class="wrap">
  <div class="brand-tag">Home Value · Wholesale</div>
  <div class="product-card sm">
    <img src="${imageDataUrl}" alt="" />
  </div>
  <div class="name">${esc(copy.productName)}</div>
  <div class="pack">${esc(copy.packLine)}</div>
  <div class="category">${esc(copy.categoryLine)}</div>
  <div class="callout-box">
    <div class="callout-title">${esc(copy.callout)}</div>
    <div class="callout-sub">${esc(copy.calloutSub)}</div>
  </div>
</div></body></html>`;
}

/** Scene 3: How to buy + link in comments */
export function reelHtmlScene3({ copy, imageDataUrl }) {
  const stepsHtml = copy.steps.map((s) =>
    `<div class="step"><div class="step-num">${esc(s.num)}</div><div class="step-text">${esc(s.text)}</div></div>`
  ).join('');
  return `<!DOCTYPE html><html><head>
<meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@600;700;800;900&display=swap" rel="stylesheet">
<style>${BASE}</style></head><body>
<div class="wrap">
  <div class="hook-banner" style="padding:28px 32px">
    <div class="hook-1" style="font-size:44px">How to order</div>
  </div>
  <div class="product-card xs">
    <img src="${imageDataUrl}" alt="" />
  </div>
  <div class="steps">${stepsHtml}</div>
  <div class="cta-btn">${esc(copy.ctaButton)}</div>
</div></body></html>`;
}

function esc(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

export const sceneRenderers = { 1: reelHtmlScene1, 2: reelHtmlScene2, 3: reelHtmlScene3 };
