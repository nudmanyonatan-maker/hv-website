#!/usr/bin/env node
/**
 * Pick next product for posting — rotates through in-stock catalog, never repeats until cycle complete.
 */

import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');
const STATE_PATH = join(ROOT, 'content/state/rotation.json');
const catalog = JSON.parse(readFileSync(join(ROOT, 'catalog/products.json'), 'utf8'));

const formats = [
  'product_spotlight', 'did_you_know', 'case_size_callout', 'buyer_tip', 'in_stock_now',
];

function loadState() {
  if (!existsSync(STATE_PATH)) {
    return { postedIds: [], lastPostedAt: null, cycle: 0 };
  }
  return JSON.parse(readFileSync(STATE_PATH, 'utf8'));
}

function saveState(state) {
  mkdirSync(dirname(STATE_PATH), { recursive: true });
  writeFileSync(STATE_PATH, JSON.stringify(state, null, 2));
}

export function pickNextProduct() {
  const state = loadState();
  const eligible = catalog.priorityProducts.filter((p) => p.inStock && p.hasImage);
  const remaining = eligible.filter((p) => !state.postedIds.includes(String(p.productId)));

  let pool = remaining.length > 0 ? remaining : eligible;
  if (remaining.length === 0 && eligible.length > 0) {
    state.postedIds = [];
    state.cycle += 1;
    pool = eligible;
  }

  const idx = state.postedIds.length % pool.length;
  const product = { ...pool[idx] };
  const format = formats[state.postedIds.length % formats.length];
  product._format = format;

  return { product, state, category: catalog.categories.find((c) => String(c.id) === String(product.categoryId))?.name };
}

export function markPosted(productId) {
  const state = loadState();
  if (!state.postedIds.includes(String(productId))) {
    state.postedIds.push(String(productId));
  }
  state.lastPostedAt = new Date().toISOString();
  saveState(state);
}

if (process.argv[1]?.endsWith('pick-next.mjs')) {
  const { product } = pickNextProduct();
  console.log(JSON.stringify(product, null, 2));
}
