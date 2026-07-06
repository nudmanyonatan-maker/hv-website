#!/usr/bin/env node
/**
 * GitHub Actions entrypoint — picks the best publish path from available secrets.
 *
 * Priority:
 *   1. CURSOR_API_KEY  → launch Cursor Cloud Agent (Composio MCP, no ak_ key)
 *   2. COMPOSIO_API_KEY → render + publish via Composio REST on the runner
 */

import { execSync } from 'child_process';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const cursorKey = process.env.CURSOR_API_KEY?.trim();
const composioKey = process.env.COMPOSIO_API_KEY?.trim();
const fullvendor = process.env.FULLVENDOR_TOKEN?.trim();

if (cursorKey) {
  console.log('→ Using Cursor Cloud Agent (Composio MCP)');
  execSync('node scripts/trigger-cursor-agent.mjs', { cwd: ROOT, stdio: 'inherit', env: process.env });
  process.exit(0);
}

if (composioKey) {
  console.log('→ Using Composio REST on GitHub runner');
  execSync('node scripts/verify-composio.mjs', { cwd: ROOT, stdio: 'inherit', env: process.env });
  execSync('node scripts/publish-next.mjs', { cwd: ROOT, stdio: 'inherit', env: process.env });
  process.exit(0);
}

console.error(`
No working publish credentials found in GitHub Secrets.

Add ONE of:
  • CURSOR_API_KEY   — recommended (cursor.com/dashboard → API Keys)
  • COMPOSIO_API_KEY — Project API key (ak_…) from composio.dev

FULLVENDOR_TOKEN is also required for catalog sync.
`);
process.exit(1);
