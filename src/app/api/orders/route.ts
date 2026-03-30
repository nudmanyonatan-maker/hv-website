import { NextRequest, NextResponse } from 'next/server';
import { createOrder, getOrders } from '@/lib/fullvendor';
import { getSession } from '@/lib/auth';
import { langToId } from '@/lib/utils';
import { orderSchema } from '@/lib/validation';

export async function GET(request: NextRequest) {
  const session = await getSession();
  if (!session?.approved) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const lang = request.nextUrl.searchParams.get('lang') ?? 'en';

  try {
    const res = await getOrders(session.userId, session.customerId, langToId(lang));
    return NextResponse.json({ orders: res.order_list ?? [] });
  } catch (error) {
    console.error('Orders GET error:', error);
    return NextResponse.json({ error: 'Failed to fetch orders' }, { status: 500 });
  }
}

export async function POST(request: NextRequest) {
  const session = await getSession();
  if (!session?.approved) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  try {
    // Validate customer ID before allowing order creation
    if (!session.customerId || session.customerId <= 0) {
      return NextResponse.json(
        { error: 'No customer account linked. Please contact support to place orders.' },
        { status: 403 }
      );
    }

    const body = await request.json();
    const parsed = orderSchema.safeParse(body);
    if (!parsed.success) {
      return NextResponse.json({ error: parsed.error.issues[0].message }, { status: 400 });
    }
    const { items, lang } = parsed.data;
    const res = await createOrder({
      userId: session.userId,
      customerId: session.customerId,
      languageId: langToId(lang ?? 'en'),
      orderComment: parsed.data.orderComment,
      items,
    });

    if (res.status !== '1') {
      return NextResponse.json({ error: res.message ?? 'Failed to create order' }, { status: 500 });
    }

    // Clear the cart after successful order
    try {
      const { clearCart } = await import('@/lib/fullvendor');
      await clearCart(session.userId);
    } catch {
      // Cart clear failure shouldn't break the success response
      console.error('[orders] Failed to clear cart after order');
    }

    return NextResponse.json({ success: true, orderId: res.order_id });
  } catch (error) {
    console.error('Orders POST error:', error);
    return NextResponse.json({ error: 'Failed to create order' }, { status: 500 });
  }
}
