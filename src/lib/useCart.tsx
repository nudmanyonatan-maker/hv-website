'use client';

import { createContext, useContext, useState, useEffect, useCallback, ReactNode } from 'react';

export interface LocalCartItem {
  productId: number;
  name: string;
  sku: string;
  image: string;
  qty: number;
  // No price for guests — they see "Login to see prices"
}

interface CartContextValue {
  items: LocalCartItem[];
  count: number;
  addItem: (item: Omit<LocalCartItem, 'qty'>, qty?: number) => void;
  removeItem: (productId: number) => void;
  updateQty: (productId: number, qty: number) => void;
  clearCart: () => void;
  getItems: () => LocalCartItem[];
}

const CartContext = createContext<CartContextValue>({
  items: [],
  count: 0,
  addItem: () => {},
  removeItem: () => {},
  updateQty: () => {},
  clearCart: () => {},
  getItems: () => [],
});

export function useCart() {
  return useContext(CartContext);
}

const STORAGE_KEY = 'hv-cart';

function loadCart(): LocalCartItem[] {
  if (typeof window === 'undefined') return [];
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

function saveCart(items: LocalCartItem[]) {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  } catch {
    // localStorage full or unavailable
  }
}

export function CartProvider({ children }: { children: ReactNode }) {
  const [items, setItems] = useState<LocalCartItem[]>([]);
  const [loaded, setLoaded] = useState(false);

  // Load from localStorage on mount
  useEffect(() => {
    setItems(loadCart());
    setLoaded(true);
  }, []);

  // Save to localStorage on change
  useEffect(() => {
    if (loaded) saveCart(items);
  }, [items, loaded]);

  const addItem = useCallback((item: Omit<LocalCartItem, 'qty'>, qty = 1) => {
    setItems((prev) => {
      const existing = prev.find((i) => i.productId === item.productId);
      if (existing) {
        return prev.map((i) =>
          i.productId === item.productId ? { ...i, qty: i.qty + qty } : i
        );
      }
      return [...prev, { ...item, qty }];
    });
  }, []);

  const removeItem = useCallback((productId: number) => {
    setItems((prev) => prev.filter((i) => i.productId !== productId));
  }, []);

  const updateQty = useCallback((productId: number, qty: number) => {
    if (qty < 1) return;
    setItems((prev) =>
      prev.map((i) => (i.productId === productId ? { ...i, qty } : i))
    );
  }, []);

  const clearCart = useCallback(() => {
    setItems([]);
  }, []);

  const count = items.reduce((sum, i) => sum + i.qty, 0);

  return (
    <CartContext.Provider value={{ items, count, addItem, removeItem, updateQty, clearCart, getItems: () => items }}>
      {children}
    </CartContext.Provider>
  );
}
