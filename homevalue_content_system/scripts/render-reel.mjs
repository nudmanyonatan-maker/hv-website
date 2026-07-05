#!/usr/bin/env node
/**
 * Render a 3-scene Reel with MULTIPLE products on screen at once.
 * Usage: node scripts/render-reel.mjs [--product-ids id1,id2,id3] [--output path.mp4]
 * Without --product-ids, picks next batch from rotation (default 3 products).
 */

import { readFileSync, writeFileSync, mkdirSync, unlinkSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';
import puppeteer from 'puppeteer';
import { buildReelCopy } from './lib/copy.mjs';
import { sceneRenderers } from './lib/reel-template.mjs';
import { pickNextProducts } from './pick-next.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const brand = JSON.parse(readFileSync(join(ROOT, 'config/brand.json'), 'utf8'));
const schedule = JSON.parse(readFileSync(join(ROOT, 'config/schedule.json'), 'utf8'));
const catalog = JSON.parse(readFileSync(join(ROOT, 'catalog/products.json'), 'utf8'));
const catMap = Object.fromEntries(catalog.categories.map((c) => [String(c.id), c.name]));

const idsArg = process.argv.indexOf('--product-ids');
const outputArg = process.argv.indexOf('--output');
const perReel = schedule.productsPerReel || 3;

let products;
if (idsArg >= 0) {
  const ids = process.argv[idsArg + 1].split(',').map((s) => s.trim());
  products = ids.map((id) => {
    const p = catalog.products.find((x) => String(x.productId) === id)
      || catalog.priorityProducts.find((x) => String(x.productId) === id);
    if (!p?.image) throw new Error(`Product ${id} not found or missing image`);
    return p;
  });
} else {
  ({ products } = pickNextProducts(perReel));
}

const batchId = products.map((p) => p.productId).join('-');
const copy = buildReelCopy(products, catMap, brand);

console.log(`Rendering Reel with ${products.length} products:`);
products.forEach((p) => console.log(`  • ${p.name}`));

console.log('Downloading product images...');
const productImages = await Promise.all(
  products.map(async (p) => {
    const res = await fetch(p.image);
    const buf = Buffer.from(await res.arrayBuffer());
    const mime = res.headers.get('content-type') || 'image/jpeg';
    return `data:${mime};base64,${buf.toString('base64')}`;
  })
);

const tmpDir = join(ROOT, 'content/tmp');
const outDir = join(ROOT, 'content/reels');
mkdirSync(tmpDir, { recursive: true });
mkdirSync(outDir, { recursive: true });

const outputPath = outputArg >= 0
  ? process.argv[outputArg + 1]
  : join(outDir, `reel-${batchId}-${Date.now()}.mp4`);

const sceneDurations = schedule.sceneDurations || [3.5, 4, 4.5];
const framePaths = [];

console.log('Rendering 3 scenes...');
const browser = await puppeteer.launch({
  headless: true,
  args: ['--no-sandbox', '--disable-setuid-sandbox', '--font-render-hinting=none'],
});

for (const scene of [1, 2, 3]) {
  const html = sceneRenderers[scene]({ brand, copy, productImages });
  const htmlPath = join(tmpDir, `scene${scene}-${batchId}.html`);
  const framePath = join(tmpDir, `scene${scene}-${batchId}.png`);
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

const clipPaths = framePaths.map((fp, i) => {
  const clip = join(tmpDir, `clip${i}-${batchId}.mp4`);
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

const silentPath = outputPath.replace('.mp4', '-silent.mp4');
execSync(
  `ffmpeg -y -i "${clipPaths[0]}" -i "${clipPaths[1]}" -i "${clipPaths[2]}" ` +
  `-filter_complex "${filter}" -map "[vout]" ` +
  `-c:v libx264 -preset slow -crf 18 -pix_fmt yuv420p -movflags +faststart -t ${totalDur - fade} "${silentPath}"`,
  { stdio: 'inherit' }
);

const musicCfg = schedule.backgroundMusic || { file: 'assets/bg-music.mp3', volume: 0.28 };
const musicPath = join(ROOT, musicCfg.file);
const vol = musicCfg.volume ?? 0.28;
const fadeOut = musicCfg.fadeOutSec ?? 1.5;
const videoDur = totalDur - fade;

console.log('Adding background music...');
execSync(
  `ffmpeg -y -i "${silentPath}" -stream_loop -1 -i "${musicPath}" ` +
  `-filter_complex "[1:a]volume=${vol},afade=t=in:st=0:d=0.5,afade=t=out:st=${videoDur - fadeOut}:d=${fadeOut}[a]" ` +
  `-map 0:v -map "[a]" -c:v copy -c:a aac -b:a 192k -shortest "${outputPath}"`,
  { stdio: 'inherit' }
);
try { unlinkSync(silentPath); } catch { /* ok */ }

const manifest = {
  productIds: products.map((p) => p.productId),
  products: products.map((p) => ({ id: p.productId, name: p.name })),
  videoPath: outputPath,
  caption: copy.caption,
  copy,
  scenes: 3,
  renderedAt: new Date().toISOString(),
};
writeFileSync(outputPath.replace('.mp4', '.json'), JSON.stringify(manifest, null, 2));
console.log(`✓ Reel saved: ${outputPath}`);
