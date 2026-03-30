'use client';

import { useState } from 'react';
import { useParams } from 'next/navigation';
import type { Locale } from '@/types';

export default function ContactPage() {
  const params = useParams();
  const lang = (params.lang as Locale) ?? 'en';

  const [form, setForm] = useState({ name: '', email: '', subject: '', message: '' });
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const t = lang === 'es' ? {
    title: 'Contáctenos', name: 'Su Nombre', email: 'Su Correo',
    subject: 'Asunto', message: 'Mensaje', send: 'Enviar Mensaje',
    success: '¡Mensaje enviado! Nos pondremos en contacto pronto.',
    sendAnother: 'Enviar otro mensaje',
  } : {
    title: 'Contact Us', name: 'Your Name', email: 'Your Email',
    subject: 'Subject', message: 'Message', send: 'Send Message',
    success: "Message sent! We'll get back to you soon.",
    sendAnother: 'Send another message',
  };

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const res = await fetch('/api/contact', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });

      if (!res.ok) {
        const data = await res.json();
        setError(data.error ?? 'Failed to send');
        return;
      }

      setSuccess(true);
    } catch {
      setError('Something went wrong.');
    } finally {
      setLoading(false);
    }
  }

  function resetForm() {
    setSuccess(false);
    setForm({ name: '', email: '', subject: '', message: '' });
    setError('');
  }

  return (
    <div className="max-w-xl mx-auto px-4 py-12">
      <h1 className="text-2xl font-bold text-gray-900 text-center mb-8">{t.title}</h1>

      {/* Inline success banner */}
      {success && (
        <div className="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-start gap-3">
          <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
            <svg className="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div className="flex-1">
            <p className="text-sm text-green-800">{t.success}</p>
            <button
              onClick={resetForm}
              className="text-sm text-green-700 font-medium hover:underline mt-1"
            >
              {t.sendAnother}
            </button>
          </div>
        </div>
      )}

      <form onSubmit={handleSubmit} className={`space-y-5 ${success ? 'opacity-50 pointer-events-none' : ''}`}>
        {error && <div className="bg-red-50 text-red-600 text-sm p-3 rounded-lg">{error}</div>}

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">{t.name}</label>
          <input type="text" required value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })}
            className="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-700" />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">{t.email}</label>
          <input type="email" required value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })}
            className="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-700" />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">{t.subject}</label>
          <input type="text" required value={form.subject} onChange={(e) => setForm({ ...form, subject: e.target.value })}
            className="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-700" />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">{t.message}</label>
          <textarea required rows={5} value={form.message} onChange={(e) => setForm({ ...form, message: e.target.value })}
            className="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-700 resize-none" />
        </div>

        <button type="submit" disabled={loading || success}
          className="w-full bg-red-700 text-white py-3 rounded-xl font-medium hover:bg-red-800 transition disabled:opacity-50">
          {loading ? '...' : t.send}
        </button>
      </form>
    </div>
  );
}
