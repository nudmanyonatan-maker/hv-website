import type { MetadataRoute } from 'next';
import { getCachedProducts } from '@/lib/catalog-cache';

const SITE_URL = 'https://hv-website-phi.vercel.app';
const locales = ['en', 'es'] as const;

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const staticPages = ['', '/contact', '/login', '/register'];

  // Static pages for both locales
  const staticEntries: MetadataRoute.Sitemap = locales.flatMap((locale) =>
    staticPages.map((page) => ({
      url: `${SITE_URL}/${locale}${page}`,
      lastModified: new Date(),
      changeFrequency: page === '' ? 'daily' as const : 'monthly' as const,
      priority: page === '' ? 1.0 : 0.7,
    })),
  );

  // Dynamic product pages
  let productEntries: MetadataRoute.Sitemap = [];
  try {
    const products = await getCachedProducts('1');

    productEntries = locales.flatMap((locale) =>
      products.map((product) => ({
        url: `${SITE_URL}/${locale}/products/${product.product_id}`,
        lastModified: new Date(),
        changeFrequency: 'weekly' as const,
        priority: 0.8,
      })),
    );
  } catch {
    // If product fetch fails, just return static entries
  }

  return [...staticEntries, ...productEntries];
}
