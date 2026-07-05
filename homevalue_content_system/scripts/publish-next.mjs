#!/usr/bin/env node
/**
 * Publish next Instagram Reel (carousel-style video, one category).
 * Usage: node scripts/publish-next.mjs [--dry-run]
 */

import { execSync } from 'child_process';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const dryRun = process.argv.includes('--dry-run');
const cmd = dryRun
  ? 'node scripts/publish-reel.mjs --dry-run'
  : 'node scripts/publish-reel.mjs';

execSync(cmd, { cwd: ROOT, stdio: 'inherit', env: process.env });
