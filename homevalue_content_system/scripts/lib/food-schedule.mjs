/** Track daily food Reel quota (2 per day guaranteed). */

import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { primaryCategoryId } from './copy-utils.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '../..');
const STATE_PATH = join(ROOT, 'content/state/daily-food.json');
const FOOD_CFG = JSON.parse(readFileSync(join(ROOT, 'config/food-categories.json'), 'utf8'));

function todayKey() {
  return new Date().toISOString().slice(0, 10);
}

function loadState() {
  if (!existsSync(STATE_PATH)) {
    return { date: todayKey(), foodPosts: 0 };
  }
  const state = JSON.parse(readFileSync(STATE_PATH, 'utf8'));
  if (state.date !== todayKey()) {
    return { date: todayKey(), foodPosts: 0 };
  }
  return state;
}

function saveState(state) {
  mkdirSync(dirname(STATE_PATH), { recursive: true });
  writeFileSync(STATE_PATH, JSON.stringify(state, null, 2));
}

export function foodRequiredPerDay() {
  return FOOD_CFG.requiredPerDay || 2;
}

export function foodCategoryIds() {
  return FOOD_CFG.categoryIds.map(String);
}

export function categoryIdsForProduct(product) {
  return String(product?.categoryId || '')
    .split(',')
    .map((s) => s.trim())
    .filter(Boolean);
}

/** Food category only when it is the product's primary category (avoids cookware tagged "Mexican"). */
export function foodCategoryKeyForProduct(product) {
  const primary = primaryCategoryId(product.categoryId);
  return primary && foodCategoryIds().includes(primary) ? primary : null;
}

export function isFoodCategory(categoryId) {
  const primary = primaryCategoryId(categoryId);
  return Boolean(primary && foodCategoryIds().includes(primary));
}

export function isFoodProduct(product, catMap) {
  if (foodCategoryKeyForProduct(product)) return true;
  const name = catMap?.[primaryCategoryId(product.categoryId)] || '';
  return FOOD_CFG.names.some((n) => name.includes(n.split(' ')[0]));
}

export function foodPostsToday() {
  return loadState().foodPosts;
}

export function foodQuotaRemaining() {
  return Math.max(0, foodRequiredPerDay() - foodPostsToday());
}

/** True when the next Reel should be food to hit daily quota. */
export function shouldPickFoodNow(forceFood = false) {
  if (forceFood) return true;
  return foodQuotaRemaining() > 0;
}

export function markFoodPosted() {
  const state = loadState();
  state.foodPosts += 1;
  saveState(state);
  return state.foodPosts;
}
