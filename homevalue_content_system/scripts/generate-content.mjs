#!/usr/bin/env node
/**
 * Generate Instagram post scripts from synced catalog.
 * Usage: node scripts/generate-content.mjs [--limit 50]
 */

import { writeFileSync, readFileSync, mkdirSync, existsSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const catalogPath = join(ROOT, 'catalog/products.json');
if (!existsSync(catalogPath)) {
  console.error('Run sync-catalog.mjs first.');
  process.exit(1);
}

const brand = JSON.parse(readFileSync(join(ROOT, 'config/brand.json'), 'utf8'));
const compliance = JSON.parse(readFileSync(join(ROOT, 'config/compliance.json'), 'utf8'));
const formats = JSON.parse(readFileSync(join(ROOT, 'formats/formats.json'), 'utf8'));
const catalog = JSON.parse(readFileSync(catalogPath, 'utf8'));

const limitArg = process.argv.indexOf('--limit');
const limit = limitArg >= 0 ? parseInt(process.argv[limitArg + 1], 10) : 50;
const products = catalog.priorityProducts.slice(0, limit);

const catMap = Object.fromEntries(catalog.categories.map((c) => [c.id, c.name]));

function extractCaseSize(name) {
  const match = name.match(/QTY\/CTN[^)]*\)|\d+\s*(LB|KG|G|ML|L|OZ|PCS|PC\(S\)|SET|CT)/i);
  return match ? match[0].replace(/[()]/g, '').trim() : null;
}

function shortName(name) {
  return name.length > 60 ? name.slice(0, 57) + '...' : name;
}

function generateScript(product, format) {
  const caseSize = extractCaseSize(product.name);
  const category = catMap[product.categoryId] || 'Wholesale';
  const cta = 'Register for wholesale pricing → hv-website-phi.vercel.app/en/register';

  switch (format.id) {
    case 'product_spotlight':
      return {
        hook: shortName(product.name),
        body: caseSize
          ? `${caseSize}. ${category} — stocked and ready for your next order. ${compliance.disclaimer}`
          : `${category} SKU ready to ship. ${compliance.disclaimer}`,
        cta,
      };
    case 'did_you_know':
      return {
        hook: 'Did you know?',
        body: `Buyers who stock ${category.toLowerCase()} see stronger repeat foot traffic. ${shortName(product.name)} is one of our top movers — login to see case pricing.`,
        cta,
      };
    case 'case_size_callout':
      return {
        hook: caseSize ? `Case pack: ${caseSize}` : 'Sold by the case',
        body: `${shortName(product.name)} — built for wholesale orders, not shelf singles. ${compliance.disclaimer}`,
        cta,
      };
    case 'category_feature':
      return {
        hook: category,
        body: `Featured: ${shortName(product.name)}. Full ${category.toLowerCase()} catalog available to registered wholesale accounts.`,
        cta,
      };
    case 'buyer_tip':
      return {
        hook: 'Buyer tip',
        body: `Keep ${category.toLowerCase()} facings full — top SKUs like this one drive basket size. Order through your Home Value account.`,
        cta,
      };
    case 'stack_builder':
      return {
        hook: 'Build a fuller order',
        body: `Add ${shortName(product.name)} to your next ${category.toLowerCase()} restock. Pair with complementary items from the same category.`,
        cta,
      };
    case 'in_stock_now':
      return {
        hook: 'In stock now',
        body: `${shortName(product.name)} — available for immediate wholesale order. ${compliance.disclaimer}`,
        cta,
      };
    case 'wholesale_cta':
      return {
        hook: brand.tagline,
        body: `2,500+ SKUs. One wholesale account. Browse ${category.toLowerCase()} and more — prices visible after registration.`,
        cta,
      };
    default:
      throw new Error(`Unknown format: ${format.id}`);
  }
}

const posts = [];
const formatIds = formats.map((f) => f.id);

for (let i = 0; i < products.length; i++) {
  const product = products[i];
  const format = formats[i % formats.length];
  const script = generateScript(product, format);

  posts.push({
    id: `hv-${product.productId}-${format.id}`,
    productId: product.productId,
    productName: product.name,
    sku: product.sku,
    category: catMap[product.categoryId] || 'Unknown',
    format: format.id,
    formatName: format.name,
    image: product.image,
    durationSec: format.durationSec,
    script,
    caption: `${script.hook}\n\n${script.body}\n\n${script.cta}\n\n#wholesale #${brand.name.replace(/\s/g, '')} #B2B`,
    status: 'draft',
    createdAt: new Date().toISOString(),
  });
}

const outDir = join(ROOT, 'content/posts');
mkdirSync(outDir, { recursive: true });
writeFileSync(join(outDir, 'queue.json'), JSON.stringify({ generatedAt: new Date().toISOString(), count: posts.length, posts }, null, 2));

// Also write first 5 as readable previews
const preview = posts.slice(0, 5).map((p) => ({
  format: p.formatName,
  product: p.productName,
  caption: p.caption,
}));
writeFileSync(join(outDir, 'preview.json'), JSON.stringify(preview, null, 2));

console.log(`✓ Generated ${posts.length} post scripts → content/posts/queue.json`);
console.log(`✓ Preview (first 5) → content/posts/preview.json`);
