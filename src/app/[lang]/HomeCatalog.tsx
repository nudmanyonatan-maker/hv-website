'use client';

import { useState, useMemo, useCallback, useRef, useEffect } from 'react';
import type { Product, PublicProduct, SlimProduct, SlimPublicProduct, Category, Dictionary, Locale } from '@/types';
import ProductCard from '@/components/products/ProductCard';
import CategoryBar from '@/components/products/CategoryBar';

const PAGE_SIZE = 40;

interface HomeCatalogProps {
  dict: Dictionary;
  lang: Locale;
  showPrice: boolean;
  initialProducts: (Product | PublicProduct | SlimProduct | SlimPublicProduct)[];
  initialCategories: Category[];
}

export default function HomeCatalog({
  dict,
  lang,
  showPrice,
  initialProducts,
  initialCategories,
}: HomeCatalogProps) {
  // Restore scroll position when returning from product detail
  useEffect(() => {
    const savedY = sessionStorage.getItem('catalog-scroll-y');
    if (savedY) {
      requestAnimationFrame(() => {
        window.scrollTo(0, parseInt(savedY, 10));
        sessionStorage.removeItem('catalog-scroll-y');
      });
    }
  }, []);

  const [isLoggedIn, setIsLoggedIn] = useState(false);
  useEffect(() => {
    setIsLoggedIn(document.cookie.includes('hv-logged-in='));
  }, []);

  const [drawerOpen, setDrawerOpen] = useState(false);
  const [categoryId, setCategoryId] = useState<string | null>(null);
  const [searchInput, setSearchInput] = useState('');
  const [search, setSearch] = useState('');
  const [stockFilter, setStockFilter] = useState<'all' | 'available'>('all');
  const [visibleCount, setVisibleCount] = useState(PAGE_SIZE);
  const loadMoreRef = useRef<HTMLDivElement>(null);

  // Debounce search
  useEffect(() => {
    const timer = setTimeout(() => setSearch(searchInput), 300);
    return () => clearTimeout(timer);
  }, [searchInput]);

  // Reset pagination when filters change
  const handleCategoryChange = useCallback((id: string | null) => {
    setCategoryId(id);
    setVisibleCount(PAGE_SIZE);
  }, []);

  const handleSearchChange = useCallback((value: string) => {
    setSearchInput(value);
    setVisibleCount(PAGE_SIZE);
  }, []);

  // Category product counts
  const categoryCounts = useMemo(() => {
    const counts = new Map<string, number>();
    initialProducts.forEach((p) => {
      p.category_id.split(',').map((s) => s.trim()).forEach((id) => {
        counts.set(id, (counts.get(id) || 0) + 1);
      });
    });
    return counts;
  }, [initialProducts]);

  const inStockCount = useMemo(() => {
    return initialProducts.filter((p) => 'available_stock' in p && (p as Product).available_stock > 0).length;
  }, [initialProducts]);

  const filtered = useMemo(() => {
    let list = initialProducts;

    if (stockFilter === 'available') {
      list = list.filter((p) => 'available_stock' in p && (p as Product).available_stock > 0);
    }

    if (categoryId) {
      list = list.filter((p) => {
        const ids = p.category_id.split(',').map((s) => s.trim());
        return ids.includes(categoryId);
      });
    }

    if (search.trim()) {
      const q = search.toLowerCase();
      list = list.filter(
        (p) =>
          p.name.toLowerCase().includes(q) ||
          p.sku.toLowerCase().includes(q) ||
          p.descriptions?.toLowerCase().includes(q) ||
          p.tags?.toLowerCase().includes(q)
      );
    }

    return list;
  }, [initialProducts, categoryId, search, stockFilter]);

  const visible = filtered.slice(0, visibleCount);
  const hasMore = visibleCount < filtered.length;
  const isFiltering = !!categoryId || !!search.trim() || stockFilter !== 'all';

  // Infinite scroll observer
  useEffect(() => {
    if (!hasMore) return;
    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting) {
          setVisibleCount((c) => c + PAGE_SIZE);
        }
      },
      { rootMargin: '200px' }
    );
    if (loadMoreRef.current) observer.observe(loadMoreRef.current);
    return () => observer.disconnect();
  }, [hasMore, visibleCount]);

  function clearFilters() {
    setCategoryId(null);
    setSearchInput('');
    setSearch('');
    setStockFilter('all');
    setVisibleCount(PAGE_SIZE);
  }

  return (
    <>
      {/* Hero — editorial magazine-like header */}
      <div className="mb-10 pt-4">
        <p className="text-xs font-semibold text-red-700 uppercase tracking-[0.2em] mb-3">
          {lang === 'es' ? 'Catálogo' : 'Catalog'}
        </p>
        <h1 className="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight leading-[1.1]">
          {dict.home.hero_title}
        </h1>
        <p className="text-gray-500 text-sm sm:text-base mt-3 max-w-md leading-relaxed">
          {dict.home.hero_subtitle}
        </p>
      </div>

      {/* Logged-in user banner — directs to priced catalog */}
      {isLoggedIn && (
        <div className="mb-6 flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded-xl px-5 py-4">
          <div>
            <p className="text-sm font-semibold text-emerald-800">
              {lang === 'es' ? '¡Bienvenido! Ya puedes ver precios.' : 'Welcome! You can view wholesale prices.'}
            </p>
            <p className="text-xs text-emerald-600 mt-0.5">
              {lang === 'es' ? 'Accede al catálogo completo con precios y pedidos.' : 'Access the full catalog with pricing and ordering.'}
            </p>
          </div>
          <a href={`/${lang}/account/catalog`} className="flex-shrink-0 bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-emerald-800 transition">
            {lang === 'es' ? 'Ver Catálogo con Precios' : 'View Catalog with Prices'} →
          </a>
        </div>
      )}

      {/* Category Drawer */}
      {drawerOpen && (
        <div className="fixed inset-0 z-50 flex">
          {/* Backdrop */}
          <div className="fixed inset-0 bg-black/40" onClick={() => setDrawerOpen(false)} />
          {/* Panel */}
          <div className="relative w-80 max-w-[85vw] bg-gray-950 h-full shadow-2xl overflow-y-auto animate-slide-in-left">
            <div className="p-4 border-b border-white/10 flex items-center justify-between">
              <h2 className="font-semibold text-white">Categories</h2>
              <button onClick={() => setDrawerOpen(false)} className="text-gray-400 hover:text-white">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <nav className="p-2">
              <button onClick={() => { handleCategoryChange(null); setDrawerOpen(false); }}
                className={`w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition ${
                  !categoryId ? 'bg-white/10 text-white font-medium' : 'text-gray-400 hover:text-white hover:bg-white/5'
                }`}>
                {dict.home.all_products} ({initialProducts.length})
              </button>
              {initialCategories.map((cat) => (
                <button key={cat.category_id}
                  onClick={() => { handleCategoryChange(String(cat.category_id)); setDrawerOpen(false); }}
                  className={`w-full text-left px-4 py-3 rounded-lg text-sm transition ${
                    categoryId === String(cat.category_id) ? 'bg-white/10 text-white font-medium' : 'text-gray-400 hover:text-white hover:bg-white/5'
                  }`}>
                  {cat.category_name}
                  <span className="text-gray-600 ml-1">({categoryCounts.get(String(cat.category_id)) ?? 0})</span>
                </button>
              ))}
            </nav>
          </div>
        </div>
      )}

      {/* Sticky search + filters on mobile */}
      <div className="sticky top-16 z-40 bg-white -mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 pb-4 pt-2 border-b border-gray-100 sm:static sm:border-0 sm:pb-0 sm:pt-0 sm:mx-0 sm:px-0">
        {/* Search + Categories button */}
        <div className="mb-6">
          <div className="w-full">
            <div className="relative w-full">
              <svg
                className="absolute left-4 top-1/2 -translate-y-1/2 w-6 h-6 text-gray-400 pointer-events-none"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                type="text"
                value={searchInput}
                onChange={(e) => handleSearchChange(e.target.value)}
                placeholder={dict.home.search_placeholder}
                className="w-full pl-12 pr-12 py-4 bg-white border-2 border-gray-200 rounded-xl text-base focus:outline-none focus:ring-2 focus:ring-red-700/20 focus:border-red-700 placeholder:text-gray-400 shadow-sm"
              />
              {searchInput && (
                <button
                  onClick={() => { setSearchInput(''); setSearch(''); setVisibleCount(PAGE_SIZE); }}
                  className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"
                  aria-label="Clear search"
                >
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              )}
            </div>
            <button onClick={() => setDrawerOpen(true)}
              className="w-full mt-3 flex items-center justify-center gap-2 py-3 rounded-xl border-2 border-gray-200 text-base font-semibold text-gray-700 hover:bg-gray-50 transition">
              <span>📋</span>
              {lang === 'es' ? 'Explorar Categorías' : 'Browse Categories'}
            </button>
          </div>
          {isFiltering && filtered.length > 0 && (
            <p className="text-center text-sm text-gray-500 mt-2">
              {dict.home.products_found.replace('{count}', String(filtered.length))}
            </p>
          )}
        </div>

        {/* Active category banner */}
        {categoryId && (
          <div className="flex items-center gap-2 mb-4 px-4 py-3 bg-red-50 rounded-xl">
            <span className="text-sm font-semibold text-red-700">
              {initialCategories.find(c => String(c.category_id) === categoryId)?.category_name}
            </span>
            <button onClick={() => handleCategoryChange(null)} className="ml-auto text-red-700 hover:text-red-900 text-sm font-medium underline">
              {dict.home.clear_filters}
            </button>
          </div>
        )}

        {/* Stock filter */}
        <div className="flex justify-center gap-2 mb-6">
          <button
            onClick={() => { setStockFilter('all'); setVisibleCount(PAGE_SIZE); }}
            className={`px-6 py-3 rounded-xl text-base font-semibold transition ${
              stockFilter === 'all'
                ? 'border border-gray-900 bg-gray-900 text-white shadow-sm'
                : 'border border-gray-200 text-gray-500 hover:text-gray-700 hover:border-gray-300 bg-white'
            }`}
          >
            {dict.home.all_products} ({initialProducts.length.toLocaleString()})
          </button>
          <button
            onClick={() => { setStockFilter('available'); setVisibleCount(PAGE_SIZE); }}
            className={`px-6 py-3 rounded-xl text-base font-semibold transition ${
              stockFilter === 'available'
                ? 'border border-emerald-600 bg-emerald-600 text-white shadow-sm'
                : 'border border-gray-200 text-gray-500 hover:text-gray-700 hover:border-gray-300 bg-white'
            }`}
          >
            {dict.home.in_stock} ({inStockCount.toLocaleString()})
          </button>
        </div>

        {/* Categories — desktop only */}
        {initialCategories.length > 0 && (
          <div className="hidden md:block mb-6">
            <CategoryBar
              categories={initialCategories}
              activeId={categoryId}
              onSelect={handleCategoryChange}
              dict={dict}
              counts={categoryCounts}
            />
          </div>
        )}
      </div>

      {/* Product Grid */}
      {filtered.length === 0 ? (
        <div className="text-center py-20">
          <p className="text-gray-400">{dict.home.no_products}</p>
          {isFiltering && (
            <button
              onClick={clearFilters}
              className="mt-4 text-sm text-red-700 hover:text-red-800 font-medium transition"
            >
              {dict.home.clear_filters}
            </button>
          )}
        </div>
      ) : (
        <>
          <div className="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
            {visible.map((product, i) => (
              <div
                key={product.product_id}
                className={i < PAGE_SIZE ? 'card-enter' : undefined}
                style={i < PAGE_SIZE ? { animationDelay: `${Math.min(i * 50, 400)}ms` } : undefined}
              >
                <ProductCard
                  product={product}
                  dict={dict}
                  lang={lang}
                  showPrice={showPrice}
                  priority={i < 8}
                />
              </div>
            ))}
          </div>

          {/* Infinite scroll sentinel */}
          {hasMore && (
            <div ref={loadMoreRef} className="flex flex-col items-center py-8">
              <div className="w-5 h-5 border-2 border-gray-200 border-t-red-700 rounded-full animate-spin" />
              <p className="text-xs text-gray-400 mt-3">
                {dict.home.showing_of.replace('{shown}', String(visible.length)).replace('{total}', String(filtered.length))}
              </p>
            </div>
          )}
        </>
      )}
    </>
  );
}
