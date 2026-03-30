'use client';

import { useState } from 'react';
import { useParams } from 'next/navigation';
import Link from 'next/link';
import type { Locale } from '@/types';

export default function RegisterPage() {
  const params = useParams();
  const lang = (params.lang as Locale) ?? 'en';

  const [form, setForm] = useState({
    contactName: '', companyName: '', taxId: '', address: '',
    email: '', phone: '', mobile: '', hasWhatsapp: false, password: '',
  });
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);
  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  function update(field: string, value: string | boolean) {
    setForm((prev) => ({ ...prev, [field]: value }));
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const res = await fetch('/api/auth/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });

      const data = await res.json();

      if (!res.ok) {
        setError(data.error ?? 'Registration failed');
        return;
      }

      setSuccess(true);
    } catch {
      setError('Something went wrong. Please try again.');
    } finally {
      setLoading(false);
    }
  }

  const t = lang === 'es' ? {
    title: 'Crear una Cuenta Mayorista',
    contactName: 'Nombre de Contacto', companyName: 'Nombre de la Empresa',
    taxId: 'RIF / Identificación Fiscal', address: 'Dirección Comercial',
    email: 'Correo Electrónico', password: 'Contraseña',
    phone: 'Teléfono', mobile: 'Celular', hasWhatsapp: '¿Tiene WhatsApp?',
    button: 'Enviar Registro', hasAccount: '¿Ya tiene una cuenta?', login: 'Iniciar Sesión',
    successTitle: '¡Registro enviado!',
    successMsg: 'Hemos recibido su solicitud. Nuestro equipo configurará su cuenta y le enviará sus credenciales de acceso por correo electrónico.',
    sectionBusiness: 'Información Comercial',
    sectionContact: 'Información de Contacto',
    sectionSecurity: 'Seguridad de la Cuenta',
    passwordHelper: 'Mínimo 6 caracteres',
  } : {
    title: 'Create a Wholesale Account',
    contactName: 'Contact Name', companyName: 'Company Name',
    taxId: 'Tax ID / EIN', address: 'Business Address',
    email: 'Email', password: 'Password',
    phone: 'Phone', mobile: 'Mobile / Cellphone', hasWhatsapp: 'Do you have WhatsApp?',
    button: 'Submit Registration', hasAccount: 'Already have an account?', login: 'Login',
    successTitle: 'Registration Submitted!',
    successMsg: 'We\'ve received your request. Our team will set up your account and send you your login credentials by email.',
    sectionBusiness: 'Business Information',
    sectionContact: 'Contact Information',
    sectionSecurity: 'Account Security',
    passwordHelper: 'Minimum 6 characters',
  };

  if (success) {
    return (
      <div className="min-h-[60vh] flex items-center justify-center px-4 py-12">
        <div className="text-center max-w-md">
          <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h2 className="text-xl font-bold text-gray-900 mb-2">{t.successTitle}</h2>
          <p className="text-gray-500">{t.successMsg}</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-[60vh] flex items-center justify-center px-4 py-12">
      <div className="w-full max-w-lg">
        <h1 className="text-2xl font-bold text-gray-900 text-center mb-8">{t.title}</h1>

        <form onSubmit={handleSubmit} className="space-y-4">
          {error && (
            <div className="bg-red-50 text-red-600 text-sm p-3 rounded-lg">{error}</div>
          )}

          {/* Business Information */}
          <h2 className="text-sm font-semibold text-gray-700 mb-3 mt-6">{t.sectionBusiness}</h2>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">{t.companyName} *</label>
              <input type="text" required value={form.companyName} onChange={(e) => update('companyName', e.target.value)}
                className="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-700" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">{t.taxId}</label>
              <input type="text" value={form.taxId} onChange={(e) => update('taxId', e.target.value)}
                className="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-700" />
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">{t.address}</label>
            <input type="text" value={form.address} onChange={(e) => update('address', e.target.value)}
              className="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-700" />
          </div>

          {/* Contact Information */}
          <h2 className="text-sm font-semibold text-gray-700 mb-3 mt-6">{t.sectionContact}</h2>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">{t.contactName} *</label>
              <input type="text" required value={form.contactName} onChange={(e) => update('contactName', e.target.value)}
                className="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-700" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">{t.email} *</label>
              <input type="email" required value={form.email} onChange={(e) => update('email', e.target.value)}
                className="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-700" />
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">{t.phone}</label>
              <input type="tel" value={form.phone} onChange={(e) => update('phone', e.target.value)}
                className="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-700" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">{t.mobile}</label>
              <input type="tel" value={form.mobile} onChange={(e) => update('mobile', e.target.value)}
                className="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-700" />
            </div>
          </div>

          <label className="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" checked={form.hasWhatsapp} onChange={(e) => update('hasWhatsapp', e.target.checked)}
              className="w-4 h-4 rounded border-gray-300" />
            <span className="text-sm text-gray-700">{t.hasWhatsapp}</span>
          </label>

          {/* Account Security */}
          <h2 className="text-sm font-semibold text-gray-700 mb-3 mt-6">{t.sectionSecurity}</h2>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">{t.password} *</label>
            <div className="relative">
              <input type={showPassword ? 'text' : 'password'} required minLength={6} value={form.password} onChange={(e) => update('password', e.target.value)}
                className="w-full px-4 py-3 pr-12 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-700" />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"
                tabIndex={-1}
              >
                {showPassword ? (
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                ) : (
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                )}
              </button>
            </div>
            <p className="text-xs text-gray-400 mt-1">{t.passwordHelper}</p>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-red-700 text-white py-3 rounded-xl font-medium hover:bg-red-800 transition disabled:opacity-50 mt-2"
          >
            {loading ? '...' : t.button}
          </button>
        </form>

        <p className="text-sm text-gray-500 text-center mt-6">
          {t.hasAccount}{' '}
          <Link href={`/${lang}/login`} className="text-gray-900 font-medium hover:underline">
            {t.login}
          </Link>
        </p>
      </div>
    </div>
  );
}
