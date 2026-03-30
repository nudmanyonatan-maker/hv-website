'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import Image from 'next/image';
import Link from 'next/link';
import type { Locale, CartItem } from '@/types';
import { getProductImage, formatPrice } from '@/lib/utils';

export default function CartPage() {
  const params = useParams();
  const lang = (params.lang as Locale) ?? 'en';

  const [items, setItems] = useState<CartItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [orderComment, setOrderComment] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [success, setSuccess] = useState('');
  const [updatingId, setUpdatingId] = useState<number | null>(null);

  const t = lang === 'es' ? {
    title: 'Carrito de Compras', empty: 'Su carrito está vacío',
    qty: 'Cant.', remove: 'Eliminar', clearAll: 'Vaciar Carrito',
    subtotal: 'Subtotal', sendOrder: 'Enviar Pedido', payNow: 'Pagar Ahora',
    comments: 'Comentarios del pedido (opcional)',
    success: '¡Pedido realizado exitosamente!',
    continueShopping: 'Seguir Comprando',
    confirmClear: '¿Está seguro de que desea vaciar su carrito?',
  } : {
    title: 'Shopping Cart', empty: 'Your cart is empty',
    qty: 'Qty', remove: 'Remove', clearAll: 'Clear Cart',
    subtotal: 'Subtotal', sendOrder: 'Send Order', payNow: 'Pay Now',
    comments: 'Order comments (optional)',
    success: 'Order placed successfully!',
    continueShopping: 'Continue Shopping',
    confirmClear: 'Are you sure you want to clear your cart?',
  };

  useEffect(() => {
    async function syncGuestCart() {
      const raw = localStorage.getItem('hv-cart');
      if (!raw) return;
      const guestItems = JSON.parse(raw) as { productId: number; qty: number }[];
      if (guestItems.length === 0) return;

      // Sync each item to FullVendor
      let allSynced = true;
      for (const item of guestItems) {
        try {
          const res = await fetch('/api/cart', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ productId: item.productId, qty: item.qty, lang }),
          });
          if (!res.ok) allSynced = false;
        } catch {
          allSynced = false;
        }
      }

      // Only clear guest cart if all items synced successfully
      if (allSynced) {
        localStorage.removeItem('hv-cart');
      } else {
        console.warn('[cart-sync] Some items failed to sync — keeping localStorage cart');
      }

      // Refresh the FullVendor cart
      fetchCart();
    }

    syncGuestCart();
  }, [lang]);

  useEffect(() => {
    fetchCart();
  }, []);

  async function fetchCart() {
    try {
      const res = await fetch(`/api/cart?lang=${lang}`);
      const data = await res.json();
      setItems(data.items ?? []);
    } catch {
      // ignore
    } finally {
      setLoading(false);
    }
  }

  async function removeItem(cartId: number) {
    await fetch('/api/cart', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ cartId }),
    });
    fetchCart();
  }

  async function updateQuantity(item: CartItem, newQty: number) {
    if (newQty < 1) return;
    setUpdatingId(item.cart_id);
    try {
      await fetch('/api/cart', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          productId: item.product_id,
          qty: newQty,
        }),
      });
      // Optimistically update local state
      setItems((prev) =>
        prev.map((i) => (i.cart_id === item.cart_id ? { ...i, qty: newQty } : i))
      );
    } catch {
      // Refetch on failure to stay in sync
      fetchCart();
    } finally {
      setUpdatingId(null);
    }
  }

  async function clearAll() {
    if (!window.confirm(t.confirmClear)) return;
    await fetch('/api/cart', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ clearAll: true }),
    });
    setItems([]);
  }

  async function sendOrder() {
    setSubmitting(true);
    try {
      const orderItems = items.map((item) => ({
        product_id: item.product_id,
        qty: item.qty,
        sale_price: item.sale_price,
      }));

      const res = await fetch('/api/orders', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ items: orderItems, orderComment, lang }),
      });

      if (res.ok) {
        setSuccess(t.success);
        setItems([]);
      } else {
        const data = await res.json().catch(() => ({}));
        alert(data.error || (lang === 'es' ? 'Error al realizar el pedido' : 'Failed to place order'));
      }
    } catch {
      alert(lang === 'es' ? 'Error de conexión' : 'Connection error');
    } finally {
      setSubmitting(false);
    }
  }

  const subtotal = items.reduce((sum, item) => sum + item.sale_price * item.qty, 0);

  if (loading) {
    return <div className="text-center py-20 text-gray-400">Loading...</div>;
  }

  if (success) {
    return (
      <div className="text-center py-20">
        <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <p className="text-gray-600">{success}</p>
      </div>
    );
  }

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold text-gray-900">{t.title}</h1>
        {items.length > 0 && (
          <button onClick={clearAll} className="text-sm text-red-500 hover:text-red-700">
            {t.clearAll}
          </button>
        )}
      </div>

      {items.length === 0 ? (
        <div className="text-center py-20">
          <p className="text-gray-400">{t.empty}</p>
          <Link
            href={`/${lang}`}
            className="inline-block mt-4 text-sm text-red-700 hover:text-red-800 font-medium transition"
          >
            {t.continueShopping}
          </Link>
        </div>
      ) : (
        <>
          <div className="space-y-4 mb-8">
            {items.map((item) => (
              <div key={item.cart_id} className="flex items-center gap-4 p-4 border border-gray-100 rounded-xl">
                <div className="relative w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden bg-gray-50">
                  <Image src={getProductImage(item)} alt={item.name} fill className="object-cover" sizes="64px" />
                </div>
                <div className="flex-1 min-w-0">
                  <h3 className="text-sm font-medium text-gray-900 truncate">{item.name}</h3>
                  <p className="text-xs text-gray-400">{item.sku}</p>
                </div>
                {/* Quantity controls */}
                <div className="flex items-center gap-1">
                  <button
                    onClick={() => updateQuantity(item, item.qty - 1)}
                    disabled={item.qty <= 1 || updatingId === item.cart_id}
                    className="w-7 h-7 flex items-center justify-center rounded-md border border-gray-200 text-gray-500 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed text-sm"
                    aria-label="Decrease quantity"
                  >
                    -
                  </button>
                  <span className="w-8 text-center text-sm text-gray-700 tabular-nums">{item.qty}</span>
                  <button
                    onClick={() => updateQuantity(item, item.qty + 1)}
                    disabled={updatingId === item.cart_id}
                    className="w-7 h-7 flex items-center justify-center rounded-md border border-gray-200 text-gray-500 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed text-sm"
                    aria-label="Increase quantity"
                  >
                    +
                  </button>
                </div>
                <div className="text-sm font-bold text-gray-900">
                  {formatPrice(item.sale_price * item.qty, 'USD', lang)}
                </div>
                <button onClick={() => removeItem(item.cart_id)} className="text-xs text-red-400 hover:text-red-600">
                  {t.remove}
                </button>
              </div>
            ))}
          </div>

          {/* Comments */}
          <textarea
            value={orderComment}
            onChange={(e) => setOrderComment(e.target.value)}
            placeholder={t.comments}
            rows={3}
            className="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-700 resize-none mb-6"
          />

          {/* Totals + Actions */}
          <div className="border-t border-gray-100 pt-6">
            <div className="flex justify-between items-center mb-6">
              <span className="text-lg font-medium text-gray-900">{t.subtotal}</span>
              <span className="text-2xl font-bold text-gray-900">{formatPrice(subtotal, 'USD', lang)}</span>
            </div>

            <button
              onClick={sendOrder}
              disabled={submitting}
              className="w-full bg-red-700 text-white py-4 rounded-xl font-bold text-base hover:bg-red-800 transition disabled:opacity-50 flex items-center justify-center gap-2"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              {submitting ? '...' : t.sendOrder}
            </button>
          </div>
        </>
      )}
    </div>
  );
}
