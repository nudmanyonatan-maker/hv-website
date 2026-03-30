'use client';

import { useEffect, useState } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import type { Product, PublicProduct, SlimProduct, SlimPublicProduct, Dictionary, Locale } from '@/types';
import { formatPrice, getProductImage } from '@/lib/utils';
import { useCart } from '@/lib/useCart';
import { useToast } from '@/components/ui/Toast';

interface ProductCardProps {
  product: Product | PublicProduct | SlimProduct | SlimPublicProduct;
  dict: Dictionary;
  lang: Locale;
  showPrice: boolean;
  priority?: boolean;
}

function isFullProduct(p: Product | PublicProduct | SlimProduct | SlimPublicProduct): p is Product | SlimProduct {
  return 'sale_price' in p;
}

export default function ProductCard({ product, dict, lang, showPrice, priority = false }: ProductCardProps) {
  const imageUrl = getProductImage(product);
  const { items, addItem, updateQty: updateCartQty, removeItem } = useCart();
  const { showToast } = useToast();

  const moq = 'minimum_stock' in product ? Math.max(1, Math.round(Number((product as any).minimum_stock))) : 1;
  const unitType = 'unit_type' in product ? String((product as any).unit_type || '') : '';

  // Read qty directly from cart — this IS the source of truth
  const cartItem = items.find(i => i.productId === product.product_id);
  const qty = cartItem?.qty ?? 0;

  const [isLoggedIn, setIsLoggedIn] = useState(false);
  useEffect(() => {
    setIsLoggedIn(document.cookie.includes('hv-logged-in='));
  }, []);

  function handleQtyChange(e: React.MouseEvent, delta: number) {
    e.preventDefault();
    e.stopPropagation();

    let newQty: number;
    if (delta > 0) {
      newQty = qty === 0 ? moq : qty + moq;
    } else {
      newQty = Math.max(0, qty - moq);
    }

    if (newQty === 0) {
      if (cartItem) removeItem(product.product_id);
    } else if (cartItem) {
      updateCartQty(product.product_id, newQty);
    } else {
      addItem({ productId: product.product_id, name: product.name, sku: product.sku, image: imageUrl }, newQty);
      showToast(lang === 'es' ? 'Agregado al carrito' : 'Added to cart', 'success');
    }
  }

  return (
    <div className="group bg-white rounded-2xl overflow-hidden card-lift border border-gray-100/80 shadow-[0_1px_3px_rgba(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgba(0,0,0,0.08)] hover:border-gray-200 flex flex-col">
      {/* Image — clickable to product detail */}
      <Link
        href={`/${lang}/products/${product.product_id}`}
        prefetch={false}
        className="block relative aspect-square bg-[#FAFAF8] overflow-hidden"
        onClick={() => sessionStorage.setItem('catalog-scroll-y', String(window.scrollY))}
      >
        <Image
          src={imageUrl}
          alt={product.name}
          fill
          className="object-contain p-4 group-hover:scale-[1.03] transition-transform duration-500 ease-out"
          sizes="(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 25vw"
          priority={priority}
          loading={priority ? 'eager' : 'lazy'}
        />
        {/* Out of stock badge */}
        {'available_stock' in product && (product as Product).available_stock <= 0 && (
          <div className="absolute top-3 left-3 bg-gray-900/75 text-white text-[11px] font-medium px-2.5 py-1 rounded-full backdrop-blur-sm">
            {dict.home.out_of_stock_badge}
          </div>
        )}
      </Link>

      {/* Info */}
      <div className="p-4 flex flex-col flex-1">
        <Link href={`/${lang}/products/${product.product_id}`} prefetch={false} className="block"
          onClick={() => sessionStorage.setItem('catalog-scroll-y', String(window.scrollY))}>
          <h3 className="text-sm font-semibold text-gray-900 line-clamp-2 leading-snug tracking-tight">
            {product.name}
          </h3>
        </Link>

        <p className="text-xs text-gray-500 mt-1 font-medium tracking-wide uppercase">
          {product.sku}
        </p>

        {/* Stock indicator */}
        {'available_stock' in product && (
          <p className={`text-xs mt-1 font-medium ${
            (product as any).available_stock > 0
              ? 'text-emerald-600'
              : 'text-red-500'
          }`}>
            {(product as any).available_stock > 0
              ? `${lang === 'es' ? 'Disponible' : 'In Stock'}: ${(product as any).available_stock}`
              : dict.home.out_of_stock_badge}
          </p>
        )}

        {/* Price or status */}
        <div className="border-t border-gray-100 mt-3 pt-3 mt-auto">
          {showPrice && isFullProduct(product) ? (
            <span className="text-lg font-bold text-gray-900 tracking-tight">
              {formatPrice(product.sale_price, product.currency_type, lang)}
            </span>
          ) : isLoggedIn ? (
            <Link
              href={`/${lang}/account/catalog`}
              onClick={(e) => e.stopPropagation()}
              className="text-xs text-emerald-700 font-semibold flex items-center gap-1 hover:underline"
            >
              <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              {lang === 'es' ? 'Ver precios en catálogo' : 'View prices in catalog'}
            </Link>
          ) : (
            <p className="text-xs text-red-600/80 font-medium flex items-center gap-1">
              <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              {dict.home.login_to_see_prices}
            </p>
          )}
        </div>

        {/* Quantity control — directly syncs with cart */}
        <div className="mt-3">
          <div className={`flex items-center rounded-xl overflow-hidden ${qty > 0 ? 'border-2 border-red-700 bg-red-50' : 'border border-gray-200'}`}>
            <button
              onClick={(e) => handleQtyChange(e, -1)}
              disabled={qty <= 0}
              className="w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-100 disabled:opacity-30 text-lg font-bold transition"
            >−</button>
            <span className={`flex-1 text-center text-base font-bold tabular-nums ${qty > 0 ? 'text-red-700' : 'text-gray-400'}`}>
              {qty}
            </span>
            <button
              onClick={(e) => handleQtyChange(e, 1)}
              className="w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-100 text-lg font-bold transition"
            >+</button>
          </div>
          {qty > 0 && (
            <p className="text-xs text-red-700 font-semibold text-center mt-1">
              ✓ {lang === 'es' ? 'En carrito' : 'In cart'}
            </p>
          )}
          {moq > 1 && qty === 0 && (
            <p className="text-sm font-semibold text-gray-500 text-center mt-1">
              Min: {moq} {unitType || (lang === 'es' ? 'uds' : 'units')}
            </p>
          )}
        </div>
      </div>
    </div>
  );
}
