import { NextRequest, NextResponse } from 'next/server';
import { getCategories } from '@/lib/fullvendor';
import { langToId } from '@/lib/utils';

export async function GET(request: NextRequest) {
  const lang = request.nextUrl.searchParams.get('lang') ?? 'en';

  try {
    const res = await getCategories(langToId(lang));

    if (res.status !== '1') {
      return NextResponse.json({ error: 'Failed to fetch categories' }, { status: 500 });
    }

    return NextResponse.json({ categories: res.list ?? [] });
  } catch (error) {
    console.error('Categories API error:', error);
    return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
  }
}
