function required(name: string): string {
  const value = process.env[name];
  if (!value) throw new Error(`Missing required environment variable: ${name}`);
  return value;
}

export const env = {
  FULLVENDOR_BASE_URL: required('FULLVENDOR_BASE_URL'),
  FULLVENDOR_TOKEN: required('FULLVENDOR_TOKEN'),
  FULLVENDOR_COMPANY_ID: required('FULLVENDOR_COMPANY_ID'),
  JWT_SECRET: required('JWT_SECRET'),
  ADMIN_EMAIL: required('ADMIN_EMAIL'),
  RESEND_API_KEY: process.env.RESEND_API_KEY || '',
  SITE_URL: process.env.NEXT_PUBLIC_SITE_URL || 'https://hv-website-phi.vercel.app',
} as const;
