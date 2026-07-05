/** Simple, clean copy — a kid can follow it. Calls out business owners. Exact buy steps. */

export function cleanProductName(name) {
  let n = name
    .replace(/\(QTY[^)]*\)/gi, '')
    .replace(/QTY\/CTN[^)]*\)?/gi, '')
    .replace(/\s+/g, ' ')
    .trim();

  n = n.toLowerCase().replace(/\b\w/g, (c) => c.toUpperCase());
  n = n.replace(/\bLb\b/g, 'LB').replace(/\bKg\b/g, 'KG').replace(/\bOz\b/g, 'OZ');
  if (n.length > 48) n = n.slice(0, 45) + '…';
  return n;
}

export function extractPackInfo(name) {
  const lb = name.match(/(\d+)\s*LB\b/i);
  if (lb) return `${lb[1]} lb case`;
  const kg = name.match(/(\d+)\s*KG\b/i);
  if (kg) return `${kg[1]} kg case`;
  const ct = name.match(/(\d+)\s*CT\b/i);
  if (ct) return `${ct[1]}-count case`;
  const qty = name.match(/QTY\.?\/CTN\.?\s*:?\s*(\d+)/i);
  if (qty) return `${qty[1]} per case`;
  const pcs = name.match(/(\d+)\s*PCS/i);
  if (pcs) return `${pcs[1]} pcs per case`;
  return 'Sold by the case';
}

export function buildReelCopy(product, category, brand) {
  const productName = cleanProductName(product.name);
  const packLine = extractPackInfo(product.name);
  const site = brand.siteDisplay || brand.website.replace(/^https?:\/\//, '');
  const registerDisplay = brand.registerUrl.replace(/^https?:\/\//, '');

  return {
    eyebrow: 'WHOLESALE ONLY',
    productName,
    packLine,
    callout: 'Own a store, restaurant, or shop?',
    calloutSub: 'This post is for you — not regular shoppers.',
    benefit: 'A top seller your customers already ask for.',
    steps: [
      { num: '1', text: `Go to ${site}` },
      { num: '2', text: 'Tap Register (free account)' },
      { num: '3', text: 'Log in → see prices → order' },
    ],
    ctaButton: `Start here → ${registerDisplay}`,

    caption: [
      `📦 ${productName}`,
      `${packLine} · Wholesale`,
      '',
      '🏪 Own a grocery, restaurant, or retail shop?',
      'Home Value sells to businesses only — not the public.',
      '',
      'How to order:',
      `① Go to ${site}`,
      '② Register your business (free)',
      '③ Log in, browse 2,500+ products, see your prices, and order',
      '',
      `#wholesale #homevalue #hvhomevalue #${category.replace(/\s+/g, '').slice(0, 20)}`,
    ].join('\n'),
  };
}
