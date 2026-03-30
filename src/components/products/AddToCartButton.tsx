'use client';

import { useToast } from '@/components/ui/Toast';
import { useCart } from '@/lib/useCart';

interface AddToCartButtonProps {
  productId: number;
  name: string;
  sku: string;
  image: string;
  label: string;
  lang: string;
  moq?: number;
  unitType?: string;
}

export default function AddToCartButton({ productId, name, sku, image, label, lang, moq = 1, unitType = '' }: AddToCartButtonProps) {
  const step = Math.max(1, Math.round(moq));
  const { items, addItem, updateQty: updateCartQty, removeItem } = useCart();
  const { showToast } = useToast();

  // Read qty directly from cart — this IS the source of truth
  const cartItem = items.find(i => i.productId === productId);
  const qty = cartItem?.qty ?? 0;

  function handleQtyChange(delta: number) {
    let newQty: number;
    if (delta > 0) {
      newQty = qty === 0 ? step : qty + step;
    } else {
      newQty = Math.max(0, qty - step);
    }

    if (newQty === 0) {
      if (cartItem) removeItem(productId);
    } else if (cartItem) {
      updateCartQty(productId, newQty);
    } else {
      addItem({ productId, name, sku, image }, newQty);
      showToast(label || (lang === 'es' ? 'Agregado al carrito' : 'Added to cart'), 'success');
    }
  }

  return (
    <div className="mt-6">
      {/* Quantity control — directly syncs with cart */}
      <div className={`flex items-center rounded-xl overflow-hidden ${qty > 0 ? 'border-2 border-red-700 bg-red-50' : 'border border-gray-200'}`}>
        <button
          onClick={() => handleQtyChange(-1)}
          disabled={qty <= 0}
          className="w-14 h-14 flex items-center justify-center text-gray-500 hover:bg-gray-100 disabled:opacity-30 text-xl font-bold transition"
        >−</button>
        <span className={`flex-1 text-center text-xl font-bold tabular-nums ${qty > 0 ? 'text-red-700' : 'text-gray-400'}`}>
          {qty}
        </span>
        <button
          onClick={() => handleQtyChange(1)}
          className="w-14 h-14 flex items-center justify-center text-gray-500 hover:bg-gray-100 text-xl font-bold transition"
        >+</button>
      </div>
      {qty > 0 && (
        <p className="text-sm text-red-700 font-semibold text-center mt-2">
          ✓ {lang === 'es' ? 'En carrito' : 'In cart'}
        </p>
      )}
      {step > 1 && qty === 0 && (
        <p className="text-base font-bold text-gray-500 text-center mt-2">
          Min: {step} {unitType || (lang === 'es' ? 'uds' : 'units')}
        </p>
      )}
    </div>
  );
}
