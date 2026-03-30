import type { Locale } from '@/types';
import { getDictionary } from '@/i18n/getDictionary';
import { getCachedProducts, getCachedCategories } from '@/lib/catalog-cache';
import { stripPrices, slimProducts, langToId } from '@/lib/utils';
import HomeCatalog from './HomeCatalog';

// ISR: rebuild page every 5 minutes in background
export const revalidate = 300;

export default async function HomePage({
  params,
}: {
  params: Promise<{ lang: string }>;
}) {
  const { lang } = await params;
  const locale = (lang === 'es' ? 'es' : 'en') as Locale;
  const dict = await getDictionary(locale);

  const [rawProducts, categories] = await Promise.all([
    getCachedProducts(langToId(locale)),
    getCachedCategories(langToId(locale)),
  ]);

  // Always serve public catalog (no prices) — auth handled client-side
  const products = slimProducts(stripPrices(rawProducts));

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <HomeCatalog
        dict={dict}
        lang={locale}
        showPrice={false}
        initialProducts={products}
        initialCategories={categories}
      />
    </div>
  );
}
