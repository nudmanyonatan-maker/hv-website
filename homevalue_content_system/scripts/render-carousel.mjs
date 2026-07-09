#!/usr/bin/env node
/**
 * Render an Instagram carousel — cover + product slides + CTA.
 * Output: JPEG slides at 1080×1350 (4:5).
 */

import { readFileSync, writeFileSync, mkdirSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';
import puppeteer from 'puppeteer';
import { buildCarouselCopy } from './lib/carousel-copy.mjs';
import { renderCoverSlide, renderCtaSlide, renderProductSlide, styleForIndex } from './lib/carousel-slides.mjs';
import { pickNextProducts } from './pick-next.mjs';
import { closeQualityBrowser } from './lib/image-quality.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const brand = JSON.parse(readFileSync(join(ROOT, 'config/brand.json'), 'utf8'));
const schedule = JSON.parse(readFileSync(join(ROOT, 'config/schedule.json'), 'utf8'));
const catalog = JSON.parse(readFileSync(join(ROOT, 'catalog/products.json'), 'utf8'));
const catMap = Object.fromEntries(catalog.categories.map((c) => [String(c.id), c.name]));

const idsArg = process.argv.indexOf('--product-ids');
const outputDirArg = process.argv.indexOf('--output-dir');
const countArg = process.argv.indexOf('--count');
const productCount = countArg >= 0
  ? parseInt(process.argv[countArg + 1], 10)
  : (schedule.productsPerCarousel || 6);

let products;
if (idsArg >= 0) {
  const ids = process.argv[idsArg + 1].split(',').map((s) => s.trim());
  products = ids.map((id) => {
    const p = catalog.products.find((x) => String(x.productId) === id);
    if (!p?.image) throw new Error(`Product ${id} not found or missing image`);
    return p;
  });
} else {
  try {
    ({ products } = await pickNextProducts(productCount));
  } finally {
    await closeQualityBrowser();
  }
}

const copy = buildCarouselCopy(products, catMap, brand);
const batchId = products.map((p) => p.productId).join('-');

console.log(`Carousel — ${products.length} products:`);
products.forEach((p) => console.log(`  • ${copy.products.find((x) => String(x.productId) === String(p.productId))?.category}: ${copy.products.find((x) => String(x.productId) === String(p.productId))?.name}`));

const productImages = await Promise.all(
  products.map(async (p) => {
    const res = await fetch(p.image);
    const buf = Buffer.from(await res.arrayBuffer());
    const mime = res.headers.get('content-type') || 'image/jpeg';
    return `data:${mime};base64,${buf.toString('base64')}`;
  })
);

const outDir = outputDirArg >= 0
  ? process.argv[outputDirArg + 1]
  : join(ROOT, 'content/carousels', `carousel-${batchId}-${Date.now()}`);

const tmpDir = join(ROOT, 'content/tmp');
mkdirSync(outDir, { recursive: true });
mkdirSync(tmpDir, { recursive: true });

const slides = [
  { type: 'cover', html: renderCoverSlide(copy, productImages) },
  ...copy.products.map((item, i) => ({
    type: 'product',
    productId: item.productId,
    style: styleForIndex(i),
    html: renderProductSlide(item, productImages[i], styleForIndex(i)),
  })),
  { type: 'cta', html: renderCtaSlide(copy) },
];

console.log(`Rendering ${slides.length} slides (1080×1350)...`);

const browser = await puppeteer.launch({
  headless: true,
  args: ['--no-sandbox', '--disable-setuid-sandbox', '--font-render-hinting=none'],
});

const slidePaths = [];

for (let i = 0; i < slides.length; i++) {
  const slide = slides[i];
  const htmlPath = join(tmpDir, `carousel-${batchId}-s${i}.html`);
  const pngPath = join(tmpDir, `carousel-${batchId}-s${i}.png`);
  const jpgPath = join(outDir, `slide-${String(i + 1).padStart(2, '0')}.jpg`);

  writeFileSync(htmlPath, slide.html);

  const page = await browser.newPage();
  await page.setViewport({ width: 1080, height: 1350, deviceScaleFactor: 2 });
  await page.goto(`file://${htmlPath}`, { waitUntil: 'networkidle0', timeout: 30000 });
  await page.evaluate(() => document.fonts.ready);
  await new Promise((r) => setTimeout(r, 350));
  await page.screenshot({ path: pngPath, type: 'png' });
  await page.close();

  execSync(
    `ffmpeg -y -i "${pngPath}" -vf "scale=1080:1350:flags=lanczos" -q:v 2 "${jpgPath}"`,
    { stdio: 'pipe' }
  );

  slidePaths.push(jpgPath);
  const label = slide.type === 'product' ? `${slide.style} · ${copy.products[i - 1]?.name}` : slide.type;
  console.log(`  ✓ Slide ${i + 1}/${slides.length} — ${label}`);
}

await browser.close();

const manifest = {
  type: 'carousel',
  category: copy.category,
  categoryId: products[0]?.categoryId,
  productIds: products.map((p) => p.productId),
  products: copy.products,
  slides: slides.map((s, i) => ({
    index: i + 1,
    type: s.type,
    style: s.style || null,
    path: slidePaths[i],
  })),
  outputDir: outDir,
  caption: copy.caption,
  copy,
  renderedAt: new Date().toISOString(),
};

writeFileSync(join(outDir, 'manifest.json'), JSON.stringify(manifest, null, 2));
console.log(`✓ Carousel saved: ${outDir}`);
