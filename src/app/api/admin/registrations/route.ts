import { NextRequest, NextResponse } from 'next/server';
import { getSession } from '@/lib/auth';
import { sql } from '@vercel/postgres';
import { createCustomer } from '@/lib/fullvendor';
import { sendApprovalEmail, sendRejectionEmail } from '@/lib/email';
import { env } from '@/lib/env';

export async function GET() {
  const session = await getSession();
  if (!session || session.email !== env.ADMIN_EMAIL) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  try {
    const result = await sql`
      SELECT * FROM pending_registrations ORDER BY created_at DESC
    `;
    return NextResponse.json({ registrations: result.rows });
  } catch (error) {
    console.error('Admin registrations error:', error);
    return NextResponse.json({ error: 'Failed to fetch registrations' }, { status: 500 });
  }
}

export async function POST(request: NextRequest) {
  const session = await getSession();
  if (!session || session.email !== env.ADMIN_EMAIL) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  try {
    const { registrationId, action } = await request.json();

    if (!registrationId || !['approve', 'reject'].includes(action)) {
      return NextResponse.json({ error: 'Invalid request' }, { status: 400 });
    }

    const reg = await sql`
      SELECT * FROM pending_registrations WHERE id = ${registrationId} LIMIT 1
    `;

    if (reg.rows.length === 0) {
      return NextResponse.json({ error: 'Registration not found' }, { status: 404 });
    }

    const registration = reg.rows[0];

    if (action === 'approve') {
      // Create customer in FullVendor
      const fvRes = await createCustomer({
        userId: session.userId,
        businessName: registration.company_name,
        name: registration.contact_name,
        taxId: registration.tax_id,
        email: registration.email,
        phone: registration.phone,
        cellPhone: registration.mobile,
        commercialAddress: registration.address,
      });

      if (fvRes.status !== '1') {
        return NextResponse.json(
          { error: 'Failed to create customer in FullVendor. Please try again.' },
          { status: 502 }
        );
      }

      let customerId = null;
      if (fvRes.info) {
        customerId = (fvRes.info as { customer_id?: number }).customer_id ?? null;
      }

      await sql`
        UPDATE pending_registrations
        SET status = 'approved', fullvendor_customer_id = ${customerId}
        WHERE id = ${registrationId}
      `;

      await sendApprovalEmail(registration.email, registration.contact_name);
    } else {
      await sql`
        UPDATE pending_registrations SET status = 'rejected' WHERE id = ${registrationId}
      `;
      await sendRejectionEmail(registration.email, registration.contact_name);
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Admin action error:', error);
    return NextResponse.json({ error: 'Action failed' }, { status: 500 });
  }
}
