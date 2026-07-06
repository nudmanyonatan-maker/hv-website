/**
 * Score product images — skip tiny, blank, or mostly-empty photos.
 */

import puppeteer from 'puppeteer';

const MIN_WIDTH = 280;
const MIN_HEIGHT = 280;
const MIN_BYTES = 8_000;
const MAX_WHITE_RATIO = 0.82;
const MAX_BLACK_RATIO = 0.88;
const MIN_CONTENT_RATIO = 0.12;

let browserPromise = null;

function getBrowser() {
  if (!browserPromise) {
    browserPromise = puppeteer.launch({
      headless: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox'],
    });
  }
  return browserPromise;
}

export async function closeQualityBrowser() {
  if (browserPromise) {
    await browserPromise.then((b) => b.close()).catch(() => {});
    browserPromise = null;
  }
}

/** Quick reject from HTTP headers / size before puppeteer. */
export function quickReject(buffer, contentType) {
  if (!buffer?.length) return { ok: false, reason: 'empty' };
  if (buffer.length < MIN_BYTES) return { ok: false, reason: 'file_too_small' };
  const ct = contentType || '';
  if (ct.includes('html') || ct.includes('text')) return { ok: false, reason: 'not_an_image' };
  return { ok: true };
}

/**
 * Analyze image content. Returns { ok, score, reason, width, height }.
 * Higher score = better product photo.
 */
export async function scoreProductImage(imageUrl) {
  try {
    const res = await fetch(imageUrl, { signal: AbortSignal.timeout(15000) });
    if (!res.ok) return { ok: false, score: 0, reason: 'fetch_failed' };

    const contentType = res.headers.get('content-type') || '';
    const buffer = Buffer.from(await res.arrayBuffer());
    const quick = quickReject(buffer, contentType);
    if (!quick.ok) return { ok: false, score: 0, reason: quick.reason };

    const b64 = buffer.toString('base64');
    const mime = contentType.includes('png') ? 'image/png' : 'image/jpeg';
    const dataUrl = `data:${mime};base64,${b64}`;

    const browser = await getBrowser();
    const page = await browser.newPage();
    const metrics = await page.evaluate(async (src) => {
      const img = new Image();
      img.crossOrigin = 'anonymous';
      await new Promise((resolve, reject) => {
        img.onload = resolve;
        img.onerror = reject;
        img.src = src;
      });

      const w = img.naturalWidth;
      const h = img.naturalHeight;
      const canvas = document.createElement('canvas');
      const maxSide = 320;
      const scale = Math.min(1, maxSide / Math.max(w, h));
      canvas.width = Math.max(1, Math.round(w * scale));
      canvas.height = Math.max(1, Math.round(h * scale));
      const ctx = canvas.getContext('2d');
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
      const { data } = ctx.getImageData(0, 0, canvas.width, canvas.height);

      let white = 0;
      let black = 0;
      let content = 0;
      let varianceSum = 0;
      const pixels = data.length / 4;

      for (let i = 0; i < data.length; i += 4) {
        const r = data[i];
        const g = data[i + 1];
        const b = data[i + 2];
        const lum = 0.299 * r + 0.587 * g + 0.114 * b;
        varianceSum += Math.abs(r - g) + Math.abs(g - b) + Math.abs(r - b);

        if (lum > 240 && Math.abs(r - g) < 15 && Math.abs(g - b) < 15) white += 1;
        else if (lum < 25) black += 1;
        else content += 1;
      }

      const whiteRatio = white / pixels;
      const blackRatio = black / pixels;
      const contentRatio = content / pixels;
      const colorVariance = varianceSum / pixels;

      return { width: w, height: h, whiteRatio, blackRatio, contentRatio, colorVariance };
    }, dataUrl);

    await page.close();

    const { width, height, whiteRatio, blackRatio, contentRatio, colorVariance } = metrics;

    if (width < MIN_WIDTH || height < MIN_HEIGHT) {
      return { ok: false, score: 0, reason: 'too_small', width, height };
    }

    const aspect = width / height;
    if (aspect > 3.5 || aspect < 0.28) {
      return { ok: false, score: 0, reason: 'bad_aspect', width, height };
    }

    if (whiteRatio > MAX_WHITE_RATIO) {
      return { ok: false, score: 0, reason: 'mostly_white', width, height, whiteRatio };
    }

    if (blackRatio > MAX_BLACK_RATIO) {
      return { ok: false, score: 0, reason: 'mostly_black', width, height, blackRatio };
    }

    if (contentRatio < MIN_CONTENT_RATIO) {
      return { ok: false, score: 0, reason: 'no_product_content', width, height, contentRatio };
    }

    if (colorVariance < 8) {
      return { ok: false, score: 0, reason: 'flat_blank', width, height };
    }

    const score =
      Math.min(width, height) * 0.5 +
      contentRatio * 100 +
      colorVariance * 2 -
      whiteRatio * 30;

    return { ok: true, score, reason: 'good', width, height, contentRatio };
  } catch (err) {
    return { ok: false, score: 0, reason: err.message || 'error' };
  }
}

export async function filterProductsByImageQuality(products, count, { log = true } = {}) {
  const accepted = [];
  const rejected = [];

  for (const p of products) {
    if (accepted.length >= count) break;
    const result = await scoreProductImage(p.image);
    if (result.ok) {
      accepted.push({ ...p, imageScore: result.score });
      if (log) console.log(`  ✓ image OK (${result.width}×${result.height}) — ${p.name.slice(0, 40)}`);
    } else {
      rejected.push({ productId: p.productId, name: p.name, reason: result.reason });
      if (log) console.log(`  ✗ skip (${result.reason}) — ${p.name.slice(0, 40)}`);
    }
  }

  return { accepted, rejected };
}
