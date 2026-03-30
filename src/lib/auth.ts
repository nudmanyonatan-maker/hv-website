import { SignJWT, jwtVerify } from 'jose';
import { cookies } from 'next/headers';
import type { SessionPayload } from '@/types';
import { env } from './env';

const SECRET = new TextEncoder().encode(env.JWT_SECRET);
const COOKIE_NAME = 'hv-session';
const COOKIE_MAX_AGE = 60 * 60 * 24 * 7; // 7 days

export async function createSession(payload: SessionPayload) {
  const token = await new SignJWT({ ...payload })
    .setProtectedHeader({ alg: 'HS256' })
    .setExpirationTime('7d')
    .setIssuedAt()
    .sign(SECRET);

  const cookieStore = await cookies();
  // JWT session cookie — httpOnly for security (not accessible via JS)
  cookieStore.set(COOKIE_NAME, token, {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    maxAge: COOKIE_MAX_AGE,
    path: '/',
  });
  // Auth indicator cookie — readable by client JS for UI state (no sensitive data)
  cookieStore.set('hv-logged-in', '1', {
    httpOnly: false,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    maxAge: COOKIE_MAX_AGE,
    path: '/',
  });

  return token;
}

export async function getSession(): Promise<SessionPayload | null> {
  const cookieStore = await cookies();
  const token = cookieStore.get(COOKIE_NAME)?.value;
  if (!token) return null;

  try {
    const { payload } = await jwtVerify(token, SECRET);
    return payload as unknown as SessionPayload;
  } catch (error) {
    if (process.env.NODE_ENV === 'development') {
      console.debug('[auth] Session verification failed:', error instanceof Error ? error.message : error);
    }
    return null;
  }
}

export async function destroySession() {
  const cookieStore = await cookies();
  cookieStore.delete(COOKIE_NAME);
  cookieStore.delete('hv-logged-in');
}

/** Verify a JWT token string (for use in proxy/middleware where cookies() isn't available) */
export async function verifyToken(token: string): Promise<SessionPayload | null> {
  try {
    const { payload } = await jwtVerify(token, SECRET);
    return payload as unknown as SessionPayload;
  } catch (error) {
    if (process.env.NODE_ENV === 'development') {
      console.debug('[auth] Token verification failed:', error instanceof Error ? error.message : error);
    }
    return null;
  }
}
