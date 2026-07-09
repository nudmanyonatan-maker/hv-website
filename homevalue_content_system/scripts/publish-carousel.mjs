#!/usr/bin/env node
/**
 * Render + publish an Instagram carousel (cover + products + CTA).
 * Usage: node scripts/publish-carousel.mjs [--dry-run]
 */

import { readFileSync, readdirSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';
import { pickNextProducts, markPosted } from './pick-next.mjs';
import { uploadImage } from './upload-catbox.mjs';
import { postLinkComment } from './post-link-comment.mjs';
import { closeQualityBrowser } from './lib/image-quality.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const composio = JSON.parse(readFileSync(join(ROOT, 'config/composio.json'), 'utf8'));
const schedule = JSON.parse(readFileSync(join(ROOT, 'config/schedule.json'), 'utf8'));
const dryRun = process.argv.includes('--dry-run');
const productCount = schedule.productsPerCarousel || 6;

let products;
try {
  ({ products } = await pickNextProducts(productCount));
} finally {
  await closeQualityBrowser();
}
const batchId = products.map((p) => p.productId).join('-');
const idList = products.map((p) => p.productId).join(',');

console.log(`→ ${products.length} products for carousel`);

execSync(`node scripts/render-carousel.mjs --product-ids ${idList}`, {
  cwd: ROOT,
  stdio: 'inherit',
});

const carouselDirs = readdirSync(join(ROOT, 'content/carousels'))
  .filter((d) => d.startsWith(`carousel-${batchId}`))
  .sort()
  .reverse();

if (!carouselDirs.length) {
  console.error('Carousel output not found');
  process.exit(1);
}

const outDir = join(ROOT, 'content/carousels', carouselDirs[0]);
const manifest = JSON.parse(readFileSync(join(outDir, 'manifest.json'), 'utf8'));

if (dryRun) {
  console.log('\n--- DRY RUN ---');
  console.log('Slides:', manifest.slides.length);
  console.log('Caption:\n', manifest.caption);
  process.exit(0);
}

console.log(`Uploading ${manifest.slides.length} slides...`);
const imageUrls = [];
for (const slide of manifest.slides) {
  const url = await uploadImage(slide.path);
  imageUrls.push(url);
  console.log(`  ✓ Slide ${slide.index}: ${url}`);
}

const apiKey = process.env.COMPOSIO_API_KEY;

async function composioExecute(toolSlug, args) {
  if (apiKey) {
    const res = await fetch(`https://backend.composio.dev/api/v3/tools/execute/${toolSlug}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'x-api-key': apiKey },
      body: JSON.stringify({
        connected_account_id: composio.instagram.accountId,
        arguments: args,
      }),
    });
    return res.json();
  }
  throw new Error('Missing COMPOSIO_API_KEY — set env or use Composio MCP');
}

let creationId;

if (apiKey) {
  console.log('Creating carousel container...');
  const containerData = await composioExecute('INSTAGRAM_CREATE_CAROUSEL_CONTAINER', {
    ig_user_id: composio.instagram.igUserId,
    caption: manifest.caption,
    child_image_urls: imageUrls,
  });

  creationId = containerData?.data?.id || containerData?.data?.creation_id;
  if (!creationId) {
    console.error('Carousel container failed:', JSON.stringify(containerData, null, 2));
    process.exit(1);
  }
  console.log(`✓ Container: ${creationId}`);

  await new Promise((r) => setTimeout(r, 3000));

  console.log('Publishing carousel...');
  const publishData = await composioExecute('INSTAGRAM_POST_IG_USER_MEDIA_PUBLISH', {
    ig_user_id: composio.instagram.igUserId,
    creation_id: String(creationId),
    max_wait_seconds: 120,
  });

  const igMediaId = publishData?.data?.id;
  if (!igMediaId) {
    console.error('Publish failed:', JSON.stringify(publishData, null, 2));
    process.exit(1);
  }

  console.log('✓ Published:', igMediaId);

  if (manifest.copy?.linkComment) {
    await postLinkComment({
      igMediaId,
      message: manifest.copy.linkComment,
      composioAccountId: composio.instagram.accountId,
      apiKey,
    });
    console.log('✓ Link in comments');
  }

  markPosted(products.map((p) => p.productId), manifest.categoryId || products[0]?.categoryId);
  console.log('✓ Done @hvhomevalue');
} else {
  console.error('Missing COMPOSIO_API_KEY');
  process.exit(1);
}
