'use client';

import { useState } from 'react';
import { useParams, useRouter, useSearchParams } from 'next/navigation';
import Link from 'next/link';
import type { Locale, Dictionary } from '@/types';
import en from '@/i18n/en.json';
import es from '@/i18n/es.json';

function isValidRedirect(url: string | null): boolean {
  if (!url) return false;
  // Must start with / and not // (prevents protocol-relative URLs)
  return url.startsWith('/') && !url.startsWith('//');
}

export default function LoginPage() {
  const params = useParams();
  const router = useRouter();
  const searchParams = useSearchParams();
  const lang = (params.lang as Locale) ?? 'en';
  const from = searchParams.get('from');
  const dict: Dictionary = lang === 'es' ? es : en;

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState('');
  const [pending, setPending] = useState(false);
  const [loading, setLoading] = useState(false);

  // Inline validation state
  const [touched, setTouched] = useState<{ email: boolean; password: boolean }>({
    email: false,
    password: false,
  });

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');
    setPending(false);
    setLoading(true);

    try {
      const res = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      });

      const data = await res.json();

      if (!res.ok) {
        setError(data.error ?? 'Login failed');
        return;
      }

      // If user is pending approval, show message instead of redirecting to account
      if (data.pending) {
        setPending(true);
        return;
      }

      if (data.success && !data.pending) {
        localStorage.setItem('hv-user-name', data.user?.name || '');
      }
      router.push(isValidRedirect(from) ? from! : `/${lang}/account/catalog`);
      router.refresh();
    } catch {
      setError(dict.common.error);
    } finally {
      setLoading(false);
    }
  }

  const emailEmpty = touched.email && !email.trim();
  const passwordEmpty = touched.password && !password.trim();

  return (
    <div className="min-h-[60vh] flex items-center justify-center px-4 py-12">
      <div className="w-full max-w-md">
        <h1 className="text-2xl font-bold text-gray-900 text-center mb-8">{dict.auth.login_title}</h1>

        <form onSubmit={handleSubmit} className="space-y-5">
          {error && (
            <div className="bg-red-50 text-red-600 text-sm p-3 rounded-lg">{error}</div>
          )}

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">{dict.auth.email}</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              onBlur={() => setTouched((prev) => ({ ...prev, email: true }))}
              required
              className={`w-full px-4 py-3 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-700 ${
                emailEmpty ? 'border-red-400' : 'border-gray-200'
              }`}
            />
            {emailEmpty && (
              <p className="text-xs text-red-500 mt-1">{dict.auth.email} is required</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">{dict.auth.password}</label>
            <div className="relative">
              <input
                type={showPassword ? 'text' : 'password'}
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                onBlur={() => setTouched((prev) => ({ ...prev, password: true }))}
                required
                className={`w-full px-4 py-3 pr-12 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-700 ${
                  passwordEmpty ? 'border-red-400' : 'border-gray-200'
                }`}
              />
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
            {passwordEmpty && (
              <p className="text-xs text-red-500 mt-1">{dict.auth.password} is required</p>
            )}
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-red-700 text-white py-3 rounded-xl font-medium hover:bg-red-800 transition disabled:opacity-50"
          >
            {loading ? '...' : dict.auth.login_button}
          </button>
        </form>

        {pending && (
          <div className="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
            <p className="font-medium">{dict.auth.pending_approval}</p>
          </div>
        )}

        <p className="text-sm text-gray-500 text-center mt-6">
          {dict.auth.no_account}{' '}
          <Link href={`/${lang}/register`} className="text-gray-900 font-medium hover:underline">
            {dict.nav.register}
          </Link>
        </p>
      </div>
    </div>
  );
}
