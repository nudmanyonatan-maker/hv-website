#!/usr/bin/env node
/**
 * Full publish pipeline: pick product → render reel → upload → publish via Composio.
 * Usage: node scripts/publish-next.mjs [--dry-run] [--product-id 123]
 *
 * Requires COMPOSIO_API_KEY in env for automated publish (GitHub Actions).
 * Agent sessions can publish via Composio MCP instead.
 */

import { readFileSync, writeFileSync, mkdirSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';
import { pickNextProduct, markPosted } from './pick-next.mjs';
import { uploadToCatbox } from './upload-catbox.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const composio = JSON.parse(readFileSync(join(ROOT, 'config/composio.json'), 'utf8'));
const schedule = JSON.parse(readFileSync(join(ROOT, 'config/schedule.json'), 'utf8'));
const dryRun = process.argv.includes('--dry-run');
const productIdArg = process.argv.indexOf('--product-id');

let product;
if (productIdArg >= 0) {
  const catalog = JSON.parse(readFileSync(join(ROOT, 'catalog/products.json'), 'utf8'));
  product = catalog.priorityProducts.find((p) => String(p.productId) === process.argv[productIdArg + 1]);
  product = { ...product, _format: 'product_spotlight' };
} else {
  ({ product } = pickNextProduct());
}

if (!product) {
  console.error('No product available');
  process.exit(1);
}

console.log(`→ Product: ${product.name} (${product.productId})`);

const reelOut = join(ROOT, 'content/reels', `next-${product.productId}.mp4`);
mkdirSync(dirname(reelOut), { recursive: true });

execSync(`node scripts/render-reel.mjs --product-id ${product.productId} --output "${reelOut}"`, {
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

console.log('Uploading to catbox...');
const videoUrl = await uploadToCatbox(reelOut);
console.log(`✓ Public URL: ${videoUrl}`);

const publishPayload = {
  productId: product.productId,
  videoUrl,
  caption: manifest.caption,
  igUserId: composio.instagram.igUserId,
  composioAccountId: composio.instagram.accountId,
  publishedAt: null,
  status: 'ready',
};

const publishPath = join(ROOT, 'content/state/pending-publish.json');
mkdirSync(dirname(publishPath), { recursive: true });
writeFileSync(publishPath, JSON.stringify(publishPayload, null, 2));

const apiKey = process.env.COMPOSIO_API_KEY;
if (apiKey) {
  console.log('Publishing via Composio API...');
  const containerRes = await fetch('https://backend.composio.dev/api/v3/tools/execute/INSTAGRAM_POST_IG_USER_MEDIA', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'x-api-key': apiKey,
    },
    body: JSON.stringify({
      connected_account_id: composio.instagram.accountId,
      arguments: {
        ig_user_id: composio.instagram.igUserId,
        video_url: videoUrl,
        caption: manifest.caption,
        media_type: 'REELS',
      },
    }),
  });
  const containerData = await containerRes.json();
  const creationId = containerData?.data?.id || containerData?.data?.creation_id;

  if (!creationId) {
    console.error('Container creation failed:', JSON.stringify(containerData, null, 2));
    process.exit(1);
  }

  await new Promise((r) => setTimeout(r, 5000));

  const publishRes = await fetch('https://backend.composio.dev/api/v3/tools/execute/INSTAGRAM_POST_IG_USER_MEDIA_PUBLISH', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'x-api-key': apiKey,
    },
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
  console.log('Publish result:', JSON.stringify(publishData, null, 2));

  publishPayload.status = 'published';
  publishPayload.publishedAt = new Date().toISOString();
  publishPayload.igMediaId = publishData?.data?.id;
  writeFileSync(publishPath, JSON.stringify(publishPayload, null, 2));
  markPosted(product.productId);
  console.log('✓ Published to @hvhomevalue');
} else {
  console.log('\n✓ Reel ready — pending-publish.json written');
  console.log('  No COMPOSIO_API_KEY — publish via Composio MCP or add secret for GitHub Actions');
  console.log('  Account: instagram_lovely-tolan ONLY (never Vantage)');
}
