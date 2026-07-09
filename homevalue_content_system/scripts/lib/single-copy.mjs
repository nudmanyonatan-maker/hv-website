/** Single-image post copy — one hero product. */

import { cleanProductName, extractPackInfo, categoryName } from './copy-utils.mjs';

export function buildSinglePostCopy(product, catMap, brand) {
  const siteUrl = brand.website;
  const site = brand.siteDisplay || siteUrl.replace(/^https?:\/\//, '');
  const category = categoryName(catMap, product.categoryId);
  const name = cleanProductName(product.name);
  const pack = extractPackInfo(product.name);

  return {
    category,
    categoryId: product.categoryId,
    product: {
      productId: product.productId,
      name,
      pack,
      category,
      image: product.image,
    },
    cta: {
      line: 'Wholesale catalog',
      action: 'Link in comments 👇',
      url: site,
    },
    linkComment: `Browse our full wholesale catalog 👇\n${siteUrl}\n\n2,500+ products — free business account to see prices & order.`,
    caption: [
      '🏪 Wholesale only — for business owners',
      '',
      `📦 ${name}`,
      `🏷️ ${category}`,
      pack ? `📐 ${pack}` : null,
      '',
      '👇 Link in comments for the full catalog',
      '',
      '#wholesale #homevalue #hvhomevalue',
    ].filter(Boolean).join('\n'),
  };
}
