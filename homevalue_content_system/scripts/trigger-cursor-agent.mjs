#!/usr/bin/env node
/**
 * Launch a Cursor Cloud Agent to prepare + publish one Instagram post.
 *
 * Env:
 *   CURSOR_API_KEY
 *   FULLVENDOR_TOKEN
 *   POST_FORMAT — "carousel" (default) or "single"
 */

const CURSOR_API_KEY = process.env.CURSOR_API_KEY?.trim();
const FULLVENDOR_TOKEN = process.env.FULLVENDOR_TOKEN?.trim();
const POST_FORMAT = (process.env.POST_FORMAT || 'carousel').toLowerCase();
const REPO_URL = process.env.REPO_URL || 'https://github.com/yonatannudman/hv-website';
const BRANCH = process.env.BRANCH || 'main';

if (!CURSOR_API_KEY) {
  console.error('Missing CURSOR_API_KEY — get one at https://cursor.com/dashboard → API Keys');
  process.exit(1);
}

const formatLabel = POST_FORMAT === 'single' ? 'single-image post' : 'carousel';

const PROMPT = `You are the Home Value Instagram publisher for @hvhomevalue. Publish exactly ONE ${formatLabel}, then stop.

## Account (never change)
- Instagram: @hvhomevalue
- Composio account: instagram_lovely-tolan (NEVER instagram_story-algid / @vantagepeptide)
- IG User ID: 27741817182104982

## Step 1 — Prepare post
Run:
  cd homevalue_content_system && POST_FORMAT=${POST_FORMAT} node scripts/prepare-post.mjs

This picks products (food quota 2/day if unmet), skips bad images, renders, uploads.
Read content/state/pending-publish.json for caption, linkComment, and mcpPublishSteps.

## Step 2 — Publish via Composio MCP
Use COMPOSIO_MULTI_EXECUTE_TOOL — follow mcpPublishSteps in pending-publish.json exactly.

For carousel: INSTAGRAM_CREATE_CAROUSEL_CONTAINER → PUBLISH → COMMENT
For single image: INSTAGRAM_POST_IG_USER_MEDIA (image_url) → PUBLISH → COMMENT

## Step 3 — Mark posted
Run:
  cd homevalue_content_system && node scripts/mark-pending-posted.mjs

## Rules
- B2B wholesale, no public pricing, one category per post
- Product images show the FULL product (hero layout) — do not re-render
- Do NOT open PRs or commit files
- On failure: report which step failed

Reply with format, category, food quota status, permalink, imageRejectedCount.`;

const body = {
  name: `HV Instagram ${POST_FORMAT} — scheduled publish`,
  prompt: { text: PROMPT },
  repos: [{ url: REPO_URL, startingRef: BRANCH }],
};

if (FULLVENDOR_TOKEN) {
  body.envVars = { FULLVENDOR_TOKEN, POST_FORMAT };
} else {
  body.envVars = { POST_FORMAT };
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
console.log(JSON.stringify({ ok: true, format: POST_FORMAT, agentId, agentUrl }, null, 2));
console.log(`\n✓ Cloud agent launched (${POST_FORMAT}): ${agentUrl}`);
