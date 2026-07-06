#!/usr/bin/env node
/**
 * Launch a Cursor Cloud Agent to prepare + publish one Instagram Reel.
 * Used by GitHub Actions when the Automations UI can't select the repo.
 *
 * Requires env:
 *   CURSOR_API_KEY  — from cursor.com/dashboard → API Keys
 *   FULLVENDOR_TOKEN — FullVendor catalog sync
 */

const CURSOR_API_KEY = process.env.CURSOR_API_KEY?.trim();
const FULLVENDOR_TOKEN = process.env.FULLVENDOR_TOKEN?.trim();
const REPO_URL = process.env.REPO_URL || 'https://github.com/yonatannudman/hv-website';
const BRANCH = process.env.BRANCH || 'main';

if (!CURSOR_API_KEY) {
  console.error('Missing CURSOR_API_KEY — get one at https://cursor.com/dashboard → API Keys');
  process.exit(1);
}

const PROMPT = `You are the Home Value Instagram Reel publisher for @hvhomevalue. Publish exactly ONE Reel, then stop.

## Account (never change)
- Instagram: @hvhomevalue
- Composio account: instagram_lovely-tolan (NEVER instagram_story-algid / @vantagepeptide)
- IG User ID: 27741817182104982

## Step 1 — Prepare Reel
Run:
  cd homevalue_content_system && node scripts/prepare-reel.mjs

This picks products (food quota 2/day if unmet), skips bad images, renders, uploads.
Read content/state/pending-publish.json for videoUrl, caption, linkComment.

## Step 2 — Publish via Composio MCP
Use COMPOSIO_MULTI_EXECUTE_TOOL:

1. INSTAGRAM_POST_IG_USER_MEDIA — account instagram_lovely-tolan, media_type REELS, share_to_feed true
2. INSTAGRAM_POST_IG_USER_MEDIA_PUBLISH — max_wait_seconds 180
3. INSTAGRAM_POST_IG_MEDIA_COMMENTS — message = linkComment

## Step 3 — Mark posted
Run:
  cd homevalue_content_system && node scripts/mark-pending-posted.mjs

## Rules
- B2B wholesale, no public pricing, one category per Reel (6 products)
- Do NOT open PRs or commit files
- On failure: report which step failed

Reply with category, food quota status, permalink, music track, imageRejectedCount.`;

const body = {
  name: 'HV Instagram Reel — scheduled publish',
  prompt: { text: PROMPT },
  repos: [{ url: REPO_URL, startingRef: BRANCH }],
};

if (FULLVENDOR_TOKEN) {
  body.envVars = { FULLVENDOR_TOKEN };
}

const auth = Buffer.from(`${CURSOR_API_KEY}:`).toString('base64');

const res = await fetch('https://api.cursor.com/v1/agents', {
  method: 'POST',
  headers: {
    Authorization: `Basic ${auth}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify(body),
});

const data = await res.json();

if (!res.ok) {
  console.error('Cursor API error:', res.status, JSON.stringify(data, null, 2));
  process.exit(1);
}

const agentUrl = data.agent?.url || data.url;
const agentId = data.agent?.id || data.id;
console.log(JSON.stringify({ ok: true, agentId, agentUrl }, null, 2));
console.log(`\n✓ Cloud agent launched: ${agentUrl}`);
