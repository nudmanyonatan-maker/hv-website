/**
 * Clean 3-scene Reel frames — premium minimal, zero clutter.
 * Scene 1: Product hero
 * Scene 2: Product + business callout
 * Scene 3: Product + 3-step how to buy
 */

const BASE_STYLES = `
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    width: 1080px;
    height: 1920px;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background: #FFFFFF;
    overflow: hidden;
    -webkit-font-smoothing: antialiased;
  }
  .wrap {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    padding: 56px 64px 64px;
  }
  .logo-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 40px;
  }
  .logo-mark {
    width: 48px;
    height: 48px;
    background: #B91C1C;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 800;
    font-size: 22px;
  }
  .logo-text {
    font-size: 32px;
    font-weight: 700;
    color: #111111;
    letter-spacing: -0.03em;
  }
  .pill {
    display: inline-block;
    background: #111111;
    color: #FFFFFF;
    font-size: 22px;
    font-weight: 700;
    letter-spacing: 0.14em;
    padding: 14px 28px;
    border-radius: 100px;
    text-transform: uppercase;
  }
  .product-card {
    background: #FAFAFA;
    border-radius: 32px;
    border: 1px solid #EEEEEE;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
  }
  .product-card img {
    max-width: 92%;
    max-height: 92%;
    object-fit: contain;
  }
  .name {
    font-size: 52px;
    font-weight: 800;
    color: #111111;
    line-height: 1.1;
    letter-spacing: -0.03em;
    margin-top: 36px;
  }
  .pack {
    font-size: 32px;
    font-weight: 600;
    color: #B91C1C;
    margin-top: 12px;
  }
  .callout-box {
    background: #FFF8E6;
    border: 2px solid #F5D061;
    border-radius: 24px;
    padding: 32px 36px;
    margin-top: 32px;
  }
  .callout-title {
    font-size: 36px;
    font-weight: 800;
    color: #111111;
    line-height: 1.25;
  }
  .callout-sub {
    font-size: 26px;
    font-weight: 500;
    color: #666666;
    margin-top: 10px;
    line-height: 1.35;
  }
  .steps {
    margin-top: 28px;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  .step {
    display: flex;
    align-items: center;
    gap: 24px;
    background: #FAFAFA;
    border-radius: 20px;
    padding: 24px 28px;
    border: 1px solid #EEEEEE;
  }
  .step-num {
    flex-shrink: 0;
    width: 56px;
    height: 56px;
    background: #B91C1C;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: 800;
  }
  .step-text {
    font-size: 30px;
    font-weight: 600;
    color: #111111;
    line-height: 1.3;
  }
  .cta-btn {
    margin-top: 32px;
    background: #B91C1C;
    color: #FFFFFF;
    text-align: center;
    font-size: 30px;
    font-weight: 800;
    padding: 32px 24px;
    border-radius: 20px;
    letter-spacing: -0.01em;
  }
`;

export function reelHtmlScene1({ copy, imageDataUrl }) {
  return `<!DOCTYPE html><html><head>
<meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>${BASE_STYLES}
  .product-card { flex: 1; min-height: 0; margin-top: 24px; }
  .center-pill { text-align: center; margin-top: 8px; }
</style></head><body>
<div class="wrap">
  <div class="logo-row">
    <div class="logo-mark">HV</div>
    <div class="logo-text">Home Value</div>
  </div>
  <div class="center-pill"><span class="pill">${esc(copy.eyebrow)}</span></div>
  <div class="product-card">
    <img src="${imageDataUrl}" alt="" />
  </div>
  <div class="name">${esc(copy.productName)}</div>
  <div class="pack">${esc(copy.packLine)}</div>
</div></body></html>`;
}

export function reelHtmlScene2({ copy, imageDataUrl }) {
  return `<!DOCTYPE html><html><head>
<meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>${BASE_STYLES}
  .product-card { height: 720px; flex-shrink: 0; }
</style></head><body>
<div class="wrap">
  <div class="logo-row">
    <div class="logo-mark">HV</div>
    <div class="logo-text">Home Value</div>
  </div>
  <div class="product-card">
    <img src="${imageDataUrl}" alt="" />
  </div>
  <div class="name">${esc(copy.productName)}</div>
  <div class="pack">${esc(copy.packLine)}</div>
  <div class="callout-box">
    <div class="callout-title">${esc(copy.callout)}</div>
    <div class="callout-sub">${esc(copy.calloutSub)}</div>
  </div>
</div></body></html>`;
}

export function reelHtmlScene3({ copy, imageDataUrl }) {
  const stepsHtml = copy.steps.map((s) =>
    `<div class="step"><div class="step-num">${esc(s.num)}</div><div class="step-text">${esc(s.text)}</div></div>`
  ).join('');
  return `<!DOCTYPE html><html><head>
<meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>${BASE_STYLES}
  .product-card { height: 520px; flex-shrink: 0; }
  .mini-name { font-size: 36px; font-weight: 800; color: #111; margin-top: 24px; }
</style></head><body>
<div class="wrap">
  <div class="logo-row">
    <div class="logo-mark">HV</div>
    <div class="logo-text">Home Value</div>
  </div>
  <div class="product-card">
    <img src="${imageDataUrl}" alt="" />
  </div>
  <div class="mini-name">${esc(copy.productName)}</div>
  <div class="steps">${stepsHtml}</div>
  <div class="cta-btn">${esc(copy.ctaButton)}</div>
</div></body></html>`;
}

function esc(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

export const sceneRenderers = {
  1: reelHtmlScene1,
  2: reelHtmlScene2,
  3: reelHtmlScene3,
};
