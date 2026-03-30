import type { Metadata, Viewport } from 'next';

export const metadata: Metadata = {
  title: 'Home Value — Wholesale Food Distribution',
  description: 'Quality wholesale products for grocery stores, restaurants, and supermarkets.',
  icons: {
    icon: '/logo.png',
  },
};

export const viewport: Viewport = {
  themeColor: '#b91c1c',
  width: 'device-width',
  initialScale: 1,
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  // The [lang] layout handles <html> and <body>
  return children;
}
