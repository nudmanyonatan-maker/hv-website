#!/usr/bin/env node
/**
 * Render a single Instagram feed image — one hero product, 1080×1350.
 */

import { readFileSync, writeFileSync, mkdirSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';
import puppeteer from 'puppeteer';
import { buildSinglePostCopy } from './lib/single-copy.mjs';
import { renderProductHeroSlide, FEED_W, FEED_H } from './lib/product-slide.mjs';
import { pickNextProducts } from './pick-next.mjs';
import { closeQualityBrowser } from './lib/image-quality.mjs';
import { categoryName } from './lib/copy-utils.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const brand = JSON.parse(readFileSync(join(ROOT, 'config/brand.json'), 'utf8'));
const catalog = JSON.parse(readFileSync(join(ROOT, 'catalog/products.json'), 'utf8'));
const catMap = Object.fromEntries(catalog.categories.map((c) => [String(c.id), c.name]));

const idsArg = process.argv.indexOf('--product-id');
const outputArg = process.argv.indexOf('--output');

let product;
let categoryId;

if (idsArg >= 0) {
  const id = process.argv[idsArg + 1].trim();
  product = catalog.products.find((x) => String(x.productId) === id);
  if (!product?.image) throw new Error(`Product ${id} not found or missing image`);
  categoryId = product.categoryId;
} else {
  try {
    const picked = await pickNextProducts(1);
    product = picked.products[0];
    categoryId = picked.categoryId;
  } finally {
    await closeQualityBrowser();
  }
}

const copy = buildSinglePostCopy(product, catMap, brand);
const batchId = String(product.productId);

console.log(`Single post — ${copy.category}: ${copy.product.name}`);

const res = await fetch(product.image);
const buf = Buffer.from(await res.arrayBuffer());
const mime = res.headers.get('content-type') || 'image/jpeg';
const imageDataUrl = `data:${mime};base64,${buf.toString('base64')}`;

const html = renderProductHeroSlide(copy.product, imageDataUrl);
const tmpDir = join(ROOT, 'content/tmp');
const outDir = join(ROOT, 'content/single-posts');
mkdirSync(tmpDir, { recursive: true });
mkdirSync(outDir, { recursive: true });

const outputPath = outputArg >= 0
  ? process.argv[outputArg + 1]
  : join(outDir, `single-${batchId}.jpg`);

const htmlPath = join(tmpDir, `single-${batchId}.html`);
const pngPath = join(tmpDir, `single-${batchId}.png`);
writeFileSync(htmlPath, html);

const browser = await puppeteer.launch({
  headless: true,
  args: ['--no-sandbox', '--disable-setuid-sandbox', '--font-render-hinting=none'],
});

const page = await browser.newPage();
await page.setViewport({ width: FEED_W, height: FEED_H, deviceScaleFactor: 2 });
await page.goto(`file://${htmlPath}`, { waitUntil: 'networkidle0', timeout: 30000 });
await page.evaluate(() => document.fonts.ready);
await new Promise((r) => setTimeout(r, 400));
await page.screenshot({ path: pngPath, type: 'png' });
await browser.close();

execSync(
  `ffmpeg -y -i "${pngPath}" -vf "scale=${FEED_W}:${FEED_H}:flags=lanczos" -q:v 2 "${outputPath}"`,
  { stdio: 'pipe' }
);

const manifest = {
  type: 'single',
  category: copy.category,
  categoryId,
  productIds: [product.productId],
  product: copy.product,
  imagePath: outputPath,
  caption: copy.caption,
  copy,
  renderedAt: new Date().toISOString(),
};

writeFileSync(outputPath.replace('.jpg', '.json'), JSON.stringify(manifest, null, 2));
console.log(`✓ Single post saved: ${outputPath}`);
