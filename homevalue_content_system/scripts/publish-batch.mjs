#!/usr/bin/env node
/**
 * Publish multiple carousels in one run.
 * Usage: node scripts/publish-batch.mjs [--count 3] [--dry-run]
 */

import { readFileSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');
const schedule = JSON.parse(readFileSync(join(ROOT, 'config/schedule.json'), 'utf8'));

const countArg = process.argv.indexOf('--count');
const count = countArg >= 0 ? parseInt(process.argv[countArg + 1], 10) : (schedule.carouselsPerBatch || 1);
const dryRun = process.argv.includes('--dry-run');

console.log(`Publishing ${count} Reel(s) (6 products · one category each)...\n`);

for (let i = 0; i < count; i++) {
  console.log(`\n════════ Reel ${i + 1} of ${count} ════════`);
  const cmd = dryRun
    ? 'node scripts/publish-reel.mjs --dry-run'
    : 'node scripts/publish-reel.mjs';
  execSync(cmd, { cwd: ROOT, stdio: 'inherit', env: process.env });
  if (!dryRun && i < count - 1) {
    console.log('Waiting 30s before next publish (rate limit)...');
    execSync('sleep 30');
  }
}

console.log(`\n✓ Batch complete — ${count} Reel(s) published`);
