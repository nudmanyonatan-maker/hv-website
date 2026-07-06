#!/usr/bin/env node
/**
 * Pick N products from ONE category per post (rotate categories across posts).
 * When daily food quota is unmet, picks from food categories only.
 * Skips products with low-quality images (tiny, blank, bad aspect ratio).
 */

import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { primaryCategoryId, categoryName } from './lib/copy-utils.mjs';
import {
  shouldPickFoodNow,
  foodCategoryKeyForProduct,
  isFoodCategory,
  foodQuotaRemaining,
} from './lib/food-schedule.mjs';
import { filterProductsByImageQuality, closeQualityBrowser } from './lib/image-quality.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');
const STATE_PATH = join(ROOT, 'content/state/rotation.json');
const MAX_IMAGE_CHECKS = 48;

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

function poolKeyForProduct(product, foodOnly) {
  if (foodOnly) {
    return foodCategoryKeyForProduct(product);
  }
  return primaryCategoryId(product.categoryId);
}

function buildCategoryPools(catalog, excludeIds, { foodOnly = false } = {}) {
  const pools = new Map();
  for (const p of catalog.products) {
    if (!p.inStock || !p.hasImage || !p.image) continue;
    if (excludeIds.includes(String(p.productId))) continue;

    const cid = poolKeyForProduct(p, foodOnly);
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

function candidateProducts(pool, count, maxChecks = MAX_IMAGE_CHECKS) {
  const seen = new Set();
  const candidates = [];
  const top = pool.slice(0, Math.max(count * 4, 24));
  const rest = shuffle(pool.slice(top.length));

  for (const p of [...shuffle(top), ...rest]) {
    const id = String(p.productId);
    if (seen.has(id)) continue;
    seen.add(id);
    candidates.push(p);
    if (candidates.length >= maxChecks) break;
  }
  return candidates;
}

async function pickProductsFromPool(pool, count, { log = true } = {}) {
  const candidates = candidateProducts(pool, count);
  const { accepted, rejected } = await filterProductsByImageQuality(candidates, count, { log });
  return { products: accepted, rejected, imageChecks: candidates.length };
}

async function pickFromPools(pools, lastCategories, count, { log = true } = {}) {
  const tried = new Set();
  let categoryId = pickCategoryId(pools, lastCategories, count);

  while (categoryId && !tried.has(categoryId)) {
    tried.add(categoryId);
    const pool = pools.get(categoryId) || [];
    const { products, rejected, imageChecks } = await pickProductsFromPool(pool, count, { log });

    if (products.length >= count) {
      return { products, categoryId, rejected, imageChecks };
    }

    if (log) {
      console.log(
        `  ⚠ Only ${products.length}/${count} good images in category ${categoryId} — trying another category`
      );
    }

    const nextPools = new Map([...pools.entries()].filter(([cid]) => !tried.has(cid)));
    categoryId = pickCategoryId(nextPools, lastCategories, count);
    pools = nextPools;
  }

  return { products: [], categoryId: null, rejected: [], imageChecks: 0 };
}

export async function pickNextProducts(count = 6, { preferFood = null, log = true } = {}) {
  const foodMode = preferFood ?? shouldPickFoodNow(false);
  const catalog = loadCatalog();
  const state = loadState();
  let excludeIds = [...state.postedIds];
  let pools = buildCategoryPools(catalog, excludeIds, { foodOnly: foodMode });

  if (foodMode && pools.size === 0) {
    if (log) console.log('  ⚠ No food pools left — resetting product rotation for food');
    state.postedIds = [];
    state.cycle += 1;
    excludeIds = [];
    pools = buildCategoryPools(catalog, [], { foodOnly: true });
  }

  if (!foodMode && pools.size === 0) {
    state.postedIds = [];
    state.cycle += 1;
    excludeIds = [];
    pools = buildCategoryPools(catalog, [], { foodOnly: false });
  }

  if (foodMode && log) {
    console.log(`  🍽 Food quota: ${foodQuotaRemaining()} remaining today — picking food category`);
  }

  let { products, categoryId, rejected } = await pickFromPools(
    pools,
    state.lastCategories || [],
    count,
    { log }
  );

  if (products.length < count && !foodMode) {
    const largest = [...pools.entries()].sort((a, b) => b[1].length - a[1].length)[0];
    if (largest && largest[0] !== categoryId) {
      const fallback = await pickProductsFromPool(largest[1], count, { log });
      if (fallback.products.length > products.length) {
        products = fallback.products;
        categoryId = largest[0];
        rejected = fallback.rejected;
      }
    }
  }

  if (products.length < count) {
    throw new Error(
      `Could not find ${count} products with good images${foodMode ? ' in food categories' : ''}. ` +
        `Got ${products.length}. Rejected ${rejected.length} bad images.`
    );
  }

  return {
    products,
    categoryId,
    state,
    catalog,
    isFood: isFoodCategory(categoryId),
    foodMode,
    imageRejected: rejected,
  };
}

export function markPosted(productIds, categoryId) {
  const state = loadState();
  for (const id of productIds) {
    const s = String(id);
    if (!state.postedIds.includes(s)) state.postedIds.push(s);
  }
  if (categoryId) {
    state.lastCategories = [String(primaryCategoryId(categoryId) || categoryId), ...(state.lastCategories || [])].slice(0, 5);
  }
  state.lastPostedAt = new Date().toISOString();
  saveState(state);
}

if (process.argv[1]?.endsWith('pick-next.mjs')) {
  const countArg = process.argv.indexOf('--count');
  const count = countArg >= 0 ? parseInt(process.argv[countArg + 1], 10) : 6;
  const forceFood = process.argv.includes('--food');

  try {
    const { products, categoryId, catalog, isFood, imageRejected } = await pickNextProducts(count, {
      preferFood: forceFood ? true : null,
    });
    const catMap = Object.fromEntries(catalog.categories.map((c) => [String(c.id), c.name]));
    const cat = categoryName(catMap, categoryId);
    console.log(`Category: ${cat}${isFood ? ' (food)' : ''}`);
    products.forEach((p) => console.log(`• ${p.name}`));
    if (imageRejected.length) {
      console.log(`Skipped ${imageRejected.length} low-quality images`);
    }
  } finally {
    await closeQualityBrowser();
  }
}
