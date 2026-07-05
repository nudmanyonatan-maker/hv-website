#!/usr/bin/env node
/**
 * Publish one multi-product Reel + link comment.
 * Usage: node scripts/publish-next.mjs [--dry-run]
 */

import { readFileSync, writeFileSync, mkdirSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';
import { pickNextProducts, markPosted } from './pick-next.mjs';
import { uploadToCatbox } from './upload-catbox.mjs';
import { postLinkComment } from './post-link-comment.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const composio = JSON.parse(readFileSync(join(ROOT, 'config/composio.json'), 'utf8'));
const schedule = JSON.parse(readFileSync(join(ROOT, 'config/schedule.json'), 'utf8'));
const dryRun = process.argv.includes('--dry-run');
const perReel = schedule.productsPerReel || 3;

const { products } = pickNextProducts(perReel);
const batchId = products.map((p) => p.productId).join('-');

console.log(`→ ${products.length} products: ${products.map((p) => p.name).join(' | ')}`);

const reelOut = join(ROOT, 'content/reels', `next-${batchId}.mp4`);
mkdirSync(dirname(reelOut), { recursive: true });

const idList = products.map((p) => p.productId).join(',');
execSync(`node scripts/render-reel.mjs --product-ids ${idList} --output "${reelOut}"`, {
  cwd: ROOT,
  stdio: 'inherit',
});

const manifest = JSON.parse(readFileSync(reelOut.replace('.mp4', '.json'), 'utf8'));

if (dryRun) {
  console.log('\n--- DRY RUN ---');
  console.log('Caption:\n', manifest.caption);
  console.log('Video:', reelOut);
  process.exit(0);
}

console.log('Uploading...');
const videoUrl = await uploadToCatbox(reelOut);
console.log(`✓ ${videoUrl}`);

const apiKey = process.env.COMPOSIO_API_KEY;
if (!apiKey) {
  console.error('Missing COMPOSIO_API_KEY');
  process.exit(1);
}

const containerRes = await fetch('https://backend.composio.dev/api/v3/tools/execute/INSTAGRAM_POST_IG_USER_MEDIA', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json', 'x-api-key': apiKey },
  body: JSON.stringify({
    connected_account_id: composio.instagram.accountId,
    arguments: {
      ig_user_id: composio.instagram.igUserId,
      video_url: videoUrl,
      caption: manifest.caption,
      media_type: 'REELS',
      share_to_feed: true,
    },
  }),
});
const containerData = await containerRes.json();
const creationId = containerData?.data?.id || containerData?.data?.creation_id;
if (!creationId) {
  console.error('Container failed:', JSON.stringify(containerData, null, 2));
  process.exit(1);
}

await new Promise((r) => setTimeout(r, 5000));

const publishRes = await fetch('https://backend.composio.dev/api/v3/tools/execute/INSTAGRAM_POST_IG_USER_MEDIA_PUBLISH', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json', 'x-api-key': apiKey },
  body: JSON.stringify({
    connected_account_id: composio.instagram.accountId,
    arguments: {
      ig_user_id: composio.instagram.igUserId,
      creation_id: String(creationId),
      max_wait_seconds: 120,
    },
  }),
});
const publishData = await publishRes.json();
const igMediaId = publishData?.data?.id;
console.log('✓ Published:', igMediaId);

if (igMediaId && manifest.copy?.linkComment) {
  await postLinkComment({
    igMediaId,
    message: manifest.copy.linkComment,
    composioAccountId: composio.instagram.accountId,
    apiKey,
  });
  console.log('✓ Link in comments');
}

markPosted(products.map((p) => p.productId));
console.log('✓ Done @hvhomevalue');
