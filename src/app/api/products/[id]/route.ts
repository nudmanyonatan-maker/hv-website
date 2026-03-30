import { NextRequest, NextResponse } from 'next/server';
import { getProductDetails } from '@/lib/fullvendor';
import { getSession } from '@/lib/auth';
import { langToId } from '@/lib/utils';

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  const lang = request.nextUrl.searchParams.get('lang') ?? 'en';

  try {
    const session = await getSession();
    const res = await getProductDetails(Number(id), langToId(lang));

    if (res.status !== '1' || !res.details) {
      return NextResponse.json({ error: 'Product not found' }, { status: 404 });
    }

    const product = res.details;

    // Strip prices for unauthenticated users
    if (!session?.approved) {
      const { sale_price, sale_price0, fob_price, purchase_price, ...rest } = product;
      return NextResponse.json({ product: rest });
    }

    return NextResponse.json({ product });
  } catch (error) {
    console.error('Product detail API error:', error);
    return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
  }
}
