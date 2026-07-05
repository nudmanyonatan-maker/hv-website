#!/usr/bin/env node
/**
 * Render a clean 3-scene Instagram Reel.
 * Scene 1: Product hero · Scene 2: Business callout · Scene 3: How to buy (3 steps)
 */

import { readFileSync, writeFileSync, mkdirSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';
import puppeteer from 'puppeteer';
import { buildReelCopy } from './lib/copy.mjs';
import { sceneRenderers } from './lib/reel-template.mjs';

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

if (!raw?.image) {
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

const tmpDir = join(ROOT, 'content/tmp');
const outDir = join(ROOT, 'content/reels');
mkdirSync(tmpDir, { recursive: true });
mkdirSync(outDir, { recursive: true });

const outputPath = outputArg >= 0
  ? process.argv[outputArg + 1]
  : join(outDir, `reel-${productId}-${Date.now()}.mp4`);

const framePaths = [];
const sceneDurations = [3.5, 4, 4.5]; // seconds per scene

console.log('Rendering 3 scenes...');
const browser = await puppeteer.launch({
  headless: true,
  args: ['--no-sandbox', '--disable-setuid-sandbox', '--font-render-hinting=none'],
});

for (const scene of [1, 2, 3]) {
  const html = sceneRenderers[scene]({ brand, copy, imageDataUrl });
  const htmlPath = join(tmpDir, `scene${scene}-${productId}.html`);
  const framePath = join(tmpDir, `scene${scene}-${productId}.png`);
  writeFileSync(htmlPath, html);

  const page = await browser.newPage();
  await page.setViewport({ width: 1080, height: 1920, deviceScaleFactor: 2 });
  await page.goto(`file://${htmlPath}`, { waitUntil: 'networkidle0', timeout: 30000 });
  await page.evaluate(() => document.fonts.ready);
  await new Promise((r) => setTimeout(r, 400));
  await page.screenshot({ path: framePath, type: 'png' });
  await page.close();
  framePaths.push(framePath);
  console.log(`  ✓ Scene ${scene}`);
}
await browser.close();

// Build video: static frames + smooth crossfades (no ugly zoom)
const clipPaths = framePaths.map((fp, i) => {
  const clip = join(tmpDir, `clip${i}-${productId}.mp4`);
  execSync(
    `ffmpeg -y -loop 1 -i "${fp}" -t ${sceneDurations[i]} ` +
    `-vf "scale=1080:1920:flags=lanczos,format=yuv420p" ` +
    `-c:v libx264 -preset slow -crf 18 -pix_fmt yuv420p -r 30 "${clip}"`,
    { stdio: 'pipe' }
  );
  return clip;
});

const totalDur = sceneDurations.reduce((a, b) => a + b, 0);
const fade = 0.4;
let filter = `[0:v][1:v]xfade=transition=fade:duration=${fade}:offset=${sceneDurations[0] - fade}[v01];`;
filter += `[v01][2:v]xfade=transition=fade:duration=${fade}:offset=${sceneDurations[0] + sceneDurations[1] - fade * 2}[vout]`;

execSync(
  `ffmpeg -y -i "${clipPaths[0]}" -i "${clipPaths[1]}" -i "${clipPaths[2]}" ` +
  `-filter_complex "${filter}" -map "[vout]" ` +
  `-c:v libx264 -preset slow -crf 18 -pix_fmt yuv420p -movflags +faststart -t ${totalDur - fade} "${outputPath}"`,
  { stdio: 'inherit' }
);

const manifest = {
  productId: raw.productId,
  productName: raw.name,
  category,
  videoPath: outputPath,
  caption: copy.caption,
  copy,
  scenes: 3,
  renderedAt: new Date().toISOString(),
};
writeFileSync(outputPath.replace('.mp4', '.json'), JSON.stringify(manifest, null, 2));

console.log(`✓ Reel saved: ${outputPath}`);
