import { NextResponse, type NextRequest } from 'next/server';
import { jwtVerify } from 'jose';

const locales = ['en', 'es'];
const defaultLocale = 'en';
const COOKIE_NAME = 'hv-session';
const JWT_SECRET_RAW = process.env.JWT_SECRET;
if (!JWT_SECRET_RAW) {
  console.error('[proxy] JWT_SECRET environment variable is not set');
}
const SECRET = new TextEncoder().encode(JWT_SECRET_RAW || '');

function getLocaleFromHeaders(request: NextRequest): string {
  const acceptLang = request.headers.get('accept-language') ?? '';
  const preferred = acceptLang.split(',')[0]?.split('-')[0]?.toLowerCase();
  return locales.includes(preferred!) ? preferred! : defaultLocale;
}

function getLocaleFromPath(pathname: string): string | null {
  const segment = pathname.split('/')[1];
  return locales.includes(segment!) ? segment! : null;
}

export async function proxy(request: NextRequest) {
  const { pathname } = request.nextUrl;

  // Skip API routes, static files, and Next.js internals
  if (
    pathname.startsWith('/api/') ||
    pathname.startsWith('/_next/') ||
    pathname.startsWith('/favicon') ||
    pathname.includes('.')
  ) {
    return NextResponse.next();
  }

  // ── Locale detection ──────────────────────────────
  const pathLocale = getLocaleFromPath(pathname);

  if (!pathLocale) {
    const locale = getLocaleFromHeaders(request);
    const url = request.nextUrl.clone();
    url.pathname = `/${locale}${pathname}`;
    return NextResponse.redirect(url);
  }

  // ── Auth guard for /[lang]/account/* ──────────────
  if (pathname.match(/^\/(en|es)\/account/)) {
    const token = request.cookies.get(COOKIE_NAME)?.value;
    if (!token) {
      const url = request.nextUrl.clone();
      url.pathname = `/${pathLocale}/login`;
      url.searchParams.set('from', pathname);
      return NextResponse.redirect(url);
    }
    try {
      const { payload } = await jwtVerify(token, SECRET);
      const approved = (payload as { approved?: boolean }).approved;
      if (approved !== true) {
        const url = request.nextUrl.clone();
        url.pathname = `/${pathLocale}/login`;
        url.searchParams.set('pending', 'true');
        return NextResponse.redirect(url);
      }
    } catch (error) {
      console.warn('[proxy] JWT verification failed:', error instanceof Error ? error.message : error);
      const url = request.nextUrl.clone();
      url.pathname = `/${pathLocale}/login`;
      url.searchParams.set('from', pathname);
      return NextResponse.redirect(url);
    }
  }

  // ── Admin guard for /[lang]/admin/* ───────────────
  if (pathname.match(/^\/(en|es)\/admin/)) {
    const token = request.cookies.get(COOKIE_NAME)?.value;
    if (!token) {
      const url = request.nextUrl.clone();
      url.pathname = `/${pathLocale}/login`;
      return NextResponse.redirect(url);
    }
    try {
      const { payload } = await jwtVerify(token, SECRET);
      const email = (payload as { email?: string }).email;
      const adminEmail = process.env.ADMIN_EMAIL;
      if (email !== adminEmail) {
        const url = request.nextUrl.clone();
        url.pathname = `/${pathLocale}`;
        return NextResponse.redirect(url);
      }
    } catch (error) {
      console.warn('[proxy] JWT verification failed:', error instanceof Error ? error.message : error);
      const url = request.nextUrl.clone();
      url.pathname = `/${pathLocale}/login`;
      return NextResponse.redirect(url);
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/((?!_next/static|_next/image|favicon.ico|.*\\..*).*)'],
};
