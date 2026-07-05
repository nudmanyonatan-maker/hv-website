#!/usr/bin/env node
/**
 * Pick N products from ONE category per post (rotate categories across posts).
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

function pickCategoryId(pools, lastCategories, count) {
  const eligible = [...pools.entries()]
    .filter(([, list]) => list.length >= count)
    .map(([cid]) => cid);

  if (eligible.length === 0) return null;

  const fresh = eligible.filter((cid) => !lastCategories.includes(cid));
  const pool = fresh.length > 0 ? fresh : eligible;
  return shuffle(pool)[0];
}

export function pickNextProducts(count = 6) {
  const catalog = loadCatalog();
  const state = loadState();
  let excludeIds = [...state.postedIds];
  let pools = buildCategoryPools(catalog, excludeIds);

  let categoryId = pickCategoryId(pools, state.lastCategories || [], count);

  if (!categoryId) {
    state.postedIds = [];
    state.cycle += 1;
    excludeIds = [];
    pools = buildCategoryPools(catalog, []);
    categoryId = pickCategoryId(pools, state.lastCategories || [], count);
  }

  if (!categoryId) {
    const largest = [...pools.entries()].sort((a, b) => b[1].length - a[1].length)[0];
    categoryId = largest?.[0] ?? null;
  }

  const pool = categoryId ? pools.get(categoryId) : [];
  const seen = new Set();
  const products = [];
  for (const p of pool || []) {
    const id = String(p.productId);
    if (seen.has(id)) continue;
    seen.add(id);
    products.push({ ...p });
    if (products.length >= count) break;
  }

  return { products, categoryId, state, catalog };
}

export function markPosted(productIds, categoryId) {
  const state = loadState();
  for (const id of productIds) {
    const s = String(id);
    if (!state.postedIds.includes(s)) state.postedIds.push(s);
  }
  if (categoryId) {
    state.lastCategories = [String(categoryId), ...(state.lastCategories || [])].slice(0, 5);
  }
  state.lastPostedAt = new Date().toISOString();
  saveState(state);
}

if (process.argv[1]?.endsWith('pick-next.mjs')) {
  const countArg = process.argv.indexOf('--count');
  const count = countArg >= 0 ? parseInt(process.argv[countArg + 1], 10) : 6;
  const { products, categoryId, catalog } = pickNextProducts(count);
  const catMap = Object.fromEntries(catalog.categories.map((c) => [String(c.id), c.name]));
  const cat = categoryName(catMap, categoryId);
  console.log(`Category: ${cat}`);
  products.forEach((p) => console.log(`• ${p.name}`));
}
