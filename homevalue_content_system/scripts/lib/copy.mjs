/** Reel copy — one category, multiple products, carousel-style video. */

import { cleanProductName, extractPackInfo, categoryName } from './copy-utils.mjs';

export { cleanProductName, extractPackInfo };

export function buildReelCopy(products, catMap, brand) {
  const siteUrl = brand.website;
  const site = brand.siteDisplay || siteUrl.replace(/^https?:\/\//, '');
  const category = categoryName(catMap, products[0]?.categoryId);

  const items = products.map((p) => ({
    productId: p.productId,
    name: cleanProductName(p.name),
    pack: extractPackInfo(p.name),
    category,
    image: p.image,
  }));

  return {
    category,
    cover: {
      title: category,
      subtitle: 'Wholesale picks · Home Value',
      swipe: 'Watch →',
    },
    products: items,
    cta: {
      line: 'Want to see more?',
      action: 'Link in comments 👇',
      url: site,
    },
    linkComment: `Browse our full wholesale catalog 👇\n${siteUrl}\n\n2,500+ products — free business account to see prices & order.`,
    caption: [
      '🏪 Wholesale only — for business owners',
      '',
      `🏷️ ${category}`,
      '',
      ...items.map((i) => `📦 ${i.name}`),
      '',
      '👇 Link in comments for the full catalog',
      '',
      '#wholesale #homevalue #hvhomevalue',
    ].join('\n'),
  };
}
