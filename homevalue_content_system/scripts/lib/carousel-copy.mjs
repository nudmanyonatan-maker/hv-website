/** Carousel copy — cover + product slides + CTA caption. */

import { cleanProductName, extractPackInfo, categoryName } from './copy-utils.mjs';

export function buildCarouselCopy(products, catMap, brand) {
  const siteUrl = brand.website;
  const site = brand.siteDisplay || siteUrl.replace(/^https?:\/\//, '');

  const items = products.map((p) => ({
    productId: p.productId,
    name: cleanProductName(p.name),
    pack: extractPackInfo(p.name),
    category: categoryName(catMap, p.categoryId),
    image: p.image,
  }));

  const categories = [...new Set(items.map((i) => i.category))];

  return {
    cover: {
      title: 'Wholesale Picks',
      subtitle: 'For stores, restaurants & shops',
      swipe: 'Swipe →',
      categories: categories.slice(0, 4).join(' · '),
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
      ...items.map((i) => `📦 ${i.name} · ${i.category}`),
      '',
      '👇 Link in comments for the full catalog',
      '',
      '#wholesale #homevalue #hvhomevalue',
    ].join('\n'),
  };
}
