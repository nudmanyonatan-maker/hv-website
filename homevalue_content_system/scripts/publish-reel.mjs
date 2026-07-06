#!/usr/bin/env node
/**
 * Render + publish a carousel-style Reel (same category, shows in Reels tab).
 * Usage: node scripts/publish-reel.mjs [--dry-run]
 */

import { readFileSync, mkdirSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';
import { pickNextProducts, markPosted } from './pick-next.mjs';
import { uploadVideo } from './upload-catbox.mjs';
import { postLinkComment } from './post-link-comment.mjs';
import { closeQualityBrowser } from './lib/image-quality.mjs';
import { isFoodCategory, markFoodPosted } from './lib/food-schedule.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const composio = JSON.parse(readFileSync(join(ROOT, 'config/composio.json'), 'utf8'));
const schedule = JSON.parse(readFileSync(join(ROOT, 'config/schedule.json'), 'utf8'));
const dryRun = process.argv.includes('--dry-run');
const perReel = schedule.productsPerReel || 6;

let products;
let categoryId;
try {
  ({ products, categoryId } = await pickNextProducts(perReel));
} finally {
  await closeQualityBrowser();
}
const batchId = products.map((p) => p.productId).join('-');
const idList = products.map((p) => p.productId).join(',');

console.log(`→ ${products.length} products · one category`);

const reelOut = join(ROOT, 'content/reels', `next-${batchId}.mp4`);
mkdirSync(dirname(reelOut), { recursive: true });

execSync(`node scripts/render-reel.mjs --product-ids ${idList} --output "${reelOut}"`, {
  cwd: ROOT,
  stdio: 'inherit',
});

const manifest = JSON.parse(readFileSync(reelOut.replace('.mp4', '.json'), 'utf8'));

if (dryRun) {
  console.log('\n--- DRY RUN ---');
  console.log('Category:', manifest.category);
  console.log('Slides:', manifest.slides);
  console.log('Caption:\n', manifest.caption);
  process.exit(0);
}

console.log('Uploading...');
const videoUrl = await uploadVideo(reelOut);
console.log(`✓ ${videoUrl}`);

const apiKey = process.env.COMPOSIO_API_KEY?.trim();
if (!apiKey) {
  console.error('Missing COMPOSIO_API_KEY');
  process.exit(1);
}

async function composioExecute(toolSlug, args) {
  const res = await fetch(`https://backend.composio.dev/api/v3/tools/execute/${toolSlug}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'x-api-key': apiKey },
    body: JSON.stringify({
      connected_account_id: composio.instagram.accountId,
      arguments: args,
    }),
  });
  const data = await res.json();
  if (data?.error?.code === 10401 || data?.error?.status === 401) {
    const hint = apiKey.startsWith('ck_')
      ? '\n\nYou pasted a Consumer/MCP key (ck_…). GitHub Actions needs a Project API key (ak_…) from composio.dev → Settings → Project Settings → API Keys.'
      : '\n\nCheck COMPOSIO_API_KEY in GitHub Secrets — get a Project API key (ak_…) from composio.dev.';
    console.error(`Composio auth failed:${hint}`);
  }
  return data;
}

console.log('Creating Reel container...');
const containerData = await composioExecute('INSTAGRAM_POST_IG_USER_MEDIA', {
  ig_user_id: composio.instagram.igUserId,
  video_url: videoUrl,
  caption: manifest.caption,
  media_type: 'REELS',
  share_to_feed: true,
});

const creationId = containerData?.data?.id || containerData?.data?.creation_id;
if (!creationId) {
  console.error('Container failed:', JSON.stringify(containerData, null, 2));
  process.exit(1);
}

await new Promise((r) => setTimeout(r, 5000));

console.log('Publishing Reel...');
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

markPosted(products.map((p) => p.productId), categoryId);
console.log('✓ Done @hvhomevalue');
