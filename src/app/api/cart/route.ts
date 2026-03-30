import { NextRequest, NextResponse } from 'next/server';
import { getCart, addToCart, removeFromCart, clearCart } from '@/lib/fullvendor';
import { getSession } from '@/lib/auth';
import { langToId } from '@/lib/utils';
import { cartAddSchema, cartRemoveSchema } from '@/lib/validation';

export async function GET(request: NextRequest) {
  const session = await getSession();
  if (!session?.approved) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const lang = request.nextUrl.searchParams.get('lang') ?? 'en';

  try {
    const res = await getCart(session.userId, langToId(lang));
    return NextResponse.json({ items: res.list ?? [] });
  } catch (error) {
    console.error('Cart GET error:', error);
    return NextResponse.json({ error: 'Failed to fetch cart' }, { status: 500 });
  }
}

export async function POST(request: NextRequest) {
  const session = await getSession();
  if (!session?.approved) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  try {
    const body = await request.json();
    const parsed = cartAddSchema.safeParse(body);
    if (!parsed.success) {
      return NextResponse.json({ error: parsed.error.issues[0].message }, { status: 400 });
    }
    const { productId, qty } = parsed.data;
    const lang = body.lang;
    const res = await addToCart({
      userId: session.userId,
      productId,
      qty,
      languageId: langToId(lang ?? 'en'),
    });
    return NextResponse.json(res);
  } catch (error) {
    console.error('Cart POST error:', error);
    return NextResponse.json({ error: 'Failed to update cart' }, { status: 500 });
  }
}

export async function DELETE(request: NextRequest) {
  const session = await getSession();
  if (!session?.approved) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  try {
    const body = await request.json();

    if (body.clearAll) {
      const res = await clearCart(session.userId);
      return NextResponse.json(res);
    }

    const parsed = cartRemoveSchema.safeParse(body);
    if (!parsed.success) {
      return NextResponse.json({ error: parsed.error.issues[0].message }, { status: 400 });
    }

    const res = await removeFromCart(parsed.data.cartId);
    return NextResponse.json(res);
  } catch (error) {
    console.error('Cart DELETE error:', error);
    return NextResponse.json({ error: 'Failed to remove from cart' }, { status: 500 });
  }
}
