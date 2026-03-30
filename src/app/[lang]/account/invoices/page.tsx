'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import type { Locale, Order, Dictionary } from '@/types';
import { formatPrice } from '@/lib/utils';
import { getDictionary } from '@/i18n/getDictionary';

export default function InvoicesPage() {
  const params = useParams();
  const lang = (params.lang as Locale) ?? 'en';

  const [invoices, setInvoices] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);
  const [dict, setDict] = useState<Dictionary | null>(null);

  useEffect(() => {
    getDictionary(lang).then(setDict);
    async function fetchInvoices() {
      try {
        const res = await fetch(`/api/orders?lang=${lang}`);
        const data = await res.json();
        // Invoices = completed/delivered orders
        const completed = (data.orders ?? []).filter(
          (o: Order) => o.name_status_english?.toLowerCase().includes('deliver') ||
                        o.name_status_english?.toLowerCase().includes('complet')
        );
        setInvoices(completed);
      } catch { /* ignore */ }
      finally { setLoading(false); }
    }
    fetchInvoices();
  }, [lang]);

  if (loading || !dict) return <div className="text-center py-20 text-gray-400">{dict?.common.loading ?? 'Loading...'}</div>;

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900 mb-6">{dict.invoices.title}</h1>

      {invoices.length === 0 ? (
        <p className="text-center text-gray-400 py-20">{dict.invoices.no_invoices}</p>
      ) : (
        <div className="space-y-3">
          {invoices.map((inv) => (
            <div key={inv.order_id} className="flex items-center justify-between p-4 border border-gray-100 rounded-xl">
              <div>
                <span className="text-sm font-medium text-gray-900">{dict.invoices.invoice} #{inv.order_number}</span>
                <span className="text-xs text-gray-400 ml-3">
                  {new Date(inv.created).toLocaleDateString(lang === 'es' ? 'es-ES' : 'en-US')}
                </span>
              </div>
              <span className="text-sm font-bold text-gray-900">{formatPrice(inv.ordered_total, 'USD', lang)}</span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
