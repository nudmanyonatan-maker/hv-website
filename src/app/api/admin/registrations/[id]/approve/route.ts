import { NextRequest, NextResponse } from 'next/server';
import { sql } from '@vercel/postgres';
import { createCustomer } from '@/lib/fullvendor';
import { sendApprovalEmail, sendRejectionEmail } from '@/lib/email';
import { verifyApprovalToken } from '@/lib/approval-token';
import { env } from '@/lib/env';

function htmlPage(title: string, color: string, message: string) {
  return new NextResponse(
    `<html><body style="font-family:sans-serif;padding:40px;text-align:center;">
      <h2 style="color:${color};">${title}</h2>
      <p>${message}</p>
    </body></html>`,
    { headers: { 'Content-Type': 'text/html' } }
  );
}

function htmlError(message: string) {
  return new NextResponse(
    `<html><body style="font-family:sans-serif;padding:40px;text-align:center;">
      <h2 style="color:#ef4444;">Error</h2>
      <p>${message}</p>
    </body></html>`,
    { status: 400, headers: { 'Content-Type': 'text/html' } }
  );
}

/** One-click approve/reject from email links — HMAC-signed token required */
export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  const action = request.nextUrl.searchParams.get('action');

  if (!['approve', 'reject'].includes(action ?? '')) {
    return htmlError('Invalid action.');
  }

  // Prefer HMAC token; fall back to path param for legacy emails
  const token = request.nextUrl.searchParams.get('token');
  let registrationId: number | null = null;

  if (token) {
    registrationId = verifyApprovalToken(token, env.JWT_SECRET);
    if (registrationId === null) {
      return htmlError('This approval link is invalid or has expired. Please ask an admin to process this registration manually.');
    }
  } else {
    return htmlError('Missing or invalid approval token. Please ask an admin to process this registration manually.');
  }

  try {
    const reg = await sql`
      SELECT * FROM pending_registrations WHERE id = ${registrationId} AND status = 'pending' LIMIT 1
    `;

    if (reg.rows.length === 0) {
      return htmlPage(
        'Already Processed',
        '#6b7280',
        'This registration was not found or has already been processed.'
      );
    }

    const registration = reg.rows[0];

    if (action === 'approve') {
      const fvRes = await createCustomer({
        userId: 1,
        businessName: registration.company_name,
        name: registration.contact_name,
        taxId: registration.tax_id,
        email: registration.email,
        phone: registration.phone,
        cellPhone: registration.mobile,
        commercialAddress: registration.address,
      });

      if (fvRes.status !== '1') {
        return htmlError('Failed to create customer in FullVendor. Please try again or process manually.');
      }

      const customerId = (fvRes.info as { customer_id?: number })?.customer_id ?? null;

      await sql`
        UPDATE pending_registrations
        SET status = 'approved', fullvendor_customer_id = ${customerId}
        WHERE id = ${registrationId}
      `;

      await sendApprovalEmail(registration.email, registration.contact_name);

      return htmlPage(
        'Approved!',
        '#22c55e',
        `${registration.contact_name} from ${registration.company_name} has been approved.`
      );
    } else {
      await sql`
        UPDATE pending_registrations SET status = 'rejected' WHERE id = ${registrationId}
      `;
      await sendRejectionEmail(registration.email, registration.contact_name);

      return htmlPage(
        'Rejected',
        '#ef4444',
        `${registration.contact_name} from ${registration.company_name} has been rejected.`
      );
    }
  } catch (error) {
    console.error('Email approval error:', error);
    return htmlError('An internal error occurred. Please try again or contact support.');
  }
}
