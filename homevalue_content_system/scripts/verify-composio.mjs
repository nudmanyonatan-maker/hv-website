#!/usr/bin/env node
/** Preflight: verify COMPOSIO_API_KEY works before rendering a Reel. */

const apiKey = process.env.COMPOSIO_API_KEY?.trim();
if (!apiKey) {
  console.error('Missing COMPOSIO_API_KEY');
  process.exit(1);
}

if (apiKey.startsWith('ck_')) {
  console.error('Wrong key type: ck_ (MCP). Use Project API key (ak_…) from composio.dev → Settings → Project Settings → API Keys.');
  process.exit(1);
}

if (!apiKey.startsWith('ak_')) {
  console.error(`Unexpected key format (starts with "${apiKey.slice(0, 4)}…"). Expected ak_… Project API key.`);
  process.exit(1);
}

const res = await fetch('https://backend.composio.dev/api/v3/tools?limit=1', {
  headers: { 'x-api-key': apiKey },
});

if (res.status === 401) {
  const body = await res.json().catch(() => ({}));
  console.error('COMPOSIO_API_KEY rejected (401). Regenerate at composio.dev → Project Settings → API Keys, paste full ak_ key with no spaces.');
  console.error(JSON.stringify(body, null, 2));
  process.exit(1);
}

if (!res.ok) {
  console.error(`Composio check failed: HTTP ${res.status}`);
  process.exit(1);
}

console.log('✓ COMPOSIO_API_KEY valid');
