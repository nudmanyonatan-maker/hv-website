#!/usr/bin/env node
/**
 * Render a professional Instagram Reel MP4.
 * Layout: header → product image (top zone) → text panel (bottom) — no overlap.
 *
 * Usage: node scripts/render-reel.mjs --product-id 119435 [--output path.mp4]
 */

import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';
import puppeteer from 'puppeteer';
import { buildReelCopy } from './lib/copy.mjs';
import { reelHtml } from './lib/reel-template.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const brand = JSON.parse(readFileSync(join(ROOT, 'config/brand.json'), 'utf8'));
const schedule = JSON.parse(readFileSync(join(ROOT, 'config/schedule.json'), 'utf8'));
const catalog = JSON.parse(readFileSync(join(ROOT, 'catalog/products.json'), 'utf8'));
const catMap = Object.fromEntries(catalog.categories.map((c) => [c.id, c.name]));

const productIdArg = process.argv.indexOf('--product-id');
const outputArg = process.argv.indexOf('--output');
const productId = productIdArg >= 0 ? process.argv[productIdArg + 1] : null;

if (!productId) {
  console.error('Usage: node scripts/render-reel.mjs --product-id <id>');
  process.exit(1);
}

const raw = catalog.products.find((p) => String(p.productId) === String(productId))
  || catalog.priorityProducts.find((p) => String(p.productId) === String(productId));

if (!raw || !raw.image) {
  console.error(`Product ${productId} not found or missing image`);
  process.exit(1);
}

const category = catMap[raw.categoryId] || 'Wholesale';
const copy = buildReelCopy(raw, category, brand);

console.log('Downloading product image...');
const imgRes = await fetch(raw.image);
const imgBuf = Buffer.from(await imgRes.arrayBuffer());
const mime = imgRes.headers.get('content-type') || 'image/jpeg';
const imageDataUrl = `data:${mime};base64,${imgBuf.toString('base64')}`;

const html = reelHtml({ brand, copy, imageDataUrl });
const tmpDir = join(ROOT, 'content/tmp');
const outDir = join(ROOT, 'content/reels');
mkdirSync(tmpDir, { recursive: true });
mkdirSync(outDir, { recursive: true });

const htmlPath = join(tmpDir, `reel-${productId}.html`);
const framePath = join(tmpDir, `frame-${productId}.png`);
const outputPath = outputArg >= 0
  ? process.argv[outputArg + 1]
  : join(outDir, `reel-${productId}-${Date.now()}.mp4`);

writeFileSync(htmlPath, html);

console.log('Rendering frame with Puppeteer...');
const browser = await puppeteer.launch({
  headless: true,
  args: ['--no-sandbox', '--disable-setuid-sandbox'],
});
const page = await browser.newPage();
await page.setViewport({ width: 1080, height: 1920, deviceScaleFactor: 1 });
await page.goto(`file://${htmlPath}`, { waitUntil: 'networkidle0', timeout: 30000 });
await page.screenshot({ path: framePath, type: 'png' });
await browser.close();

const duration = schedule.reelDurationSec || 12;
console.log(`Encoding ${duration}s Reel with subtle motion...`);

// Ken Burns zoom on full frame — subtle, professional
execSync(
  `ffmpeg -y -loop 1 -i "${framePath}" ` +
  `-vf "scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920,` +
  `zoompan=z='min(zoom+0.0008,1.08)':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=${duration * 30}:s=1080x1920:fps=30" ` +
  `-c:v libx264 -pix_fmt yuv420p -t ${duration} -movflags +faststart "${outputPath}"`,
  { stdio: 'inherit' }
);

const manifest = {
  productId: raw.productId,
  productName: raw.name,
  category,
  videoPath: outputPath,
  caption: copy.caption,
  copy,
  renderedAt: new Date().toISOString(),
};
const manifestPath = outputPath.replace('.mp4', '.json');
writeFileSync(manifestPath, JSON.stringify(manifest, null, 2));

console.log(`✓ Reel saved: ${outputPath}`);
console.log(`✓ Manifest: ${manifestPath}`);
