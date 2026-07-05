#!/usr/bin/env node
/**
 * Pick products from ALL categories — one per category for variety.
 * Uses full catalog (2,400+ SKUs), not just food or top movers.
 */

import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { primaryCategoryId, categoryName } from './lib/copy-utils.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');
const STATE_PATH = join(ROOT, 'content/state/rotation.json');

function loadCatalog() {
  return JSON.parse(readFileSync(join(ROOT, 'catalog/products.json'), 'utf8'));
}

function loadState() {
  if (!existsSync(STATE_PATH)) {
    return { postedIds: [], lastPostedAt: null, cycle: 0, lastCategories: [] };
  }
  return JSON.parse(readFileSync(STATE_PATH, 'utf8'));
}

function saveState(state) {
  mkdirSync(dirname(STATE_PATH), { recursive: true });
  writeFileSync(STATE_PATH, JSON.stringify(state, null, 2));
}

function shuffle(arr) {
  const a = [...arr];
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}

/** Best in-stock product per category (by order volume). */
function buildCategoryPools(catalog, excludeIds) {
  const pools = new Map();
  for (const p of catalog.products) {
    if (!p.inStock || !p.hasImage) continue;
    if (excludeIds.includes(String(p.productId))) continue;
    const cid = primaryCategoryId(p.categoryId);
    if (!cid) continue;
    if (!pools.has(cid)) pools.set(cid, []);
    pools.get(cid).push(p);
  }
  for (const [cid, list] of pools) {
    list.sort((a, b) => (b.totalOrders || 0) - (a.totalOrders || 0));
    pools.set(cid, list);
  }
  return pools;
}

export function pickNextProducts(count = 3) {
  const catalog = loadCatalog();
  const state = loadState();
  const excludeIds = [...state.postedIds];
  let pools = buildCategoryPools(catalog, excludeIds);

  if (pools.size < count) {
    state.postedIds = [];
    state.cycle += 1;
    pools = buildCategoryPools(catalog, []);
  }

  const categoryIds = shuffle([...pools.keys()]);
  const products = [];

  for (const cid of categoryIds) {
    if (products.length >= count) break;
    const pool = pools.get(cid);
    const pick = pool[0];
    if (pick) products.push({ ...pick });
  }

  // Fill if fewer categories than count
  if (products.length < count) {
    const used = new Set(products.map((p) => String(p.productId)));
    const rest = catalog.products
      .filter((p) => p.inStock && p.hasImage && !used.has(String(p.productId)) && !excludeIds.includes(String(p.productId)))
      .sort((a, b) => (b.totalOrders || 0) - (a.totalOrders || 0));
    for (const p of rest) {
      if (products.length >= count) break;
      products.push({ ...p });
    }
  }

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

if (process.argv[1]?.endsWith('pick-next.mjs')) {
  const countArg = process.argv.indexOf('--count');
  const count = countArg >= 0 ? parseInt(process.argv[countArg + 1], 10) : 3;
  const { products, catalog } = pickNextProducts(count);
  const catMap = Object.fromEntries(catalog.categories.map((c) => [String(c.id), c.name]));
  products.forEach((p) => console.log(`• [${categoryName(catMap, p.categoryId)}] ${p.name}`));
}
