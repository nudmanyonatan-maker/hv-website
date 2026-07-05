#!/usr/bin/env node
/**
 * Render a carousel-style Reel — cover + one product per scene + CTA.
 * Posts to Instagram Reels (video), same category per post.
 */

import { readFileSync, writeFileSync, mkdirSync, unlinkSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';
import puppeteer from 'puppeteer';
import { buildReelCopy } from './lib/copy.mjs';
import { categoryName } from './lib/copy-utils.mjs';
import { renderCoverSlide, renderCtaSlide, renderProductSlide, styleForIndex } from './lib/reel-slides.mjs';
import { pickNextProducts } from './pick-next.mjs';
import { pickNextMusic } from './pick-next-music.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const brand = JSON.parse(readFileSync(join(ROOT, 'config/brand.json'), 'utf8'));
const schedule = JSON.parse(readFileSync(join(ROOT, 'config/schedule.json'), 'utf8'));
const catalog = JSON.parse(readFileSync(join(ROOT, 'catalog/products.json'), 'utf8'));
const catMap = Object.fromEntries(catalog.categories.map((c) => [String(c.id), c.name]));

const idsArg = process.argv.indexOf('--product-ids');
const outputArg = process.argv.indexOf('--output');
const perReel = schedule.productsPerReel || 6;

let products;
let categoryId;
if (idsArg >= 0) {
  const ids = process.argv[idsArg + 1].split(',').map((s) => s.trim());
  products = ids.map((id) => {
    const p = catalog.products.find((x) => String(x.productId) === id);
    if (!p?.image) throw new Error(`Product ${id} not found or missing image`);
    return p;
  });
  categoryId = products[0]?.categoryId;
} else {
  ({ products, categoryId } = pickNextProducts(perReel));
}

const copy = buildReelCopy(products, catMap, brand);
const batchId = products.map((p) => p.productId).join('-');

console.log(`Category: ${copy.category}`);
products.forEach((p) => console.log(`  • ${copy.products.find((x) => String(x.productId) === String(p.productId))?.name}`));

const productImages = await Promise.all(
  products.map(async (p) => {
    const res = await fetch(p.image);
    const buf = Buffer.from(await res.arrayBuffer());
    const mime = res.headers.get('content-type') || 'image/jpeg';
    return `data:${mime};base64,${buf.toString('base64')}`;
  })
);

const slideDefs = [
  { type: 'cover', html: renderCoverSlide(copy, productImages), duration: schedule.coverDurationSec || 3 },
  ...copy.products.map((item, i) => ({
    type: 'product',
    html: renderProductSlide(item, productImages[i], styleForIndex(i)),
    duration: schedule.productDurationSec || 2.5,
    style: styleForIndex(i),
  })),
  { type: 'cta', html: renderCtaSlide(copy), duration: schedule.ctaDurationSec || 3.5 },
];

const tmpDir = join(ROOT, 'content/tmp');
const outDir = join(ROOT, 'content/reels');
mkdirSync(tmpDir, { recursive: true });
mkdirSync(outDir, { recursive: true });

const outputPath = outputArg >= 0
  ? process.argv[outputArg + 1]
  : join(outDir, `reel-carousel-${batchId}-${Date.now()}.mp4`);

console.log(`Rendering ${slideDefs.length} slides as Reel...`);

const browser = await puppeteer.launch({
  headless: true,
  args: ['--no-sandbox', '--disable-setuid-sandbox', '--font-render-hinting=none'],
});

const framePaths = [];

for (let i = 0; i < slideDefs.length; i++) {
  const slide = slideDefs[i];
  const htmlPath = join(tmpDir, `reel-${batchId}-s${i}.html`);
  const framePath = join(tmpDir, `reel-${batchId}-s${i}.png`);
  writeFileSync(htmlPath, slide.html);

  const page = await browser.newPage();
  await page.setViewport({ width: 1080, height: 1920, deviceScaleFactor: 2 });
  await page.goto(`file://${htmlPath}`, { waitUntil: 'networkidle0', timeout: 30000 });
  await page.evaluate(() => document.fonts.ready);
  await new Promise((r) => setTimeout(r, 400));
  await page.screenshot({ path: framePath, type: 'png' });
  await page.close();
  framePaths.push(framePath);
  console.log(`  ✓ Slide ${i + 1}/${slideDefs.length} — ${slide.type}${slide.style ? ` (${slide.style})` : ''}`);
}

await browser.close();

const clipPaths = framePaths.map((fp, i) => {
  const clip = join(tmpDir, `reel-clip${i}-${batchId}.mp4`);
  execSync(
    `ffmpeg -y -loop 1 -i "${fp}" -t ${slideDefs[i].duration} ` +
    `-vf "scale=1080:1920:flags=lanczos,format=yuv420p" ` +
    `-c:v libx264 -preset slow -crf 18 -pix_fmt yuv420p -r 30 "${clip}"`,
    { stdio: 'pipe' }
  );
  return clip;
});

const fade = schedule.slideFadeSec || 0.3;
const silentPath = outputPath.replace('.mp4', '-silent.mp4');

if (clipPaths.length === 1) {
  execSync(`ffmpeg -y -i "${clipPaths[0]}" -c copy "${silentPath}"`, { stdio: 'pipe' });
} else {
  let filter = '';
  let lastLabel = '[0:v]';
  let offset = slideDefs[0].duration - fade;

  for (let i = 1; i < clipPaths.length; i++) {
    const outLabel = i === clipPaths.length - 1 ? '[vout]' : `[v${i}]`;
    filter += `${lastLabel}[${i}:v]xfade=transition=fade:duration=${fade}:offset=${offset}${outLabel};`;
    lastLabel = outLabel;
    offset += slideDefs[i].duration - fade;
  }

  const inputs = clipPaths.map((p) => `-i "${p}"`).join(' ');
  const totalDur = slideDefs.reduce((a, s) => a + s.duration, 0) - fade * (clipPaths.length - 1);

  execSync(
    `ffmpeg -y ${inputs} -filter_complex "${filter.slice(0, -1)}" ` +
    `-map "[vout]" -c:v libx264 -preset slow -crf 18 -pix_fmt yuv420p -movflags +faststart ` +
    `-t ${totalDur} "${silentPath}"`,
    { stdio: 'inherit' }
  );
}

const musicCfg = schedule.backgroundMusic || { volume: 0.28, fadeOutSec: 1.5 };
const { track: musicTrack, filePath: musicPath } = pickNextMusic();
const vol = musicCfg.volume ?? 0.28;
const fadeOut = musicCfg.fadeOutSec ?? 1.5;

const probeOut = execSync(`ffprobe -v error -show_entries format=duration -of csv=p=0 "${silentPath}"`, { encoding: 'utf8' });
const videoDur = parseFloat(probeOut.trim());

console.log(`Music: ${musicTrack.name}`);
execSync(
  `ffmpeg -y -i "${silentPath}" -stream_loop -1 -i "${musicPath}" ` +
  `-filter_complex "[1:a]volume=${vol},afade=t=in:st=0:d=0.5,afade=t=out:st=${Math.max(0, videoDur - fadeOut)}:d=${fadeOut}[a]" ` +
  `-map 0:v -map "[a]" -c:v copy -c:a aac -b:a 192k -shortest "${outputPath}"`,
  { stdio: 'inherit' }
);
try { unlinkSync(silentPath); } catch { /* ok */ }

const manifest = {
  type: 'reel-carousel',
  category: copy.category,
  categoryId,
  productIds: products.map((p) => p.productId),
  products: copy.products,
  slides: slideDefs.length,
  videoPath: outputPath,
  caption: copy.caption,
  copy,
  music: { id: musicTrack.id, name: musicTrack.name },
  renderedAt: new Date().toISOString(),
};
writeFileSync(outputPath.replace('.mp4', '.json'), JSON.stringify(manifest, null, 2));
console.log(`✓ Reel saved: ${outputPath}`);
