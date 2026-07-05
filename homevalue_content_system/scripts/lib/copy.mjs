/** Copy for Reels featuring multiple products at once. */

import { cleanProductName, extractPackInfo } from './copy-utils.mjs';

export { cleanProductName, extractPackInfo };

export function buildReelCopy(products, catMap, brand) {
  const site = brand.siteDisplay || brand.website.replace(/^https?:\/\//, '');
  const siteUrl = brand.website;

  const items = products.map((p) => ({
    productId: p.productId,
    name: cleanProductName(p.name),
    pack: extractPackInfo(p.name),
    category: catMap[p.categoryId] || 'Wholesale',
    image: p.image,
  }));

  const namesList = items.map((i) => i.name).join(' · ');
  const primaryCategory = items[0]?.category || 'Wholesale';

  return {
    hookLine1: 'Run a store or restaurant?',
    hookLine2: 'We sell wholesale.',
    hookLine3: 'Not for regular shoppers.',

    products: items,
    headline: `${items.length} products · Wholesale`,
    namesList,

    callout: 'This is for business owners.',
    calloutSub: 'Grocery · Restaurant · Retail · Food service',

    steps: [
      { num: '1', text: `Go to ${site}` },
      { num: '2', text: 'Tap Register — free' },
      { num: '3', text: 'Log in & order' },
    ],
    ctaButton: 'Tap link in comments 👇',

    linkComment: `Order wholesale here 👇\n${siteUrl}\n\nFree business account — register to see prices & place orders.`,

    caption: [
      '🏪 Run a store or restaurant?',
      '',
      `📦 ${items.length} wholesale picks:`,
      ...items.map((i) => `• ${i.name} (${i.pack})`),
      '',
      'Home Value — 2,500+ products for grocery, restaurants & shops.',
      '',
      '👇 Link in comments to order',
      '',
      '#wholesale #homevalue #hvhomevalue',
    ].join('\n'),
  };
}
