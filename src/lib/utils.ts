import type { Product, ProductImage, PublicProduct, SlimProduct, SlimPublicProduct } from '@/types';

/** Strip price fields from products for public (unauthenticated) visitors */
export function stripPrices(products: Product[]): PublicProduct[] {
  return products.map(({ sale_price, sale_price0, fob_price, purchase_price, ...rest }) => rest);
}

/** Strip heavy fields not needed for catalog display to reduce payload */
export function slimProducts(products: Product[]): SlimProduct[];
export function slimProducts(products: PublicProduct[]): SlimPublicProduct[];
export function slimProducts(products: (Product | PublicProduct)[]): (SlimProduct | SlimPublicProduct)[];
export function slimProducts(products: (Product | PublicProduct)[]): (SlimProduct | SlimPublicProduct)[] {
  return products.map((p) => {
    const { requested, barcode, stock, total_order, force_moq, catalog_id, lblstock, ...slim } = p as Product;
    // Keep only the best image for catalog cards (detail page fetches its own data)
    if (Array.isArray(slim.images) && slim.images.length > 1) {
      const best = slim.images.find((img: any) => img.img_default === 1) ||
        [...slim.images].sort((a: any, b: any) => (a.img_order ?? 999) - (b.img_order ?? 999))[0];
      slim.images = best ? [best] : [slim.images[0]];
    }
    return slim;
  });
}

/** Format price with Intl.NumberFormat */
export function formatPrice(price: number, currency = 'USD', locale: string = 'en'): string {
  return new Intl.NumberFormat(locale === 'es' ? 'es-US' : 'en-US', {
    style: 'currency',
    currency: currency === '$' ? 'USD' : currency,
  }).format(price);
}

/** Get the best product image URL, or a fallback */
export function getProductImage(product: { images?: ProductImage[] }): string {
  if (!product.images || product.images.length === 0) {
    return 'https://app.fullvendor.com/uploads/noimg.png';
  }
  // Prefer image marked as default
  const defaultImg = product.images.find((img) => img.img_default === 1);
  if (defaultImg?.pic) return defaultImg.pic;
  // Sort by img_order if available
  const sorted = [...product.images].sort((a, b) => (a.img_order ?? 999) - (b.img_order ?? 999));
  return sorted[0]?.pic || 'https://app.fullvendor.com/uploads/noimg.png';
}

/** Parse tags string into array */
export function parseTags(tags: string | null | undefined): string[] {
  if (!tags) return [];
  return tags.split(',').map((t) => t.trim()).filter(Boolean);
}

/** Language ID for FullVendor: en=1, es=2 */
export function langToId(lang: string): string {
  return lang === 'es' ? '2' : '1';
}

/** cn helper for conditional classNames */
export function cn(...classes: (string | false | null | undefined)[]): string {
  return classes.filter(Boolean).join(' ');
}
