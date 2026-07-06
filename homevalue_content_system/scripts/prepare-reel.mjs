#!/usr/bin/env node
/**
 * Render + upload a Reel — stops before Instagram publish.
 * For Cursor Automations: agent publishes via Composio MCP (no ak_ API key needed).
 *
 * Usage: FULLVENDOR_TOKEN=... node scripts/prepare-reel.mjs
 * Output: JSON manifest with videoUrl, caption, linkComment, composio args
 */

import { readFileSync, mkdirSync, writeFileSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';
import { pickNextProducts } from './pick-next.mjs';
import { uploadVideo } from './upload-catbox.mjs';
import { foodQuotaRemaining, foodRequiredPerDay, foodPostsToday } from './lib/food-schedule.mjs';
import { closeQualityBrowser } from './lib/image-quality.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const schedule = JSON.parse(readFileSync(join(ROOT, 'config/schedule.json'), 'utf8'));
const composio = JSON.parse(readFileSync(join(ROOT, 'config/composio.json'), 'utf8'));
const perReel = schedule.productsPerReel || 6;

if (process.env.FULLVENDOR_TOKEN) {
  execSync('node scripts/sync-catalog.mjs', { cwd: ROOT, stdio: 'inherit' });
}

let products;
let categoryId;
let isFood;
let imageRejected = [];

try {
  const picked = await pickNextProducts(perReel);
  products = picked.products;
  categoryId = picked.categoryId;
  isFood = picked.isFood;
  imageRejected = picked.imageRejected || [];
} finally {
  await closeQualityBrowser();
}

const batchId = products.map((p) => p.productId).join('-');
const idList = products.map((p) => p.productId).join(',');

const reelOut = join(ROOT, 'content/reels', `next-${batchId}.mp4`);
mkdirSync(dirname(reelOut), { recursive: true });

execSync(`node scripts/render-reel.mjs --product-ids ${idList} --output "${reelOut}"`, {
  cwd: ROOT,
  stdio: 'inherit',
});

const manifest = JSON.parse(readFileSync(reelOut.replace('.mp4', '.json'), 'utf8'));
const videoUrl = await uploadVideo(reelOut);

const prepared = {
  ready: true,
  videoUrl,
  videoPath: reelOut,
  caption: manifest.caption,
  linkComment: manifest.copy?.linkComment,
  category: manifest.category,
  categoryId,
  isFood,
  foodPostsToday: foodPostsToday(),
  foodRequiredPerDay: foodRequiredPerDay(),
  foodQuotaRemaining: foodQuotaRemaining(),
  imageRejectedCount: imageRejected.length,
  productIds: products.map((p) => p.productId),
  music: manifest.music,
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
        video_url: videoUrl,
        caption: manifest.caption,
        media_type: 'REELS',
        share_to_feed: true,
      },
    },
    {
      tool: 'INSTAGRAM_POST_IG_USER_MEDIA_PUBLISH',
      account: composio.instagram.accountId,
      note: 'Use creation_id from previous step',
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

const outPath = join(ROOT, 'content/state/pending-publish.json');
writeFileSync(outPath, JSON.stringify(prepared, null, 2));
console.log(JSON.stringify(prepared, null, 2));
