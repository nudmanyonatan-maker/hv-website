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

console.log(`Publishing ${count} carousel(s) (${schedule.productsPerCarousel || 6} products each)...\n`);

for (let i = 0; i < count; i++) {
  console.log(`\n════════ Carousel ${i + 1} of ${count} ════════`);
  const cmd = dryRun
    ? 'node scripts/publish-carousel.mjs --dry-run'
    : 'node scripts/publish-carousel.mjs';
  execSync(cmd, { cwd: ROOT, stdio: 'inherit', env: process.env });
  if (!dryRun && i < count - 1) {
    console.log('Waiting 30s before next publish (rate limit)...');
    execSync('sleep 30');
  }
}

console.log(`\n✓ Batch complete — ${count} carousel(s) published`);
