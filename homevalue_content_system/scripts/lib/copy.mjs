/** Simple copy — one product, picture-first, easy CTA. */

import { cleanProductName, extractPackInfo, categoryName } from './copy-utils.mjs';

export { cleanProductName, extractPackInfo };

export function buildReelCopy(products, catMap, brand) {
  const siteUrl = brand.website;
  const p = products[0];

  const item = {
    productId: p.productId,
    name: cleanProductName(p.name),
    pack: extractPackInfo(p.name),
    category: categoryName(catMap, p.categoryId),
    image: p.image,
  };

  return {
    hook: 'Wholesale only',
    hookSub: 'Run a store, restaurant, or shop?',

    products: [item],

    ctaLine: 'Want to see more?',
    ctaAction: 'Link in comments 👇',

    linkComment: `Browse our full wholesale catalog 👇\n${siteUrl}\n\n2,500+ products — free business account to see prices & order.`,

    caption: [
      '🏪 Wholesale only — for business owners',
      '',
      `📦 ${item.name}`,
      `🏷️ ${item.category}`,
      '',
      '👇 Link in comments for the full catalog',
      '',
      '#wholesale #homevalue #hvhomevalue',
    ].join('\n'),
  };
}
