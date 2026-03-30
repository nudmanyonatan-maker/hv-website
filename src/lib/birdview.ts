const BASE_URL = 'http://telecom.birdviewmall.com/api';
const TIMEOUT_MS = 10_000;

/** Normalize phone number: strip non-digits, ensure country code */
function normalizePhone(phone: string): string {
  const digits = phone.replace(/\D/g, '');
  // If starts with 1 and is 11 digits, it's a US number
  if (digits.length === 11 && digits.startsWith('1')) return digits;
  // If 10 digits, assume US and prepend 1
  if (digits.length === 10) return '1' + digits;
  return digits;
}

export async function sendWhatsApp(to: string, message: string): Promise<{ success: boolean; error?: string }> {
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), TIMEOUT_MS);
  try {
    const res = await fetch(`${BASE_URL}/WhatsApp/Send`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ to: normalizePhone(to), message }),
      signal: controller.signal,
    });
    if (!res.ok) return { success: false, error: `HTTP ${res.status}` };
    return { success: true };
  } catch (error) {
    console.error('[birdview] WhatsApp send failed:', error);
    return { success: false, error: String(error) };
  } finally {
    clearTimeout(timeoutId);
  }
}

export async function sendWhatsAppImage(to: string, message: string, url: string): Promise<{ success: boolean }> {
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), TIMEOUT_MS);
  try {
    const res = await fetch(`${BASE_URL}/WhatsApp/SendImage`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ to: normalizePhone(to), message, url }),
      signal: controller.signal,
    });
    return { success: res.ok };
  } catch (error) {
    console.error('[birdview] WhatsApp image send failed:', error);
    return { success: false };
  } finally {
    clearTimeout(timeoutId);
  }
}

export async function sendBirdviewEmail(
  to: string[],
  subject: string,
  body: string,
  isHtml = false
): Promise<{ success: boolean }> {
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), TIMEOUT_MS);
  try {
    const res = await fetch(`${BASE_URL}/Email/SendEmail`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ Destinatary: to, Subject: subject, Cuerpo: body, isHTML: isHtml }),
      signal: controller.signal,
    });
    return { success: res.ok };
  } catch (error) {
    console.error('[birdview] Email send failed:', error);
    return { success: false };
  } finally {
    clearTimeout(timeoutId);
  }
}
