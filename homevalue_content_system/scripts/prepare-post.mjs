#!/usr/bin/env node
/**
 * Render + upload Instagram post — carousel or single image.
 * Stops before publish; agent publishes via Composio MCP.
 *
 * Usage:
 *   POST_FORMAT=carousel node scripts/prepare-post.mjs
 *   POST_FORMAT=single node scripts/prepare-post.mjs
 */

import { readFileSync, readdirSync, mkdirSync, writeFileSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';
import { pickNextProducts } from './pick-next.mjs';
import { uploadImage } from './upload-catbox.mjs';
import { foodQuotaRemaining, foodRequiredPerDay, foodPostsToday } from './lib/food-schedule.mjs';
import { closeQualityBrowser } from './lib/image-quality.mjs';
import { categoryName } from './lib/copy-utils.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const schedule = JSON.parse(readFileSync(join(ROOT, 'config/schedule.json'), 'utf8'));
const composio = JSON.parse(readFileSync(join(ROOT, 'config/composio.json'), 'utf8'));
const catalog = JSON.parse(readFileSync(join(ROOT, 'catalog/products.json'), 'utf8'));
const catMap = Object.fromEntries(catalog.categories.map((c) => [String(c.id), c.name]));

const format = (process.env.POST_FORMAT || 'carousel').toLowerCase();
const perCarousel = schedule.productsPerCarousel || 6;

if (process.env.FULLVENDOR_TOKEN) {
  execSync('node scripts/sync-catalog.mjs', { cwd: ROOT, stdio: 'inherit' });
}

let products;
let categoryId;
let isFood;
let imageRejected = [];

try {
  const count = format === 'single' ? 1 : perCarousel;
  const picked = await pickNextProducts(count);
  products = picked.products;
  categoryId = picked.categoryId;
  isFood = picked.isFood;
  imageRejected = picked.imageRejected || [];
} finally {
  await closeQualityBrowser();
}

const batchId = products.map((p) => p.productId).join('-');
const category = categoryName(catMap, categoryId);

let prepared;

if (format === 'single') {
  const product = products[0];
  const outPath = join(ROOT, 'content/single-posts', `single-${product.productId}.jpg`);
  mkdirSync(dirname(outPath), { recursive: true });

  execSync(`node scripts/render-single-post.mjs --product-id ${product.productId} --output "${outPath}"`, {
    cwd: ROOT,
    stdio: 'inherit',
  });

  const manifest = JSON.parse(readFileSync(outPath.replace('.jpg', '.json'), 'utf8'));
  const imageUrl = await uploadImage(outPath);

  prepared = {
    ready: true,
    format: 'single',
    imageUrl,
    imagePath: outPath,
    caption: manifest.caption,
    linkComment: manifest.copy?.linkComment,
    category: manifest.category,
    categoryId,
    isFood,
    foodPostsToday: foodPostsToday(),
    foodRequiredPerDay: foodRequiredPerDay(),
    foodQuotaRemaining: foodQuotaRemaining(),
    imageRejectedCount: imageRejected.length,
    productIds: [product.productId],
    composio: {
      account: composio.instagram.accountId,
      igUserId: composio.instagram.igUserId,
      username: composio.instagram.username,
    },
    mcpPublishSteps: [
      {
        tool: 'INSTAGRAM_POST_IG_USER_MEDIA',
        account: composio.instagram.accountId,
        arguments: {
          ig_user_id: composio.instagram.igUserId,
          image_url: imageUrl,
          caption: manifest.caption,
        },
      },
      {
        tool: 'INSTAGRAM_POST_IG_USER_MEDIA_PUBLISH',
        account: composio.instagram.accountId,
        note: 'Use creation_id from previous step',
        arguments: {
          ig_user_id: composio.instagram.igUserId,
          max_wait_seconds: 120,
        },
      },
      {
        tool: 'INSTAGRAM_POST_IG_MEDIA_COMMENTS',
        account: composio.instagram.accountId,
        note: 'Use ig_media_id from publish step',
        arguments: {
          message: manifest.copy?.linkComment,
        },
      },
    ],
  };
} else {
  const idList = products.map((p) => p.productId).join(',');

  execSync(`node scripts/render-carousel.mjs --product-ids ${idList}`, {
    cwd: ROOT,
    stdio: 'inherit',
  });

  const carouselDirs = readdirSync(join(ROOT, 'content/carousels'))
    .filter((d) => d.startsWith(`carousel-${batchId}`))
    .sort()
    .reverse();

  if (!carouselDirs.length) {
    throw new Error('Carousel output not found');
  }

  const outDir = join(ROOT, 'content/carousels', carouselDirs[0]);
  const manifest = JSON.parse(readFileSync(join(outDir, 'manifest.json'), 'utf8'));

  console.log(`Uploading ${manifest.slides.length} carousel slides...`);
  const imageUrls = [];
  for (const slide of manifest.slides) {
    const url = await uploadImage(slide.path);
    imageUrls.push(url);
    console.log(`  ✓ Slide ${slide.index}: ${url}`);
  }

  prepared = {
    ready: true,
    format: 'carousel',
    imageUrls,
    outputDir: outDir,
    caption: manifest.caption,
    linkComment: manifest.copy?.linkComment,
    category,
    categoryId,
    isFood,
    foodPostsToday: foodPostsToday(),
    foodRequiredPerDay: foodRequiredPerDay(),
    foodQuotaRemaining: foodQuotaRemaining(),
    imageRejectedCount: imageRejected.length,
    productIds: products.map((p) => p.productId),
    slideCount: manifest.slides.length,
    composio: {
      account: composio.instagram.accountId,
      igUserId: composio.instagram.igUserId,
      username: composio.instagram.username,
    },
    mcpPublishSteps: [
      {
        tool: 'INSTAGRAM_CREATE_CAROUSEL_CONTAINER',
        account: composio.instagram.accountId,
        arguments: {
          ig_user_id: composio.instagram.igUserId,
          caption: manifest.caption,
          child_image_urls: imageUrls,
        },
      },
      {
        tool: 'INSTAGRAM_POST_IG_USER_MEDIA_PUBLISH',
        account: composio.instagram.accountId,
        note: 'Use creation_id from carousel container step',
        arguments: {
          ig_user_id: composio.instagram.igUserId,
          max_wait_seconds: 180,
        },
      },
      {
        tool: 'INSTAGRAM_POST_IG_MEDIA_COMMENTS',
        account: composio.instagram.accountId,
        note: 'Use ig_media_id from publish step',
        arguments: {
          message: manifest.copy?.linkComment,
        },
      },
    ],
  };
}

const outPath = join(ROOT, 'content/state/pending-publish.json');
writeFileSync(outPath, JSON.stringify(prepared, null, 2));
console.log(JSON.stringify(prepared, null, 2));
