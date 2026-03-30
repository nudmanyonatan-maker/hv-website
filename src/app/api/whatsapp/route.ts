import { NextRequest, NextResponse } from 'next/server';
import { z } from 'zod';
import { sendWhatsApp } from '@/lib/birdview';

const BUSINESS_NUMBER = '17736812440';

// Simple in-memory rate limiter
const rateLimit = new Map<string, { count: number; resetAt: number }>();
const RATE_LIMIT = 3;
const RATE_WINDOW = 60_000; // 1 minute

function checkRateLimit(ip: string): boolean {
  const now = Date.now();
  const entry = rateLimit.get(ip);
  if (!entry || now > entry.resetAt) {
    rateLimit.set(ip, { count: 1, resetAt: now + RATE_WINDOW });
    return true;
  }
  if (entry.count >= RATE_LIMIT) return false;
  entry.count++;
  return true;
}

const messageSchema = z.object({
  name: z.string().min(1).max(200),
  message: z.string().min(1).max(1000),
  phone: z.string().optional(),
});

export async function POST(request: NextRequest) {
  const ip = request.headers.get('x-forwarded-for')?.split(',')[0]?.trim() || 'unknown';
  if (!checkRateLimit(ip)) {
    return NextResponse.json({ error: 'Too many requests. Please wait.' }, { status: 429 });
  }

  try {
    const body = await request.json();
    const parsed = messageSchema.safeParse(body);
    if (!parsed.success) {
      return NextResponse.json({ error: parsed.error.issues[0].message }, { status: 400 });
    }

    const { name, message, phone } = parsed.data;
    const fullMessage = `New inquiry from ${name}${phone ? ` (${phone})` : ''}:\n\n${message}`;

    const result = await sendWhatsApp(BUSINESS_NUMBER, fullMessage);

    if (!result.success) {
      return NextResponse.json({ error: 'Failed to send message' }, { status: 500 });
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('[whatsapp-api] Error:', error);
    return NextResponse.json({ error: 'Internal error' }, { status: 500 });
  }
}
