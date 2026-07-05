#!/usr/bin/env node
/**
 * Pick next N products for a multi-product Reel.
 */

import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');
const STATE_PATH = join(ROOT, 'content/state/rotation.json');

function loadCatalog() {
  return JSON.parse(readFileSync(join(ROOT, 'catalog/products.json'), 'utf8'));
}

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

export function pickNextProducts(count = 3) {
  const catalog = loadCatalog();
  const state = loadState();
  const eligible = catalog.priorityProducts.filter((p) => p.inStock && p.hasImage);
  let remaining = eligible.filter((p) => !state.postedIds.includes(String(p.productId)));

  if (remaining.length < count) {
    state.postedIds = [];
    state.cycle += 1;
    remaining = eligible;
  }

  const products = remaining.slice(0, count).map((p) => ({ ...p }));
  return { products, state, catalog };
}

export function markPosted(productIds) {
  const state = loadState();
  for (const id of productIds) {
    const s = String(id);
    if (!state.postedIds.includes(s)) state.postedIds.push(s);
  }
  state.lastPostedAt = new Date().toISOString();
  saveState(state);
}

/** @deprecated use pickNextProducts */
export function pickNextProduct() {
  const { products, catalog } = pickNextProducts(1);
  const product = products[0];
  return {
    product,
    category: catalog.categories.find((c) => String(c.id) === String(product.categoryId))?.name,
  };
}

if (process.argv[1]?.endsWith('pick-next.mjs')) {
  const countArg = process.argv.indexOf('--count');
  const count = countArg >= 0 ? parseInt(process.argv[countArg + 1], 10) : 3;
  const { products } = pickNextProducts(count);
  console.log(JSON.stringify(products, null, 2));
}
