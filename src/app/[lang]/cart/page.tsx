'use client';

import { useState, useEffect } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Image from 'next/image';
import Link from 'next/link';
import type { Locale } from '@/types';
import en from '@/i18n/en.json';
import es from '@/i18n/es.json';
import { useCart } from '@/lib/useCart';

export default function CartPage() {
  const params = useParams();
  const router = useRouter();
  const lang = (params.lang as Locale) ?? 'en';
  const dict = lang === 'es' ? es : en;
  const { items, removeItem, updateQty, clearCart, count } = useCart();

  // Logged-in users go straight to account cart (has prices + FullVendor sync)
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [checked, setChecked] = useState(false);
  useEffect(() => {
    const loggedIn = document.cookie.includes('hv-logged-in=');
    setIsLoggedIn(loggedIn);
    setChecked(true);
    if (loggedIn) {
      router.replace(`/${lang}/account/cart`);
    }
  }, [lang, router]);

  // Don't render anything until we've checked auth (prevents flash of guest cart)
  if (!checked) {
    return (
      <div className="max-w-4xl mx-auto px-4 py-20 text-center">
        <div className="w-6 h-6 border-2 border-gray-200 border-t-red-700 rounded-full animate-spin mx-auto" />
      </div>
    );
  }

  // If logged in, we're redirecting — show nothing
  if (isLoggedIn) {
    return (
      <div className="max-w-4xl mx-auto px-4 py-20 text-center">
        <div className="w-6 h-6 border-2 border-gray-200 border-t-red-700 rounded-full animate-spin mx-auto" />
      </div>
    );
  }

  if (items.length === 0) {
    return (
      <div className="max-w-4xl mx-auto px-4 py-20 text-center">
        <h1 className="text-2xl font-bold text-gray-900 mb-4">{dict.cart.title}</h1>
        <p className="text-gray-400 mb-6">{dict.cart.empty}</p>
        <Link href={`/${lang}`} className="text-sm text-red-700 hover:text-red-800 font-medium">
          {dict.cart.continue_shopping ?? dict.home.continue_shopping}
        </Link>
      </div>
    );
  }

  function handleCheckout() {
    if (!isLoggedIn) {
      // Redirect to login, come back to cart after
      router.push(`/${lang}/login?from=/${lang}/cart`);
      return;
    }
    // For logged-in users, redirect to the full account cart which syncs with FullVendor
    router.push(`/${lang}/account/cart`);
  }

  return (
    <div className="max-w-4xl mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold text-gray-900">{dict.cart.title}</h1>
        <button
          onClick={() => { if (window.confirm(dict.cart.confirm_clear)) clearCart(); }}
          className="text-sm text-red-500 hover:text-red-700"
        >
          {dict.cart.clear_all}
        </button>
      </div>

      <div className="space-y-4 mb-8">
        {items.map((item) => (
          <div key={item.productId} className="flex items-center gap-4 p-4 border border-gray-100 rounded-xl">
            <div className="relative w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden bg-white">
              <Image src={item.image} alt={item.name} fill className="object-contain p-1" sizes="64px" />
            </div>
            <div className="flex-1 min-w-0">
              <h3 className="text-sm font-medium text-gray-900 truncate">{item.name}</h3>
              <p className="text-xs text-gray-400">{item.sku}</p>
            </div>
            <div className="flex items-center gap-1">
              <button
                onClick={() => updateQty(item.productId, item.qty - 1)}
                disabled={item.qty <= 1}
                className="w-7 h-7 flex items-center justify-center rounded-md border border-gray-200 text-gray-500 hover:bg-gray-50 disabled:opacity-30 text-sm"
              >-</button>
              <span className="w-8 text-center text-sm text-gray-700 tabular-nums">{item.qty}</span>
              <button
                onClick={() => updateQty(item.productId, item.qty + 1)}
                className="w-7 h-7 flex items-center justify-center rounded-md border border-gray-200 text-gray-500 hover:bg-gray-50 text-sm"
              >+</button>
            </div>
            {/* No price shown for guests */}
            {!isLoggedIn && (
              <span className="text-xs text-red-700 font-medium whitespace-nowrap">{dict.home.login_to_see_prices}</span>
            )}
            <button onClick={() => removeItem(item.productId)} className="text-xs text-red-400 hover:text-red-600">
              {dict.cart.remove}
            </button>
          </div>
        ))}
      </div>

      <div className="border-t border-gray-100 pt-6">
        <p className="text-sm text-gray-500 mb-4">
          {count} {lang === 'es' ? 'articulos en el carrito' : 'items in cart'}
        </p>
        <button
          onClick={handleCheckout}
          className="w-full bg-red-700 text-white py-4 rounded-xl font-bold text-base hover:bg-red-800 transition flex items-center justify-center gap-2"
        >
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
          </svg>
          {isLoggedIn
            ? (lang === 'es' ? 'Continuar al Pago' : 'Proceed to Checkout')
            : (lang === 'es' ? 'Iniciar Sesión para Comprar' : 'Login to Checkout')}
        </button>
      </div>
    </div>
  );
}
