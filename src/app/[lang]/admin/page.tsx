'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import type { Locale, PendingRegistration, Dictionary } from '@/types';
import { getDictionary } from '@/i18n/getDictionary';

export default function AdminPage() {
  const params = useParams();
  const lang = (params.lang as Locale) ?? 'en';

  const [registrations, setRegistrations] = useState<PendingRegistration[]>([]);
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState<number | null>(null);
  const [dict, setDict] = useState<Dictionary | null>(null);

  useEffect(() => {
    getDictionary(lang).then(setDict);
    fetchRegistrations();
  }, [lang]);

  async function fetchRegistrations() {
    try {
      const res = await fetch('/api/admin/registrations');
      const data = await res.json();
      setRegistrations(data.registrations ?? []);
    } catch { /* ignore */ }
    finally { setLoading(false); }
  }

  async function handleAction(id: number, action: 'approve' | 'reject') {
    setActionLoading(id);
    try {
      await fetch('/api/admin/registrations', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ registrationId: id, action }),
      });
      fetchRegistrations();
    } finally {
      setActionLoading(null);
    }
  }

  if (loading || !dict) return <div className="text-center py-20 text-gray-400">{dict?.common.loading ?? 'Loading...'}</div>;

  return (
    <div className="max-w-4xl mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold text-gray-900 mb-6">{dict.admin.title}</h1>

      {registrations.length === 0 ? (
        <p className="text-center text-gray-400 py-20">{dict.admin.no_registrations}</p>
      ) : (
        <div className="space-y-4">
          {registrations.map((reg) => (
            <div key={reg.id} className="p-4 border border-gray-100 rounded-xl">
              <div className="flex items-start justify-between">
                <div>
                  <h3 className="font-medium text-gray-900">{reg.contact_name}</h3>
                  <p className="text-sm text-gray-500">{reg.company_name}</p>
                  <div className="text-xs text-gray-400 mt-1 space-y-0.5">
                    <p>{reg.email} | {reg.phone} | {reg.mobile}</p>
                    <p>{dict.admin.tax_id}: {reg.tax_id} | {dict.admin.address}: {reg.address}</p>
                    <p>{dict.admin.whatsapp}: {reg.has_whatsapp ? dict.common.yes : dict.common.no}</p>
                    <p>{dict.admin.registered}: {new Date(reg.created_at).toLocaleDateString(lang === 'es' ? 'es-ES' : 'en-US')}</p>
                  </div>
                </div>

                <div className="flex items-center gap-2">
                  {reg.status === 'pending' ? (
                    <>
                      <button
                        onClick={() => handleAction(reg.id, 'approve')}
                        disabled={actionLoading === reg.id}
                        className="px-3 py-1.5 bg-green-500 text-white text-xs rounded-lg hover:bg-green-600 disabled:opacity-50"
                      >
                        {dict.admin.approve}
                      </button>
                      <button
                        onClick={() => handleAction(reg.id, 'reject')}
                        disabled={actionLoading === reg.id}
                        className="px-3 py-1.5 bg-red-500 text-white text-xs rounded-lg hover:bg-red-600 disabled:opacity-50"
                      >
                        {dict.admin.reject}
                      </button>
                    </>
                  ) : (
                    <span className={`text-xs px-2 py-1 rounded-full font-medium ${
                      reg.status === 'approved'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-700'
                    }`}>
                      {reg.status === 'approved' ? dict.admin.approved : dict.admin.rejected}
                    </span>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
