import type { Locale, Dictionary } from '@/types';

const dictionaries: Record<Locale, () => Promise<Dictionary>> = {
  en: () => import('./en.json').then((m) => m.default as Dictionary),
  es: () => import('./es.json').then((m) => m.default as Dictionary),
};

export async function getDictionary(locale: Locale): Promise<Dictionary> {
  return (dictionaries[locale] ?? dictionaries.en)();
}

export const locales: Locale[] = ['en', 'es'];
export const defaultLocale: Locale = 'en';
