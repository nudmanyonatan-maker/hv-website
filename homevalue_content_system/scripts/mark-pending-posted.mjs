#!/usr/bin/env node
/** Mark products from pending-publish.json as posted in rotation state. */

import { readFileSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { markPosted } from './pick-next.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');
const pendingPath = join(ROOT, 'content/state/pending-publish.json');

const pending = JSON.parse(readFileSync(pendingPath, 'utf8'));
markPosted(pending.productIds, pending.categoryId);
console.log(`✓ Marked ${pending.productIds.length} products posted (${pending.category})`);
