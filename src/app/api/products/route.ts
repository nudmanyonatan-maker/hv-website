import { NextRequest, NextResponse } from 'next/server';
import { getSession } from '@/lib/auth';
import { getCachedProducts } from '@/lib/catalog-cache';
import { stripPrices, langToId } from '@/lib/utils';

export async function GET(request: NextRequest) {
  const lang = request.nextUrl.searchParams.get('lang') ?? 'en';

  try {
    const [session, products] = await Promise.all([
      getSession(),
      getCachedProducts(langToId(lang)),
    ]);

    const data = session?.approved ? products : stripPrices(products);

    const response = NextResponse.json({ products: data });
    response.headers.set('Cache-Control', 'public, s-maxage=300, stale-while-revalidate=600');
    return response;
  } catch (error) {
    console.error('Products API error:', error);
    return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
  }
}
