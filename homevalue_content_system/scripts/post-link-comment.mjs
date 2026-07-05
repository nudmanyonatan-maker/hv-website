#!/usr/bin/env node
/** Post the wholesale link as the first comment on a published Reel. */

import { readFileSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { buildReelCopy } from './lib/copy.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

export async function postLinkComment({ igMediaId, message, composioAccountId, apiKey }) {
  const res = await fetch('https://backend.composio.dev/api/v3/tools/execute/INSTAGRAM_POST_IG_MEDIA_COMMENTS', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'x-api-key': apiKey,
    },
    body: JSON.stringify({
      connected_account_id: composioAccountId,
      arguments: { ig_media_id: String(igMediaId), message },
    }),
  });
  const data = await res.json();
  if (!data?.data?.id && !data?.successful) {
    throw new Error(`Comment failed: ${JSON.stringify(data)}`);
  }
  return data?.data?.id || data?.data;
}

export function getLinkCommentMessage(brand, productName) {
  const copy = buildReelCopy({ name: productName }, 'Wholesale', brand);
  return copy.linkComment;
}

if (process.argv[1]?.endsWith('post-link-comment.mjs')) {
  const igMediaId = process.argv[2];
  if (!igMediaId) {
    console.error('Usage: COMPOSIO_API_KEY=... node scripts/post-link-comment.mjs <ig_media_id>');
    process.exit(1);
  }
  const brand = JSON.parse(readFileSync(join(ROOT, 'config/brand.json'), 'utf8'));
  const composio = JSON.parse(readFileSync(join(ROOT, 'config/composio.json'), 'utf8'));
  const message = getLinkCommentMessage(brand, 'Wholesale');
  const id = await postLinkComment({
    igMediaId,
    message,
    composioAccountId: composio.instagram.accountId,
    apiKey: process.env.COMPOSIO_API_KEY,
  });
  console.log('✓ Comment posted:', id);
}
