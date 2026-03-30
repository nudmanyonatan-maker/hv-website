import { NextResponse } from 'next/server';
import { getCachedProducts, getCachedCategories } from '@/lib/catalog-cache';

/**
 * Cache warmup endpoint. Called by Vercel cron daily at 8am
 * to pre-warm the in-memory product cache.
 */
export async function GET() {
  const start = Date.now();

  try {
    const [productsEn, productsEs, categoriesEn, categoriesEs] = await Promise.all([
      getCachedProducts('1'),
      getCachedProducts('2'),
      getCachedCategories('1'),
      getCachedCategories('2'),
    ]);

    return NextResponse.json({
      ok: true,
      products: { en: productsEn.length, es: productsEs.length },
      categories: { en: categoriesEn.length, es: categoriesEs.length },
      ms: Date.now() - start,
    });
  } catch (error) {
    return NextResponse.json({ ok: false, error: String(error) }, { status: 500 });
  }
}
