/** Simple copy with an instant hook. Calls out business owners. Exact buy steps. */

export function cleanProductName(name) {
  let n = name
    .replace(/\(QTY[^)]*\)/gi, '')
    .replace(/QTY\/CTN[^)]*\)?/gi, '')
    .replace(/\s+/g, ' ')
    .trim();

  n = n.toLowerCase().replace(/\b\w/g, (c) => c.toUpperCase());
  n = n.replace(/\bLb\b/g, 'LB').replace(/\bKg\b/g, 'KG').replace(/\bOz\b/g, 'OZ');
  if (n.length > 42) n = n.slice(0, 39) + '…';
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
  const siteUrl = brand.website;
  const registerUrl = brand.registerUrl;

  return {
    // Scene 1 — instant hook (biggest text on screen)
    hookLine1: 'Run a store or restaurant?',
    hookLine2: 'We sell wholesale.',
    hookLine3: 'Not for regular shoppers.',

    productName,
    packLine,
    categoryLine: `${category} · Wholesale`,

    callout: 'This is for business owners.',
    calloutSub: 'Grocery · Restaurant · Retail · Food service',

    steps: [
      { num: '1', text: `Go to ${site}` },
      { num: '2', text: 'Tap Register — free' },
      { num: '3', text: 'Log in & order' },
    ],
    ctaButton: `Tap link in comments 👇`,

    // First comment (1 URL max, <300 chars, not all caps)
    linkComment: `Order wholesale here 👇\n${siteUrl}\n\nFree business account — register to see prices & place orders.`,

    caption: [
      `🏪 Run a store or restaurant?`,
      '',
      `📦 ${productName}`,
      `${packLine} · Wholesale from Home Value`,
      '',
      'We supply grocery stores, restaurants & shops — 2,500+ products.',
      '',
      '👇 Link in comments to order',
      '',
      `#wholesale #homevalue #hvhomevalue`,
    ].join('\n'),
  };
}
