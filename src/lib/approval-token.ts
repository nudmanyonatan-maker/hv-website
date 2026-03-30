import crypto from 'crypto';

/** Generate HMAC-signed approval token */
export function generateApprovalToken(registrationId: number, secret: string): string {
  const expiry = Date.now() + 24 * 60 * 60 * 1000; // 24 hours
  const payload = `${registrationId}:${expiry}`;
  const signature = crypto.createHmac('sha256', secret).update(payload).digest('hex');
  return Buffer.from(`${payload}:${signature}`).toString('base64url');
}

/** Verify HMAC-signed approval token. Returns registration ID or null. */
export function verifyApprovalToken(token: string, secret: string): number | null {
  try {
    const decoded = Buffer.from(token, 'base64url').toString();
    const [idStr, expiryStr, signature] = decoded.split(':');
    const payload = `${idStr}:${expiryStr}`;
    const expected = crypto.createHmac('sha256', secret).update(payload).digest('hex');
    const sigBuf = Buffer.from(signature, 'hex');
    const expBuf = Buffer.from(expected, 'hex');
    if (sigBuf.length !== expBuf.length || !crypto.timingSafeEqual(sigBuf, expBuf)) return null;
    const expiry = Number(expiryStr);
    const id = Number(idStr);
    if (!Number.isFinite(expiry) || !Number.isFinite(id)) return null;
    if (Date.now() > expiry) return null;
    return id;
  } catch (error) {
    console.error('[approval-token] Verification failed:', error);
    return null;
  }
}
