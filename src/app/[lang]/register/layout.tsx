import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Register | Home Value',
  robots: { index: false, follow: true },
};

export default function RegisterLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return children;
}
