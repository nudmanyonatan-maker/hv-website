/** Simple copy — pictures first, one easy CTA. No numbered steps. */

import { cleanProductName, extractPackInfo, categoryName } from './copy-utils.mjs';

export { cleanProductName, extractPackInfo };

export function buildReelCopy(products, catMap, brand) {
  const site = brand.siteDisplay || brand.website.replace(/^https?:\/\//, '');
  const siteUrl = brand.website;

  const items = products.map((p) => ({
    productId: p.productId,
    name: cleanProductName(p.name),
    pack: extractPackInfo(p.name),
    category: categoryName(catMap, p.categoryId),
    image: p.image,
  }));

  const categories = [...new Set(items.map((i) => i.category))];

  return {
    hook: 'Wholesale only',
    hookSub: 'Run a store, restaurant, or shop?',

    products: items,
    categoryTags: categories.slice(0, 3).join(' · '),

    ctaLine: 'Want to see more?',
    ctaAction: 'Link in comments 👇',
    ctaUrl: site,

    linkComment: `Browse our full wholesale catalog 👇\n${siteUrl}\n\n2,500+ products — free business account to see prices & order.`,

    caption: [
      '🏪 Wholesale only — for business owners',
      '',
      ...items.map((i) => `📦 ${i.name}`),
      '',
      `Categories: ${categories.join(', ')}`,
      '',
      '👇 Link in comments for the full catalog',
      '',
      '#wholesale #homevalue #hvhomevalue',
    ].join('\n'),
  };
}
