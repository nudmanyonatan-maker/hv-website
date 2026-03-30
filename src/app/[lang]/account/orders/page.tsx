'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import type { Locale, Order, Dictionary } from '@/types';
import { formatPrice } from '@/lib/utils';
import { getDictionary } from '@/i18n/getDictionary';

export default function OrdersPage() {
  const params = useParams();
  const lang = (params.lang as Locale) ?? 'en';

  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);
  const [expandedId, setExpandedId] = useState<number | null>(null);
  const [dict, setDict] = useState<Dictionary | null>(null);

  useEffect(() => {
    getDictionary(lang).then(setDict);
    async function fetchOrders() {
      try {
        const res = await fetch(`/api/orders?lang=${lang}`);
        const data = await res.json();
        setOrders(data.orders ?? []);
      } catch { /* ignore */ }
      finally { setLoading(false); }
    }
    fetchOrders();
  }, [lang]);

  if (loading || !dict) return <div className="text-center py-20 text-gray-400">{dict?.common.loading ?? 'Loading...'}</div>;

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900 mb-6">{dict.orders.title}</h1>

      {orders.length === 0 ? (
        <p className="text-center text-gray-400 py-20">{dict.orders.no_orders}</p>
      ) : (
        <div className="space-y-4">
          {orders.map((order) => {
            const statusText = lang === 'es' ? order.name_status_spanish : order.name_status_english;
            const expanded = expandedId === order.order_id;

            return (
              <div key={order.order_id} className="border border-gray-100 rounded-xl overflow-hidden">
                <button
                  onClick={() => setExpandedId(expanded ? null : order.order_id)}
                  className="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition text-left"
                >
                  <div className="flex items-center gap-4">
                    <span className="text-sm font-medium text-gray-900">{dict.orders.order_number}{order.order_number}</span>
                    <span
                      className="text-xs px-2 py-1 rounded-full font-medium"
                      style={{ backgroundColor: order.scolor + '20', color: order.scolor }}
                    >
                      {statusText}
                    </span>
                  </div>
                  <div className="flex items-center gap-4">
                    <span className="text-xs text-gray-400">
                      {new Date(order.created).toLocaleDateString(lang === 'es' ? 'es-ES' : 'en-US')}
                    </span>
                    <span className="text-sm font-bold text-gray-900">
                      {formatPrice(order.ordered_total, 'USD', lang)}
                    </span>
                    <svg className={`w-4 h-4 text-gray-400 transition ${expanded ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </button>

                {expanded && (
                  <div className="border-t border-gray-50 p-4 bg-gray-50/50">
                    {order.order_comments && (
                      <p className="text-xs text-gray-500 mb-3 italic">{order.order_comments}</p>
                    )}
                    <table className="w-full text-sm">
                      <thead>
                        <tr className="text-left text-xs text-gray-400 uppercase">
                          <th className="pb-2">{dict.table.product}</th>
                          <th className="pb-2">{dict.table.sku}</th>
                          <th className="pb-2 text-right">{dict.table.qty}</th>
                          <th className="pb-2 text-right">{dict.table.price}</th>
                          <th className="pb-2 text-right">{dict.table.line_total}</th>
                        </tr>
                      </thead>
                      <tbody>
                        {order.product_list.map((item, i) => (
                          <tr key={i} className="border-t border-gray-100">
                            <td className="py-2 text-gray-900">{item.name}</td>
                            <td className="py-2 text-gray-400">{item.sku}</td>
                            <td className="py-2 text-right">{item.qty}</td>
                            <td className="py-2 text-right">{formatPrice(item.sale_price, 'USD', lang)}</td>
                            <td className="py-2 text-right font-medium">{formatPrice(item.sale_price * item.qty, 'USD', lang)}</td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}
