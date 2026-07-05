/** B2B wholesale copy — valuable context, always ends with CTA. No public pricing. */

export function cleanProductName(name) {
  return name
    .replace(/\(QTY[^)]*\)/gi, '')
    .replace(/QTY\/CTN[^)]*\)?/gi, '')
    .replace(/\s+/g, ' ')
    .trim();
}

export function extractPackInfo(name) {
  const match = name.match(/(?:QTY\.?\/CTN\.?\s*:?\s*[^)]+\)|\d+\s*(?:LB|KG|G|ML|L|OZ|PCS|PC\(S\)|SET|CT|UNIT))/i);
  return match ? match[0].replace(/[()]/g, '').trim() : null;
}

export function buildReelCopy(product, category, brand) {
  const displayName = cleanProductName(product.name);
  const pack = extractPackInfo(product.name);
  const registerUrl = brand.registerUrl.replace(/^https?:\/\//, '');

  const hooks = {
    product_spotlight: 'Wholesale pick',
    did_you_know: 'Buyer insight',
    case_size_callout: 'Case pack detail',
    category_feature: category,
    buyer_tip: 'Stock smarter',
    stack_builder: 'Build the order',
    in_stock_now: 'Available now',
    wholesale_cta: 'Wholesale account',
  };

  const format = product._format || 'product_spotlight';
  const badge = hooks[format] || 'Wholesale pick';

  let context;
  if (pack) {
    context = `${displayName} ships ${pack.toLowerCase()}. A proven mover in ${category.toLowerCase()} — ideal for stores and food-service buyers restocking high-turn categories.`;
  } else {
    context = `${displayName} — a top wholesale SKU in ${category.toLowerCase()}. Built for case orders, not retail singles.`;
  }

  const value =
    'Home Value supplies 2,500+ SKUs to grocery, restaurant, and supermarket accounts across the US.';

  const cta = `Create your free wholesale account for pricing & ordering → ${registerUrl}`;

  const caption = [
    `${badge} · ${displayName}`,
    '',
    context,
    '',
    value,
    '',
    cta,
    '',
    '#wholesale #homevalue #B2B #restaurantsupply #grocerywholesale #hvhomevalue',
  ].join('\n');

  return {
    badge,
    headline: displayName,
    subline: pack ? `Case pack: ${pack}` : category,
    context,
    value,
    cta: `Wholesale account → ${registerUrl}`,
    ctaShort: 'Register for wholesale pricing',
    caption,
  };
}
