/**
 * In-memory cache for the product catalog.
 *
 * The FullVendor productList response is ~2.3MB which exceeds
 * the Next.js data cache limit (2MB). This means `next: { revalidate }`
 * silently fails and every request hits the slow API.
 *
 * This module caches the PROCESSED (filtered, deduped, slimmed) catalog
 * in the serverless function's memory. It persists across requests to the
 * same instance and re-fetches when the TTL expires.
 */

import type { Product, Category } from '@/types';
import { getProducts, getCategories } from './fullvendor';

const CACHE_TTL = 5 * 60 * 1000; // 5 minutes

interface CacheEntry<T> {
  data: T;
  ts: number;
}

const productCache = new Map<string, CacheEntry<Product[]>>();
const categoryCache = new Map<string, CacheEntry<Category[]>>();

function isExpired<T>(entry: CacheEntry<T> | undefined): entry is undefined {
  if (!entry) return true;
  return Date.now() - entry.ts > CACHE_TTL;
}

/** Deduplicate products by SKU, keep first occurrence */
function dedup(products: Product[]): Product[] {
  const seen = new Set<string>();
  return products.filter((p) => {
    const sku = p.sku?.trim().toLowerCase();
    if (sku && seen.has(sku)) return false;
    if (sku) seen.add(sku);
    return true;
  });
}

/** Filter to active products with at least one image */
function filterActive(products: Product[]): Product[] {
  return products.filter((p) => {
    const isActive = String(p.status) === '1';
    const hasImage = p.images && p.images.length > 0 && p.images[0].pic;
    return isActive && hasImage;
  });
}

/**
 * Get the processed product catalog. Cached in memory for 5 minutes.
 * Does NOT include customer-specific pricing — this is the public catalog.
 */
export async function getCachedProducts(languageId: string): Promise<Product[]> {
  const key = `products-${languageId}`;
  const cached = productCache.get(key);

  if (!isExpired(cached)) {
    return cached.data;
  }

  try {
    // Fetch without customer_id (public catalog, same for everyone)
    const res = await getProducts({ languageId });
    const processed = dedup(filterActive(res.list ?? []));
    processed.sort((a, b) => (a.catalog_order ?? 999) - (b.catalog_order ?? 999));

    productCache.set(key, { data: processed, ts: Date.now() });
    return processed;
  } catch (error) {
    console.error(`[catalog-cache] Failed to refresh products (lang=${languageId}):`, error);
    const stale = productCache.get(key);
    if (stale) {
      console.warn(`[catalog-cache] Serving stale data (age: ${Math.round((Date.now() - stale.ts) / 1000)}s)`);
      return stale.data;
    }
    throw error;
  }
}

/**
 * Get categories. Cached in memory for 5 minutes.
 */
export async function getCachedCategories(languageId: string): Promise<Category[]> {
  const key = `categories-${languageId}`;
  const cached = categoryCache.get(key);

  if (!isExpired(cached)) {
    return cached.data;
  }

  try {
    const res = await getCategories(languageId);
    const categories = res.list ?? [];
    categories.sort((a, b) => a.category_name.localeCompare(b.category_name));

    categoryCache.set(key, { data: categories, ts: Date.now() });
    return categories;
  } catch (error) {
    console.error(`[catalog-cache] Failed to refresh categories (lang=${languageId}):`, error);
    const stale = categoryCache.get(key);
    if (stale) {
      console.warn(`[catalog-cache] Serving stale data (age: ${Math.round((Date.now() - stale.ts) / 1000)}s)`);
      return stale.data;
    }
    throw error;
  }
}
