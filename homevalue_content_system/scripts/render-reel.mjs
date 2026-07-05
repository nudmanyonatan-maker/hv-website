#!/usr/bin/env node
/**
 * Render a Reel — multiple products, rotating visual format, 2 scenes.
 */

import { readFileSync, writeFileSync, mkdirSync, unlinkSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';
import puppeteer from 'puppeteer';
import { buildReelCopy } from './lib/copy.mjs';
import { categoryName } from './lib/copy-utils.mjs';
import { renderScene } from './lib/formats.mjs';
import { pickNextProducts } from './pick-next.mjs';
import { pickNextMusic } from './pick-next-music.mjs';
import { pickNextFormat } from './pick-next-format.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const brand = JSON.parse(readFileSync(join(ROOT, 'config/brand.json'), 'utf8'));
const schedule = JSON.parse(readFileSync(join(ROOT, 'config/schedule.json'), 'utf8'));
const catalog = JSON.parse(readFileSync(join(ROOT, 'catalog/products.json'), 'utf8'));
const catMap = Object.fromEntries(catalog.categories.map((c) => [String(c.id), c.name]));

const idsArg = process.argv.indexOf('--product-ids');
const outputArg = process.argv.indexOf('--output');
const formatArg = process.argv.indexOf('--format');
const perReel = schedule.productsPerReel || 3;

let products;
if (idsArg >= 0) {
  const ids = process.argv[idsArg + 1].split(',').map((s) => s.trim());
  products = ids.map((id) => {
    const p = catalog.products.find((x) => String(x.productId) === id);
    if (!p?.image) throw new Error(`Product ${id} not found or missing image`);
    return p;
  });
} else {
  ({ products } = pickNextProducts(perReel));
}

const format = formatArg >= 0
  ? { id: process.argv[formatArg + 1], name: process.argv[formatArg + 1] }
  : pickNextFormat();

const batchId = products.map((p) => p.productId).join('-');
const copy = buildReelCopy(products, catMap, brand);

console.log(`Format: ${format.name} (${format.id})`);
console.log(`Products (${products.length}) — mixed categories:`);
products.forEach((p) => console.log(`  • [${categoryName(catMap, p.categoryId)}] ${p.name}`));

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
  : join(outDir, `reel-${format.id}-${batchId}-${Date.now()}.mp4`);

const sceneDurations = schedule.sceneDurations || [5, 4.5];
const sceneCount = 2;
const framePaths = [];

console.log(`Rendering ${sceneCount} scenes...`);
const browser = await puppeteer.launch({
  headless: true,
  args: ['--no-sandbox', '--disable-setuid-sandbox', '--font-render-hinting=none'],
});

for (let scene = 1; scene <= sceneCount; scene++) {
  const html = renderScene(format.id, scene, copy, productImages);
  const htmlPath = join(tmpDir, `${format.id}-s${scene}-${batchId}.html`);
  const framePath = join(tmpDir, `${format.id}-s${scene}-${batchId}.png`);
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

const totalDur = sceneDurations.slice(0, sceneCount).reduce((a, b) => a + b, 0);
const fade = 0.35;
const silentPath = outputPath.replace('.mp4', '-silent.mp4');

execSync(
  `ffmpeg -y -i "${clipPaths[0]}" -i "${clipPaths[1]}" ` +
  `-filter_complex "[0:v][1:v]xfade=transition=fade:duration=${fade}:offset=${sceneDurations[0] - fade}[vout]" ` +
  `-map "[vout]" -c:v libx264 -preset slow -crf 18 -pix_fmt yuv420p -movflags +faststart ` +
  `-t ${totalDur - fade} "${silentPath}"`,
  { stdio: 'inherit' }
);

const musicCfg = schedule.backgroundMusic || { volume: 0.28, fadeOutSec: 1.5 };
const { track: musicTrack, filePath: musicPath } = pickNextMusic();
const vol = musicCfg.volume ?? 0.28;
const fadeOut = musicCfg.fadeOutSec ?? 1.5;
const videoDur = totalDur - fade;

console.log(`Music: ${musicTrack.name}`);
execSync(
  `ffmpeg -y -i "${silentPath}" -stream_loop -1 -i "${musicPath}" ` +
  `-filter_complex "[1:a]volume=${vol},afade=t=in:st=0:d=0.5,afade=t=out:st=${videoDur - fadeOut}:d=${fadeOut}[a]" ` +
  `-map 0:v -map "[a]" -c:v copy -c:a aac -b:a 192k -shortest "${outputPath}"`,
  { stdio: 'inherit' }
);
try { unlinkSync(silentPath); } catch { /* ok */ }

const manifest = {
  format: { id: format.id, name: format.name },
  productIds: products.map((p) => p.productId),
  products: products.map((p) => ({ id: p.productId, name: p.name, category: categoryName(catMap, p.categoryId) })),
  videoPath: outputPath,
  caption: copy.caption,
  copy,
  music: { id: musicTrack.id, name: musicTrack.name },
  renderedAt: new Date().toISOString(),
};
writeFileSync(outputPath.replace('.mp4', '.json'), JSON.stringify(manifest, null, 2));
console.log(`✓ Reel saved: ${outputPath}`);
