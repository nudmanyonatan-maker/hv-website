#!/usr/bin/env node
/**
 * Sync product catalog from FullVendor API into catalog/products.json
 * Usage: FULLVENDOR_TOKEN=xxx node scripts/sync-catalog.mjs
 */

import { writeFileSync, readFileSync, mkdirSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const config = JSON.parse(readFileSync(join(ROOT, 'config/fullvendor.json'), 'utf8'));
const TOKEN = process.env.FULLVENDOR_TOKEN;
if (!TOKEN) {
  console.error('Missing FULLVENDOR_TOKEN env var. Copy .env.example → .env and fill in.');
  process.exit(1);
}

const BASE = process.env.FULLVENDOR_BASE_URL || config.baseUrl;

async function post(endpoint, data) {
  const res = await fetch(`${BASE}${endpoint}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-API-KEY': TOKEN,
    },
    body: JSON.stringify({ company_id: config.companyId, ...data }),
  });
  if (!res.ok) throw new Error(`HTTP ${res.status} on ${endpoint}`);
  return res.json();
}

function normalizeProduct(p) {
  const image = p.images?.[0]?.pic ?? null;
  return {
    productId: p.product_id,
    name: p.name,
    sku: p.sku,
    categoryId: p.category_id,
    unitType: p.unit_type,
    stock: parseFloat(p.stock) || 0,
    totalOrders: parseFloat(p.total_order) || 0,
    image,
    inStock: parseFloat(p.stock) > 0,
    hasImage: Boolean(image),
  };
}

console.log('Fetching categories...');
const catRes = await post('categoryList', { language_id: config.languageId });
const categories = (catRes.list || []).map((c) => ({
  id: String(c.category_id),
  name: c.category_name,
}));

console.log('Fetching products (this may take ~45s)...');
const prodRes = await post('productList', {
  language_id: config.languageId,
  customer_id: config.defaultCustomerId,
});

const all = (prodRes.list || []).map(normalizeProduct);
const eligible = all
  .filter((p) => p.inStock && p.hasImage)
  .sort((a, b) => b.totalOrders - a.totalOrders);

const catalog = {
  syncedAt: new Date().toISOString(),
  companyId: config.companyId,
  totalProducts: all.length,
  eligibleForContent: eligible.length,
  categories,
  products: all,
  priorityProducts: eligible.slice(0, 100),
};

mkdirSync(join(ROOT, 'catalog'), { recursive: true });
writeFileSync(join(ROOT, 'catalog/products.json'), JSON.stringify(catalog, null, 2));

console.log(`✓ Synced ${all.length} products (${eligible.length} in-stock with images)`);
console.log(`✓ Top priority SKU: ${eligible[0]?.name ?? 'none'}`);
console.log(`✓ Saved to catalog/products.json`);
