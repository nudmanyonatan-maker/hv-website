/** Shared product name / pack helpers. */

export function cleanProductName(name) {
  let n = name
    .replace(/\(QTY[^)]*\)/gi, '')
    .replace(/QTY\/CTN[^)]*\)?/gi, '')
    .replace(/\s+/g, ' ')
    .trim();

  n = n.toLowerCase().replace(/\b\w/g, (c) => c.toUpperCase());
  n = n.replace(/\bLb\b/g, 'LB').replace(/\bKg\b/g, 'KG').replace(/\bOz\b/g, 'OZ');
  if (n.length > 36) n = n.slice(0, 33) + '…';
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
  if (pcs) return `${pcs[1]} pcs/case`;
  return 'By the case';
}
