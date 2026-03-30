'use client';

import en from '@/i18n/en.json';
import es from '@/i18n/es.json';

const dictionaries = { en, es } as const;

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  const locale = typeof window !== 'undefined' && window.location.pathname.startsWith('/es') ? 'es' : 'en';
  const dict = dictionaries[locale];

  return (
    <div className="flex flex-col items-center justify-center min-h-[50vh] px-4 text-center">
      <div className="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mb-4">
        <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
      </div>
      <h1 className="text-xl font-semibold text-gray-900 mb-2">{dict.common.something_went_wrong}</h1>
      <p className="text-sm text-gray-500 mb-6 max-w-sm">{dict.common.error}</p>
      <div className="flex gap-3">
        <button onClick={() => reset()} className="px-5 py-2.5 bg-red-700 text-white text-sm font-medium rounded-xl hover:bg-red-800 transition">
          {dict.common.try_again}
        </button>
        <a href={`/${locale}`} className="px-5 py-2.5 border border-gray-200 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition">
          {dict.common.go_home}
        </a>
      </div>
      {process.env.NODE_ENV === 'development' && error.message && (
        <p className="text-xs text-gray-400 mt-4 font-mono max-w-md mx-auto break-all">
          {error.message.slice(0, 200)}
        </p>
      )}
    </div>
  );
}
