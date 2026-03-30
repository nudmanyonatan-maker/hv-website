import { NextRequest, NextResponse } from 'next/server';
import { destroySession } from '@/lib/auth';
import { env } from '@/lib/env';

export async function POST(request: NextRequest) {
  await destroySession();
  // Preserve the user's language preference from the referrer URL
  const referer = request.headers.get('referer') || '';
  const lang = referer.includes('/es') ? 'es' : 'en';
  return NextResponse.redirect(new URL(`/${lang}`, env.SITE_URL));
}
