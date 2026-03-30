import Link from 'next/link';
import type { Locale } from '@/types';
import { getDictionary } from '@/i18n/getDictionary';

export default async function AccountLayout({
  children,
  params,
}: {
  children: React.ReactNode;
  params: Promise<{ lang: string }>;
}) {
  const { lang } = await params;
  const locale = (lang === 'es' ? 'es' : 'en') as Locale;
  const dict = await getDictionary(locale);

  const links = [
    { href: `/${locale}/account/catalog`, label: dict.nav.catalog },
    { href: `/${locale}/account/cart`, label: dict.nav.cart },
    { href: `/${locale}/account/orders`, label: dict.nav.orders },
    { href: `/${locale}/account/invoices`, label: dict.nav.invoices },
  ];

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {/* Account nav */}
      <nav className="flex gap-2 overflow-x-auto pb-4 mb-6 border-b border-gray-100">
        {links.map((link) => (
          <Link
            key={link.href}
            href={link.href}
            className="whitespace-nowrap px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition"
          >
            {link.label}
          </Link>
        ))}
      </nav>

      {children}
    </div>
  );
}
