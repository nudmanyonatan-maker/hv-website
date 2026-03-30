import type { Metadata } from 'next';
import { DM_Sans } from 'next/font/google';
import type { Locale } from '@/types';
import { getDictionary } from '@/i18n/getDictionary';
import Header from '@/components/layout/Header';
import Footer from '@/components/layout/Footer';
import WhatsAppButton from '@/components/ui/WhatsAppButton';
import ToastProvider from '@/components/ui/ToastProvider';
import { CartProvider } from '@/lib/useCart';
import '../globals.css';

const dmSans = DM_Sans({ subsets: ['latin'], weight: ['300', '400', '500', '600', '700'] });

const SITE_URL = 'https://hv-website-phi.vercel.app';

export async function generateMetadata({
  params,
}: {
  params: Promise<{ lang: string }>;
}): Promise<Metadata> {
  const { lang } = await params;
  const locale = (lang === 'es' ? 'es' : 'en') as Locale;
  const dict = await getDictionary(locale);

  return {
    metadataBase: new URL(SITE_URL),
    title: dict.seo.title,
    description: dict.seo.description,
    robots: 'index, follow',
    alternates: {
      canonical: `${SITE_URL}/${locale}`,
      languages: {
        en: `${SITE_URL}/en`,
        es: `${SITE_URL}/es`,
      },
    },
    openGraph: {
      title: dict.seo.title,
      description: dict.seo.description,
      siteName: 'Home Value',
      type: 'website',
      images: ['/og-image.png'],
    },
    twitter: {
      card: 'summary_large_image',
    },
  };
}

export function generateStaticParams() {
  return [{ lang: 'en' }, { lang: 'es' }];
}

export default async function LangLayout({
  children,
  params,
}: {
  children: React.ReactNode;
  params: Promise<{ lang: string }>;
}) {
  const { lang } = await params;
  const locale = (lang === 'es' ? 'es' : 'en') as Locale;
  const dict = await getDictionary(locale);

  const organizationJsonLd = {
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: 'Home Value',
    url: SITE_URL,
    logo: `${SITE_URL}/logo.png`,
    description: dict.seo.description,
    contactPoint: {
      '@type': 'ContactPoint',
      contactType: 'customer service',
      availableLanguage: ['English', 'Spanish'],
    },
  };

  return (
    <html lang={locale} className="light" style={{ colorScheme: 'light' }}>
      <body className={`${dmSans.className} min-h-screen flex flex-col text-gray-900 antialiased`}>
        <ToastProvider>
          <CartProvider>
            <script
              type="application/ld+json"
              dangerouslySetInnerHTML={{ __html: JSON.stringify(organizationJsonLd) }}
            />
            <link rel="preconnect" href="https://app.fullvendor.com" />
            <link rel="dns-prefetch" href="https://app.fullvendor.com" />
            <Header dict={dict} lang={locale} />
            <main className="flex-1 pb-20 md:pb-0">{children}</main>
            <Footer dict={dict} lang={locale} />
            <WhatsAppButton />
          </CartProvider>
        </ToastProvider>
      </body>
    </html>
  );
}
