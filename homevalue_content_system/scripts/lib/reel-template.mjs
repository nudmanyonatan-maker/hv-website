/** Professional reel frame — image zone top, text panel bottom, zero overlap. */

export function reelHtml({ brand, copy, imageDataUrl }) {
  const { colors } = brand;
  return `<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    width: 1080px;
    height: 1920px;
    font-family: 'DM Sans', system-ui, sans-serif;
    background: ${colors.background};
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }
  /* ── Header bar ── */
  .header {
    flex: 0 0 100px;
    background: ${colors.text};
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 48px;
  }
  .brand {
    color: #fff;
    font-size: 36px;
    font-weight: 700;
    letter-spacing: -0.02em;
  }
  .brand span { color: ${colors.primary}; }
  .badge {
    background: ${colors.primary};
    color: #fff;
    font-size: 22px;
    font-weight: 600;
    padding: 10px 24px;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }
  /* ── Image zone (top 52%) — text never enters here ── */
  .image-zone {
    flex: 0 0 920px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 48px;
    border-bottom: 4px solid ${colors.primary};
  }
  .image-zone img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
  }
  /* ── Text panel (bottom) — separate from image ── */
  .text-panel {
    flex: 1;
    background: ${colors.background};
    padding: 40px 48px 48px;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  .headline {
    font-size: 42px;
    font-weight: 700;
    color: ${colors.text};
    line-height: 1.15;
    letter-spacing: -0.02em;
  }
  .subline {
    font-size: 26px;
    font-weight: 600;
    color: ${colors.primary};
  }
  .context {
    font-size: 28px;
    font-weight: 400;
    color: ${colors.textMuted};
    line-height: 1.45;
    flex: 1;
  }
  .value-line {
    font-size: 24px;
    color: ${colors.textMuted};
    line-height: 1.4;
    font-style: italic;
  }
  .cta-bar {
    background: ${colors.primary};
    border-radius: 16px;
    padding: 28px 32px;
    margin-top: auto;
  }
  .cta-label {
    font-size: 20px;
    font-weight: 600;
    color: rgba(255,255,255,0.85);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 8px;
  }
  .cta-text {
    font-size: 26px;
    font-weight: 700;
    color: #fff;
    line-height: 1.3;
  }
</style>
</head>
<body>
  <div class="header">
    <div class="brand">Home <span>Value</span></div>
    <div class="badge">${escapeHtml(copy.badge)}</div>
  </div>
  <div class="image-zone">
    <img src="${imageDataUrl}" alt="product" />
  </div>
  <div class="text-panel">
    <div class="headline">${escapeHtml(copy.headline)}</div>
    <div class="subline">${escapeHtml(copy.subline)}</div>
    <div class="context">${escapeHtml(copy.context)}</div>
    <div class="value-line">${escapeHtml(copy.value)}</div>
    <div class="cta-bar">
      <div class="cta-label">Wholesale buyers</div>
      <div class="cta-text">${escapeHtml(copy.ctaShort)} → hv-website-phi.vercel.app</div>
    </div>
  </div>
</body>
</html>`;
}

function escapeHtml(s) {
  return String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}
