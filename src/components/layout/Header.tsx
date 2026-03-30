'use client';

import Link from 'next/link';
import { useState, useEffect } from 'react';
import { usePathname } from 'next/navigation';
import type { Dictionary, Locale } from '@/types';
import { cn } from '@/lib/utils';
import LangSwitcher from '@/components/ui/LangSwitcher';
import { useCart } from '@/lib/useCart';

/* Note: layout.tsx needs pb-20 md:pb-0 on main for bottom nav clearance */

interface HeaderProps {
  dict: Dictionary;
  lang: Locale;
}

export default function Header({ dict, lang }: HeaderProps) {
  const [mobileOpen, setMobileOpen] = useState(false);
  const pathname = usePathname();
  const { count } = useCart();

  // Client-side auth detection — check cookie existence (no API call)
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [userName, setUserName] = useState('');
  const [authChecked, setAuthChecked] = useState(false);
  useEffect(() => {
    setIsAuthenticated(document.cookie.includes('hv-logged-in='));
    setUserName(localStorage.getItem('hv-user-name') || '');
    setAuthChecked(true);
  }, []);

  /** Returns true when the given href matches the current path */
  function isActive(href: string) {
    // Exact match for home, startsWith for sub-pages
    if (href === `/${lang}`) return pathname === `/${lang}` || pathname === `/${lang}/`;
    return pathname.startsWith(href);
  }

  function navClass(href: string, base = 'text-sm font-medium tracking-wide transition') {
    return cn(base, isActive(href) ? 'text-red-700' : 'text-gray-500 hover:text-gray-900');
  }

  function mobileNavClass(href: string) {
    return cn('text-sm py-1 px-3 rounded-lg', isActive(href) ? 'text-red-700 font-medium' : 'text-gray-600');
  }

  return (
    <>
      {/* Company info bar — desktop only */}
      <div className="hidden md:block bg-gray-950 text-gray-400 text-xs py-1.5">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
          <span className="font-medium text-gray-300">Home Value LLC</span>
          <div className="flex items-center gap-4">
            <span>📞 773.681.2440</span>
            <span>📍 525 W University Dr, Arlington Heights, IL 60004</span>
          </div>
        </div>
      </div>
      <header className="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-100/80 shadow-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-20">
            {/* Logo — image only, no text */}
            <Link href={`/${lang}`} className="flex-shrink-0">
              <img src="/logo.png" alt="Home Value" className="h-12 w-auto" />
            </Link>

            {/* Desktop Nav — icons + text labels */}
            <nav className="hidden md:flex items-center gap-6">
              <Link href={`/${lang}`} prefetch={false} className={navClass(`/${lang}`)}>
                <span className="flex items-center gap-1.5">
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                  </svg>
                  {dict.nav.home}
                </span>
              </Link>
              <Link href={`/${lang}/contact`} className={navClass(`/${lang}/contact`)}>
                <span className="flex items-center gap-1.5">
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  {dict.nav.contact}
                </span>
              </Link>

              {isAuthenticated && (
                <>
                  <Link href={`/${lang}/account/catalog`} className={navClass(`/${lang}/account/catalog`)}>
                    <span className="flex items-center gap-1.5">
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                      </svg>
                      {dict.nav.catalog}
                    </span>
                  </Link>
                  <Link href={`/${lang}/account/orders`} className={navClass(`/${lang}/account/orders`)}>
                    <span className="flex items-center gap-1.5">
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                      </svg>
                      {dict.nav.orders}
                    </span>
                  </Link>
                </>
              )}
            </nav>

            {/* Right side — cart with label, lang switcher, auth buttons */}
            <div className="hidden md:flex items-center gap-3">
              <Link href={isAuthenticated ? `/${lang}/account/cart` : `/${lang}/cart`} className="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition px-3 py-2 rounded-lg hover:bg-gray-50">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                </svg>
                <span className="text-sm font-medium">{dict.nav.cart}</span>
                {count > 0 && (
                  <span className="bg-red-700 text-white text-xs min-w-[20px] h-5 flex items-center justify-center rounded-full px-1.5">
                    {count > 99 ? '99+' : count}
                  </span>
                )}
              </Link>
              <LangSwitcher lang={lang} />

              {!authChecked ? (
                <div className="w-32 h-10" /> /* Placeholder while checking auth */
              ) : isAuthenticated ? (
                <div className="flex items-center gap-3">
                  <div className="flex items-center gap-2.5 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2">
                    <div className="w-8 h-8 rounded-full bg-red-700 text-white flex items-center justify-center text-sm font-bold">
                      {(userName || 'U').charAt(0).toUpperCase()}
                    </div>
                    <div className="flex flex-col">
                      <span className="text-[10px] text-gray-400 uppercase tracking-wider leading-none">
                        {lang === 'es' ? 'Bienvenido' : 'Welcome'}
                      </span>
                      <span className="text-sm font-semibold text-gray-900 leading-tight">
                        {userName || 'User'}
                      </span>
                    </div>
                  </div>
                  <form action="/api/auth/logout" method="POST">
                    <button
                      type="submit"
                      className="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-500 hover:text-red-700 hover:bg-red-50 transition"
                    >
                      {dict.nav.logout}
                    </button>
                  </form>
                </div>
              ) : (
                <>
                  <Link
                    href={`/${lang}/login`}
                    className="px-5 py-2.5 rounded-xl text-sm font-semibold border-2 border-gray-200 text-gray-700 hover:border-gray-400 hover:bg-gray-50 transition"
                  >
                    {dict.nav.login}
                  </Link>
                  <Link
                    href={`/${lang}/register`}
                    className="px-5 py-2.5 rounded-xl text-sm font-semibold bg-red-700 text-white hover:bg-red-800 transition"
                  >
                    {dict.nav.register}
                  </Link>
                </>
              )}
            </div>

            {/* Mobile hamburger — secondary, for lang switcher + auth only */}
            <button
              className="md:hidden p-2"
              onClick={() => setMobileOpen(!mobileOpen)}
              aria-label="Toggle menu"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {mobileOpen ? (
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                ) : (
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                )}
              </svg>
            </button>
          </div>

          {/* Mobile menu — lang switcher + auth links only (main nav is in bottom bar) */}
          <div
            className={cn(
              'md:hidden transition-all duration-200 ease-in-out overflow-hidden',
              mobileOpen ? 'max-h-96 opacity-100 pb-4' : 'max-h-0 opacity-0'
            )}
          >
            <nav className="flex flex-col gap-3 bg-gray-50/50 rounded-lg p-3 border-t border-gray-100">
              <Link href={`/${lang}`} prefetch={false} className={mobileNavClass(`/${lang}`)} onClick={() => setMobileOpen(false)}>
                {dict.nav.home}
              </Link>
              <Link href={`/${lang}/contact`} className={mobileNavClass(`/${lang}/contact`)} onClick={() => setMobileOpen(false)}>
                {dict.nav.contact}
              </Link>

              {isAuthenticated && (
                <>
                  <Link href={`/${lang}/account/catalog`} className={mobileNavClass(`/${lang}/account/catalog`)} onClick={() => setMobileOpen(false)}>
                    {dict.nav.catalog}
                  </Link>
                  <Link href={`/${lang}/account/cart`} className={mobileNavClass(`/${lang}/account/cart`)} onClick={() => setMobileOpen(false)}>
                    {dict.nav.cart}
                  </Link>
                  <Link href={`/${lang}/account/orders`} className={mobileNavClass(`/${lang}/account/orders`)} onClick={() => setMobileOpen(false)}>
                    {dict.nav.orders}
                  </Link>
                </>
              )}

              <Link href={isAuthenticated ? `/${lang}/account/cart` : `/${lang}/cart`} className="relative text-gray-600 hover:text-gray-900 transition flex items-center gap-2" onClick={() => setMobileOpen(false)}>
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                </svg>
                <span className="text-sm">{dict.nav.cart}</span>
                {count > 0 && (
                  <span className="bg-red-700 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                    {count > 99 ? '99+' : count}
                  </span>
                )}
              </Link>
              <div className="pt-3 border-t border-gray-100 space-y-3">
                {!authChecked ? null : isAuthenticated ? (
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <div className="w-8 h-8 rounded-full bg-red-700 text-white flex items-center justify-center text-sm font-bold">
                        {(userName || 'U').charAt(0).toUpperCase()}
                      </div>
                      <div>
                        <p className="text-xs text-gray-400">{lang === 'es' ? 'Conectado como' : 'Logged in as'}</p>
                        <p className="text-sm font-semibold text-gray-900">{userName || 'User'}</p>
                      </div>
                    </div>
                    <form action="/api/auth/logout" method="POST">
                      <button type="submit" className="text-sm text-gray-500 hover:text-red-700 font-medium" onClick={() => setMobileOpen(false)}>
                        {dict.nav.logout}
                      </button>
                    </form>
                  </div>
                ) : (
                  <div className="flex items-center gap-3">
                    <Link href={`/${lang}/login`} className="flex-1 text-center py-2.5 rounded-xl text-sm font-semibold border-2 border-gray-200 text-gray-700 hover:bg-gray-50" onClick={() => setMobileOpen(false)}>
                      {dict.nav.login}
                    </Link>
                    <Link href={`/${lang}/register`} className="flex-1 text-center py-2.5 rounded-xl text-sm font-semibold bg-red-700 text-white hover:bg-red-800" onClick={() => setMobileOpen(false)}>
                      {dict.nav.register}
                    </Link>
                  </div>
                )}
                <div className="flex justify-center">
                  <LangSwitcher lang={lang} />
                </div>
              </div>
            </nav>
          </div>
        </div>
      </header>

      {/* Mobile bottom navigation — always visible on small screens */}
      <nav className="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 shadow-[0_-2px_10px_rgba(0,0,0,0.05)] px-2 py-2">
        <div className="flex items-center justify-around">
          <Link href={`/${lang}`} prefetch={false} onClick={() => setMobileOpen(false)}
            className={cn('flex flex-col items-center gap-0.5 px-3 py-1 rounded-lg min-w-[60px]', isActive(`/${lang}`) ? 'text-red-700' : 'text-gray-500')}>
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span className="text-[10px] font-semibold">{dict.nav.home}</span>
          </Link>

          <Link href={`/${lang}`} prefetch={false} onClick={() => setMobileOpen(false)}
            className="flex flex-col items-center gap-0.5 px-3 py-1 rounded-lg min-w-[60px] text-gray-500">
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <span className="text-[10px] font-semibold">{lang === 'es' ? 'Catálogo' : 'Browse'}</span>
          </Link>

          <Link href={isAuthenticated ? `/${lang}/account/cart` : `/${lang}/cart`} onClick={() => setMobileOpen(false)}
            className={cn('flex flex-col items-center gap-0.5 px-3 py-1 rounded-lg min-w-[60px] relative', (isActive(`/${lang}/cart`) || isActive(`/${lang}/account/cart`)) ? 'text-red-700' : 'text-gray-500')}>
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
            </svg>
            <span className="text-[10px] font-semibold">{dict.nav.cart}</span>
            {count > 0 && (
              <span className="absolute top-0 right-1 bg-red-700 text-white text-[9px] w-4 h-4 flex items-center justify-center rounded-full">
                {count}
              </span>
            )}
          </Link>

          {isAuthenticated ? (
            <Link href={`/${lang}/account/orders`} onClick={() => setMobileOpen(false)}
              className={cn('flex flex-col items-center gap-0.5 px-3 py-1 rounded-lg min-w-[60px]', isActive(`/${lang}/account/orders`) ? 'text-red-700' : 'text-gray-500')}>
              <div className="w-6 h-6 rounded-full bg-red-700 text-white flex items-center justify-center text-[10px] font-bold">
                {(userName || 'U').charAt(0).toUpperCase()}
              </div>
              <span className="text-[10px] font-semibold truncate max-w-[60px]">{userName?.split(' ')[0] || dict.nav.account}</span>
            </Link>
          ) : (
            <Link href={`/${lang}/login`} onClick={() => setMobileOpen(false)}
              className={cn('flex flex-col items-center gap-0.5 px-3 py-1 rounded-lg min-w-[60px]', isActive(`/${lang}/login`) ? 'text-red-700' : 'text-gray-500')}>
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span className="text-[10px] font-semibold">{dict.nav.login}</span>
            </Link>
          )}
        </div>
      </nav>
    </>
  );
}
