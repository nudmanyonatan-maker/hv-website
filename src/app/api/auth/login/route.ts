import { NextRequest, NextResponse } from 'next/server';
import { login as fullvendorLogin } from '@/lib/fullvendor';
import { createSession } from '@/lib/auth';
import { sql } from '@vercel/postgres';
import { loginSchema } from '@/lib/validation';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const parsed = loginSchema.safeParse(body);
    if (!parsed.success) {
      return NextResponse.json({ error: parsed.error.issues[0].message }, { status: 400 });
    }
    const { email, password } = parsed.data;

    // Check if user is in pending_registrations and approved
    let registration;
    try {
      const result = await sql`
        SELECT * FROM pending_registrations WHERE email = ${email} LIMIT 1
      `;
      registration = result.rows[0];
    } catch (dbError) {
      console.error('[login] Database query for registration failed:', dbError);
      // Fall through to FullVendor login
    }

    // Try FullVendor login
    const res = await fullvendorLogin(email, password);

    if (res.status !== '1' || !res.info) {
      return NextResponse.json({ error: 'Invalid email or password' }, { status: 401 });
    }

    const user = res.info;

    // Check local registration for customer_id mapping
    // But FullVendor authentication is the source of truth — if they can log in, they're legitimate
    let customerId = 0;
    let isApproved = true; // FullVendor auth succeeded = approved by default

    if (registration) {
      // User went through our registration flow
      if (registration.status === 'approved' && registration.fullvendor_customer_id) {
        customerId = registration.fullvendor_customer_id;
      } else if (registration.status === 'pending') {
        // They registered but haven't been approved yet
        isApproved = false;
      }
      // If status is 'rejected' but they can still log in via FullVendor,
      // it means they were created independently in FV — allow access
    }

    await createSession({
      userId: user.user_id,
      companyId: user.company_id,
      customerId,
      email: user.email,
      name: `${user.first_name} ${user.last_name}`.trim(),
      approved: isApproved,
    });

    if (!isApproved) {
      return NextResponse.json({
        success: true,
        pending: true,
        user: { name: `${user.first_name} ${user.last_name}` },
      });
    }

    return NextResponse.json({ success: true, user: { name: `${user.first_name} ${user.last_name}` } });
  } catch (error) {
    console.error('Login error:', error);
    return NextResponse.json({ error: 'Login failed' }, { status: 500 });
  }
}
