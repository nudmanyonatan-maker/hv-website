import type { Locale } from '@/types';
import { getDictionary } from '@/i18n/getDictionary';
import { getCachedProducts, getCachedCategories } from '@/lib/catalog-cache';
import { slimProducts, langToId } from '@/lib/utils';
import HomeCatalog from '../../HomeCatalog';

export default async function AuthenticatedCatalogPage({
  params,
}: {
  params: Promise<{ lang: string }>;
}) {
  const { lang } = await params;
  const locale = (lang === 'es' ? 'es' : 'en') as Locale;

  const [dict, products, categories] = await Promise.all([
    getDictionary(locale),
    getCachedProducts(langToId(locale)),
    getCachedCategories(langToId(locale)),
  ]);

  return (
    <HomeCatalog
      dict={dict}
      lang={locale}
      showPrice={true}
      initialProducts={slimProducts(products)}
      initialCategories={categories}
    />
  );
}
