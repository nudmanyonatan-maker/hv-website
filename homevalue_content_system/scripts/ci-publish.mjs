#!/usr/bin/env node
/**
 * GitHub Actions entrypoint — carousel or single image post.
 *
 * Env:
 *   POST_FORMAT — carousel | single (default carousel)
 *   CURSOR_API_KEY or COMPOSIO_API_KEY
 *   FULLVENDOR_TOKEN
 */

import { execSync } from 'child_process';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const format = (process.env.POST_FORMAT || 'carousel').toLowerCase();
const cursorKey = process.env.CURSOR_API_KEY?.trim();
const composioKey = process.env.COMPOSIO_API_KEY?.trim();

process.env.POST_FORMAT = format;

if (cursorKey) {
  console.log(`→ Using Cursor Cloud Agent (${format})`);
  execSync('node scripts/trigger-cursor-agent.mjs', { cwd: ROOT, stdio: 'inherit', env: process.env });
  process.exit(0);
}

if (composioKey) {
  console.log(`→ Using Composio REST on runner (${format})`);
  execSync('node scripts/verify-composio.mjs', { cwd: ROOT, stdio: 'inherit', env: process.env });
  if (format === 'single') {
    execSync('node scripts/prepare-post.mjs', { cwd: ROOT, stdio: 'inherit', env: process.env });
    console.error('Composio REST publish-from-pending not yet automated — use CURSOR_API_KEY');
    process.exit(1);
  }
  execSync('node scripts/publish-carousel.mjs', { cwd: ROOT, stdio: 'inherit', env: process.env });
  process.exit(0);
}

console.error(`
No working publish credentials found in GitHub Secrets.

Add CURSOR_API_KEY (recommended) or COMPOSIO_API_KEY, plus FULLVENDOR_TOKEN.
`);
process.exit(1);
