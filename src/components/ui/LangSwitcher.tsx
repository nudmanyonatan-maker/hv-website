'use client';

import { usePathname, useRouter } from 'next/navigation';
import type { Locale } from '@/types';

export default function LangSwitcher({ lang }: { lang: Locale }) {
  const pathname = usePathname();
  const router = useRouter();

  function switchTo(newLang: Locale) {
    if (newLang === lang) return;
    const newPath = pathname.replace(`/${lang}`, `/${newLang}`);
    router.push(newPath);
  }

  return (
    <div className="flex items-center gap-1">
      <button
        onClick={() => switchTo('en')}
        className={`flex items-center gap-1 px-3 py-1.5 rounded-md text-sm font-medium transition ${
          lang === 'en'
            ? 'bg-gray-100 text-gray-900'
            : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'
        }`}
        aria-label="English"
      >
        <svg className="w-5 h-3.5 rounded-sm" viewBox="0 0 20 14" fill="none">
          <rect width="20" height="14" fill="#B22234"/>
          <rect y="2" width="20" height="2" fill="white"/>
          <rect y="6" width="20" height="2" fill="white"/>
          <rect y="10" width="20" height="2" fill="white"/>
          <rect width="8" height="8" fill="#3C3B6E"/>
        </svg>
        <span>EN</span>
      </button>
      <button
        onClick={() => switchTo('es')}
        className={`flex items-center gap-1 px-3 py-1.5 rounded-md text-sm font-medium transition ${
          lang === 'es'
            ? 'bg-gray-100 text-gray-900'
            : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'
        }`}
        aria-label="Español"
      >
        <svg className="w-5 h-3.5 rounded-sm" viewBox="0 0 20 14" fill="none">
          <rect width="20" height="14" fill="#AA151B"/>
          <rect y="3.5" width="20" height="7" fill="#F1BF00"/>
        </svg>
        <span>ES</span>
      </button>
    </div>
  );
}
